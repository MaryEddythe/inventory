<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>:root { --total-records: "{{ $items->count() }}"; } {{ $css }}</style>
</head>
<body>
    <table class="pdf-header" style="width: 100%; border: none;">
        <tr>
            <td style="width: 50%; text-align: left; vertical-align: middle;">
                <img src="data:image/jpeg;base64,{{ $mgbLogo }}" alt="MGB Logo" style="height: 100px;">
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle;">
                <h2>Mines and Geosciences Bureau</h2>
                <h3>Regional Office VI</h3>
                <h1>INVENTORY REPORT SUMMARY</h1>
                <p>Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}</p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle;">
                <img src="data:image/jpeg;base64,{{ $bpLogo }}" alt="BP Logo" style="height: 100px;">
            </td>
        </tr>
    </table>
    <hr>

    <!-- Detailed Inventory -->
    <div class="pdf-mt-3">
        <table class="pdf-table pdf-table-striped">
            <thead>
                <tr>
                    <th colspan="12" class="pdf-bg-dark">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr>
                    <th class="pdf-col-2 pdf-text-center" style="width: 0.5%;">No</th>
                    <th class="pdf-col-10">Department</th>
                    <th class="pdf-col-10">End User</th>
                    <th class="pdf-col-9" style="width: 10%;">Classification</th>
                    <th class="pdf-col-15">Description</th>
                    <th class="pdf-col-8">Serial No</th>
                    <th class="pdf-col-8">Property No</th>
                    <th class="pdf-col-8 pdf-text-right">Unit Price</th>
                    <th class="pdf-col-5">CO/MOOE</th>
                    <th class="pdf-col-6 pdf-text-center">Date Acquired</th>
                    <th class="pdf-col-12">Remarks</th>
                    <th class="pdf-col-5 pdf-text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Group PDF items by division
                    $groupedItems = $items->groupBy('division');
                    $rowNumber = 1;
                @endphp

                @foreach($groupedItems as $division => $divisionItems)
                    <tr>
                        <td colspan="12" style="background:#efefef;"><strong>Division: {{ $division ?? 'N/A' }}</strong></td>
                    </tr>

                    @foreach($divisionItems as $item)
                        @php
                            $yearsSinceAcquisition = $item->date_acquired ? \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now()) : 10;
                            $pdfBadgeClass = $yearsSinceAcquisition <= 5 ? 'pdf-status-new' : 'pdf-status-replace';
                        @endphp
                        <tr>
                            <td class="pdf-text-center">{{ $rowNumber++ }}</td>
                            <td>{{ $item->department_name ?? $item->division }}</td>
                            <td>{!! $item->enduser ?? 'N/A' !!}</td>
                            <td>{{ $item->classification }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->serial_number ?? 'N/A' }}</td>
                            <td>{{ $item->property_number }}</td>
                            <td class="pdf-text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->co_mooe }}</td>
                            <td class="pdf-text-center pdf-nowrap">{{ $item->date_acquired ? $item->date_acquired->format('m/d/Y') : 'N/A' }}</td>
                            <td>{{ $item->remarks ?? 'N/A' }}</td>
                            <td class="pdf-text-center">
                                <span class="{{ $pdfBadgeClass }}">
                                    {{ $yearsSinceAcquisition <= 5 ? 'NEW' : 'REPL' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Executive Summary -->
    <div class="pdf-summary-section pdf-mt-3" style="page-break-before: always;">
        <table class="pdf-summary-table pdf-table-striped">
            <thead>
                <tr>
                    <th colspan="4" class="pdf-bg-dark pdf-summary-header">EXECUTIVE SUMMARY BY DEPARTMENT</th>
                </tr>
                <tr class="pdf-bg-primary">
                    <th class="pdf-col-40 pdf-summary-th">Department</th>
                    <th class="pdf-col-20 pdf-text-center pdf-summary-th pdf-new-col">New Items</th>
                    <th class="pdf-col-20 pdf-text-center pdf-summary-th pdf-replace-col">For Replacement</th>
                    <th class="pdf-col-20 pdf-text-center pdf-summary-th pdf-total-col">Total Items</th>
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
                            $yearsSinceAcquisition = $item->date_acquired ? \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now()) : 10;
                            return $yearsSinceAcquisition <= 5;
                        })->count();
                        $replacementCount = $group->filter(function ($item) {
                            $yearsSinceAcquisition = $item->date_acquired ? \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now()) : 10;
                            return $yearsSinceAcquisition > 5;
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
                    <tr class="pdf-summary-row">
                        <td class="pdf-summary-td pdf-dept-name"><strong>{{ $summary['name'] }}</strong></td>
                        <td class="pdf-text-center pdf-summary-td pdf-new-count">{{ $summary['new'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-replace-count">{{ $summary['replacement'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-total-count">{{ $summary['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="pdf-bg-overall-total pdf-font-bold pdf-summary-footer pdf-overall-total">
                    <td class="pdf-summary-td"><strong>OVERALL TOTAL</strong></td>
                    <td class="pdf-text-center pdf-summary-td pdf-new-total">{{ $totalNew }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-replace-total">{{ $totalReplacement }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-grand-total">{{ $grandTotalItems }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Item Type Summary -->
    <div class="pdf-summary-section pdf-mt-3" style="page-break-after: always;">
        <table class="pdf-summary-table pdf-table-striped">
            <thead>
                <tr>
                    <th colspan="8" class="pdf-bg-dark pdf-summary-header">ITEM TYPE SUMMARY BY DEPARTMENT</th>
                </tr>
                <tr class="pdf-bg-primary">
                    <th class="pdf-col-20 pdf-summary-th">Department</th>
                    <th class="pdf-col-12 pdf-text-center pdf-summary-th pdf-laptop-col">Laptops</th>
                    <th class="pdf-col-12 pdf-text-center pdf-summary-th pdf-printer-col">Printers</th>
                    <th class="pdf-col-12 pdf-text-center pdf-summary-th pdf-desktop-col">Desktops</th>
                    <th class="pdf-col-12 pdf-text-center pdf-summary-th pdf-scanner-col">Scanners</th>
                    <th class="pdf-col-12 pdf-text-center pdf-summary-th pdf-photocopier-col">Photocopiers</th>
                    <th class="pdf-col-12 pdf-text-center pdf-summary-th pdf-monitor-col">Monitors</th>
                    <th class="pdf-col-12 pdf-text-center pdf-summary-th pdf-total-col">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $itemTypeSummaries = collect();
                    foreach ($allDepts as $dept) {
                        $deptItems = $items->filter(function ($item) use ($dept) {
                            return $item->division == $dept->department;
                        });

                        $laptops = $deptItems->filter(function ($item) {
                            return stripos($item->classification, 'laptop') !== false || stripos($item->description, 'laptop') !== false;
                        })->count();

                        $printers = $deptItems->filter(function ($item) {
                            return stripos($item->classification, 'printer') !== false || stripos($item->description, 'printer') !== false;
                        })->count();

                        $desktops = $deptItems->filter(function ($item) {
                            return stripos($item->classification, 'desktop') !== false || stripos($item->description, 'desktop') !== false;
                        })->count();

        $scanners = $deptItems->filter(function ($item) {
            return stripos($item->classification, 'scanner') !== false || stripos($item->description, 'scanner') !== false;
        })->count();

                        $photocopiers = $deptItems->filter(function ($item) {
                            return stripos($item->classification, 'photocopier') !== false || stripos($item->description, 'photocopier') !== false;
                        })->count();

                        $monitors = $deptItems->filter(function ($item) {
                            return stripos($item->classification, 'monitor') !== false || stripos($item->description, 'monitor') !== false;
                        })->count();
                        $total = $deptItems->count();

                        $itemTypeSummaries->push([
                            'name' => $dept->department,
                            'laptops' => $laptops,
                            'printers' => $printers,
                            'desktops' => $desktops,
                            'scanners' => $scanners,
                            'photocopiers' => $photocopiers,
                            'monitors' => $monitors,
                            'total' => $total
                        ]);
                    }

                    $totalLaptops = $itemTypeSummaries->sum('laptops');
                    $totalPrinters = $itemTypeSummaries->sum('printers');
                    $totalDesktops = $itemTypeSummaries->sum('desktops');
                    $totalScanners = $itemTypeSummaries->sum('scanners');
                    $totalPhotocopiers = $itemTypeSummaries->sum('photocopiers');
                    $totalMonitors = $itemTypeSummaries->sum('monitors');
                    $grandTotalTypes = $itemTypeSummaries->sum('total');
                @endphp
                @foreach($itemTypeSummaries as $summary)
                    <tr class="pdf-summary-row">
                        <td class="pdf-summary-td pdf-dept-name"><strong>{{ $summary['name'] }}</strong></td>
                        <td class="pdf-text-center pdf-summary-td pdf-laptop-count">{{ $summary['laptops'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-printer-count">{{ $summary['printers'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-desktop-count">{{ $summary['desktops'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-scanner-count">{{ $summary['scanners'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-photocopier-count">{{ $summary['photocopiers'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-monitor-count">{{ $summary['monitors'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-total-count">{{ $summary['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="pdf-bg-overall-total pdf-font-bold pdf-summary-footer pdf-overall-total">
                    <td class="pdf-summary-td"><strong>OVERALL TOTAL</strong></td>
                    <td class="pdf-text-center pdf-summary-td pdf-laptop-total">{{ $totalLaptops }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-printer-total">{{ $totalPrinters }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-desktop-total">{{ $totalDesktops }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-scanner-total">{{ $totalScanners }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-photocopier-total">{{ $totalPhotocopiers }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-monitor-total">{{ $totalMonitors }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-grand-total">{{ $grandTotalTypes }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="pdf-signature-section pdf-mt-3" style="page-break-inside: avoid;">
        <table style="width: 100%; border: none;">
            <tr>
                <!-- Left cell -->
                <td class="pdf-col-30 pdf-text-left">
                    <span class="pdf-signature-label">Prepared by:</span><br><br>
                    <span class="pdf-signature-name">MARY EDDYTHE M. SORNITO</span><br>
                    <span class="pdf-signature-title">Information Systems Analyst II</span>
                </td>

                <!-- Center cell for Regional Director -->
                <td class="pdf-col-40 pdf-text-center" style="padding-top: 80px;">
                    <span class="pdf-signature-label">Approved by:</span><br><br>
                    <span class="pdf-signature-name">CECILIA L. OCHAVO-SAYCON</span><br>
                    <span class="pdf-signature-title">Regional Director</span>
                </td>

                <!-- Right cell -->
                <td class="pdf-col-30 pdf-text-right" style="padding-right: 80px;">
                    <span class="pdf-signature-label" style="margin-right: 120px;">Reviewed by:</span><br><br>
                    <span class="pdf-signature-name">MAY FLORENCE A. PABELONIO</span><br>
                    <span class="pdf-signature-title" style="margin-right: 50px;">ICT Focal Person</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer pdf-mt-2">
        Total Records: {{ $items->count() }} | Generated by Inventory Management System - MGB</p>
    </div>
</body>
</html>