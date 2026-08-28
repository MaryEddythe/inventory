<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Biometric Attendance Slots
    |--------------------------------------------------------------------------
    |
    | Single source of truth for the biometric attendance tiles shown on
    | dashboards. Keep slot definitions here rather than hardcoding them in
    | views; swap the static list for real data once the biometric module is
    | wired up, and the layout will not change.
    |
    | Each entry renders one square attendance tile with:
    |   - 'label' : the slot name shown under the icon
    |   - 'state' : the current status text shown beneath the label
    */
    'biometric_slots' => [
        ['label' => 'Biometric 1', 'state' => 'Pending'],
        ['label' => 'Biometric 2', 'state' => 'Pending'],
        ['label' => 'Biometric 3', 'state' => 'Pending'],
        ['label' => 'Biometric 4', 'state' => 'Pending'],
        ['label' => 'Biometric 5', 'state' => 'Pending'],
        ['label' => 'Biometric 6', 'state' => 'Pending'],
        ['label' => 'Biometric 7', 'state' => 'Pending'],
    ],

    /*
    |----------------------------------------------------------------------
    | Attendance Schedules
    |----------------------------------------------------------------------
    |
    | The office uses two attendance setups:
    | - regular: 7:00 AM to 7:00 PM
    | - holiday: 8:00 AM to 5:00 PM
    |
    | Both setups treat 8:01 AM onward as late for the morning check-in.
    | The expected logout time is computed from the actual check-in time.
    */
    'default_schedule' => 'regular',
    'late_cutoff_time' => '08:00',
    'schedules' => [
        'regular' => [
            'label' => '7:00 AM - 7:00 PM',
            'start_time' => '07:00',
            'end_time' => '19:00',
            'checkout_offset_minutes' => 661, // 11 hours + 1 minute
        ],
        'holiday' => [
            'label' => '8:00 AM - 5:00 PM',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'checkout_offset_minutes' => 541, // 9 hours + 1 minute
        ],
    ],

];
