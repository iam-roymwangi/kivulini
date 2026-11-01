<?php

use App\Models\Merchandise;
use App\Models\MerchandiseOrder;
use App\Models\MpesaTransaction;
use Illuminate\Support\Facades\Http;

function fakeMerchStkPush(): void
{
    Http::preventStrayRequests();

    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'test-token',
            'expires_in' => '3599',
        ]),
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
            'MerchantRequestID' => 'mr-merch',
            'CheckoutRequestID' => 'ws_CO_MERCH',
            'ResponseCode' => '0',
            'CustomerMessage' => 'Success. Request accepted for processing',
        ]),
    ]);
}

function merchOrderPayload(array $overrides = []): array
{
    return array_merge([
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 1,
        'size' => 'M',
        'color' => 'Black',
        'payment_method' => 'mpesa',
        'mpesa_phone' => '0700000001',
    ], $overrides);
}

test('merchandise order store initiates stk push and reserves stock', function () {
    fakeMerchStkPush();

    $item = Merchandise::factory()->published()->create([
        'price' => 2000,
        'stock' => 5,
        'sizes' => ['S', 'M', 'L'],
        'colors' => ['Black', 'White'],
    ]);

    $this->postJson("/merchandise/{$item->id}/orders", merchOrderPayload(['quantity' => 2]))
        ->assertCreated()
        ->assertJsonStructure(['order_reference', 'order_id', 'payment_id']);

    expect($item->fresh()->stock)->toBe(3);
    expect(MerchandiseOrder::query()->first()?->payment_status)->toBe('pending');
    $this->assertDatabaseHas('mpesa_transactions', [
        'checkout_request_id' => 'ws_CO_MERCH',
        'amount' => 4000,
        'status' => 'pending',
    ]);
});

test('merchandise order store returns 422 when stock is insufficient', function () {
    $item = Merchandise::factory()->published()->create([
        'stock' => 1,
        'sizes' => ['M'],
        'colors' => ['Black'],
    ]);

    $this->postJson("/merchandise/{$item->id}/orders", merchOrderPayload(['quantity' => 3]))
        ->assertUnprocessable()
        ->assertJson(['message' => 'This item is out of stock.']);
});

test('merchandise order store validates size against the item', function () {
    $item = Merchandise::factory()->published()->create([
        'sizes' => ['M'],
        'colors' => ['Black'],
        'stock' => 5,
    ]);

    $this->postJson("/merchandise/{$item->id}/orders", merchOrderPayload(['size' => 'XXL']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['size']);
});

test('successful merch stk callback marks the order paid without changing stock twice', function () {
    fakeMerchStkPush();

    $item = Merchandise::factory()->published()->create([
        'price' => 2000,
        'stock' => 4,
        'sizes' => ['M'],
        'colors' => ['Black'],
    ]);

    $this->postJson("/merchandise/{$item->id}/orders", merchOrderPayload())->assertCreated();

    $this->postJson('/webhooks/mpesa/stk/test-callback-secret', [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => 'mr-merch',
                'CheckoutRequestID' => 'ws_CO_MERCH',
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 2000],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'QWE9AB12CD'],
                    ],
                ],
            ],
        ],
    ])->assertOk();

    expect(MerchandiseOrder::query()->first()?->payment_status)->toBe('paid');
    expect(MerchandiseOrder::query()->first()?->payment_reference)->toBe('QWE9AB12CD');
    expect($item->fresh()->stock)->toBe(3);
    expect(MpesaTransaction::query()->first()?->status)->toBe('completed');
});

test('failed merch stk callback restores reserved stock', function () {
    fakeMerchStkPush();

    $item = Merchandise::factory()->published()->create([
        'price' => 2000,
        'stock' => 4,
        'sizes' => ['M'],
        'colors' => ['Black'],
    ]);

    $this->postJson("/merchandise/{$item->id}/orders", merchOrderPayload())->assertCreated();

    $this->postJson('/webhooks/mpesa/stk/test-callback-secret', [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => 'mr-merch',
                'CheckoutRequestID' => 'ws_CO_MERCH',
                'ResultCode' => 1032,
                'ResultDesc' => 'Request cancelled by user',
            ],
        ],
    ])->assertOk();

    expect(MerchandiseOrder::query()->first()?->payment_status)->toBe('failed');
    expect($item->fresh()->stock)->toBe(4);
});
