<?php

namespace App\Observers;

use App\Models\Employee;
use App\Jobs\CreateEmployeeDriveFolder;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        CreateEmployeeDriveFolder::dispatch($employee);
    }
}