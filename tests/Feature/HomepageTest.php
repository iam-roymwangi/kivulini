<?php

use App\Models\Event;
use App\Models\EventMedia;

test('homepage shows upcoming event previews and latest completed trip images', function () {
    $publishedEvent = Event::factory()->create([
        'start_date' => now()->addWeek(),
    ]);

    EventMedia::factory()->create([
        'event_id' => $publishedEvent->id,
        'file_path' => 'events/upcoming-cover.webp',
        'is_featured' => false,
        'sort_order' => 1,
    ]);

    $completedEvent = Event::factory()->completed()->create();
    $olderMedia = EventMedia::factory()->create([
        'event_id' => $completedEvent->id,
        'file_path' => 'events/older.webp',
        'created_at' => now()->subDay(),
    ]);
    $latestMedia = EventMedia::factory()->create([
        'event_id' => $completedEvent->id,
        'file_path' => 'events/latest.webp',
        'created_at' => now(),
    ]);

    $draftEvent = Event::factory()->draft()->create();
    EventMedia::factory()->create([
        'event_id' => $draftEvent->id,
        'file_path' => 'events/draft.webp',
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('events/Home')
        ->has('featuredEvents.data', 1)
        ->where('featuredEvents.data.0.cover_image_url', '/storage/events/upcoming-cover.webp')
        ->has('featuredGallery.data', 2)
        ->where('featuredGallery.data.0.id', $latestMedia->id)
        ->where('featuredGallery.data.0.event.id', $completedEvent->id)
        ->where('featuredGallery.data.1.id', $olderMedia->id)
    );
});
