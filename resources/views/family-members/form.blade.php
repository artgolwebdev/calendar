<x-app-layout>
    @php
        $isCreate = $familyMember === null;
    @endphp

    <div class="py-8">
        <div class="container">
            {{-- Header: back link --}}
            <a href="{{ route('family-members.index') }}"
                class="inline-flex items-center gap-1.5 mb-4 text-sm font-semibold text-ink-500 hover:text-ink-900 transition-colors">
                → חזרה לחברי המשפחה
            </a>

            {{-- Header: title + actions --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-ink-950 tracking-tight">
                        {{ $isCreate ? 'הוסף חבר משפחה חדש' : $familyMember->name }}
                    </h1>
                    <p class="mt-1 text-sm text-ink-500">
                        {{ $isCreate
                            ? 'הזינו את הפרטים, צרפו תמונות ונתחיל לבנות את האירועים המשפחתיים'
                            : 'עריכת פרטים, אירועים והתמונות של ' . $familyMember->name }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" form="member-form"
                        class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $isCreate ? 'הוסף חבר משפחה' : 'שמירת שינויים' }}
                    </button>
                    @if (! $isCreate)
                        <form action="{{ route('family-members.destroy', $familyMember) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-white border border-danger/40 text-danger font-bold text-sm transition-colors hover:bg-danger-light"
                                onclick="return confirm('האם אתה בטוח שברצונך למחוק את חבר המשפחה?')">
                                מחק
                            </button>
                        </form>
                    @endif
                </div>
            </div>

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

            <div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-6 items-start">
                {{-- Details sidebar --}}
                <aside class="lg:sticky lg:top-24">
                    <form id="member-form" method="POST"
                        action="{{ $isCreate ? route('family-members.store') : route('family-members.update', $familyMember) }}"
                        enctype="multipart/form-data" class="card p-6 space-y-4">
                        @csrf
                        @if (! $isCreate)
                            @method('PUT')
                        @endif

                        @if (! $isCreate && $media->isNotEmpty())
                            <img src="{{ $media->first()->getUrl('thumb') }}" alt=""
                                class="h-36 w-full object-cover rounded-2xl">
                        @else
                            <div class="h-36 w-full rounded-2xl bg-ink-100 flex items-center justify-center">
                                <span class="text-5xl font-bold text-ink-300">
                                    {{ $isCreate ? '?' : mb_substr($familyMember->name, 0, 1) }}
                                </span>
                            </div>
                        @endif

                        <div>
                            <label for="name" class="label">שם מלא</label>
                            <input type="text" name="name" id="name" required
                                class="input"
                                placeholder="שם חבר המשפחה"
                                value="{{ old('name', $isCreate ? '' : $familyMember->name) }}">
                        </div>

                        <div>
                            <label for="birth_date" class="label">תאריך לידה</label>
                            <input type="date" name="birth_date" id="birth_date" required
                                class="input"
                                value="{{ old('birth_date', $isCreate ? '' : $familyMember->birth_date->format('Y-m-d')) }}">
                        </div>

                        <div>
                            <label for="anniversary_date" class="label">תאריך נישואין <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                            <input type="date" name="anniversary_date" id="anniversary_date"
                                class="input"
                                value="{{ old('anniversary_date', $isCreate ? '' : ($familyMember->anniversary_date ? $familyMember->anniversary_date->format('Y-m-d') : '')) }}">
                        </div>

                        <div>
                            <label for="notes" class="label">הערות <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                            <textarea name="notes" id="notes" rows="3"
                                class="input"
                                placeholder="הערות נוספות על חבר המשפחה">{{ old('notes', $isCreate ? '' : $familyMember->notes) }}</textarea>
                        </div>

                        @if ($errors->any())
                            <div class="p-4 rounded-xl bg-danger-light border border-danger/30 text-sm text-danger">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950">
                            {{ $isCreate ? 'הוסף חבר משפחה' : 'שמירת שינויים' }}
                        </button>
                    </form>
                </aside>

                {{-- Main column --}}
                <div class="space-y-6">
                    {{-- Gallery --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-5 pb-3 border-b border-ink-200">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-ink-950">תמונות</h3>
                                @if (! $isCreate)
                                    <span class="chip bg-volt/20 text-ink-900">{{ $media->count() }}</span>
                                @endif
                            </div>
                            @if (! $isCreate && $folder)
                                <a href="{{ route('media.index', ['folder' => $folder->id]) }}"
                                    class="text-xs font-bold text-ink-900 hover:text-ink-950 hover:underline transition-colors">
                                    פתיחת התיקייה בספרייה
                                </a>
                            @endif
                        </div>

                        {{-- Dropzone + previews --}}
                        <div x-data="memberImagePicker()">
                            <label for="memberImages" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                                @drop.prevent="onDrop($event)"
                                :class="dragOver
                                    ? 'flex flex-col items-center justify-center gap-3 w-full border-2 border-dashed border-ink-900 bg-volt/10 rounded-2xl p-8 cursor-pointer transition-colors'
                                    : 'flex flex-col items-center justify-center gap-3 w-full border-2 border-dashed border-ink-300 bg-ink-50/50 hover:border-ink-900 hover:bg-volt/5 rounded-2xl p-8 cursor-pointer transition-colors'">
                                <span
                                    :class="dragOver ? 'flex items-center justify-center w-12 h-12 rounded-2xl bg-ink-900 text-volt' : 'flex items-center justify-center w-12 h-12 rounded-2xl bg-ink-100 text-ink-600'">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <span class="text-sm text-ink-600" x-text="dragOver ? 'שחררו כדי לצרף' : 'גררו תמונות לכאן או בחרו מהמכשיר'"></span>
                                <span class="text-xs text-ink-400">
                                    JPEG · PNG · WebP · GIF · AVIF, עד 10MB לתמונה —
                                    {{ $isCreate ? 'התמונות יוצמדו לחבר החדש עם השמירה' : 'התמונות ייווספו לתיקייה של ' . $familyMember->name }}
                                </span>
                                <span class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-xl bg-ink-900 text-volt font-bold text-sm pointer-events-none">בחירת קבצים</span>
                                <input type="file" id="memberImages" name="images[]" form="member-form"
                                    accept="image/jpeg,image/png,image/webp,image/gif,image/avif"
                                    multiple class="hidden" @change="onFilesSelected($event)">
                            </label>

                            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" x-show="items.length" x-cloak>
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="card p-2 relative">
                                        <button type="button" @click="removeItem(index)"
                                            title="הסרה מהרשימה"
                                            class="absolute top-1.5 left-1.5 z-10 flex items-center justify-center w-6 h-6 rounded-full bg-ink-950/70 text-white hover:bg-danger transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round">
                                                <path d="M6 6l12 12M18 6L6 18" />
                                            </svg>
                                        </button>

                                        <template x-if="item.isImage">
                                            <img :src="item.preview" alt="" class="h-24 w-full object-cover rounded-lg border border-ink-200">
                                        </template>
                                        <template x-if="!item.isImage">
                                            <div class="flex items-center justify-center h-24 w-full rounded-lg border border-ink-200 bg-ink-50 text-ink-400">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h.01M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" />
                                                </svg>
                                            </div>
                                        </template>

                                        <div class="mt-2">
                                            <p class="truncate text-xs font-semibold text-ink-900" x-text="item.name"></p>
                                            <p class="text-[11px] text-ink-400" x-text="item.size"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p class="mt-3 text-xs text-ink-400" x-show="items.length" x-cloak x-text="selectedHint()"></p>
                        </div>

                        {{-- Existing images (edit mode) --}}
                        @if (! $isCreate)
                            @if ($media->isEmpty())
                                <div class="mt-6 p-8 text-center">
                                    <p class="text-sm text-ink-500">אין עדיין תמונות. צרפו את התמונות הראשונות של {{ $familyMember->name }} למעלה.</p>
                                </div>
                            @else
                                <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach ($media as $item)
                                        <div class="card overflow-hidden relative group">
                                            <img src="{{ $item->getUrl('thumb') }}" alt="{{ $item->name }}" class="h-32 w-full object-cover">
                                            <a href="{{ $item->getUrl() }}" target="_blank" class="absolute inset-0" title="פתיחה בגודל מלא"></a>
                                            <form action="{{ route('media.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('האם למחוק את התמונה מהספרייה?')"
                                                class="absolute top-1.5 left-1.5 z-10">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="מחיקת התמונה"
                                                    class="flex items-center justify-center w-7 h-7 rounded-full bg-ink-950/70 text-white hover:bg-danger transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M6 6l12 12M18 6L6 18" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Upcoming events (edit mode) --}}
                    @if (! $isCreate)
                        <div class="card p-5 sm:p-6">
                            <h3 class="text-base font-bold text-ink-950 mb-4 pb-3 border-b border-ink-200">אירועים קרובים</h3>

                            @php
                                $upcomingEvents = $familyMember->calendarEvents()
                                    ->where('event_date', '>=', now())
                                    ->orderBy('event_date')
                                    ->take(10)
                                    ->get();
                            @endphp

                            @if ($upcomingEvents->isEmpty())
                                <div class="py-8 text-center text-sm text-ink-500">
                                    אין אירועים עתידיים משויכים לחבר משפחה זה
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach ($upcomingEvents as $event)
                                        <div class="p-4 rounded-lg bg-ink-50 border border-ink-200 flex justify-between items-center">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-semibold text-sm text-ink-900">{{ $event->title }}</h4>
                                                    <span class="chip text-xs
                                                        @if ($event->event_type === 'birthday') chip-birthday
                                                        @elseif ($event->event_type === 'anniversary') chip-anniversary
                                                        @else chip-event
                                                        @endif">
                                                        {{ $event->event_type === 'birthday' ? 'יום הולדת' : ($event->event_type === 'anniversary' ? 'יום נישואין' : 'מותאם אישית') }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-ink-500">{{ $event->event_date->format('d/m/Y') }}</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                @if ($event->is_auto_generated)
                                                    <span class="text-xs font-medium text-ink-400"
                                                        title="אירוע אוטומטי — נוצר מתאריך ההולדת או יום הנישואין של חבר המשפחה. ניתן לערוך את הכותרת, התיאור ותמונת הכריכה.">
                                                        אוטומטי
                                                    </span>
                                                @endif
                                                <a href="{{ route('calendar-events.edit', [$event->calendar, $event]) }}" class="text-xs font-medium text-ink-900 hover:text-ink-950 hover:underline transition-colors">
                                                    ערוך אירוע
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function memberImagePicker() {
            return {
                items: [],
                dragOver: false,
                _files: [],

                onDrop(event) {
                    this.dragOver = false;
                    this.addFiles(event.dataTransfer.files);
                },

                onFilesSelected(event) {
                    this.addFiles(event.target.files);
                },

                addFiles(fileList) {
                    const files = Array.from(fileList);
                    if (!files.length) {
                        return;
                    }

                    files.forEach((file) => {
                        this._files.push(file);
                        this.items.push({
                            name: file.name,
                            size: this.formatSize(file.size),
                            preview: URL.createObjectURL(file),
                            isImage: file.type.startsWith('image/'),
                        });
                    });

                    this.syncInput();
                },

                removeItem(index) {
                    this._files.splice(index, 1);
                    this.items.splice(index, 1);
                    this.syncInput();
                },

                syncInput() {
                    const dt = new DataTransfer();
                    this._files.forEach((file) => dt.items.add(file));
                    document.getElementById('memberImages').files = dt.files;
                },

                selectedHint() {
                    if (this.items.length === 1) {
                        return 'תמונה אחת תישמר עם השמירה';
                    }
                    return this.items.length + ' תמונות יישמרו עם השמירה';
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
            };
        }
    </script>
</x-app-layout>
