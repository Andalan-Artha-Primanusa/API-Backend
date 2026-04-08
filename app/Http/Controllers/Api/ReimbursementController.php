<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reimbursement;
use App\Models\Employee;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReimbursementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🔥 HR / MANAGER / FINANCE
    |--------------------------------------------------------------------------
    */

    // ✅ GET ALL REIMBURSEMENTS
    public function index(Request $request)
    {
        $query = Reimbursement::with('employee', 'approver');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by employee
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        $reimbursements = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $reimbursements
        ]);
    }

    // ✅ CREATE REIMBURSEMENT
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|in:travel,medical,office_supplies,training,meal,accommodation,transportation,other',
            'expense_date' => 'required|date|before_or_equal:today',
            'receipt_path' => 'nullable|string'
        ]);

        $reimbursement = Reimbursement::create([
            'employee_id' => $request->employee_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'receipt_path' => $request->receipt_path,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil dibuat',
            'data' => $reimbursement->load('employee')
        ]);
    }

    // ✅ DETAIL REIMBURSEMENT
    public function show($id)
    {
        $reimbursement = Reimbursement::with('employee', 'approver')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $reimbursement
        ]);
    }

    // ✅ UPDATE REIMBURSEMENT
    public function update(Request $request, $id)
    {
        $reimbursement = Reimbursement::findOrFail($id);

        // Only allow updates for draft status
        if (!$reimbursement->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Reimbursement yang sudah disubmit tidak bisa diupdate'
            ], 400);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string|in:travel,medical,office_supplies,training,meal,accommodation,transportation,other',
            'expense_date' => 'sometimes|date|before_or_equal:today',
            'receipt_path' => 'nullable|string'
        ]);

        $reimbursement->update($request->only([
            'title',
            'description',
            'amount',
            'category',
            'expense_date',
            'receipt_path'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil diupdate',
            'data' => $reimbursement->load('employee')
        ]);
    }

    // ✅ DELETE REIMBURSEMENT
    public function destroy($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);

        // Only allow deletion for draft status
        if (!$reimbursement->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Reimbursement yang sudah disubmit tidak bisa dihapus'
            ], 400);
        }

        $reimbursement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil dihapus'
        ]);
    }

    // ✅ APPROVE REIMBURSEMENT
    public function approve(Request $request, $id)
    {
        $reimbursement = Reimbursement::findOrFail($id);

        if (!$reimbursement->isSubmitted()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya reimbursement yang sudah disubmit yang bisa disetujui'
            ], 400);
        }

        $reimbursement->approve(auth()->id(), $request->note);

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil disetujui',
            'data' => $reimbursement->load('employee', 'approver')
        ]);
    }

    // ✅ REJECT REIMBURSEMENT
    public function reject(Request $request, $id)
    {
        $reimbursement = Reimbursement::findOrFail($id);

        if (!$reimbursement->isSubmitted()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya reimbursement yang sudah disubmit yang bisa ditolak'
            ], 400);
        }

        $request->validate([
            'note' => 'required|string|max:500'
        ]);

        $reimbursement->reject(auth()->id(), $request->note);

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil ditolak',
            'data' => $reimbursement->load('employee', 'approver')
        ]);
    }

    // ✅ MARK AS PAID
    public function markAsPaid($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);

        if (!$reimbursement->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya reimbursement yang sudah disetujui yang bisa ditandai sebagai dibayar'
            ], 400);
        }

        $reimbursement->markAsPaid();

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil ditandai sebagai dibayar',
            'data' => $reimbursement->load('employee', 'approver')
        ]);
    }

    // ✅ GET PENDING REIMBURSEMENTS
    public function pending()
    {
        $reimbursements = Reimbursement::with('employee')
            ->where('status', 'submitted')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reimbursements
        ]);
    }

    // ✅ GET REIMBURSEMENTS BY EMPLOYEE
    public function byEmployee($employee_id)
    {
        $reimbursements = Reimbursement::where('employee_id', $employee_id)
            ->with('approver')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reimbursements
        ]);
    }

    // ✅ GET STATISTICS
    public function statistics(Request $request)
    {
        $employeeId = $request->employee_id;

        $stats = [
            'total_count' => Reimbursement::getCountByStatus($employeeId),
            'total_amount' => Reimbursement::getTotalByStatus($employeeId),
            'draft_count' => Reimbursement::getCountByStatus($employeeId, 'draft'),
            'draft_amount' => Reimbursement::getTotalByStatus($employeeId, 'draft'),
            'submitted_count' => Reimbursement::getCountByStatus($employeeId, 'submitted'),
            'submitted_amount' => Reimbursement::getTotalByStatus($employeeId, 'submitted'),
            'approved_count' => Reimbursement::getCountByStatus($employeeId, 'approved'),
            'approved_amount' => Reimbursement::getTotalByStatus($employeeId, 'approved'),
            'paid_count' => Reimbursement::getCountByStatus($employeeId, 'paid'),
            'paid_amount' => Reimbursement::getTotalByStatus($employeeId, 'paid'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 EMPLOYEE (USER)
    |--------------------------------------------------------------------------
    */

    // ✅ MY REIMBURSEMENTS
    public function myReimbursements(Request $request)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'User bukan employee'
            ], 403));
        }

        $query = Reimbursement::where('employee_id', $employee->id)->with('approver');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $reimbursements = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $reimbursements
        ]);
    }

    // ✅ SUBMIT REIMBURSEMENT
    public function submit(Request $request, $id)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'User bukan employee'
            ], 403));
        }

        $reimbursement = Reimbursement::findOrFail($id);

        // Check ownership
        if ($reimbursement->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya akses ke reimbursement ini'
            ], 403);
        }

        // Only draft can be submitted
        if (!$reimbursement->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Reimbursement sudah disubmit / diproses'
            ], 400);
        }

        $reimbursement->submit();

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil disubmit',
            'data' => $reimbursement->load('employee', 'approver')
        ]);
    }

    // ✅ CREATE MY REIMBURSEMENT
    public function createMyReimbursement(Request $request)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'User bukan employee'
            ], 403));
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|in:travel,medical,office_supplies,training,meal,accommodation,transportation,other',
            'expense_date' => 'required|date|before_or_equal:today',
            'receipt_path' => 'nullable|string'
        ]);

        $reimbursement = Reimbursement::create([
            'employee_id' => $employee->id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'receipt_path' => $request->receipt_path,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil dibuat',
            'data' => $reimbursement->load('employee')
        ]);
    }
}