<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Requests\UpdateCalendarEventRequest;
use App\Models\Calendar;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    /**
     * Display a listing of events for a specific calendar
     */
    public function index(Calendar $calendar)
    {
        $this->authorize('view', $calendar);
        $events = $calendar->calendarEvents()->with('familyMember')->get();
        return view('calendar-events.index', compact('calendar', 'events'));
    }

    /**
     * Show the form for creating a new event
     */
    public function create(Calendar $calendar)
    {
        $this->authorize('view', $calendar);
        return view('calendar-events.create', compact('calendar'));
    }

    /**
     * Store a newly created event
     */
    public function store(StoreCalendarEventRequest $request, Calendar $calendar)
    {
        $this->authorize('view', $calendar);
        $calendar->calendarEvents()->create($request->validated());

        return redirect()->route('calendars.show', $calendar)
            ->with('success', 'אירוע נוצר בהצלחה');
    }

    /**
     * Display the specified event
     */
    public function show(Calendar $calendar, CalendarEvent $calendarEvent)
    {
        $this->authorize('view', $calendar);
        $calendarEvent->load('familyMember');
        return view('calendar-events.show', compact('calendar', 'calendarEvent'));
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit(Calendar $calendar, CalendarEvent $calendarEvent)
    {
        $this->authorize('view', $calendar);
        $familyMembers = $calendar->user->familyMembers;
        return view('calendar-events.edit', compact('calendar', 'calendarEvent', 'familyMembers'));
    }

    /**
     * Update the specified event
     */
    public function update(UpdateCalendarEventRequest $request, Calendar $calendar, CalendarEvent $calendarEvent)
    {
        $this->authorize('view', $calendar);
        $calendarEvent->update($request->validated());

        return redirect()->route('calendars.show', $calendar)
            ->with('success', 'האירוע עודכן בהצלחה');
    }

    /**
     * Remove the specified event
     */
    public function destroy(Calendar $calendar, CalendarEvent $calendarEvent)
    {
        $this->authorize('view', $calendar);
        $calendarEvent->delete();

        return redirect()->route('calendars.show', $calendar)
            ->with('success', 'האירוע נמחק בהצלחה');
    }
}
