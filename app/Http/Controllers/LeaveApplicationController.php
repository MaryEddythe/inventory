<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeaveApplication;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLeaveBenefit;
use App\Models\EmployeeLeaveHistory;
use App\Models\Role;
use App\Models\User;
use App\Notifications\LeaveApplicationSubmittedNotification;
use App\Notifications\LeaveApplicationPendingReviewNotification;
use App\States\LeaveApplication\Approved;
use App\States\LeaveApplication\PendingDivisionChief;
use App\States\LeaveApplication\PendingHr;
use App\States\LeaveApplication\PendingRegionalDirector;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeaveApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = $user?->employee()->first();

        abort_unless($user && $employee, 403, 'No linked employee record found for this account.');

        $roleSlug = (string) ($user->role?->slug ?? '');
        $isApprover = $user->isSuperAdmin() || in_array($roleSlug, ['hr', 'rd', ...$this->divisionChiefRoleSlugs()], true);
        $pendingStatuses = $this->pendingStatusesForUser($user);
        $pendingApplicationsTitle = $this->pendingApplicationsTitleForUser($user);
        $pendingApplicationsSubtitle = $this->pendingApplicationsSubtitleForUser($user);

        $applications = EmployeeLeaveApplication::query()
            ->with([
                'employee.division',
                'hrSigner',
                'divisionChiefSigner',
                'regionalDirectorSigner',
            ])
            ->when($this->isDivisionChiefRoleSlug($roleSlug), function ($query) use ($user) {
                $deptNo = $this->divisionChiefDepartmentNoForUser($user);

                if ($deptNo !== null) {
                    $query->whereHas('employee', function ($employeeQuery) use ($deptNo) {
                        $employeeQuery->where('department', $deptNo);
                    });
                }
            })
            ->when(! $isApprover, function ($query) use ($employee) {
                $query->where('employee_id', $employee->emp_no);
            })
            ->latest()
            ->get();

        $pendingApplications = ! empty($pendingStatuses)
            ? EmployeeLeaveApplication::query()
                ->with([
                    'employee.division',
                'hrSigner',
                'divisionChiefSigner',
                'regionalDirectorSigner',
            ])
                ->when($this->isDivisionChiefRoleSlug($roleSlug), function ($query) use ($user) {
                    $deptNo = $this->divisionChiefDepartmentNoForUser($user);

                    if ($deptNo !== null) {
                        $query->whereHas('employee', function ($employeeQuery) use ($deptNo) {
                            $employeeQuery->where('department', $deptNo);
                        });
                    }
                })
                ->whereIn('status', $pendingStatuses)
                ->latest()
                ->get()
            : collect();

        return view('leave-applications.index', [
            'employee' => $employee,
            'applications' => $applications,
            'pendingApplications' => $pendingApplications,
            'pendingApplicationsTitle' => $pendingApplicationsTitle,
            'pendingApplicationsSubtitle' => $pendingApplicationsSubtitle,
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

    public function view(EmployeeLeaveApplication $leaveApplication)
    {
        $user = auth()->user();

        abort_unless($user, 403, 'You must be logged in to view this leave application.');

        // NOTE: $user->employee is the raw DB column value (string).
        // Use ->employee() relationship explicitly to get the Employee model.
        $employee = $user->employee()->first();

        $isOwner = $employee && (string) $employee->emp_no === (string) $leaveApplication->employee_id;
        $roleSlug = (string) ($user->role?->slug ?? '');
        $isGlobalApprover = $user->isSuperAdmin() || in_array($roleSlug, ['hr', 'rd'], true);
        $isAssignedDivisionChief = $this->isDivisionChiefRoleSlug($roleSlug)
            && $this->canViewLeaveApplicationAsApprover($user, $leaveApplication);

        abort_unless(
            $isOwner
            || $isGlobalApprover
            || $isAssignedDivisionChief,
            403,
            'You are not allowed to view this leave application.'
        );

        $leaveApplication->load([
            'employee.division',
            'employee.departmentRecord',
            'hrSigner',
            'divisionChiefSigner',
            'regionalDirectorSigner',
        ]);

        try {
            $view = $this->canonicalLeaveType((string) $leaveApplication->leave_type) === 'Credited Time-Off'
                ? 'leaves.cto-print'
                : 'leaves.print';
            $pdf = Pdf::loadView($view, [
                'leaveApplication' => $leaveApplication,
                'employee' => $leaveApplication->employee,
                'leavePrintCss' => File::exists(public_path('leave-application-print.css'))
                    ? File::get(public_path('leave-application-print.css'))
                    : '',
                'applicantSignaturePath' => $this->publicDiskPath($leaveApplication->applicant_signature_path),
                'hrSignaturePath' => $this->publicDiskPath($leaveApplication->hr_signature_path),
                'divisionChiefSignaturePath' => $this->publicDiskPath($leaveApplication->division_chief_signature_path),
                'regionalDirectorSignaturePath' => $this->publicDiskPath($leaveApplication->regional_director_signature_path),
            ])->setPaper('a4', 'portrait');

            return $pdf->stream($this->leaveApplicationFilename($leaveApplication));
        } catch (\Exception $e) {
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
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
            'cto_leave_history_id' => 'nullable|integer',
            'cto_duration' => 'nullable|in:am,pm,whole_day',
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

        $leaveType = $this->canonicalLeaveType($validated['leave_type']);
        $ctoHistory = null;
        if ($leaveType === 'Credited Time-Off') {
            $request->validate([
                'cto_leave_history_id' => 'required|integer',
                'cto_duration' => 'required|in:am,pm,whole_day',
            ]);

            $ctoHistory = EmployeeLeaveHistory::query()
                ->whereKey($validated['cto_leave_history_id'])
                ->where('emp_no', $employee->emp_no)
                ->where(function ($query) {
                    $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', ['credited time-off', 'credited time off'])
                        ->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
                })
                ->where('credits_added', '>', 0)
                ->first();

            if (! $ctoHistory) {
                return back()->withErrors(['cto_leave_history_id' => 'Please select an available CTO credit.'])->withInput();
            }

            $requestedHours = $this->ctoDurationHours($validated['cto_duration']);
            $availableHours = (int) EmployeeLeaveBenefit::query()
                ->where('emp_no', $employee->emp_no)
                ->where(function ($query) {
                    $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', ['credited time-off', 'credited time off'])
                        ->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
                })
                ->sum('credit_hours');

            if ($availableHours < $requestedHours) {
                return back()->withErrors(['cto_duration' => 'Insufficient CTO balance for the selected duration.'])->withInput();
            }
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
            'leave_type' => $leaveType,
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'cto_leave_history_id' => $ctoHistory?->id,
            'cto_remarks' => $ctoHistory?->remarks,
            'cto_duration' => $leaveType === 'Credited Time-Off' ? $validated['cto_duration'] : null,
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

        $this->notifyRoleUsers(
            roleSlugs: [$this->divisionChiefRoleSlugForEmployee($leaveApplication)],
            application: $leaveApplication,
            stepLabel: 'Division Chief',
            headline: 'New leave application awaiting Division Chief review',
            message: $leaveApplication->employee?->full_name . ' signed a leave application. It is now pending Division Chief approval.'
        );

        return back()->with('success', 'Leave application signed by HR.');
    }

    public function signDivisionChief(Request $request, EmployeeLeaveApplication $leaveApplication)
    {
        $user = $request->user();

        abort_unless($user && ($user->isSuperAdmin() || $this->isDivisionChiefRoleSlug((string) ($user->role?->slug ?? ''))), 403, 'Only the Division Chief can sign this leave application.');

        $request->validate([
            'current_password' => 'required|string',
        ]);

        if ((string) $leaveApplication->status !== 'pending_division_chief') {
            return back()->withErrors([
                'current_password' => 'This leave application is no longer waiting for Division Chief signature.',
            ])->with('division_chief_sign_leave_id', $leaveApplication->id);
        }

        $targetDivisionChiefSlug = $this->divisionChiefRoleSlugForEmployee($leaveApplication);

        if (! $user->isSuperAdmin() && (string) ($user->role?->slug ?? '') !== $targetDivisionChiefSlug) {
            abort(403, 'This leave application is assigned to a different division chief.');
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => 'The password you entered does not match your login password.',
                ])
                ->withInput()
                ->with('division_chief_sign_leave_id', $leaveApplication->id);
        }

        if (! $user->signature_path) {
            return back()
                ->withErrors([
                    'signature_path' => 'Please upload your signature in your profile before signing leave applications.',
                ])
                ->withInput()
                ->with('division_chief_sign_leave_id', $leaveApplication->id);
        }

        $leaveApplication->forceFill([
            'division_chief_signed_by' => $user->id,
            'division_chief_signed_at' => now(),
            'division_chief_signature_path' => $user->signature_path,
            'current_step' => 'regional_director',
            'status' => PendingRegionalDirector::class,
        ])->save();

        activity('leave-applications')
            ->causedBy($user)
            ->performedOn($leaveApplication)
            ->withProperties([
                'employee_id' => $leaveApplication->employee_id,
                'signed_by' => $user->id,
                'step' => 'division_chief',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('division chief signed leave application');

        $this->notifyRoleUsers(
            roleSlugs: ['rd'],
            application: $leaveApplication,
            stepLabel: 'Regional Director',
            headline: 'New leave application awaiting Regional Director review',
            message: $leaveApplication->employee?->full_name . ' signed a leave application. It is now pending Regional Director approval.'
        );

        return back()->with('success', 'Leave application signed by the Division Chief.');
    }

    public function signRegionalDirector(Request $request, EmployeeLeaveApplication $leaveApplication)
    {
        $user = $request->user();

        abort_unless($user && ($user->isSuperAdmin() || $user->role?->slug === 'rd'), 403, 'Only the Regional Director can sign this leave application.');

        $request->validate([
            'current_password' => 'required|string',
        ]);

        if ((string) $leaveApplication->status !== 'pending_regional_director') {
            return back()->withErrors([
                'current_password' => 'This leave application is no longer waiting for Regional Director signature.',
            ])->with('regional_director_sign_leave_id', $leaveApplication->id);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => 'The password you entered does not match your login password.',
                ])
                ->withInput()
                ->with('regional_director_sign_leave_id', $leaveApplication->id);
        }

        if (! $user->signature_path) {
            return back()
                ->withErrors([
                    'signature_path' => 'Please upload your signature in your profile before signing leave applications.',
                ])
                ->withInput()
                ->with('regional_director_sign_leave_id', $leaveApplication->id);
        }

        DB::transaction(function () use ($leaveApplication, $user) {
            $leaveApplication->forceFill([
                'regional_director_signed_by' => $user->id,
                'regional_director_signed_at' => now(),
                'regional_director_signature_path' => $user->signature_path,
                'current_step' => 'approved',
                'status' => Approved::class,
            ])->save();

            $this->deductLeaveBenefitForApplication($leaveApplication);
        });

        activity('leave-applications')
            ->causedBy($user)
            ->performedOn($leaveApplication)
            ->withProperties([
                'employee_id' => $leaveApplication->employee_id,
                'signed_by' => $user->id,
                'step' => 'regional_director',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('regional director signed leave application');

        return back()->with('success', 'Leave application approved by the Regional Director.');
    }

    protected function pendingStatusesForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->isSuperAdmin()) {
            return [
                PendingHr::class,
                'pending_hr',
                PendingDivisionChief::class,
                'pending_division_chief',
                PendingRegionalDirector::class,
                'pending_regional_director',
            ];
        }

        $roleSlug = (string) ($user->role?->slug ?? '');

        if ($roleSlug === 'hr') {
            return [PendingHr::class, 'pending_hr'];
        }

        if ($this->isDivisionChiefRoleSlug($roleSlug)) {
            return [PendingDivisionChief::class, 'pending_division_chief'];
        }

        if ($roleSlug === 'rd') {
            return [PendingRegionalDirector::class, 'pending_regional_director'];
        }

        return [];
    }

    protected function pendingApplicationsTitleForUser(?User $user): string
    {
        if (! $user || $user->isSuperAdmin()) {
            return 'Pending Leave Applications';
        }

        $roleSlug = (string) ($user->role?->slug ?? '');

        if ($roleSlug === 'hr') {
            return 'Pending Leave Applications for HR';
        }

        if ($this->isDivisionChiefRoleSlug($roleSlug)) {
            return 'Pending Leave Applications for ' . $this->divisionChiefRoleLabelForSlug($roleSlug);
        }

        if ($roleSlug === 'rd') {
            return 'Pending Leave Applications for Regional Director';
        }

        return 'Pending Leave Applications';
    }

    protected function pendingApplicationsSubtitleForUser(?User $user): string
    {
        if (! $user) {
            return 'Leave requests waiting for approval.';
        }

        if ($user->isSuperAdmin()) {
            return 'All leave requests waiting for any approval stage.';
        }

        $roleSlug = (string) ($user->role?->slug ?? '');

        if ($roleSlug === 'hr') {
            return 'Leave requests waiting for HR signature.';
        }

        if ($this->isDivisionChiefRoleSlug($roleSlug)) {
            return 'Leave requests waiting for ' . $this->divisionChiefRoleLabelForSlug($roleSlug) . ' signature.';
        }

        if ($roleSlug === 'rd') {
            return 'Leave requests waiting for Regional Director signature.';
        }

        return 'Leave requests waiting for approval.';
    }

    protected function notifyRoleUsers(array $roleSlugs, EmployeeLeaveApplication $application, string $stepLabel, string $headline, string $message): void
    {
        User::query()
            ->whereHas('role', function ($query) use ($roleSlugs) {
                $query->whereIn('slug', $roleSlugs);
            })
            ->distinct()
            ->get()
            ->each(function (User $user) use ($application, $stepLabel, $headline, $message) {
                $user->notify(new LeaveApplicationPendingReviewNotification(
                    application: $application,
                    stepLabel: $stepLabel,
                    headline: $headline,
                    message: $message,
                ));
            });
    }

    protected function deductLeaveBenefitForApplication(EmployeeLeaveApplication $application): void
    {
        $application->loadMissing('employee.division');

        $employee = $application->employee;

        if (! $employee) {
            return;
        }

        $leaveType = $this->canonicalLeaveType((string) $application->leave_type);
        $hoursUsed = $this->leaveApplicationHours($application);

        if ($hoursUsed <= 0) {
            return;
        }

        $isCto = $leaveType === 'Credited Time-Off';
        $currentBalance = (int) EmployeeLeaveBenefit::query()
            ->where('emp_no', $employee->emp_no)
            ->whereRaw('LOWER(TRIM(credit_type)) = ?', [strtolower($leaveType)])
            ->sum('credit_hours');

        if ($isCto && $currentBalance < $hoursUsed) {
            throw new \RuntimeException('The employee no longer has enough CTO hours to approve this request.');
        }

        $creditHours = $isCto ? -$hoursUsed : $hoursUsed;
        $hoursRemaining = $isCto
            ? max(0, $currentBalance - $hoursUsed)
            : max(0, $this->annualLeaveHours($leaveType) - ($currentBalance + $hoursUsed));

        $benefit = EmployeeLeaveBenefit::create([
            'emp_no' => $employee->emp_no,
            'name' => $employee->full_name,
            'departments' => optional($employee->division)->code ?? optional($employee->division)->department ?? 'N/A',
            'role' => $employee->position ?? 'N/A',
            'employment_type' => $employee->employment_type ?? 'N/A',
            'credit_type' => $leaveType,
            'start_date' => $application->date_from,
            'end_date' => $application->date_to,
            'date_applied' => $application->created_at?->toDateString() ?? now()->toDateString(),
            'date_approved' => now()->toDateString(),
            'credit_hours' => $creditHours,
            'hours_used' => $hoursUsed,
            'hours_remaining' => $hoursRemaining,
            'status' => 'ACTIVE',
            'remarks' => 'Deducted from approved leave application #' . $application->id
                . ($application->cto_remarks ? ' (CTO source: ' . $application->cto_remarks . ')' : ''),
        ]);

        EmployeeLeaveHistory::create([
            'emp_no' => $employee->emp_no,
            'leave_benefit_id' => $benefit->id,
            'credit_type' => $leaveType,
            'credits_added' => 0,
            'hours_used' => $hoursUsed,
            'hours_remaining' => $hoursRemaining,
            'remarks' => 'Approved leave application #' . $application->id
                . ($application->cto_remarks ? ' (CTO source: ' . $application->cto_remarks . ')' : ''),
        ]);
    }

    protected function leaveApplicationHours(EmployeeLeaveApplication $application): int
    {
        if ($this->canonicalLeaveType((string) $application->leave_type) === 'Credited Time-Off') {
            return $this->ctoDurationHours((string) $application->cto_duration);
        }

        $start = Carbon::parse($application->date_from);
        $end = $application->date_to ? Carbon::parse($application->date_to) : $start;

        return ((int) $start->diffInDays($end) + 1) * 10;
    }

    protected function ctoDurationHours(string $duration): int
    {
        return match ($duration) {
            'am' => 4,
            'pm' => 6,
            'whole_day' => 10,
            default => 0,
        };
    }

    protected function canonicalLeaveType(string $leaveType): string
    {
        $type = strtolower(trim($leaveType));

        return match (true) {
            $type === 'credited time-off',
            $type === 'credited time off',
            str_contains($type, 'cto') => 'Credited Time-Off',
            $type === 'vacation leave' => 'Vacation Leave',
            $type === 'sick leave' => 'Sick Leave',
            $type === 'wellness leave' => 'Wellness Leave',
            $type === 'special privilege leave' => 'Special Privilege Leave',
            $type === 'maternity leave' => 'Maternity Leave',
            $type === 'paternity leave' => 'Paternity Leave',
            $type === 'solo parent leave' => 'Solo Parent Leave',
            $type === 'rehabilitation leave' => 'Rehabilitation Leave',
            $type === 'special emergency leave' => 'Special Emergency Leave',
            default => trim($leaveType),
        };
    }

    protected function annualLeaveHours(string $leaveType): int
    {
        return match ($leaveType) {
            'Vacation Leave',
            'Sick Leave' => 150,
            'Wellness Leave',
            'Special Emergency Leave' => 50,
            'Special Privilege Leave' => 30,
            'Maternity Leave' => 1050,
            'Paternity Leave',
            'Solo Parent Leave' => 70,
            default => 0,
        };
    }

    protected function publicDiskPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->path($path)
            : null;
    }

    protected function canViewLeaveApplicationAsApprover(User $user, EmployeeLeaveApplication $leaveApplication): bool
    {
        if (! $this->isDivisionChiefRoleSlug((string) ($user->role?->slug ?? ''))) {
            return false;
        }

        return $this->divisionChiefRoleSlugForEmployee($leaveApplication) === (string) ($user->role?->slug ?? '');
    }

    protected function divisionChiefRoleSlugs(): array
    {
        return collect($this->divisionChiefRoleMap())
            ->pluck('slug')
            ->values()
            ->all();
    }

    protected function divisionChiefRoleMap(): array
    {
        $fallback = [
            1 => ['slug' => 'division-chief-fad', 'label' => 'FAD Division Chief'],
            3 => ['slug' => 'division-chief-ord', 'label' => 'ORD Division Chief'],
            4 => ['slug' => 'division-chief-msesdd', 'label' => 'MSESDD Division Chief'],
            5 => ['slug' => 'division-chief-gd', 'label' => 'GD Division Chief'],
            6 => ['slug' => 'division-chief-mmd', 'label' => 'MMD Division Chief'],
        ];

        if (
            ! Schema::connection('inventory')->hasTable('departments')
            || ! Schema::connection('inventory')->hasColumn('departments', 'division_chief_role_id')
        ) {
            return $fallback;
        }

        $departments = Department::query()
            ->whereNotNull('division_chief_role_id')
            ->get(['dept_no', 'division_chief_role_id']);

        $roles = Role::query()
            ->whereIn('id', $departments->pluck('division_chief_role_id')->filter()->unique()->values())
            ->get(['id', 'name', 'slug'])
            ->keyBy('id');

        foreach ($departments as $department) {
            $role = $roles->get($department->division_chief_role_id);

            if ($role) {
                $fallback[(int) $department->dept_no] = [
                    'slug' => $role->slug,
                    'label' => $role->name,
                ];
            }
        }

        return $fallback;
    }

    protected function divisionChiefRoleSlugForEmployee(EmployeeLeaveApplication $leaveApplication): string
    {
        $deptNo = (int) ($leaveApplication->employee?->department ?? 1);

        return $this->divisionChiefRoleMap()[$deptNo]['slug'] ?? 'division-chief';
    }

    protected function divisionChiefDepartmentNoForUser(?User $user): ?int
    {
        $slug = (string) ($user?->role?->slug ?? '');

        foreach ($this->divisionChiefRoleMap() as $deptNo => $role) {
            if ($role['slug'] === $slug) {
                return (int) $deptNo;
            }
        }

        return null;
    }

    protected function divisionChiefRoleLabelForSlug(string $slug): string
    {
        foreach ($this->divisionChiefRoleMap() as $role) {
            if ($role['slug'] === $slug) {
                return $role['label'];
            }
        }

        return 'Division Chief';
    }

    protected function isDivisionChiefRoleSlug(?string $slug): bool
    {
        return in_array((string) $slug, $this->divisionChiefRoleSlugs(), true);
    }

    protected function leaveApplicationFilename(EmployeeLeaveApplication $leaveApplication): string
    {
        $employeeName = $leaveApplication->employee?->full_name ?: 'leave-application';
        $date = $leaveApplication->created_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        return Str::slug($employeeName . '-' . $date) . '.pdf';
    }
}
