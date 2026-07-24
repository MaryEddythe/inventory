<?php

return [
    'enabled' => env('ACTIVITYLOG_ENABLED', true),
    'clean_after_days' => 365,
    'default_log_name' => 'default',
    'default_auth_driver' => null,
    'include_soft_deleted_subjects' => false,
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
    'default_except_attributes' => [],
    'actions' => [
        'log_activity' => \Spatie\Activitylog\Actions\LogActivityAction::class,
        'clean_log' => \Spatie\Activitylog\Actions\CleanActivityLogAction::class,
    ],
];
