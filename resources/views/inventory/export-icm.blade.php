<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICM Export</title>
    <link rel="stylesheet" href="{{ asset('pdf-styles.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 8.5in 13in landscape;
            margin: 0.25in;
        }

        @media print {
            @page {
                size: 8.5in 13in landscape;
                margin: 0.25in;
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

        body {
            font-family: Merriweather, "DejaVu Sans", Arial, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
            line-height: 1.15;
        }

        .icm-header {
            width: 100%;
            border: none;
            margin-bottom: 8px;
        }

        .icm-header-cell {
            vertical-align: middle;
            padding: 0 5px;
        }

        .icm-header-logo-left {
            width: 50%;
            text-align: left;
        }

        .icm-header-logo-right {
            width: 50%;
            text-align: right;
        }

        .icm-header-title-cell {
            width: 60%;
            text-align: center;
            vertical-align: middle;
            padding: 0 5px;
        }

        .icm-header h2 {
            margin: 2px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .icm-header h3 {
            margin: 2px 0;
            font-size: 13px;
            font-weight: bold;
        }

        .icm-header h1 {
            margin: 2px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .icm-header p {
            margin: 2px 0;
            font-size: 10px;
        }

        .icm-header img {
            height: 50px;
        }

        .icm-header-divider {
            margin: 4px 0;
            border: none;
            border-top: 1px solid #000;
        }

        .icm-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: auto;
            margin-top: 8px;
        }

        .icm-table,
        .icm-table * {
            font-family: Merriweather, "DejaVu Sans", Arial, sans-serif !important;
            line-height: 1.15 !important;
        }

        .icm-table th,
        .icm-table td {
            border: 1px solid #000 !important;
            padding: 1px 2px !important;
            text-align: left !important;
            vertical-align: top !important;
            word-wrap: break-word !important;
            font-family: Merriweather, "DejaVu Sans", Arial, sans-serif !important;
            font-size: 5pt !important;
            line-height: 1.1 !important;
        }

        .icm-table th {
            background-color: #d9d9d9 !important;
            color: #000 !important;
            font-weight: bold !important;
            font-size: 5pt !important;
            text-align: center !important;
            border-bottom: 2px solid #000 !important;
            font-family: Merriweather, "DejaVu Sans", Arial, sans-serif !important;
        }

        .icm-table thead th {
            font-size: 5pt !important;
        }

        .icm-table tbody td {
            font-size: 5pt !important;
            font-family: Merriweather, "DejaVu Sans", Arial, sans-serif !important;
            line-height: 1.1 !important;
        }

        .icm-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .icm-table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .icm-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .icm-signature-section {
            page-break-inside: avoid;
            margin-top: 6mm;
        }

        .icm-signature-section table {
            width: 100%;
            border: none;
            margin-top: 4mm;
        }

        .icm-signature-section td {
            border: none;
            padding: 2mm 2mm;
            vertical-align: top;
            font-size: 8pt;
        }

        .icm-signature-label {
            font-weight: bold;
            margin-bottom: 2mm;
            display: block;
            font-size: 8pt;
        }

        .icm-signature-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }

        .pdf-text-right { text-align: right; }
        .pdf-text-center { text-align: center; }
    </style>
</head>
<body>
    <table class="icm-header" style="width: 100%; border: none; margin-bottom: 8px;">
        <tr>
            <td class="icm-header-cell icm-header-logo-left">
                <img src="data:image/jpeg;base64,{{ $mgbLogo }}" alt="MGB Logo">
            </td>
            <td class="icm-header-title-cell">
                <h2>Mines and Geosciences Bureau</h2>
                <h3>Regional Office VI</h3>
                <h1>ICM REPORT SUMMARY</h1>
                <p>Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : 'All' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : 'Present' }}</p>
            </td>
            <td class="icm-header-cell icm-header-logo-right">
                <img src="data:image/jpeg;base64,{{ $bpLogo }}" alt="BP Logo">
            </td>
        </tr>
    </table>
    <hr class="icm-header-divider">

    @if(isset($tab) || isset($title))
        <table style="width: 100%; margin-bottom: 3mm; border-collapse: collapse;">
            <tr>
                <td style="border: none !important; padding: 1px 0; font-size: 8pt;">
                    <strong>Tab:</strong> {{ $tab ?? 'icm' }}
                </td>
                <td class="pdf-text-right" style="border: none !important; padding: 1px 0; font-size: 8pt;">
                    <strong>Title:</strong> {{ $title ?? 'ICM' }}
                </td>
            </tr>
        </table>
    @endif

    <table class="icm-table">
        <thead>
            <tr>
                <th style="width: 7%">ICM No</th>
                <th style="width: 10%">Division</th>
                <th style="width: 10%">Personnel</th>
                <th style="width: 18%">Problem Description</th>
                <th style="width: 7%">Type</th>
                <th style="width: 7%">Priority</th>
                <th style="width: 12%">Hardware/Software</th>
                <th style="width: 10%">Brand/Model</th>
                <th style="width: 8%">Serial No</th>
                <th style="width: 8%">Property No</th>
                <th style="width: 7%">Open Date</th>
                <th style="width: 7%">Close Date</th>
                <th style="width: 12%">Findings</th>
                <th style="width: 12%">Actions Taken</th>
                <th style="width: 14%">Recommendations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items ?? [] as $item)
                <tr>
                    <td>{{ $item->icm_no ?? 'N/A' }}</td>
                    <td>{{ $item->division ?? 'N/A' }}</td>
                    <td>{{ preg_replace('/\s*\(\d+\)\s*/', '', $item->requesting_personnel ?? 'N/A') }}</td>
                    <td>{{ $item->problem_description ?? 'N/A' }}</td>
                    <td>{{ $item->icm_type ?? 'N/A' }}</td>
                    <td>{{ $item->priority ?? 'N/A' }}</td>
                    <td>{{ $item->hardware_software ?? 'N/A' }}</td>
                    <td>{{ $item->brand_model ?? 'N/A' }}</td>
                    <td>{{ $item->serial_number ?? 'N/A' }}</td>
                    <td>{{ $item->property_number ?? 'N/A' }}</td>
                    <td>{{ isset($item->open_date) && $item->open_date ? $item->open_date->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ isset($item->close_date) && $item->close_date ? $item->close_date->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $item->icm_findings ?? 'N/A' }}</td>
                    <td>{{ $item->actions_taken ?? 'N/A' }}</td>
                    <td>{{ $item->icm_recommendations ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="pdf-text-center">No ICM items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="icm-signature-section" style="page-break-inside: avoid; margin: 8px 0;">
        <table style="width: 100%; border: none; margin-top: 20px;">
            <tr>
                <!-- Left cell -->
                <td style="width: 30%; text-align: left; padding-right: 10px;">
                    <span class="icm-signature-label">Prepared by:</span><br>
                    <span class="icm-signature-name" style="margin-top: 30px; display: block;">HERO JOHN E. LAPORGA</span><br>
                    <span style="font-size: 10px;">Senior IT Support Specialist</span><br><br><br><br><br><br>
                    <span class="icm-signature-name" style="display: block;">MARY EDDYTHE M. SORNITO</span><br>
                    <span style="font-size: 10px;">Computer Maintenance Technologist I</span>
                </td>

                <!-- Center cell for ict focal -->
                <td style="width: 40%; text-align: center;">
                    <span class="icm-signature-label">Reviewed by:</span><br>
                    <span class="icm-signature-name" style="margin-top: 30px; display: block;">MAY FLORENCE A. PABELONIO</span><br>
                    <span style="font-size: 10px;">ICT Focal Person</span>
                </td>

                <!-- Right cell -->
                <td style="width: 30%; text-align: right; padding-left: 10px;">
                    <span class="icm-signature-label">Approved by:</span><br>
                    <span class="icm-signature-name" style="margin-top: 30px; display: block;">CECILIA L. OCHAVO-SAYCON</span><br>
                    <span style="font-size: 10px;">Regional Director</span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
