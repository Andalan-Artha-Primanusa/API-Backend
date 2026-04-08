<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

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
        $result = $this->getNearestLocation($latitude, $longitude);

        if (!$result['location']) {
            throw new \DomainException('No office location configured.');
        }

        if ($result['distance'] > $result['location']->radius) {
            throw new \DomainException(
                'Outside attendance area. Distance: ' . round($result['distance']) . ' m, '
                    . 'max: ' . $result['location']->radius . ' m.'
            );
        }

        // Use try-catch with unique constraint to prevent race condition duplicates
        try {
            $attendance = Attendance::create([
                'user_id'   => $user->id,
                'date'      => now()->toDateString(),
                'check_in'  => now(),
                'latitude'  => $latitude,
                'longitude' => $longitude,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // MySQL error 1062 = duplicate entry (unique constraint violation)
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                throw new \DomainException('Already checked in today.');
            }
            throw $e;
        }

        return [
            'attendance' => $attendance,
            'location'   => $result['location']->name,
            'distance'   => round($result['distance']),
        ];
    }

    /**
     * Process a check-out request.
     *
     * @throws \DomainException if not checked in or already checked out
     */
    public function checkOut(User $user): Attendance
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance) {
            throw new \DomainException('Not checked in today.');
        }

        if ($attendance->check_out) {
            throw new \DomainException('Already checked out.');
        }

        $attendance->update(['check_out' => now()]);

        return $attendance->fresh();
    }

    /**
     * Get attendance history for a specific user.
     */
    public function getHistory(User $user): LengthAwarePaginator
    {
        return Attendance::where('user_id', $user->id)
            ->latest('date')
            ->paginate(15);
    }

    /**
     * Get today's attendance record for a user.
     */
    public function getToday(User $user): ?Attendance
    {
        return Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();
    }

    /**
     * Get all attendance records (admin view).
     */
    public function getAll(): LengthAwarePaginator
    {
        return Attendance::with('user')
            ->latest('date')
            ->paginate(15);
    }

    /**
     * Find the nearest office location to the given coordinates.
     * Locations are cached for 1 hour since they rarely change.
     *
     * @return array{location: ?Location, distance: float}
     */
    private function getNearestLocation(float $lat, float $lon): array
    {
        $locations = Cache::remember('office_locations', 3600, function () {
            return Location::select('id', 'name', 'latitude', 'longitude', 'radius')->get();
        });

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = $this->haversineDistance(
                $location->latitude,
                $location->longitude,
                $lat,
                $lon
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $location;
            }
        }

        return ['location' => $nearest, 'distance' => $minDistance];
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
