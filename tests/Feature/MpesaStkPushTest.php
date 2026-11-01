<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\MpesaTransaction;
use Illuminate\Support\Facades\Http;

function fakeSuccessfulStkPush(): void
{
    Http::preventStrayRequests();

    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'test-token',
            'expires_in' => '3599',
        ]),
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
            'MerchantRequestID' => 'mr-123',
            'CheckoutRequestID' => 'ws_CO_123',
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success. Request accepted for processing',
            'CustomerMessage' => 'Success. Request accepted for processing',
        ]),
    ]);
}

function stkCallbackPayload(string $checkoutRequestId, int $amount = 1500, string $receipt = 'NLJ7RT61SV', int $resultCode = 0): array
{
    return [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => 'mr-123',
                'CheckoutRequestID' => $checkoutRequestId,
                'ResultCode' => $resultCode,
                'ResultDesc' => $resultCode === 0 ? 'The service request is processed successfully.' : 'Request cancelled by user',
                'CallbackMetadata' => $resultCode === 0 ? [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => $amount],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt],
                        ['Name' => 'TransactionDate', 'Value' => 20191219102115],
                        ['Name' => 'PhoneNumber', 'Value' => 254700000001],
                    ],
                ] : [],
            ],
        ],
    ];
}

test('booking store initiates stk push and keeps the booking pending', function () {
    fakeSuccessfulStkPush();

    $event = Event::factory()->create(['price' => 1500, 'capacity' => 10, 'booked_slots' => 0, 'status' => 'published']);

    $response = $this->postJson("/events/{$event->id}/bookings", validPayload([
        'quantity' => 2,
        'payment_method' => 'mpesa',
        'mpesa_phone' => '0700000001',
    ]));

    $response->assertCreated()
        ->assertJsonStructure(['booking_reference', 'booking_id', 'payment_id', 'payment_status']);

    expect($event->fresh()->booked_slots)->toBe(2);
    expect(Booking::query()->first()?->payment_status)->toBe('pending');
    $this->assertDatabaseHas('mpesa_transactions', [
        'checkout_request_id' => 'ws_CO_123',
        'amount' => 3000,
        'phone' => '254700000001',
        'status' => 'pending',
    ]);
});

test('booking store rejects an invalid mpesa phone number', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $this->postJson("/events/{$event->id}/bookings", validPayload([
        'payment_method' => 'mpesa',
        'mpesa_phone' => '12345',
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['mpesa_phone']);
});

test('booking store rejects card payments', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $this->postJson("/events/{$event->id}/bookings", validPayload([
        'payment_method' => 'card',
        'mpesa_phone' => '0700000001',
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['payment_method']);
});

test('stk callback with a valid secret marks the booking paid', function () {
    fakeSuccessfulStkPush();

    $event = Event::factory()->create(['price' => 1500, 'capacity' => 10, 'booked_slots' => 0, 'status' => 'published']);

    $this->postJson("/events/{$event->id}/bookings", validPayload([
        'payment_method' => 'mpesa',
        'mpesa_phone' => '+254700000001',
    ]))->assertCreated();

    $this->postJson('/webhooks/mpesa/stk/test-callback-secret', stkCallbackPayload('ws_CO_123'))
        ->assertOk()
        ->assertJson(['ResultCode' => 0]);

    expect(Booking::query()->first()?->payment_status)->toBe('paid');
    expect(Booking::query()->first()?->payment_reference)->toBe('NLJ7RT61SV');
    expect(MpesaTransaction::query()->first()?->status)->toBe('completed');
    expect($event->fresh()->booked_slots)->toBe(1);
});

test('stk callback with the wrong secret is not found', function () {
    $this->postJson('/webhooks/mpesa/stk/wrong-secret', stkCallbackPayload('ws_CO_123'))
        ->assertNotFound();
});

test('stk callback with an amount mismatch does not mark the booking paid', function () {
    fakeSuccessfulStkPush();

    $event = Event::factory()->create(['price' => 1500, 'capacity' => 10, 'booked_slots' => 0, 'status' => 'published']);

    $this->postJson("/events/{$event->id}/bookings", validPayload([
        'payment_method' => 'mpesa',
        'mpesa_phone' => '0700000001',
    ]))->assertCreated();

    $this->postJson('/webhooks/mpesa/stk/test-callback-secret', stkCallbackPayload('ws_CO_123', amount: 1))
        ->assertOk();

    expect(Booking::query()->first()?->payment_status)->toBe('failed');
    expect($event->fresh()->booked_slots)->toBe(0);
    expect(MpesaTransaction::query()->first()?->status)->toBe('failed');
});

test('stk callback is idempotent for the same checkout request', function () {
    fakeSuccessfulStkPush();

    $event = Event::factory()->create(['price' => 1500, 'capacity' => 5, 'booked_slots' => 0, 'status' => 'published']);

    $this->postJson("/events/{$event->id}/bookings", validPayload([
        'payment_method' => 'mpesa',
        'mpesa_phone' => '0700000001',
    ]))->assertCreated();

    $this->postJson('/webhooks/mpesa/stk/test-callback-secret', stkCallbackPayload('ws_CO_123'))->assertOk();
    $this->postJson('/webhooks/mpesa/stk/test-callback-secret', stkCallbackPayload('ws_CO_123'))->assertOk();

    expect(Booking::query()->count())->toBe(1);
    expect(Booking::query()->first()?->payment_status)->toBe('paid');
    expect($event->fresh()->booked_slots)->toBe(1);
});

test('stk push failure marks the booking failed and releases reserved slots', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'test-token',
            'expires_in' => '3599',
        ]),
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
            'errorMessage' => 'Bad Request',
        ], 400),
    ]);

    $event = Event::factory()->create(['price' => 1500, 'capacity' => 10, 'booked_slots' => 0, 'status' => 'published']);

    // With async dispatch (sync queue in tests), the HTTP layer returns 201 and the
    // job runs inline. A Daraja 400 makes the job fail the transaction and release slots.
    $this->postJson("/events/{$event->id}/bookings", validPayload([
        'payment_method' => 'mpesa',
        'mpesa_phone' => '0700000001',
    ]))->assertCreated();

    expect(Booking::query()->first()?->payment_status)->toBe('failed');
    expect($event->fresh()->booked_slots)->toBe(0);
});

test('booking store does not create a booking when mpesa is not configured', function () {
    config(['mpesa.consumer_key' => '']);

    $event = Event::factory()->create(['status' => 'published', 'capacity' => 5, 'booked_slots' => 0]);

    // isConfigured() fails before any dispatch — no booking is created.
    $this->postJson("/events/{$event->id}/bookings", validPayload())
        ->assertStatus(502);

    expect(Booking::query()->count())->toBe(0);
    expect($event->fresh()->booked_slots)->toBe(0);
});

test('stk callback with an empty configured secret is not found', function () {
    config(['mpesa.callback_secret' => '']);

    $this->postJson('/webhooks/mpesa/stk/test-callback-secret', stkCallbackPayload('ws_CO_123'))
        ->assertNotFound();
});

test('payment status queries daraja after the grace period', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'test-token',
            'expires_in' => '3599',
        ]),
        'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query' => Http::response([
            'ResponseCode' => '0',
            'ResultCode' => '0',
            'ResultDesc' => 'The service request is processed successfully.',
        ]),
    ]);

    $booking = Booking::factory()->create(['payment_status' => 'pending']);
    $transaction = MpesaTransaction::factory()->create([
        'payable_id' => $booking->id,
        'amount' => (int) round((float) $booking->total_price),
        'checkout_request_id' => 'ws_CO_STATUS',
        'created_at' => now()->subMinutes(2),
    ]);

    $this->getJson("/payments/{$transaction->uuid}")
        ->assertOk()
        ->assertJson([
            'status' => 'completed',
            'payable' => [
                'payment_status' => 'paid',
            ],
        ]);
});

test('payment status endpoint returns the current transaction status', function () {
    $booking = Booking::factory()->create(['payment_status' => 'pending']);
    $transaction = MpesaTransaction::factory()->create([
        'payable_id' => $booking->id,
        'status' => 'pending',
        'created_at' => now(),
    ]);

    $this->getJson("/payments/{$transaction->uuid}")
        ->assertOk()
        ->assertJson([
            'payment_id' => $transaction->uuid,
            'status' => 'pending',
            'payable' => [
                'type' => 'Booking',
                'payment_status' => 'pending',
                'reference' => $booking->booking_reference,
            ],
        ]);
});
