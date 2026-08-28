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

];