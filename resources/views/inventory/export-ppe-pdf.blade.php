<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report - PPE</title>
    <style>
        :root { --total-records: "{{ $items->count() }}"; }
        
        @page {
            size: 14in 8.5in landscape;
            margin: 0.25in 0.25in 0.25in 0.25in;
        }

        @media print {
            @page {
                size: 14in 8.5in landscape;
                margin: 0.25in 0.25in 0.25in 0.25in;
            }
            
            * {
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                margin: 0 !important;
                padding: 0.25in 0.25in !important;
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
            padding: 0.25in 0.25in;
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
                <p style="margin: 2px 0; font-size: 10px;">
                    Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} |
                    Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }}
                    to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}
                </p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle; padding: 0 5px;">
                <img src="data:image/jpeg;base64,{{ $bpLogo }}" alt="BP Logo" style="height: 50px;">
            </td>
        </tr>
    </table>

    <hr style="margin: 4px 0; border: none; border-top: 1px solid #000;">

    <!-- Detailed PPE Inventory -->
    <div class="pdf-mt-3" style="margin: 8px 0;">
        <table class="pdf-table pdf-table-striped" style="font-size: 10px;">
            <thead>
                <tr>
                    <th colspan="13" class="pdf-bg-dark" style="padding: 4px; background-color: #333; color: white;">
                        DETAILED INVENTORY LISTING (PPE)
                    </th>
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
                    $ppeItems = $items->filter(function($item) {
                        // Only include ACTIVE items (x 'active' only)
                        $isActive = isset($item->x) && $item->x !== null && strtolower(trim((string)$item->x)) !== 'inactive';
                        if (!$isActive) {
                            return false;
                        }

                        return $item->unit_price !== null
                            && (float)$item->unit_price >= 50000
                            && $item->co_mooe === 'CO';
                    });

                    $groupedItems = $ppeItems->groupBy('division');
                    $rowNumber = 1;
                @endphp

                @foreach($groupedItems as $division => $divisionItems)
                    <tr>
                        <td colspan="13" style="background:#efefef; padding: 4px; font-weight: bold; border: 1px solid #ccc;">
                            Division: {{ $division ?? 'N/A' }}
                        </td>
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

                @if($ppeItems->count() === 0)
                    <tr>
                        <td colspan="13" style="padding: 12px; text-align:center; border: 1px solid #ccc;">
                            No qualifying PPE items found.
                            <br>Criteria: Unit Price ≥ 50,000 AND CO/MOOE = 'CO'.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="pdf-signature-section pdf-mt-3" style="page-break-inside: avoid; margin: 8px 0;">
        <table style="width: 100%; border: none; margin-top: 20px;">
            <tr>
                <td class="pdf-col-30 pdf-text-left" style="width: 30%; text-align: left; padding-right: 10px;">
                    <span class="pdf-signature-label" style="font-weight: bold; font-size: 11px;">Prepared by:</span><br>
                    <span class="pdf-signature-name" style="font-weight: bold; margin-top: 30px; display: block;">HERO JOHN E. LAPORGA</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">Senior IT Support Specialist</span><br><br><br><br><br><br>
                    <span class="pdf-signature-name" style="font-weight: bold; display: block;">MARY EDDYTHE M. SORNITO</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">Computer Maintenance Technologist I</span>
                </td>

                <td class="pdf-col-40 pdf-text-center" style="width: 40%; text-align: center;">
                    <span class="pdf-signature-label" style="font-weight: bold; font-size: 11px;">Reviewed by:</span><br>
                    <span class="pdf-signature-name" style="font-weight: bold; margin-top: 30px; display: block;">MAY FLORENCE A. PABELONIO</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">ICT Focal Person</span>
                </td>

                <td class="pdf-col-30 pdf-text-right" style="width: 30%; text-align: right; padding-left: 10px;">
                    <span class="pdf-signature-label" style="font-weight: bold; font-size: 11px;">Approved by:</span><br>
                    <span class="pdf-signature-name" style="font-weight: bold; margin-top: 30px; display: block;">CECILIA L. OCHAVO-SAYCON</span><br>
                    <span class="pdf-signature-title" style="font-size: 10px;">Regional Director</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer pdf-mt-2" style="margin-top: 8px; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
        Total Records: {{ $ppeItems->count() }} | Generated by Inventory Management System - MGB
    </div>
</body>
</html>

