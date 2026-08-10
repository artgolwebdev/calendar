<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-ink-900">ערוך לוח שנה</h1>
            <a href="{{ route('calendars.show', $calendar) }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition-colors">
                → חזור ללוח השנה
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card p-6 max-w-xl mx-auto">
                <form action="{{ route('calendars.update', $calendar) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="name" class="label">שם לוח השנה</label>
                        <input type="text" name="name" id="name" required
                            class="input"
                            placeholder="לדוגמה: לוח שנה 2026"
                            value="{{ old('name', $calendar->name) }}">
                    </div>

                    <x-cover-upload :existing="$calendar->cover_image_path ? asset('storage/' . $calendar->cover_image_path) : null" />

                    <div class="flex items-start gap-3 p-4 bg-ink-50 border border-ink-200 rounded-lg">
                        <input type="hidden" name="is_main" value="0">
                        <input type="checkbox" name="is_main" id="is_main" value="1"
                            @checked($calendar->is_main)
                            class="mt-1 h-4 w-4 rounded border-ink-300 text-ink-950 focus:ring-ink-950">
                        <label for="is_main" class="text-sm text-ink-900 cursor-pointer">
                            <span class="font-medium">לוח שנה ראשי</span>
                            <span class="block text-xs text-ink-500 font-normal mt-0.5">
                                לוח השנה הראשי מוצג בלוח הבקרה ובסקירת החודש הנוכחי
                            </span>
                        </label>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-error">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="pt-4 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            עדכן לוח שנה
                        </button>
                        <a href="{{ route('calendars.show', $calendar) }}" class="btn btn-outline">
                            ביטול
                        </a>
                    </div>
                </form>
            </div>

            {{-- Family members --}}
            <div class="card p-6 mt-6 max-w-xl mx-auto">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-ink-200">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-ink-950">חברי משפחה</h2>
                        <span class="chip bg-volt/20 text-ink-900">{{ $familyMembers->count() }}</span>
                    </div>
                    <a href="{{ route('family-members.create', $calendar) }}"
                        class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14m-7-7h14" />
                        </svg>
                        הוסף חבר משפחה
                    </a>
                </div>

                @if ($familyMembers->isEmpty())
                    <div class="py-10 text-center">
                        <p class="text-sm text-ink-500 mb-4">
                            עדיין אין חברי משפחה בלוח הזה. הוסיפו חבר כדי ליצור אוטומטית אירועי יום הולדת ויום נישואין.
                        </p>
                        <a href="{{ route('family-members.create', $calendar) }}"
                            class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950">
                            הוסף חבר משפחה
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-ink-200">
                        @foreach ($familyMembers as $member)
                            <li class="py-3 flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-ink-900">{{ $member->name }}</span>
                                        @if ($member->folder && $member->folder->media_count > 0)
                                            <span class="chip bg-ink-100 text-ink-500">{{ $member->folder->media_count }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-0.5 text-xs text-ink-500">
                                        יום הולדת: {{ $member->birth_date->format('d/m/Y') }}
                                        @if ($member->anniversary_date)
                                            · יום נישואין: {{ $member->anniversary_date->format('d/m/Y') }}
                                        @endif
                                    </div>
                                    @if ($member->notes)
                                        <div class="mt-0.5 text-xs text-ink-400">{{ Str::limit($member->notes, 60) }}</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <a href="{{ route('family-members.edit', [$calendar, $member]) }}"
                                        class="text-xs font-medium text-ink-900 hover:text-ink-950 hover:underline transition-colors">
                                        ערוך
                                    </a>
                                    <span class="text-ink-200">|</span>
                                    <form action="{{ route('family-members.destroy', [$calendar, $member]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-xs font-medium text-danger hover:text-danger-hover transition-colors"
                                            onclick="return confirm('האם אתה בטוח שברצונך למחוק את חבר המשפחה?')">
                                            מחק
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>