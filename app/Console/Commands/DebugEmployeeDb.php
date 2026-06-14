<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugEmployeeDb extends Command
{
    protected $signature = 'debug:employees-db';
    protected $description = 'Debug which table/connection the Employee model writes to and reads from.';

    public function handle(): int
    {
        $this->info('Default DB connection: ' . config('database.default'));

        // Try to read a count from inventory.employees
        try {
            $count = DB::table('inventory.employees')->count();
            $this->info('inventory.employees count: ' . $count);
        } catch (\Throwable $e) {
            $this->error('Failed reading inventory.employees: ' . $e->getMessage());
        }

        try {
            $count2 = DB::table('employees')->count();
            $this->info('employees count: ' . $count2);
        } catch (\Throwable $e) {
            $this->error('Failed reading employees: ' . $e->getMessage());
        }

        // Dump the employment_type column definition (ENUM values)
        try {
            $row = DB::selectOne("SHOW COLUMNS FROM inventory.employees LIKE 'employment_type'");
            $this->info('employment_type column: ' . json_encode($row));
        } catch (\Throwable $e) {
            $this->error('Failed showing employment_type: ' . $e->getMessage());
        }


        return self::SUCCESS;
    }
}

