<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreditsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventController;
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

    Route::resource('employees', EmployeeController::class);
    Route::put('/inventory/{inventory}', [InventoryItemController::class, 'update'])->name('inventory.update');
    Route::resource('inventory', InventoryItemController::class);
    Route::get('/inventory/dashboard', [InventoryItemController::class, 'dashboard'])
        ->name('inventory.tabs.dashboard');
    
    // Inventory Category Tabs
    Route::get('/inventory/tabs/moto-vehicle', function() {
        $motorVehicles = \App\Models\MotorVehicle::latest()->get();

        return view('inventory.tabs.moto-vehicle', compact('motorVehicles'));
    })->name('inventory.tabs.moto-vehicle');
    Route::get('/inventory/tabs/cip', function() {
        $cips = \App\Models\Cip::latest()->get();

        return view('inventory.tabs.cip', compact('cips'));
    })->name('inventory.tabs.cip');
    Route::get('/inventory/tabs/machine-equipment', function() {
        $machineEquipments = \App\Models\MachineEquipment::latest()->get();

        return view('inventory.tabs.machine-equipment', compact('machineEquipments'));
    })->name('inventory.tabs.machine-equipment');
    Route::get('/inventory/tabs/office-equipment', function() {
        $officeEquipments = \App\Models\OfficeEquipment::latest()->get();

        return view('inventory.tabs.office-equipment', compact('officeEquipments'));
    })->name('inventory.tabs.office-equipment');
    Route::get('/inventory/tabs/technical-scientific-equipment', function() {
        $technicalScientificEquipments = \App\Models\TechnicalScientificEquipment::latest()->get();

        return view('inventory.tabs.technical-scientific-equipment', compact('technicalScientificEquipments'));
    })->name('inventory.tabs.technical-scientific-equipment');
    Route::get('/inventory/tabs/other-ppe', function() {
        $otherPpes = \App\Models\OtherPpe::latest()->get();

        return view('inventory.tabs.other-ppe', compact('otherPpes'));
    })->name('inventory.tabs.other-ppe');

    Route::get('/inventory/tabs/furniture-fixtures', function() {
        $furnitureFixtures = \App\Models\FurnitureFixture::latest()->get();

        return view('inventory.tabs.furniturez-fixtures', compact('furnitureFixtures'));
    })->name('inventory.tabs.furniture-fixtures');

    // Motor Vehicle Routes
    Route::post('/motor-vehicle/store', [InventoryItemController::class, 'storeMotorVehicle'])->name('motor-vehicle.store');
    Route::put('/motor-vehicle/{motorVehicle}', [InventoryItemController::class, 'updateMotorVehicle'])->name('motor-vehicle.update');
    Route::delete('/motor-vehicle/{motorVehicle}', [InventoryItemController::class, 'destroyMotorVehicle'])->name('motor-vehicle.destroy');

    // CIP Routes
    Route::post('/cip/store', [InventoryItemController::class, 'storeCip'])->name('cip.store');
    Route::put('/cip/{cip}', [InventoryItemController::class, 'updateCip'])->name('cip.update');
    Route::delete('/cip/{cip}', [InventoryItemController::class, 'destroyCip'])->name('cip.destroy');

    // PPE Category Routes
    Route::post('/machine-equipment/store', [InventoryItemController::class, 'storeMachineEquipment'])->name('machine-equipment.store');

    // Furniture & Fixtures Routes
    Route::post('/furniture-fixtures/store', [InventoryItemController::class, 'storeFurnitureFixture'])->name('furniture-fixtures.store');
    Route::put('/furniture-fixtures/{furnitureFixture}', [InventoryItemController::class, 'updateFurnitureFixture'])->name('furniture-fixtures.update');
    Route::delete('/furniture-fixtures/{furnitureFixture}', [InventoryItemController::class, 'destroyFurnitureFixture'])->name('furniture-fixtures.destroy');

    // Military, Police & Security Equipment Routes
    Route::get('/inventory/tabs/military-police-security', function() {
        $militaryPoliceSecurityEquipments = \App\Models\MilitaryPoliceSecurityEquipment::latest()->get();
        return view('inventory.tabs.military-police-security', compact('militaryPoliceSecurityEquipments'));
    })->name('inventory.tabs.military-police-security');

    Route::post('/military-police-security/store', [InventoryItemController::class, 'storeMilitaryPoliceSecurityEquipment'])->name('military-police-security.store');
    Route::put('/military-police-security/{militaryPoliceSecurityEquipment}', [InventoryItemController::class, 'updateMilitaryPoliceSecurityEquipment'])->name('military-police-security.update');
    Route::delete('/military-police-security/{militaryPoliceSecurityEquipment}', [InventoryItemController::class, 'destroyMilitaryPoliceSecurityEquipment'])->name('military-police-security.destroy');
    Route::put('/machine-equipment/{machineEquipment}', [InventoryItemController::class, 'updateMachineEquipment'])->name('machine-equipment.update');
    Route::delete('/machine-equipment/{machineEquipment}', [InventoryItemController::class, 'destroyMachineEquipment'])->name('machine-equipment.destroy');
    Route::post('/office-equipment/store', [InventoryItemController::class, 'storeOfficeEquipment'])->name('office-equipment.store');
    Route::put('/office-equipment/{officeEquipment}', [InventoryItemController::class, 'updateOfficeEquipment'])->name('office-equipment.update');
    Route::delete('/office-equipment/{officeEquipment}', [InventoryItemController::class, 'destroyOfficeEquipment'])->name('office-equipment.destroy');
    Route::post('/technical-scientific-equipment/store', [InventoryItemController::class, 'storeTechnicalScientificEquipment'])->name('technical-scientific-equipment.store');
    Route::put('/technical-scientific-equipment/{technicalScientificEquipment}', [InventoryItemController::class, 'updateTechnicalScientificEquipment'])->name('technical-scientific-equipment.update');
    Route::delete('/technical-scientific-equipment/{technicalScientificEquipment}', [InventoryItemController::class, 'destroyTechnicalScientificEquipment'])->name('technical-scientific-equipment.destroy');
    Route::post('/other-ppe/store', [InventoryItemController::class, 'storeOtherPpe'])->name('other-ppe.store');
    Route::put('/other-ppe/{otherPpe}', [InventoryItemController::class, 'updateOtherPpe'])->name('other-ppe.update');
    Route::delete('/other-ppe/{otherPpe}', [InventoryItemController::class, 'destroyOtherPpe'])->name('other-ppe.destroy');
    
    Route::get('/ipm', [InventoryItemController::class, 'ipm'])->name('inventory.ipm');
    Route::get('/icm', [InventoryItemController::class, 'icm'])->name('inventory.icm');
    Route::get('/inventory/export/{type}', [InventoryItemController::class, 'export'])->name('inventory.export');
    Route::get('/inventory/category-export/{category}/pdf', [InventoryItemController::class, 'exportCategoryPdf'])->name('inventory.category.export.pdf');

    // API Routes (Calendar/CTO/ICM)
    // CTO search employees (expects route name: api.employees.search)
    Route::get('/api/employees/search', [InventoryItemController::class, 'searchEmployees'])->name('api.employees.search');

    // Existing ICM-related APIs
    Route::get('/api/search-employees', [InventoryItemController::class, 'searchEmployees'])->name('api.search-employees');
    Route::get('/api/items-by-personnel', [InventoryItemController::class, 'getItemsByPersonnel'])->name('api.items-by-personnel');
    Route::get('/api/item-details/{itemId}', [InventoryItemController::class, 'getItemDetails'])->name('api.item-details');


    // Profile Routes
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.change-password');

    // PDF - Controller

    // Credits routes
    Route::get('/credits', [CreditsController::class, 'index'])->name('credits.index');
    Route::get('/credits/cto', [CreditsController::class, 'cto'])->name('credits.cto');
    Route::post('/credits', [CreditsController::class, 'store'])->name('credits.store');
    Route::put('/credits/{credit}', [CreditsController::class, 'update'])->name('credits.update');
    Route::delete('/credits/{credit}', [CreditsController::class, 'destroy'])->name('credits.destroy');


    // Calendar page
    Route::get('/calendar', function () {
        return view('calendar.index');
    })->name('calendar.index');

    Route::get('/calendar/create', function () {
        return view('calendar.create');
    })->name('calendar.create');

    // Calendar create (fix: POST /calendar)
    Route::post('/calendar', [EventController::class, 'store'])->name('calendar.store');

    // Calendar API endpoints for FullCalendar
    Route::get('/api/events', [EventController::class, 'index'])->name('calendar.api.events');
    Route::get('/api/events/types', [EventController::class, 'getTypes'])->name('calendar.api.events.types');

});


