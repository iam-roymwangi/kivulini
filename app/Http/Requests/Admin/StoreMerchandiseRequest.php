<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMerchandiseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:t_shirt,hoodie,hat,other'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sizes' => ['required', 'array', 'min:1'],
            'sizes.*' => ['required', 'string', 'max:10'],
            'colors' => ['required', 'array', 'min:1'],
            'colors.*' => ['required', 'string', 'max:50'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp'],
        ];
    }
}
