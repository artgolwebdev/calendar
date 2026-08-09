<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyMemberRequest;
use App\Http\Requests\UpdateFamilyMemberRequest;
use App\Models\FamilyMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FamilyMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $familyMembers = Auth::user()->familyMembers()
            ->with(['folder' => fn ($query) => $query->withCount('media')])
            ->get();

        return view('family-members.index', compact('familyMembers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('family-members.form', [
            'familyMember' => null,
            'folder' => null,
            'media' => collect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFamilyMemberRequest $request): RedirectResponse
    {
        $member = Auth::user()->familyMembers()->create($request->safe()->except(['images']));

        $this->storeImages($member, $request->file('images', []));

        return redirect()->route('family-members.show', $member)
            ->with('success', 'חבר המשפחה נוסף בהצלחה');
    }

    /**
     * Display the specified resource. The member page is a single editable
     * view holding both the details form and the image gallery.
     */
    public function show(FamilyMember $familyMember): View
    {
        $this->authorize('view', $familyMember);

        return $this->memberPage($familyMember);
    }

    /**
     * Show the form for editing the specified resource. Renders the same
     * combined member page used for viewing.
     */
    public function edit(FamilyMember $familyMember): View
    {
        $this->authorize('update', $familyMember);

        return $this->memberPage($familyMember);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFamilyMemberRequest $request, FamilyMember $familyMember): RedirectResponse
    {
        $this->authorize('update', $familyMember);

        $familyMember->update($request->safe()->except(['images']));

        $this->storeImages($familyMember, $request->file('images', []));

        return redirect()->route('family-members.show', $familyMember)
            ->with('success', 'השינויים נשמרו בהצלחה');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FamilyMember $familyMember): RedirectResponse
    {
        $this->authorize('delete', $familyMember);
        $familyMember->delete();

        return redirect()->route('family-members.index')
            ->with('success', 'חבר המשפחה נמחק בהצלחה');
    }

    /**
     * Render the combined member page (details form + gallery + events).
     */
    private function memberPage(FamilyMember $familyMember): View
    {
        $folder = $familyMember->folder()->withCount('media')->first();
        $media = $folder?->media()->orderByDesc('id')->get() ?? collect();

        return view('family-members.form', compact('familyMember', 'folder', 'media'));
    }

    /**
     * Persist uploaded images into the member's linked media folder.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function storeImages(FamilyMember $member, array $files): void
    {
        $user = $member->user;
        $folderId = $member->folder()->first()?->id;

        foreach ($files as $file) {
            $media = $user->addMedia($file)->toMediaCollection('user_media');
            $media->folder_id = $folderId;
            $media->save();
        }
    }
}
