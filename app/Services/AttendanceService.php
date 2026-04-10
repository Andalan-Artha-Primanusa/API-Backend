<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceService
{
    /**
     * Process a check-in request with geofencing validation.
     *
     * Uses unique constraint on [user_id, date] to prevent race-condition duplicates.
     *
     * @throws \DomainException for business rule violations
     * @return array{attendance: Attendance, location: string, distance: int}
     */
    public function checkIn(User $user, float $latitude, float $longitude): array
    {

        $existing = Attendance::where('user_id', $user->id)
        ->where('date', now()->toDateString())
        ->first();

        if ($existing) {
            throw new \DomainException('Already checked in today.');
        }
        // 🔥 LOAD RELATION (ANTI N+1)
        $user->loadMissing('employee.location', 'employee.workSchedule');

        $employee = $user->employee;

        if (!$employee) {
            throw new \DomainException('Employee data not found.');
        }

        // =========================
        // 🔥 VALIDASI LOCATION
        // =========================
        if (!$employee->location) {
            throw new \DomainException('No assigned location for this employee.');
        }

        $location = $employee->location;

        $distance = $this->haversineDistance(
            $location->latitude,
            $location->longitude,
            $latitude,
            $longitude
        );

        if ($distance > $location->radius) {
            throw new \DomainException(
                'Outside your assigned location area. Distance: ' . round($distance) . ' m'
            );
        }

        // =========================
        // 🔥 VALIDASI SCHEDULE
        // =========================
        if (!$employee->workSchedule) {
            throw new \DomainException('No work schedule assigned.');
        }

        $schedule = $employee->workSchedule;

        $now = now();
        $checkInTime = now()->setTimeFromTimeString($schedule->check_in_time);
        $graceLimit = $checkInTime->copy()->addMinutes($schedule->grace_period);

        // =========================
        // 🔥 STATUS LOGIC (INI YANG BARU)
        // =========================
        $status = 'on_time';

        if ($now->gt($checkInTime) && $now->lte($graceLimit)) {
            $status = 'late';
        }

        if ($now->gt($graceLimit)) {
            $status = 'absent';
        }

        // =========================
        // CREATE ATTENDANCE
        // =========================
        try {
            $attendance = Attendance::create([
                'user_id'   => $user->id,
                'date'      => now()->toDateString(),
                'check_in'  => now(),
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'status'    => $status, // 🔥 TAMBAHAN PENTING
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                throw new \DomainException('Already checked in today.');
            }
            throw $e;
        }

        return [
            'attendance' => $attendance->load('user.profile'),
            'location'   => $location->name,
            'distance'   => round($distance),
        ];
    }

    /**
     * Process a check-out request.
     *
     * @throws \DomainException if not checked in or already checked out
     */
    public function checkOut(User $user): Attendance
    {

        $user->loadMissing('employee.workSchedule'); // 🔥 TAMBAH INI

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance) {
            throw new \DomainException('Not checked in today.');
        }

        if ($attendance->check_out) {
            throw new \DomainException('Already checked out.');
        }

        $employee = $user->employee;

        if (!$employee || !$employee->workSchedule) {
            throw new \DomainException('No work schedule assigned.');
        }

        $schedule = $employee->workSchedule;

        $now = now();
        $checkOutTime = now()->setTimeFromTimeString($schedule->check_out_time);

        if ($now->lt($checkOutTime)) {
            throw new \DomainException('Cannot check out before minimum time.');
        }

        $attendance->update(['check_out' => now()]);

        return $attendance->fresh(['user.profile']);
    }

    /**
     * Get attendance history for a specific user.
     */
    public function getHistory(User $user): LengthAwarePaginator
    {
        return Attendance::with('user.profile')
            ->where('user_id', $user->id)
            ->latest('date')
            ->paginate(15);
    }

    /**
     * Get today's attendance record for a user.
     */
    public function getToday(User $user): ?Attendance
    {
        return Attendance::with('user.profile')
            ->where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();
    }

    /**
     * Get all attendance records (admin view).
     */
    public function getAll(): LengthAwarePaginator
    {
        return Attendance::with('user.profile')
            ->latest('date')
            ->paginate(15);
    }

    /**
     * Calculate the distance between two GPS coordinates using the Haversine formula.
     *
     * @return float Distance in meters
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $earthRadius * atan2(sqrt($a), sqrt(1 - $a));
    }
}
