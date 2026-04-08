<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('employee.update');
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee') ?? $this->route('id');

        return [
            'employee_code' => [
                'sometimes', 'string',
                Rule::unique('employees', 'employee_code')->ignore($employeeId),
            ],
            'manager_id'    => ['sometimes', 'nullable', 'exists:users,id'],
            'position'      => ['sometimes', 'string', 'max:255'],
            'department'    => ['sometimes', 'string', 'max:255'],
            'hire_date'     => ['sometimes', 'date'],
            'salary'        => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
