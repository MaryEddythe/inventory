<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</title>
    <link rel="stylesheet" href="{{ public_path('pdf-styles.css') }}">
</head>
<body class="rpcsp-body">

<div class="rpcsp-header-info">
    <h1 class="rpcsp-h1">REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</h1>
    <h2 class="rpcsp-h2">CIP-BUILDING AND OTHER STRUCTURES</h2>
    <h3 class="rpcsp-h3">(Type of Inventory Item)</h3>
    <p><strong>As at December 31, 2024</strong></p>
</div>

<div class="rpcsp-meta-row">
    <span class="rpcsp-meta-label">Fund Cluster:</span>
    <span>&nbsp;</span>
</div>

<div style="margin: 5px 0; font-size: 7px;">
    <div style="margin: 3px 0;">
        <span class="rpcsp-meta-label">For which</span>
        <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 120px;">MAY FLORENCE A. PABELENONIO</span>
        <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 100px;">Supply Officer II/GSS</span>
        <span>,</span>
        <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 150px;">DENR-Mines and Geosciences Bureau R-6</span>
        <span style="margin-left: 10px;">is accountable, having assumed such accountability on</span>
        <span style="border-bottom: 1px solid #000; padding: 0 5px; display: inline-block; min-width: 60px;">&nbsp;</span>
    </div>
    <div style="margin-top: 3px; text-align: center; font-size: 8px;">
        <span>(Name of Accountable Officer)</span>
        <span style="margin-left: 40px;">(Office Designation)</span>
        <span style="margin-left: 40px;">(Agency/Office)</span>
        <span style="margin-left: 40px;">(Date of Assumption)</span>
    </div>
</div>

<table class="rpcsp-table">
    <thead>
        <tr class="rpcsp-header-row">
            <th class="rpcsp-article-col" rowspan="2">ARTICLE</th>
            <th class="rpcsp-description-col" rowspan="2">DESCRIPTION</th>
            <th class="rpcsp-property-col" rowspan="2">PROPERTY NUMBER</th>
            <th class="rpcsp-uom-col" rowspan="2">UNIT OF MEASURE</th>
            <th colspan="2" class="rpcsp-text-center">BALANCE PER</th>
            <th class="rpcsp-onhand-col" rowspan="2">ON HAND<br>PER COUNT<br>(Quantity)</th>
            <th class="rpcsp-total-value-col" rowspan="2">TOTAL<br>VALUE</th>
            <th class="rpcsp-date-col" rowspan="2">DATE<br>ACQUIRED</th>
            <th colspan="2" class="rpcsp-text-center">SHORTAGE/<br>OVERAGE</th>
            <th class="rpcsp-remarks-col" rowspan="2">REMARKS</th>
        </tr>
        <tr class="rpcsp-header-row">
            <th class="rpcsp-unit-value-col">UNIT VALUE</th>
            <th class="rpcsp-card-col">CARD<br>(Quantity)</th>
            <th class="rpcsp-shortage-qty-col">Quantity</th>
            <th class="rpcsp-shortage-value-col">Value</th>
        </tr>
    </thead>
    <tbody>
        @php
            // Debug: Check what data we have
            $totalItems = $items->count();
            $filteredItems = $items->filter(function($item) {
                $isValid = $item->unit_price >= 50000 && $item->co_mooe === 'CO';
                return $isValid;
            });

            // Group by classification
            $classificationItems = $filteredItems->groupBy('classification');

            $totalGrandValue = 0;
            $totalUnitValue = 0;
            $itemCount = 0;
        @endphp

        @if($filteredItems->count() > 0)
            @foreach($classificationItems as $classification => $groupedItems)
                @foreach($groupedItems as $item)
                    @php
                        // Determine Unit of Measure based on description
                        $uom = 'unit';
                        $desc = strtolower($item->description ?? '');

                        if (str_contains($desc, 'desktop') || str_contains($desc, 'set')) {
                            $uom = 'set';
                        } elseif (str_contains($desc, 'monitor') ||
                                  str_contains($desc, 'printer') ||
                                  str_contains($desc, 'scanner') ||
                                  str_contains($desc, 'laptop') ||
                                  str_contains($desc, 'tablet') ||
                                  str_contains($desc, 'phone')) {
                            $uom = 'pc';
                        } elseif (str_contains($desc, 'pair')) {
                            $uom = 'pair';
                        }

                        // Format remarks: enduser / division
                        $remarks = '';
                        if ($item->enduser && $item->division) {
                            $remarks = $item->enduser . ' / ' . $item->division;
                        } elseif ($item->enduser) {
                            $remarks = $item->enduser;
                        } elseif ($item->division) {
                            $remarks = $item->division;
                        }

                        // Calculate total value (unit_price × 1 since quantity is 1)
                        $totalValue = $item->unit_price;
                        $totalGrandValue += $totalValue;
                        $totalUnitValue += $item->unit_price;
                        $itemCount++;

                        // Format classification - convert DESKTOP to COMPUTER
                        $article = $classification;
                        if (strtoupper($classification) === 'DESKTOP') {
                            $article = 'COMPUTER';
                        }
                    @endphp
                    <tr>
                        <td class="rpcsp-article-col pdf-text-center">{{ strtoupper($article) }}</td>
                        <td class="rpcsp-description-col">{{ ucwords($item->description) }}</td>
                        <td class="rpcsp-property-col pdf-text-center">{{ $item->property_number ?? 'N/A' }}</td>
                        <td class="rpcsp-uom-col pdf-text-center">{{ $uom }}</td>
                        <td class="rpcsp-unit-value-col pdf-text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="rpcsp-card-col pdf-text-center">1</td> <!-- Balance per Card (Quantity) -->
                        <td class="rpcsp-onhand-col pdf-text-center">1</td> <!-- On Hand per Count (Quantity) -->
                        <td class="rpcsp-total-value-col pdf-text-right">{{ number_format($totalValue, 2) }}</td>
                        <td class="rpcsp-date-col pdf-text-center">{{ $item->date_acquired ? $item->date_acquired->format('m/d/Y') : 'N/A' }}</td>
                        <td class="rpcsp-shortage-qty-col pdf-text-center"></td> <!-- Shortage/Overage Quantity -->
                        <td class="rpcsp-shortage-value-col pdf-text-right"></td> <!-- Shortage/Overage Value -->
                        <td class="rpcsp-remarks-col">{{ $remarks }}</td>
                    </tr>
                @endforeach
            @endforeach

            <!-- Subtotal and Grand Total on Same Row -->
            <tr class="pdf-bg-gray pdf-font-bold">
                <td colspan="4" class="pdf-text-right">SUBTOTAL:</td>
                <td class="pdf-text-right">{{ number_format($totalUnitValue, 2) }}</td>
                <td colspan="3" class="pdf-text-right">GRAND TOTAL:</td>
                <td class="pdf-text-right">{{ number_format($totalGrandValue, 2) }}</td>
                <td colspan="4"></td>
            </tr>
        @else
            <tr>
                <td colspan="12" class="no-data">
                    No qualifying semi-expendable property items found.<br>
                    Criteria: Unit Price ≥ ₱49,999.00 AND CO/MOOE = 'CO'<br>
                    Total items in database: {{ $totalItems }}<br>
                    @if($totalItems > 0)
                        Sample items:
                        @foreach($items->take(3) as $sample)
                            <br>- {{ $sample->description }} (₱{{ number_format($sample->unit_price, 2) }}, {{ $sample->co_mooe }})
                        @endforeach
                    @endif
                </td>
            </tr>
        @endif
    </tbody>
</table>

<div class="signature-section" style="margin-top: 20px; text-align: center;">
    <table style="width: 100%; border: none; margin-bottom: 20px;">
        <tr>
            <td style="width: 30%; text-align: center; vertical-align: top;">
                <span style="font-size: 8px; font-style: italic;">Certified Correct by</span><br>
                <div style="border-bottom: 1px solid #000; margin: 5px 0; height: 40px;"></div>
                <strong>GLENN L. UMIPIG</strong><br>
                <span style="font-size: 9px;">OIC Chief, FAD in Concurrent Capacity as</span><br>
                <span style="font-size: 9px;">Chief, Finance Section</span>
            </td>
            <td style="width: 30%; text-align: center; vertical-align: top;">
                <span style="font-size: 8px; font-style: italic;">Approved by</span><br>
                <div style="border-bottom: 1px solid #000; margin: 5px 0; height: 40px;"></div>
                <strong>DELILAH P. AGUILAR</strong><br>
                <span style="font-size: 9px;">Administrative Assistant III</span><br>
                <span style="font-size: 9px;">Member</span>
            </td>
            <td style="width: 30%; text-align: center; vertical-align: top;">
                <span style="font-size: 8px; font-style: italic;">Witnessed by</span><br>
                <div style="border-bottom: 1px solid #000; margin: 5px 0; height: 40px;"></div>
                <strong>CECILIA L. OCHAVO-SAYCON</strong><br>
                <span style="font-size: 9px;">OIC Regional Director</span><br>
                <span style="font-size: 9px;">Signature over Printed of COA</span><br>
                <span style="font-size: 9px;">Representative</span>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 45%; text-align: center; vertical-align: top;">
                <div style="border-bottom: 1px solid #000; margin: 5px 0; height: 40px;"></div>
                <strong>PRUDENCIO C. BULAWAN IV</strong><br>
                <span style="font-size: 9px;">D./Prop. Inspector</span><br>
                <span style="font-size: 9px;">Member</span>
            </td>
            <td style="width: 45%; text-align: center; vertical-align: top;">
                <div style="border-bottom: 1px solid #000; margin: 5px 0; height: 40px;"></div>
                <strong>MAY FLORENCE A. PABELENONIO</strong><br>
                <span style="font-size: 9px;">Supply Officer II/GSS</span><br>
                <span style="font-size: 9px;">Member</span>
            </td>
        </tr>
    </table>
</div>

<div class="footer" style="text-align: center;">
    <p>Generated on: {{ now()->format('F d, Y h:i A') }} | Inventory Management System - MGB Region VI</p>
</div>

</body>
</html>
