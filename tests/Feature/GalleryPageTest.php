<?php

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gallery page returns 15 event media items per page ordered by featured status', function () {
    $event = Event::factory()->create();

    // Create 20 media items, only 1 featured
    for ($i = 1; $i <= 20; $i++) {
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
        ->has('pastEventsMedia.data', 15)
        ->where('pastEventsMedia.meta.per_page', 15)
        ->where('pastEventsMedia.meta.total', 20)
        ->where('pastEventsMedia.meta.last_page', 2)
    );

    $page2Response = $this->get(route('events.gallery', ['page' => 2]));

    $page2Response->assertOk();
    $page2Response->assertInertia(fn ($page) => $page
        ->component('events/Gallery')
        ->has('pastEventsMedia.data', 5)
        ->where('pastEventsMedia.meta.current_page', 2)
    );
});
