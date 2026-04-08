<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'period',
        'basic_salary',
        'allowance',
        'bonus',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'pph21',
        'total_deduction',
        'take_home_pay',
        'status'
    ];

    // Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }
}
