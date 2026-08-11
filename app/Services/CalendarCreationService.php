<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class CalendarCreationService
{
    public function __construct(protected MonthPageStyleService $monthPageStyleService) {}

    /**
     * Create a calendar with its 12 month pages, storing the cover image if provided.
     */
    public function create(User $user, array $data, ?UploadedFile $cover = null): Calendar
    {
        if ($cover) {
            $data['cover_image_path'] = $cover->store('calendar-covers', 'public');
        }

        $calendar = $user->calendars()->create($data);

        for ($month = 1; $month <= 12; $month++) {
            $calendar->monthPages()->create([
                'month_number' => $month,
                ...$this->monthPageStyleService->defaults(),
            ]);
        }

        return $calendar;
    }
}
