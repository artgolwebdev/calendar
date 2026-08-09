<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-[#1A1A1E]">{{ $calendar->name }}</h1>
                <p class="text-xs text-[#6B6B75] mt-1">בחר חודש לצפייה ועיצוב הלוח</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendar-events.index', $calendar) }}" class="btn btn-secondary btn-sm">
                    כל האירועים
                </a>
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

    <div class="py-8"
        x-data="themePicker({
            applyUrl: '{{ route('calendars.themes.apply', $calendar) }}',
            themes: @js(config('themes'))
        })"
        @keydown.escape.window="themesOpen = false; pendingTheme = null">
        <div class="container">
            <div class="mb-6 flex items-center justify-between gap-3">
                <p class="text-sm text-[#6B6B75]">החל נושא עיצוב אחיד על כל 12 חודשי הלוח</p>
                <button type="button" @click="themesOpen = !themesOpen"
                    :class="themesOpen ? 'bg-volt text-ink-950' : 'bg-ink-950 text-volt hover:bg-ink-800'"
                    class="inline-flex items-center gap-2 h-10 px-5 rounded-full font-bold text-sm transition-colors shadow-sm"
                    aria-label="נושאים" aria-expanded="false" :aria-expanded="themesOpen ? 'true' : 'false'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a10 10 0 1 1 10-10c0 2.21-1.79 4-4 4h-2.5a2.5 2.5 0 0 0-2 4.04c.58.72.5 1.96-1.5 1.96Z" />
                        <circle cx="7.5" cy="11.5" r="1.5" fill="currentColor" stroke="none" />
                        <circle cx="11" cy="7" r="1.5" fill="currentColor" stroke="none" />
                        <circle cx="16" cy="8" r="1.5" fill="currentColor" stroke="none" />
                    </svg>
                    נושאים
                </button>
            </div>

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
                        <p class="text-sm text-gray-200">{{ $year }} · {{ $hebrewYear }}</p>
                        <p class="text-sm text-gray-200">לחץ על חודש כדי למסגר ולצפות באירועים</p>
                    </div>
                </div>
            @else
                <div class="card p-6 mb-6 bg-gradient-to-r from-[#4F46E5] to-[#6366F1] text-white">
                    <h2 class="text-2xl font-bold mb-1 text-white">{{ $calendar->name }}</h2>
                    <p class="text-sm text-indigo-100">{{ $year }} · {{ $hebrewYear }}</p>
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
                        $info = $monthInfo[$monthPage->month_number] ?? null;
                        $hasImage = $info && $info['background_image_url'];
                    @endphp
                    
                    <a href="{{ route('calendars.month', [$calendar, $monthPage->month_number, now()->year]) }}" 
                        class="card overflow-hidden block group hover:border-[#4F46E5] transition-all">
                        @if ($hasImage)
                            <div class="relative h-32 overflow-hidden">
                                <img src="{{ $info['background_image_url'] }}"
                                    alt="{{ $monthNames[$monthPage->month_number] }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                <div class="absolute bottom-2 inset-x-3 flex items-end justify-between">
                                    <h3 class="text-white text-lg font-semibold bg-black/50 rounded-md px-2 py-0.5">
                                        {{ $monthNames[$monthPage->month_number] }}
                                    </h3>
                                    @if ($info)
                                        <p class="text-white text-[11px] bg-black/40 rounded px-1.5 py-0.5">{{ $info['hebrew_month'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="p-4">
                            @unless ($hasImage)
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="text-base font-semibold text-[#1A1A1E] group-hover:text-[#4F46E5] transition-colors">
                                            {{ $monthNames[$monthPage->month_number] }}
                                        </h3>
                                        @if ($info)
                                            <p class="text-xs text-[#6B6B75]">{{ $info['hebrew_month'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endunless

                            <div class="flex flex-wrap gap-1.5">
                                @if ($info)
                                    @foreach ($info['events'] as $event)
                                        @php
                                            $chipClass = 'chip-event';
                                            if ($event['type'] === 'birthday') {
                                                $chipClass = 'chip-birthday';
                                            } elseif ($event['type'] === 'anniversary') {
                                                $chipClass = 'chip-anniversary';
                                            }
                                        @endphp
                                        <span class="chip {{ $chipClass }} text-[11px]">{{ $event['title'] }}</span>
                                    @endforeach
                                    @if ($info['events_count'] > count($info['events']))
                                        <span class="chip chip-event text-[11px]">+{{ $info['events_count'] - count($info['events']) }} אירועים</span>
                                    @endif

                                    @foreach ($info['holidays'] as $title)
                                        <span class="chip chip-holiday text-[11px]">{{ $title }}</span>
                                    @endforeach
                                    @if ($info['holidays_count'] > count($info['holidays']))
                                        <span class="chip chip-holiday text-[11px]">+{{ $info['holidays_count'] - count($info['holidays']) }} חגים</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    → חזור ללוח הבקרה
                </a>
            </div>

            <!-- Theme Picker -->
            @include('calendars.partials.themes-picker', ['themesScope' => 'year'])
        </div>
    </div>
</x-app-layout>