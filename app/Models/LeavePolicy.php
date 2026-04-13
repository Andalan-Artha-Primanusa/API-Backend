<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    protected $fillable = [
        'year',
        'annual_allowance',
        'carry_over_allowance',
        'max_pending_days',
        'active',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}