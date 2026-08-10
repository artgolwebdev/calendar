<x-app-layout>
    <div class="py-8">
        <div class="container" x-data="mediaLibrary()">
            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error mb-4">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header: title + upload CTA --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-ink-950 tracking-tight">הספרייה שלי</h1>
                    <p class="mt-1 text-sm text-ink-500">
                        @if ($currentFolder)
                            מציגים את התיקייה <span class="font-semibold text-ink-900">{{ $currentFolder->name }}</span>
                        @else
                            כל התמונות בספרייה שלכם — בחרו תיקייה או העלו תמונות חדשות
                        @endif
                    </p>
                </div>
                <a href="{{ route('media.create') }}"
                    class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14m-7-7h14" />
                    </svg>
                    העלאת תמונות
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6 items-start">
                {{-- Folder sidebar --}}
                <aside class="card p-4 space-y-4 lg:sticky lg:top-24">
                    <form action="{{ route('folders.store') }}" method="POST" class="space-y-2">
                        @csrf
                        <label for="newFolderName" class="label">תיקייה חדשה</label>
                        <div class="flex gap-2">
                            <input type="text" name="name" id="newFolderName" maxlength="255" required
                                placeholder="שם התיקייה"
                                class="w-full h-10 px-3 rounded-lg border border-ink-200 text-sm text-ink-900 bg-white placeholder:text-ink-400 focus:outline-none focus:border-ink-900 focus:ring-2 focus:ring-volt/50 transition-colors">
                            <button type="submit"
                                class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-ink-900 text-volt text-sm font-bold shrink-0 transition-colors hover:bg-ink-800">הוספה</button>
                        </div>
                        @error('name')
                            <p class="text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </form>

                    <nav class="space-y-1">
                        {{-- All media --}}
                        <div x-data="{ over: false }" @dragenter="over = true" @dragleave="over = false"
                            @drop.prevent="over = false; onFolderDrop(null)">
                            <a href="{{ route('media.index') }}"
                                :class="over ? 'ring-2 ring-volt' : ''"
                                class="flex items-center justify-between gap-2 rounded-lg px-2 py-2 text-sm transition-colors {{ $currentFolder === null ? 'bg-ink-100 text-ink-900 font-semibold' : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900' }}">
                                <span class="flex items-center gap-2 min-w-0">
                                    <span class="shrink-0 {{ $currentFolder === null ? 'text-ink-900' : 'text-ink-400' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h3l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                        </svg>
                                    </span>
                                    <span class="truncate">כל התמונות</span>
                                </span>
                                <span class="chip bg-ink-100 text-ink-500">{{ $allMedia->count() }}</span>
                            </a>
                        </div>

                        {{-- Manual folders --}}
                        @foreach ($folders->whereNull('calendar_id') as $folder)
                            <div x-data="{ editing: false, over: false }" class="rounded-lg">
                                <template x-if="editing">
                                    <form action="{{ route('folders.update', $folder) }}" method="POST" class="flex gap-2 p-1">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $folder->name }}" maxlength="255" required
                                            class="w-full h-9 px-2.5 rounded-lg border border-ink-200 text-sm text-ink-900 bg-white focus:outline-none focus:border-ink-900 focus:ring-2 focus:ring-volt/50 transition-colors">
                                        <button type="submit" class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-ink-900 text-volt text-xs font-bold shrink-0">שמירה</button>
                                    </form>
                                </template>
                                <template x-if="!editing">
                                    <div
                                        @dragenter="over = true" @dragleave="over = false"
                                        @drop.prevent="over = false; onFolderDrop({{ $folder->id }})"
                                        :class="over ? 'ring-2 ring-volt' : ''"
                                        class="flex items-center justify-between gap-1 rounded-lg px-2 py-2 text-sm transition-colors group
                                        {{ $currentFolder?->id === $folder->id ? 'bg-ink-100 text-ink-900 font-semibold' : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900' }}">
                                        <a href="{{ route('media.index', ['folder' => $folder->id]) }}"
                                            class="flex items-center gap-2 flex-1 min-w-0">
                                            <span class="shrink-0 {{ $currentFolder?->id === $folder->id ? 'text-ink-900' : 'text-ink-400' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h3l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                                </svg>
                                            </span>
                                            <span class="truncate">{{ $folder->name }}</span>
                                        </a>
                                        <span class="chip bg-ink-100 text-ink-500">{{ $folder->media_count }}</span>
                                        <div class="flex gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button" @click="editing = true" title="שינוי שם התיקייה"
                                                class="p-1 rounded text-ink-400 hover:text-ink-900 hover:bg-ink-100">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('folders.destroy', $folder) }}" method="POST"
                                                onsubmit="return confirm('האם למחוק את התיקייה? התמונות בתוכה לא יימחקו, רק יוחזרו לכל התמונות')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="מחיקת התיקייה"
                                                    class="p-1 rounded text-ink-400 hover:text-danger hover:bg-danger-light">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endforeach

                        @php
                            $calendarRoots = $folders->filter(fn ($folder) => $folder->isCalendarRoot());
                        @endphp

                        @foreach ($calendarRoots as $root)
                            @php
                                $memberFolders = $folders->where('parent_id', $root->id);
                            @endphp
                            <div class="pt-3">
                                <p class="pb-1 text-xs font-bold text-ink-400 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $root->name }}
                                </p>

                                @foreach ($memberFolders as $folder)
                                    <div x-data="{ over: false }" @dragenter="over = true" @dragleave="over = false"
                                        @drop.prevent="over = false; onFolderDrop({{ $folder->id }})"
                                        :class="over ? 'ring-2 ring-volt' : ''"
                                        class="ms-3 flex items-center justify-between gap-1 rounded-lg px-2 py-2 text-sm transition-colors
                                        {{ $currentFolder?->id === $folder->id ? 'bg-ink-100 text-ink-900 font-semibold' : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900' }}">
                                        <a href="{{ route('media.index', ['folder' => $folder->id]) }}"
                                            class="flex items-center gap-2 flex-1 min-w-0">
                                            <span class="shrink-0 {{ $currentFolder?->id === $folder->id ? 'text-ink-900' : 'text-ink-400' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h3l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                                </svg>
                                            </span>
                                            <span class="truncate">{{ $folder->name }}</span>
                                        </a>
                                        <span class="chip bg-volt/20 text-ink-900 shrink-0">חבר משפחה</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </nav>
                </aside>

                {{-- Media grid --}}
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <h2 class="text-base font-bold text-ink-950">
                            {{ $currentFolder ? $currentFolder->name : 'התמונות שלי' }} ({{ $media->count() }})
                        </h2>
                        @if ($folders->isNotEmpty() && $media->isNotEmpty())
                            <p class="text-xs text-ink-400">גררו תמונה לתיקייה בסרגל הצד או בחרו תיקייה מהרשימה</p>
                        @endif
                    </div>

                    @if ($media->isEmpty())
                        <div class="card p-12 text-center">
                            @if ($currentFolder)
                                <p class="text-sm text-ink-500">אין עדיין תמונות בתיקייה הזו.</p>
                            @else
                                <p class="text-sm text-ink-500">אין עדיין תמונות בספרייה. העלו תמונות כדי להשתמש בהן כרקע לחודשים.</p>
                                <a href="{{ route('media.create') }}"
                                    class="inline-flex items-center justify-center gap-2 h-11 px-6 mt-5 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800">
                                    העלאת תמונות
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach ($media as $item)
                                <div class="card overflow-hidden flex flex-col group">
                                    <div draggable="true"
                                        @dragstart="onMediaDragStart($event, '{{ route('media.move', $item) }}')"
                                        @dragend="onMediaDragEnd()"
                                        class="relative block cursor-grab active:cursor-grabbing"
                                        title="גרור לתיקייה">
                                        <img src="{{ $item->getUrl('thumb') }}" alt="{{ $item->name }}"
                                            class="h-40 w-full object-cover">
                                        <a href="{{ $item->getUrl() }}" target="_blank"
                                            class="absolute inset-0" title="פתיחה בגודל מלא"></a>
                                        <span
                                            class="absolute top-2 left-2 chip bg-ink-950/70 text-white opacity-0 group-hover:opacity-100 transition-opacity">גרור לתיקייה</span>
                                    </div>
                                    <div class="p-3 flex-1 flex flex-col gap-2">
                                        <p class="truncate text-xs font-semibold text-ink-900" title="{{ $item->name }}">{{ $item->name }}</p>

                                        @if ($folders->isNotEmpty())
                                            <form action="{{ route('media.move', $item) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <label for="moveFolder{{ $item->id }}" class="sr-only">העברה לתיקייה</label>
                                                <select name="folder_id" id="moveFolder{{ $item->id }}"
                                                    class="w-full h-9 px-2.5 rounded-lg border border-ink-200 text-xs text-ink-900 bg-white focus:outline-none focus:border-ink-900 focus:ring-2 focus:ring-volt/50 transition-colors"
                                                    onchange="this.form.submit()">
                                                    <option value="" @selected($item->folder_id === null)>ללא תיקייה</option>
                                                    @foreach ($folders as $folder)
                                                        <option value="{{ $folder->id }}" @selected($item->folder_id === $folder->id)>
                                                            {{ $folder->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @endif

                                        <form action="{{ route('media.update', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <label for="mediaName{{ $item->id }}" class="sr-only">שם התמונה</label>
                                            <div class="flex gap-1.5">
                                                <input type="text" name="name" id="mediaName{{ $item->id }}"
                                                    value="{{ $item->name }}" maxlength="255"
                                                    class="w-full h-9 px-2.5 rounded-lg border border-ink-200 text-xs text-ink-900 bg-white focus:outline-none focus:border-ink-900 focus:ring-2 focus:ring-volt/50 transition-colors">
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-ink-100 text-ink-900 text-xs font-bold shrink-0 transition-colors hover:bg-ink-900 hover:text-volt">שמירה</button>
                                            </div>
                                        </form>

                                        <form action="{{ route('media.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('האם למחוק את התמונה מהספרייה?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center gap-1.5 w-full h-9 rounded-lg border border-danger/30 text-danger text-xs font-bold transition-colors hover:bg-danger-light">מחיקה</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function mediaLibrary() {
            return {
                draggingUrl: null,

                onMediaDragStart(event, url) {
                    this.draggingUrl = url;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', url);
                },

                onMediaDragEnd() {
                    this.draggingUrl = null;
                },

                onFolderDrop(folderId) {
                    if (this.draggingUrl === null) {
                        return;
                    }

                    this.moveMedia(this.draggingUrl, folderId);
                    this.draggingUrl = null;
                },

                moveMedia(url, folderId) {
                    const formData = new FormData();
                    formData.append('folder_id', folderId === null ? '' : folderId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    fetch(url, {
                        method: 'PUT',
                        body: formData,
                        headers: { 'Accept': 'application/json' },
                    }).then((response) => {
                        if (response.ok) {
                            window.location.reload();
                        }
                    });
                },
            };
        }
    </script>
</x-app-layout>
