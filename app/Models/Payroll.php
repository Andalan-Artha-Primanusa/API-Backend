<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'status',
    ];

    protected $casts = [
        'basic_salary'          => 'decimal:2',
        'allowance'             => 'decimal:2',
        'bonus'                 => 'decimal:2',
        'bpjs_kesehatan'        => 'decimal:2',
        'bpjs_ketenagakerjaan'  => 'decimal:2',
        'pph21'                 => 'decimal:2',
        'total_deduction'       => 'decimal:2',
        'take_home_pay'         => 'decimal:2',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }
}
