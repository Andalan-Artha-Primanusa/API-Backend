<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    /**
     * Permission names for location operations
     */
    private const PERMISSION_MAP = [
        'index'   => 'location.view',
        'show'    => 'location.view',
        'store'   => 'location.create',
        'update'  => 'location.update',
        'destroy' => 'location.delete',
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $action = $request->route()->getActionMethod();
            $permission = self::PERMISSION_MAP[$action] ?? null;

            if ($permission && !$request->user()->hasPermission($permission)) {
                return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
            }

            return $next($request);
        });
    }

    /**
     * GET /locations - List all locations with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'per_page' => 'sometimes|integer|min:1|max:100',
                'search'   => 'sometimes|string|max:255',
            ]);

            $perPage = $validated['per_page'] ?? 15;
            $search = $validated['search'] ?? null;

            $query = Location::select(['id', 'name', 'latitude', 'longitude', 'radius', 'created_at', 'updated_at']);

            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

            $locations = $query->latest('id')->paginate($perPage);

            return ApiResponse::success(
                $locations->isEmpty() ? 'No locations available' : 'Location list',
                $locations
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid query parameters', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Location Index Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch locations', null, 500);
        }
    }

    /**
     * POST /locations - Create new location
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:255|unique:locations,name',
                'latitude'  => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius'    => 'required|integer|min:10|max:5000',
            ]);

            $location = Location::create($validated);

            \Log::info('Location Created', ['location_id' => $location->id, 'created_by' => $request->user()->id]);

            return ApiResponse::success('Location created successfully', $location, 201);

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Location Store Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to create location', null, 500);
        }
    }

    /**
     * GET /locations/{id} - Show specific location
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid location ID']);
            }

            $location = Location::select(['id', 'name', 'latitude', 'longitude', 'radius', 'created_at', 'updated_at'])
                ->findOrFail($id);

            return ApiResponse::success('Location detail', $location);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Location not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Location Show Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch location', null, 500);
        }
    }

    /**
     * PUT /locations/{id} - Update location
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid location ID']);
            }

            $location = Location::findOrFail($id);

            $validated = $request->validate([
                'name'      => 'sometimes|string|max:255|unique:locations,name,' . $id,
                'latitude'  => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',
                'radius'    => 'sometimes|integer|min:10|max:5000',
            ]);

            $location->update($validated);

            \Log::info('Location Updated', ['location_id' => $id, 'updated_by' => $request->user()->id]);

            return ApiResponse::success('Location updated successfully', $location->fresh());

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Location not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Location Update Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to update location', null, 500);
        }
    }

    /**
     * DELETE /locations/{id} - Delete location
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid location ID']);
            }

            $location = Location::select(['id', 'name', 'latitude', 'longitude', 'radius'])
                ->findOrFail($id);

            $deleted = $location->toArray();
            $location->delete();

            \Log::info('Location Deleted', ['deleted_id' => $id, 'deleted_by' => $request->user()->id]);

            return ApiResponse::success('Location deleted successfully', $deleted);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Location not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Location Delete Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to delete location', null, 500);
        }
    }
}