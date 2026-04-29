<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompanyController extends Controller
{
    /**
     * GET /company - Get company data
     */
    public function show(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('admin.company.view')) {
            return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
        }

        try {
            $company = Company::first();

            if (!$company) {
                return ApiResponse::error('Company not found', null, 404);
            }

            return ApiResponse::success('Company data retrieved', $company);

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch company data', null, 500);
        }
    }

    /**
     * PUT /company/{id} - Update company data (ID hardcoded = 1)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('admin.company.update')) {
            return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
        }

        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid company ID']);
            }

            $company = Company::findOrFail($id);

            $validated = $request->validate([
                'name'        => 'sometimes|string|max:255',
                'legal_name'  => 'sometimes|nullable|string|max:255',
                'tax_number'  => 'sometimes|nullable|string|max:100',
                'email'       => 'sometimes|nullable|email|max:255',
                'phone'       => 'sometimes|nullable|string|max:50',
                'website'     => 'sometimes|nullable|string|max:255',
                'address'     => 'sometimes|nullable|string|max:1000',
                'city'        => 'sometimes|nullable|string|max:100',
                'state'       => 'sometimes|nullable|string|max:100',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'country'     => 'sometimes|nullable|string|max:100',
                'logo'        => 'sometimes|nullable|image|max:2048',
            ]);

            if ($request->hasFile('logo')) {
                if ($company->logo_path) {
                    Storage::disk('public')->delete($company->logo_path);
                }

                $file = $request->file('logo');
                $storedName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $company->logo_path = $file->storeAs('company/logo', $storedName, 'public');
            }

            unset($validated['logo']);

            $company->update($validated);

            return ApiResponse::success('Company data updated successfully', $company->fresh());

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Company not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update company data', null, 500);
        }
    }
}
