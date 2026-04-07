<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * GET /locations
     */
    public function index()
    {
        $locations = Location::latest('id')->get(); // pakai id, bukan created_at

        return response()->json([
            'message' => 'List lokasi',
            'data' => $locations
        ]);
    }

    /**
     * POST /locations
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:10'
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Lokasi berhasil dibuat',
            'data' => $location
        ], 201);
    }

    /**
     * GET /locations/{id}
     */
    public function show($id)
    {
        $location = Location::findOrFail($id);

        return response()->json([
            'message' => 'Detail lokasi',
            'data' => $location
        ]);
    }

    /**
     * PUT /locations/{id}
     */
    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'radius' => 'sometimes|integer|min:10'
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'Lokasi berhasil diupdate',
            'data' => $location
        ]);
    }

    /**
     * DELETE /locations/{id}
     */
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return response()->json([
            'message' => 'Lokasi berhasil dihapus'
        ]);
    }
}