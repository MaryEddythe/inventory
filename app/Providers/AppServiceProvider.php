<?php

namespace App\Providers;

use App\Models\EmployeeLeaveApplication;
use App\Observers\EmployeeLeaveApplicationObserver;
use Illuminate\Support\Facades\View;
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
        // Override mail driver to log to avoid Docker dependency on mailpit
        $this->app->make('config')->set('mail.default', 'log');

        EmployeeLeaveApplication::observe(EmployeeLeaveApplicationObserver::class);

        // Share notification data so it's available when layout.app is rendered
        // via @extends('layout.app') from any child view
        View::composer('layout.app', function ($view) {
            $headerUser = auth()->user();
            $view->with('headerNotifications', $headerUser?->notifications()->latest()->take(5)->get() ?? collect())
                 ->with('headerUnreadCount', $headerUser?->unreadNotifications()->count() ?? 0);
        });
    }
}

