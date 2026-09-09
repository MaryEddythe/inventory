<?php

namespace Tests\Unit;

use App\Http\Controllers\AttendanceController;
use App\Services\PhilippineHolidayService;
use Carbon\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class AttendanceScheduleTest extends TestCase
{
    public function test_friday_holidays_use_the_holiday_schedule(): void
    {
        $controller = new AttendanceController($this->createMock(PhilippineHolidayService::class));

        $scheduleType = $this->invokeResolveScheduleTypeForDate($controller, Carbon::create(2026, 9, 4), true);

        $this->assertSame('holiday', $scheduleType);
    }

    public function test_monday_through_thursday_use_holiday_schedule_when_friday_is_a_holiday(): void
    {
        $controller = new AttendanceController($this->createMock(PhilippineHolidayService::class));

        $scheduleType = $this->invokeResolveScheduleTypeForDate($controller, Carbon::create(2026, 9, 3), false, true);

        $this->assertSame('holiday', $scheduleType);
    }

    public function test_monday_through_thursday_holidays_keep_the_regular_schedule(): void
    {
        $controller = new AttendanceController($this->createMock(PhilippineHolidayService::class));

        $scheduleType = $this->invokeResolveScheduleTypeForDate($controller, Carbon::create(2026, 9, 7), true);

        $this->assertSame('regular', $scheduleType);
    }

    public function test_non_holidays_use_the_regular_schedule(): void
    {
        $controller = new AttendanceController($this->createMock(PhilippineHolidayService::class));

        $scheduleType = $this->invokeResolveScheduleTypeForDate($controller, Carbon::create(2026, 9, 4), false);

        $this->assertSame('regular', $scheduleType);
    }

    protected function invokeResolveScheduleTypeForDate(AttendanceController $controller, Carbon $date, bool $isHoliday, bool $isFridayHoliday = false): string
    {
        $method = new ReflectionMethod($controller, 'resolveScheduleTypeForDate');
        $method->setAccessible(true);

        return $method->invoke($controller, $date, $isHoliday, $isFridayHoliday);
    }
}
