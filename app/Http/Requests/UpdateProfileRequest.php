<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership check is done in the controller
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'      => ['sometimes', 'nullable', 'string', 'max:20'],
            'address'    => ['sometimes', 'nullable', 'string', 'max:500'],
            'birth_date' => ['sometimes', 'nullable', 'date', 'before:today'],
        ];
    }
}
