<?php

use App\Models\Event;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ── Store ─────────────────────────────────────────────────────────────────────

test('visitor can submit a review for a completed event', function () {
    Storage::fake('public');

    $event = Event::factory()->completed()->create();

    $this->post("/events/{$event->id}/reviews", [
        'reviewer_name' => 'Jane Doe',
        'reviewer_email' => 'jane@example.com',
        'rating' => 5,
        'body' => 'Absolutely amazing trip through the national park!',
    ])->assertRedirect();

    expect(Review::where('event_id', $event->id)->count())->toBe(1);

    $review = Review::first();
    expect($review->reviewer_name)->toBe('Jane Doe');
    expect($review->rating)->toBe(5);
    expect($review->is_approved)->toBeFalse();
});

test('review with optional image stores the file', function () {
    Storage::fake('public');

    $event = Event::factory()->completed()->create();
    $image = UploadedFile::fake()->image('photo.webp', 800, 600);

    $this->post("/events/{$event->id}/reviews", [
        'reviewer_name' => 'John K.',
        'rating' => 4,
        'body' => 'Great experience on the hike, very well organised.',
        'image' => $image,
    ])->assertRedirect();

    $review = Review::first();
    expect($review->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($review->image_path);
});

test('review cannot be submitted for a non-completed event', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $this->post("/events/{$event->id}/reviews", [
        'reviewer_name' => 'Sam',
        'rating' => 5,
        'body' => 'Great event, would highly recommend it!',
    ])->assertForbidden();
});

test('review requires name, rating, and body', function () {
    $event = Event::factory()->completed()->create();

    $this->post("/events/{$event->id}/reviews", [])
        ->assertSessionHasErrors(['reviewer_name', 'rating', 'body']);
});

test('rating must be between 1 and 5', function () {
    $event = Event::factory()->completed()->create();

    $this->post("/events/{$event->id}/reviews", [
        'reviewer_name' => 'Test',
        'rating' => 6,
        'body' => 'This is a test review body that is long enough.',
    ])->assertSessionHasErrors('rating');
});

test('event show page passes approved reviews to the frontend', function () {
    $event = Event::factory()->completed()->create();
    Review::factory()->approved()->count(3)->create(['event_id' => $event->id]);
    Review::factory()->count(2)->create(['event_id' => $event->id]); // unapproved

    $this->withoutVite()
        ->get("/events/{$event->slug}")
        ->assertInertia(fn ($page) => $page
            ->component('events/Show')
            ->has('event.data.reviews', 3),
        );
});
