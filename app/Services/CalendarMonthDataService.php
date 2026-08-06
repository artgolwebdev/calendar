<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use Illuminate\Support\Collection;

class CalendarMonthDataService
{
    public function __construct(
        protected FamilyEventGeneratorService $familyEventGeneratorService,
        protected IsraeliHolidaysService $israeliHolidaysService,
    ) {}

    /**
     * Events for the given month, with auto-generated recurring family events
     * resolved against the displayed year.
     *
     * @return Collection<int, CalendarEvent>
     */
    public function eventsForMonth(Calendar $calendar, int $year, int $month): Collection
    {
        return $this->familyEventGeneratorService->resolveForYear(
            $calendar->calendarEvents()
                ->where('is_auto_generated', false)
                ->whereYear('event_date', $year)
                ->whereMonth('event_date', $month)
                ->with('familyMember')
                ->get()
                ->merge(
                    $calendar->calendarEvents()
                        ->where('is_auto_generated', true)
                        ->with('familyMember')
                        ->get()
                ),
            $year
        )->filter(fn (CalendarEvent $event) => $event->display_date->month === $month)
            ->sortBy(fn (CalendarEvent $event) => $event->display_date)
            ->values();
    }

    /**
     * Israeli holidays for the given month.
     *
     * @return array<int, array<string, mixed>>
     */
    public function holidaysForMonth(int $year, int $month): array
    {
        try {
            return $this->israeliHolidaysService->getHolidaysForMonth($year, $month);
        } catch (\Exception $e) {
            \Log::error('Error fetching holidays: '.$e->getMessage());

            return [];
        }
    }
}
