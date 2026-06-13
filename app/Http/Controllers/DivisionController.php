<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::latest()->get();
        return view('divisions.index', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:divisions,name',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:255',
        ]);

        Division::create($request->only('name', 'code', 'description'));

        return back()->with('success', "Division \"{$request->name}\" added.");
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:divisions,name,' . $division->id,
            'code' => 'nullable|string|max:20',
        ]);

        $division->update($request->only('name', 'code', 'description', 'is_active'));

        return back()->with('success', "Division updated.");
    }

    public function destroy(Division $division)
    {
        if ($division->employees()->count() > 0) {
            return back()->with('error', "Cannot delete — {$division->employees()->count()} employee(s) are assigned to this division.");
        }

        $division->delete();
        return back()->with('success', "Division deleted.");
    }
}