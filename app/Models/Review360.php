<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review360 extends Model
{
    use SoftDeletes;

    protected $table = 'review_360s';

    protected $fillable = [
        'cycle_id',
        'employee_id',
        'manager_id',
        'status',
        'start_date',
        'end_date',
        'feeders_required',
        'feeders_received',
        'self_assessment',
        'manager_notes',
        'manager_competency_ratings',
        'overall_score',
        'completed_at',
        'reviewed_at',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'manager_competency_ratings' => 'array',
        'overall_score' => 'decimal:2',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(ReviewCycle::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function feeders(): HasMany
    {
        return $this->hasMany(Review360Feeder::class);
    }

    public function getCompletionPercentage(): float
    {
        if ($this->feeders_required == 0) {
            return 0;
        }
        return ($this->feeders_received / $this->feeders_required) * 100;
    }

    public function markComplete(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function submitForReview(): bool
    {
        return $this->update([
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);
    }

    public function approve(): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}

class Review360Feeder extends Model
{
    protected $table = 'review_360_feeders';

    protected $fillable = [
        'review_360_id',
        'feeder_id',
        'feeder_type',
        'status',
        'feedback',
        'competency_ratings',
        'rating',
        'submitted_at',
        'read_at',
    ];

    protected $casts = [
        'competency_ratings' => 'array',
        'submitted_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function review360(): BelongsTo
    {
        return $this->belongsTo(Review360::class);
    }

    public function feeder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'feeder_id');
    }

    public function submitFeedback($feedback, $ratings = null, $rating = null): bool
    {
        return $this->update([
            'feedback' => $feedback,
            'competency_ratings' => $ratings,
            'rating' => $rating,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }
}

class CalibrationSession extends Model
{
    protected $table = 'calibration_sessions';

    protected $fillable = [
        'cycle_id',
        'name',
        'description',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'facilitator_id',
        'participants_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(ReviewCycle::class);
    }

    public function facilitator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'facilitator_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CalibrationParticipant::class);
    }

    public function employeeReviews(): HasMany
    {
        return $this->hasMany(CalibrationEmployeeReview::class);
    }

    public function start(): bool
    {
        return $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}

class CalibrationParticipant extends Model
{
    protected $table = 'calibration_participants';

    protected $fillable = [
        'calibration_session_id',
        'manager_id',
        'role',
        'notes',
    ];

    public function calibrationSession(): BelongsTo
    {
        return $this->belongsTo(CalibrationSession::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}

class CalibrationEmployeeReview extends Model
{
    protected $table = 'calibration_employee_reviews';

    protected $fillable = [
        'calibration_session_id',
        'review_360_id',
        'employee_id',
        'initial_score',
        'calibrated_score',
        'discussion_notes',
        'rating_category',
        'aligned',
    ];

    protected $casts = [
        'initial_score' => 'decimal:2',
        'calibrated_score' => 'decimal:2',
        'aligned' => 'boolean',
    ];

    public function calibrationSession(): BelongsTo
    {
        return $this->belongsTo(CalibrationSession::class);
    }

    public function review360(): BelongsTo
    {
        return $this->belongsTo(Review360::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
