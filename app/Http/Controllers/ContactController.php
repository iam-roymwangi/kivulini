<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request): JsonResponse
    {
        $key = 'contact:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 3)) {
            return response()->json(
                ['message' => 'Too many messages sent. Please try again later.'],
                429
            );
        }

        RateLimiter::hit($key, 3600);

        $data = $request->validated();

        try {
            $recipient = config('mail.contact_address')
                ?: config('mail.from.address')
                ?: 'info@kivuliniadventures.com';

            Mail::to($recipient)->send(new ContactMail(
                senderName: $data['name'],
                senderEmail: $data['email'],
                contactSubject: $data['subject'] ?? '',
                body: $data['message'],
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact email: '.$e->getMessage(), [
                'data' => $data,
            ]);
        }

        return response()->json(['message' => 'Message sent successfully.'], 200);
    }
}
