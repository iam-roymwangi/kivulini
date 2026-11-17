<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'follow_up_status' => ['required', Rule::in(Booking::FOLLOW_UP_STATUSES)],
            'follow_up_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
