<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriveUploadService
{
    /**
     * Kept under the old service name for compatibility.
     * Files are now stored locally, not on Google Drive.
     */
    public function uploadToEmployeeFolder(UploadedFile $file, Employee $employee): array
    {
        $employeeNumber = (string) $employee->emp_no;
        $fileId = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();

        $path = $file->storeAs(
            "employee-files/{$employeeNumber}",
            "{$fileId}.{$extension}",
            'local'
        );

        return [
            'file_id' => $fileId,
            'file_name' => $file->getClientOriginalName(),
            'file_url' => $path,
        ];
    }
}
