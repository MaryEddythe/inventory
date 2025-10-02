<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <link rel="stylesheet" href="{{ asset('pdf-styles.css') }}">

</head>
<body>
    <div class="pdf-header">
        <h1>INVENTORY REPORT SUMMARY</h1>
        <p>Generated on: {{ now()->format('F d, Y h:i A') }} | Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}</p>
    </div>

    <!-- Executive Summary -->
    <div class="pdf-summary-section">
        <table class="pdf-summary-table">
            <thead>
                <tr>
                    <th colspan="5" style="text-align: center; background-color: #343a40; color: white;">EXECUTIVE SUMMARY BY DEPARTMENT</th>
                </tr>
                <tr>
                    <th width="25%">Department</th>
                    <th width="15%" class="pdf-text-center">New Items</th>
                    <th width="20%" class="pdf-text-center">For Replacement</th>
                    <th width="15%" class="pdf-text-center">Total Items</th>
                    <th width="25%" class="pdf-text-center">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $departments = \App\Models\Department::pluck('dept_no')->toArray();
                    $totalNew = 0;
                    $totalReplacement = 0;
                    $grandTotalItems = 0;
                    $grandTotalValue = 0;
                    $departmentData = [];
                @endphp
                @foreach($departments as $dept)
                    @php
                        $deptItems = $items->where('division', $dept);
                        $newItems = $deptItems->where('status', 'NEW');
                        $replacementItems = $deptItems->where('status', '!=', 'NEW');
                        $deptTotal = $deptItems->count();
                        $deptValue = $deptItems->sum('unit_price');
                        
                        $totalNew += $newItems->count();
                        $totalReplacement += $replacementItems->count();
                        $grandTotalItems += $deptTotal;
                        $grandTotalValue += $deptValue;
                        
                        $departmentData[$dept] = [
                            'new' => $newItems->count(),
                            'replacement' => $replacementItems->count(),
                            'total' => $deptTotal,
                            'value' => $deptValue,
                            'name' => \App\Models\Department::where('dept_no', $dept)->first()->department ?? 'Unknown Department'
                        ];
                    @endphp
                    <tr>
                        <td><strong>{{ $departmentData[$dept]['name'] }}</strong></td>
                        <td class="text-center">{{ $newItems->count() }}</td>
                        <td class="text-center">{{ $replacementItems->count() }}</td>
                        <td class="text-center">{{ $deptTotal }}</td>
                        <td class="text-right">₱{{ number_format($deptValue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #dee2e6; font-weight: bold;">
                    <td><strong>TOTAL</strong></td>
                    <td class="text-center">{{ $totalNew }}</td>
                    <td class="text-center">{{ $totalReplacement }}</td>
                    <td class="text-center">{{ $grandTotalItems }}</td>
                    <td class="text-right">₱{{ number_format($grandTotalValue, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Quick Stats -->
    <div class="pdf-overall-summary">
        <table width="100%">
            <tr>
                <td width="25%"><strong>Total New Items:</strong> {{ $totalNew }}</td>
                <td width="25%"><strong>Total For Replacement:</strong> {{ $totalReplacement }}</td>
                <td width="25%"><strong>Overall Total Items:</strong> {{ $grandTotalItems }}</td>
                <td width="25%"><strong>Total Inventory Value:</strong> ₱{{ number_format($grandTotalValue, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Detailed Inventory -->
    <div style="margin-top: 12px;">
        <table class="pdf-table">
            <thead>
                <tr>
                    <th colspan="12" style="text-align: center; background-color: #343a40; color: white;">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr>
                    <th width="4%" class="text-center">No</th>
                    <th width="8%">Department</th>
                    <th width="10%">End User</th>
                    <th width="9%">Classification</th>
                    <th width="16%">Description</th>
                    <th width="9%">Serial No</th>
                    <th width="9%">Property No</th>
                    <th width="7%" class="text-right">Unit Price</th>
                    <th width="8%">CO/MOOE</th>
                    <th width="7%" class="text-center">Date Acquired</th>
                    <th width="9%">Remarks</th>
                    <th width="4%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $item->no }}</td>
                    <td>{{ $item->division_name }}</td>
                    <td>{{ $item->enduser }}</td>
                    <td>{{ $item->classification }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->serial_number ?? 'N/A' }}</td>
                    <td>{{ $item->property_number }}</td>
                    <td class="text-right nowrap">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->co_mooe }}</td>
                    <td class="text-center nowrap">{{ $item->date_acquired->format('m/d/Y') }}</td>
                    <td>{{ $item->remarks ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span style="background-color: {{ $item->status === 'NEW' ? '#28a745' : '#ffc107' }}; color: {{ $item->status === 'NEW' ? 'white' : 'black' }}; padding: 1px 3px; border-radius: 2px; font-size: 7px; display: inline-block; min-width: 20px;">
                            {{ $item->status === 'NEW' ? 'NEW' : 'REPL' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="pdf-signature-section" style="margin-top: 20px; page-break-inside: avoid;">
        <table style="width: 100%; border: none; margin-top: 30px;">
            <tr>
                <td style="width: 35%; text-align: left;">
                    <span class="pdf-signature-label">Prepared by:</span>
                    <span class="pdf-signature-name">HERO JOHN E. LAPORGA</span><br>
                    <span class="pdf-signature-title">Senior IT Support Specialist</span>
                </td>
                <td style="width: 30%;"></td>
                <td style="width: 35%; text-align: right;">
                    <span class="pdf-signature-label">Reviewed by:</span>
                    <span class="pdf-signature-name">MAY FLORENCE A. PABELONIO</span><br>
                    <span class="pdf-signature-title">ICT Focal Person</span>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center; padding-top: 20px;">
                    <span class="pdf-signature-name">CECILIA L. OCHAVO-SAYCON</span><br>
                    <span class="pdf-signature-title">Regional Director</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer">
        <p>Page 1 of 1 | Total Records: {{ $items->count() }} | Generated by Inventory Management System ni Idith</p>
    </div>
</body>
</html>