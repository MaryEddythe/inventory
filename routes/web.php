<?php

use App\Http\Controllers\InventoryItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inventory.index');
});

Route::resource('inventory', InventoryItemController::class);