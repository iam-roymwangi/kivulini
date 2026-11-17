<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view admin bookings', function () {
    $booking = Booking::factory()->create();

    $this->get(route('admin.bookings.index'))->assertRedirect(route('login'));
    $this->get(route('admin.bookings.show', $booking))->assertRedirect(route('login'));
    $this->patch(route('admin.bookings.follow-up', $booking), ['follow_up_status' => 'contacted'])
        ->assertRedirect(route('login'));

    expect($booking->fresh()->follow_up_status)->toBe('new');
});

test('admin sees bookings paginated newest first with status counts', function () {
    $this->actingAs(User::factory()->create());

    Booking::factory()->count(21)->create(['payment_status' => 'paid', 'created_at' => now()->subDay()]);
    $latest = Booking::factory()->create(['payment_status' => 'pending']);

    $this->get(route('admin.bookings.index'))
        ->assertInertia(fn ($page) => $page
            ->component('admin/bookings/Index')
            ->has('bookings.data', 20)
            ->where('bookings.data.0.id', $latest->id)
            ->where('bookings.total', 22)
            ->where('bookings.last_page', 2)
            ->where('counts.all', 22)
            ->where('counts.paid', 21)
            ->where('counts.pending', 1));

    $this->get(route('admin.bookings.index', ['page' => 2]))
        ->assertInertia(fn ($page) => $page->has('bookings.data', 2));
});

test('admin can filter bookings', function (array $query, string $expected) {
    $this->actingAs(User::factory()->create());

    $safari = Event::factory()->create();

    Booking::factory()->create(['booking_reference' => 'BK-PAID0001', 'payment_status' => 'paid']);
    Booking::factory()->create(['booking_reference' => 'BK-FAIL0001', 'payment_status' => 'failed', 'follow_up_status' => 'follow_up']);
    Booking::factory()->create(['booking_reference' => 'BK-SAFARI01', 'event_id' => $safari->id, 'contact_name' => 'Achieng Otieno']);

    $query = array_map(fn ($value) => $value === ':safari' ? $safari->id : $value, $query);

    $this->get(route('admin.bookings.index', $query))
        ->assertInertia(fn ($page) => $page
            ->has('bookings.data', 1)
            ->where('bookings.data.0.booking_reference', $expected));
})->with([
    'payment status' => [['status' => 'failed'], 'BK-FAIL0001'],
    'follow-up status' => [['follow_up' => 'follow_up'], 'BK-FAIL0001'],
    'event' => [['event' => ':safari'], 'BK-SAFARI01'],
    'search by name' => [['search' => 'achieng'], 'BK-SAFARI01'],
    'search by reference' => [['search' => 'PAID0001'], 'BK-PAID0001'],
]);

test('admin can view a booking', function () {
    $this->actingAs(User::factory()->create());

    $booking = Booking::factory()->create(['tier_name' => 'VIP']);

    $this->get(route('admin.bookings.show', $booking))
        ->assertInertia(fn ($page) => $page
            ->component('admin/bookings/Show')
            ->where('booking.id', $booking->id)
            ->where('booking.tier_name', 'VIP')
            ->where('booking.event.id', $booking->event_id)
            ->has('followUpStatuses', 4));
});

test('admin can record a follow-up', function () {
    $this->actingAs(User::factory()->create());
    $this->freezeTime();

    $booking = Booking::factory()->create();

    $this->from(route('admin.bookings.show', $booking))
        ->patch(route('admin.bookings.follow-up', $booking), [
            'follow_up_status' => 'contacted',
            'follow_up_notes' => 'Called, will pay on Friday.',
        ])
        ->assertRedirect(route('admin.bookings.show', $booking));

    $booking->refresh();

    expect($booking->follow_up_status)->toBe('contacted')
        ->and($booking->follow_up_notes)->toBe('Called, will pay on Friday.')
        ->and($booking->followed_up_at?->toDateTimeString())->toBe(now()->toDateTimeString());
});

test('resetting a booking to not contacted clears the follow-up time', function () {
    $this->actingAs(User::factory()->create());

    $booking = Booking::factory()->create(['follow_up_status' => 'contacted', 'followed_up_at' => now()]);

    $this->patch(route('admin.bookings.follow-up', $booking), ['follow_up_status' => 'new']);

    expect($booking->fresh()->followed_up_at)->toBeNull();
});

test('follow-up status must be a known value', function () {
    $this->actingAs(User::factory()->create());

    $booking = Booking::factory()->create();

    $this->patch(route('admin.bookings.follow-up', $booking), ['follow_up_status' => 'ignored'])
        ->assertSessionHasErrors('follow_up_status');

    expect($booking->fresh()->follow_up_status)->toBe('new');
});
