<?php

namespace App\Providers;

use App\Models\EmployeeLeaveApplication;
use App\Observers\EmployeeLeaveApplicationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EmployeeLeaveApplication::observe(EmployeeLeaveApplicationObserver::class);
    }
}
