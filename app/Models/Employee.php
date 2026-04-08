<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'manager_id',
        'employee_code',
        'position',
        'department',
        'hire_date',
        'salary',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary'    => 'decimal:2', // Matches migration decimal(12,2)
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The manager (User) of this employee.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
