<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'date'      => 'date',
        'check_in'  => 'datetime',
        'check_out' => 'datetime',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
        'status'    => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
