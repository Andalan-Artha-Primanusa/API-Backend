<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('employee.create');
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'exists:users,id', 'unique:employees,user_id'],
            'manager_id'    => ['nullable', 'exists:users,id'],
            'employee_code' => ['required', 'string', 'unique:employees,employee_code'],
            'position'      => ['required', 'string', 'max:255'],
            'department'    => ['required', 'string', 'max:255'],
            'hire_date'     => ['nullable', 'date'],
            'salary'        => ['nullable', 'numeric', 'min:0'],
            'location_id' => ['required', 'exists:locations,id'],
            'work_schedule_id' => ['required', 'exists:work_schedules,id'],
        ];
    }
}
