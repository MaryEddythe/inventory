<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} Report</title>
    <style>
        :root { --total-records: "{{ $items->count() }}"; }

        @page {
            size: 14in 8.5in landscape;
            margin: 0.35in 0.25in 0.5in 0.25in;
        }

        @media print {
            @page {
                size: 14in 8.5in landscape;
                margin: 0.35in 0.25in 0.5in 0.25in;
            }

            * {
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                margin: 0 !important;
                padding: 0.25in !important;
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
            padding: 0.25in;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header Styles */
        .pdf-header {
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            page-break-inside: avoid;
        }

        .pdf-header-left, .pdf-header-center, .pdf-header-right {
            vertical-align: middle;
            padding: 0 5px;
        }

        .pdf-header-left {
            width: 12%;
            text-align: left;
        }

        .pdf-header-center {
            width: 76%;
            text-align: center;
        }

        .pdf-header-right {
            width: 12%;
            text-align: right;
        }

        .pdf-header img {
            max-height: 45px;
            max-width: 80px;
        }

        .pdf-header h2 {
            margin: 2px 0;
            font-size: 11pt;
            font-weight: bold;
        }

        .pdf-header h3 {
            margin: 1px 0;
            font-size: 9pt;
            font-weight: bold;
        }

        .pdf-header h1 {
            margin: 2px 0;
            font-size: 12pt;
            font-weight: bold;
        }

        .pdf-header p {
            margin: 2px 0;
            font-size: 8pt;
        }

        /* Table Styles */
        .pdf-category-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            page-break-inside: auto;
            table-layout: auto;
        }

        .pdf-category-table th,
        .pdf-category-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
            font-size: 8pt;
            vertical-align: top;
            word-wrap: break-word;
        }

        .pdf-category-table th {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #000;
        }

        .pdf-category-table tbody tr {
            page-break-inside: avoid;
        }

        .pdf-category-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .pdf-text-center {
            text-align: center;
        }

        .pdf-text-right {
            text-align: right;
        }

        .pdf-nowrap {
            white-space: nowrap;
        }

        /* Column Widths */
        .pdf-no-col { width: 5%; }
        .pdf-article-col { width: 10%; }
        .pdf-description-col { width: 20%; }
        .pdf-property-col { width: 12%; }
        .pdf-unit-value-col { width: 12%; }
        .pdf-date-col { width: 10%; }
        .pdf-remarks-col { width: 21%; }

        .pdf-bg-dark {
            background-color: #2c3e50 !important;
            color: white !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            font-size: 8pt !important;
            padding: 4px !important;
        }

        .pdf-bg-primary {
            background-color: #34495e !important;
            color: white !important;
            font-weight: bold !important;
        }

        /* Total Row Styles */
        .pdf-category-total-row {
            background-color: #95a5a6 !important;
            color: #2c3e50 !important;
            font-weight: bold !important;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
        }

        .pdf-category-total-row td {
            padding: 4px !important;
            font-size: 8pt !important;
        }

        .pdf-no-data {
            text-align: center !important;
            padding: 15px !important;
            color: #999;
            font-style: italic;
        }

        /* Signature Section */
        .pdf-signature-section {
            margin-top: 15px;
            page-break-inside: avoid;
            width: 100%;
        }

        .pdf-signature-section table {
            border: none;
            width: 100%;
        }

        .pdf-signature-section td {
            border: none;
            padding: 0 10px;
            font-size: 8pt;
            vertical-align: top;
        }

        .pdf-signature-left {
            width: 30%;
            text-align: left;
        }

        .pdf-signature-center {
            width: 40%;
            text-align: center;
        }

        .pdf-signature-right {
            width: 30%;
            text-align: right;
        }

        .pdf-signature-label {
            font-weight: bold;
            font-size: 8pt;
            display: block;
            margin-bottom: 2px;
        }

        .pdf-signature-name {
            font-weight: bold;
            font-size: 8pt;
            display: block;
            margin-top: 30px;
        }

        .pdf-signature-title {
            font-size: 7pt;
            display: block;
            color: #555;
        }

        /* Footer */
        .pdf-footer {
            margin-top: 20px;
            padding-top: 5px;
            border-top: 1px solid #000;
            font-size: 8pt;
            text-align: center;
            page-break-inside: avoid;
        }

        .pdf-footer-page {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        {{ $css }}
    </style>
</head>
<body>
    <!-- Header -->
    <table class="pdf-header">
        <tr>
            <td class="pdf-header-left">
                <img src="data:image/jpeg;base64,{{ $mgbLogo }}" alt="MGB Logo">
            </td>
            <td class="pdf-header-center">
                <h2>Mines and Geosciences Bureau</h2>
                <h3>Regional Office VI</h3>
                <h1>{{ strtoupper($title) }} INVENTORY REPORT</h1>
                <p>Generated on: {{ now('Asia/Manila')->format('F d, Y \a\t h:i A') }}</p>
            </td>
            <td class="pdf-header-right">
                <img src="data:image/jpeg;base64,{{ $bpLogo }}" alt="BP Logo">
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="pdf-category-table">
        <thead>
            <tr>
                <th colspan="7" class="pdf-bg-dark">{{ strtoupper($title) }} LISTING</th>
            </tr>
            <tr class="pdf-bg-primary">
                <th class="pdf-no-col pdf-text-center">No</th>
                <th class="pdf-article-col">Article</th>
                <th class="pdf-description-col">Description</th>
                <th class="pdf-property-col">Property Number</th>
                <th class="pdf-unit-value-col pdf-text-right">Unit Value</th>
                <th class="pdf-date-col pdf-text-center">Date Acquired</th>
                <th class="pdf-remarks-col">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td class="pdf-text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->article ?? 'N/A' }}</td>
                    <td>{{ $item->description ?? 'N/A' }}</td>
                    <td>{{ $item->property_number ?? 'N/A' }}</td>
                    <td class="pdf-text-right">{{ number_format($item->unit_value ?? 0, 2) }}</td>
                    <td class="pdf-text-center pdf-nowrap">{{ $item->date_acquired ? $item->date_acquired->format('m/d/Y') : 'N/A' }}</td>
                    <td>{{ $item->remarks ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="pdf-no-data">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="pdf-category-total-row">
                <td colspan="4">TOTAL UNIT VALUE & RECORD COUNT</td>
                <td class="pdf-text-right">₱ {{ number_format($items->sum('unit_value') ?? 0, 2) }}</td>
                <td colspan="2" class="pdf-text-right">{{ $items->count() }} record(s)</td>
            </tr>
        </tfoot>
    </table>

    <!-- Signature Section -->
    <div class="pdf-signature-section">
        <table>
            <tr>
                <td class="pdf-signature-left">
                    <span class="pdf-signature-label">Prepared by:</span>
                    <span class="pdf-signature-name">HERO JOHN E. LAPORGA</span>
                    <span class="pdf-signature-title">Senior IT Support Specialist</span>
                    <br><br><br><br><br>
                    <span class="pdf-signature-name">MARY EDDYTHE M. SORNITO</span>
                    <span class="pdf-signature-title">Computer Maintenance Technologist I</span>
                </td>

                <td class="pdf-signature-center">
                    <span class="pdf-signature-label">Reviewed by:</span>
                    <span class="pdf-signature-name">MAY FLORENCE A. PABELONIO</span>
                    <span class="pdf-signature-title">ICT Focal Person</span>
                </td>

                <td class="pdf-signature-right">
                    <span class="pdf-signature-label">Approved by:</span>
                    <span class="pdf-signature-name">CECILIA L. OCHAVO-SAYCON</span>
                    <span class="pdf-signature-title">Regional Director</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="pdf-footer">
        <div class="pdf-footer-page">
            <span>Total Records: {{ $items->count() }} | Total Value: ₱ {{ number_format($items->sum('unit_value') ?? 0, 2) }}</span>
            <span>Inventory Management System - MGB RO VI</span>
            <span>Page <span class="pageNumber"></span> of <span class="totalPages"></span></span>
        </div>
    </div>
</body>
</html>
