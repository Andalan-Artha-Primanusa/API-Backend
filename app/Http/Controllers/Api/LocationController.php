<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * GET /locations
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('location.view')) {
            return ApiResponse::error('Forbidden', 'No permission to view locations', 403);
        }

        $locations = Location::latest('id')->get();

        return ApiResponse::success('Location list', $locations);
    }

    /**
     * POST /locations
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('location.create')) {
            return ApiResponse::error('Forbidden', 'No permission to create locations', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10'
        ]);

        $location = Location::create($validated);

        return ApiResponse::success('Location created successfully', $location, 201);
    }

    /**
     * GET /locations/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasPermission('location.view')) {
            return ApiResponse::error('Forbidden', 'No permission to view locations', 403);
        }

        $location = Location::findOrFail($id);

        return ApiResponse::success('Location detail', $location);
    }

    /**
     * PUT /locations/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasPermission('location.update')) {
            return ApiResponse::error('Forbidden', 'No permission to update locations', 403);
        }

        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'radius' => 'sometimes|integer|min:10'
        ]);

        $location->update($validated);

        return ApiResponse::success('Location updated successfully', $location->fresh());
    }

    /**
     * DELETE /locations/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasPermission('location.delete')) {
            return ApiResponse::error('Forbidden', 'No permission to delete locations', 403);
        }

        Location::findOrFail($id)->delete();

        return ApiResponse::success('Location deleted successfully');
    }
}