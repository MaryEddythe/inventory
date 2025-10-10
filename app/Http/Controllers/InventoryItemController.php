<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use League\Csv\Writer;

class InventoryItemController extends Controller
{
    private function calculateStatus($dateAcquired)
    {
        if (!$dateAcquired) {
            return 'NEW';
        }

        $years = now()->diffInYears($dateAcquired);
        return $years <= 5 ? 'NEW' : 'FOR REPLACEMENT';
    }

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
            'property_number' => 'required|string|max:255',
            'description' => 'required|string',
            'serial_number' => 'nullable|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'co_mooe' => 'required|string|max:255',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $validated['status'] = $this->calculateStatus($validated['date_acquired']);

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

        // Check if this is an IPM update (has IPM-specific fields)
        $isIpmUpdate = $request->hasAny(['system_boot_up', 'hardware', 'performance', 'cables_connections', 'peripherals', 'recommendation', 'date_conducted', 'time_started', 'time_ended']);

        if ($isIpmUpdate) {
            // IPM update validation
            $validated = $request->validate([
                'condition' => 'nullable|string|in:NEW,FOR REPLACEMENT,Functional,Nonfunctional',
                'system_boot_up' => 'nullable|boolean',
                'hardware' => 'nullable|boolean',
                'performance' => 'nullable|boolean',
                'cables_connections' => 'nullable|boolean',
                'peripherals' => 'nullable|boolean',
                'recommendation' => 'nullable|string',
                'date_conducted' => 'nullable|date',
                'time_started' => 'nullable|date_format:H:i',
                'time_ended' => 'nullable|date_format:H:i|after:time_started',
            ]);
        } else {
            // Regular inventory update validation
            $validated = $request->validate([
                'division' => 'required|string|max:255',
                'enduser' => 'required|string|max:255',
                'emp_no' => 'required|string|max:255|exists:employee_db.employees,emp_no',
                'classification' => 'required|string|max:255',
                'property_number' => 'required|string|max:255',
                'description' => 'required|string',
                'serial_number' => 'nullable|string|max:255',
                'unit_price' => 'required|numeric|min:0',
                'co_mooe' => 'required|string|max:255',
                'date_acquired' => 'required|date',
                'remarks' => 'nullable|string',
            ]);

            // Recalculate status based on possibly updated date_acquired
            $validated['status'] = $this->calculateStatus($validated['date_acquired']);
        }

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'item'    => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->update(['x' => 'INACTIVE']);

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

        $tab = $request->tab ?? 'inventory';
        $css = File::get(public_path('pdf-styles.css'));

        if ($type === 'pdf') {
            $view = $tab === 'ipm' ? 'inventory.export-ipm-pdf' : 'inventory.export-pdf';
            $pdf = Pdf::loadView($view, compact('items', 'tab', 'css'))
                ->setPaper('a3', 'landscape');
            return $pdf->download('inventory.pdf');
        }

        if ($type === 'csv') {
            $csv = Writer::createFromString('');
            $headers = [
                'No', 'Division', 'Enduser', 'Classification', 'Description',
                'Serial Number', 'Property Number', 'Unit Price', 'CO/MOOE',
                'Date Acquired', 'Remarks', 'Status'
            ];
            $csv->insertOne($headers);

            foreach ($items as $item) {
                $row = [
                    $item->no,
                    $item->division,
                    $item->enduser,
                    $item->classification,
                    $item->description,
                    $item->serial_number ?? 'N/A',
                    $item->property_number,
                    number_format($item->unit_price, 2),
                    $item->co_mooe,
                    $item->date_acquired->format('M d, Y'),
                    $item->remarks ?? 'N/A',
                    $item->status,
                ];
                $csv->insertOne($row);
            }

            return response($csv->getContent(), 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="inventory.csv"');
        }

        return back()->with('error', 'Invalid export type');
    }

    public function dashboard(Request $request)
    {
        $filterableQuery = InventoryItem::active();

        $totalItems = $filterableQuery->count();
        $totalValue = $filterableQuery->sum('unit_price');
        $itemsThisMonth = $filterableQuery->whereMonth('date_acquired', now()->month)
            ->whereYear('date_acquired', now()->year)
            ->count();
        $totalDivisions = $filterableQuery->distinct('division')->count('division');

        $divisionData = $filterableQuery
            ->select('division', \DB::raw('count(*) as count'))
            ->groupBy('division')
            ->get();

        $classificationData = $filterableQuery
            ->select('classification', \DB::raw('sum(unit_price) as total_value'))
            ->groupBy('classification')
            ->get();

        $acquisitionQuery = clone $filterableQuery;

        if ($request->filled('period')) {
            switch ($request->period) {
                case 'last_6_months':
                    $startDate = now()->subMonths(5)->startOfMonth();
                    $acquisitionQuery->where('date_acquired', '>=', $startDate);
                    break;
                case 'this_year':
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

    public function ipm(Request $request)
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
                                ->orWhere('emp_no', 'LIKE', "%{$term}%")
                                ->orWhere('remarks', 'LIKE', "%{$term}%")
                                ->orWhere('recommendation', 'LIKE', "%{$term}%")
                                ->orWhere('condition', 'LIKE', "%{$term}%");
                    });
                }
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
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
            return view('inventory.table-data-ipm', compact('items'))->render();
        }

        return view('inventory.tabs.ipm', compact('items', 'departments', 'employees'));
    }

    public function show(InventoryItem $inventoryItem)
    {
    }
}