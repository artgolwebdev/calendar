<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Services\FamilyEventGeneratorService;
use App\Services\HebrewDateService;
use App\Services\IsraeliHolidaysService;
use App\Services\MonthPageStyleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    protected HebrewDateService $hebrewDateService;

    protected MonthPageStyleService $monthPageStyleService;

    protected FamilyEventGeneratorService $familyEventGeneratorService;

    public function __construct(
        HebrewDateService $hebrewDateService,
        MonthPageStyleService $monthPageStyleService,
        FamilyEventGeneratorService $familyEventGeneratorService
    ) {
        $this->hebrewDateService = $hebrewDateService;
        $this->monthPageStyleService = $monthPageStyleService;
        $this->familyEventGeneratorService = $familyEventGeneratorService;
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
        $data = $request->validated();

        // Handle cover image upload
        if ($request->hasFile('cover_image_path')) {
            $path = $request->file('cover_image_path')->store('calendar-covers', 'public');
            $data['cover_image_path'] = $path;
        }

        $calendar = Auth::user()->calendars()->create($data);

        // Create 12 month pages for the calendar
        for ($month = 1; $month <= 12; $month++) {
            $calendar->monthPages()->create([
                'month_number' => $month,
                ...$this->monthPageStyleService->defaults(),
            ]);
        }

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

        return view('calendars.edit', compact('calendar'));
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

        $calendar->update($data);

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
        $events = $this->familyEventGeneratorService->resolveForYear(
            $calendar->calendarEvents()
                ->where('is_auto_generated', false)
                ->whereYear('event_date', $year)
                ->whereMonth('event_date', $monthNumber)
                ->with('familyMember')
                ->get()
                ->merge(
                    $calendar->calendarEvents()
                        ->where('is_auto_generated', true)
                        ->with('familyMember')
                        ->get()
                ),
            $year
        )->filter(fn (CalendarEvent $event) => $event->display_date->month === $monthNumber)
            ->sortBy(fn (CalendarEvent $event) => $event->display_date)
            ->values();

        // Get Israeli holidays for this month
        try {
            $israeliHolidaysService = app(IsraeliHolidaysService::class);
            $holidays = $israeliHolidaysService->getHolidaysForMonth($year, $monthNumber);
        } catch (\Exception $e) {
            $holidays = [];
            \Log::error('Error fetching holidays: '.$e->getMessage());
        }

        // Calculate navigation dates
        $currentDate = Carbon::create($year, $monthNumber, 1);
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $userMedia = Auth::user()->getMedia('user_media');

        return view('calendars.month', compact('calendar', 'monthPage', 'events', 'holidays', 'year', 'previousMonth', 'nextMonth', 'userMedia'));
    }
}
