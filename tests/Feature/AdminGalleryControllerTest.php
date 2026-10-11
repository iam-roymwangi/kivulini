<?php

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ── Update — event reassignment ───────────────────────────────────────────────

test('admin can reassign a gallery image to another event', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $media = EventMedia::factory()->create();
    $newEvent = Event::factory()->create();

    $this->actingAs($user)
        ->post("/admin/gallery/{$media->id}", [
            'event_id' => $newEvent->id,
        ])
        ->assertRedirect('/admin/gallery');

    expect($media->fresh()->event_id)->toBe($newEvent->id);
});

test('admin can crop and replace a gallery image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('events/placeholder.webp', 'dummy');

    $user = User::factory()->create();
    $event = Event::factory()->create();
    $media = EventMedia::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'events/placeholder.webp',
    ]);

    $newImage = UploadedFile::fake()->image('cropped.webp', 400, 300);

    $this->actingAs($user)
        ->post("/admin/gallery/{$media->id}", [
            'event_id' => $event->id,
            'image' => $newImage,
        ])
        ->assertRedirect('/admin/gallery');

    // Old record replaced with a new one
    expect(EventMedia::find($media->id))->toBeNull();
    expect(EventMedia::where('event_id', $event->id)->exists())->toBeTrue();
    Storage::disk('public')->assertMissing('events/placeholder.webp');
});

test('update requires a valid event_id', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $media = EventMedia::factory()->create();

    $this->actingAs($user)
        ->post("/admin/gallery/{$media->id}", [
            'event_id' => 99999,
        ])
        ->assertSessionHasErrors('event_id');
});

test('update requires authentication', function () {
    $media = EventMedia::factory()->create();

    $this->post("/admin/gallery/{$media->id}", ['event_id' => $media->event_id])
        ->assertRedirect('/login');
});
