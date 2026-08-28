<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use App\Models\EmployeeFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $employees = Employee::query()
            ->leftJoin('inventory.departments as inv_dept',
                \DB::raw('CAST(inventory.employees.department AS UNSIGNED)'),
                '=',
                'inv_dept.dept_no'
            )
            ->select(
                'inventory.employees.*',
                'inv_dept.description as inv_dept_description',
                'inv_dept.department as inv_dept_code'
            )
            // Don't show deactivated employees
            ->where('inventory.employees.status', '!=', 'inactive')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('inventory.employees.firstname', 'like', "%{$search}%")
                        ->orWhere('inventory.employees.lastname', 'like', "%{$search}%")
                        ->orWhere('inventory.employees.Role', 'like', "%{$search}%")
                        ->orWhere('inv_dept.description', 'like', "%{$search}%")
                        ->orWhere('inv_dept.department', 'like', "%{$search}%");
                });
            })



            ->orderByDesc('emp_no')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }


    public function create()
    {
        // Load departments from inventory.departments table (dept_no, department, description, ...)
        $departments = \App\Models\Department::orderByDesc('last_updated')->get();

        // Backward compatibility: some views/logic may still reference $divisions.
        $divisions = $departments;

        return view('employees.create', compact('departments', 'divisions'));
    }




    public function store(Request $request)
    {
        // Legacy inventory.employees schema does NOT have many of the modern fields.
        // We only persist the columns that actually exist.
        $validated = $request->validate([
            'first_name' => 'required|string|max:150',
            'last_name'  => 'required|string|max:150',
            'dob'         => 'nullable|date',
        ]);

        // Map modern form fields to legacy columns.
        $payload = [
            // legacy columns in inventory.employees
            'firstname' => $validated['first_name'],
            'lastname'  => $validated['last_name'],
            'dob'        => $validated['dob'] ?? null,
        ];


        $employee = Employee::query()->create([
            'emp_no' => $request->input('emp_no') ?? null,
            'firstname' => $payload['firstname'],
            'lastname' => $payload['lastname'],
            'dob' => $payload['dob'],
        ]);

        return redirect()->route('employees.show', $employee)
            ->with('status', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['departmentRecord', 'leaveBenefits']);

        $employeeFiles = EmployeeFile::where('emp_no', $employee->emp_no)
            ->latest()
            ->get();

        // Reuse the exact records displayed in Leave Credits History.
        $ctoHistory = $employee->leaveBenefits
            ->filter(function ($benefit) {
                $type = strtolower(trim((string) $benefit->credit_type));

                return ($type === 'credited time-off' || $type === 'credited time off' || str_contains($type, 'cto'))
                    && (int) $benefit->credit_hours > 0
                    && filled($benefit->remarks);
            })
            ->sortByDesc('start_date')
            ->values();

        $leaveApplications = $employee->leaveApplications()
            ->with([
                'hrSigner',
                'divisionChiefSigner',
                'regionalDirectorSigner',
            ])
            ->latest()
            ->get();

        // Compute ledger-based accumulated days for Vacation Leave and Sick Leave
        $ledgerDays = $this->computeLedgerAccumulatedDays($employee);

        return view('employees.show', compact(
            'employee',
            'leaveApplications',
            'ledgerDays',
            'ctoHistory',
            'employeeFiles'
        ));
    }

    /**
     * Compute accumulated Vacation Leave and Sick Leave days from the ledger.
     * Each 1.25 leave credit = 1 accrued day.
     */
    protected function computeLedgerAccumulatedDays(Employee $employee): array
    {
        $setting = \App\Models\EmployeeLeaveLedgerSetting::firstOrCreate(['emp_no' => $employee->emp_no]);

        $year = (int) now()->year;
        $today = now()->startOfDay();
        $vacationBalance = (float) $setting->opening_vacation_balance;
        $sickBalance = (float) $setting->opening_sick_balance;

        // Get approved leave usage grouped by month
        $usageByMonth = $this->approvedVacationAndSickUsageByMonth($employee, $year);

        for ($month = 1; $month <= 12; $month++) {
            $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->startOfDay();
            $isClosed = $monthEnd->lte($today);
            $vacationUsed = (float) ($usageByMonth[$month]['Vacation Leave'] ?? 0);
            $sickUsed = (float) ($usageByMonth[$month]['Sick Leave'] ?? 0);
            $vacationEarned = $isClosed ? 1.25 : 0;
            $sickEarned = $isClosed ? 1.25 : 0;

            $vacationBalance = $vacationBalance - $vacationUsed + $vacationEarned;
            $sickBalance = $sickBalance - $sickUsed + $sickEarned;
        }

        return [
            'vacation_days' => round($vacationBalance / 1.25, 3),
            'sick_days' => round($sickBalance / 1.25, 3),
            'vacation_balance' => $vacationBalance,
            'sick_balance' => $sickBalance,
        ];
    }

    /**
     * Get approved Vacation Leave and Sick Leave usage by month.
     */
    protected function approvedVacationAndSickUsageByMonth(Employee $employee, int $year): array
    {
        $applications = \App\Models\EmployeeLeaveApplication::query()
            ->where('employee_id', $employee->emp_no)
            ->whereIn('leave_type', ['Vacation Leave', 'Sick Leave'])
            ->whereNotNull('regional_director_signed_at')
            ->whereYear('date_from', $year)
            ->orderBy('date_from')
            ->get();

        $usage = [];

        foreach ($applications as $application) {
            $start = \Carbon\Carbon::parse($application->date_from);
            $end = \Carbon\Carbon::parse($application->date_to ?: $application->date_from);
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                if ((int) $cursor->year === $year) {
                    $month = (int) $cursor->month;
                    $usage[$month][$application->leave_type] = ($usage[$month][$application->leave_type] ?? 0) + 1.25;
                }
                $cursor->addDay();
            }
        }

        return $usage;
    }

    public function edit(Employee $employee)
    {
        $employee->load(['departmentRecord']);

        // Division data is stored in inventory.departments (not inventory.divisions)
        $divisions = \App\Models\Department::orderByDesc('last_updated')->get();


        return view('employees.edit', [
            'employee' => $employee,
            'divisions' => $divisions,
        ]);

    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            // Edit modal submits division_id (not dept_no)
            'division_id'     => 'required',
            'position'        => 'required|string|max:100',
            'employment_type' => 'required|in:COS,PERMANENT',
            'dob'             => 'required|date',
        ]);

        // Map modal field names to legacy inventory.employees columns.
        $payload = [
            'firstname' => $validated['first_name'],
            'lastname'  => $validated['last_name'],
            'dob'       => $validated['dob'],

            // Legacy schema: inventory.employees.department stores inventory.departments.dept_no
            'department' => $validated['division_id'],

            // Legacy schema: inventory.employees.Role stores the position/title
            'Role' => $validated['position'],

            'employment_type' => $validated['employment_type'],
        ];

        $employee->update($payload);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully');
    }



    public function leaveHistory(Employee $employee)
    {
                $employee->load(['departmentRecord', 'leaveHistory.leaveBenefit']);

        return view('employees.leave-history', [
            'employee' => $employee,
            'benefits' => $employee->leaveHistory()
                ->with('leaveBenefit')
                ->latest()
                ->get(),
        ]);
    }


    public function uploadFile(Request $request, Employee $employee)
    {
        $fileTypes = [
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

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'file_type' => ['required', 'in:' . implode(',', $fileTypes)],
        ]);

        try {
            $file = $request->file('file');
            $fileId = (string) Str::uuid();
            $directory = "employee-files/{$employee->emp_no}";

            $storedPath = $file->storeAs(
                $directory,
                "{$fileId}.{$file->getClientOriginalExtension()}",
                'local'
            );

            $existingFile = EmployeeFile::where('emp_no', $employee->emp_no)
                ->where('file_type', $validated['file_type'])
                ->first();

            if ($existingFile) {
                Storage::disk('local')->delete($existingFile->file_url);
                $existingFile->update([
                    'file_name' => $file->getClientOriginalName(),
                    'file_url' => $storedPath,
                    'file_id' => $fileId,
                ]);
            } else {
                EmployeeFile::create([
                    'emp_no' => $employee->emp_no,
                    'file_type' => $validated['file_type'],
                    'file_name' => $file->getClientOriginalName(),
                    'file_url' => $storedPath,
                    'file_id' => $fileId,
                ]);
            }

            return back()->with('success', 'File uploaded successfully.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function uploadProfileImage(Request $request, Employee $employee)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
        ]);

        // Delete old image if exists
        if ($employee->profile_image) {
            Storage::disk('public')->delete($employee->profile_image);
        }

        // Store new image
        $path = $request->file('profile_image')->store('profile-images', 'public');

        $employee->profile_image = $path;
        $employee->save();

        // Sync the profile image to the associated user so the sidebar avatar reflects the same image
        $user = $employee->relationLoaded('user') && $employee->user
            ? $employee->user
            : User::where('emp_no', $employee->emp_no)->first();

        if ($user) {
            if ($user->profile_image && $user->profile_image !== $employee->getOriginal('profile_image')) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->profile_image = $path;
            $user->save();
        }

        return back()->with('success', 'Profile image updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;

        // Soft-delete by marking status as inactive (do not hard delete).
        // Use save() to avoid any mass-assignment ambiguity.
        $employee->status = 'inactive';
        $employee->save();

        return redirect()
            ->route('employees.index')
            ->with('success', "{$name} has been deactivated.");
    }
}
