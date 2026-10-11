<?php

use App\Models\Event;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ── Index ─────────────────────────────────────────────────────────────────────

test('admin reviews index requires authentication', function () {
    $this->get('/admin/reviews')->assertRedirect('/login');
});

test('admin reviews index lists all reviews', function () {
    $user = User::factory()->create();
    Review::factory()->approved()->count(3)->create();
    Review::factory()->count(2)->create();

    $this->actingAs($user)
        ->withoutVite()
        ->get('/admin/reviews')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reviews/Index')
            ->where('counts.all', 5)
            ->where('counts.approved', 3)
            ->where('counts.pending', 2)
        );
});

test('admin reviews index filters by status', function () {
    $user = User::factory()->create();
    Review::factory()->approved()->count(3)->create();
    Review::factory()->count(2)->create();

    $this->actingAs($user)
        ->withoutVite()
        ->get('/admin/reviews?status=pending')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('reviews.data', 2)
        );
});

// ── Approve ───────────────────────────────────────────────────────────────────

test('admin can approve a pending review', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['is_approved' => false]);

    $this->actingAs($user)
        ->post("/admin/reviews/{$review->id}/approve")
        ->assertRedirect();

    expect($review->fresh()->is_approved)->toBeTrue();
});

// ── Unapprove ─────────────────────────────────────────────────────────────────

test('admin can unapprove an approved review', function () {
    $user = User::factory()->create();
    $review = Review::factory()->approved()->create();

    $this->actingAs($user)
        ->post("/admin/reviews/{$review->id}/unapprove")
        ->assertRedirect();

    expect($review->fresh()->is_approved)->toBeFalse();
});

// ── Destroy ───────────────────────────────────────────────────────────────────

test('admin can delete a review', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $review = Review::factory()->create();

    $this->actingAs($user)
        ->delete("/admin/reviews/{$review->id}")
        ->assertRedirect();

    expect(Review::find($review->id))->toBeNull();
});

test('admin deleting a review also deletes its image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('reviews/1/photo.webp', 'data');

    $user = User::factory()->create();
    $review = Review::factory()->create(['image_path' => 'reviews/1/photo.webp']);

    $this->actingAs($user)->delete("/admin/reviews/{$review->id}");

    Storage::disk('public')->assertMissing('reviews/1/photo.webp');
});
