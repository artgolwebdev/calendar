<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-[#1A1A1E]">ערוך חבר משפחה</h1>
            <a href="{{ route('family-members.index') }}" class="text-sm font-medium text-[#6B6B75] hover:text-[#1A1A1E] transition-colors">
                ← חזור לרשימה
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            <div class="card p-6 max-w-xl mx-auto">
                <form action="{{ route('family-members.update', $familyMember) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="name" class="label">שם מלא</label>
                        <input type="text" name="name" id="name" required
                            class="input"
                            placeholder="שם חבר המשפחה"
                            value="{{ old('name', $familyMember->name) }}">
                    </div>

                    <div>
                        <label for="birth_date" class="label">תאריך לידה</label>
                        <input type="date" name="birth_date" id="birth_date" required
                            class="input"
                            value="{{ old('birth_date', $familyMember->birth_date->format('Y-m-d')) }}">
                    </div>

                    <div>
                        <label for="anniversary_date" class="label">תאריך נישואין <span class="text-xs text-[#6B6B75] font-normal">(אופציונלי)</span></label>
                        <input type="date" name="anniversary_date" id="anniversary_date"
                            class="input"
                            value="{{ old('anniversary_date', $familyMember->anniversary_date ? $familyMember->anniversary_date->format('Y-m-d') : '') }}">
                    </div>

                    <div>
                        <label for="notes" class="label">הערות <span class="text-xs text-[#6B6B75] font-normal">(אופציונלי)</span></label>
                        <textarea name="notes" id="notes" rows="3"
                            class="input"
                            placeholder="הערות על חבר המשפחה">{{ old('notes', $familyMember->notes) }}</textarea>
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
                            עדכן חבר משפחה
                        </button>
                        <a href="{{ route('family-members.index') }}" class="btn btn-secondary">
                            ביטול
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>