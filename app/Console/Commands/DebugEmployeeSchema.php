<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugEmployeeSchema extends Command
{
    protected $signature = 'debug:employee-schema';
    protected $description = 'Print column names and full definition of inventory.employees.employment_type (if present).';

    public function handle(): int
    {
        $this->info('Default DB connection: ' . config('database.default'));

        $columns = DB::select("SHOW COLUMNS FROM inventory.employees");
        $this->info('Columns:');
        foreach ($columns as $c) {
            $this->line(' - ' . $c->Field . ' (' . $c->Type . ')');
        }

        foreach ($columns as $c) {
            if (strtolower($c->Field) === 'employment_type') {
                $this->info('employment_type raw Type: ' . $c->Type);
            }
        }

        return self::SUCCESS;
    }
}

