<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PayrollDetailController extends Controller
{
    // 📌 GET DETAIL BY PAYROLL
    public function index($payroll_id): JsonResponse
    {
        $payroll = Payroll::find($payroll_id);

        if (!$payroll) {
            return ApiResponse::error('Payroll tidak ditemukan', null, 404);
        }

        $details = PayrollDetail::where('payroll_id', $payroll_id)->get();

        return ApiResponse::success(
            $details->isEmpty() ? 'Belum ada detail payroll' : 'Berhasil ambil data',
            [
                'payroll' => $payroll,
                'details' => $details
            ]
        );
    }

    // 📌 STORE (BULK)
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'payroll_id' => 'required|exists:payrolls,id',
            'details' => 'required|array|min:1',
            'details.*.type' => 'required|in:allowance,deduction',
            'details.*.name' => 'required|string|max:255',
            'details.*.amount' => 'required|numeric|min:0',
        ]);

        $payroll = Payroll::findOrFail($request->payroll_id);

        if ($payroll->status !== 'draft') {
            return ApiResponse::error('Payroll sudah diproses', null, 400);
        }

        $insertData = collect($request->details)->map(function ($item) use ($request) {
            return [
                'payroll_id' => $request->payroll_id,
                'type' => $item['type'],
                'name' => $item['name'],
                'amount' => $item['amount'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        PayrollDetail::insert($insertData);

        return ApiResponse::success('Berhasil tambah detail', $insertData, 201);
    }

    // 📌 UPDATE (SINGLE)
    public function update(Request $request, $id): JsonResponse
    {
        $detail = PayrollDetail::with('payroll')->find($id);

        if (!$detail) {
            return ApiResponse::error("Detail ID $id tidak ditemukan", null, 404);
        }

        if (!$detail->payroll || $detail->payroll->status !== 'draft') {
            return ApiResponse::error('Tidak bisa edit detail', null, 400);
        }

        $data = array_filter($request->only(['type', 'name', 'amount']), function ($v) {
            return !is_null($v);
        });

        if (empty($data)) {
            return ApiResponse::error('Tidak ada data yang diupdate', null, 400);
        }

        $detail->update($data);

        return ApiResponse::success('Berhasil update', $detail->load('payroll'));
    }

    // 📌 BULK UPDATE
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'details' => 'required|array|min:1',
            'details.*.id' => 'required|exists:payroll_details,id',
            'details.*.type' => 'nullable|in:allowance,deduction',
            'details.*.name' => 'nullable|string|max:255',
            'details.*.amount' => 'nullable|numeric|min:0',
        ]);

        $updated = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($request->details as $item) {
                $detail = PayrollDetail::with('payroll')->find($item['id']);

                if (!$detail) {
                    $errors[] = [
                        'id' => $item['id'],
                        'message' => 'Detail tidak ditemukan'
                    ];
                    continue;
                }

                if (!$detail->payroll || $detail->payroll->status !== 'draft') {
                    $errors[] = [
                        'id' => $item['id'],
                        'message' => 'Tidak bisa edit (bukan draft)'
                    ];
                    continue;
                }

                $data = array_filter([
                    'type' => $item['type'] ?? null,
                    'name' => $item['name'] ?? null,
                    'amount' => $item['amount'] ?? null,
                ], fn($v) => !is_null($v));

                if (!empty($data)) {
                    $detail->update($data);
                    $updated[] = $detail->load('payroll');
                }
            }

            DB::commit();

            return ApiResponse::success('Bulk update selesai', [
                'updated' => $updated,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Terjadi error', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 📌 DELETE
    public function destroy($id): JsonResponse
    {
        $detail = PayrollDetail::with('payroll')->find($id);

        if (!$detail) {
            return ApiResponse::error('Detail tidak ditemukan', null, 404);
        }

        if (!$detail->payroll || $detail->payroll->status !== 'draft') {
            return ApiResponse::error('Tidak bisa hapus detail', null, 400);
        }

        $detail->delete();

        return ApiResponse::success('Berhasil dihapus');
    }
}