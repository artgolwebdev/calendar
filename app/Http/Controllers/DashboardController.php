<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Services\CalendarMonthDataService;
use App\Services\HebrewDateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected const SHORT_WEEKDAYS = ['א׳', 'ב׳', 'ג׳', 'ד׳', 'ה׳', 'ו׳', 'ש׳'];

    public function __construct(
        protected CalendarMonthDataService $calendarMonthDataService,
        protected HebrewDateService $hebrewDateService,
    ) {}

    public function index()
    {
        $user = Auth::user();

        $calendars = $user->calendars;
        $mainCalendar = $user->mainCalendar();

        $days = collect();
        $month = null;
        $year = null;

        if ($mainCalendar) {
            $today = now()->startOfDay();
            $month = $today->month;
            $year = $today->year;

            // Two-week window: the current week (Sun–Sat) plus the following week.
            $windowStart = $today->copy()->startOfWeek(Carbon::SUNDAY);
            $windowEnd = $windowStart->copy()->addDays(13);

            // The window spans at most two months; fetch data for each covered month.
            $events = collect();
            $holidays = collect();
            $coveredMonths = [
                "{$windowStart->year}-{$windowStart->month}" => [$windowStart->year, $windowStart->month],
            ];

            if ($windowEnd->month !== $windowStart->month || $windowEnd->year !== $windowStart->year) {
                $coveredMonths["{$windowEnd->year}-{$windowEnd->month}"] = [$windowEnd->year, $windowEnd->month];
            }

            foreach ($coveredMonths as [$windowYear, $windowMonth]) {
                $events = $events->merge(
                    $this->calendarMonthDataService->eventsForMonth($mainCalendar, $windowYear, $windowMonth)
                );
                $holidays = $holidays->merge(
                    $this->calendarMonthDataService->holidaysForMonth($windowYear, $windowMonth)
                );
            }

            $eventsByDate = $events->groupBy(fn (CalendarEvent $event) => $event->display_date->toDateString());
            $holidaysByDate = $holidays->groupBy(fn (array $holiday) => $holiday['date'] ?? '');

            for ($offset = 0; $offset < 14; $offset++) {
                $date = $windowStart->copy()->addDays($offset);
                $dateKey = $date->toDateString();

                $items = collect();

                foreach ($holidaysByDate->get($dateKey, collect()) as $holiday) {
                    $items->push([
                        'type' => 'holiday',
                        'title' => $holiday['title'] ?? $holiday['hebrew'] ?? 'חג',
                    ]);
                }

                foreach ($eventsByDate->get($dateKey, collect()) as $event) {
                    $items->push([
                        'type' => $event->event_type,
                        'title' => $event->display_title,
                    ]);
                }

                $days->push([
                    'day' => $date->day,
                    'month' => $date->month,
                    'year' => $date->year,
                    'date' => $date->toDateString(),
                    'weekday' => self::SHORT_WEEKDAYS[$date->dayOfWeek],
                    'hebrew_date' => $this->hebrewDateService->toHebrewDayMonthString($date),
                    'is_today' => $date->isSameDay($today),
                    'items' => $items,
                    'total' => $items->count(),
                ]);
            }
        }

        return view('dashboard', compact('calendars', 'mainCalendar', 'days', 'month', 'year'));
    }
}
