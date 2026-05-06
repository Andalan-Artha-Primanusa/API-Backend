<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_id',
        'date',
        'scheduled_checkout',
        'actual_checkout',
        'overtime_minutes',
        'status',
        'reason',
        'reject_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'overtime_minutes' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function evidences()
    {
        return $this->hasMany(OvertimeEvidence::class);
    }

    public function approvedEvidences()
    {
        return $this->evidences()->where('status', 'approved');
    }
}
