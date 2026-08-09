<x-app-layout>
    <div class="py-8">
        <div class="container">
            @if (session('success'))
                <div class="mb-6 p-4 rounded-lg bg-[#F0FDF4] border border-[#DCFCE7] text-[#15803D] text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <x-dashboard.day-scroller
                :days="$days"
                :main-calendar="$mainCalendar"
                :month="$month"
                :year="$year" />

            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($calendars as $calendar)
                    <div class="card overflow-hidden transition-colors hover:border-[#D4D4D8]">
                        @if ($calendar->cover_image_path)
                            <div class="h-36 bg-cover bg-center border-b border-[#E5E5E8]" style="background-image: url('{{ asset('storage/' . $calendar->cover_image_path) }}');"></div>
                        @else
                            <div class="h-36 border-b border-[#E5E5E8] bg-gradient-to-br from-[#EEF2FF] to-[#E0E7FF] flex items-center justify-center">
                                <svg class="w-10 h-10 text-[#818CF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-2 gap-2">
                                <h3 class="text-base font-semibold text-[#1A1A1E] truncate">{{ $calendar->name }}</h3>
                                @if ($calendar->is_main)
                                    <span class="chip chip-birthday text-[10px] shrink-0">ראשי</span>
                                @endif
                            </div>
                            <p class="text-xs text-[#6B6B75] mb-5">
                                נוצר בתאריך: {{ $calendar->created_at->format('d/m/Y') }}
                            </p>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('calendars.show', $calendar) }}"
                                    class="btn btn-primary flex-1 text-center justify-center">
                                    צפה בלוח
                                </a>
                                <a href="{{ route('calendars.edit', $calendar) }}"
                                    class="btn btn-secondary flex-1 text-center justify-center">
                                    ערוך לוח
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full card p-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-[#EEF2FF] text-[#4F46E5] flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-[#1A1A1E] mb-1">עדיין אין לך לוחות שנה</h3>
                        <p class="text-sm text-[#6B6B75] mb-6">צור את לוח השנה המשפחתי הראשון שלך עכשיו</p>
                        <a href="{{ route('calendars.create') }}" class="btn btn-primary inline-flex">
                            + צור לוח שנה חדש
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
