<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create event with past start date if status is completed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('admin.events.store'), [
        'title' => 'Past Completed Tour',
        'type' => 'cultural_heritage',
        'summary' => 'A past completed event summary.',
        'description' => 'Detailed description of completed trip.',
        'location' => 'Mombasa, Kenya',
        'start_date' => '2025-01-10T09:00',
        'end_date' => '2025-01-15T18:00',
        'price' => 5000,
        'capacity' => 20,
        'status' => 'completed',
    ]);

    $response->assertRedirect(route('admin.events.index'));

    $this->assertDatabaseHas('events', [
        'title' => 'Past Completed Tour',
        'status' => 'completed',
    ]);
});

test('admin cannot create event with past start date if status is published', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('admin.events.store'), [
        'title' => 'Future Tour',
        'type' => 'cultural_heritage',
        'summary' => 'A future event summary.',
        'description' => 'Detailed description of trip.',
        'location' => 'Mombasa, Kenya',
        'start_date' => '2020-01-10T09:00',
        'end_date' => '2020-01-15T18:00',
        'price' => 5000,
        'capacity' => 20,
        'status' => 'published',
    ]);

    $response->assertSessionHasErrors(['start_date']);
});
