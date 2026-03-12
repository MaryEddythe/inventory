<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>IPM Report</title>
    <style>
        @charset "UTF-8";
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
     <table class="pdf-header" style="width: 100%; border: none; margin-bottom: 8px;">
        <tr>
            <td style="width: 50%; text-align: left; vertical-align: middle; padding: 0 5px;">
                <img src="data:image/jpeg;base64,{{ $mgbLogo }}" alt="MGB Logo" style="height: 45px;">
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle; padding: 0 5px;">
                <h2 style="margin: 2px 0; font-size: 16px;">Mines and Geosciences Bureau</h2>
                <h3 style="margin: 2px 0; font-size: 13px;">Regional Office VI</h3>
                <h1 style="margin: 2px 0; font-size: 14px;">IPM REPORT SUMMARY</h1>
                <p style="margin: 2px 0; font-size: 10px;">Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}</p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle; padding: 0 5px;">
                <img src="data:image/jpeg;base64,{{ $bpLogo }}" alt="BP Logo" style="height: 45px;">
            </td>
        </tr>
    </table>
    <hr style="margin: 4px 0; border: none; border-top: 1px solid #000;">

    <!-- Detailed IPM -->
    <div class="pdf-mt-3" style="margin: 8px 0;">
        <table class="pdf-table pdf-table-striped" style="font-size: 9px;">
            <thead>
                <tr>
                    <th colspan="15" class="pdf-bg-dark" style="padding: 4px; background-color: #333; color: white;">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <th class="pdf-col-5 pdf-text-center" style="width: 2%; padding: 3px; border: 1px solid #ccc;">No</th>
                    <th class="pdf-col-8" style="width: 5%; padding: 3px; border: 1px solid #ccc;">Div.</th>
                    <th class="pdf-col-10" style="width: 7%; padding: 3px; border: 1px solid #ccc;">User</th>
                    <th class="pdf-col-6" style="width: 8%; padding: 3px; border: 1px solid #ccc;">Type</th>
                    <th class="pdf-col-12" style="width: 10%; padding: 3px; border: 1px solid #ccc;">Desc</th>
                    <th class="pdf-col-6" style="width: 7%; padding: 3px; border: 1px solid #ccc; text-align: center;">Condition</th>
                    <th class="pdf-col-5" style="width: 5%; padding: 3px; border: 1px solid #ccc; text-align: center;">Boot</th>
                    <th class="pdf-col-5" style="width: 5%; padding: 3px; border: 1px solid #ccc; text-align: center;">HW</th>
                    <th class="pdf-col-5" style="width: 5%; padding: 3px; border: 1px solid #ccc; text-align: center;">Perf</th>
                    <th class="pdf-col-5" style="width: 5%; padding: 3px; border: 1px solid #ccc; text-align: center;">Cables</th>
                    <th class="pdf-col-5" style="width: 5%; padding: 3px; border: 1px solid #ccc; text-align: center;">Per</th>
                    <th class="pdf-col-8" style="width: 10%; padding: 3px; border: 1px solid #ccc;">Rec.</th>
                    <th class="pdf-col-6" style="width: 6%; padding: 3px; border: 1px solid #ccc; text-align: center;">Date</th>
                    <th class="pdf-col-6" style="width: 5%; padding: 3px; border: 1px solid #ccc; text-align: center;">Start</th>
                    <th class="pdf-col-6" style="width: 5%; padding: 3px; border: 1px solid #ccc; text-align: center;">End</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowNumber = 1;
                    $filteredItems = $items->filter(function($item) {
                        return stripos($item->classification, 'monitor') === false;
                    });
                @endphp
                @foreach($filteredItems as $item)
                    <tr style="border-bottom: 1px solid #ccc;">
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $rowNumber++ }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->division }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{!! $item->enduser !!}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->classification }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->description }}</td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">
                            <span class="{{ $item->condition === 'Functional' ? 'pdf-status-new' : 'pdf-status-replace' }}">
                                {{ $item->condition === 'Functional' ? 'OK' : 'BAD' }}
                            </span>
                        </td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;"><span class="{{ $item->system_boot_up ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;"><span class="{{ $item->hardware ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;"><span class="{{ $item->performance ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;"><span class="{{ $item->cables_connections ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;"><span class="{{ $item->peripherals ? 'pdf-checkmark' : 'pdf-cross' }}"></span></td>
                        <td style="padding: 3px; border: 1px solid #ccc; font-size: 8px;">{{ $item->recommendation ?? 'N/A' }}</td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $item->date_conducted ? $item->date_conducted->format('m/d/Y') : 'N/A' }}</td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $item->time_started ? \Carbon\Carbon::parse($item->time_started)->format('h:iA') : 'N/A' }}</td>
                        <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $item->time_ended ? \Carbon\Carbon::parse($item->time_ended)->format('h:iA') : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
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
                    <span class="pdf-signature-title" style="font-size: 10px;">ICT Focal Person</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer pdf-mt-2" style="margin-top: 8px; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
        Total Records: {{ $filteredItems->count() }} | Generated by Inventory Management System - MGB
    </div>
</body>
</html>
