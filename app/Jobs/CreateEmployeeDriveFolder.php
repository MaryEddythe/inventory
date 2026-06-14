<?php

namespace App\Jobs;

use App\Models\Employee;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateEmployeeDriveFolder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Employee $employee)
    {
        // Job payload is the employee row.
    }

    public function handle(): void
    {
        // Refresh to avoid stale attributes
        $employee = Employee::query()->find($this->employee->getKey());

        if (!$employee) {
            return;
        }

        try {
            $client = new Client();
            $client->setClientId(config('google.client_id'));
            $client->setClientSecret(config('google.client_secret'));
            $client->addScope(Drive::DRIVE);
            $client->setAccessType('offline');

            $refreshToken = config('google.refresh_token');
            if (!$refreshToken) {
                throw new \RuntimeException('Missing GOOGLE_REFRESH_TOKEN.');
            }

            $accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (isset($accessToken['error'])) {
                $message = $accessToken['error_description'] ?? $accessToken['error'];
                throw new \RuntimeException('Google token refresh failed: ' . $message);
            }

            $service = new Drive($client);

            // Where to create the employee folder.
            // Provide your folder ID via config/google.php (e.g. google.employee_root_folder_id)
            // Fallback to null => Drive will create in user's root.
            $parentId = config('google.employee_root_folder_id');

            $folderName = trim(($employee->last_name ?? '') . ', ' . ($employee->first_name ?? ''));
            if ($folderName === ',') {
                $folderName = $employee->getFullNameAttribute();
            }
            if ($folderName === '') {
                $folderName = $employee->emp_no;
            }

            $metadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

            if ($parentId) {
                $metadata->setParents([$parentId]);
            }

            // fields: id + link
            $created = $service->files->create($metadata, [
                'fields' => 'id, webViewLink, name',
            ]);

            $links = [];
            if (!empty($created->webViewLink)) {
                $links[] = $created->webViewLink;
            }

            $employee->drive_folder_id = $created->id ?? null;
            $employee->drive_folder_url = $created->webViewLink ?? null;
            $employee->drive = $links ? json_encode($links) : null;
            $employee->save();

            Log::info("Created Drive folder for {$employee->full_name}", [
                'drive_folder_id' => $employee->drive_folder_id,
                'drive_folder_url' => $employee->drive_folder_url,
            ]);
        } catch (\Throwable $e) {
            Log::error('CreateEmployeeDriveFolder failed', [
                'employee_id' => $this->employee->getKey(),
                'error' => $e->getMessage(),
            ]);

            // Let the job fail so it can be retried per queue settings.
            throw $e;
        }
    }
}

