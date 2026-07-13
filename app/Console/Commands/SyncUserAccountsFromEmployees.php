<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncUserAccountsFromEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-from-employees {--default-password=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing users accounts from inventory.employees using emp_no. Only creates users for employees with a non-null email.';

    public function handle(): int
    {
        $defaultPassword = (string) $this->option('default-password');
        if ($defaultPassword === '') {
            // No hardcoded credentials.
            // If no override exists in inventory.users (or inventory_users_override),
            // we will skip setting password and leave it for the admin to set manually.
            $defaultPassword = null;
        }


        $dryRun = (bool) $this->option('dry-run');

        $this->info('Syncing user accounts from employees...');
        $this->info('Default password: ' . ($dryRun ? '*** (dry-run)' : $defaultPassword));

        $created = 0;
        $skipped = 0;
        $updated = 0;

        $employees = Employee::query()
            ->select(['emp_no', 'email'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($employees as $employee) {
            $empNo = (string) $employee->emp_no;
            $email = (string) $employee->email;

            if (!Str::contains($email, '@')) {
                $skipped++;
                continue;
            }

            $user = User::query()->where('emp_no', $empNo)->first();

            if (!$user) {
                $user = new User();
                $user->emp_no = $empNo;
                $user->employee = $empNo; // keep consistent with your design
                $user->name = $employee->full_name ?? $empNo;
                $user->email = $email;

                // Password handling:
                // - For normal sync: use the command option `--default-password`.
                // - For any special credential overrides, store them in `inventory.users`.

                $passwordToUse = $defaultPassword;

                // If user exists in inventory.users override table, apply its password/email
                // (this command is syncing from inventory.employees into public users).
                try {
                    // Preferred: inventory schema override table
                    $overrideRow = \Illuminate\Support\Facades\DB::table('inventory.users')
                        ->where('emp_no', $empNo)
                        ->first();

                    // Fallback: override table created via migration
                    // (non-schema-specific table name)
                    if (!$overrideRow) {
                        $overrideRow = \Illuminate\Support\Facades\DB::table('inventory_users_override')
                            ->where('emp_no', $empNo)
                            ->first();
                    }

                    // Another fallback: if migration was created as `inventory.users_override`
                    // or with a different naming convention.
                    // Keep silent if it doesn't exist.
                    if (!$overrideRow) {
                        $overrideRow = \Illuminate\Support\Facades\DB::table('users_override')
                            ->where('emp_no', $empNo)
                            ->first();
                    }



                    if ($overrideRow) {
                        if (!empty($overrideRow->email)) {
                            $user->email = $overrideRow->email;
                        }

                        // Assume `password` in override table is already bcrypt-hashed.
                        if (!empty($overrideRow->password)) {
                            $user->password = $overrideRow->password;
                        } elseif (!empty($passwordToUse)) {
                            $user->password = bcrypt($passwordToUse);
                        }
                    } else {
                        if (!empty($passwordToUse)) {
                            $user->password = bcrypt($passwordToUse);
                        }
                    }

                } catch (\Throwable $e) {
                    // If override table doesn't exist yet, fallback to default password.
                    $user->password = bcrypt($passwordToUse);
                }



                if ($dryRun) {
                    $this->line("[DRY] Create user emp_no={$empNo}, email={$email}");
                    $created++;
                    continue;
                }

                $user->save();
                $created++;
                continue;
            }


            // If user exists but email/name empty, patch it.
            $needsUpdate = false;
            if (empty($user->email) && !empty($email)) {
                $user->email = $email;
                $needsUpdate = true;
            }
            if (empty($user->name) && !empty($employee->full_name)) {
                $user->name = $employee->full_name;
                $needsUpdate = true;
            }
            if (empty($user->employee) && !empty($empNo)) {
                $user->employee = $empNo;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                if ($dryRun) {
                    $this->line("[DRY] Update user emp_no={$empNo}");
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
        return 0;
    }
}

