<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMonthPageRequest;
use App\Models\Calendar;
use App\Models\Media;
use App\Models\MonthPage;
use Illuminate\Support\Facades\Storage;

class MonthPageController extends Controller
{
    /**
     * Update the specified month page.
     */
    public function update(UpdateMonthPageRequest $request, Calendar $calendar, MonthPage $monthPage)
    {
        $this->authorize('view', $calendar);

        $data = $request->safe()->except(['custom_image_path', 'background_media_id']);

        if ($request->hasFile('custom_image_path')) {
            if ($monthPage->custom_image_path) {
                Storage::disk('public')->delete($monthPage->custom_image_path);
            }

            $data['custom_image_path'] = $request->file('custom_image_path')->store('month-pages', 'public');
            $data['background_media_id'] = null;
        } elseif ($request->filled('background_media_id')) {
            $media = Media::findOrFail($request->input('background_media_id'));
            $this->authorize('view', $media);

            $data['background_media_id'] = $media->id;
        } elseif ($request->has('background_media_id')) {
            $data['background_media_id'] = null;
        }

        $monthPage->update($data);

        return back()->with('success', 'הגדרות החודש עודכנו בהצלחה');
    }

    /**
     * Remove custom image from month page.
     */
    public function removeImage(Calendar $calendar, MonthPage $monthPage)
    {
        $this->authorize('view', $calendar);

        if ($monthPage->custom_image_path) {
            Storage::disk('public')->delete($monthPage->custom_image_path);
            $monthPage->update(['custom_image_path' => null]);
        }

        return redirect()->route('calendars.month', [$calendar, $monthPage->month_number, now()->year])
            ->with('success', 'התמונה נמחקה בהצלחה');
    }
}
