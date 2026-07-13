<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUserAccountsFromEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-from-employees
        {--emp-no= : Sync only one employee number}
        {--email= : Force the user email to this value}
        {--default-password= : Password to hash for created users}
        {--dry-run : Show what would change without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update user accounts from inventory.employees using emp_no.';

    public function handle(): int
    {
        $defaultPassword = (string) $this->option('default-password');
        $defaultPassword = $defaultPassword !== '' ? $defaultPassword : null;

        $forcedEmail = trim((string) $this->option('email'));
        $empNoFilter = trim((string) $this->option('emp-no'));
        $dryRun = (bool) $this->option('dry-run');

        $query = Employee::query()->select([
            'emp_no',
            'firstname',
            'lastname',
        ]);

        if ($empNoFilter !== '') {
            $query->where('emp_no', $empNoFilter);
        }

        $employees = $query->get();

        $this->info('Syncing user accounts from employees...');
        $this->info('Dry run: ' . ($dryRun ? 'yes' : 'no'));
        $this->info('Forced email: ' . ($forcedEmail !== '' ? $forcedEmail : '(none)'));

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            $empNo = (string) $employee->emp_no;
            $name = trim(($employee->firstname ?? '') . ' ' . ($employee->lastname ?? '')) ?: 'Employee ' . $empNo;
            $email = $forcedEmail !== '' ? $forcedEmail : null;

            if ($email === null) {
                $this->warn("Skipping emp_no={$empNo} because no email was supplied and inventory.employees has no email column.");
                $skipped++;
                continue;
            }

            $user = User::query()
                ->where('emp_no', $empNo)
                ->orWhere('email', $email)
                ->first();

            if (!$user) {
                $user = new User();
                $user->emp_no = $empNo;
                $user->employee = $empNo;
                $user->name = $name;
                $user->email = $email;
                if ($defaultPassword !== null) {
                    $user->password = $defaultPassword;
                }

                if ($dryRun) {
                    $this->line("[DRY] create user emp_no={$empNo}, name={$name}, email={$email}");
                    $created++;
                    continue;
                }

                $user->save();
                $created++;
                continue;
            }

            $needsUpdate = false;

            if ($user->name !== $name) {
                $user->name = $name;
                $needsUpdate = true;
            }

            if ($user->email !== $email) {
                $user->email = $email;
                $needsUpdate = true;
            }

            if ($user->emp_no !== $empNo) {
                $user->emp_no = $empNo;
                $needsUpdate = true;
            }

            if (($user->employee ?? null) !== $empNo) {
                $user->employee = $empNo;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                if ($dryRun) {
                    $this->line("[DRY] update user emp_no={$empNo}, name={$name}, email={$email}");
                    $updated++;
                    continue;
                }

                $user->save();
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->info("Done. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
