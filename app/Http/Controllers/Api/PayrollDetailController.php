<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollDetailController extends Controller
{
    // 📌 RESPONSE HELPER (BIAR CONSISTENT)
    private function success($message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    private function error($message, $code = 400, $extra = [])
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message
        ], $extra), $code);
    }

    // 📌 GET DETAIL BY PAYROLL
    public function index($payroll_id)
    {
        $payroll = Payroll::find($payroll_id);

        if (!$payroll) {
            return $this->error('Payroll tidak ditemukan', 404);
        }

        $details = PayrollDetail::where('payroll_id', $payroll_id)->get();

        return $this->success(
            $details->isEmpty()
                ? 'Belum ada detail payroll'
                : 'Berhasil ambil data',
            [
                'payroll' => $payroll,
                'details' => $details
            ]
        );
    }

    // 📌 STORE (BULK)
    public function store(Request $request)
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
            return $this->error('Payroll sudah diproses', 400);
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

        return $this->success('Berhasil tambah detail', $insertData, 201);
    }

    // 📌 UPDATE (SINGLE)
    public function update(Request $request, $id)
    {
        $detail = PayrollDetail::with('payroll')->find($id);

        if (!$detail) {
            return $this->error("Detail ID $id tidak ditemukan", 404);
        }

        if (!$detail->payroll || $detail->payroll->status !== 'draft') {
            return $this->error('Tidak bisa edit detail', 400);
        }

        $data = array_filter($request->only(['type', 'name', 'amount']), function ($v) {
            return !is_null($v);
        });

        if (empty($data)) {
            return $this->error('Tidak ada data yang diupdate', 400);
        }

        $detail->update($data);

        return $this->success('Berhasil update', $detail->load('payroll'));
    }

    // 📌 BULK UPDATE
    public function bulkUpdate(Request $request)
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

            return $this->success('Bulk update selesai', [
                'updated' => $updated,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error('Terjadi error', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // 📌 DELETE
    public function destroy($id)
    {
        $detail = PayrollDetail::with('payroll')->find($id);

        if (!$detail) {
            return $this->error('Detail tidak ditemukan', 404);
        }

        if (!$detail->payroll || $detail->payroll->status !== 'draft') {
            return $this->error('Tidak bisa hapus detail', 400);
        }

        $detail->delete();

        return $this->success('Berhasil dihapus');
    }
}