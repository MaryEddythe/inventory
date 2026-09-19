<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report on the Physical Count of Semi-Expendable Property</title>
    <style>
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

        .property-report-table,
        .property-report-table tbody {
            page-break-inside: auto;
        }

        .property-report-table tbody tr {
            page-break-inside: auto !important;
            page-break-after: auto !important;
        }

        .pdf-signature-section {
            page-break-before: always !important;
            page-break-inside: avoid !important;
        }

        .property-report-table {
            table-layout: fixed;
        }

        .property-report-table th,
        .property-report-table td {
            padding: 1px 2px !important;
            font-size: 5.5pt !important;
            line-height: 1.05;
        }

        .property-report-table th {
            font-size: 6pt !important;
        }
    </style>
</head>
<body>

    {{-- ===== HEADER (matches PPE layout) ===== --}}
    <table style="width: 100%; border: none; margin-bottom: 8px;">
        <tr>
            <td style="width: 50%; text-align: left; vertical-align: middle; padding: 0 5px;">
                <img src="data:image/jpeg;base64,{{ $mgbLogo }}" alt="MGB Logo" style="height: 50px;">
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle; padding: 0 5px;">
                <h2 style="margin: 2px 0; font-size: 16px;">Mines and Geosciences Bureau</h2>
                <h3 style="margin: 2px 0; font-size: 13px;">Regional Office VI</h3>
                <h1 style="margin: 2px 0; font-size: 14px;">REPORT ON THE PHYSICAL COUNT OF SEMI-EXPENDABLE PROPERTY</h1>
                <p style="margin: 2px 0; font-size: 10px;">OTHER PROPERTY PLANT AND EQUIPMENT</p>
                <p style="margin: 2px 0; font-size: 10px;">(Type of Semi-Expendable Property)</p>
                <p style="margin: 2px 0; font-size: 10px;"><strong>As at December 31, 2025</strong></p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle; padding: 0 5px;">
                <img src="data:image/jpeg;base64,{{ $bpLogo }}" alt="BP Logo" style="height: 50px;">
            </td>
        </tr>
    </table>

    <hr style="margin: 4px 0; border: none; border-top: 1px solid #000;">

    {{-- ===== ACCOUNTABLE OFFICER INFO ===== --}}
    <div style="margin: 6px 0 4px 0; font-size: 9px;">
        <div style="margin: 3px 0;">
            <span style="font-weight: bold;">Fund Cluster:</span>
            <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </div>
        <div style="margin: 3px 0;">
            <span style="font-weight: bold;">For which</span>
            <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 200px; text-align: center;">MAY FLORENCE A. PABELENONIO</span>
            <span>,</span>
            <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 150px; text-align: center;">Supply Officer II/GSS</span>
            <span>,</span>
            <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 200px; text-align: center;">DENR-Mines and Geosciences Bureau R-6</span>
            <span>is accountable, having assumed such accountability on</span>
            <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 60px; text-align: center;">&nbsp;</span>
        </div>
        <div style="margin-top: 3px; font-size: 8px; text-align: left; white-space: nowrap;">
            <span style="display:inline-block; width: 52px;">&nbsp;</span>
            <span style="display:inline-block; width: 200px; text-align:center;">(Name of Accountable Officer)</span>
            <span style="display:inline-block; width: 6px;">&nbsp;</span>
            <span style="display:inline-block; width: 150px; text-align:center;">(Office Designation)</span>
            <span style="display:inline-block; width: 6px;">&nbsp;</span>
            <span style="display:inline-block; width: 200px; text-align:center;">(Agency/Office)</span>
            <span style="display:inline-block; width: 238px;">&nbsp;</span>
            <span style="display:inline-block; width: 60px; text-align:center;">(Date of Assumption)</span>
        </div>
    </div>

    {{-- ===== RPCSP TABLE ===== --}}
    <div style="margin: 8px 0;">
        <table class="pdf-table pdf-table-striped property-report-table" style="font-size: 5.5pt; table-layout: fixed;">
            <colgroup>
                <col style="width: 3%">
                <col style="width: 3%">
                <col style="width: 6%">
                <col style="width: 13%">
                <col style="width: 7%">
                <col style="width: 5%">
                <col style="width: 4%">
                <col style="width: 4%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 8%">
                <col style="width: 3%">
                <col style="width: 4%">
                <col style="width: 3%">
                <col style="width: 4%">
                <col style="width: 6%">
                <col style="width: 4%">
                <col style="width: 3%">
                <col style="width: 4%">
                <col style="width: 5%">
            </colgroup>
            <thead>
                <tr>
                    <th colspan="20" class="pdf-bg-dark" style="padding: 4px; background-color: #333; color: white;">DETAILED INVENTORY LISTING</th>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <th style="padding: 3px; border: 1px solid #ccc;">OFFICE</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">ARTICLE</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">EXPENSE<br>CLASSIFICATION</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">MAIN<br>SPECIFICATIONS</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">SERIAL NUMBER</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">UNIT<br>CLASSIFICATION</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">BRAND</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">MODEL</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">ACQUISITION COST</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">ACQUISITION DATE</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">PROPERTY NUMBER</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">PAR</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">DIVISION</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">SECTION</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">USER CATEGORY</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">ACTUAL USER</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">DIVISION</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">SECTION</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">USER CATEGORY</th>
                    <th style="padding: 3px; border: 1px solid #ccc;">REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $propertyItems = $items->filter(function ($item) {
                        return $item->unit_price !== null && (float) $item->unit_price <= 49999;
                    });
                @endphp
                @forelse ($propertyItems as $item)
                    @php
                        $expenseClassification = strtoupper((string) $item->co_mooe) === 'CO'
                            ? 'Capital Outlay - CO'
                            : (strtoupper((string) $item->co_mooe) === 'MOOE'
                                ? 'Maintenance and Other Operating Expenses - MOOE'
                                : (string) $item->co_mooe);
                    @endphp
                    <tr style="border-bottom: 1px solid #ccc;">
                        <td style="padding: 3px; border: 1px solid #ccc;">MGB-R6</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->classification ?? '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $expenseClassification }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->description ?? '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->serial_number ?? '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">Hardware</td>
                        <td style="padding: 3px; border: 1px solid #ccc;"></td>
                        <td style="padding: 3px; border: 1px solid #ccc;"></td>
                        <td class="pdf-text-right" style="padding: 3px; border: 1px solid #ccc;">{{ $item->unit_price === null ? '' : number_format((float) $item->unit_price, 2) }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->date_acquired ? $item->date_acquired->format('d-M-y') : '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->property_number ?? '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;"></td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->division ?? '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;"></td>
                        <td style="padding: 3px; border: 1px solid #ccc;"></td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->enduser ?? '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->division ?? '' }}</td>
                        <td style="padding: 3px; border: 1px solid #ccc;"></td>
                        <td style="padding: 3px; border: 1px solid #ccc;"></td>
                        <td style="padding: 3px; border: 1px solid #ccc;">{{ $item->remarks ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="20" style="padding: 12px; text-align: center; border: 1px solid #ccc;">No qualifying semi-expendable property items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== SIGNATURE SECTION ===== --}}
    <div class="pdf-signature-section pdf-mt-3" style="margin-top: 15px; page-break-inside: avoid;">

        {{-- Section labels row --}}
        <table style="width: 100%; border: none; margin-bottom: 4px;">
            <tr>
                <td style="width: 45%; border: none; padding: 0;">
                    <span style="font-size: 8px; font-weight: bold;">Certified Correct by:</span>
                </td>
                <td style="width: 30%; border: none; padding: 0;">
                    <span style="font-size: 8px; font-weight: bold;">Approved by:</span>
                </td>
                <td style="width: 25%; border: none; padding: 0;">
                    <span style="font-size: 8px; font-weight: bold;">Witnessed by:</span>
                </td>
            </tr>
        </table>

        {{-- Signature boxes --}}
        <table style="width: 100%; border: none; margin-top: 8px;">
        <tr>

        {{-- Certified Correct Ã¢â‚¬â€ 4 signatories --}}
        <td style="width: 45%; vertical-align: top; border: none; padding-right: 12px;">
            <table style="width: 100%; border: none;">

                <tr>
                    {{-- Signatory 1 --}}
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 6px 0 0;">

                        {{-- VERY SMALL GAP --}}
                        <div style="height: 2px;"></div>

                        <div style="border-bottom: 1px solid #000; margin: 0 10%;"></div>

                        <div style="margin-top: 2px;">
                            <strong style="font-size: 9px;">GLENN L. UMIPIG</strong><br>
                            <span style="font-size: 8px;">Chief, FAD in Concurrent Capacity as</span><br>
                            <span style="font-size: 8px;">Accountant III</span>
                        </div>
                    </td>

                    {{-- Signatory 2 --}}
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 0 0 6px;">
                        <div style="height: 2px;"></div>

                        <div style="border-bottom: 1px solid #000; margin: 0 10%;"></div>

                        <div style="margin-top: 2px;">
                            <strong style="font-size: 9px;">DELILAH P. AGUILAR</strong><br>
                            <span style="font-size: 8px;">Administrative Assistant II</span><br>
                            <span style="font-size: 8px;">Member</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="height: 20px; border: none;"></td>
                </tr>

                <tr>
                    {{-- Signatory 3 --}}
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 6px 0 0;">
                        <div style="height: 2px;"></div>

                        <div style="border-bottom: 1px solid #000; margin: 0 10%;"></div>

                        <div style="margin-top: 2px;">
                            <strong style="font-size: 9px;">PRUDENCIO C. BULAWAN IV</strong><br>
                            <span style="font-size: 8px;">D./Prop. Inspector</span><br>
                            <span style="font-size: 8px;">Member</span>
                        </div>
                    </td>

                    {{-- Signatory 4 --}}
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 0 0 6px;">
                        <div style="height: 2px;"></div>

                        <div style="border-bottom: 1px solid #000; margin: 0 10%;"></div>

                        <div style="margin-top: 2px;">
                            <strong style="font-size: 9px;">MAY FLORENCE A. PABELONIO</strong><br>
                            <span style="font-size: 8px;">Supply Officer II, GSS</span><br>
                            <span style="font-size: 8px;">Member</span>
                        </div>
                    </td>
                </tr>

            </table>
        </td>

        {{-- Approved by --}}
        <td style="width: 30%; text-align: center; vertical-align: top; border: none; padding: 0 12px;">
            <div style="height: 35px;"></div>
            <div style="border-bottom: 1px solid #000; margin: 0 10%;"></div>
            <div style="margin-top: 4px;">
                <strong style="font-size: 9px;">CECILIA L. OCHAVO-SAYCON</strong><br>
                <span style="font-size: 8px;">Regional Director</span>
            </div>
        </td>

        {{-- Witnessed by --}}
        <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0;">
            <div style="height: 35px;"></div>
            <div style="border-bottom: 1px solid #000; margin: 0 10%;"></div>
            <div style="margin-top: 4px;">
                <span style="font-size: 8px;">Signature over Printed Name of COA</span><br>
                <span style="font-size: 8px;">Representative</span>
            </div>
        </td>

    </tr>
    </table>

    {{-- ===== FOOTER (matches PPE style) ===== --}}
    <div class="pdf-footer pdf-mt-2" style="margin-top: 8px; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
        Total Records: {{ $propertyItems->count() }} | Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Inventory Management System - MGB
    </div>

</body>
</html>
