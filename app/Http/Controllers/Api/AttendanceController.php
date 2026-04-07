<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * 📍 HITUNG JARAK (HAVERSINE)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c * 1000; // meter
    }

    /**
     * 📍 CARI LOKASI TERDEKAT
     */
    private function getNearestLocation($lat, $lon)
    {
        $locations = Location::all();

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($locations as $loc) {
            $distance = $this->calculateDistance(
                $loc->latitude,
                $loc->longitude,
                $lat,
                $lon
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $loc;
            }
        }

        return [
            'location' => $nearest,
            'distance' => $minDistance
        ];
    }

    /**
     * ✅ CHECK-IN
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();

        // 🔥 cari lokasi terdekat
        $result = $this->getNearestLocation(
            $request->latitude,
            $request->longitude
        );

        $location = $result['location'];
        $distance = $result['distance'];

        if (!$location) {
            return response()->json([
                'message' => 'Lokasi kantor tidak tersedia'
            ], 400);
        }

        // 🔒 cek radius
        if ($distance > $location->radius) {
            return response()->json([
                'message' => 'Di luar area absensi',
                'distance' => round($distance) . ' meter',
                'max_radius' => $location->radius . ' meter'
            ], 403);
        }

        // 🔒 cek sudah absen
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Sudah check-in hari ini'
            ], 400);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Check-in berhasil',
            'location' => $location->name,
            'distance' => round($distance) . ' meter',
            'data' => $attendance
        ]);
    }

    /**
     * 🚪 CHECK-OUT
     */
    public function checkOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'Belum check-in'
            ], 400);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'Sudah check-out'
            ], 400);
        }

        $attendance->update([
            'check_out' => now()
        ]);

        return response()->json([
            'message' => 'Check-out berhasil',
            'data' => $attendance
        ]);
    }

    /**
     * 📊 HISTORY
     */
    public function history()
    {
        $data = Attendance::where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return response()->json([
            'message' => 'History absensi',
            'data' => $data
        ]);
    }

    /**
     * 📅 TODAY
     */
    public function today()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
            ->first();

        return response()->json([
            'message' => 'Absensi hari ini',
            'data' => $attendance
        ]);
    }
}