<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('leave.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'type'       => ['required', 'string', 'in:annual,sick,unpaid'],
            'reason'     => ['nullable', 'string', 'max:1000'],
        ];
    }
}
