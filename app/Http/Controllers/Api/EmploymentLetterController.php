<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class EmploymentLetterController
{
    public function generateExperienceLetter(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::with(['user.profile', 'manager'])->findOrFail($employeeId);
        $profile = $employee->user->profile;
        $now = now();
        $filename = 'experience-letter-' . $employee->id . '-' . $now->format('YmdHis') . '.pdf';

        $pdf = Pdf::loadView('pdf.experience-letter', [
            'employee' => $employee,
            'profile' => $profile,
            'date' => $now->toDateString(),
        ]);
        $pdfContent = $pdf->output();
        $storedPath = 'employee-documents/' . $employee->id . '/' . $filename;
        Storage::disk('public')->put($storedPath, $pdfContent);

        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'uploaded_by' => $request->user()->id,
            'title' => 'Surat Pengalaman Kerja',
            'document_type' => 'experience_letter',
            'category' => 'letter',
            'status' => EmployeeDocument::STATUS_APPROVED,
            'file_name' => $filename,
            'file_path' => $storedPath,
            'file_mime' => 'application/pdf',
            'file_size' => strlen($pdfContent),
        ]);

        return ApiResponse::success('Surat pengalaman kerja berhasil dibuat', $document->load(['employee.user.profile']), 201);
    }

    public function generateEmploymentLetter(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::with(['user.profile', 'manager'])->findOrFail($employeeId);
        $profile = $employee->user->profile;
        $now = now();
        $filename = 'employment-letter-' . $employee->id . '-' . $now->format('YmdHis') . '.pdf';

        $pdf = Pdf::loadView('pdf.employment-letter', [
            'employee' => $employee,
            'profile' => $profile,
            'date' => $now->toDateString(),
        ]);
        $pdfContent = $pdf->output();
        $storedPath = 'employee-documents/' . $employee->id . '/' . $filename;
        Storage::disk('public')->put($storedPath, $pdfContent);

        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'uploaded_by' => $request->user()->id,
            'title' => 'Surat Keterangan Bekerja',
            'document_type' => 'employment_letter',
            'category' => 'letter',
            'status' => EmployeeDocument::STATUS_APPROVED,
            'file_name' => $filename,
            'file_path' => $storedPath,
            'file_mime' => 'application/pdf',
            'file_size' => strlen($pdfContent),
        ]);

        return ApiResponse::success('Surat keterangan bekerja berhasil dibuat', $document->load(['employee.user.profile']), 201);
    }
}
