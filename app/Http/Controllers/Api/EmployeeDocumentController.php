<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\UserNotification;
use App\Traits\HasEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeDocumentController extends Controller
{
    use HasEmployee;

    public function contracts(Request $request): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'employee_id' => ['sometimes', 'integer', 'exists:employees,id'],
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected,expired,archived'],
            'days' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
        ]);

        $days = $validated['days'] ?? 90;
        $from = now()->startOfDay();
        $until = now()->addDays($days)->endOfDay();

        $query = EmployeeDocument::with(['employee.user.profile', 'uploader:id,name,email', 'reviewer:id,name,email'])
            ->where(function ($builder) {
                $builder->where('category', 'contract')
                    ->orWhere('document_type', 'contract')
                    ->orWhere('document_type', 'employment_contract')
                    ->orWhere('document_type', 'pkwt')
                    ->orWhere('document_type', 'pkwtt');
            })
            ->latest();

        if (!empty($validated['employee_id'])) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('document_type', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('file_name', 'like', '%' . $search . '%');
            });
        }

        $contracts = $query->paginate($validated['per_page'] ?? 15);

        $baseCountQuery = EmployeeDocument::query()
            ->where(function ($builder) {
                $builder->where('category', 'contract')
                    ->orWhere('document_type', 'contract')
                    ->orWhere('document_type', 'employment_contract')
                    ->orWhere('document_type', 'pkwt')
                    ->orWhere('document_type', 'pkwtt');
            });

        if (!empty($validated['employee_id'])) {
            $baseCountQuery->where('employee_id', $validated['employee_id']);
        }

        $summary = [
            'total_contracts' => (clone $baseCountQuery)->count(),
            'active_contracts' => (clone $baseCountQuery)
                ->where('status', EmployeeDocument::STATUS_APPROVED)
                ->where(function ($q) use ($from) {
                    $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $from->toDateString());
                })
                ->count(),
            'expiring_within_days' => (clone $baseCountQuery)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '>=', $from->toDateString())
                ->whereDate('expires_at', '<=', $until->toDateString())
                ->count(),
            'expired_contracts' => (clone $baseCountQuery)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '<', $from->toDateString())
                ->count(),
            'pending_review' => (clone $baseCountQuery)
                ->where('status', EmployeeDocument::STATUS_PENDING)
                ->count(),
        ];

        return ApiResponse::success('Contract tracking retrieved successfully', [
            'window_days' => $days,
            'from' => $from->toDateString(),
            'to' => $until->toDateString(),
            'summary' => $summary,
            'contracts' => $contracts,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        return ApiResponse::success('Employee documents retrieved successfully', $this->buildQuery($request)->paginate($request->integer('per_page', 15)));
    }

    public function myDocuments(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $query = EmployeeDocument::with(['employee.user.profile', 'uploader:id,name,email', 'reviewer:id,name,email'])
            ->where('employee_id', $employee->id)
            ->latest();

        return ApiResponse::success('My documents retrieved successfully', $query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $isPrivileged = $user->isAdmin() || $user->isHR();

        $validated = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected,expired,archived'],
            'expires_at' => ['nullable', 'date'],
            'is_confidential' => ['sometimes', 'boolean'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        if ($isPrivileged) {
            if (empty($validated['employee_id'])) {
                return ApiResponse::error('Validation error', ['employee_id' => ['The employee_id field is required.']], 422);
            }
            $employee = Employee::findOrFail($validated['employee_id']);
        } else {
            $employee = $this->getAuthenticatedEmployee();

            if (!empty($validated['employee_id']) && (int) $validated['employee_id'] !== $employee->id) {
                return ApiResponse::error('Forbidden', 'You can only upload documents for your own employee record', 403);
            }
        }

        $file = $request->file('file');
        $storedName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('employee-documents/' . $employee->id, $storedName, 'public');

        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'uploaded_by' => $user->id,
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'] ?? EmployeeDocument::STATUS_PENDING,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_mime' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'expires_at' => $validated['expires_at'] ?? null,
            'is_confidential' => $validated['is_confidential'] ?? false,
        ]);

        UserNotification::create([
            'user_id' => $employee->user_id,
            'sender_user_id' => $user->id,
            'title' => 'Document uploaded',
            'message' => 'A new document has been added to your employee file: ' . $document->title,
            'type' => 'document.uploaded',
            'category' => 'document_management',
            'data' => [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
                'status' => $document->status,
            ],
        ]);

        return ApiResponse::success('Employee document created successfully', $document->load(['employee.user.profile', 'uploader:id,name,email']), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $document = EmployeeDocument::with(['employee.user.profile', 'uploader:id,name,email', 'reviewer:id,name,email'])->find($id);

        if (!$document) {
            return ApiResponse::error('Document not found', null, 404);
        }

        if (!$this->canAccessDocument($request, $document)) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        return ApiResponse::success('Document detail', $document);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $document = EmployeeDocument::find($id);

        if (!$document) {
            return ApiResponse::error('Document not found', null, 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'document_type' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected,expired,archived'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
            'is_confidential' => ['sometimes', 'boolean'],
            'file' => ['sometimes', 'file', 'max:10240'],
        ]);

        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $storedName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs('employee-documents/' . $document->employee_id, $storedName, 'public');

            $document->fill([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_mime' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        $document->fill($validated);
        $document->save();

        return ApiResponse::success('Employee document updated successfully', $document->fresh(['employee.user.profile', 'uploader:id,name,email', 'reviewer:id,name,email']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $document = EmployeeDocument::find($id);

        if (!$document) {
            return ApiResponse::error('Document not found', null, 404);
        }

        $deleted = $document->toArray();

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return ApiResponse::success('Employee document deleted successfully', $deleted);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected,archived,pending,expired'],
            'review_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $document = EmployeeDocument::with('employee.user')->find($id);

        if (!$document) {
            return ApiResponse::error('Document not found', null, 404);
        }

        $document->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($document->employee?->user) {
            UserNotification::create([
                'user_id' => $document->employee->user_id,
                'sender_user_id' => $request->user()->id,
                'title' => 'Document reviewed',
                'message' => 'Your document "' . $document->title . '" has been reviewed.',
                'type' => 'document.reviewed',
                'category' => 'document_management',
                'data' => [
                    'document_id' => $document->id,
                    'status' => $document->status,
                ],
            ]);
        }

        return ApiResponse::success('Employee document reviewed successfully', $document->fresh(['employee.user.profile', 'uploader:id,name,email', 'reviewer:id,name,email']));
    }

    public function expiring(Request $request): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'days' => ['sometimes', 'integer', 'min:1', 'max:365'],
        ]);

        $days = $validated['days'] ?? 30;

        $documents = EmployeeDocument::with(['employee.user.profile', 'uploader:id,name,email'])
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->startOfDay(), now()->addDays($days)->endOfDay()])
            ->orderBy('expires_at')
            ->get();

        return ApiResponse::success('Expiring documents retrieved successfully', $documents);
    }

    protected function buildQuery(Request $request)
    {
        $query = EmployeeDocument::with(['employee.user.profile', 'uploader:id,name,email', 'reviewer:id,name,email'])->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('document_type', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('file_name', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    protected function canAccessDocument(Request $request, EmployeeDocument $document): bool
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isHR()) {
            return true;
        }

        return $document->employee?->user_id === $user->id;
    }
}