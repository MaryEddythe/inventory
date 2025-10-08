<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>:root { --total-records: "{{ $items->count() }}"; } {{ $css }}</style>
</head>
<body>
    <div class="pdf-header">
        <h2>Mines and Geosciences Bureau</h2>
        <h3>Regional Office VI</h3>
        <h1>INVENTORY REPORT SUMMARY</h1>
        <p>Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}</p>
    </div>

    <!-- Detailed Inventory -->
    <div class="pdf-mt-3">
        <table class="pdf-table pdf-table-striped">
            <thead>
                <tr>
                    <th colspan="12" class="pdf-bg-dark">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr>
                    <th class="pdf-col-0 pdf-text-center">No</th>
                    <th class="pdf-col-8">Department</th>
                    <th class="pdf-col-8">End User</th>
                    <th class="pdf-col-6">Classification</th>
                    <th class="pdf-col-12">Description</th>
                    <th class="pdf-col-6">Serial No</th>
                    <th class="pdf-col-6">Property No</th>
                    <th class="pdf-col-6 pdf-text-right">Unit Price</th>
                    <th class="pdf-col-4">CO/MOOE</th>
                    <th class="pdf-col-4 pdf-text-center">Date Acquired</th>
                    <th class="pdf-col-6">Remarks</th>
                    <th class="pdf-col-3 pdf-text-center">Status</th>
                    <th class="pdf-col-2 pdf-text-center">Sys Boot</th>
                    <th class="pdf-col-2 pdf-text-center">HW</th>
                    <th class="pdf-col-2 pdf-text-center">Perf</th>
                    <th class="pdf-col-2 pdf-text-center">Cables</th>
                    <th class="pdf-col-2 pdf-text-center">Periph</th>
                    <th class="pdf-col-8">Recommendation</th>
                    <th class="pdf-col-4 pdf-text-center">Date Cond</th>
                    <th class="pdf-col-3 pdf-text-center">Time Start</th>
                    <th class="pdf-col-3 pdf-text-center">Time End</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td class="pdf-text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->department_name ?? $item->division }}</td>
                    <td>{{ $item->enduser }}</td>
                    <td>{{ $item->classification }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->serial_number ?? 'N/A' }}</td>
                    <td>{{ $item->property_number }}</td>
                    <td class="pdf-text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->co_mooe }}</td>
                    <td class="pdf-text-center pdf-nowrap">{{ $item->date_acquired->format('m/d/Y') }}</td>
                    <td>{{ $item->remarks ?? 'N/A' }}</td>
                    <td class="pdf-text-center">
                        <span class="{{ $item->status === 'Functional' ? 'pdf-status-new' : 'pdf-status-replace' }}">
                            {{ $item->status === 'Functional' ? 'FUNC' : 'NONFUNC' }}
                        </span>
                    </td>
                    <td class="pdf-text-center">{{ $item->system_boot_up ? '✓' : '✗' }}</td>
                    <td class="pdf-text-center">{{ $item->hardware ? '✓' : '✗' }}</td>
                    <td class="pdf-text-center">{{ $item->performance ? '✓' : '✗' }}</td>
                    <td class="pdf-text-center">{{ $item->cables_connections ? '✓' : '✗' }}</td>
                    <td class="pdf-text-center">{{ $item->peripherals ? '✓' : '✗' }}</td>
                    <td>{{ $item->recommendation ?? 'N/A' }}</td>
                    <td class="pdf-text-center">{{ $item->date_conducted ? $item->date_conducted->format('m/d/Y') : 'N/A' }}</td>
                    <td class="pdf-text-center">{{ $item->time_started ?? 'N/A' }}</td>
                    <td class="pdf-text-center">{{ $item->time_ended ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>     
    </div>

    <!-- Executive Summary -->
    <div class="pdf-summary-section pdf-mt-3">
        <table class="pdf-summary-table pdf-table-striped">
            <thead>
                <tr>
                    <th colspan="4" class="pdf-bg-dark">EXECUTIVE SUMMARY BY DEPARTMENT</th>
                </tr>
                <tr class="pdf-bg-primary">
                    <th class="pdf-col-40">Department</th>
                    <th class="pdf-col-20 pdf-text-center">New Items</th>
                    <th class="pdf-col-20 pdf-text-center">For Replacement</th>
                    <th class="pdf-col-20 pdf-text-center">Total Items</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $departmentSummaries = collect();
                    $allDepts = \App\Models\Department::all();
                    foreach ($allDepts as $dept) {
                        $group = $items->filter(function ($item) use ($dept) {
                            return $item->division == $dept->department;
                        });
                        $newCount = $group->filter(function ($item) {
                            return $item->status == 'NEW';
                        })->count();
                        $replacementCount = $group->filter(function ($item) {
                            return $item->status != 'NEW';
                        })->count();
                        $totalCount = $group->count();
                        $departmentSummaries->push([
                            'name' => $dept->department,
                            'new' => $newCount,
                            'replacement' => $replacementCount,
                            'total' => $totalCount
                        ]);
                    }
                    $totalNew = $departmentSummaries->sum('new');
                    $totalReplacement = $departmentSummaries->sum('replacement');
                    $grandTotalItems = $departmentSummaries->sum('total');
                @endphp
                @foreach($departmentSummaries as $summary)
                    <tr>
                        <td><strong>{{ $summary['name'] }}</strong></td>
                        <td class="pdf-text-center">{{ $summary['new'] }}</td>
                        <td class="pdf-text-center">{{ $summary['replacement'] }}</td>
                        <td class="pdf-text-center">{{ $summary['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="pdf-bg-gray pdf-font-bold">
                    <td><strong>TOTAL</strong></td>
                    <td class="pdf-text-center">{{ $totalNew }}</td>
                    <td class="pdf-text-center">{{ $totalReplacement }}</td>
                    <td class="pdf-text-center">{{ $grandTotalItems }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="pdf-signature-section pdf-mt-3" style="page-break-inside: avoid;">
        <table style="width: 100%; border: none;">
            <tr>
                <td class="pdf-col-35 pdf-text-left">
                    <span class="pdf-signature-label">Prepared by:</span>
                    <span class="pdf-signature-name">HERO JOHN E. LAPORGA</span><br>
                    <span class="pdf-signature-title">Senior IT Support Specialist</span>
                </td>
                <td class="pdf-col-30"></td>
                <td class="pdf-col-35 pdf-text-right">
                    <span class="pdf-signature-label">Reviewed by:</span>
                    <span class="pdf-signature-name">MAY FLORENCE A. PABELONIO</span><br>
                    <span class="pdf-signature-title">ICT Focal Person</span>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="pdf-text-center pdf-pt-3">
                    <span class="pdf-signature-name">CECILIA L. OCHAVO-SAYCON</span><br>
                    <span class="pdf-signature-title">Regional Director</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer pdf-mt-2">
        Total Records: {{ $items->count() }} | Generated by Inventory Management System ni Idith</p>
    </div>
</body>
</html>
