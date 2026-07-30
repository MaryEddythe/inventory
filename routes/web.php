<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreditsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\LeaveLedgerController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\SidebarAccessController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth', 'sidebar.access'])->group(function () {
    Route::get('/', function () {
        $user = auth()->user();

        return redirect()->route($user?->defaultLandingRouteName() ?? 'inventory.dashboard');
    });

    Route::get('/dashboard', [InventoryItemController::class, 'dashboard'])->name('inventory.dashboard');

    Route::resource('employees', EmployeeController::class);
    Route::post('/employees/{employee}/upload-profile-image', [EmployeeController::class, 'uploadProfileImage'])->name('employees.upload-profile-image');
    Route::put('/inventory/{inventory}', [InventoryItemController::class, 'update'])->name('inventory.update');
    Route::resource('inventory', InventoryItemController::class);
    Route::get('/inventory/dashboard', [InventoryItemController::class, 'dashboard'])->name('inventory.tabs.dashboard');

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

    // API Routes
    Route::get('/api/employees/search', [InventoryItemController::class, 'searchEmployees'])->name('api.employees.search');
    Route::get('/api/search-employees', [InventoryItemController::class, 'searchEmployees'])->name('api.search-employees');
    Route::get('/api/items-by-personnel', [InventoryItemController::class, 'getItemsByPersonnel'])->name('api.items-by-personnel');
    Route::get('/api/item-details/{itemId}', [InventoryItemController::class, 'getItemDetails'])->name('api.item-details');

    // Notifications
    Route::get('/notifications/{notification}/read', function (string $notification) {
        $user = auth()->user();
        $record = $user?->notifications()->findOrFail($notification);
        $record->markAsRead();
        return redirect()->to(data_get($record->data, 'url', route('leave-applications.index')));
    })->name('notifications.read');
    Route::post('/notifications/read-all', function () {
        auth()->user()?->unreadNotifications->markAsRead();
        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back();
    })->name('notifications.read-all');

    Route::get('/user/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])->name('password.confirm.store');
    Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])->name('password.confirmation');

    Route::get('/roles', [SidebarAccessController::class, 'index'])->name('roles.index');
    Route::put('/roles/{user}', [SidebarAccessController::class, 'update'])->name('roles.update');
    Route::redirect('/sidebar-access', '/roles');

    // Calendar Routes
    Route::get('/calendar', [EventController::class, 'index'])->name('calendar.index');
    Route::post('/calendar', [EventController::class, 'store'])->name('calendar.store');
    Route::get('/api/events/types', [EventController::class, 'getTypes'])->name('calendar.api.events.types');

    // Leave Applications Routes
    Route::get('/leave-applications', [LeaveApplicationController::class, 'index'])->name('leave-applications.index');
    Route::get('/leave-applications/{leaveApplication}/view', [LeaveApplicationController::class, 'view'])->name('leave-applications.view');
    Route::post('/leave-applications', [LeaveApplicationController::class, 'store'])->name('leave-applications.store');
    Route::post('/leave-applications/{leaveApplication}/sign/hr', [LeaveApplicationController::class, 'signHr'])->name('leave-applications.sign-hr');
    Route::post('/leave-applications/{leaveApplication}/sign/division-chief', [LeaveApplicationController::class, 'signDivisionChief'])->name('leave-applications.sign-division-chief');
    Route::post('/leave-applications/{leaveApplication}/sign/regional-director', [LeaveApplicationController::class, 'signRegionalDirector'])->name('leave-applications.sign-regional-director');

    // HR Leave Ledgers
    Route::get('/leave-ledgers', [LeaveLedgerController::class, 'index'])->name('leave-ledgers.index');
    Route::get('/leave-ledgers/{employee}', [LeaveLedgerController::class, 'show'])->name('leave-ledgers.show');
    Route::get('/leave-ledgers/{employee}/edit', [LeaveLedgerController::class, 'edit'])->name('leave-ledgers.edit');
    Route::put('/leave-ledgers/{employee}', [LeaveLedgerController::class, 'update'])->name('leave-ledgers.update');

    // Credits Routes
    Route::get('/credits', [CreditsController::class, 'index'])->name('credits.index');
    Route::get('/credits/cto', [CreditsController::class, 'cto'])->name('credits.cto');
    Route::post('/credits', [CreditsController::class, 'store'])->name('credits.store');
    Route::get('/credits/search', [CreditsController::class, 'search'])->name('credits.search');
    Route::get('/credits/{credit}/edit', [CreditsController::class, 'edit'])->name('credits.edit');
    Route::put('/credits/{credit}', [CreditsController::class, 'update'])->name('credits.update');
    Route::delete('/credits/{credit}', [CreditsController::class, 'destroy'])->name('credits.destroy');
});

// Profile Routes — outside sidebar.access middleware but still authenticated
Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/signature', [AuthController::class, 'storeSignature'])->name('profile.signature.store');
});
