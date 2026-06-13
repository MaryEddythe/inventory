<?php

namespace App\Http\Controllers;

use App\Jobs\CreateEmployeeDriveFolder;
use App\Models\Employee;
use App\Models\Division;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{

    public function index()
    {
        $employees = Employee::orderByDesc('emp_no')->paginate(15);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $divisions = \App\Models\Division::latest()->get();
        return view('employees.create', compact('divisions'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:employees',
            'division_id'     => 'required|exists:divisions,id',
            'position'        => 'required|string|max:255',
            'employment_type' => 'required|in:PERMANENT,COS',
            'hired_at'        => 'required|date',
        ]);

        // Generate unique employee_id server-side
        $lastEmployee = Employee::orderByDesc('emp_no')->first();
        $nextNumber = ($lastEmployee ? (int)substr((string) $lastEmployee->employee_id, 4) : 0) + 1;
        $validated['employee_id'] = 'EMP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Create employee
        $employee = Employee::create($validated);

        // Dispatch job to create Drive folder
        CreateEmployeeDriveFolder::dispatch($employee);

        return redirect()->route('employees.show', $employee)
            ->with('status', 'Employee created successfully. Drive folder is being created...');
    }

    public function show(Employee $employee)
    {
        $employee->load(['division', 'leaveBenefits']);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('division');
        $divisions = \App\Models\Division::latest()->get();

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
            'email'           => 'required|email|max:255|unique:employees,email,' . $employee->emp_no,
            'division_id'     => 'required|exists:divisions,id',
            'position'        => 'required|string|max:100',
            'employment_type' => 'required|in:COS,PERMANENT',
            'hired_at'        => 'required|date',
        ]);

        $employee->update($validated);

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

    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;

        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', "{$name} has been deleted.");
    }
}
