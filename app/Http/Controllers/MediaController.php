<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveMediaRequest;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\Folder;
use App\Models\Media;
use App\Models\MonthPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Display the authenticated user's media library, optionally filtered by folder.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $folders = Folder::where('user_id', $user->id)->withCount('media')->get();

        $currentFolder = null;
        $allMedia = $user->getMedia('user_media')->sortByDesc('id');
        $media = $allMedia;

        if ($request->filled('folder')) {
            $currentFolder = $folders->firstWhere('id', $request->integer('folder'));

            if ($currentFolder !== null) {
                $media = $allMedia->where('folder_id', $currentFolder->id);
            }
        }

        return view('media.index', compact('media', 'allMedia', 'folders', 'currentFolder'));
    }

    /**
     * Display the dedicated media upload view.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();

        $folders = Folder::where('user_id', $user->id)->withCount('media')->get();

        return view('media.upload', compact('folders'));
    }

    /**
     * Store uploaded files in the authenticated user's media library.
     */
    public function store(StoreMediaRequest $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $folderId = $request->validated('folder_id')
            ?: ($request->query('folder') ?: null);

        if ($folderId !== null) {
            $this->authorize('view', Folder::findOrFail($folderId));
        }

        foreach ($request->file('files') as $file) {
            $media = $user->addMedia($file)->toMediaCollection('user_media');
            $media->folder_id = $folderId;
            $media->save();
        }

        $message = 'התמונות הועלו בהצלחה';

        if ($request->expectsJson()) {
            return response()->json(['success' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Rename a media item.
     */
    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse
    {
        $this->authorize('update', $media);

        $media->name = $request->validated('name');
        $media->save();

        return back()->with('success', 'השם עודכן בהצלחה');
    }

    /**
     * Move a media item to a folder, or back to the unfiled library.
     */
    public function moveToFolder(MoveMediaRequest $request, Media $media): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $media);

        $folderId = $request->validated('folder_id') ?: null;

        if ($folderId !== null) {
            $this->authorize('view', Folder::findOrFail($folderId));
        }

        $media->folder_id = $folderId;
        $media->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'התמונה הועברה בהצלחה');
    }

    /**
     * Remove a media item from the user's media library.
     */
    public function destroy(Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        MonthPage::where('background_media_id', $media->id)
            ->update(['background_media_id' => null]);

        $media->delete();

        return back()->with('success', 'התמונה נמחקה מהספרייה');
    }
}
