<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-[#1A1A1E]">הוסף אירוע חדש</h1>
            <a href="{{ route('calendars.show', $calendar) }}" class="text-sm font-medium text-[#6B6B75] hover:text-[#1A1A1E] transition-colors">
                → חזור ללוח
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            <div class="card p-6 max-w-xl mx-auto">
                <form action="{{ route('calendar-events.store', $calendar) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="title" class="label">כותרת האירוע</label>
                        <input type="text" name="title" id="title" required
                            class="input"
                            placeholder="לדוגמה: יום הולדת 30 לדני"
                            value="{{ old('title') }}">
                    </div>

                    <div>
                        <label for="event_date" class="label">תאריך האירוע</label>
                        <input type="date" name="event_date" id="event_date" required
                            class="input"
                            value="{{ request()->get('date') ?? old('event_date') }}">
                    </div>

                    <div>
                        <label for="event_type" class="label">סוג אירוע</label>
                        <select name="event_type" id="event_type" required class="input">
                            <option value="birthday" {{ old('event_type') === 'birthday' ? 'selected' : '' }}>יום הולדת</option>
                            <option value="anniversary" {{ old('event_type') === 'anniversary' ? 'selected' : '' }}>יום נישואין</option>
                            <option value="custom" {{ old('event_type') === 'custom' ? 'selected' : '' }}>מותאם אישית</option>
                        </select>
                    </div>

                    <div>
                        <label for="family_member_id" class="label">שיוך לחבר משפחה <span class="text-xs text-[#6B6B75] font-normal">(אופציונלי)</span></label>
                        <select name="family_member_id" id="family_member_id" class="input">
                            <option value="">ללא שיוך לחבר משפחה</option>
                            @foreach ($calendar->user->familyMembers as $member)
                                <option value="{{ $member->id }}" {{ old('family_member_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($errors->any())
                        <div class="p-4 rounded-lg bg-[#FEF2F2] border border-[#FCA5A5] text-sm text-[#DC2626]">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="pt-4 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            הוסף אירוע
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