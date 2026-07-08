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
use App\Models\FurnitureFixture;
use App\Models\MilitaryPoliceSecurityEquipment;
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

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQuery) use ($term) {
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

        $items = $query->orderByRaw("SUBSTRING_INDEX(enduser, ' ', -1) ASC")
            ->paginate($perPage)
            ->withQueryString();

        $groupedItems = $items->getCollection()->groupBy('enduser');

        $departments = Department::orderBy('department')->get();
        $employees = Employee::orderBy('firstname')->get();

        return view('inventory.tabs.index', compact(
            'items',
            'groupedItems',
            'departments',
            'employees',
            'perPage'
        ));
    }

    public function create()
    {
        $departments = Department::orderBy('department')->get();
        $employees = Employee::with('departmentInfo')->orderBy('firstname')->get(['emp_no', 'firstname', 'lastname', 'department']);
        return view('inventory.modals.create-modal', compact('departments', 'employees'));
    }

    public function store(Request $request)
    {
        $isIcmSubmission = $request->filled('icm_type') && $request->filled('priority') && $request->filled('problem_description');

        if ($isIcmSubmission) {
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
    $tab = $request->tab ?? 'inventory';
    $subtype = $request->get('subtype', 'inventory');

    // ─────────────────────────────────────────────────────────────────────────────
    // ICM export (inventory.icm table only)
    // ─────────────────────────────────────────────────────────────────────────────
    if ($tab === 'icm') {
        $query = Icm::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = array_filter(explode(' ', $search));

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQuery) use ($term) {
                        $subQuery->where('division', 'LIKE', "%{$term}%")
                            ->orWhere('classification', 'LIKE', "%{$term}%")
                            ->orWhere('icm_no', 'LIKE', "%{$term}%")
                            ->orWhere('problem_description', 'LIKE', "%{$term}%")
                            ->orWhere('requesting_personnel', 'LIKE', "%{$term}%")
                            ->orWhere('brand_model', 'LIKE', "%{$term}%")
                            ->orWhere('serial_number', 'LIKE', "%{$term}%")
                            ->orWhere('property_number', 'LIKE', "%{$term}%")
                            ->orWhere('icm_findings', 'LIKE', "%{$term}%")
                            ->orWhere('actions_taken', 'LIKE', "%{$term}%")
                            ->orWhere('icm_recommendations', 'LIKE', "%{$term}%");
                    });
                }
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
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

        // Keep consistent ordering with icm() page
        $items = $query->orderBy('icm_no', 'desc')->get();

        $css = File::get(public_path('pdf-styles.css'));
        $mgbLogo = base64_encode(file_get_contents(public_path('assets/mgb.jpg')));
        $bpLogo = base64_encode(file_get_contents(public_path('assets/bp.jpg')));

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('inventory.export-icm', [
                'items' => $items,
                'tab' => 'icm',
                'title' => 'ICM',
                'css' => $css,
                'mgbLogo' => $mgbLogo,
                'bpLogo' => $bpLogo,
            ])->setPaper('landscape');

            return $pdf->download('icm.pdf');
        }

        if ($type === 'csv') {
            $csv = Writer::createFromString('');
            $headers = [
                'ICM No',
                'Div.',
                'Personnel',
                'Problem Description',
                'Type',
                'Priority',
                'HW/SW',
                'Brand/Model',
                'Serial No',
                'Prop. No',
                'Open Date',
                'Close Date',
                'Findings',
                'Actions',
                'Recommendations',
            ];

            $csv->insertOne($headers);

            foreach ($items as $item) {
                $csv->insertOne([
                    $item->icm_no ?? 'N/A',
                    $item->division ?? 'N/A',
                    $item->requesting_personnel ?? 'N/A',
                    $item->problem_description ?? 'N/A',
                    $item->icm_type ?? 'N/A',
                    $item->priority ?? 'N/A',
                    $item->hardware_software ?? 'N/A',
                    $item->brand_model ?? 'N/A',
                    $item->serial_number ?? 'N/A',
                    $item->property_number ?? 'N/A',
                    $item->open_date ? $item->open_date->format('M d, Y') : 'N/A',
                    $item->close_date ? $item->close_date->format('M d, Y') : 'N/A',
                    $item->icm_findings ?? 'N/A',
                    $item->actions_taken ?? 'N/A',
                    $item->icm_recommendations ?? 'N/A',
                ]);
            }

            return response($csv->getContent(), 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="icm.csv"');
        }

        return back()->with('error', 'Invalid export type');
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Existing Inventory/IPM/RPCSP/PPE exports
    // ─────────────────────────────────────────────────────────────────────────────
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

    $css = File::get(public_path('pdf-styles.css'));

    $mgbLogo = base64_encode(file_get_contents(public_path('assets/mgb.jpg')));
    $bpLogo = base64_encode(file_get_contents(public_path('assets/bp.jpg')));

    if ($type === 'pdf') {
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
                        'RPCSP Export'
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

    public function exportCategoryPdf(Request $request, string $category)
    {
        $categories = [
            'machine-equipment' => [
                'model' => MachineEquipment::class,
                'title' => 'Machine & Equipment',
                'filename' => 'machine-equipment.pdf',
                'view' => 'inventory.export-machine-equipment-pdf',
            ],
            'office-equipment' => [
                'model' => OfficeEquipment::class,
                'title' => 'Office Equipment',
                'filename' => 'office-equipment.pdf',
                'view' => 'inventory.export-office-equipment-pdf',
            ],
            'technical-scientific-equipment' => [
                'model' => TechnicalScientificEquipment::class,
                'title' => 'Technical and Scientific Equipment',
                'filename' => 'technical-scientific-equipment.pdf',
                'view' => 'inventory.export-technical-scientific-equipment-pdf',
            ],
            'other-ppe' => [
                'model' => OtherPpe::class,
                'title' => 'Other PPE',
                'filename' => 'other-ppe.pdf',
                'view' => 'inventory.export-other-ppe-pdf',
            ],
            'furniture-fixtures' => [
                'model' => FurnitureFixture::class,
                'title' => 'Furniture/Fixtures',
                'filename' => 'furniture-fixtures.pdf',
                'view' => 'inventory.export-furniture-fixtures-pdf',
            ],
            'military-police-security' => [
                'model' => MilitaryPoliceSecurityEquipment::class,
                'title' => 'Military, Police & Security Equipment',
                'filename' => 'military-police-security.pdf',
                'view' => 'inventory.export-military-police-security-pdf',
            ],
            'cip' => [
                'model' => Cip::class,
                'title' => 'CIP',
                'filename' => 'cip.pdf',
                'view' => 'inventory.export-cip-pdf',
            ],
            'moto-vehicle' => [
                'model' => MotorVehicle::class,
                'title' => 'Motor Vehicle',
                'filename' => 'motor-vehicle.pdf',
                'view' => 'inventory.export-motor-vehicle-pdf',
            ],
        ];

        abort_unless(isset($categories[$category]), 404);

        $config = $categories[$category];
        $model = $config['model'];
        $items = $model::latest()->get();
        $title = $config['title'];
        $css = File::get(public_path('pdf-styles.css'));
        $mgbLogo = base64_encode(file_get_contents(public_path('assets/mgb.jpg')));
        $bpLogo = base64_encode(file_get_contents(public_path('assets/bp.jpg')));

        $pdf = Pdf::loadView($config['view'], compact('items', 'title', 'category', 'css', 'mgbLogo', 'bpLogo'))
            ->setPaper('legal', 'landscape');

        return $pdf->download($config['filename']);
    }

    public function dashboard(Request $request)
    {
        // Non-AJAX: return the blade page (initial render)
        if (!$request->ajax() && $request->header('X-Requested-With') !== 'XMLHttpRequest') {
            return view('inventory.tabs.dashboard');
        }

        // AJAX/XHR: return JSON expected by resources/views/inventory/tabs/dashboard.blade.php
        $query = InventoryItem::active();

        // Apply same date filter logic used elsewhere
        $this->applyDateFilters($request, $query);

        // Classification filtering (rpcsp / ppe) driven by dashboard checkboxes
        $classifications = $request->get('classifications', []);
        if (is_string($classifications)) {
            // dashboard JS sends comma-separated sometimes
            $classifications = array_filter(array_map('trim', explode(',', $classifications)));
        }

        if (is_array($classifications) && !empty($classifications)) {
            $query->where(function ($q) use ($classifications) {
                foreach ($classifications as $classification) {
                    if ($classification === 'rpcsp') {
                        $q->orWhere(function ($rpcsp) {
                            $rpcsp->where('unit_price', '<=', 49999)
                                ->whereNotNull('unit_price');
                        });
                    }
                    if ($classification === 'ppe') {
                        $q->orWhere(function ($ppe) {
                            $ppe->where('unit_price', '>=', 50000)
                                ->where('co_mooe', 'CO');
                        });
                    }
                }
            });
        }

        // Metrics
        $totalItems = (clone $query)->count();
        $rpcspValue = (clone $query)
            ->where('unit_price', '<=', 49999)
            ->whereNotNull('unit_price')
            ->sum('unit_price');

        $ppeValue = (clone $query)
            ->where('unit_price', '>=', 50000)
            ->where('co_mooe', 'CO')
            ->sum('unit_price');

        $itemsThisMonth = (clone $query)
            ->whereMonth('date_acquired', now()->month)
            ->whereYear('date_acquired', now()->year)
            ->count();

        // Division summary + breakdown
        $divisionData = (clone $query)
            ->select('division', 
                \DB::raw('COUNT(*) as count')
            )
            ->groupBy('division')
            ->orderBy('count', 'desc')
            ->get();

        $divisionBreakdown = [];
        if ($divisionData->count() > 0) {
            // classification counts inside each division
            $breakdownRows = (clone $query)
                ->select('division', 'classification', \DB::raw('COUNT(*) as count'))
                ->groupBy('division', 'classification')
                ->get();

            foreach ($breakdownRows as $row) {
                $divisionKey = $row->division;
                if (!isset($divisionBreakdown[$divisionKey])) {
                    $divisionBreakdown[$divisionKey] = [];
                }
                // The dashboard expects keys like Desktop/Laptop/Monitor/... based on classification value
                $divisionBreakdown[$divisionKey][$row->classification] = (int) $row->count;
            }
        }

        // Status summary
        $statusData = (clone $query)
            ->select('status', \DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        // Condition summary (the dashboard expects `condition` and `count`)
        // Use inventory_items.condition column directly.
        $conditionData = (clone $query)
            ->select('condition', \DB::raw('COUNT(*) as count'))
            ->groupBy('condition')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json([
            'totalItems' => $totalItems,
            'rpcspValue' => (float) $rpcspValue,
            'ppeValue' => (float) $ppeValue,
            'itemsThisMonth' => $itemsThisMonth,
            'totalDivisions' => (int) $divisionData->count(),
            'divisionData' => $divisionData,
            'divisionBreakdown' => $divisionBreakdown,
            'statusData' => $statusData,
            'conditionData' => $conditionData,
        ]);
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

        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

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

    public function storeFurnitureFixture(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:furniture_fixtures,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

        FurnitureFixture::create($validated);

        return redirect()
            ->route('inventory.tabs.furniture-fixtures')
            ->with('success', 'Furniture/Fixtures added successfully!');
    }

    public function updateFurnitureFixture(Request $request, FurnitureFixture $furnitureFixture)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('furniture_fixtures', 'property_number')->ignore($furnitureFixture->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        // Auto-set CO/MOOE based on unit_value cutoff
        // <= 49999 => RPCSP
        // >= 50000 => PPE
        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

        $furnitureFixture->update($validated);

        return redirect()
            ->route('inventory.tabs.furniture-fixtures')
            ->with('success', 'Furniture/Fixtures updated successfully!');
    }

    public function destroyFurnitureFixture(FurnitureFixture $furnitureFixture)
    {
        $furnitureFixture->delete();

        return redirect()
            ->route('inventory.tabs.furniture-fixtures')
            ->with('success', 'Furniture/Fixtures deleted successfully!');
    }

    public function storeMilitaryPoliceSecurityEquipment(Request $request)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => 'required|string|max:255|unique:military_police_security_equipments,property_number',
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

        MilitaryPoliceSecurityEquipment::create($validated);

        return redirect()
            ->route('inventory.tabs.military-police-security')
            ->with('success', 'Military, Police & Security Equipment added successfully!');
    }

    public function updateMilitaryPoliceSecurityEquipment(Request $request, MilitaryPoliceSecurityEquipment $militaryPoliceSecurityEquipment)
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'description' => 'required|string',
            'property_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('military_police_security_equipments', 'property_number')->ignore($militaryPoliceSecurityEquipment->id),
            ],
            'unit_value' => 'required|numeric|min:0',
            'date_acquired' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $validated['co_mooe'] = ((float) $validated['unit_value'] >= 50000) ? 'PPE' : 'RPCSP';

        $militaryPoliceSecurityEquipment->update($validated);

        return redirect()
            ->route('inventory.tabs.military-police-security')
            ->with('success', 'Military, Police & Security Equipment updated successfully!');
    }

    public function destroyMilitaryPoliceSecurityEquipment(MilitaryPoliceSecurityEquipment $militaryPoliceSecurityEquipment)
    {
        $militaryPoliceSecurityEquipment->delete();

        return redirect()
            ->route('inventory.tabs.military-police-security')
            ->with('success', 'Military, Police & Security Equipment deleted successfully!');
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
        // Frontend historically used `q`; newer CTO modal uses `query`.
        $search = $request->get('query', $request->get('q', ''));

        \Log::info('searchEmployees called', [
            'search' => $search,
            'query_param_query' => $request->get('query'),
            'query_param_q' => $request->get('q'),
        ]);

        try {
            $employees = Employee::where('status', 'active')
                ->where(function($q) use ($search) {
                    $q->orWhere('firstname', 'LIKE', "%{$search}%")
                      ->orWhere('lastname', 'LIKE', "%{$search}%")
                      ->orWhere('emp_no', 'LIKE', "%{$search}%");
                })
                ->with('departmentInfo')
                ->limit(10)
                ->get();

            \Log::info('Employees query executed', ['count' => $employees->count()]);

            $result = $employees->map(function($employee) {
                $division = $employee->departmentInfo;

                // Inventory/employee DB may store names as either:
                // - first_name / last_name
                // - firstname / lastname
                $first = $employee->first_name ?? $employee->firstname ?? '';
                $last  = $employee->last_name ?? $employee->lastname ?? '';

                $divisionCodeOrName = null;
                if ($division) {
                    $divisionCodeOrName = $division->department ?? $division->code ?? $division->name ?? null;
                }

                return [
                    'emp_no' => (string)$employee->emp_no,

                    // UI expects `firstname/lastname` (see create-icm-modal.blade.php)
                    'firstname' => $first,
                    'lastname' => $last,

                    // legacy field kept
                    'department' => $divisionCodeOrName ?? '',

                    // what the UI needs: full division/department name
                    'department_name' => $divisionCodeOrName ?? 'Unknown',

                    'fullname' => trim($first . ' ' . $last),
                    'full_name' => trim($first . ' ' . $last),
                    'position' => $employee->position ?? $employee->Role ?? '',
                    'role' => $employee->role ?? $employee->Role ?? 'N/A',
                    'division_code' => $divisionCodeOrName ?? '',
                    'employment_type' => $employee->employment_type ?? 'PERMANENT',
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
