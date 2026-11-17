<?php

namespace App\Http\Requests\Admin;

use App\Support\RichText;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Strip unsafe markup from the description and, when price tiers are
     * given, use the cheapest tier as the event's base ("from") price.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('description'))) {
            $this->merge(['description' => RichText::sanitize($this->input('description'))]);
        }

        $tierPrices = collect($this->input('price_tiers', []))
            ->pluck('price')
            ->filter(fn (mixed $price): bool => is_numeric($price));

        if ($tierPrices->isNotEmpty()) {
            $this->merge(['price' => $tierPrices->min()]);
        }
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
            'start_date' => array_values(array_filter([
                'required',
                'date',
                $this->input('status') === 'completed' ? null : 'after_or_equal:today',
            ])),
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,completed,cancelled'],
            'liability_waiver_text' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp'],
            'price_tiers' => ['nullable', 'array', 'max:10'],
            'price_tiers.*.id' => ['nullable', 'integer'],
            'price_tiers.*.name' => ['required', 'string', 'max:50', 'distinct:ignore_case'],
            'price_tiers.*.price' => ['required', 'numeric', 'min:1'],
            'price_tiers.*.description' => ['nullable', 'string', 'max:255'],
            'questions' => ['nullable', 'array'],
            'questions.*.question_text' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', 'in:text,textarea,select'],
            'questions.*.is_required' => ['boolean'],
            'questions.*.options' => ['nullable', 'array'],
        ];
    }
}
