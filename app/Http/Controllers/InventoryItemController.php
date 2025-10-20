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

        // Paginate items first, then group for display
        $paginatedItems = $query->orderBy('enduser')->orderBy('no', 'desc')->paginate(10)->withQueryString();

        // Group the paginated items by enduser for display
        $groupedItems = $paginatedItems->getCollection()->groupBy('enduser');

        $departments = Department::orderBy('department')->get();
        $employees = Employee::orderBy('firstname')->get();

        if ($request->ajax()) {
            return view('inventory.table-data', compact('groupedItems', 'departments', 'employees'))->render();
        }

        return view('inventory.tabs.index', compact('groupedItems', 'departments', 'employees'));
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
        $originalId = $item->id;

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
            'original_id' => $originalId,
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

        $items = $query->orderBy('enduser')->orderBy('no', 'desc')->get();

        $tab = $request->tab ?? 'inventory';
        $css = File::get(public_path('pdf-styles.css'));

        // Encode logos for PDF
        $mgbLogo = base64_encode(file_get_contents(public_path('assets/mgb.jpg')));
        $bpLogo = base64_encode(file_get_contents(public_path('assets/bp.jpg')));

        if ($type === 'pdf') {
            $view = $tab === 'ipm' ? 'inventory.export-ipm-pdf' : 'inventory.export-pdf';
            $pdf = Pdf::loadView($view, compact('items', 'tab', 'css', 'mgbLogo', 'bpLogo'))
                ->setPaper('a3', 'landscape');
            return $pdf->download('inventory.pdf');
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
                // Regular inventory CSV
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

                $filename = 'inventory.csv';
            }

            return response($csv->getContent(), 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        return back()->with('error', 'Invalid export type');
    }

    public function dashboard(Request $request)
    {
        $filterableQuery = InventoryItem::query();

        // Apply date filters
        $this->applyDateFilters($request, $filterableQuery);

        $totalItems = $filterableQuery->count();
        $totalValue = $filterableQuery->sum('unit_price');
        $itemsThisMonth = (clone $filterableQuery)->whereMonth('date_acquired', now()->month)
            ->whereYear('date_acquired', now()->year)
            ->count();
        $totalDivisions = $filterableQuery->distinct('division')->count('division');

        // Get all divisions with item counts, including those with 0 items
        $allDivisions = Department::orderBy('department')->pluck('department');
        $divisionCounts = $filterableQuery
            ->select('division', \DB::raw('count(*) as count'))
            ->groupBy('division')
            ->pluck('count', 'division');

        $divisionData = $allDivisions->map(function ($division) use ($divisionCounts) {
            return (object) [
                'division' => $division,
                'count' => $divisionCounts->get($division, 0)
            ];
        });

        // Update totalDivisions to count unique divisions with items
        $totalDivisions = $divisionCounts->filter(function ($count) {
            return $count > 0;
        })->count();

        // Status data: NEW and FOR REPLACEMENT
        $newCount = (clone $filterableQuery)->where('status', 'NEW')->count();
        $forReplacementCount = (clone $filterableQuery)->where('status', 'FOR REPLACEMENT')->count();
        $statusData = collect([
            (object)['status' => 'NEW', 'count' => $newCount],
            (object)['status' => 'FOR REPLACEMENT', 'count' => $forReplacementCount],
        ]);

        // Condition data: Functional and Nonfunctional
        $functionalCount = (clone $filterableQuery)->where('condition', 'Functional')->count();
        $nonfunctionalCount = (clone $filterableQuery)->where('condition', 'Nonfunctional')->count();
        $conditionData = collect([
            (object)['condition' => 'Functional', 'count' => $functionalCount],
            (object)['condition' => 'Nonfunctional', 'count' => $nonfunctionalCount],
        ]);

        // totalDivisions is already calculated above

        $divisionBreakdown = [];
        foreach ($allDivisions as $division) {
            $breakdown = $filterableQuery
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
            ];
        }

        if ($request->ajax()) {
            return response()->json([
                'totalItems' => (int)$totalItems,
                'totalValue' => (float)$totalValue,
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