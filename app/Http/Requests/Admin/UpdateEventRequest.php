<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cultural_heritage,wildlife_safari,food_music,road_trip,hiking,vacation'],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,completed,cancelled'],
            'liability_waiver_text' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp'],
            'delete_media_ids' => ['nullable', 'array'],
            'delete_media_ids.*' => ['integer', 'exists:event_media,id'],
            'featured_media_id' => ['nullable', 'integer', 'exists:event_media,id'],
            'questions' => ['nullable', 'array'],
            'questions.*.id' => ['nullable', 'integer', 'exists:event_questions,id'],
            'questions.*.question_text' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', 'in:text,textarea,select'],
            'questions.*.is_required' => ['boolean'],
            'questions.*.options' => ['nullable', 'array'],
            'delete_question_ids' => ['nullable', 'array'],
            'delete_question_ids.*' => ['integer', 'exists:event_questions,id'],
        ];
    }
}
