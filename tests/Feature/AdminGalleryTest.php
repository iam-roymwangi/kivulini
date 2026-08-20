<?php

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('guests are redirected to the login page when accessing gallery admin', function () {
    $response = $this->get(route('admin.gallery.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view the gallery admin page', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $media = EventMedia::create([
        'event_id' => $event->id,
        'file_path' => 'events/test.jpg',
        'type' => 'image',
        'is_featured' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs($user);
    $response = $this->get(route('admin.gallery.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/gallery/Index')
        ->has('events')
        ->has('media.data')
    );
});

test('authenticated users can upload multiple images', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('admin.gallery.store'), [
        'event_id' => $event->id,
        'images' => [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.png'),
        ],
    ]);

    $response->assertRedirect(route('admin.gallery.index'));

    // Check they exist in the database (converted to WebP)
    $this->assertDatabaseCount('event_media', 2);

    $mediaItems = EventMedia::all();
    foreach ($mediaItems as $media) {
        expect($media->event_id)->toBe($event->id);
        expect($media->type)->toBe('image');
        expect(str_ends_with($media->file_path, '.webp'))->toBeTrue();
        Storage::disk('public')->assertExists($media->file_path);
    }
});

test('authenticated users can toggle image featured status', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $media = EventMedia::create([
        'event_id' => $event->id,
        'file_path' => 'events/test.webp',
        'type' => 'image',
        'is_featured' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('admin.gallery.toggle-featured', $media));

    $response->assertRedirect(route('admin.gallery.index'));
    expect($media->fresh()->is_featured)->toBeTrue();

    // Toggle again
    $this->post(route('admin.gallery.toggle-featured', $media));
    expect($media->fresh()->is_featured)->toBeFalse();
});

test('authenticated users can delete a gallery image', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $filePath = 'events/test.webp';
    Storage::disk('public')->put($filePath, 'fake content');

    $media = EventMedia::create([
        'event_id' => $event->id,
        'file_path' => $filePath,
        'type' => 'image',
        'is_featured' => false,
        'sort_order' => 1,
    ]);

    Storage::disk('public')->assertExists($filePath);

    $this->actingAs($user);
    $response = $this->delete(route('admin.gallery.destroy', $media));

    $response->assertRedirect(route('admin.gallery.index'));
    $this->assertDatabaseMissing('event_media', ['id' => $media->id]);
    Storage::disk('public')->assertMissing($filePath);
});
