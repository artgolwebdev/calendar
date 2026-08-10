<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Requests\UpdateCalendarEventRequest;
use App\Models\Calendar;
use App\Models\CalendarEvent;
use Illuminate\Support\Arr;

class CalendarEventController extends Controller
{
    /**
     * Display a listing of events for a specific calendar.
     */
    public function index(Calendar $calendar)
    {
        $this->authorize('view', $calendar);

        $events = $calendar->calendarEvents()
            ->with('familyMember')
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

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

        $data = $request->validated();

        if ($request->hasFile('cover_image_path')) {
            $data['cover_image_path'] = $request->file('cover_image_path')->store('calendar-covers', 'public');
        }

        $calendar->calendarEvents()->create($data);

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

        $familyMembers = $calendar->familyMembers;

        return view('calendar-events.edit', compact('calendar', 'calendarEvent', 'familyMembers'));
    }

    /**
     * Update the specified event
     */
    public function update(UpdateCalendarEventRequest $request, Calendar $calendar, CalendarEvent $calendarEvent)
    {
        $this->authorize('view', $calendar);

        $data = $request->validated();

        if ($request->hasFile('cover_image_path')) {
            if ($calendarEvent->cover_image_path) {
                \Storage::disk('public')->delete($calendarEvent->cover_image_path);
            }

            $data['cover_image_path'] = $request->file('cover_image_path')->store('calendar-covers', 'public');
        }

        // Auto-generated events are derived from the family member's birth /
        // anniversary date, so only presentation fields may be changed.
        if ($calendarEvent->is_auto_generated) {
            $data = Arr::only($data, ['title', 'description', 'cover_image_path']);

            if (array_key_exists('title', $data)) {
                $data['title_customized'] = true;
            }
        }

        $calendarEvent->update($data);

        return redirect()->route('calendars.show', $calendar)
            ->with('success', 'האירוע עודכן בהצלחה');
    }

    /**
     * Remove the specified event
     */
    public function destroy(Calendar $calendar, CalendarEvent $calendarEvent)
    {
        $this->authorize('view', $calendar);
        $this->ensureDeletable($calendarEvent);

        $calendarEvent->delete();

        return redirect()->route('calendars.show', $calendar)
            ->with('success', 'האירוע נמחק בהצלחה');
    }

    /**
     * Block deletion of auto-generated events, which are derived from the
     * attached family member's birth / anniversary date.
     */
    protected function ensureDeletable(CalendarEvent $calendarEvent): void
    {
        abort_if($calendarEvent->is_auto_generated, 403, 'אירועים אוטומטיים נגזרים מחבר המשפחה ואינם ניתנים למחיקה.');
    }
}
