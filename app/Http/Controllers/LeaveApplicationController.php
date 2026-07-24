<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeaveApplication;
use App\States\LeaveApplication\PendingHr;
use Illuminate\Http\Request;

class LeaveApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = $user?->employee;

        abort_unless($user && $employee, 403, 'No linked employee record found for this account.');

        $applications = EmployeeLeaveApplication::query()
            ->with('employee.division')
            ->when(! $user->isSuperAdmin() && $user->role?->slug !== 'hr', function ($query) use ($employee) {
                $query->where('employee_id', $employee->emp_no);
            })
            ->latest()
            ->get();

        return view('leave-applications.index', [
            'employee' => $employee,
            'applications' => $applications,
            'leaveTypes' => [
                'Vacation Leave',
                'Sick Leave',
                'Wellness Leave',
                'Special Privilege Leave',
                'Maternity Leave',
                'Paternity Leave',
                'Solo Parent Leave',
                'Rehabilitation Leave',
                'Special Emergency Leave',
                'Credited Time-Off',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        abort_unless($user && $employee, 403, 'No linked employee record found for this account.');

        $validated = $request->validate([
            'leave_type' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'reason' => 'nullable|string|max:2000',
        ]);

        $application = EmployeeLeaveApplication::create([
            'employee_id' => $employee->emp_no,
            'leave_type' => $validated['leave_type'],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'status' => PendingHr::class,
            'current_step' => 'hr',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        activity('leave-applications')
            ->causedBy($user)
            ->performedOn($application)
            ->withProperties([
                'employee_id' => $employee->emp_no,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('leave application submitted');

        return back()->with('success', 'Leave application submitted successfully.');
    }
}
