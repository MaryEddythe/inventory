<?php

namespace App\Http\Controllers;

use App\Models\EmployeeFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeFileController extends Controller
{
    private const FILE_TYPES = [
        'PDS',
        'SALN',
        'POLICE CLEARANCE CLEARANCE',
        'MEDICAL CERTIFICATE',
        'PAG-IBIG',
        'PHILHEALTH',
        'TIN',
        'GSIS',
        'PRC',
        'Civil Service Eligibility',
        'Contract of Employment',
    ];

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;

        abort_unless($employee, 403);

        $validated = $request->validate([
            'file_type' => ['required', 'in:' . implode(',', self::FILE_TYPES)],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $file = $request->file('file');
        $empNo = (string) ($employee->emp_no ?: $employee->employee_id);
        $fileId = (string) Str::uuid();
        $directory = "employee-files/{$empNo}";
        $storedPath = $file->storeAs(
            $directory,
            "{$fileId}.{$file->getClientOriginalExtension()}",
            'local'
        );

        EmployeeFile::create([
            'emp_no' => $empNo,
            'file_type' => $validated['file_type'],
            'file_name' => $file->getClientOriginalName(),
            'file_url' => $storedPath,
            'file_id' => $fileId,
        ]);

        return back()->with('success', 'Employee file uploaded successfully.');
    }

    public function download(EmployeeFile $employeeFile): StreamedResponse
    {
        $this->authorizeFile($employeeFile);

        abort_unless(Storage::disk('local')->exists($employeeFile->file_url), 404);

        return Storage::disk('local')->download(
            $employeeFile->file_url,
            $employeeFile->file_name
        );
    }

    public function destroy(EmployeeFile $employeeFile)
    {
        $this->authorizeFile($employeeFile);

        Storage::disk('local')->delete($employeeFile->file_url);
        $employeeFile->delete();

        return back()->with('success', 'Employee file deleted successfully.');
    }

    private function authorizeFile(EmployeeFile $employeeFile): void
    {
        $employee = Auth::user()->employee;
        $empNo = $employee?->emp_no ?: $employee?->employee_id;

        abort_unless($employee && (string) $employeeFile->emp_no === (string) $empNo, 403);
    }
}
