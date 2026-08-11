<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarWizardRequest;
use App\Models\FamilyMember;
use App\Models\MonthPage;
use App\Services\CalendarCreationService;
use App\Services\FamilyMemberMediaService;
use App\Services\HebrewDateService;
use App\Services\IsraeliHolidaysService;
use App\Services\MonthPageStyleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CalendarWizardController extends Controller
{
    public function __construct(
        protected CalendarCreationService $calendarCreationService,
        protected FamilyMemberMediaService $familyMemberMediaService,
        protected MonthPageStyleService $monthPageStyleService
    ) {}

    /**
     * Show the multi-step calendar creation wizard.
     */
    public function create(): View
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        $monthNames = [
            1 => 'ינואר', 2 => 'פברואר', 3 => 'מרץ', 4 => 'אפריל',
            5 => 'מאי', 6 => 'יוני', 7 => 'יולי', 8 => 'אוגוסט',
            9 => 'ספטמבר', 10 => 'אוקטובר', 11 => 'נובמבר', 12 => 'דצמבר',
        ];

        $hebrewDateService = app(HebrewDateService::class);
        $hebrewMonthName = $hebrewDateService->hebrewMonthName($now->copy()->startOfMonth());
        $hebrewYear = $hebrewDateService->toHebrewArray($now->copy()->startOfMonth())['year'];

        $holidaysByDate = [];
        foreach (app(IsraeliHolidaysService::class)->getHolidaysForMonth($year, $month) as $holiday) {
            $dateKey = Carbon::parse($holiday['date'])->format('Y-m-d');
            $holidaysByDate[$dateKey][] = $holiday;
        }

        $styles = $this->monthPageStyleService->resolve(new MonthPage($this->monthPageStyleService->defaults()));

        return view('calendars.wizard', compact(
            'year',
            'month',
            'monthNames',
            'hebrewMonthName',
            'hebrewYear',
            'holidaysByDate',
            'styles'
        ));
    }

    /**
     * Create the calendar (and any scoped family members, manual events) from
     * the wizard. Members are persisted first so the FamilyMemberObserver can
     * auto-generate their recurring events, then manual events are created
     * with the member ids resolved from the temporary client keys.
     */
    public function store(StoreCalendarWizardRequest $request): JsonResponse
    {
        $calendar = DB::transaction(function () use ($request) {
            $calendar = $this->calendarCreationService->create(
                Auth::user(),
                $request->safe()->except(['cover_image_path', 'members', 'events']),
                $request->file('cover_image_path')
            );

            $memberIdsByKey = [];

            foreach ($request->validated('members', []) as $index => $memberData) {
                $member = $calendar->familyMembers()->create([
                    'name' => $memberData['name'],
                    'birth_date' => $memberData['birth_date'],
                    'anniversary_date' => $memberData['anniversary_date'] ?? null,
                    'hobbies' => $memberData['hobbies'] ?? [],
                    'favorite_sports' => $memberData['favorite_sports'] ?? [],
                    'favorite_music' => $memberData['favorite_music'] ?? [],
                    'favorite_food' => $memberData['favorite_food'] ?? [],
                ]);

                $memberIdsByKey[$memberData['key'] ?? $index] = $member->id;

                if ($image = $request->file("members.{$index}.image")) {
                    $this->storeMemberImage($member, $image);
                }
            }

            foreach ($request->validated('events', []) as $index => $eventData) {
                $data = [
                    'title' => $eventData['title'],
                    'description' => $eventData['description'] ?? null,
                    'event_date' => $eventData['event_date'],
                    'event_type' => 'custom',
                    'start_time' => $eventData['start_time'] ?? null,
                    'end_time' => $eventData['end_time'] ?? null,
                    'is_auto_generated' => false,
                    'title_customized' => false,
                    'family_member_id' => $eventData['family_member_key'] ?? null
                        ? $memberIdsByKey[$eventData['family_member_key']] ?? null
                        : null,
                ];

                if ($cover = $request->file("events.{$index}.cover_image_path")) {
                    $data['cover_image_path'] = $cover->store('calendar-covers', 'public');
                }

                $calendar->calendarEvents()->create($data);
            }

            if ($themeKey = $request->validated('theme')) {
                $calendar->monthPages()->update(Arr::except(config("themes.{$themeKey}"), ['name']));
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
