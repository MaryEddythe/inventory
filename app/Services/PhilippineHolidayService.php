<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class PhilippineHolidayService
{
    public function officialHolidaysForYear(int $year): Collection
    {
        $easterSunday = Carbon::createFromTimestamp(easter_date($year));

        $holidays = [
            [
                'holiday_date' => Carbon::create($year, 1, 1),
                'title' => "New Year's Day",
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => $easterSunday->copy()->subDays(3),
                'title' => 'Maundy Thursday',
                'notes' => 'Movable regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => $easterSunday->copy()->subDays(2),
                'title' => 'Good Friday',
                'notes' => 'Movable regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => $easterSunday->copy()->subDay(),
                'title' => 'Black Saturday',
                'notes' => 'Special non-working day',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 4, 9),
                'title' => 'Araw ng Kagitingan',
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 5, 1),
                'title' => 'Labor Day',
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 6, 12),
                'title' => 'Independence Day',
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 8, 21),
                'title' => 'Ninoy Aquino Day',
                'notes' => 'Special non-working day',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => $this->lastMondayOfMonth($year, 8),
                'title' => 'National Heroes Day',
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 11, 1),
                'title' => "All Saints' Day",
                'notes' => 'Special non-working day',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 11, 30),
                'title' => 'Bonifacio Day',
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 12, 8),
                'title' => 'Feast of the Immaculate Conception of Mary',
                'notes' => 'Special non-working day',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 12, 25),
                'title' => 'Christmas Day',
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 12, 30),
                'title' => 'Rizal Day',
                'notes' => 'Regular holiday',
                'source' => 'Philippine calendar',
            ],
            [
                'holiday_date' => Carbon::create($year, 12, 31),
                'title' => "Last Day of the Year",
                'notes' => 'Special non-working day',
                'source' => 'Philippine calendar',
            ],
        ];

        return collect($holidays)
            ->map(fn (array $holiday) => $this->makeHolidayObject($holiday))
            ->keyBy(fn (object $holiday) => $holiday->holiday_date->toDateString())
            ->sortBy(fn (object $holiday) => $holiday->holiday_date->toDateString())
            ->values();
    }

    public function officialHolidaysForMonth(int $year, int $month): Collection
    {
        return $this->officialHolidaysForYear($year)
            ->filter(fn (object $holiday) => (int) $holiday->holiday_date->month === $month)
            ->values();
    }

    public function holidayForDate(Carbon|string $date): ?object
    {
        $carbonDate = Carbon::parse($date);
        $dateKey = $carbonDate->toDateString();

        return $this->officialHolidaysForYear((int) $carbonDate->year)
            ->first(fn (object $holiday) => $holiday->holiday_date->toDateString() === $dateKey);
    }

    public function mergeHolidays(Collection $officialHolidays, Collection $customHolidays): Collection
    {
        $merged = $officialHolidays->keyBy(fn (object $holiday) => $holiday->holiday_date->toDateString());

        foreach ($customHolidays as $holiday) {
            $holidayDate = $holiday->holiday_date instanceof Carbon
                ? $holiday->holiday_date->copy()
                : Carbon::parse($holiday->holiday_date);

            $merged->put($holidayDate->toDateString(), (object) [
                'id' => $holiday->id ?? null,
                'holiday_date' => $holidayDate,
                'title' => $holiday->title,
                'notes' => $holiday->notes,
                'source' => 'HR-added',
                'is_custom' => true,
            ]);
        }

        return $merged
            ->sortBy(fn (object $holiday) => $holiday->holiday_date->toDateString())
            ->values();
    }

    public function mergeHolidayForDate(Carbon|string $date, ?Collection $customHolidays = null): ?object
    {
        $carbonDate = Carbon::parse($date);
        $dateKey = $carbonDate->toDateString();

        if ($customHolidays) {
            $customHoliday = $customHolidays->first(function ($holiday) use ($dateKey) {
                $holidayDate = $holiday->holiday_date instanceof Carbon
                    ? $holiday->holiday_date->toDateString()
                    : Carbon::parse($holiday->holiday_date)->toDateString();

                return $holidayDate === $dateKey;
            });

            if ($customHoliday) {
                return (object) [
                    'id' => $customHoliday->id ?? null,
                    'holiday_date' => $customHoliday->holiday_date instanceof Carbon
                        ? $customHoliday->holiday_date->copy()
                        : Carbon::parse($customHoliday->holiday_date),
                    'title' => $customHoliday->title,
                    'notes' => $customHoliday->notes,
                    'source' => 'HR-added',
                    'is_custom' => true,
                ];
            }
        }

        return $this->officialHolidaysForYear((int) $carbonDate->year)
            ->first(fn (object $holiday) => $holiday->holiday_date->toDateString() === $dateKey);
    }

    protected function makeHolidayObject(array $holiday): object
    {
        return (object) [
            'holiday_date' => $holiday['holiday_date'] instanceof Carbon
                ? $holiday['holiday_date']->copy()->startOfDay()
                : Carbon::parse($holiday['holiday_date'])->startOfDay(),
            'title' => $holiday['title'],
            'notes' => $holiday['notes'] ?? null,
            'source' => $holiday['source'] ?? 'Philippine calendar',
            'is_custom' => $holiday['is_custom'] ?? false,
        ];
    }

    protected function lastMondayOfMonth(int $year, int $month): Carbon
    {
        $date = Carbon::create($year, $month, 1)->endOfMonth();

        while (! $date->isMonday()) {
            $date->subDay();
        }

        return $date;
    }
}
