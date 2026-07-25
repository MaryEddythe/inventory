<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaveApplication;
use App\Models\User;
use App\Notifications\LeaveApplicationSubmittedNotification;
use App\States\LeaveApplication\PendingDivisionChief;
use App\States\LeaveApplication\PendingHr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LeaveApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = $user?->employee()->first();

        abort_unless($user && $employee, 403, 'No linked employee record found for this account.');

        $isHrOrAdmin = $user->isSuperAdmin() || $user->role?->slug === 'hr';

        $applications = EmployeeLeaveApplication::query()
            ->with([
                'employee.division',
                'hrSigner',
                'divisionChiefSigner',
                'regionalDirectorSigner',
            ])
            ->when(! $isHrOrAdmin, function ($query) use ($employee) {
                $query->where('employee_id', $employee->emp_no);
            })
            ->latest()
            ->get();

        $pendingApplications = $isHrOrAdmin
            ? EmployeeLeaveApplication::query()
                ->with([
                    'employee.division',
                    'hrSigner',
                    'divisionChiefSigner',
                    'regionalDirectorSigner',
                ])
                ->whereIn('status', [PendingHr::class, 'pending_hr'])
                ->latest()
                ->get()
            : collect();

        return view('leave-applications.index', [
            'employee' => $employee,
            'applications' => $applications,
            'pendingApplications' => $pendingApplications,
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
        abort_unless($user, 403, 'You must be logged in to submit a leave application.');

        $validated = $request->validate([
            'employee_id' => 'required|exists:inventory.employees,emp_no',
            'leave_type' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'reason' => 'nullable|string|max:2000',
            'signature_mode' => 'required|in:saved,upload',
            'signature_path' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'current_password' => 'required|string',
        ]);

        $employee = Employee::query()->where('emp_no', $validated['employee_id'])->firstOrFail();
        $linkedEmployee = $user->employee()->first();

        if (
            $linkedEmployee
            && (string) $linkedEmployee->emp_no !== (string) $employee->emp_no
            && ! $user->isSuperAdmin()
            && $user->role?->slug !== 'hr'
        ) {
            abort(403, 'You can only submit a leave application for your own employee record.');
        }

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => 'The password you entered does not match your login password.',
                ])
                ->withInput();
        }

        $signaturePath = $user->signature_path;

        if ($validated['signature_mode'] === 'upload') {
            if (! $request->hasFile('signature_path')) {
                return back()
                    ->withErrors([
                        'signature_path' => 'Please upload your signature image.',
                    ])
                    ->withInput();
            }

            if ($signaturePath) {
                Storage::disk('public')->delete($signaturePath);
            }

            $signaturePath = $request->file('signature_path')->store('signatures', 'public');

            $user->update([
                'signature_path' => $signaturePath,
            ]);
        }

        if (! $signaturePath) {
            return back()
                ->withErrors([
                    'signature_mode' => 'Please save or upload your signature before submitting the leave application.',
                ])
                ->withInput();
        }

        $application = EmployeeLeaveApplication::create([
            'employee_id' => $employee->emp_no,
            'leave_type' => $validated['leave_type'],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'applicant_signature_path' => $signaturePath,
            'applicant_signed_at' => now(),
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

        User::query()
            ->where('role_id', 4)
            ->orWhereHas('role', function ($query) {
                $query->where('slug', 'hr');
            })
            ->distinct()
            ->get()
            ->each(fn (User $hrUser) => $hrUser->notify(new LeaveApplicationSubmittedNotification($application)));

        return back()->with('success', 'Leave application submitted successfully.');
    }

    public function signHr(Request $request, EmployeeLeaveApplication $leaveApplication)
    {
        $user = $request->user();

        abort_unless($user && ($user->isSuperAdmin() || $user->role?->slug === 'hr'), 403, 'Only HR can sign this leave application.');

        $request->validate([
            'current_password' => 'required|string',
        ]);

        if ((string) $leaveApplication->status !== 'pending_hr') {
            return back()->withErrors([
                'current_password' => 'This leave application is no longer waiting for HR signature.',
            ])->with('hr_sign_leave_id', $leaveApplication->id);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => 'The password you entered does not match your login password.',
                ])
                ->withInput()
                ->with('hr_sign_leave_id', $leaveApplication->id);
        }

        if (! $user->signature_path) {
            return back()
                ->withErrors([
                    'signature_path' => 'Please upload your signature in your profile before signing leave applications.',
                ])
                ->withInput()
                ->with('hr_sign_leave_id', $leaveApplication->id);
        }

        $leaveApplication->forceFill([
            'hr_signed_by' => $user->id,
            'hr_signed_at' => now(),
            'hr_signature_path' => $user->signature_path,
            'current_step' => 'division_chief',
            'status' => PendingDivisionChief::class,
        ])->save();

        activity('leave-applications')
            ->causedBy($user)
            ->performedOn($leaveApplication)
            ->withProperties([
                'employee_id' => $leaveApplication->employee_id,
                'signed_by' => $user->id,
                'step' => 'hr',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('hr signed leave application');

        return back()->with('success', 'Leave application signed by HR.');
    }
}
