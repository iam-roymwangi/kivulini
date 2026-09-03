<?php

use App\Mail\ContactMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => Mail::fake());

test('contact form sends email and returns 200', function () {
    $response = $this->postJson(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Trip inquiry',
        'message' => 'I would like to know more about the Mt. Kenya hike.',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Message sent successfully.']);

    Mail::assertSent(ContactMail::class, function (ContactMail $mail) {
        return $mail->senderName === 'Jane Doe'
            && $mail->senderEmail === 'jane@example.com'
            && $mail->contactSubject === 'Trip inquiry'
            && str_contains($mail->body, 'Mt. Kenya');
    });
});

test('contact mail renders the submitted subject', function () {
    $html = (new ContactMail(
        senderName: 'Jane Doe',
        senderEmail: 'jane@example.com',
        contactSubject: 'Trip inquiry',
        body: 'I would like to know more about the Mt. Kenya hike.',
    ))->render();

    expect($html)
        ->toContain('Trip inquiry')
        ->toContain('New Message from Jane Doe');
});

test('contact form returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('contact.store'), [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'message']);

    Mail::assertNothingSent();
});

test('contact form rate limits after 3 submissions', function () {
    $payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'Test message',
    ];

    foreach (range(1, 3) as $i) {
        $this->postJson(route('contact.store'), $payload)->assertStatus(200);
    }

    $this->postJson(route('contact.store'), $payload)->assertStatus(429);
});
