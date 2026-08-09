<x-app-layout>
    @php $isAuto = $calendarEvent->is_auto_generated; @endphp

    <div class="py-8">
        <div class="container">
            {{-- Header: back link --}}
            <a href="{{ route('calendars.show', $calendar) }}"
                class="inline-flex items-center gap-1.5 mb-4 text-sm font-semibold text-ink-500 hover:text-ink-900 transition-colors">
                → חזרה ללוח השנה
            </a>

            {{-- Header: title --}}
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-ink-950 tracking-tight">
                    ערוך אירוע
                </h1>
                <p class="mt-1 text-sm text-ink-500">
                    {{ $calendar->name }} — {{ $calendarEvent->event_date->format('d/m/Y') }}
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-danger-light border border-danger/30 text-danger text-sm font-medium">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($isAuto)
                <div class="mb-6 p-4 rounded-xl bg-volt/10 border border-volt-300 text-sm text-ink-700 leading-relaxed">
                    <p class="font-bold text-ink-900">אירוע אוטומטי — עריכה חלקית בלבד</p>
                    <p class="mt-1">
                        האירוע נוצר מתאריך ההולדת / יום הנישואין של
                        {{ $calendarEvent->familyMember?->name }}. ניתן לערוך רק את הכותרת, התיאור ותמונת הכריכה.
                    </p>
                </div>
            @endif

            <div class="card p-6 max-w-xl mx-auto">
                <form action="{{ route('calendar-events.update', [$calendar, $calendarEvent]) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="event_type" class="label">סוג אירוע</label>
                        @if ($isAuto)
                            <select id="event_type" disabled class="input opacity-60 cursor-not-allowed">
                                <option selected>
                                    {{ match ($calendarEvent->event_type) {
                                        'birthday' => 'יום הולדת',
                                        'anniversary' => 'יום נישואין',
                                        default => 'מותאם אישית',
                                    } }}
                                </option>
                            </select>
                        @else
                            <select name="event_type" id="event_type" required class="input">
                                <option value="custom" {{ old('event_type', $calendarEvent->event_type) === 'custom' ? 'selected' : '' }}>מותאם אישית</option>
                            </select>
                            <p class="mt-1 text-xs text-ink-400">
                                אירועי יום הולדת ויום נישואין נוצרים אוטומטית מחברי המשפחה
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="title" class="label">כותרת האירוע</label>
                        <input type="text" name="title" id="title" required
                            class="input"
                            placeholder="כותרת האירוע"
                            value="{{ old('title', $calendarEvent->title) }}">
                    </div>

                    <div>
                        <label for="description" class="label">תיאור <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                        <textarea name="description" id="description" rows="3"
                            class="input"
                            placeholder="פרטים נוספים על האירוע">{{ old('description', $calendarEvent->description) }}</textarea>
                    </div>

                    <div>
                        <label for="event_date" class="label">תאריך האירוע</label>
                        <input type="date" name="event_date" id="event_date" required
                            @if ($isAuto) disabled class="input opacity-60 cursor-not-allowed"
                            @else class="input" @endif
                            value="{{ old('event_date', $calendarEvent->event_date->format('Y-m-d')) }}">
                        @if ($isAuto)
                            <p class="mt-1 text-xs text-ink-400">נקבע אוטומטית מתאריך חבר המשפחה</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="label">שעת התחלה <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                            <input type="time" name="start_time" id="start_time"
                                @if ($isAuto) disabled class="input opacity-60 cursor-not-allowed"
                                @else class="input" @endif
                                value="{{ old('start_time', $calendarEvent->start_time ? \Carbon\Carbon::parse($calendarEvent->start_time)->format('H:i') : '') }}">
                        </div>
                        <div>
                            <label for="end_time" class="label">שעת סיום <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                            <input type="time" name="end_time" id="end_time"
                                @if ($isAuto) disabled class="input opacity-60 cursor-not-allowed"
                                @else class="input" @endif
                                value="{{ old('end_time', $calendarEvent->end_time ? \Carbon\Carbon::parse($calendarEvent->end_time)->format('H:i') : '') }}">
                        </div>
                    </div>

                    <div>
                        <label for="family_member_id" class="label">שיוך לחבר משפחה <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                        <select name="family_member_id" id="family_member_id"
                            @if ($isAuto) disabled class="input opacity-60 cursor-not-allowed"
                            @else class="input" @endif>
                            <option value="">ללא שיוך לחבר משפחה</option>
                            @foreach ($calendar->user->familyMembers as $member)
                                <option value="{{ $member->id }}" {{ old('family_member_id', $calendarEvent->family_member_id) == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isAuto)
                            <p class="mt-1 text-xs text-ink-400">נקבע אוטומטית מחבר המשפחה</p>
                        @endif
                    </div>

                    <x-cover-upload :existing="$calendarEvent->cover_image_path ? asset('storage/' . $calendarEvent->cover_image_path) : null" />

                    <div class="pt-4 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            עדכן אירוע
                        </button>
                        <a href="{{ route('calendars.show', $calendar) }}" class="btn btn-secondary">
                            ביטול
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
