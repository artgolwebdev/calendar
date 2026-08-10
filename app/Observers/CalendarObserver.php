<?php

namespace App\Observers;

use App\Models\Calendar;
use App\Services\FamilyEventGeneratorService;
use App\Services\FolderSyncService;

class CalendarObserver
{
    public function created(Calendar $calendar): void
    {
        app(FolderSyncService::class)->ensureRootFor($calendar);
        app(FamilyEventGeneratorService::class)->syncForCalendar($calendar);
    }

    public function updated(Calendar $calendar): void
    {
        if ($calendar->wasChanged('name')) {
            app(FolderSyncService::class)->ensureRootFor($calendar);
        }
    }
}
