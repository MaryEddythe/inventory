<?php

namespace App\States\LeaveApplication;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class LeaveApplicationState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(PendingHr::class)
            ->allowTransition(PendingHr::class, PendingDivisionChief::class)
            ->allowTransition(PendingDivisionChief::class, PendingRegionalDirector::class)
            ->allowTransition(PendingRegionalDirector::class, Approved::class)
            ->allowTransition(PendingRegionalDirector::class, Completed::class)
            ->allowTransition(PendingHr::class, Rejected::class)
            ->allowTransition(PendingDivisionChief::class, Rejected::class)
            ->allowTransition(PendingRegionalDirector::class, Rejected::class);
    }

    public function label(): string
    {
        return static::$name ?? class_basename(static::class);
    }
}
