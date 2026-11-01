<?php

namespace App\Http\Requests;

use App\Models\Merchandise;
use App\Rules\KenyanMpesaPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMerchandiseOrderRequest extends FormRequest
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
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['required', 'in:mpesa'],
            'mpesa_phone' => ['required', 'string', 'max:20', new KenyanMpesaPhone],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Merchandise $merchandise */
                $merchandise = $this->route('merchandise');

                $sizes = $merchandise->sizes ?? [];
                $colors = $merchandise->colors ?? [];

                if ($sizes !== [] && ! in_array($this->string('size')->toString(), $sizes, true)) {
                    $validator->errors()->add('size', 'Select a valid size for this item.');
                }

                if ($colors !== [] && ! in_array($this->string('color')->toString(), $colors, true)) {
                    $validator->errors()->add('color', 'Select a valid color for this item.');
                }
            },
        ];
    }
}
