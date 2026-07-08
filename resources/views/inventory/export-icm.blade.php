<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICM Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; }
        .title { text-align: center; font-size: 16px; margin-bottom: 10px; font-weight: 700; }
        .meta { margin-bottom: 10px; }
        .meta td { border: none !important; padding: 2px 0; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="title">ICM Inventory Export</div>

    @if(isset($tab) || isset($title))
        <table class="meta">
            <tr>
                <td>
                    <strong>Tab:</strong> {{ $tab ?? 'icm' }}
                </td>
                <td class="text-right">
                    <strong>Title:</strong> {{ $title ?? 'ICM' }}
                </td>
            </tr>
        </table>
    @endif

    <table>
        <thead>
            <tr>
                <th>ICM No</th>
                <th>Division</th>
                <th>Personnel</th>
                <th>Problem Description</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Hardware/Software</th>
                <th>Brand/Model</th>
                <th>Serial No</th>
                <th>Property No</th>
                <th>Open Date</th>
                <th>Close Date</th>
                <th>Findings</th>
                <th>Actions Taken</th>
                <th>Recommendations</th>
            </tr>
        </thead>
        <tbody>
        @forelse($items ?? [] as $item)
            <tr>
                <td>{{ $item->icm_no ?? 'N/A' }}</td>
                <td>{{ $item->division ?? 'N/A' }}</td>
                <td>{{ $item->requesting_personnel ?? 'N/A' }}</td>
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
                <td colspan="15" class="text-center">No ICM items found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>

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
</html>
