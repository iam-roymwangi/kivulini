<?php

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeEvent(array $overrides = []): Event
{
    return Event::create(array_merge([
        'title' => 'Test Event',
        'slug' => 'test-event',
        'type' => 'hiking',
        'summary' => 'A test event summary.',
        'description' => 'Full description.',
        'location' => 'Nairobi, Kenya',
        'start_date' => now()->addDays(7),
        'end_date' => now()->addDays(8),
        'price' => '1500.00',
        'capacity' => 10,
        'booked_slots' => 0,
        'status' => 'published',
        'liability_waiver_text' => 'I agree to the waiver.',
    ], $overrides));
}

function validPayload(array $overrides = []): array
{
    return array_merge([
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 1,
        'responses' => [],
        'consent' => [
            'agreed' => true,
            'signer_name' => 'Jane Doe',
        ],
    ], $overrides);
}

// --- store() ---

test('store creates booking and returns 201 with booking reference', function () {
    $event = makeEvent(['capacity' => 10, 'booked_slots' => 0]);

    $response = $this->postJson("/events/{$event->id}/bookings", validPayload(['quantity' => 3]));

    $response->assertStatus(201)
        ->assertJsonStructure(['booking_reference', 'booking_id']);

    $this->assertDatabaseHas('bookings', ['contact_email' => 'jane@example.com']);
    expect($event->fresh()->booked_slots)->toBe(3);
    expect($event->fresh()->available_slots)->toBe(7);
});

test('store returns 422 when event is sold out', function () {
    $event = makeEvent(['capacity' => 2, 'booked_slots' => 2]);

    $response = $this->postJson("/events/{$event->id}/bookings", validPayload());

    $response->assertStatus(422)
        ->assertJson(['message' => 'No slots remaining for this event.']);
});

test('store returns 422 when requested quantity exceeds available slots', function () {
    $event = makeEvent(['capacity' => 1, 'booked_slots' => 0]);

    $response = $this->postJson("/events/{$event->id}/bookings", validPayload(['quantity' => 5]));

    $response->assertStatus(422)
        ->assertJson(['message' => 'No slots remaining for this event.']);
});

test('store returns 422 validation error when required fields are missing', function () {
    $event = makeEvent();

    $response = $this->postJson("/events/{$event->id}/bookings", [
        'contact_name' => '',
        'quantity' => 1,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['contact_email', 'contact_phone']);
});

// --- downloadPass() ---

test('downloadPass returns PDF file with correct content-disposition header', function () {
    $event = makeEvent();

    $booking = Booking::create([
        'event_id' => $event->id,
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 1,
        'total_price' => '1500.00',
        'payment_status' => 'paid',
    ]);

    $response = $this->get("/bookings/{$booking->id}/pass");

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', "attachment; filename=pass-{$booking->booking_reference}.pdf");
});

test('downloadPass view contains required booking and event fields', function () {
    $event = makeEvent();

    $booking = Booking::create([
        'event_id' => $event->id,
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 1,
        'total_price' => '1500.00',
        'payment_status' => 'paid',
    ]);

    $html = view('bookings.pass-pdf', ['booking' => $booking])->render();

    expect($html)
        ->toContain($booking->booking_reference)
        ->toContain($event->title)
        ->toContain($event->start_date->format('D, M j Y'))
        ->toContain($event->location)
        ->toContain($booking->contact_name)
        ->toContain($booking->contact_email);
});

// --- cancel() ---

test('cancel updates payment_status to cancelled and releases booked slots if paid', function () {
    $event = makeEvent(['capacity' => 10, 'booked_slots' => 3]);

    $booking = Booking::create([
        'event_id' => $event->id,
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 2,
        'total_price' => '3000.00',
        'payment_status' => 'paid',
    ]);

    $response = $this->postJson("/bookings/{$booking->id}/cancel");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Booking cancelled successfully.']);

    expect($booking->fresh()->payment_status)->toBe('cancelled');
    expect($event->fresh()->booked_slots)->toBe(1);
});

test('cancel updates pending booking payment_status to cancelled without changing slots', function () {
    $event = makeEvent(['capacity' => 10, 'booked_slots' => 2]);

    $booking = Booking::create([
        'event_id' => $event->id,
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 2,
        'total_price' => '3000.00',
        'payment_status' => 'pending',
    ]);

    $response = $this->postJson("/bookings/{$booking->id}/cancel");

    $response->assertStatus(200);
    expect($booking->fresh()->payment_status)->toBe('cancelled');
    expect($event->fresh()->booked_slots)->toBe(2);
});

test('cancel returns 422 if booking is already cancelled', function () {
    $event = makeEvent();

    $booking = Booking::create([
        'event_id' => $event->id,
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 1,
        'total_price' => '1500.00',
        'payment_status' => 'cancelled',
    ]);

    $response = $this->postJson("/bookings/{$booking->id}/cancel");

    $response->assertStatus(422)
        ->assertJson(['message' => 'Booking is already cancelled.']);
});
