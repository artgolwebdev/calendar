<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-[#1A1A1E]">ערוך לוח שנה</h1>
            <a href="{{ route('calendars.show', $calendar) }}" class="text-sm font-medium text-[#6B6B75] hover:text-[#1A1A1E] transition-colors">
                → חזור ללוח השנה
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
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

                    <div>
                        <label for="cover_image_path" class="label">תמונת כריכה <span class="text-xs text-[#6B6B75] font-normal">(אופציונלי)</span></label>
                        <input type="file" name="cover_image_path" id="cover_image_path" class="input pt-1.5 cursor-pointer" accept="image/*">
                        @if ($calendar->cover_image_path)
                            <div class="mt-3 p-3 bg-[#F7F7F8] border border-[#E5E5E8] rounded-lg flex items-center gap-3">
                                <img src="{{ asset('storage/' . $calendar->cover_image_path) }}" alt="תמונה נוכחית" class="h-16 w-24 object-cover rounded-md border border-[#E5E5E8]">
                                <p class="text-xs text-[#6B6B75]">העלאת תמונה חדשה תחליף את התמונה הנוכחית</p>
                            </div>
                        @endif
                        <p class="text-xs text-[#6B6B75] mt-1">גודל מקסימלי: 50MB</p>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-[#F7F7F8] border border-[#E5E5E8] rounded-lg">
                        <input type="hidden" name="is_main" value="0">
                        <input type="checkbox" name="is_main" id="is_main" value="1"
                            @checked($calendar->is_main)
                            class="mt-1 h-4 w-4 rounded border-[#D1D1D8] text-[#4F46E5] focus:ring-[#4F46E5]">
                        <label for="is_main" class="text-sm text-[#1A1A1E] cursor-pointer">
                            <span class="font-medium">לוח שנה ראשי</span>
                            <span class="block text-xs text-[#6B6B75] font-normal mt-0.5">
                                לוח השנה הראשי מוצג בלוח הבקרה ובסקירת החודש הנוכחי
                            </span>
                        </label>
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
                            עדכן לוח שנה
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