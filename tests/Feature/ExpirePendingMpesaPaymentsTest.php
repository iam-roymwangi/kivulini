<?php

use App\Models\Booking;
use App\Models\Merchandise;
use App\Models\MerchandiseOrder;
use App\Models\MpesaTransaction;
use Illuminate\Support\Facades\Http;

test('expire command queries daraja and completes a successful pending payment', function () {
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

    $booking = Booking::factory()->create(['payment_status' => 'pending', 'quantity' => 1]);
    $booking->event->update(['booked_slots' => 1]);

    MpesaTransaction::factory()->create([
        'payable_id' => $booking->id,
        'amount' => (int) round((float) $booking->total_price),
        'checkout_request_id' => 'ws_CO_EXPIRE',
        'created_at' => now()->subMinutes(5),
    ]);

    $this->artisan('mpesa:expire-pending')->assertSuccessful();

    expect($booking->fresh()->payment_status)->toBe('paid');
});

test('expire command marks stale pending payments as failed and releases slots', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'test-token',
            'expires_in' => '3599',
        ]),
        'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query' => Http::response([
            'ResponseCode' => '0',
            'ResultCode' => '4999',
            'ResultDesc' => 'The transaction is being processed',
        ]),
    ]);

    $booking = Booking::factory()->create(['payment_status' => 'pending', 'quantity' => 2]);
    $booking->event->update(['booked_slots' => 2]);

    MpesaTransaction::factory()->create([
        'payable_id' => $booking->id,
        'checkout_request_id' => 'ws_CO_STALE',
        'created_at' => now()->subMinutes(20),
    ]);

    $this->artisan('mpesa:expire-pending')->assertSuccessful();

    expect($booking->fresh()->payment_status)->toBe('failed');
    expect($booking->event->fresh()->booked_slots)->toBe(0);
});

test('expire command restores reserved merch stock for a stale pending payment', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'test-token',
            'expires_in' => '3599',
        ]),
        'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query' => Http::response([
            'ResponseCode' => '0',
            'ResultCode' => '4999',
            'ResultDesc' => 'The transaction is being processed',
        ]),
    ]);

    $item = Merchandise::factory()->published()->create(['stock' => 3]);
    $order = MerchandiseOrder::factory()->create([
        'merchandise_id' => $item->id,
        'quantity' => 2,
        'payment_status' => 'pending',
    ]);
    $item->update(['stock' => 1]);

    MpesaTransaction::factory()->create([
        'payable_type' => MerchandiseOrder::class,
        'payable_id' => $order->id,
        'checkout_request_id' => 'ws_CO_MERCH_STALE',
        'created_at' => now()->subMinutes(20),
    ]);

    $this->artisan('mpesa:expire-pending')->assertSuccessful();

    expect($order->fresh()->payment_status)->toBe('failed');
    expect($item->fresh()->stock)->toBe(3);
});
