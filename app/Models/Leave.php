<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 kalau pakai employee (HRIS biasanya pakai ini)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS CONSTANT
    |--------------------------------------------------------------------------
    */

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

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
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
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