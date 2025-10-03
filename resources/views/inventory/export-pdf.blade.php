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
                    <th class="pdf-col-12">Department</th>
                    <th class="pdf-col-12">End User</th>
                    <th class="pdf-col-9">Classification</th>
                    <th class="pdf-col-18">Description</th>
                    <th class="pdf-col-9">Serial No</th>
                    <th class="pdf-col-9">Property No</th>
                    <th class="pdf-col-9 pdf-text-right">Unit Price</th>
                    <th class="pdf-col-5">CO/MOOE</th>
                    <th class="pdf-col-5 pdf-text-center">Date Acquired</th>
                    <th class="pdf-col-9">Remarks</th>
                    <th class="pdf-col-3 pdf-text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td class="pdf-text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->division_name ?? $item->division }}</td>
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
                        <span class="{{ $item->status === 'NEW' ? 'pdf-status-new' : 'pdf-status-replace' }}">
                            {{ $item->status === 'NEW' ? 'NEW' : 'REPL' }}
                        </span>
                    </td>
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
                    $departments = \App\Models\Department::pluck('dept_no')->toArray();
                    $totalNew = 0;
                    $totalReplacement = 0;
                    $grandTotalItems = 0;
                    $departmentData = [];
                @endphp
                @foreach($departments as $dept)
                    @php
                        $deptItems = $items->where('division', $dept);
                        $newItems = $deptItems->where('status', 'NEW');
                        $replacementItems = $deptItems->where('status', '!=', 'NEW');
                        $deptTotal = $deptItems->count();
                        
                        $totalNew += $newItems->count();
                        $totalReplacement += $replacementItems->count();
                        $grandTotalItems += $deptTotal;
                        
                        $departmentData[$dept] = [
                            'new' => $newItems->count(),
                            'replacement' => $replacementItems->count(),
                            'total' => $deptTotal,
                            'name' => \App\Models\Department::where('dept_no', $dept)->first()->department ?? 'Unknown Department'
                        ];
                    @endphp
                    <tr>
                        <td><strong>{{ $departmentData[$dept]['name'] }}</strong></td>
                        <td class="pdf-text-center">{{ $newItems->count() }}</td>
                        <td class="pdf-text-center">{{ $replacementItems->count() }}</td>
                        <td class="pdf-text-center">{{ $deptTotal }}</td>
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
