<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',      // 🔥 tambahan (kalau pakai employee)
        'start_date',
        'end_date',
        'total_days',       // 🔥 tambahan
        'type',             // 🔥 tambahan (annual, sick, dll)
        'reason',
        'status',
        'approved_by',      // 🔥 tambahan
        'approved_at',      // 🔥 tambahan
        'approval_note',    // 🔥 tambahan
        'approval_flow_id',
        'current_step',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function user()
        'end_date'   => 'date',
        'status'     => LeaveStatus::class,
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    // =========================================================================
    // STATUS HELPERS
    // =========================================================================

    /*
    |--------------------------------------------------------------------------
    | TYPE CONSTANT
    |--------------------------------------------------------------------------
    */

    const TYPE_ANNUAL = 'annual';
    const TYPE_SICK = 'sick';
    const TYPE_UNPAID = 'unpaid';

    /*
    |--------------------------------------------------------------------------
    | HELPER STATUS
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === LeaveStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === LeaveStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === LeaveStatus::Rejected;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER CALCULATION
    |--------------------------------------------------------------------------
    */

    public static function calculateDays($start, $end): int
    {
        return Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVAL ACTION
    |--------------------------------------------------------------------------
    */

    public function approve($userId, $note = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'approval_note' => $note,
        ]);
    }

    public function reject($userId, $note = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'approval_note' => $note,
        ]);
    }
}