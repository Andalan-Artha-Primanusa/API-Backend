<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlow extends Model
{
    protected $fillable = [
        'name',
        'module',
    ];

    // 🔥 relasi ke steps
    public function steps()
    {
        return $this->hasMany(ApprovalStep::class)
            ->orderBy('step_order');
    }
}
