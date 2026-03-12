<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        :root { --total-records: "{{ $items->count() }}"; }
        
        @page {
            size: 8.5in 14in;
            margin: 0.5in 0.5in 0.5in 0.5in;
        }
        
        @media print {
            @page {
                size: 8.5in 14in;
                margin: 0.5in 0.5in 0.5in 0.5in;
            }
            
            * {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0.5in 0.5in !important;
            }
            
            html {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
        }
        
        body {
            margin: 0;
            padding: 0.5in 0.5in;
            font-size: 11px;
            font-family: Arial, sans-serif;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        {{ $css }}
    </style>
</head>
<body>
    <table class="pdf-header" style="width: 100%; border: none; margin-bottom: 8px;">
        <tr>
            <td style="width: 50%; text-align: left; vertical-align: middle; padding: 0 5px;">
                <img src="data:image/jpeg;base64,{{ $mgbLogo }}" alt="MGB Logo" style="height: 50px;">
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle; padding: 0 5px;">
                <h2 style="margin: 2px 0; font-size: 16px;">Mines and Geosciences Bureau</h2>
                <h3 style="margin: 2px 0; font-size: 13px;">Regional Office VI</h3>
                <h1 style="margin: 2px 0; font-size: 14px;">INVENTORY REPORT SUMMARY</h1>
                <p style="margin: 2px 0; font-size: 10px;">Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}</p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle; padding: 0 5px;">
                <img src="data:image/jpeg;base64,{{ $bpLogo }}" alt="BP Logo" style="height: 50px;">
            </td>
        </tr>
    </table>
    <hr style="margin: 4px 0; border: none; border-top: 1px solid #000;">

    <!-- Detailed Inventory -->
    <div class="pdf-mt-3" style="margin: 8px 0;">
        <table class="pdf-table pdf-table-striped" style="font-size: 10px;">
            <thead>
                <tr>
                    <th colspan="13" class="pdf-bg-dark" style="padding: 4px; background-color: #333; color: white;">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <th class="pdf-col-2 pdf-text-center" style="width: 3%; padding: 4px; border: 1px solid #ccc;">No</th>
                    <th class="pdf-col-10" style="width: 7%; padding: 4px; border: 1px solid #ccc;">Department</th>
                    <th class="pdf-col-10" style="width: 7%; padding: 4px; border: 1px solid #ccc;">End User</th>
                    <th class="pdf-col-9" style="width: 8%; padding: 4px; border: 1px solid #ccc;">Classification</th>
                    <th class="pdf-col-15" style="width: 13%; padding: 4px; border: 1px solid #ccc;">Description</th>
                    <th class="pdf-col-8" style="width: 8%; padding: 4px; border: 1px solid #ccc;">Serial No</th>
                    <th class="pdf-col-8" style="width: 8%; padding: 4px; border: 1px solid #ccc;">Property No</th>
                    <th class="pdf-col-8 pdf-text-right" style="width: 9%; padding: 4px; border: 1px solid #ccc;">Unit Price</th>
                    <th class="pdf-col-5" style="width: 6%; padding: 4px; border: 1px solid #ccc;">CO/MOOE</th>
                    <th class="pdf-col-6 pdf-text-center" style="width: 7%; padding: 4px; border: 1px solid #ccc;">Date Acquired</th>
                    <th class="pdf-col-12" style="width: 9%; padding: 4px; border: 1px solid #ccc;">Remarks</th>
                    <th class="pdf-col-5 pdf-text-center" style="width: 5%; padding: 4px; border: 1px solid #ccc;">Status</th>
                    <th class="pdf-col-8 pdf-text-center" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Serviceability</th>
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
                        <td colspan="13" style="background:#efefef; padding: 4px; font-weight: bold; border: 1px solid #ccc;">Division: {{ $division ?? 'N/A' }}</td>
                    </tr>

                    @foreach($divisionItems as $item)
                        @php
                            $yearsSinceAcquisition = $item->date_acquired ? \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now()) : 10;
                            $pdfBadgeClass = $yearsSinceAcquisition <= 5 ? 'pdf-status-new' : 'pdf-status-replace';
                        @endphp
                        <tr style="border-bottom: 1px solid #ccc;">
                            <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $rowNumber++ }}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->department_name ?? $item->division }}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{!! $item->enduser ?? 'N/A' !!}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->classification }}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->description }}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->serial_number ?? 'N/A' }}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->property_number }}</td>
                            <td class="pdf-text-right" style="padding: 3px; border: 1px solid #ccc;">{{ number_format($item->unit_price, 2) }}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->co_mooe }}</td>
                            <td class="pdf-text-center pdf-nowrap" style="padding: 3px; border: 1px solid #ccc; white-space: nowrap;">{{ $item->date_acquired ? $item->date_acquired->format('m/d/Y') : 'N/A' }}</td>
                            <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->remarks ?? 'N/A' }}</td>
                            <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">
                                <span class="{{ $pdfBadgeClass }}">
                                    {{ $yearsSinceAcquisition <= 5 ? '<= 5' : '> 5' }}
                                </span>
                            </td>
                            <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $item->serviceability ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Executive Summary -->
    <div class="pdf-summary-section pdf-mt-3" style="page-break-before: always; margin: 8px 0;">
        <table class="pdf-summary-table pdf-table-striped" style="font-size: 11px;">
            <thead>
                <tr>
                    <th colspan="4" class="pdf-bg-dark pdf-summary-header" style="padding: 6px; background-color: #333; color: white;">EXECUTIVE SUMMARY BY DEPARTMENT</th>
                </tr>
                <tr class="pdf-bg-primary" style="background-color: #007bff; color: white;">
                    <th class="pdf-col-40 pdf-summary-th" style="width: 40%; padding: 6px; border: 1px solid #ccc;">Department</th>
                    <th class="pdf-col-20 pdf-text-center pdf-summary-th pdf-new-col" style="width: 20%; padding: 6px; border: 1px solid #ccc;">New Items</th>
                    <th class="pdf-col-20 pdf-text-center pdf-summary-th pdf-replace-col" style="width: 20%; padding: 6px; border: 1px solid #ccc;">For Replacement</th>
                    <th class="pdf-col-20 pdf-text-center pdf-summary-th pdf-total-col" style="width: 20%; padding: 6px; border: 1px solid #ccc;">Total Items</th>
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
                    <tr class="pdf-summary-row" style="border-bottom: 1px solid #ccc;">
                        <td class="pdf-summary-td pdf-dept-name" style="padding: 5px; border: 1px solid #ccc;"><strong>{{ $summary['name'] }}</strong></td>
                        <td class="pdf-text-center pdf-summary-td pdf-new-count" style="padding: 5px; border: 1px solid #ccc;">{{ $summary['new'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-replace-count" style="padding: 5px; border: 1px solid #ccc;">{{ $summary['replacement'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-total-count" style="padding: 5px; border: 1px solid #ccc;">{{ $summary['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="pdf-bg-overall-total pdf-font-bold pdf-summary-footer pdf-overall-total" style="background-color: #e9ecef; font-weight: bold;">
                    <td class="pdf-summary-td" style="padding: 5px; border: 1px solid #ccc;"><strong>OVERALL TOTAL</strong></td>
                    <td class="pdf-text-center pdf-summary-td pdf-new-total" style="padding: 5px; border: 1px solid #ccc;">{{ $totalNew }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-replace-total" style="padding: 5px; border: 1px solid #ccc;">{{ $totalReplacement }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-grand-total" style="padding: 5px; border: 1px solid #ccc;">{{ $grandTotalItems }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Item Type Summary -->
    <div class="pdf-summary-section pdf-mt-3" style="page-break-after: always; margin: 8px 0;">
        <table class="pdf-summary-table pdf-table-striped" style="font-size: 10px;">
            <thead>
                <tr>
                    <th colspan="9" class="pdf-bg-dark pdf-summary-header" style="padding: 6px; background-color: #333; color: white;">ITEM TYPE SUMMARY BY DEPARTMENT</th>
                </tr>
                <tr class="pdf-bg-primary" style="background-color: #007bff; color: white;">
                    <th class="pdf-col-15 pdf-summary-th" style="width: 15%; padding: 4px; border: 1px solid #ccc;">Department</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-laptop-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Laptops</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-printer-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Printers</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-desktop-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Desktops</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-scanner-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Scanners</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-photocopier-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Photocopiers</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-monitor-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Monitors</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-other-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Others</th>
                    <th class="pdf-col-10 pdf-text-center pdf-summary-th pdf-total-col" style="width: 10%; padding: 4px; border: 1px solid #ccc;">Total</th>
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

                        $others = $deptItems->filter(function ($item) {
                            $classification = strtolower($item->classification);
                            $description = strtolower($item->description);
                            return !(
                                stripos($classification, 'laptop') !== false || stripos($description, 'laptop') !== false ||
                                stripos($classification, 'printer') !== false || stripos($description, 'printer') !== false ||
                                stripos($classification, 'desktop') !== false || stripos($description, 'desktop') !== false ||
                                stripos($classification, 'scanner') !== false || stripos($description, 'scanner') !== false ||
                                stripos($classification, 'photocopier') !== false || stripos($description, 'photocopier') !== false ||
                                stripos($classification, 'monitor') !== false || stripos($description, 'monitor') !== false
                            );
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
                            'others' => $others,
                            'total' => $total
                        ]);
                    }

                    $totalLaptops = $itemTypeSummaries->sum('laptops');
                    $totalPrinters = $itemTypeSummaries->sum('printers');
                    $totalDesktops = $itemTypeSummaries->sum('desktops');
                    $totalScanners = $itemTypeSummaries->sum('scanners');
                    $totalPhotocopiers = $itemTypeSummaries->sum('photocopiers');
                    $totalMonitors = $itemTypeSummaries->sum('monitors');
                    $totalOthers = $itemTypeSummaries->sum('others');
                    $grandTotalTypes = $itemTypeSummaries->sum('total');
                @endphp
                @foreach($itemTypeSummaries as $summary)
                    <tr class="pdf-summary-row" style="border-bottom: 1px solid #ccc;">
                        <td class="pdf-summary-td pdf-dept-name" style="padding: 4px; border: 1px solid #ccc;"><strong>{{ $summary['name'] }}</strong></td>
                        <td class="pdf-text-center pdf-summary-td pdf-laptop-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['laptops'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-printer-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['printers'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-desktop-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['desktops'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-scanner-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['scanners'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-photocopier-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['photocopiers'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-monitor-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['monitors'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-other-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['others'] }}</td>
                        <td class="pdf-text-center pdf-summary-td pdf-total-count" style="padding: 4px; border: 1px solid #ccc;">{{ $summary['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="pdf-bg-overall-total pdf-font-bold pdf-summary-footer pdf-overall-total" style="background-color: #e9ecef; font-weight: bold;">
                    <td class="pdf-summary-td" style="padding: 4px; border: 1px solid #ccc;"><strong>OVERALL TOTAL</strong></td>
                    <td class="pdf-text-center pdf-summary-td pdf-laptop-total" style="padding: 4px; border: 1px solid #ccc;">{{ $totalLaptops }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-printer-total" style="padding: 4px; border: 1px solid #ccc;">{{ $totalPrinters }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-desktop-total" style="padding: 4px; border: 1px solid #ccc;">{{ $totalDesktops }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-scanner-total" style="padding: 4px; border: 1px solid #ccc;">{{ $totalScanners }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-photocopier-total" style="padding: 4px; border: 1px solid #ccc;">{{ $totalPhotocopiers }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-monitor-total" style="padding: 4px; border: 1px solid #ccc;">{{ $totalMonitors }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-other-total" style="padding: 4px; border: 1px solid #ccc;">{{ $totalOthers }}</td>
                    <td class="pdf-text-center pdf-summary-td pdf-grand-total" style="padding: 4px; border: 1px solid #ccc;">{{ $grandTotalTypes }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="pdf-signature-section pdf-mt-3" style="page-break-inside: avoid; margin: 8px 0;">
        <table style="width: 100%; border: none; margin-top: 20px;">
            <tr>
                <!-- Left cell -->
                <td class="pdf-col-30 pdf-text-left" style="width: 30%; text-align: left; padding-right: 10px;">
                    <span class="pdf-signature-label" style="font-weight: bold; font-size: 11px;">Prepared by:</span><br>
                    <span class="pdf-signature-name" style="font-weight: bold; margin-top: 30px; display: block;">HERO JOHN E. LAPORGA</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">Senior IT Support Specialist</span><br><br><br><br><br><br>
                    <span class="pdf-signature-name" style="font-weight: bold; display: block;">MARY EDDYTHE M. SORNITO</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">Computer Maintenance Technologist I</span>
                </td>

                <!-- Center cell for Regional Director -->
                <td class="pdf-col-40 pdf-text-center" style="width: 40%; text-align: center;">
                    <span class="pdf-signature-label" style="font-weight: bold; font-size: 11px;">Reviewed by:</span><br>
                    <span class="pdf-signature-name" style="font-weight: bold; margin-top: 30px; display: block;">MAY FLORENCE A. PABELONIO</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">ICT Focal Person</span>
                </td>

                <!-- Right cell -->
                <td class="pdf-col-30 pdf-text-right" style="width: 30%; text-align: right; padding-left: 10px;">
                    <span class="pdf-signature-label" style="font-weight: bold; font-size: 11px;">Approved by:</span><br>
                    <span class="pdf-signature-name" style="font-weight: bold; margin-top: 30px; display: block;">CECILIA L. OCHAVO-SAYCON</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">Regional Director</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer pdf-mt-2" style="margin-top: 8px; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
        Total Records: {{ $items->count() }} | Generated by Inventory Management System - MGB
    </div>
</body>
</html>
