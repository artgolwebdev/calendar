<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-[#1A1A1E]">הספרייה שלי</h1>
    </x-slot>

    <div class="py-8">
        <div class="container space-y-6" x-data="mediaLibrary({ currentFolder: {{ $currentFolder?->id ?? 'null' }} })">
            @if (session('success'))
                <div class="p-4 rounded-lg bg-[#F0FDF4] border border-[#DCFCE7] text-[#15803D] text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-lg bg-[#FEF2F2] border border-[#FCA5A5] text-[#DC2626] text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 items-start">
                <aside class="card p-4 space-y-4 lg:sticky lg:top-4">
                    <form action="{{ route('folders.store') }}" method="POST" class="space-y-2">
                        @csrf
                        <label for="newFolderName" class="label">תיקייה חדשה</label>
                        <div class="flex gap-2">
                            <input type="text" name="name" id="newFolderName" maxlength="255" required
                                placeholder="שם התיקייה" class="input text-xs">
                            <button type="submit" class="btn btn-primary btn-sm shrink-0">הוספה</button>
                        </div>
                        @error('name')
                            <p class="text-xs text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </form>

                    <nav class="space-y-1">
                        <a href="{{ route('media.index') }}"
                            @dragover.prevent
                            @drop="onFolderDrop(null)"
                            class="flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 text-sm transition-colors {{ $currentFolder === null ? 'bg-[#EEF2FF] text-[#4F46E5] font-medium' : 'text-[#1A1A1E] hover:bg-[#F7F7F8]' }}">
                            <span class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 shrink-0 text-[#6B6B75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 6a2 2 0 012-2h3l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                </svg>
                                <span class="truncate">כל התמונות</span>
                            </span>
                            <span class="chip bg-[#F7F7F8] text-[#6B6B75]">{{ $allMedia->count() }}</span>
                        </a>

                        @foreach ($folders->whereNull('family_member_id') as $folder)
                            <div x-data="{ editing: false }" class="rounded-lg">
                                <template x-if="editing">
                                    <form action="{{ route('folders.update', $folder) }}" method="POST"
                                        class="flex gap-2 p-1">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $folder->name }}" maxlength="255"
                                            required class="input text-xs">
                                        <button type="submit" class="btn btn-primary btn-sm shrink-0">שמירה</button>
                                    </form>
                                </template>
                                <template x-if="!editing">
                                    <div
                                        class="flex items-center justify-between gap-1 rounded-lg px-2 py-1.5 text-sm transition-colors group
                                        {{ $currentFolder?->id === $folder->id ? 'bg-[#EEF2FF] text-[#4F46E5] font-medium' : 'text-[#1A1A1E] hover:bg-[#F7F7F8]' }}"
                                        @dragover.prevent @drop="onFolderDrop({{ $folder->id }})">
                                        <a href="{{ route('media.index', ['folder' => $folder->id]) }}"
                                            class="flex items-center gap-2 flex-1 min-w-0">
                                            <svg class="w-4 h-4 shrink-0 text-[#6B6B75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 6a2 2 0 012-2h3l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                            </svg>
                                            <span class="truncate">{{ $folder->name }}</span>
                                        </a>
                                        <span class="chip bg-[#F7F7F8] text-[#6B6B75]">{{ $folder->media_count }}</span>
                                        <div class="flex gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button" @click="editing = true"
                                                title="שינוי שם התיקייה"
                                                class="p-1 rounded text-[#6B6B75] hover:text-[#4F46E5] hover:bg-[#EEF2FF]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('folders.destroy', $folder) }}" method="POST"
                                                onsubmit="return confirm('האם למחוק את התיקייה? התמונות בתוכה לא יימחקו, רק יוחזרו לכל התמונות')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="מחיקת התיקייה"
                                                    class="p-1 rounded text-[#6B6B75] hover:text-[#DC2626] hover:bg-[#FEF2F2]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endforeach

                        @php
                            $memberFolders = $folders->whereNotNull('family_member_id');
                        @endphp

                        @if ($memberFolders->isNotEmpty())
                            <p class="pt-2 text-xs font-medium text-[#6B6B75]">תיקיות חברי משפחה</p>

                            @foreach ($memberFolders as $folder)
                                <div class="flex items-center justify-between gap-1 rounded-lg px-2 py-1.5 text-sm transition-colors
                                    {{ $currentFolder?->id === $folder->id ? 'bg-[#EEF2FF] text-[#4F46E5] font-medium' : 'text-[#1A1A1E] hover:bg-[#F7F7F8]' }}"
                                    @dragover.prevent @drop="onFolderDrop({{ $folder->id }})">
                                    <a href="{{ route('media.index', ['folder' => $folder->id]) }}"
                                        class="flex items-center gap-2 flex-1 min-w-0">
                                        <svg class="w-4 h-4 shrink-0 text-[#6B6B75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6a2 2 0 012-2h3l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                        </svg>
                                        <span class="truncate">{{ $folder->name }}</span>
                                    </a>
                                    <span class="chip bg-[#EEF2FF] text-[#4F46E5] shrink-0">חבר משפחה</span>
                                </div>
                            @endforeach
                        @endif
                    </nav>
                </aside>

                <div class="space-y-6">
                    <div class="card p-5">
                        <h2 class="text-base font-semibold text-[#1A1A1E] mb-4">העלאת תמונות</h2>

                        <label for="mediaFiles"
                            class="flex flex-col items-center justify-center gap-3 w-full border-2 border-dashed border-[#E5E5E8] rounded-lg p-8 cursor-pointer hover:border-[#4F46E5] hover:bg-[#EEF2FF]/50 transition-colors">
                            <svg class="w-8 h-8 text-[#6B6B75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm text-[#6B6B75]">לחץ לבחירת תמונות מהמכשיר (JPEG, PNG, WebP, GIF, AVIF)</span>
                            <span class="btn btn-primary pointer-events-none">בחירת קבצים</span>
                            <input type="file" id="mediaFiles" accept="image/*" multiple class="hidden" @change="onFilesSelected">
                        </label>

                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" x-show="uploads.length" x-cloak>
                            <template x-for="(upload, index) in uploads" :key="index">
                                <div class="card p-2">
                                    <img :src="upload.preview" alt="" class="h-24 w-full object-cover rounded border border-[#E5E5E8]">
                                    <div class="mt-1 truncate text-xs text-[#1A1A1E]" x-text="upload.name"></div>
                                    <div class="mt-1">
                                        <div class="h-1.5 bg-[#E5E5E8] rounded-full overflow-hidden">
                                            <div class="h-full bg-[#4F46E5] rounded-full transition-all duration-150"
                                                :style="'width:' + upload.progress + '%'"></div>
                                        </div>
                                        <div class="mt-1 flex justify-between text-[11px] text-[#6B6B75]">
                                            <span
                                                x-text="upload.status === 'uploading' ? 'מעלה...' : (upload.status === 'done' ? 'הושלם' : (upload.status === 'error' ? 'שגיאה' : 'ממתין'))"></span>
                                            <span x-text="upload.progress + '%'"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 flex gap-2" x-show="uploads.length" x-cloak>
                            <button type="button" class="btn btn-primary" @click="uploadAll()" :disabled="uploading"
                                x-text="uploading ? 'מעלה...' : 'העלאה'"></button>
                            <button type="button" class="btn btn-secondary" @click="clearSelection()" x-show="!uploading">ניקוי</button>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-[#1A1A1E] mb-4">
                            {{ $currentFolder ? $currentFolder->name : 'התמונות שלי' }} ({{ $media->count() }})
                        </h2>

                        @if ($media->isEmpty())
                            <div class="card p-10 text-center">
                                @if ($currentFolder)
                                    <p class="text-sm text-[#6B6B75]">אין עדיין תמונות בתיקייה הזו. גרור תמונות לכאן או השתמש ברשימת ההעברה.</p>
                                @else
                                    <p class="text-sm text-[#6B6B75]">אין עדיין תמונות בספרייה. העלה תמונות כדי להשתמש בהן כרקע לחודשים.</p>
                                @endif
                            </div>
                        @else
                            @if ($folders->isNotEmpty())
                                <p class="mb-3 text-xs text-[#6B6B75]">ניתן לגרור תמונה לתיקייה בסרגל הצד או לבחור תיקייה מרשימת ההעברה.</p>
                            @endif

                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach ($media as $item)
                                    <div class="card overflow-hidden flex flex-col group">
                                        <div draggable="true"
                                            @dragstart="onMediaDragStart($event, '{{ route('media.move', $item) }}')"
                                            @dragend="onMediaDragEnd()"
                                            class="relative block cursor-grab active:cursor-grabbing"
                                            title="גרור לתיקייה">
                                            <img src="{{ $item->getUrl('thumb') }}" alt="{{ $item->name }}"
                                                class="h-32 w-full object-cover">
                                            <a href="{{ $item->getUrl() }}" target="_blank"
                                                class="absolute inset-0" title="פתיחה בגודל מלא"></a>
                                            <span
                                                class="absolute top-2 left-2 chip bg-black/60 text-white opacity-0 group-hover:opacity-100 transition-opacity">גרור לתיקייה</span>
                                        </div>
                                        <div class="p-3 flex-1 flex flex-col gap-2">
                                            @if ($folders->isNotEmpty())
                                                <form action="{{ route('media.move', $item) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <label for="moveFolder{{ $item->id }}" class="sr-only">העברה לתיקייה</label>
                                                    <select name="folder_id" id="moveFolder{{ $item->id }}"
                                                        class="input text-xs" onchange="this.form.submit()">
                                                        <option value="" @selected($item->folder_id === null)>ללא תיקייה</option>
                                                        @foreach ($folders as $folder)
                                                            <option value="{{ $folder->id }}"
                                                                @selected($item->folder_id === $folder->id)>
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
                                                <input type="text" name="name" id="mediaName{{ $item->id }}"
                                                    value="{{ $item->name }}" maxlength="255" class="input text-xs">
                                                <button type="submit" class="btn btn-secondary btn-sm w-full mt-1">שמירת שם</button>
                                            </form>
                                            <form action="{{ route('media.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('האם למחוק את התמונה מהספרייה?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-destructive btn-sm w-full">מחיקה</button>
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
    </div>
</x-app-layout>

<script>
    function mediaLibrary(config = {}) {
        return {
            currentFolder: config.currentFolder ?? null,
            uploads: [],
            uploading: false,
            draggingUrl: null,
            onFilesSelected(event) {
                const files = Array.from(event.target.files);
                this.uploads = files.map((file) => ({
                    name: file.name,
                    preview: URL.createObjectURL(file),
                    progress: 0,
                    status: 'pending',
                }));
                this._files = files;
            },
            clearSelection() {
                this.uploads = [];
                this._files = [];
                document.getElementById('mediaFiles').value = '';
            },
            uploadAll() {
                if (!this._files || !this._files.length) {
                    return;
                }

                this.uploading = true;
                const promises = this._files.map((file, index) => this.uploadFile(file, index));

                Promise.all(promises).then(() => {
                    window.location.reload();
                });
            },
            uploadFile(file, index) {
                return new Promise((resolve) => {
                    const upload = this.uploads[index];
                    const formData = new FormData();
                    formData.append('files[]', file);
                    if (this.currentFolder !== null) {
                        formData.append('folder_id', this.currentFolder);
                    }
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    const xhr = new XMLHttpRequest();
                    upload.status = 'uploading';
                    xhr.open('POST', @json(route('media.store')));
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.upload.onprogress = (event) => {
                        if (event.lengthComputable) {
                            upload.progress = Math.round((event.loaded / event.total) * 100);
                        }
                    };
                    xhr.onload = () => {
                        upload.status = xhr.status >= 200 && xhr.status < 300 ? 'done' : 'error';
                        upload.progress = upload.status === 'done' ? 100 : upload.progress;
                        resolve();
                    };
                    xhr.onerror = () => {
                        upload.status = 'error';
                        resolve();
                    };
                    xhr.send(formData);
                });
            },
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
