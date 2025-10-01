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
                                ->orWhere('property_number', 'LIKE', "%{$term}%")
                                ->orWhere('emp_no', 'LIKE', "%{$term}%");
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
        $employees = Employee::orderBy('firstname')->get(['emp_no', 'firstname', 'lastname']);
        return view('inventory.modals.create-modal', compact('departments', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division' => 'required|string|max:255',
            'enduser' => 'required|string|max:255',
            'emp_no' => 'required|string|max:255|exists:employee_db.employees,emp_no',
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
        $employees = Employee::orderBy('firstname')->get(['emp_no', 'firstname', 'lastname']);
        return view('inventory.modals.edit-modal', compact('inventoryItem', 'departments', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'division' => 'required|string|max:255',
            'enduser' => 'required|string|max:255',
            'emp_no' => 'required|string|max:255|exists:employees,emp_no',
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
                                ->orWhere('property_number', 'LIKE', "%{$term}%")
                                ->orWhere('emp_no', 'LIKE', "%{$term}%");
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
        
        $csv->insertOne([
            'No', 'Division', 'End User', 'Employee No', 'Classification', 'Description', 
            'Serial Number', 'Property Number', 'Unit Price', 'CO/MOOE', 
            'Date Acquired', 'Remarks', 'Status'
        ]);

        foreach ($items as $item) {
            $csv->insertOne([
                $item->no,
                $item->division,
                $item->enduser,
                $item->emp_no ?? 'N/A',
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

        $csv->insertOne(array_fill(0, 13, '')); 
        $csv->insertOne(array_fill(0, 13, '')); 

        $signature1 = array_fill(0, 13, '');
        $signature1[0] = 'Prepared by:';
        $signature1[7] = 'Reviewed by:';
        $csv->insertOne($signature1);

        $signature2 = array_fill(0, 13, '');
        $signature2[0] = '_______________________________';
        $signature2[7] = '_______________________________';
        $csv->insertOne($signature2);

        $signature3 = array_fill(0, 13, '');
        $signature3[0] = 'HERO JOHN E. LAPORGA';
        $signature3[7] = 'MAY FLORENCE A. PABELONIO';
        $csv->insertOne($signature3);

        $signature4 = array_fill(0, 13, '');
        $signature4[0] = 'Senior IT Support Specialist';
        $signature4[7] = 'ICT Focal Person';
        $csv->insertOne($signature4);

        $csv->insertOne(array_fill(0, 13, '')); 
        $csv->insertOne(array_fill(0, 13, ''));

        $signature5 = array_fill(0, 13, '');
        $signature5[6] = '_______________________________';
        $csv->insertOne($signature5);

        $signature6 = array_fill(0, 13, '');
        $signature6[6] = 'CECILIA L. OCHAVO-SAYCON';
        $csv->insertOne($signature6);

        $signature7 = array_fill(0, 13, '');
        $signature7[6] = 'Regional Director';
        $csv->insertOne($signature7);

        return Response::make($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
        ]);
    }

    public function dashboard(Request $request)
    {
        $filterableQuery = InventoryItem::active();
        $itemsThisMonth = InventoryItem::active()
            ->whereMonth('date_acquired', now()->month)
            ->whereYear('date_acquired', now()->year)
            ->count();
        
        if ($request->filled('filter') && $request->filter !== 'none') {
            switch ($request->filter) {
                case 'today':
                    $filterableQuery->whereDate('date_acquired', now()->toDateString());
                    break;
                case 'week':
                    $filterableQuery->whereBetween('date_acquired', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $filterableQuery->whereMonth('date_acquired', now()->month)
                          ->whereYear('date_acquired', now()->year);
                    break;
                case 'year':
                    $filterableQuery->whereYear('date_acquired', now()->year);
                    break;
                case 'custom':
                    if ($request->filled('date_from')) {
                        $filterableQuery->whereDate('date_acquired', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $filterableQuery->whereDate('date_acquired', '<=', $request->date_to);
                    }
                    break;
            }
        }

        $totalItems = $filterableQuery->count();
        $totalValue = $filterableQuery->sum('unit_price');
        $totalDivisions = $filterableQuery->distinct('division')->count('division');

        $divisionData = $filterableQuery->selectRaw('division, count(*) as count')
            ->groupBy('division')
            ->get();

        $classificationData = $filterableQuery->selectRaw('classification, sum(unit_price) as total_value')
            ->groupBy('classification')
            ->get();
        
        if ($request->filled('filter') && $request->filter !== 'none') {
             $endDate = now();
             switch ($request->filter) {
                case 'today':
                    $startDate = now()->subMonth();
                    $endDate = now()->endOfDay();
                    $acquisitionQuery->whereBetween('date_acquired', [$startDate, $endDate]);
                    break;
                case 'week':
                    $startDate = now()->subMonths(2);
                    $endDate = now()->endOfWeek();
                    $acquisitionQuery->whereBetween('date_acquired', [$startDate, $endDate]);
                    break;
                case 'month':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfMonth();
                    $acquisitionQuery->whereBetween('date_acquired', [$startDate, $endDate]);
                    break;
                case 'year':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    $acquisitionQuery->whereBetween('date_acquired', [$startDate, $endDate]);
                    break;
            }
        } else {
            $startDate = now()->subMonths(11)->startOfMonth();
            $acquisitionQuery->where('date_acquired', '>=', $startDate);
        }

        $monthlyAcquisitions = $acquisitionQuery
            ->selectRaw('DATE_FORMAT(date_acquired, "%b %Y") as month, MONTH(date_acquired) as month_num, YEAR(date_acquired) as year, count(*) as count')
            ->groupBy('month', 'month_num', 'year')
            ->orderBy('year', 'asc')
            ->orderBy('month_num', 'asc')
            ->get();

        $newCount = (clone $filterableQuery)->whereRaw('TRIM(UPPER(status)) = "NEW"')->count();
        $forReplacementCount = (clone $filterableQuery)->whereRaw('TRIM(UPPER(status)) = "FOR REPLACEMENT"')->count();

        $statusData = collect([
            (object)['status' => 'NEW', 'count' => $newCount],
            (object)['status' => 'FOR REPLACEMENT', 'count' => $forReplacementCount],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'totalItems' => (int)$totalItems, 
                'totalValue' => (float)$totalValue, 
                'itemsThisMonth' => (int)$itemsThisMonth,
                'totalDivisions' => (int)$totalDivisions,
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