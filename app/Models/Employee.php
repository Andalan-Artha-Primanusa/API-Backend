<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'manager_id', // 🔥 TAMBAHAN
        'employee_code',
        'position',
        'department',
        'hire_date',
        'salary',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 TAMBAHAN RELASI KE MANAGER
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }
}
