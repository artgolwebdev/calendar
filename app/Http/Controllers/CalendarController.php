<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Services\CalendarCreationService;
use App\Services\CalendarMonthDataService;
use App\Services\DayViewLayoutService;
use App\Services\FamilyEventGeneratorService;
use App\Services\HebrewDateService;
use App\Services\IsraeliHolidaysService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    protected HebrewDateService $hebrewDateService;

    protected FamilyEventGeneratorService $familyEventGeneratorService;

    protected CalendarMonthDataService $calendarMonthDataService;

    protected DayViewLayoutService $dayViewLayoutService;

    protected CalendarCreationService $calendarCreationService;

    public function __construct(
        HebrewDateService $hebrewDateService,
        FamilyEventGeneratorService $familyEventGeneratorService,
        CalendarMonthDataService $calendarMonthDataService,
        DayViewLayoutService $dayViewLayoutService,
        CalendarCreationService $calendarCreationService
    ) {
        $this->hebrewDateService = $hebrewDateService;
        $this->familyEventGeneratorService = $familyEventGeneratorService;
        $this->calendarMonthDataService = $calendarMonthDataService;
        $this->dayViewLayoutService = $dayViewLayoutService;
        $this->calendarCreationService = $calendarCreationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $calendars = Auth::user()->calendars;

        return view('calendars.index', compact('calendars'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('calendars.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarRequest $request)
    {
        $calendar = $this->calendarCreationService->create(
            Auth::user(),
            $request->safe()->except(['cover_image_path']),
            $request->file('cover_image_path')
        );

        return redirect()->route('calendars.show', $calendar)
            ->with('success', 'לוח שנה נוצר בהצלחה');
    }

    /**
     * Display the specified resource.
     */
    public function show(Calendar $calendar)
    {
        $this->authorize('view', $calendar);

        $monthPages = $calendar->monthPages()->orderBy('month_number')->get();

        $year = now()->year;

        // Group the calendar's events for the current year by month, resolving
        // auto-generated recurring family events against the displayed year
        $events = $calendar->calendarEvents()
            ->where('is_auto_generated', false)
            ->whereYear('event_date', $year)
            ->get()
            ->merge($calendar->calendarEvents()->where('is_auto_generated', true)->get());

        $eventsByMonth = $this->familyEventGeneratorService->resolveForYear($events, $year)
            ->groupBy(fn (CalendarEvent $event) => $event->display_date->month);

        // Fetch all major Israeli holidays for the current year once and group by month
        $holidaysByMonth = collect([]);
        try {
            $holidaysByMonth = collect(app(IsraeliHolidaysService::class)->getHolidaysForYear($year))
                ->filter(fn (array $holiday) => isset($holiday['date']) && ($holiday['category'] ?? null) === 'holiday')
                ->groupBy(fn (array $holiday) => Carbon::parse($holiday['date'])->month);
        } catch (\Exception $e) {
            \Log::error('Error fetching holidays: '.$e->getMessage());
        }

        // Get Hebrew month name and summary data for each month
        $monthInfo = [];
        foreach ($monthPages as $monthPage) {
            $startDate = now()->month($monthPage->month_number)->startOfMonth();

            $monthEvents = $eventsByMonth->get($monthPage->month_number, collect());
            $monthHolidays = $holidaysByMonth->get($monthPage->month_number, collect());

            $imageUrl = null;

            if ($monthPage->background_media_id && $monthPage->backgroundMedia) {
                $imageUrl = $monthPage->backgroundMedia->getUrl();
            } elseif ($imagePath = $monthPage->custom_image_path ?? $monthPage->background_image_path) {
                $imageUrl = asset('storage/'.$imagePath);
            }

            $monthInfo[$monthPage->month_number] = [
                'hebrew_month' => $this->hebrewDateService->hebrewMonthName($startDate),
                'background_image_url' => $imageUrl,
                'events_count' => $monthEvents->count(),
                'events' => $monthEvents->take(3)
                    ->map(fn (CalendarEvent $event) => ['title' => $event->display_title, 'type' => $event->event_type])
                    ->values()
                    ->all(),
                'holidays_count' => $monthHolidays->count(),
                'holidays' => $monthHolidays->take(3)
                    ->map(fn (array $holiday) => $holiday['title'] ?? $holiday['hebrew'] ?? 'חג')
                    ->values()
                    ->all(),
            ];
        }

        $hebrewYear = $this->hebrewDateService->toHebrewArray(now())['year'];

        return view('calendars.show', compact('calendar', 'monthPages', 'monthInfo', 'year', 'hebrewYear'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        $familyMembers = $calendar->familyMembers()
            ->with(['folder' => fn ($query) => $query->withCount('media')])
            ->orderBy('name')
            ->get();

        return view('calendars.edit', compact('calendar', 'familyMembers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarRequest $request, Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        $data = $request->validated();

        // Handle cover image upload
        if ($request->hasFile('cover_image_path')) {
            // Delete old image if exists
            if ($calendar->cover_image_path) {
                \Storage::disk('public')->delete($calendar->cover_image_path);
            }

            $path = $request->file('cover_image_path')->store('calendar-covers', 'public');
            $data['cover_image_path'] = $path;
        }

        $shouldBeMain = (bool) ($data['is_main'] ?? false);
        $data['is_main'] = $shouldBeMain;

        DB::transaction(function () use ($calendar, $data, $shouldBeMain) {
            $calendar->update($data);

            // Only one calendar can be the main calendar per user
            if ($shouldBeMain) {
                $calendar->user->calendars()
                    ->whereKeyNot($calendar->id)
                    ->update(['is_main' => false]);
            }
        });

        return redirect()->route('calendars.show', $calendar)
            ->with('success', 'לוח השנה עודכן בהצלחה');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Calendar $calendar)
    {
        $this->authorize('delete', $calendar);
        $calendar->delete();

        return redirect()->route('dashboard')
            ->with('success', 'לוח השנה נמחק בהצלחה');
    }

    /**
     * Display a specific month page
     */
    public function showMonth(Calendar $calendar, $monthNumber, $year = null)
    {
        $this->authorize('view', $calendar);

        $monthNumber = (int) $monthNumber;
        $year = $year ?? now()->year;
        $monthPage = $calendar->monthPages()->where('month_number', $monthNumber)->firstOrFail();

        // Get events for this month, resolving auto-generated recurring family
        // events against the displayed year
        $events = $this->calendarMonthDataService->eventsForMonth($calendar, $year, $monthNumber);

        // Get Israeli holidays for this month
        $holidays = $this->calendarMonthDataService->holidaysForMonth($year, $monthNumber);

        // Calculate navigation dates
        $currentDate = Carbon::create($year, $monthNumber, 1);
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $userMedia = Auth::user()->getMedia('user_media');

        return view('calendars.month', compact('calendar', 'monthPage', 'events', 'holidays', 'year', 'previousMonth', 'nextMonth', 'userMedia'));
    }

    /**
     * Display a single day with events positioned by hour.
     */
    public function showDay(Calendar $calendar, string $date)
    {
        $this->authorize('view', $calendar);

        try {
            $currentDate = Carbon::parse($date)->startOfDay();
        } catch (\Exception $e) {
            abort(404);
        }

        $events = $this->calendarMonthDataService->eventsForMonth($calendar, $currentDate->year, $currentDate->month)
            ->filter(fn (CalendarEvent $event) => $event->display_date->isSameDay($currentDate))
            ->values();

        $holidays = collect($this->calendarMonthDataService->holidaysForMonth($currentDate->year, $currentDate->month))
            ->filter(fn (array $holiday) => isset($holiday['date']) && Carbon::parse($holiday['date'])->isSameDay($currentDate))
            ->values()
            ->all();

        [$allDayEvents, $positionedEvents] = $this->layoutDayEvents($events);

        $isToday = $currentDate->isSameDay(now());
        $nowTop = $isToday
            ? round((now()->hour * 60 + now()->minute) / DayViewLayoutService::DAY_MINUTES * 100, 4)
            : 0;

        $hebrewDate = $this->hebrewDateService->toHebrewDayMonthString($currentDate);
        $hebrewYear = $this->hebrewDateService->toHebrewArray($currentDate)['year'];

        $previousDate = $currentDate->copy()->subDay();
        $nextDate = $currentDate->copy()->addDay();

        return view('calendars.day', compact(
            'calendar', 'currentDate', 'events', 'holidays', 'allDayEvents',
            'positionedEvents', 'isToday', 'nowTop', 'hebrewDate', 'hebrewYear',
            'previousDate', 'nextDate'
        ));
    }

    /**
     * Split the day's events into all-day events and positioned timed events.
     *
     * @param  Collection<int, CalendarEvent>  $events
     * @return array{0: array<int, CalendarEvent>, 1: array<int, array<string, mixed>>}
     */
    protected function layoutDayEvents($events): array
    {
        $allDayEvents = [];
        $timed = [];

        foreach ($events as $event) {
            $start = $this->timeToMinutes($event->start_time);

            if ($start === null) {
                $allDayEvents[] = $event;

                continue;
            }

            $timed[] = [
                'start' => $start,
                'end' => $this->timeToMinutes($event->end_time) ?? min($start + 60, DayViewLayoutService::DAY_MINUTES),
                'event' => $event,
            ];
        }

        $positionedEvents = array_map(function (array $item): array {
            $event = $item['event'];

            return [
                'event' => $event,
                'title' => $event->display_title ?? $event->title,
                'start_label' => $this->minutesToTime((int) $item['start']),
                'end_label' => $this->minutesToTime((int) $item['end']),
                'top' => $item['top'],
                'height' => $item['height'],
                'left' => $item['left'],
                'width' => $item['width'],
            ];
        }, $this->dayViewLayoutService->layout($timed));

        return [$allDayEvents, $positionedEvents];
    }

    /**
     * Convert an "H:i" time string to minutes since midnight.
     */
    protected function timeToMinutes(?string $time): ?int
    {
        if ($time === null || $time === '') {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }

    /**
     * Convert minutes since midnight to an "H:i" time string.
     */
    protected function minutesToTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
