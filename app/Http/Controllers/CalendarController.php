<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Models\Calendar;
use App\Services\HebrewDateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    protected HebrewDateService $hebrewDateService;

    public function __construct(HebrewDateService $hebrewDateService)
    {
        $this->hebrewDateService = $hebrewDateService;
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
                'font_choice' => 'default',
                'overlay_opacity' => 30,
                'day_box_bg_color' => '#FFFFFF',
                'day_box_font_color' => '#2B2E3A',
                'day_box_bg_opacity' => 100,
                'show_adjacent_month_days' => true,
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
        
        // Get Hebrew date ranges for each month
        $monthInfo = [];
        foreach ($monthPages as $monthPage) {
            $startDate = now()->month($monthPage->month_number)->startOfMonth();
            $endDate = now()->month($monthPage->month_number)->endOfMonth();
            
            $hebrewStart = $this->hebrewDateService->toHebrewString($startDate);
            $hebrewEnd = $this->hebrewDateService->toHebrewString($endDate);
            
            $monthInfo[$monthPage->month_number] = [
                'hebrew_start' => $hebrewStart,
                'hebrew_end' => $hebrewEnd,
            ];
        }

        return view('calendars.show', compact('calendar', 'monthPages', 'monthInfo'));
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
        
        // Get events for this month
        $events = $calendar->calendarEvents()
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $monthNumber)
            ->with('familyMember')
            ->orderBy('event_date')
            ->get();

        // Get Israeli holidays for this month
        try {
            $israeliHolidaysService = app(\App\Services\IsraeliHolidaysService::class);
            $holidays = $israeliHolidaysService->getHolidaysForMonth($year, $monthNumber);
        } catch (\Exception $e) {
            $holidays = [];
            \Log::error('Error fetching holidays: ' . $e->getMessage());
        }

        // Calculate navigation dates
        $currentDate = \Carbon\Carbon::create($year, $monthNumber, 1);
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        return view('calendars.month', compact('calendar', 'monthPage', 'events', 'holidays', 'year', 'previousMonth', 'nextMonth'));
    }
}
