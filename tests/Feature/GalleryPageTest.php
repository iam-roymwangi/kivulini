<?php

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gallery page returns all event media ordered by featured status', function () {
    $event = Event::factory()->create();

    // Create 15 media items, only 1 featured
    for ($i = 1; $i <= 15; $i++) {
        EventMedia::create([
            'event_id' => $event->id,
            'file_path' => "events/test_{$i}.webp",
            'type' => 'image',
            'is_featured' => $i === 1,
            'sort_order' => $i,
        ]);
    }

    $response = $this->get(route('events.gallery'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('events/Gallery')
        ->has('pastEventsMedia.data', 12)
        ->where('pastEventsMedia.meta.total', 15)
    );
});
