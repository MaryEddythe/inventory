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

    {{-- ===== HEADER ===== --}}
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

    {{-- ===== DETAILED PPE INVENTORY TABLE ===== --}}
    <div class="pdf-mt-3" style="margin: 8px 0;">
        <table class="pdf-table pdf-table-striped" style="font-size: 10px;">
            <thead>
                {{-- Dark banner — PPE style --}}
                <tr>
                    <th colspan="12" class="pdf-bg-dark" style="padding: 4px; background-color: #333; color: white;">
                        DETAILED INVENTORY LISTING (PPE)
                    </th>
                </tr>

                {{-- Column headers — RPCSP structure, PPE colours --}}
                <tr style="background-color: #f0f0f0;">
                    <th class="rpcsp-article-col"      rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">ARTICLE</th>
                    <th class="rpcsp-description-col"  rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">DESCRIPTION</th>
                    <th class="rpcsp-property-col"     rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">PROPERTY<br>NUMBER</th>
                    <th class="rpcsp-uom-col"          rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">UNIT OF<br>MEASURE</th>
                    <th colspan="2"                                style="padding: 4px; border: 1px solid #ccc; text-align: center;">BALANCE PER</th>
                    <th class="rpcsp-onhand-col"       rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">ON HAND<br>PER COUNT<br>(Quantity)</th>
                    <th class="rpcsp-total-value-col"  rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">TOTAL<br>VALUE</th>
                    <th class="rpcsp-date-col"         rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">DATE<br>ACQUIRED</th>
                    <th colspan="2"                                style="padding: 4px; border: 1px solid #ccc; text-align: center;">SHORTAGE/<br>OVERAGE</th>
                    <th class="rpcsp-remarks-col"      rowspan="2" style="padding: 4px; border: 1px solid #ccc; text-align: center;">REMARKS</th>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <th class="rpcsp-unit-value-col"      style="padding: 4px; border: 1px solid #ccc; text-align: center;">UNIT VALUE</th>
                    <th class="rpcsp-card-col"            style="padding: 4px; border: 1px solid #ccc; text-align: center;">CARD<br>(Quantity)</th>
                    <th class="rpcsp-shortage-qty-col"    style="padding: 4px; border: 1px solid #ccc; text-align: center;">Quantity</th>
                    <th class="rpcsp-shortage-value-col"  style="padding: 4px; border: 1px solid #ccc; text-align: center;">Value</th>
                </tr>
            </thead>

            <tbody>
                @php
                    // Filter: PPE criteria — unit_price >= 50,000 AND co_mooe = 'CO'
                    $ppeItems = $items->filter(function ($item) {
                        return $item->unit_price !== null
                            && (float) $item->unit_price >= 50000
                            && $item->co_mooe === 'CO';
                    });

                    // Group by division (same as original)
                    $groupedItems = $ppeItems->groupBy('division');

                    $grandTotalUnitValue  = 0;
                    $grandTotalValue      = 0;
                @endphp

                @if ($ppeItems->count() > 0)

                    @foreach ($groupedItems as $division => $divisionItems)

                        {{-- Division banner row --}}
                        <tr>
                            <td colspan="12" style="background: #efefef; padding: 4px; font-weight: bold; border: 1px solid #ccc;">
                                Division: {{ $division ?? 'N/A' }}
                            </td>
                        </tr>

                        @foreach ($divisionItems as $item)
                            @php
                                // ── UOM logic (mirrors RPCSP) ──────────────────────────
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

                                // ── Article label (mirrors RPCSP DESKTOP→COMPUTER fix) ─
                                $article = strtoupper($item->classification ?? 'N/A');
                                if ($article === 'DESKTOP') {
                                    $article = 'COMPUTER';
                                }

                                // ── Remarks: enduser / division ────────────────────────
                                $remarks = '';
                                if ($item->enduser && $item->division) {
                                    $remarks = $item->enduser . ' / ' . $item->division;
                                } elseif ($item->enduser) {
                                    $remarks = $item->enduser;
                                } elseif ($item->division) {
                                    $remarks = $item->division;
                                }

                                // ── Totals ─────────────────────────────────────────────
                                $totalValue           = (float) $item->unit_price;   // qty = 1
                                $grandTotalUnitValue += (float) $item->unit_price;
                                $grandTotalValue     += $totalValue;

                                // ── Age badge (kept from original PPE) ─────────────────
                                $yearsSinceAcquisition = $item->date_acquired
                                    ? \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now())
                                    : 10;
                                $pdfBadgeClass = $yearsSinceAcquisition <= 5 ? 'pdf-status-new' : 'pdf-status-replace';
                            @endphp

                            <tr style="border-bottom: 1px solid #ccc;">
                                {{-- ARTICLE --}}
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">
                                    {{ $article }}
                                </td>

                                {{-- DESCRIPTION --}}
                                <td style="padding: 3px; border: 1px solid #ccc;">
                                    {{ ucwords($item->description) }}
                                </td>

                                {{-- PROPERTY NUMBER --}}
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">
                                    {{ $item->property_number ?? 'N/A' }}
                                </td>

                                {{-- UNIT OF MEASURE --}}
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">
                                    {{ $uom }}
                                </td>

                                {{-- BALANCE PER — UNIT VALUE --}}
                                <td class="pdf-text-right" style="padding: 3px; border: 1px solid #ccc;">
                                    {{ number_format($item->unit_price, 2) }}
                                </td>

                                {{-- BALANCE PER — CARD (Quantity) --}}
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">
                                    1
                                </td>

                                {{-- ON HAND PER COUNT (Quantity) --}}
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;">
                                    1
                                </td>

                                {{-- TOTAL VALUE --}}
                                <td class="pdf-text-right" style="padding: 3px; border: 1px solid #ccc;">
                                    {{ number_format($totalValue, 2) }}
                                </td>

                                {{-- DATE ACQUIRED --}}
                                <td class="pdf-text-center pdf-nowrap" style="padding: 3px; border: 1px solid #ccc; white-space: nowrap;">
                                    {{ $item->date_acquired ? $item->date_acquired->format('m/d/Y') : 'N/A' }}
                                </td>

                                {{-- SHORTAGE/OVERAGE — Quantity (blank, same as RPCSP) --}}
                                <td class="pdf-text-center" style="padding: 3px; border: 1px solid #ccc;"></td>

                                {{-- SHORTAGE/OVERAGE — Value (blank) --}}
                                <td class="pdf-text-right" style="padding: 3px; border: 1px solid #ccc;"></td>

                                {{-- REMARKS: enduser / division  +  age badge (PPE original) --}}
                                <td style="padding: 3px; border: 1px solid #ccc;">
                                    {{ $remarks }}<br>
                                    <span class="{{ $pdfBadgeClass }}" style="font-size: 9px;">
                                        Yrs: {{ $yearsSinceAcquisition <= 5 ? '≤ 5' : '> 5' }}
                                    </span>
                                </td>
                            </tr>

                        @endforeach
                    @endforeach

                    {{-- ── Grand Total row (PPE style) ── --}}
                    @php
                        $grandTotalUnitValue = $ppeItems->sum('unit_price');
                        $grandTotalValue     = $ppeItems->sum('unit_price'); // qty=1 for all
                    @endphp
                    <tr class="pdf-bg-gray pdf-font-bold">
                        <td colspan="4" class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            GRAND TOTAL UNIT VALUE:
                        </td>
                        <td class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            {{ number_format($grandTotalUnitValue, 2) }}
                        </td>
                        <td colspan="2" class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            GRAND TOTAL:
                        </td>
                        <td class="pdf-text-right" style="padding: 4px; border: 1px solid #ccc; font-weight: bold;">
                            {{ number_format($grandTotalValue, 2) }}
                        </td>
                        <td colspan="4" style="border: 1px solid #ccc;"></td>
                    </tr>

                @else

                    <tr>
                        <td colspan="12" style="padding: 12px; text-align: center; border: 1px solid #ccc;">
                            No qualifying PPE items found.<br>
                            Criteria: Unit Price ≥ ₱50,000.00 AND CO/MOOE = 'CO'.
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

    {{-- ===== FOOTER ===== --}}
    <div class="pdf-footer pdf-mt-2" style="margin-top: 8px; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
        Total Records: {{ $ppeItems->count() }} | Generated by Inventory Management System - MGB
    </div>

</body>
</html>