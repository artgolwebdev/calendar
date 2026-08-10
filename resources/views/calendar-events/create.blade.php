<x-app-layout>
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
                    הוסף אירוע חדש
                </h1>
                <p class="mt-1 text-sm text-ink-500">
                    {{ $calendar->name }} — יצירת אירוע ידני
                </p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error mb-6">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card p-6 max-w-xl mx-auto">
                <form action="{{ route('calendar-events.store', $calendar) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="event_type" class="label">סוג אירוע</label>
                        <select name="event_type" id="event_type" required class="input">
                            <option value="custom" {{ old('event_type', 'custom') === 'custom' ? 'selected' : '' }}>מותאם אישית</option>
                        </select>
                        <p class="mt-1 text-xs text-ink-400">
                            אירועי יום הולדת ויום נישואין נוצרים אוטומטית מחברי המשפחה
                        </p>
                    </div>

                    <div>
                        <label for="title" class="label">כותרת האירוע</label>
                        <input type="text" name="title" id="title" required
                            class="input"
                            placeholder="לדוגמה: יום הולדת 30 לדני"
                            value="{{ old('title') }}">
                    </div>

                    <div>
                        <label for="description" class="label">תיאור <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                        <textarea name="description" id="description" rows="3"
                            class="input"
                            placeholder="פרטים נוספים על האירוע">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="event_date" class="label">תאריך האירוע</label>
                        <input type="date" name="event_date" id="event_date" required
                            class="input"
                            value="{{ request()->get('date') ?? old('event_date') }}">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="label">שעת התחלה <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                            <input type="time" name="start_time" id="start_time"
                                class="input"
                                value="{{ request()->get('time') ?? old('start_time') }}">
                        </div>
                        <div>
                            <label for="end_time" class="label">שעת סיום <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                            <input type="time" name="end_time" id="end_time"
                                class="input"
                                value="{{ old('end_time') }}">
                        </div>
                    </div>

                    <div>
                        <label for="family_member_id" class="label">שיוך לחבר משפחה <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                        <select name="family_member_id" id="family_member_id" class="input">
                            <option value="">ללא שיוך לחבר משפחה</option>
                            @foreach ($calendar->familyMembers as $member)
                                <option value="{{ $member->id }}" {{ old('family_member_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-cover-upload />

                    <div class="pt-4 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            הוסף אירוע
                        </button>
                        <a href="{{ route('calendars.show', $calendar) }}" class="btn btn-outline">
                            ביטול
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
