<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use League\Csv\Writer;
use Illuminate\Support\Facades\Response;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::active();

        // Search functionality with debounce and optimization
        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = array_filter(explode(' ', $search));

            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function($subQuery) use ($term) {
                        $subQuery->where('division', 'LIKE', "%{$term}%")
                                ->orWhere('enduser', 'LIKE', "%{$term}%")
                                ->orWhere('classification', 'LIKE', "%{$term}%")
                                ->orWhere('description', 'LIKE', "%{$term}%")
                                ->orWhere('serial_number', 'LIKE', "%{$term}%")
                                ->orWhere('property_number', 'LIKE', "%{$term}%");
                    });
                }
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by division
        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('date_acquired', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date_acquired', '<=', $request->date_to);
        }

        $items = $query->orderBy('no', 'desc')->paginate(10)->withQueryString();
        $departments = Department::orderBy('department')->get();
        $employees = Employee::orderBy('firstname')->get();

        if ($request->ajax()) {
            return view('inventory.table-data', compact('items'))->render();
        }

        return view('inventory.tabs.index', compact('items', 'departments', 'employees'));
    }

    public function create()
    {
        $departments = Department::orderBy('department')->get();
        $employees = Employee::orderBy('firstname')->get();
        return view('inventory.modals.create-modal', compact('departments', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division' => 'required|string|max:255',
            'enduser' => 'required|string|max:255',
            'classification' => 'required|string|max:255',
            'property_number' => 'required|string|max:255|unique:inventory_items,property_number',
            'description' => 'required|string',
            'serial_number' => 'nullable|string|max:255|unique:inventory_items,serial_number',
            'unit_price' => 'required|numeric|min:0',
            'co_mooe' => 'required|string|max:255',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $item = InventoryItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully',
            'item'    => $item,
        ], 201);
    }

    public function edit(InventoryItem $inventoryItem)
    {
        $departments = Department::orderBy('department')->get();
        $employees = Employee::orderBy('firstname')->get();
        return view('inventory.modals.edit-modal', compact('inventoryItem', 'departments', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'division' => 'required|string|max:255',
            'enduser' => 'required|string|max:255',
            'classification' => 'required|string|max:255',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventory_items')->ignore($item->no, 'no'),
            ],
            'description' => 'required|string',
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('inventory_items')->ignore($item->no, 'no'),
            ],
            'unit_price' => 'required|numeric|min:0',
            'co_mooe' => 'required|string|max:255',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'item'    => $item,
        ], 200);
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->update(['x' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully'
        ]);
    }

    public function export(Request $request, $type)
    {
        $query = InventoryItem::active();

        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = array_filter(explode(' ', $search));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function($subQuery) use ($term) {
                        $subQuery->where('division', 'LIKE', "%{$term}%")
                                ->orWhere('enduser', 'LIKE', "%{$term}%")
                                ->orWhere('classification', 'LIKE', "%{$term}%")
                                ->orWhere('description', 'LIKE', "%{$term}%")
                                ->orWhere('serial_number', 'LIKE', "%{$term}%")
                                ->orWhere('property_number', 'LIKE', "%{$term}%");
                    });
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_acquired', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date_acquired', '<=', $request->date_to);
        }

        $items = $query->orderBy('no', 'desc')->get();

        $fileName = 'inventory_' . now()->format('Y-m-d_H-i-s');

        if ($type === 'pdf') {
            return $this->exportPDF($items, $fileName);
        } elseif ($type === 'csv') {
            return $this->exportCSV($items, $fileName);
        }

        return redirect()->back()->with('error', 'Invalid export type.');
    }

    private function exportPDF($items, $fileName)
    {
        $pdf = Pdf::loadView('inventory.export-pdf', compact('items'));
        return $pdf->download($fileName . '.pdf');
    }

    private function exportCSV($items, $fileName)
    {
        $csv = Writer::createFromString('');
        
        // Add CSV headers
        $csv->insertOne([
            'No', 'Division', 'End User', 'Classification', 'Description', 
            'Serial Number', 'Property Number', 'Unit Price', 'CO/MOOE', 
            'Date Acquired', 'Remarks', 'Status'
        ]);

        foreach ($items as $item) {
            $csv->insertOne([
                $item->no,
                $item->division,
                $item->enduser,
                $item->classification,
                $item->description,
                $item->serial_number ?? 'N/A',
                $item->property_number,
                '₱' . number_format($item->unit_price, 2),
                $item->co_mooe,
                $item->date_acquired->format('M d, Y'),
                $item->remarks ?? 'N/A',
                $item->status
            ]);
        }

        // Signature section for CSV
        $csv->insertOne(array_fill(0, 12, '')); // Empty row
        $csv->insertOne(array_fill(0, 12, '')); // Empty row

        $signature1 = array_fill(0, 12, '');
        $signature1[0] = 'Prepared by:';
        $signature1[6] = 'Reviewed by:';
        $csv->insertOne($signature1);

        $signature2 = array_fill(0, 12, '');
        $signature2[0] = '_______________________________';
        $signature2[6] = '_______________________________';
        $csv->insertOne($signature2);

        $signature3 = array_fill(0, 12, '');
        $signature3[0] = 'HERO JOHN E. LAPORGA';
        $signature3[6] = 'MAY FLORENCE A. PABELONIO';
        $csv->insertOne($signature3);

        $signature4 = array_fill(0, 12, '');
        $signature4[0] = 'Senior IT Support Specialist';
        $signature4[6] = 'ICT Focal Person';
        $csv->insertOne($signature4);

        $csv->insertOne(array_fill(0, 12, '')); // Empty row
        $csv->insertOne(array_fill(0, 12, '')); // Empty row

        $signature5 = array_fill(0, 12, '');
        $signature5[5] = '_______________________________';
        $csv->insertOne($signature5);

        $signature6 = array_fill(0, 12, '');
        $signature6[5] = 'CECILIA L. OCHAVO-SAYCON';
        $csv->insertOne($signature6);

        $signature7 = array_fill(0, 12, '');
        $signature7[5] = 'Regional Director';
        $csv->insertOne($signature7);

        return Response::make($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
        ]);
    }

    public function dashboard(Request $request)
    {
        $query = InventoryItem::active();

        // Apply date filter if provided
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date_acquired', [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            $query->where('date_acquired', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->where('date_acquired', '<=', $request->date_to);
        }

        $totalItems = $query->count();
        $totalValue = $query->sum('unit_price');
        $itemsThisMonth = $query->whereMonth('date_acquired', now()->month)
            ->whereYear('date_acquired', now()->year)
            ->count();
        $totalDivisions = $query->distinct('division')->count('division');

        $divisionData = $query->selectRaw('division, count(*) as count')
            ->groupBy('division')
            ->get();

        // Monthly acquisitions - adjust range based on filter
        $acquisitionQuery = InventoryItem::active();
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $startDate = $request->date_from ?: now()->subMonths(6)->startOfMonth();
            $endDate = $request->date_to ?: now();
            $acquisitionQuery->whereBetween('date_acquired', [$startDate, $endDate]);
        } else {
            $acquisitionQuery->where('date_acquired', '>=', now()->subMonths(6));
        }

        $monthlyAcquisitions = $acquisitionQuery
            ->selectRaw('DATE_FORMAT(date_acquired, "%b %Y") as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('date_acquired')
            ->get();

        // Value by classification
        $classificationData = $query->selectRaw('classification, sum(unit_price) as total_value')
            ->groupBy('classification')
            ->get();

        // Status distribution
        $statusData = $query->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'totalItems' => $totalItems,
                'totalValue' => $totalValue,
                'itemsThisMonth' => $itemsThisMonth,
                'totalDivisions' => $totalDivisions,
                'divisionData' => [
                    'labels' => $divisionData->pluck('division'),
                    'counts' => $divisionData->pluck('count')
                ],
                'monthlyAcquisitions' => [
                    'labels' => $monthlyAcquisitions->pluck('month'),
                    'counts' => $monthlyAcquisitions->pluck('count')
                ],
                'classificationData' => [
                    'labels' => $classificationData->pluck('classification'),
                    'values' => $classificationData->pluck('total_value')
                ],
                'statusData' => [
                    'labels' => $statusData->pluck('status'),
                    'counts' => $statusData->pluck('count')
                ]
            ]);
        }

        return view('inventory.tabs.dashboard', compact(
            'totalItems',
            'totalValue',
            'itemsThisMonth',
            'totalDivisions',
            'divisionData',
            'monthlyAcquisitions',
            'classificationData',
            'statusData'
        ));
    }

    public function show(InventoryItem $inventoryItem)
    {
    }


}