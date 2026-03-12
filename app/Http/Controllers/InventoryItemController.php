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
            return '≤ 5 years';
        }

        $years = now()->diffInYears($dateAcquired);
        return $years <= 5 ? '≤ 5 years' : '> 5 years';
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

    $items = $query->orderByRaw("SUBSTRING_INDEX(enduser, ' ', -1) ASC")->paginate($perPage)->withQueryString();
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
        // Check if this is an ICM submission
        $isIcmSubmission = $request->filled('icm_type') && $request->filled('priority') && $request->filled('problem_description');

        if ($isIcmSubmission) {
            // ICM form validation
            $validated = $request->validate([
                'division' => 'required|string|max:255',
                'requesting_personnel' => 'required|string|max:255',
                'classification' => 'required|string|max:255',
                'property_number' => 'required|string|max:255',
                'problem_description' => 'required|string',
                'icm_type' => 'required|string|in:Assistance,Troubleshoot',
                'priority' => 'required|string|in:P1-Critical,P2-Important,P3-Normal,P4-Low',
                'serial_number' => 'nullable|string|max:255',
                'brand_model' => 'required|string|max:255',
                'hardware_software' => 'required|string|in:Hardware,Software',
                'open_date' => 'required|date',
                'open_time' => 'required|date_format:H:i',
                'close_date' => 'nullable|date|after_or_equal:open_date',
                'close_time' => 'nullable|date_format:H:i',
                'icm_findings' => 'nullable|string',
                'actions_taken' => 'nullable|string',
                'icm_recommendations' => 'nullable|string',
            ]);

            // Generate ICM number
            $currentYear = now()->year;
            $lastIcmNo = InventoryItem::where('icm_no', 'LIKE', "%-{$currentYear}")
                ->orderBy('icm_no', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastIcmNo) {
                $parts = explode('-', $lastIcmNo->icm_no);
                $nextNumber = (int)$parts[0] + 1;
            }

            $validated['icm_no'] = str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '-' . $currentYear;

            $item = InventoryItem::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'ICM created successfully with number: ' . $validated['icm_no'],
                'item'    => $item,
            ], 201);
        } else {
            // Regular inventory form validation
            $validated = $request->validate([
                'division' => 'required|string|max:255',
                'enduser' => 'required|string|max:255',
                'emp_no' => 'required|string|max:255|exists:employee_db.employees,emp_no',
                'classification' => 'required|string|max:255',
                'property_number' => 'required|string|max:255',
                'description' => 'required|string',
                'serial_number' => 'nullable|string|max:255',
                'unit_price' => 'nullable|numeric|min:0',
                'unit_price_type' => 'required|in:value,na',
                'co_mooe' => 'required|string|max:255',
                'date_acquired' => 'nullable|date',
                'date_acquired_type' => 'required|in:date,na',
                'remarks' => 'nullable|string',
            ]);

            // Handle NA values
            if ($request->input('unit_price_type') === 'na') {
                $validated['unit_price'] = null;
            }
            if ($request->input('date_acquired_type') === 'na') {
                $validated['date_acquired'] = null;
            }

            // Remove the _type fields from the data to be stored
            unset($validated['unit_price_type']);
            unset($validated['date_acquired_type']);

            $validated['status'] = $this->calculateStatus($validated['date_acquired']);

            $item = InventoryItem::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'item'    => $item,
            ], 201);
        }
    }

    public function edit(InventoryItem $inventoryItem)
    {
        $departments = Department::orderBy('department')->get();
        $employees = Employee::with('departmentInfo')->orderBy('firstname')->get(['emp_no', 'firstname', 'lastname', 'department']);
        return view('inventory.modals.edit-modal', compact('inventoryItem', 'departments', 'employees'));
    }

   public function update(Request $request, $id)
{
    try {
        $item = InventoryItem::findOrFail($id);
        $originalId = $item->id;

        // Check if this is an ICM update (has ICM-specific fields)
        $isIcmUpdate = $request->hasAny(['icm_type', 'priority', 'problem_description']);

        // Check if this is an IPM update (has IPM-specific fields)
        $isIpmUpdate = $request->hasAny(['system_boot_up', 'hardware', 'performance', 'cables_connections', 'peripherals', 'recommendation', 'date_conducted', 'time_started', 'time_ended']);

        if ($isIcmUpdate) {
            // ICM update validation
            $validated = $request->validate([
                'problem_description' => 'required|string',
                'icm_type' => 'required|string|in:Assistance,Troubleshoot',
                'priority' => 'required|string|in:P1-Critical,P2-Important,P3-Normal,P4-Low',
                'requesting_personnel' => 'required|string|max:255',
                'classification' => 'required|string|max:255',
                'brand_model' => 'required|string|max:255',
                'hardware_software' => 'required|string|in:Hardware,Software',
                'open_date' => 'required|date',
                'open_time' => 'required|date_format:H:i',
                'close_date' => 'nullable|date|after_or_equal:open_date',
                'close_time' => 'nullable|date_format:H:i',
                'icm_findings' => 'nullable|string',
                'actions_taken' => 'nullable|string',
                'icm_recommendations' => 'nullable|string',
            ]);
        } elseif ($isIpmUpdate) {
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
                'unit_price' => 'nullable|numeric|min:0',
                'unit_price_type' => 'required|in:value,na',
                'co_mooe' => 'required|string|max:255',
                'date_acquired' => 'nullable|date',
                'date_acquired_type' => 'required|in:date,na',
                'remarks' => 'nullable|string',
                'serviceability' => 'nullable|string',
            ]);

            // Handle NA values
            if ($request->input('unit_price_type') === 'na') {
                $validated['unit_price'] = null;
            }
            if ($request->input('date_acquired_type') === 'na') {
                $validated['date_acquired'] = null;
            }

            // Remove the _type fields from the data to be stored
            unset($validated['unit_price_type']);
            unset($validated['date_acquired_type']);

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

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item,
                'original_id' => $originalId,
            ], 200);
        }

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
        throw $e;
    } catch (\Exception $e) {
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
        throw $e;
    }
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

    public function icm(Request $request)
    {
        $query = InventoryItem::active()->whereNotNull('icm_no');

        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = array_filter(explode(' ', $search));

            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function($subQuery) use ($term) {
                        $subQuery->where('division', 'LIKE', "%{$term}%")
                                ->orWhere('enduser', 'LIKE', "%{$term}%")
                                ->orWhere('classification', 'LIKE', "%{$term}%")
                                ->orWhere('icm_no', 'LIKE', "%{$term}%")
                                ->orWhere('problem_description', 'LIKE', "%{$term}%")
                                ->orWhere('requesting_personnel', 'LIKE', "%{$term}%");
                    });
                }
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('icm_type')) {
            $query->where('icm_type', $request->icm_type);
        }

        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('open_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('open_date', '<=', $request->date_to);
        }

        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $items = $query->orderBy('icm_no', 'desc')->paginate($perPage)->withQueryString();
        $departments = Department::orderBy('department')->get();
        $employees = Employee::orderBy('firstname')->get();

        if ($request->ajax()) {
            return view('inventory.table-data-icm', compact('items'))->render();
        }

        return view('inventory.tabs.icm', compact('items', 'departments', 'employees', 'perPage'));
    }

    /**
     * Search employees from employee_db
     */
    public function searchEmployees(Request $request)
    {
        $search = $request->get('query', '');
        
        $employees = Employee::where('status', 'ACTIVE')
            ->where(function($q) use ($search) {
                $q->Where('firstname', 'LIKE', "%{$search}%")
                  ->orWhere('lastname', 'LIKE', "%{$search}%")
                  ->orWhere('emp_no', 'LIKE', "%{$search}%");
            })
            ->leftJoin('inventory.departments', 'employees.department', '=', 'departments.dept_no')
            ->select('employees.emp_no', 'employees.firstname', 'employees.lastname', 'departments.department')
            ->limit(10)
            ->get();

        return response()->json($employees);
    }

    /**
     * Get inventory items by requesting personnel (emp_no)
     */
    public function getItemsByPersonnel(Request $request)
    {
        $empNo = $request->get('emp_no');
        
        if (!$empNo) {
            return response()->json([]);
        }

        // Get items for this employee grouped by classification
        $items = InventoryItem::where('emp_no', $empNo)
            ->active()
            ->whereNotNull('classification')
            ->select('no', 'classification', 'brand_model', 'serial_number', 'property_number')
            ->get()
            ->groupBy('classification');

        return response()->json($items);
    }

    /**
     * Get specific inventory item details by ID
     */
    public function getItemDetails(Request $request, $itemId)
    {
        $item = InventoryItem::findOrFail($itemId);

        return response()->json([
            'serial_number' => $item->serial_number,
            'property_number' => $item->property_number,
            'brand_model' => $item->brand_model,
        ]);
    }
}
