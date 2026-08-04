<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-[#1A1A1E]">{{ $calendar->name }}</h1>
                <p class="text-xs text-[#6B6B75] mt-1">בחר חודש לצפייה ועיצוב הלוח</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendars.edit', $calendar) }}" class="btn btn-secondary btn-sm">
                    ערוך הגדרות
                </a>
                <form action="{{ route('calendars.destroy', $calendar) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-destructive btn-sm"
                        onclick="return confirm('האם אתה בטוח שברצונך למחוק את לוח השנה?')">
                        מחק
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            @if (session('success'))
                <div class="mb-6 p-4 rounded-lg bg-[#F0FDF4] border border-[#DCFCE7] text-[#15803D] text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Calendar Banner -->
            @if ($calendar->cover_image_path)
                <div class="card p-6 mb-6 bg-cover bg-center relative overflow-hidden" style="background-image: url('{{ asset('storage/' . $calendar->cover_image_path) }}'); min-height: 140px;">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-black/30"></div>
                    <div class="relative z-10 text-white">
                        <h2 class="text-2xl font-bold mb-1 text-white">{{ $calendar->name }}</h2>
                        <p class="text-sm text-gray-200">לחץ על חודש כדי למסגר ולצפות באירועים</p>
                    </div>
                </div>
            @else
                <div class="card p-6 mb-6 bg-gradient-to-r from-[#4F46E5] to-[#6366F1] text-white">
                    <h2 class="text-2xl font-bold mb-1 text-white">{{ $calendar->name }}</h2>
                    <p class="text-sm text-indigo-100">לחץ על חודש כדי לצפות באירועים וחגים</p>
                </div>
            @endif

            <!-- Month Tiles Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($monthPages as $monthPage)
                    @php
                        $monthNames = [
                            1 => 'ינואר', 2 => 'פברואר', 3 => 'מרץ', 4 => 'אפריל',
                            5 => 'מאי', 6 => 'יוני', 7 => 'יולי', 8 => 'אוגוסט',
                            9 => 'ספטמבר', 10 => 'אוקטובר', 11 => 'נובמבר', 12 => 'דצמבר'
                        ];
                        $hebrewInfo = $monthInfo[$monthPage->month_number] ?? null;
                    @endphp
                    
                    <a href="{{ route('calendars.month', [$calendar, $monthPage->month_number, now()->year]) }}" 
                        class="card p-5 block group hover:border-[#4F46E5] transition-all">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-base font-semibold text-[#1A1A1E] group-hover:text-[#4F46E5] transition-colors">
                                    {{ $monthNames[$monthPage->month_number] }}
                                </h3>
                                <p class="text-xs text-[#6B6B75]">חודש {{ $monthPage->month_number }}</p>
                            </div>
                            <div class="text-left">
                                @if ($hebrewInfo)
                                    <p class="text-[11px] text-[#6B6B75]">{{ $hebrewInfo['hebrew_start'] }}</p>
                                    <p class="text-[11px] text-[#6B6B75]">{{ $hebrewInfo['hebrew_end'] }}</p>
                                @endif
                            </div>
                        </div>
                        
                        @if ($monthPage->background_image_path || $monthPage->custom_image_path)
                            <div class="mt-2">
                                <span class="chip chip-holiday text-[11px]">
                                    עם תמונה
                                </span>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    ← חזור ללוח הבקרה
                </a>
            </div>
        </div>
    </div>
</x-app-layout>