<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        @charset "UTF-8";
        :root { --total-records: "{{ $items->count() }}"; }
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

    <!-- Detailed IPM -->
    <div class="pdf-mt-3">
        <table class="pdf-table pdf-table-striped">
            <thead>
                <tr>
                    <th colspan="15" class="pdf-bg-dark">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr>
                    <th class="pdf-col-5 pdf-text-center" style="width: 0.5%;">No</th>
                    <th class="pdf-col-8">Div.</th>
                    <th class="pdf-col-10">User</th>
                    <th class="pdf-col-6" style="width: 10%;">Type</th>
                    <th class="pdf-col-12">Desc</th>
                    <th class="pdf-col-6 pdf-text-center">Condition</th>
                    <th class="pdf-col-5 pdf-text-center">Boot Up</th>
                    <th class="pdf-col-5 pdf-text-center">Hardware</th>
                    <th class="pdf-col-5 pdf-text-center">Performance</th>
                    <th class="pdf-col-5 pdf-text-center">Cables/Conn</th>
                    <th class="pdf-col-5 pdf-text-center">Periph</th>
                    <th class="pdf-col-8">Recommendations</th>
                    <th class="pdf-col-6 pdf-text-center">Date</th>
                    <th class="pdf-col-6 pdf-text-center">Start</th>
                    <th class="pdf-col-6 pdf-text-center">End</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowNumber = 1;
                @endphp
                @foreach($items as $item)
                    <tr>
                        <td class="pdf-text-center">{{ $rowNumber++ }}</td>
                        <td>{{ $item->division }}</td>
                        <td>{!! $item->enduser !!}</td>
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
                        <td>{{ $item->recommendation ?? 'N/A' }}</td>
                        <td class="pdf-text-center">{{ $item->date_conducted ? $item->date_conducted->format('m/d/Y') : 'N/A' }}</td>
                        <td class="pdf-text-center">{{ $item->time_started ? \Carbon\Carbon::parse($item->time_started)->format('h:iA') : 'N/A' }}</td>
                        <td class="pdf-text-center">{{ $item->time_ended ? \Carbon\Carbon::parse($item->time_ended)->format('h:iA') : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
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
                <td class="pdf-col-30 pdf-text-right" style="padding-right: 40px;">
                    <span class="pdf-signature-label" style="margin-right: 100px;">Reviewed by:</span><br><br>
                    <span class="pdf-signature-name">MAY FLORENCE A. PABELONIO</span><br>
                    <span class="pdf-signature-title" style="margin-right: 50px;">ICT Focal Person</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer pdf-mt-2">
        Total Records: {{ $items->count() }} | Generated by Inventory Management System - MGB
    </div>
</body>
</html>