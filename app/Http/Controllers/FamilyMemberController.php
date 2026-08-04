<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyMemberRequest;
use App\Http\Requests\UpdateFamilyMemberRequest;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $familyMembers = Auth::user()->familyMembers;
        return view('family-members.index', compact('familyMembers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('family-members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFamilyMemberRequest $request)
    {
        Auth::user()->familyMembers()->create($request->validated());

        return redirect()->route('family-members.index')
            ->with('success', 'חבר משפחה נוסף בהצלחה');
    }

    /**
     * Display the specified resource.
     */
    public function show(FamilyMember $familyMember)
    {
        $this->authorize('view', $familyMember);
        return view('family-members.show', compact('familyMember'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FamilyMember $familyMember)
    {
        $this->authorize('update', $familyMember);
        return view('family-members.edit', compact('familyMember'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFamilyMemberRequest $request, FamilyMember $familyMember)
    {
        $this->authorize('update', $familyMember);
        $familyMember->update($request->validated());

        return redirect()->route('family-members.index')
            ->with('success', 'חבר המשפחה עודכן בהצלחה');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FamilyMember $familyMember)
    {
        $this->authorize('delete', $familyMember);
        $familyMember->delete();

        return redirect()->route('family-members.index')
            ->with('success', 'חבר המשפחה נמחק בהצלחה');
    }
}
