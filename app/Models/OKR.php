<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OKR extends Model
{
    use SoftDeletes;

    protected $table = 'okrs';

    protected $fillable = [
        'employee_id',
        'period_id',
        'objective',
        'description',
        'weight',
        'status',
        'target_value',
        'current_value',
        'unit',
        'start_date',
        'end_date',
        'created_by',
        'approved_by',
        'submitted_at',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ReviewCycle::class, 'period_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getProgressPercentage(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }
        return min(100, ($this->current_value / $this->target_value) * 100);
    }

    public function submit(): bool
    {
        return $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function approve($approverUserId, $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approverUserId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function markInProgress(): bool
    {
        return $this->update(['status' => 'in_progress']);
    }

    public function markCompleted(): bool
    {
        return $this->update(['status' => 'completed']);
    }

    public function cancel(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }
}
