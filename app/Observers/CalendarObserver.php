<?php

namespace App\Observers;

use App\Models\Calendar;
use App\Services\FamilyEventGeneratorService;

class CalendarObserver
{
    public function created(Calendar $calendar): void
    {
        app(FamilyEventGeneratorService::class)->syncForCalendar($calendar);
    }
}
