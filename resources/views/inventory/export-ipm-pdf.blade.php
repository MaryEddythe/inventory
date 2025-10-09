<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        @charset "UTF-8";
        :root { --total-records: "{{ $items->count() }}"; }
        {{ $css }}
        /* Fallback for checkmark and cross symbols */
        .pdf-checkmark::before {
            content: "✓";
            font-family: "DejaVu Sans", Arial, sans-serif;
        }
        .pdf-cross::before {
            content: "✗";
            font-family: "DejaVu Sans", Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="pdf-header">
        <h2>Mines and Geosciences Bureau</h2>
        <h3>Regional Office VI</h3>
        <h1>INVENTORY REPORT SUMMARY</h1>
        <p>Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}</p>
    </div>

    <!-- Detailed IPM -->
    <div class="pdf-mt-3">
        <table class="pdf-table pdf-table-striped">
            <thead>
                <tr>
                    <th colspan="16" class="pdf-bg-dark">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr>
                    <th class="pdf-col-5 pdf-text-center">No</th>
                    <th class="pdf-col-8">Div.</th>
                    <th class="pdf-col-10">User</th>
                    <th class="pdf-col-6">Type</th>
                    <th class="pdf-col-12">Desc</th>
                    <th class="pdf-col-6 pdf-text-center">Condition</th>
                    <th class="pdf-col-5 pdf-text-center">Boot Up</th>
                    <th class="pdf-col-5 pdf-text-center">HW</th>
                    <th class="pdf-col-5 pdf-text-center">Perf</th>
                    <th class="pdf-col-5 pdf-text-center">Cables/Conn</th>
                    <th class="pdf-col-5 pdf-text-center">Periph</th>
                    <th class="pdf-col-6">Rem</th>
                    <th class="pdf-col-8">Rec</th>
                    <th class="pdf-col-6 pdf-text-center">Date</th>
                    <th class="pdf-col-6 pdf-text-center">Start</th>
                    <th class="pdf-col-6 pdf-text-center">End</th>
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
                    <td class="pdf-text-center">
                        <span class="{{ $item->condition === 'Functional' ? 'pdf-status-new' : 'pdf-status-replace' }}">
                            {{ $item->condition === 'Functional' ? 'FUNC' : 'NONFUNC' }}
                        </span>
                    </td>
                    <td class="pdf-text-center"><span class="{{ $item->system_boot_up ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                    <td class="pdf-text-center"><span class="{{ $item->hardware ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                    <td class="pdf-text-center"><span class="{{ $item->performance ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                    <td class="pdf-text-center"><span class="{{ $item->cables_connections ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                    <td class="pdf-text-center"><span class="{{ $item->peripherals ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                    <td>{{ $item->remarks ?? 'N/A' }}</td>
                    <td>{{ $item->recommendation ?? 'N/A' }}</td>
                    <td class="pdf-text-center">{{ $item->date_conducted ? $item->date_conducted->format('m/d/Y') : 'N/A' }}</td>
                    <td class="pdf-text-center">{{ $item->time_started ? \Carbon\Carbon::parse($item->time_started)->format('h:iA') : 'N/A' }}</td>
                    <td class="pdf-text-center">{{ $item->time_ended ? \Carbon\Carbon::parse($item->time_ended)->format('h:iA') : 'N/A' }}</td>
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
                    <th class="pdf-col-20 pdf-text-center">Functional Items</th>
                    <th class="pdf-col-20 pdf-text-center">Nonfunctional Items</th>
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
                        $functionalCount = $group->filter(function ($item) {
                            return $item->condition == 'Functional';
                        })->count();
                        $nonfunctionalCount = $group->filter(function ($item) {
                            return $item->condition == 'Nonfunctional';
                        })->count();
                        $totalCount = $group->count();
                        $departmentSummaries->push([
                            'name' => $dept->department,
                            'functional' => $functionalCount,
                            'nonfunctional' => $nonfunctionalCount,
                            'total' => $totalCount
                        ]);
                    }
                    $totalFunctional = $departmentSummaries->sum('functional');
                    $totalNonfunctional = $departmentSummaries->sum('nonfunctional');
                    $grandTotalItems = $departmentSummaries->sum('total');
                @endphp
                @foreach($departmentSummaries as $summary)
                    <tr>
                        <td><strong>{{ $summary['name'] }}</strong></td>
                        <td class="pdf-text-center">{{ $summary['functional'] }}</td>
                        <td class="pdf-text-center">{{ $summary['nonfunctional'] }}</td>
                        <td class="pdf-text-center">{{ $summary['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="pdf-bg-gray pdf-font-bold">
                    <td><strong>TOTAL</strong></td>
                    <td class="pdf-text-center">{{ $totalFunctional }}</td>
                    <td class="pdf-text-center">{{ $totalNonfunctional }}</td>
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
        Total Records: {{ $items->count() }} | Generated by Inventory Management System ni Idith
    </div>
</body>
</html>