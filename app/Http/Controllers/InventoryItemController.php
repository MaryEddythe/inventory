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
            return 'New';
        }

        $years = now()->diffInYears($dateAcquired);
        return $years <= 5 ? 'New' : 'For Replacement';
    }

    private function applyDateFilters(Request $request, $query)
    {
        $filter = $request->get('filter');

        switch ($filter) {
            case 'today':
                $query->whereDate('date_acquired', today());
                break;
            case 'week':
                $query->whereBetween('date_acquired', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('date_acquired', now()->month)
                      ->whereYear('date_acquired', now()->year);
                break;
            case 'year':
                $query->whereYear('date_acquired', now()->year);
                break;
            case 'custom':
                if ($request->filled('date_from')) {
                    $query->whereDate('date_acquired', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('date_acquired', '<=', $request->date_to);
                }
                break;
            default:
                // No filter, show all
                break;
        }
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
                            ->orWhere('emp_no', 'LIKE', "%{$term}%")
                            ->orWhere('remarks', 'LIKE', "%{$term}%");
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

    $allowedPerPage = [10, 25, 50, 100];
    $perPage = (int) $request->get('per_page', 10);
    if (!in_array($perPage, $allowedPerPage)) {
        $perPage = 10;
    }

    $items = $query->orderBy('division')->orderByRaw("SUBSTRING_INDEX(enduser, ' ', -1) ASC")->paginate($perPage)->withQueryString();
    $groupedItems = $items->getCollection()->groupBy('enduser');

    $departments = Department::orderBy('department')->get();
    $employees = Employee::orderBy('firstname')->get();

    return view('inventory.tabs.index', compact('items', 'groupedItems', 'departments', 'employees', 'perPage'));
}

    public function create()
    {
        $departments = Department::orderBy('department')->get();
        $employees = Employee::with('departmentInfo')->orderBy('firstname')->get(['emp_no', 'firstname', 'lastname', 'department']);
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
        $employees = Employee::with('departmentInfo')->orderBy('firstname')->get(['emp_no', 'firstname', 'lastname', 'department']);
        return view('inventory.modals.edit-modal', compact('inventoryItem', 'departments', 'employees'));
    }

   public function update(Request $request, $id)
{
    $item = InventoryItem::findOrFail($id);
    $originalId = $item->id;

    // Check if this is an IPM update (has IPM-specific fields)
    $isIpmUpdate = $request->hasAny(['system_boot_up', 'hardware', 'performance', 'cables_connections', 'peripherals', 'recommendation', 'date_conducted', 'time_started', 'time_ended']);

    if ($isIpmUpdate) {
        // IPM update validation
        $validated = $request->validate([
            'condition' => 'nullable|string|in:New,For Replacement,Functional,Nonfunctional',
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

    // SAVE WHO UPDATED THE ITEM
    $item->updated_by = auth()->user()->emp_no;

    // Apply validated data
    $item->update($validated);

    // Set updated_at to current timestamp
    $item->updated_at = now();

    $item->save();

    return response()->json([
        'success' => true,
        'message' => 'Item updated successfully',
        'item'    => $item,
        'original_id' => $originalId,
    ]);
}


    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->update(['x' => 'INACTIVE']);
        $item->updated_at = now();
        $item->save();

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
                            ->orWhere('remarks', 'LIKE', "%{$term}%")
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

    if ($request->tab === 'ipm') {
        $query->where('classification', '!=', 'Monitor');
    }

    // GET THE SUBTYPE FROM REQUEST
    $subtype = $request->get('subtype', 'inventory');
    
    if ($subtype === 'rpcsp') {
        $query->where('unit_price', '<=', 49999)
              ->where('co_mooe', 'CO');
    } elseif ($subtype === 'ppe') {
        $query->where('unit_price', '>=', 50000)
              ->where('co_mooe', 'CO');
    }

    if ($subtype === 'ppe') {
        $items = $query->orderByRaw("SUBSTRING_INDEX(enduser, ' ', -1) ASC")->get();
    } else {
        $items = $query->orderBy('enduser')->orderBy('no', 'desc')->get();
    }

    $items = $query->orderBy('enduser')->orderBy('no', 'desc')->get();

    $tab = $request->tab ?? 'inventory';
    $css = File::get(public_path('pdf-styles.css'));

    // Encode logos for PDF
    $mgbLogo = base64_encode(file_get_contents(public_path('assets/mgb.jpg')));
    $bpLogo = base64_encode(file_get_contents(public_path('assets/bp.jpg')));

    if ($type === 'pdf') {
        // Use the subtype already determined above
        if ($subtype === 'rpcsp') {
            $view = 'inventory.export-rpcsp-pdf';
        } elseif ($subtype === 'ppe') {
            $view = 'inventory.export-ppe-pdf';
        } else {
            $view = $tab === 'ipm' ? 'inventory.export-ipm-pdf' : 'inventory.export-pdf';
        }
        $pdf = Pdf::loadView($view, compact('items', 'tab', 'css', 'mgbLogo', 'bpLogo'))
            ->setPaper('landscape');
        return $pdf->download($subtype . '.pdf');
    }

    if ($type === 'csv') {
        $csv = Writer::createFromString('');

        if ($tab === 'ipm') {
            // IPM-specific CSV headers and data
            $headers = [
                'No',
                'Div.',
                'User',
                'Type',
                'Desc',
                'Condition',
                'Boot Up',
                'HW',
                'Perf',
                'Cables/Conn',
                'Periph',
                'Rem',
                'Rec',
                'Date',
                'Start',
                'End'
            ];
            $csv->insertOne($headers);

            foreach ($items as $item) {
                $row = [
                    $item->no,
                    $item->division,
                    $item->enduser,
                    $item->classification,
                    $item->description,
                    $item->condition,
                    $item->system_boot_up ? 'Yes' : 'No',
                    $item->hardware ? 'Yes' : 'No',
                    $item->performance ? 'Yes' : 'No',
                    $item->cables_connections ? 'Yes' : 'No',
                    $item->peripherals ? 'Yes' : 'No',
                    $item->remarks ?? 'N/A',
                    $item->recommendation ?? 'N/A',
                    $item->date_conducted ? $item->date_conducted->format('M d, Y') : 'N/A',
                    $item->time_started ?? 'N/A',
                    $item->time_ended ?? 'N/A'
                ];
                $csv->insertOne($row);
            }

            $filename = 'ipm_inventory.csv';
        } else {
            // For RPCSP CSV export, add note about filtering
            if ($subtype === 'rpcsp') {
                $headers = [
                    'No', 'Division', 'Enduser', 'Classification', 'Description',
                    'Serial Number', 'Property Number', 'Unit Price', 'CO/MOOE',
                    'Date Acquired', 'Remarks', 'Status', 'NOTE'
                ];
            } else {
                $headers = [
                    'No', 'Division', 'Enduser', 'Classification', 'Description',
                    'Serial Number', 'Property Number', 'Unit Price', 'CO/MOOE',
                    'Date Acquired', 'Remarks', 'Status'
                ];
            }
            
            $csv->insertOne($headers);

            foreach ($items as $item) {
                if ($subtype === 'rpcsp') {
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
                        'RPCSP Export' // Add note for RPCSP
                    ];
                } else {
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
                }
                $csv->insertOne($row);
            }

            $filename = $subtype === 'rpcsp' ? 'rpcsp_inventory.csv' : 'inventory.csv';
        }

        return response($csv->getContent(), 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    return back()->with('error', 'Invalid export type');
}

    public function dashboard(Request $request)
{
    $filterableQuery = InventoryItem::where('x', 'active');

    // Apply date filters
    $this->applyDateFilters($request, $filterableQuery);

    $totalItems = $filterableQuery->count();
    $totalValue = $filterableQuery->sum('unit_price');
    $rpcspValue = (clone $filterableQuery)->where('unit_price', '<=', 49999)
        ->where('co_mooe', 'CO')
        ->sum('unit_price');
    $ppeValue = (clone $filterableQuery)->where('unit_price', '>=', 50000)
        ->where('co_mooe', 'CO')
        ->sum('unit_price');
    $itemsThisMonth = (clone $filterableQuery)->whereMonth('date_acquired', now()->month)
        ->whereYear('date_acquired', now()->year)
        ->count();

    // Get all divisions with item counts, including those with 0 items
    $allDivisions = Department::orderBy('department')->pluck('department');
    $divisionCounts = (clone $filterableQuery)
        ->select('division', \DB::raw('count(*) as count'))
        ->groupBy('division')
        ->pluck('count', 'division');

    $divisionData = $allDivisions->map(function ($division) use ($divisionCounts) {
        return (object) [
            'division' => $division,
            'count' => $divisionCounts->get($division, 0)
        ];
    });

    // Count only divisions with items
    $totalDivisions = $divisionCounts->filter(function ($count) {
        return $count > 0;
    })->count();

    // Status data: Items less than 5 years and 5 years or more since date acquired
    $fiveYearsAgo = now()->subYears(5);
    $allStatusItems = (clone $filterableQuery)->get();
    $lessThan5Years = 0;
    $fiveYearsOrMore = 0;

    foreach ($allStatusItems as $item) {
        if ($item->date_acquired) {
            // If date_acquired is before 5 years ago, it's 5 years or more
            if ($item->date_acquired < $fiveYearsAgo) {
                $fiveYearsOrMore++;
            } else {
                $lessThan5Years++;
            }
        }
    }

    $statusData = collect([
        (object)[
            'status' => 'Less than 5 years',
            'count' => $lessThan5Years
        ],
        (object)[
            'status' => '5 years or more',
            'count' => $fiveYearsOrMore
        ],
    ]);

    // Condition data: Functional and Nonfunctional
    // Using backticks to escape 'condition' reserved word
    $conditionCounts = (clone $filterableQuery)
        ->selectRaw('`condition`, COUNT(*) as count')
        ->whereNotNull('condition')
        ->groupBy('condition')
        ->pluck('count', 'condition');

    $conditionData = collect([
        (object)[
            'condition' => 'Functional',
            'count' => $conditionCounts->get('Functional', 0)
        ],
        (object)[
            'condition' => 'Nonfunctional',
            'count' => $conditionCounts->get('Nonfunctional', 0)
        ],
    ]);

    // Division breakdown by classification
    $divisionBreakdown = [];
    foreach ($allDivisions as $division) {
        $breakdown = (clone $filterableQuery)
            ->where('division', $division)
            ->select('classification', \DB::raw('count(*) as count'))
            ->groupBy('classification')
            ->pluck('count', 'classification');

        $divisionBreakdown[$division] = [
            'Desktop' => $breakdown->get('Desktop', 0),
            'Laptop' => $breakdown->get('Laptop', 0),
            'Monitor' => $breakdown->get('Monitor', 0),
            'Printer' => $breakdown->get('Printer', 0),
            'Scanner' => $breakdown->get('Scanner', 0),
            'Others' => $breakdown->filter(function ($count, $classification) {
                return !in_array($classification, ['Desktop', 'Laptop', 'Monitor', 'Printer', 'Scanner']);
            })->sum(),
        ];
    }

    if ($request->ajax()) {
        return response()->json([
            'totalItems' => (int)$totalItems,
            'totalValue' => (float)$totalValue,
            'rpcspValue' => (float)$rpcspValue,
            'ppeValue' => (float)$ppeValue,
            'itemsThisMonth' => (int)$itemsThisMonth,
            'totalDivisions' => (int)$totalDivisions,
            'divisionData' => $divisionData,
            'statusData' => $statusData,
            'conditionData' => $conditionData,
            'divisionBreakdown' => $divisionBreakdown
        ]);
    }

    return view('inventory.tabs.dashboard', compact(
        'totalItems',
        'totalValue',
        'rpcspValue',
        'ppeValue',
        'itemsThisMonth',
        'totalDivisions',
        'divisionData',
        'statusData',
        'conditionData',
        'divisionBreakdown'
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
                            ->orWhere('remarks', 'LIKE', "%{$term}%");
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

        // EXCLUDE Monitor classification from IPM listing
        $query->where('classification', '!=', 'Monitor');

        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $items = $query->orderBy('no', 'desc')->paginate($perPage)->withQueryString();
        $departments = Department::orderBy('department')->get();
        $employees = Employee::orderBy('firstname')->get();

        if ($request->ajax()) {
            return view('inventory.table-data-ipm', compact('items'))->render();
        }

        return view('inventory.tabs.ipm', compact('items', 'departments', 'employees', 'perPage'));
    }

    public function show(InventoryItem $inventoryItem)
    {
    }
}
