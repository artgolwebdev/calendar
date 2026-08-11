<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyMemberRequest;
use App\Http\Requests\UpdateFamilyMemberRequest;
use App\Models\Calendar;
use App\Models\FamilyMember;
use App\Services\FamilyMemberMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FamilyMemberController extends Controller
{
    public function __construct(protected FamilyMemberMediaService $familyMemberMediaService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Calendar $calendar): View
    {
        $this->authorize('update', $calendar);

        $familyMembers = $calendar->familyMembers()
            ->with(['folder' => fn ($query) => $query->withCount('media')])
            ->orderBy('name')
            ->get();

        return view('family-members.index', compact('calendar', 'familyMembers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Calendar $calendar): View
    {
        $this->authorize('update', $calendar);

        return view('family-members.form', [
            'calendar' => $calendar,
            'familyMember' => null,
            'folder' => null,
            'media' => collect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFamilyMemberRequest $request, Calendar $calendar): RedirectResponse
    {
        $this->authorize('update', $calendar);

        $member = $calendar->familyMembers()->create($request->safe()->except(['images']));

        $this->familyMemberMediaService->storeImages($member, $request->file('images', []));

        return redirect()->route('calendars.edit', $calendar)
            ->with('success', 'חבר המשפחה נוסף בהצלחה');
    }

    /**
     * Display the specified resource. The member page is a single editable
     * view holding both the details form and the image gallery.
     */
    public function show(Calendar $calendar, FamilyMember $familyMember): View
    {
        $this->authorize('view', $familyMember);

        return $this->memberPage($calendar, $familyMember);
    }

    /**
     * Show the form for editing the specified resource. Renders the same
     * combined member page used for viewing.
     */
    public function edit(Calendar $calendar, FamilyMember $familyMember): View
    {
        $this->authorize('update', $familyMember);

        return $this->memberPage($calendar, $familyMember);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFamilyMemberRequest $request, Calendar $calendar, FamilyMember $familyMember): RedirectResponse
    {
        $this->authorize('update', $familyMember);

        $familyMember->update($request->safe()->except(['images']));

        $this->familyMemberMediaService->storeImages($familyMember, $request->file('images', []));

        return redirect()->route('calendars.edit', $calendar)
            ->with('success', 'השינויים נשמרו בהצלחה');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Calendar $calendar, FamilyMember $familyMember): RedirectResponse
    {
        $this->authorize('delete', $familyMember);
        $familyMember->delete();

        return redirect()->route('calendars.edit', $calendar)
            ->with('success', 'חבר המשפחה נמחק בהצלחה');
    }

    /**
     * Render the combined member page (details form + gallery + events).
     */
    private function memberPage(Calendar $calendar, FamilyMember $familyMember): View
    {
        $folder = $familyMember->folder()->withCount('media')->first();
        $media = $folder?->media()->orderByDesc('id')->get() ?? collect();

        return view('family-members.form', compact('calendar', 'familyMember', 'folder', 'media'));
    }
}
