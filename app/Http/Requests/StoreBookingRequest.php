<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Public booking — no auth required.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1'],
            'responses' => ['array'],
            'responses.*.event_question_id' => ['required', 'integer', 'exists:event_questions,id'],
            'responses.*.answer' => ['nullable', 'string'],
            'consent.agreed' => ['required', 'accepted'],
            'consent.signer_name' => ['required', 'string', 'max:255'],
        ];
    }
}
