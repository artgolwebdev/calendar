<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\FamilyMember;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FamilyEventGeneratorService
{
    /**
     * Create or update a single canonical auto-generated event per date-type
     * for the member on the calendar the member belongs to.
     */
    public function syncForMember(FamilyMember $member): void
    {
        $this->syncDateType($member->calendar, $member, 'birthday');

        if ($member->anniversary_date) {
            $this->syncDateType($member->calendar, $member, 'anniversary');
        }
    }

    /**
     * Create or update auto-generated events for all family members of a
     * single calendar.
     */
    public function syncForCalendar(Calendar $calendar): void
    {
        foreach ($calendar->familyMembers as $member) {
            $this->syncDateType($calendar, $member, 'birthday');

            if ($member->anniversary_date) {
                $this->syncDateType($calendar, $member, 'anniversary');
            }
        }
    }

    /**
     * Remove all auto-generated events for the member.
     */
    public function purgeForMember(FamilyMember $member): void
    {
        CalendarEvent::where('family_member_id', $member->id)
            ->where('is_auto_generated', true)
            ->delete();
    }

    /**
     * Resolve a collection of events against a specific year, deriving a
     * display date (handling e.g. Feb 29) and a display title (adding age /
     * years married to auto-generated events). Transient attributes only,
     * nothing is persisted.
     *
     * @param  Collection<int, CalendarEvent>  $events
     * @return Collection<int, CalendarEvent>
     */
    public function resolveForYear(Collection $events, int $year): Collection
    {
        return $events->map(function (CalendarEvent $event) use ($year) {
            $sourceDate = $event->event_date;
            $daysInTargetMonth = Carbon::create($year, $sourceDate->month, 1)->daysInMonth;

            $event->display_date = Carbon::create($year, $sourceDate->month, min($sourceDate->day, $daysInTargetMonth));
            $event->display_title = $this->displayTitle($event, $year);

            return $event;
        });
    }

    private function syncDateType(Calendar $calendar, FamilyMember $member, string $type): void
    {
        $date = $type === 'birthday' ? $member->birth_date : $member->anniversary_date;

        $event = $calendar->calendarEvents()->firstOrNew([
            'family_member_id' => $member->id,
            'event_type' => $type,
            'is_auto_generated' => true,
        ]);

        if (! $event->exists || ! $event->title_customized) {
            $event->title = $this->baseTitle($member, $type);
        }

        $event->event_date = $date;
        $event->save();
    }

    private function baseTitle(FamilyMember $member, string $type): string
    {
        return $type === 'anniversary'
            ? "יום נישואין - {$member->name}"
            : "יום הולדת - {$member->name}";
    }

    private function displayTitle(CalendarEvent $event, int $year): string
    {
        if (! $event->is_auto_generated || $event->event_type === 'custom') {
            return $event->title;
        }

        $years = max($year - $event->event_date->year, 0);
        $suffix = $event->event_type === 'anniversary' ? "{$years} שנים" : (string) $years;

        return "{$event->title} ({$suffix})";
    }
}
