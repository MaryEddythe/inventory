<?php

use App\Http\Controllers\InventoryItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inventory.index');
});

Route::resource('inventory', InventoryItemController::class);
Route::get('/inventory/dashboard', [InventoryItemController::class, 'dashboard'])
    ->name('inventory.tabs.dashboard');
Route::get('/users', [UserController::class, 'userManagement'])
    ->name('user.management');
Route::get('/inventory/export/{type}', [InventoryItemController::class, 'export'])->name('inventory.export');