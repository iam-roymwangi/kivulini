<?php

use App\Http\Requests\StoreBookingRequest;
use Illuminate\Support\Facades\Validator;

/**
 * Helper to validate data against StoreBookingRequest rules.
 *
 * @param  array<string, mixed>  $data
 */
function validateBookingRequest(array $data): Illuminate\Contracts\Validation\Validator
{
    $request = new StoreBookingRequest;

    return Validator::make($data, $request->rules());
}

/**
 * Returns a valid complete booking payload.
 *
 * @return array<string, mixed>
 */
function validBookingPayload(): array
{
    return [
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '+254700000001',
        'quantity' => 2,
        'responses' => [],
        'consent' => [
            'agreed' => true,
            'signer_name' => 'Jane Doe',
        ],
    ];
}

test('authorize returns true for public bookings', function () {
    $request = new StoreBookingRequest;

    expect($request->authorize())->toBeTrue();
});

test('valid payload passes validation', function () {
    $validator = validateBookingRequest(validBookingPayload());

    expect($validator->fails())->toBeFalse();
});

test('responses are optional and can be empty', function () {
    $data = validBookingPayload();
    unset($data['responses']);

    $validator = validateBookingRequest($data);

    expect($validator->fails())->toBeFalse();
});

test('contact_name is required', function () {
    $data = validBookingPayload();
    unset($data['contact_name']);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('contact_name'))->toBeTrue();
});

test('contact_email must be a valid email', function () {
    $data = array_merge(validBookingPayload(), ['contact_email' => 'not-an-email']);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('contact_email'))->toBeTrue();
});

test('contact_phone is required', function () {
    $data = validBookingPayload();
    unset($data['contact_phone']);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('contact_phone'))->toBeTrue();
});

test('quantity must be an integer of at least 1', function () {
    $data = array_merge(validBookingPayload(), ['quantity' => 0]);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('quantity'))->toBeTrue();
});

test('quantity is required', function () {
    $data = validBookingPayload();
    unset($data['quantity']);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('quantity'))->toBeTrue();
});

test('consent.agreed must be accepted', function () {
    $data = validBookingPayload();
    $data['consent']['agreed'] = false;

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('consent.agreed'))->toBeTrue();
});

test('consent.signer_name is required', function () {
    $data = validBookingPayload();
    unset($data['consent']['signer_name']);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('consent.signer_name'))->toBeTrue();
});

test('response answer is nullable', function () {
    $data = validBookingPayload();
    // event_question_id 999 won't exist in DB — skip the exists check in unit context
    // We test the nullable rule specifically
    $data['responses'] = [
        ['event_question_id' => 1, 'answer' => null],
    ];

    // The exists rule will fail in unit context (no DB record), but nullable on answer should not add an error for answer
    $validator = validateBookingRequest($data);
    $errors = $validator->errors()->toArray();

    expect(array_key_exists('responses.0.answer', $errors))->toBeFalse();
});

test('contact_name max 255 characters enforced', function () {
    $data = array_merge(validBookingPayload(), ['contact_name' => str_repeat('a', 256)]);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('contact_name'))->toBeTrue();
});

test('contact_phone max 30 characters enforced', function () {
    $data = array_merge(validBookingPayload(), ['contact_phone' => str_repeat('1', 31)]);

    $validator = validateBookingRequest($data);

    expect($validator->errors()->has('contact_phone'))->toBeTrue();
});
