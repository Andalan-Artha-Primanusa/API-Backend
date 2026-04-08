<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    protected $fillable = [
        'payroll_id',
        'type',
        'name',
        'amount'
    ];

    // Relasi ke Payroll
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
