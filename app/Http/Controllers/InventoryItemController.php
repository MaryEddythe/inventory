<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Icm;
use App\Models\Cip;
use App\Models\Department;
use App\Models\Employee;
use App\Models\MachineEquipment;
use App\Models\OfficeEquipment;
use App\Models\OtherPpe;
use App\Models\MotorVehicle;
use App\Models\TechnicalScientificEquipment;
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

    private function getDashboardMetrics()
    {
        $query = InventoryItem::active();

        $totalItems = $query->count();
        $totalValue = $query->sum('unit_price');
       $rpcspValue = (clone $query)->where('unit_price', '<=', 49999)
        ->whereNotNull('unit_price')
        ->sum('unit_price');
        $ppeValue = (clone $query)->where('unit_price', '>=', 50000)
            ->where('co_mooe', 'CO')
            ->sum('unit_price');
        $itemsThisMonth = (clone $query)->whereMonth('date_acquired', now()->month)
            ->whereYear('date_acquired', now()->year)
            ->count();

        return [
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'rpcspValue' => $rpcspValue,
            'ppeValue' => $ppeValue,
            'itemsThisMonth' => $itemsThisMonth,
        ];
    }

    public function index(Request $request)
{
    $query = InventoryItem::active();

    if ($request->filled('ppe_type')) {
        if ($request->ppe_type === 'rpcsp') {
            $query->where('unit_price', '<=', 49999)
                ->whereNotNull('unit_price');
        } elseif ($request->ppe_type === 'ppe') {
            $query->where('unit_price', '>=', 50000)
                ->where('co_mooe', 'CO');
        }
    }


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
                'date_conducted' => 'nullable|date',
                'time_started' => 'nullable|date_format:H:i',
                'time_ended' => 'nullable|date_format:H:i|after:time_started',
            ]);

            // Generate ICM number
            $currentYear = now()->year;
            $lastIcmNo = Icm::where('icm_no', 'LIKE', "%-{$currentYear}")
                ->orderBy('icm_no', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastIcmNo) {
                $parts = explode('-', $lastIcmNo->icm_no);
                $nextNumber = (int)$parts[0] + 1;
            }

            $validated['icm_no'] = str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '-' . $currentYear;

            // brand_model holds the inventory_items.no of the selected item.
            // Look it up and denormalise description → brand_model (for display)
            // and pull serial_number / property_number so the ICM record is
            // self-contained even if the inventory item changes later.
            if (!empty($validated['brand_model'])) {
                $linkedItem = InventoryItem::where('no', (int) $validated['brand_model'])
                    ->select('no', 'description', 'serial_number', 'property_number')
                    ->first();

                if ($linkedItem) {
                    // Store the human-readable description in brand_model column
                    $validated['brand_model']    = $linkedItem->description;
                    // Overwrite serial/property with the authoritative values from inventory
                    $validated['serial_number']  = $linkedItem->serial_number;
                    $validated['property_number'] = $linkedItem->property_number;
                }
            }

            $item = Icm::create($validated);
            $metrics = $this->getDashboardMetrics();

            return response()->json([
                'success' => true,
                'message' => 'ICM created successfully with number: ' . $validated['icm_no'],
                'item'    => $item,
                'metrics' => $metrics,
            ], 201);
        } else {
            // Convert empty strings to null for proper validation
            if ($request->input('unit_price') === '') {
                $request->merge(['unit_price' => null]);
            }
            if ($request->input('date_acquired') === '') {
                $request->merge(['date_acquired' => null]);
            }

            // Regular inventory form validation
            $validated = $request->validate([
                'division' => 'required|string|max:255',
                'enduser' => 'required|string|max:255',
                'emp_no' => 'required|string|max:255|exists:employees,emp_no',

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
            $metrics = $this->getDashboardMetrics();

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'item'    => $item,
                'metrics' => $metrics,
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
        // Check if this is an ICM update (has ICM-specific fields)
        $isIcmUpdate = $request->hasAny(['icm_type', 'priority', 'problem_description']);

        // Check if this is an IPM update (has IPM-specific fields)
        $isIpmUpdate = $request->hasAny(['system_boot_up', 'hardware', 'performance', 'cables_connections', 'peripherals', 'recommendation', 'date_conducted', 'time_started', 'time_ended']);

        if ($isIcmUpdate) {
            // Update in ICM table
            $item = Icm::findOrFail($id);
            
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
                'date_conducted' => 'nullable|date',
                'time_started' => 'nullable|date_format:H:i',
                'time_ended' => 'nullable|date_format:H:i|after:time_started',
            ]);

            $item->update($validated);
        } else {
            // Update in InventoryItem table
            $item = InventoryItem::findOrFail($id);
            $originalId = $item->id;

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
                // Pre-process NA values and log request data
                $inputData = $request->all();
                \Log::info('Raw update request for item ' . $id . ':', $inputData);
                
                if (isset($inputData['unit_price_type']) && $inputData['unit_price_type'] === 'na') {
                    $inputData['unit_price'] = null;
                }
                if (isset($inputData['date_acquired_type']) && $inputData['date_acquired_type'] === 'na') {
                    $inputData['date_acquired'] = null;
                }
                $request->merge($inputData);

                // Regular inventory update validation (loosened emp_no validation)
                $validated = $request->validate([
                    'division' => 'required|string|max:255',
                    'enduser' => 'required|string|max:255',
                    'emp_no' => 'required|string|max:255', // Removed exists check to allow updates
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
                
                \Log::info('Update validation passed for item ID: ' . $id, ['data' => $validated]);

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
        }

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $metrics = $this->getDashboardMetrics();
            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item,
                'metrics' => $metrics,
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

        $metrics = $this->getDashboardMetrics();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully',
            'metrics' => $metrics,
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

    // GET THE SUBTYPE FROM REQUEST (must be declared before any use of $subtype)
    $subtype = $request->get('subtype', 'inventory');

    if ($request->tab === 'ipm' && $subtype === 'inventory') {
        $query->where('classification', '!=', 'Monitor');
    }
    
    if ($subtype === 'rpcsp') {
        $query->where('unit_price', '<=', 49999)
              ->whereNotNull('unit_price');
    } elseif ($subtype === 'ppe') {
        $query->where('unit_price', '>=', 50000)
              ->where('co_mooe', 'CO');
    }

    if ($subtype === 'ppe') {
        $items = $query->orderByRaw("SUBSTRING_INDEX(enduser, ' ', -1) ASC")->get();
    } else {
        $items = $query->orderBy('enduser')->orderBy('no', 'desc')->get();
    }

    // NOTE: do NOT reassign $items after this point — the line below was removed
    // because it was clobbering the filtered result set, causing RPCSP/PPE PDF
    // exports to include ALL items instead of only those matching the subtype filter.

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
        $rpcspValue = InventoryItem::active()->where('unit_price', '<=', 49999)->whereNotNull('unit_price')->sum('unit_price');
        $rpcspCount = InventoryItem::active()->where('unit_price', '<=', 49999)->whereNotNull('unit_price')->count();
        $pdf = Pdf::loadView($view, compact('items', 'tab', 'css', 'mgbLogo', 'bpLogo', 'rpcspValue', 'rpcspCount'))
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
    $filterableQuery = InventoryItem::active();

    // Apply date filters
    $this->applyDateFilters($request, $filterableQuery);

    // Apply classification filters (RPCSP and PPE)
    $classifications = $request->get('classifications');
    if ($classifications) {
        $classArray = explode(',', $classifications);
        $filterableQuery->where(function ($query) use ($classArray) {
            foreach ($classArray as $classification) {
                if ($classification === 'rpcsp') {
                    $query->orWhere(function ($q) {
                        $q->where('unit_price', '<=', 49999)
                          ->whereNotNull('unit_price');
                    });
                } elseif ($classification === 'ppe') {
                    $query->orWhere(function ($q) {
                        $q->where('unit_price', '>=', 50000)
                          ->where('co_mooe', 'CO');
                    });
                }
            }
        });
    }

    $totalItems = $filterableQuery->count();
    $totalValue = $filterableQuery->sum('unit_price');
    $rpcspValue = (clone $filterableQuery)->where('unit_price', '<=', 49999)
    ->whereNotNull('unit_price')
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

        $items = $query->orderByRaw("SUBSTRING_INDEX(enduser, ' ', -1) ASC")->paginate($perPage)->withQueryString();
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

    public function storeMotorVehicle(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:motor_vehicles,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $vehicle = MotorVehicle::create($validated);

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Motor vehicle added successfully',
                'vehicle' => $vehicle,
            ], 201);
        }

        return redirect()
            ->route('inventory.tabs.moto-vehicle')
            ->with('success', 'Motor vehicle added successfully!');
    }

    public function updateMotorVehicle(Request $request, MotorVehicle $motorVehicle)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('motor_vehicles', 'property_number')->ignore($motorVehicle->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $motorVehicle->update($validated);

        return redirect()
            ->route('inventory.tabs.moto-vehicle')
            ->with('success', 'Motor vehicle updated successfully!');
    }

    public function destroyMotorVehicle(MotorVehicle $motorVehicle)
    {
        $motorVehicle->delete();

        return redirect()
            ->route('inventory.tabs.moto-vehicle')
            ->with('success', 'Motor vehicle deleted successfully!');
    }

    public function storeCip(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:cips,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'nullable|date',
            'date_acquired_type' => 'required|in:date,na',
            'remarks' => 'nullable|string',
        ]);

        if ($request->input('date_acquired_type') === 'na') {
            $validated['date_acquired'] = null;
        }

        unset($validated['date_acquired_type']);

        $cip = Cip::create($validated);

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'CIP added successfully',
                'cip' => $cip,
            ], 201);
        }

        return redirect()
            ->route('inventory.tabs.cip')
            ->with('success', 'CIP added successfully!');
    }

    public function updateCip(Request $request, Cip $cip)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cips', 'property_number')->ignore($cip->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'nullable|date',
            'date_acquired_type' => 'required|in:date,na',
            'remarks' => 'nullable|string',
        ]);

        if ($request->input('date_acquired_type') === 'na') {
            $validated['date_acquired'] = null;
        }

        unset($validated['date_acquired_type']);

        $cip->update($validated);

        return redirect()
            ->route('inventory.tabs.cip')
            ->with('success', 'CIP updated successfully!');
    }

    public function destroyCip(Cip $cip)
    {
        $cip->delete();

        return redirect()
            ->route('inventory.tabs.cip')
            ->with('success', 'CIP deleted successfully!');
    }

    public function storeMachineEquipment(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:machine_equipments,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        MachineEquipment::create($validated);

        return redirect()
            ->route('inventory.tabs.machine-equipment')
            ->with('success', 'Machine & Equipment added successfully!');
    }

    public function updateMachineEquipment(Request $request, MachineEquipment $machineEquipment)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('machine_equipments', 'property_number')->ignore($machineEquipment->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $machineEquipment->update($validated);

        return redirect()
            ->route('inventory.tabs.machine-equipment')
            ->with('success', 'Machine & Equipment updated successfully!');
    }

    public function destroyMachineEquipment(MachineEquipment $machineEquipment)
    {
        $machineEquipment->delete();

        return redirect()
            ->route('inventory.tabs.machine-equipment')
            ->with('success', 'Machine & Equipment deleted successfully!');
    }

    public function storeOfficeEquipment(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:office_equipments,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        OfficeEquipment::create($validated);

        return redirect()
            ->route('inventory.tabs.office-equipment')
            ->with('success', 'Office Equipment added successfully!');
    }

    public function updateOfficeEquipment(Request $request, OfficeEquipment $officeEquipment)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('office_equipments', 'property_number')->ignore($officeEquipment->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $officeEquipment->update($validated);

        return redirect()
            ->route('inventory.tabs.office-equipment')
            ->with('success', 'Office Equipment updated successfully!');
    }

    public function destroyOfficeEquipment(OfficeEquipment $officeEquipment)
    {
        $officeEquipment->delete();

        return redirect()
            ->route('inventory.tabs.office-equipment')
            ->with('success', 'Office Equipment deleted successfully!');
    }

    public function storeTechnicalScientificEquipment(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:technical_scientific_equipments,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        TechnicalScientificEquipment::create($validated);

        return redirect()
            ->route('inventory.tabs.technical-scientific-equipment')
            ->with('success', 'Technical and Scientific Equipment added successfully!');
    }

    public function updateTechnicalScientificEquipment(Request $request, TechnicalScientificEquipment $technicalScientificEquipment)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('technical_scientific_equipments', 'property_number')->ignore($technicalScientificEquipment->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $technicalScientificEquipment->update($validated);

        return redirect()
            ->route('inventory.tabs.technical-scientific-equipment')
            ->with('success', 'Technical and Scientific Equipment updated successfully!');
    }

    public function destroyTechnicalScientificEquipment(TechnicalScientificEquipment $technicalScientificEquipment)
    {
        $technicalScientificEquipment->delete();

        return redirect()
            ->route('inventory.tabs.technical-scientific-equipment')
            ->with('success', 'Technical and Scientific Equipment deleted successfully!');
    }

    public function storeOtherPpe(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:other_ppes,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        OtherPpe::create($validated);

        return redirect()
            ->route('inventory.tabs.other-ppe')
            ->with('success', 'Other PPE added successfully!');
    }

    public function updateOtherPpe(Request $request, OtherPpe $otherPpe)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('other_ppes', 'property_number')->ignore($otherPpe->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $otherPpe->update($validated);

        return redirect()
            ->route('inventory.tabs.other-ppe')
            ->with('success', 'Other PPE updated successfully!');
    }

    public function destroyOtherPpe(OtherPpe $otherPpe)
    {
        $otherPpe->delete();

        return redirect()
            ->route('inventory.tabs.other-ppe')
            ->with('success', 'Other PPE deleted successfully!');
    }

    public function icm(Request $request)
    {
        $query = Icm::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = array_filter(explode(' ', $search));

            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function($subQuery) use ($term) {
                        $subQuery->where('division', 'LIKE', "%{$term}%")
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

        \Log::info('searchEmployees called', ['search' => $search]);

        try {
            $employees = Employee::where('status', 'active')
                ->where(function($q) use ($search) {
                    $q->where('firstname', 'LIKE', "%{$search}%")
                      ->orWhere('lastname', 'LIKE', "%{$search}%")
                      ->orWhere('emp_no', 'LIKE', "%{$search}%");
                })
                ->with('departmentInfo')
                ->limit(10)
                ->get();

            \Log::info('Employees query executed', ['count' => $employees->count()]);

            $result = $employees->map(function($employee) {
                return [
                    'emp_no' => (string)$employee->emp_no,
                    'firstname' => $employee->firstname ?? '',
                    'lastname' => $employee->lastname ?? '',
                    'department' => $employee->department ?? '',
                    'department_name' => $employee->departmentInfo ? $employee->departmentInfo->department : ($employee->department ?? 'Unknown'),
                    'fullname' => trim(($employee->firstname ?? '') . ' ' . ($employee->lastname ?? ''))
                ];
            });

            \Log::info('Employees mapped', ['count' => $result->count(), 'result' => $result->toArray()]);

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Error in searchEmployees', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to search employees', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get inventory items by requesting personnel (emp_no)
     */
   public function getItemsByPersonnel(Request $request)
{
    try {
        $empNo = $request->get('emp_no');
        
        \Log::info('getItemsByPersonnel called', [
            'emp_no' => $empNo, 
            'type' => gettype($empNo)
        ]);

        if (!$empNo) {
            return response()->json([]);
        }

        // Remove 'brand_model' from the select - it doesn't exist in your table
        $items = InventoryItem::where('emp_no', $empNo)
            ->orWhere('emp_no', (string)$empNo)
            ->orWhere('emp_no', (int)$empNo)
            ->active()
            ->whereNotNull('classification')
            ->select('no', 'classification', 'description', 'serial_number', 'property_number')
            ->get();
        
        \Log::info('Items found', [
            'count' => $items->count(),
            'items' => $items->toArray()
        ]);
        
        // Group by classification
        $groupedItems = $items->groupBy('classification');
        
        return response()->json($groupedItems);
        
    } catch (\Exception $e) {
        \Log::error('Error in getItemsByPersonnel', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Failed to fetch items',
            'message' => $e->getMessage()
        ], 500);
    }
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
