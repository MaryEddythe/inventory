<?php

namespace App\Http\Controllers;

use App\Jobs\CreateEmployeeDriveFolder;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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



        // Dispatch job to create Drive folder
        CreateEmployeeDriveFolder::dispatch($employee);

        // Also persist the latest known folder URLs for this employee (if job/controller sets them).
        // The `drive` column is intended to store *all* links created on employee creation.
        // Since the folder creation logic lives in CreateEmployeeDriveFolder, this is a best-effort fallback.
        try {
            $employee->refresh();

            $links = [];
            if ($employee->drive_folder_url) {
                $links[] = $employee->drive_folder_url;
            }

            $employee->drive = $links ? json_encode($links) : null;
            $employee->save();
        } catch (\Throwable $e) {
            // Ignore; job will still set drive_folder_url and the UI can retry later.
        }

        return redirect()->route('employees.show', $employee)
            ->with('status', 'Employee created successfully. Drive folder is being created...');
    }

    public function show(Employee $employee)
    {
        $employee->load(['division', 'leaveBenefits']);

        $leaveApplications = $employee->leaveApplications()
            ->with([
                'hrSigner',
                'divisionChiefSigner',
                'regionalDirectorSigner',
            ])
            ->latest()
            ->get();

        return view('employees.show', compact('employee', 'leaveApplications'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('division');
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
        $employee->load(['division', 'leaveHistory.leaveBenefit']);

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
        $request->validate([
'file' => 'required|file|max:20480', // 20MB
            'file_type' => 'required|in:PDS,SALN,"NBI Clearance","Medical Certificate","PAG-IBIG","PhilHealth",PAG-IBIG,PhilHealth',
        ]);


        if (!$employee->drive_folder_id) {
            return back()->with(
                'error',
                'Google Drive folder is not ready yet. Please wait a few seconds and try again.'
            );
        }

        try {

            $uploader = new DriveUploadService();

            $result = $uploader->uploadToEmployeeFolder(
                $request->file('file'),
                $employee
            );

            EmployeeFile::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'file_type' => $request->input('file_type'),
                ],
                [
                    'file_name' => $result['file_name'] ?? $request->file('file')->getClientOriginalName(),
                    'file_url' => $result['file_url'] ?? null,
                    'file_id' => $result['file_id'] ?? null,
                ]
            );

            return back()->with(
                'success',
                "File uploaded successfully to Google Drive!"
            );


        } catch (\Exception $e) {

            return back()->with(
                'error',
                'Upload failed: ' . $e->getMessage()
            );
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
