<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarWizardRequest;
use App\Models\FamilyMember;
use App\Services\CalendarCreationService;
use App\Services\FamilyMemberMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CalendarWizardController extends Controller
{
    public function __construct(
        protected CalendarCreationService $calendarCreationService,
        protected FamilyMemberMediaService $familyMemberMediaService
    ) {}

    /**
     * Show the multi-step calendar creation wizard.
     */
    public function create(): View
    {
        return view('calendars.wizard');
    }

    /**
     * Create the calendar (and any scoped family members) from the wizard.
     */
    public function store(StoreCalendarWizardRequest $request): JsonResponse
    {
        $calendar = DB::transaction(function () use ($request) {
            $calendar = $this->calendarCreationService->create(
                Auth::user(),
                $request->safe()->except(['cover_image_path', 'members']),
                $request->file('cover_image_path')
            );

            foreach ($request->validated('members', []) as $index => $memberData) {
                $member = $calendar->familyMembers()->create([
                    'name' => $memberData['name'],
                    'birth_date' => $memberData['birth_date'],
                    'hobbies' => $memberData['hobbies'] ?? [],
                    'favorite_sports' => $memberData['favorite_sports'] ?? [],
                    'favorite_music' => $memberData['favorite_music'] ?? [],
                    'favorite_food' => $memberData['favorite_food'] ?? [],
                ]);

                if ($image = $request->file("members.{$index}.image")) {
                    $this->storeMemberImage($member, $image);
                }
            }

            return $calendar;
        });

        return response()->json([
            'redirect' => route('dashboard'),
            'success' => 'לוח השנה נוצר בהצלחה',
            'calendar_id' => $calendar->id,
            'members' => $calendar->familyMembers()->count(),
        ]);
    }

    /**
     * Attach a single wizard photo to a member's linked media folder.
     */
    protected function storeMemberImage(FamilyMember $member, UploadedFile $image): void
    {
        $this->familyMemberMediaService->storeImages($member, [$image]);
    }
}
