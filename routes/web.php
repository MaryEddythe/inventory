<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryItemController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('inventory.dashboard');
    });

    Route::get('/dashboard', [InventoryItemController::class, 'dashboard'])->name('inventory.dashboard');
    Route::put('/inventory/{inventory}', [InventoryItemController::class, 'update'])->name('inventory.update');
    Route::resource('inventory', InventoryItemController::class);
    Route::get('/inventory/dashboard', [InventoryItemController::class, 'dashboard'])
        ->name('inventory.tabs.dashboard');
    
    // Inventory Category Tabs
    Route::get('/inventory/tabs/moto-vehicle', function() {
        return view('inventory.tabs.moto-vehicle');
    })->name('inventory.tabs.moto-vehicle');
    Route::get('/inventory/tabs/cip', function() {
        return view('inventory.tabs.cip');
    })->name('inventory.tabs.cip');
    Route::get('/inventory/tabs/machine-equipment', function() {
        return view('inventory.tabs.machine-equipment');
    })->name('inventory.tabs.machine-equipment');
    Route::get('/inventory/tabs/office-equipment', function() {
        return view('inventory.tabs.office-equipment');
    })->name('inventory.tabs.office-equipment');
    Route::get('/inventory/tabs/technical-scientific-equipment', function() {
        return view('inventory.tabs.technical-scientific-equipment');
    })->name('inventory.tabs.technical-scientific-equipment');
    Route::get('/inventory/tabs/other-ppe', function() {
        return view('inventory.tabs.other-ppe');
    })->name('inventory.tabs.other-ppe');
    
    Route::get('/ipm', [InventoryItemController::class, 'ipm'])->name('inventory.ipm');
    Route::get('/icm', [InventoryItemController::class, 'icm'])->name('inventory.icm');
    Route::get('/inventory/export/{type}', [InventoryItemController::class, 'export'])->name('inventory.export');

    // API Routes for ICM - Keep only one version
    Route::get('/api/search-employees', [InventoryItemController::class, 'searchEmployees'])->name('api.search-employees');
    Route::get('/api/items-by-personnel', [InventoryItemController::class, 'getItemsByPersonnel'])->name('api.items-by-personnel');
    Route::get('/api/item-details/{itemId}', [InventoryItemController::class, 'getItemDetails'])->name('api.item-details');

    // Profile Routes
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.change-password');
});