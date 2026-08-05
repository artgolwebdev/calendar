<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    /**
     * Store a newly created folder for the authenticated user.
     */
    public function store(StoreFolderRequest $request): RedirectResponse
    {
        Auth::user()->folders()->create($request->validated());

        return back()->with('success', 'התיקייה נוצרה בהצלחה');
    }

    /**
     * Update a folder's name.
     */
    public function update(UpdateFolderRequest $request, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        if ($folder->isLinkedToMember()) {
            return back()->with('error', 'תיקיית חבר משפחה מתעדכנת אוטומטית לפי שם החבר');
        }

        $folder->update(['name' => $request->validated('name')]);

        return back()->with('success', 'התיקייה עודכנה בהצלחה');
    }

    /**
     * Remove a folder, leaving the contained media unfiled.
     */
    public function destroy(Folder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        if ($folder->isLinkedToMember()) {
            return back()->with('error', 'לא ניתן למחוק תיקיית חבר משפחה');
        }

        $folder->delete();

        return redirect()->route('media.index')->with('success', 'התיקייה נמחקה והתמונות חזרו ל"כל התמונות"');
    }
}
