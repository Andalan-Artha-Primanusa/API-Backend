<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    public const STAGE_APPLIED = 'applied';
    public const STAGE_SCREENING = 'screening';
    public const STAGE_INTERVIEW = 'interview';
    public const STAGE_OFFER = 'offer';
    public const STAGE_HIRED = 'hired';
    public const STAGE_REJECTED = 'rejected';
    public const STAGE_WITHDRAWN = 'withdrawn';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'job_opening_id',
        'full_name',
        'email',
        'phone',
        'source',
        'current_stage',
        'status',
        'score',
        'expected_salary',
        'applied_at',
        'last_activity_at',
        'notes',
        'assigned_to',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'applied_at' => 'date',
        'last_activity_at' => 'datetime',
        'score' => 'decimal:2',
        'expected_salary' => 'decimal:2',
    ];

    public function opening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
