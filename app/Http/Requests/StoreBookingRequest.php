<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Rules\KenyanMpesaPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $event = $this->route('event');
        $event = $event instanceof Event ? $event : null;

        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1'],
            'event_price_tier_id' => [
                Rule::requiredIf(fn (): bool => $event?->priceTiers()->exists() ?? false),
                'nullable',
                'integer',
                Rule::exists('event_price_tiers', 'id')->where('event_id', $event?->id),
            ],
            'responses' => ['array'],
            'responses.*.event_question_id' => ['required', 'integer', 'exists:event_questions,id'],
            'responses.*.answer' => ['nullable', 'string'],
            'consent.agreed' => ['required', 'accepted'],
            'consent.signer_name' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:mpesa'],
            'mpesa_phone' => ['required', 'string', 'max:20', new KenyanMpesaPhone],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_price_tier_id.required' => 'Please choose a ticket type.',
            'event_price_tier_id.exists' => 'The selected ticket type is no longer available.',
        ];
    }
}
