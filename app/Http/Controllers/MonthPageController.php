<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMonthPageRequest;
use App\Models\Calendar;
use App\Models\MonthPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonthPageController extends Controller
{
    /**
     * Update the specified month page
     */
    public function update(UpdateMonthPageRequest $request, Calendar $calendar, MonthPage $monthPage)
    {
        $this->authorize('view', $calendar);

        try {
            $data = $request->validated();
            
            // Start with empty update array - only update what's explicitly provided
            $updateData = [];

            // Handle custom image upload ONLY if a new file is provided
            if ($request->hasFile('custom_image_path')) {
                // Delete old image if exists
                if ($monthPage->custom_image_path) {
                    Storage::disk('public')->delete($monthPage->custom_image_path);
                }

                $path = $request->file('custom_image_path')->store('month-pages', 'public');
                $updateData['custom_image_path'] = $path;
            }
            // IMPORTANT: If no file uploaded, do NOT touch custom_image_path at all

            // Update other fields only if they're present in the request
            if (isset($data['font_choice'])) {
                $updateData['font_choice'] = $data['font_choice'];
            }
            if (isset($data['overlay_opacity'])) {
                $updateData['overlay_opacity'] = (int) $data['overlay_opacity'];
            }
            if (isset($data['day_box_bg_color'])) {
                $updateData['day_box_bg_color'] = $data['day_box_bg_color'];
            }
            if (isset($data['day_box_font_color'])) {
                $updateData['day_box_font_color'] = $data['day_box_font_color'];
            }
            if (isset($data['day_box_bg_opacity'])) {
                $updateData['day_box_bg_opacity'] = (int) $data['day_box_bg_opacity'];
            }
            
            // Handle checkbox explicitly - if it's present in request, use its value; if not, set to false
            if ($request->has('show_adjacent_month_days')) {
                $updateData['show_adjacent_month_days'] = (bool) $request->input('show_adjacent_month_days');
            } else {
                $updateData['show_adjacent_month_days'] = false;
            }

            // Only update if there are actual changes
            if (!empty($updateData)) {
                $monthPage->update($updateData);
            }

            return redirect()->back()
                ->with('success', 'הגדרות החודש עודכנו בהצלחה');
        } catch (\Exception $e) {
            \Log::error('MonthPage update error: ' . $e->getMessage());
            return back()->with('error', 'שגיאה בעדכון הגדרות: ' . $e->getMessage());
        }
    }

    /**
     * Remove custom image from month page
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
