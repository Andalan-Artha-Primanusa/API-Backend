<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'approval_flow_id',
        'step_order',
        'role_id',
    ];

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
