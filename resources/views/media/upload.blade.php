<x-app-layout>
    <div class="py-8">
        <div class="container" x-data="mediaUpload()" @keydown.escape.window="dragOver = false">
            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-volt/15 border border-volt text-ink-900 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 rounded-xl bg-danger-light border border-danger/30 text-danger text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header: back link + title --}}
            <a href="{{ route('media.index') }}"
                class="inline-flex items-center gap-1.5 mb-4 text-sm font-semibold text-ink-500 hover:text-ink-900 transition-colors">
                → חזרה לספרייה
            </a>

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-ink-950 tracking-tight">העלאת תמונות</h1>
                <p class="mt-1 text-sm text-ink-500">שלחו תמונות לספרייה שלכם, בחרו תיקיית יעד ונגמר — הנה הן יהפכו לרקעים.</p>
            </div>

            {{-- Upload card --}}
            <div class="card p-5 sm:p-6">
                {{-- Folder target picker --}}
                <div class="mb-5">
                    <p class="label mb-2">העלאה אל תיקייה</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="selectedFolder = null"
                            :class="selectedFolder === null
                                ? 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-sm font-bold border border-ink-900 bg-ink-900 text-volt transition-colors'
                                : 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-sm font-semibold border bg-white border-ink-200 text-ink-600 hover:border-ink-400 hover:text-ink-900 transition-colors'">
                            כל התמונות
                        </button>
                        @foreach ($folders as $folder)
                            <button type="button" @click="selectedFolder = {{ $folder->id }}"
                                :class="selectedFolder === {{ $folder->id }}
                                    ? 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-sm font-bold border border-ink-900 bg-ink-900 text-volt transition-colors'
                                    : 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-sm font-semibold border bg-white border-ink-200 text-ink-600 hover:border-ink-400 hover:text-ink-900 transition-colors'">
                                <span class="w-1.5 h-1.5 rounded-full {{ $folder->isLinkedToMember() ? 'bg-volt' : 'bg-ink-300' }}"></span>
                                {{ $folder->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Dropzone --}}
                <label for="mediaFiles" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                    @drop.prevent="onDrop($event)"
                    :class="dragOver
                        ? 'flex flex-col items-center justify-center gap-4 w-full border-2 border-dashed border-ink-900 bg-volt/10 rounded-2xl p-10 cursor-pointer transition-colors'
                        : 'flex flex-col items-center justify-center gap-4 w-full border-2 border-dashed border-ink-300 bg-ink-50/50 hover:border-ink-900 hover:bg-volt/5 rounded-2xl p-10 cursor-pointer transition-colors'">
                    <span
                        :class="dragOver ? 'flex items-center justify-center w-14 h-14 rounded-2xl bg-ink-900 text-volt' : 'flex items-center justify-center w-14 h-14 rounded-2xl bg-ink-100 text-ink-600'">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="text-sm text-ink-600" x-text="dragOver ? 'שחררו כדי להעלות' : 'גררו תמונות לכאן או בחרו מהמכשיר'"></span>
                    <span class="text-xs text-ink-400">JPEG · PNG · WebP · GIF · AVIF, עד 10MB לתמונה</span>
                    <span class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-ink-900 text-volt font-bold text-sm pointer-events-none">בחירת קבצים</span>
                    <input type="file" id="mediaFiles" accept="image/jpeg,image/png,image/webp,image/gif,image/avif"
                        multiple class="hidden" @change="onFilesSelected($event)">
                </label>

                {{-- Upload queue --}}
                <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" x-show="uploads.length" x-cloak>
                    <template x-for="(upload, index) in uploads" :key="index">
                        <div class="card p-2 relative">
                            <button type="button" @click="removeUpload(index)"
                                title="הסרה מהרשימה"
                                class="absolute top-1.5 left-1.5 z-10 flex items-center justify-center w-6 h-6 rounded-full bg-ink-950/70 text-white hover:bg-danger transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M6 6l12 12M18 6L6 18" />
                                </svg>
                            </button>

                            <template x-if="upload.isImage">
                                <img :src="upload.preview" alt="" class="h-24 w-full object-cover rounded-lg border border-ink-200">
                            </template>
                            <template x-if="!upload.isImage">
                                <div class="flex items-center justify-center h-24 w-full rounded-lg border border-ink-200 bg-ink-50 text-ink-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h.01M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" />
                                    </svg>
                                </div>
                            </template>

                            <div class="mt-2 flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-ink-900" x-text="upload.name"></p>
                                    <p class="text-[11px] text-ink-400" x-text="upload.size"></p>
                                </div>
                            </div>

                            <div class="mt-2">
                                <div class="h-1.5 bg-ink-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-volt rounded-full transition-all duration-150"
                                        :class="upload.status === 'error' ? 'bg-danger' : ''"
                                        :style="'width:' + upload.progress + '%'"></div>
                                </div>
                                <div class="mt-1.5 flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-medium"
                                        :class="upload.status === 'error' ? 'text-danger' : (upload.status === 'done' ? 'text-ink-900' : 'text-ink-500')"
                                        x-text="statusLabel(upload)"></span>
                                    <button type="button" x-show="upload.status === 'error'" @click="uploadFile(upload, index)"
                                        class="text-[11px] font-bold text-ink-900 hover:text-ink-950 underline">נסיון חוזר</button>
                                    <span x-show="upload.status === 'done'" class="flex items-center justify-center w-4 h-4 rounded-full bg-volt text-ink-950">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Actions --}}
                <div class="mt-6 flex flex-wrap gap-2" x-show="uploads.length" x-cloak>
                    <button type="button" @click="uploadAll()" :disabled="uploading"
                        class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950 disabled:opacity-50 disabled:pointer-events-none"
                        x-text="uploading ? 'מעלה...' : 'העלאה'"></button>
                    <button type="button" @click="clearSelection()" x-show="!uploading"
                        class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-white border border-ink-200 text-ink-900 font-bold text-sm transition-colors hover:bg-ink-100">ניקוי</button>
                </div>

                {{-- Success panel --}}
                <div x-show="successCount > 0 && !uploads.some(u => u.status === 'pending' || u.status === 'uploading')"
                    x-cloak class="mt-6 p-5 rounded-2xl bg-ink-900 text-white">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-volt text-ink-950">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-base font-bold" x-text="successCount === 1 ? 'תמונה אחת הועלתה בהצלחה' : successCount + ' תמונות הועלו בהצלחה'"></p>
                                <p class="text-xs text-white/70 mt-0.5">כל הקבצים נשמרו בספרייה שלכם.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('media.index') }}"
                                class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-volt text-ink-950 font-bold text-sm transition-colors hover:bg-volt-400">צפייה בספרייה</a>
                            <button type="button" @click="resetForNextBatch()"
                                class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl border border-white/30 text-white font-bold text-sm transition-colors hover:bg-white/10">העלאה נוספת</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mediaUpload() {
            return {
                uploads: [],
                selectedFolder: null,
                uploading: false,
                dragOver: false,
                successCount: 0,
                _files: [],
                _xhrs: [],

                onDrop(event) {
                    this.dragOver = false;
                    this.addFiles(Array.from(event.dataTransfer.files));
                },

                onFilesSelected(event) {
                    this.addFiles(Array.from(event.target.files));
                    event.target.value = '';
                },

                addFiles(files) {
                    if (!files.length) {
                        return;
                    }
                    files.forEach((file) => {
                        this.uploads.push({
                            name: file.name,
                            size: this.formatSize(file.size),
                            preview: URL.createObjectURL(file),
                            isImage: file.type.startsWith('image/'),
                            progress: 0,
                            status: 'pending',
                        });
                        this._files.push(file);
                    });
                },

                formatSize(bytes) {
                    if (bytes < 1024) {
                        return bytes + ' B';
                    }
                    if (bytes < 1024 * 1024) {
                        return Math.round(bytes / 1024) + ' KB';
                    }
                    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                },

                statusLabel(upload) {
                    if (upload.status === 'uploading') return 'מעלה...';
                    if (upload.status === 'done') return 'הושלם';
                    if (upload.status === 'error') return 'שגיאה';
                    return 'ממתין';
                },

                clearSelection() {
                    this._xhrs.forEach((xhr) => xhr && xhr.abort());
                    this._xhrs = [];
                    this.uploads = [];
                    this._files = [];
                    this.successCount = 0;
                },

                removeUpload(index) {
                    const xhr = this._xhrs[index];
                    if (xhr) {
                        xhr.abort();
                    }
                    this.uploads.splice(index, 1);
                    this._files.splice(index, 1);
                    this._xhrs.splice(index, 1);
                },

                resetForNextBatch() {
                    this.clearSelection();
                    this.selectedFolder = null;
                },

                uploadAll() {
                    if (!this._files.length || this.uploading) {
                        return;
                    }
                    this.uploading = true;
                    this.successCount = 0;
                    const tasks = this.uploads
                        .map((upload, index) => ({ upload, index }))
                        .filter(({ upload }) => upload.status !== 'done');

                    Promise.all(tasks.map(({ upload, index }) => this.uploadFile(upload, index))).then(() => {
                        this.uploading = false;
                    });
                },

                uploadFile(upload, index) {
                    return new Promise((resolve) => {
                        const formData = new FormData();
                        formData.append('files[]', this._files[index]);
                        if (this.selectedFolder !== null) {
                            formData.append('folder_id', this.selectedFolder);
                        }
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                        const xhr = new XMLHttpRequest();
                        this._xhrs[index] = xhr;
                        upload.status = 'uploading';
                        upload.progress = 0;
                        xhr.open('POST', @json(route('media.store')));
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.upload.onprogress = (event) => {
                            if (event.lengthComputable) {
                                upload.progress = Math.round((event.loaded / event.total) * 100);
                            }
                        };
                        xhr.onload = () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                upload.status = 'done';
                                upload.progress = 100;
                                this.successCount += 1;
                            } else {
                                upload.status = 'error';
                            }
                            resolve();
                        };
                        xhr.onerror = () => {
                            upload.status = 'error';
                            resolve();
                        };
                        xhr.onabort = () => {
                            resolve();
                        };
                        xhr.send(formData);
                    });
                },
            };
        }
    </script>
</x-app-layout>
