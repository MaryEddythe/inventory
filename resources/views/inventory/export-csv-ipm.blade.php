@php
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ipm_export_' . now()->format('Y-m-d_H-i-s') . '.csv"');
    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, [
        'No',
        'Div.',
        'User',
        'Type',
        'Desc',
        'Condition',
        'Boot Up',
        'HW',
        'Perf',
        'Cables/Conn',
        'Periph',
        'Rem',
        'Rec',
        'Date',
        'Start',
        'End'
    ]);

    // Data rows
    foreach($items as $item) {
        fputcsv($output, [
            $item->no,
            $item->division,
            $item->enduser,
            $item->classification,
            $item->description,
            $item->condition,
            $item->system_boot_up ? '✓' : '⨉',
            $item->hardware ? '✓' : '⨉',
            $item->performance ? '✓' : '⨉',
            $item->cables_connections ? '✓' : '⨉',
            $item->peripherals ? '✓' : '⨉',
            $item->remarks ?? 'N/A',
            $item->recommendation ?? 'N/A',
            $item->date_conducted ? $item->date_conducted->format('M d, Y') : 'N/A',
            $item->time_started ?? 'N/A',
            $item->time_ended ?? 'N/A'
        ]);
    }

    fclose($output);
    exit();
@endphp
