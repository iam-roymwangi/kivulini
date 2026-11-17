<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\EventPriceTier;
use App\Models\MpesaTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function tieredEventPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Tiered Concert Trip',
        'type' => 'food_music',
        'summary' => 'A night of music.',
        'description' => '<p>Live music.</p>',
        'location' => 'Naivasha, Kenya',
        'start_date' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date' => now()->addWeeks(2)->format('Y-m-d\TH:i'),
        'capacity' => 50,
        'status' => 'published',
        'price_tiers' => [
            ['name' => 'Regular', 'price' => 2500, 'description' => ''],
            ['name' => 'VIP', 'price' => 5000, 'description' => 'Front row seats'],
            ['name' => 'VVIP', 'price' => 9000, 'description' => 'Backstage access'],
        ],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function tierBookingPayload(array $overrides = []): array
{
    return array_merge([
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 2,
        'responses' => [],
        'consent' => ['agreed' => true, 'signer_name' => 'Jane Doe'],
        'payment_method' => 'mpesa',
        'mpesa_phone' => '0700000001',
    ], $overrides);
}

function fakeTierStkPush(): void
{
    Http::preventStrayRequests();

    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'test-token', 'expires_in' => '3599']),
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
            'MerchantRequestID' => 'mr-123',
            'CheckoutRequestID' => 'ws_CO_TIER',
            'ResponseCode' => '0',
        ]),
    ]);
}

test('admin can create an event with custom price tiers', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.events.store'), tieredEventPayload())
        ->assertRedirect(route('admin.events.index'));

    $event = Event::where('title', 'Tiered Concert Trip')->firstOrFail();

    expect($event->priceTiers->pluck('name')->all())->toBe(['Regular', 'VIP', 'VVIP'])
        ->and($event->priceTiers->pluck('price')->all())->toBe(['2500.00', '5000.00', '9000.00'])
        ->and($event->price)->toBe('2500.00');
});

test('admin event without tiers still requires a price', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.events.store'), tieredEventPayload(['price_tiers' => []]))
        ->assertSessionHasErrors('price');
});

test('admin cannot create duplicate or unpriced tiers', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.events.store'), tieredEventPayload([
        'price_tiers' => [
            ['name' => 'VIP', 'price' => 5000],
            ['name' => 'vip', 'price' => 6000],
            ['name' => 'Regular', 'price' => ''],
        ],
    ]))->assertSessionHasErrors(['price_tiers.0.name', 'price_tiers.1.name', 'price_tiers.2.price']);

    expect(EventPriceTier::count())->toBe(0);
});

test('updating an event syncs its price tiers', function () {
    $this->actingAs(User::factory()->create());

    $event = Event::factory()->create(['price' => 2500]);
    $regular = EventPriceTier::factory()->for($event)->create(['name' => 'Regular', 'price' => 2500, 'sort_order' => 0]);
    $vip = EventPriceTier::factory()->for($event)->create(['name' => 'VIP', 'price' => 5000, 'sort_order' => 1]);
    $otherEventTier = EventPriceTier::factory()->create(['name' => 'Other', 'price' => 100]);

    $this->put(route('admin.events.update', $event), tieredEventPayload([
        'title' => $event->title,
        'price_tiers' => [
            ['id' => $regular->id, 'name' => 'Early Bird', 'price' => 2000],
            ['name' => 'VVIP', 'price' => 12000],
            ['id' => $otherEventTier->id, 'name' => 'Hijack', 'price' => 1],
        ],
    ]))->assertRedirect(route('admin.events.edit', $event));

    expect($event->fresh()->priceTiers->pluck('name')->all())->toBe(['Early Bird', 'VVIP', 'Hijack'])
        ->and($regular->fresh()->price)->toBe('2000.00')
        ->and($vip->fresh())->toBeNull()
        ->and($otherEventTier->fresh()->name)->toBe('Other')
        ->and($event->fresh()->price)->toBe('1.00');
});

test('booking a tier charges the tier price and records the tier', function () {
    fakeTierStkPush();

    $event = Event::factory()->create(['price' => 2500, 'capacity' => 10, 'booked_slots' => 0]);
    EventPriceTier::factory()->for($event)->create(['name' => 'Regular', 'price' => 2500]);
    $vip = EventPriceTier::factory()->for($event)->create(['name' => 'VIP', 'price' => 5000]);

    $this->postJson(route('bookings.store', $event), tierBookingPayload(['event_price_tier_id' => $vip->id]))
        ->assertCreated();

    $booking = Booking::sole();

    expect($booking->event_price_tier_id)->toBe($vip->id)
        ->and($booking->tier_name)->toBe('VIP')
        ->and($booking->unit_price)->toBe('5000.00')
        ->and($booking->total_price)->toBe('10000.00')
        ->and(MpesaTransaction::sole()->amount)->toBe(10000);
});

test('booking a tiered event requires a tier from that event', function (?string $tier) {
    $event = Event::factory()->create(['capacity' => 10, 'booked_slots' => 0]);
    EventPriceTier::factory()->for($event)->create();
    $foreignTier = EventPriceTier::factory()->create();

    $payload = tierBookingPayload(['event_price_tier_id' => $tier === 'foreign' ? $foreignTier->id : null]);

    $this->postJson(route('bookings.store', $event), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('event_price_tier_id');

    expect(Booking::count())->toBe(0);
})->with([
    'missing tier' => [null],
    'tier from another event' => ['foreign'],
]);

test('event page exposes price tiers for booking', function () {
    $event = Event::factory()->create();
    EventPriceTier::factory()->for($event)->create(['name' => 'VIP', 'price' => 5000, 'description' => 'Front row']);

    $this->get(route('events.show', $event->slug))
        ->assertInertia(fn ($page) => $page
            ->where('event.data.price_tiers.0.name', 'VIP')
            ->where('event.data.price_tiers.0.price', '5000.00')
            ->where('event.data.price_tiers.0.description', 'Front row'));
});

test('pass shows the ticket tier', function () {
    $event = Event::factory()->create();

    $booking = Booking::create([
        'event_id' => $event->id,
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 1,
        'tier_name' => 'VVIP',
        'unit_price' => '9000.00',
        'total_price' => '9000.00',
        'payment_status' => 'paid',
    ]);

    expect(view('bookings.pass-pdf', ['booking' => $booking])->render())->toContain('VVIP');
});
