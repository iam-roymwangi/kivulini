<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\EventQuestion;
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

test('admin event description keeps formatting but strips unsafe markup', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.events.store'), [
        'title' => 'Formatted Tour',
        'type' => 'hiking',
        'summary' => 'A formatted summary.',
        'description' => '<h2>Highlights</h2><p><strong>Summit</strong> at dawn</p><ul><li>Water</li></ul><script>alert(1)</script>',
        'location' => 'Nanyuki, Kenya',
        'start_date' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date' => now()->addWeeks(2)->format('Y-m-d\TH:i'),
        'price' => 5000,
        'capacity' => 20,
        'status' => 'published',
    ])->assertRedirect(route('admin.events.index'));

    $this->assertDatabaseHas('events', [
        'title' => 'Formatted Tour',
        'description' => '<h2>Highlights</h2><p><strong>Summit</strong> at dawn</p><ul><li>Water</li></ul>',
    ]);
});

test('admin cannot add the same booking question twice', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.events.store'), [
        'title' => 'Duplicate Question Tour',
        'type' => 'hiking',
        'summary' => 'Summary.',
        'description' => 'Description.',
        'location' => 'Nairobi',
        'start_date' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date' => now()->addWeeks(2)->format('Y-m-d\TH:i'),
        'price' => 1000,
        'capacity' => 10,
        'status' => 'published',
        'questions' => [
            ['question_text' => 'Dietary preference', 'type' => 'text', 'is_required' => '1'],
            ['question_text' => 'dietary preference', 'type' => 'text', 'is_required' => '1'],
        ],
    ])->assertSessionHasErrors(['questions.0.question_text', 'questions.1.question_text']);

    expect(EventQuestion::count())->toBe(0);
});

test('duplicate booking questions are merged without losing answers', function () {
    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();

    $kept = EventQuestion::create(['event_id' => $event->id, 'question_text' => 'T-shirt size', 'type' => 'text']);
    $duplicate = EventQuestion::create(['event_id' => $event->id, 'question_text' => ' t-shirt SIZE ', 'type' => 'text']);
    $unrelated = EventQuestion::create(['event_id' => $otherEvent->id, 'question_text' => 'T-shirt size', 'type' => 'text']);

    $answeredBoth = Booking::factory()->create(['event_id' => $event->id]);
    $answeredDuplicateOnly = Booking::factory()->create(['event_id' => $event->id]);

    $answeredBoth->responses()->create(['event_question_id' => $kept->id, 'answer' => 'M']);
    $answeredBoth->responses()->create(['event_question_id' => $duplicate->id, 'answer' => 'M']);
    $answeredDuplicateOnly->responses()->create(['event_question_id' => $duplicate->id, 'answer' => 'L']);

    (require database_path('migrations/2026_11_17_084833_remove_duplicate_event_questions.php'))->up();

    expect(EventQuestion::pluck('id')->all())->toBe([$kept->id, $unrelated->id])
        ->and($answeredBoth->responses()->pluck('event_question_id')->all())->toBe([$kept->id])
        ->and($answeredDuplicateOnly->responses()->sole()->only(['event_question_id', 'answer']))
        ->toBe(['event_question_id' => $kept->id, 'answer' => 'L']);
});
