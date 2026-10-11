<?php

use App\Models\Event;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home page renders with past events and reviews props', function () {
    Event::factory()->completed()->count(3)->create();
    Event::factory()->count(2)->create(['status' => 'published']);
    Review::factory()->approved()->count(4)->create();
    Review::factory()->count(2)->create(); // unapproved — should not appear

    $this->withoutVite()
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('events/Home')
            ->has('pastEvents.data', 3)
            ->has('reviews.data', 4)
            ->where('reviews.total', 4)
        );
});

test('home page only shows approved reviews', function () {
    Review::factory()->approved()->count(2)->create();
    Review::factory()->count(5)->create(); // unapproved

    $this->withoutVite()
        ->get('/')
        ->assertInertia(fn ($page) => $page
            ->where('reviews.total', 2)
        );
});

test('home page only shows completed events in past events', function () {
    Event::factory()->completed()->count(4)->create();
    Event::factory()->count(3)->create(['status' => 'published']);
    Event::factory()->count(2)->create(['status' => 'draft']);

    $this->withoutVite()
        ->get('/')
        ->assertInertia(fn ($page) => $page
            ->where('pastEvents.meta.total', 4)
        );
});

test('reviews paginate with reviews_page query param', function () {
    Review::factory()->approved()->count(8)->create();

    $this->withoutVite()
        ->get('/?reviews_page=2')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reviews.current_page', 2)
            ->where('reviews.last_page', 2)
        );
});
