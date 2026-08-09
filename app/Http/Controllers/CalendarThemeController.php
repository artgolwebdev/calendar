<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyThemeRequest;
use App\Models\Calendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CalendarThemeController extends Controller
{
    /**
     * Apply a pre-built theme to a calendar. When a valid month number is
     * provided only that month page is updated; otherwise the theme is applied
     * to every month page in one transaction-backed bulk update.
     */
    public function apply(ApplyThemeRequest $request, Calendar $calendar): JsonResponse
    {
        $this->authorize('view', $calendar);

        $theme = config('themes.'.$request->validated('theme'));
        $values = Arr::except($theme, ['name']);
        $month = $request->validated('month');

        DB::transaction(function () use ($calendar, $values, $month) {
            $query = $calendar->monthPages();

            if ($month !== null) {
                $query->where('month_number', $month);
            }

            $query->update($values);
        });

        $message = $month !== null
            ? 'הנושא "'.$theme['name'].'" הוחל על חודש '.$month.' בהצלחה'
            : 'הנושא "'.$theme['name'].'" הוחל על כל החודשים בהצלחה';

        return response()->json([
            'success' => true,
            'message' => $message,
            'theme' => $request->validated('theme'),
            'name' => $theme['name'],
            'month' => $month,
            'fields' => $values,
        ]);
    }
}
