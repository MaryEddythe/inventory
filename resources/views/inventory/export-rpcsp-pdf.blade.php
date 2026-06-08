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
        <table class="pdf-table pdf-table-striped" style="font-size: 10px;">
            <thead>
                {{-- Dark banner — matches PPE style --}}
                <tr>
                    <th colspan="12" class="pdf-bg-dark" style="padding: 4px; background-color: #2c3e50; color: white;">
                        DETAILED LISTING — SEMI-EXPENDABLE PROPERTY
                    </th>
                </tr>

                {{-- Column headers --}}
                <tr style="background-color: #f0f0f0;">
                    <th class="rpcsp-article-col"     rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">ARTICLE</th>
                    <th class="rpcsp-description-col" rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">DESCRIPTION</th>
                    <th class="rpcsp-property-col"    rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">PROPERTY<br>NUMBER</th>
                    <th class="rpcsp-uom-col"         rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">UNIT OF<br>MEASURE</th>
                    <th colspan="2"                               style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">BALANCE PER</th>
                    <th class="rpcsp-onhand-col"      rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">ON HAND<br>PER COUNT<br>(Quantity)</th>
                    <th class="rpcsp-total-value-col" rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">TOTAL<br>VALUE</th>
                    <th class="rpcsp-date-col"        rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">DATE<br>ACQUIRED</th>
                    <th colspan="2"                               style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">SHORTAGE/<br>OVERAGE</th>
                    <th class="rpcsp-remarks-col"     rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">REMARKS</th>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <th class="rpcsp-unit-value-col"     style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">UNIT VALUE</th>
                    <th class="rpcsp-card-col"           style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">CARD<br>(Quantity)</th>
                    <th class="rpcsp-shortage-qty-col"   style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">Quantity</th>
                    <th class="rpcsp-shortage-value-col" style="padding: 4px; border: 1px solid #ccc; text-align: center; background-color: #f0f0f0;">Value</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $totalItems = $items->count();
                    $filteredItems = $items->filter(function ($item) {
                        $isActive = isset($item->x) && strtolower((string) $item->x) === 'active';
                        return $isActive
                            && $item->unit_price <= 49999
                            && !is_null($item->unit_price);
                    });

                    $classificationItems = $filteredItems->groupBy('classification');

                    $totalGrandValue = 0;
                    $totalUnitValue  = 0;
                    $itemCount       = 0;
                @endphp

                @if ($filteredItems->count() > 0)

                    @foreach ($classificationItems as $classification => $groupedItems)

                        {{-- Classification banner — matches PPE division banner --}}
                        <tr>
                            <td colspan="12" style="background: #efefef; padding: 4px; font-weight: bold; border: 1px solid #ccc;">
                                Classification: {{ strtoupper($classification) === 'DESKTOP' ? 'COMPUTER' : strtoupper($classification ?? 'N/A') }}
                            </td>
                        </tr>

                        @foreach ($groupedItems as $item)
                            @php
                                $uom  = 'unit';
                                $desc = strtolower($item->description ?? '');

                                if (str_contains($desc, 'desktop') || str_contains($desc, 'set')) {
                                    $uom = 'set';
                                } elseif (str_contains($desc, 'monitor')  ||
                                          str_contains($desc, 'printer')  ||
                                          str_contains($desc, 'scanner')  ||
                                          str_contains($desc, 'laptop')   ||
                                          str_contains($desc, 'tablet')   ||
                                          str_contains($desc, 'phone')) {
                                    $uom = 'pc';
                                } elseif (str_contains($desc, 'pair')) {
                                    $uom = 'pair';
                                }

                                $remarks = '';
                                if ($item->enduser && $item->division) {
                                    $remarks = $item->enduser . ' / ' . $item->division;
                                } elseif ($item->enduser) {
                                    $remarks = $item->enduser;
                                } elseif ($item->division) {
                                    $remarks = $item->division;
                                }

                                $article = strtoupper($classification);
                                if ($article === 'DESKTOP') {
                                    $article = 'COMPUTER';
                                }

                                $totalValue      = (float) $item->unit_price;
                                $totalGrandValue += $totalValue;
                                $totalUnitValue  += (float) $item->unit_price;
                                $itemCount++;
                            @endphp

                            <tr style="border-bottom: 1px solid #ccc;">
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $article }}</td>
                                <td style="padding: 3px; border: 1px solid #ccc;">{{ ucwords($item->description) }}</td>
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $item->property_number ?? 'N/A' }}</td>
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">{{ $uom }}</td>
                                <td class="pdf-text-right"  style="padding: 3px; border: 1px solid #ccc;">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">1</td>
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">1</td>
                                <td class="pdf-text-right"  style="padding: 3px; border: 1px solid #ccc;">{{ number_format($totalValue, 2) }}</td>
                                <td class="pdf-text-center pdf-nowrap" style="padding: 3px; border: 1px solid #ccc; white-space: nowrap;">
                                    {{ $item->date_acquired ? $item->date_acquired->format('m/d/Y') : 'N/A' }}
                                </td>
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;"></td>
                                <td class="pdf-text-right"  style="padding: 3px; border: 1px solid #ccc;"></td>
                                <td style="padding: 3px; border: 1px solid #ccc;">{{ $remarks }}</td>
                            </tr>

                        @endforeach
                    @endforeach

                    {{-- Grand total row — matches PPE pdf-bg-gray style --}}
                    @php
                        $totalUnitValue  = $filteredItems->sum('unit_price');
                        $totalGrandValue = $filteredItems->sum('unit_price');
                    @endphp
                    <tr class="pdf-bg-gray pdf-font-bold">
                        <td colspan="4" class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            SUBTOTAL UNIT VALUE:
                        </td>
                        <td class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            {{ number_format($totalUnitValue, 2) }}
                        </td>
                        <td colspan="2" class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            GRAND TOTAL:
                        </td>
                        <td class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            {{ number_format($totalGrandValue, 2) }}
                        </td>
                        <td colspan="4" style="border: 1px solid #ccc;"></td>
                    </tr>

                @else

                    <tr>
                        <td colspan="12" style="padding: 12px; text-align: center; border: 1px solid #ccc;">
                            No qualifying semi-expendable property items found.<br>
                            Criteria: Unit Price &le; &#8369;49,999.00<br>
                            Total items in database: {{ $totalItems }}
                            @if ($totalItems > 0)
                                <br>Sample items:
                                @foreach ($items->take(3) as $sample)
                                    <br>- {{ $sample->description }} (&#8369;{{ number_format($sample->unit_price, 2) }}, {{ $sample->co_mooe }})
                                @endforeach
                            @endif
                        </td>
                    </tr>

                @endif
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

        {{-- Certified Correct — 4 signatories --}}
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
        Total Records: {{ $filteredItems->count() }} | Generated on: {{ now('Asia/Manila')->format('F d, Y h:i A') }} | Inventory Management System - MGB
    </div>

</body>
</html>