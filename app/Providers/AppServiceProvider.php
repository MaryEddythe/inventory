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

