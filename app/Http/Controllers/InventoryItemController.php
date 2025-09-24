<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('no', 'desc')->paginate(10);
        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'division' => 'required|string|max:100',
            'enduser' => 'required|string|max:100',
            'classification' => 'required|string|max:100',
            'description' => 'required|string',
            'serial_number' => 'nullable|string|unique:inventory_items',
            'property_number' => 'required|string|unique:inventory_items',
            'unit_price' => 'required|numeric|min:0',
            'co_mooe' => 'required|string|max:50',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string'
        ]);

        InventoryItem::create($request->all());

        return redirect()->route('inventory.index');
    }

    public function show(InventoryItem $inventoryItem)
    {
        return view('inventory.show', compact('inventoryItem'));
    }

    public function edit(InventoryItem $inventoryItem)
    {
        return view('inventory.edit', compact('inventoryItem'));
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'division' => 'required|string|max:100',
            'enduser' => 'required|string|max:100',
            'classification' => 'required|string|max:100',
            'description' => 'required|string',
            'serial_number' => 'nullable|string|unique:inventory_items,serial_number,' . $inventoryItem->no . ',no',
            'property_number' => 'required|string|unique:inventory_items,property_number,' . $inventoryItem->no . ',no',
            'unit_price' => 'required|numeric|min:0',
            'co_mooe' => 'required|string|max:50',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string'
        ]);

        $inventoryItem->update($request->all());

        return redirect()->route('inventory.index');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return redirect()->route('inventory.index');
    }
}