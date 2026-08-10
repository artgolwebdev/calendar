<x-app-layout>
    @php
        $weekdayNames = ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'];
        $monthNames = [
            1 => 'ינואר', 2 => 'פברואר', 3 => 'מרץ', 4 => 'אפריל',
            5 => 'מאי', 6 => 'יוני', 7 => 'יולי', 8 => 'אוגוסט',
            9 => 'ספטמבר', 10 => 'אוקטובר', 11 => 'נובמבר', 12 => 'דצמבר',
        ];
        $weekdayName = $weekdayNames[$currentDate->dayOfWeek];
        $monthName = $monthNames[$currentDate->month];
        $createUrl = route('calendar-events.create', $calendar);
    @endphp

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('calendars.month', [$calendar, $currentDate->month, $currentDate->year]) }}" class="btn btn-outline btn-sm">
                    → חזור לחודש
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendars.day', [$calendar, $previousDate->format('Y-m-d')]) }}"
                    class="btn btn-outline btn-sm" aria-label="יום קודם">
                    → יום קודם
                </a>
                <a href="{{ route('calendars.day', [$calendar, now()->format('Y-m-d')]) }}"
                    class="btn btn-outline btn-sm" aria-label="היום">
                    היום
                </a>
                <a href="{{ route('calendars.day', [$calendar, $nextDate->format('Y-m-d')]) }}"
                    class="btn btn-outline btn-sm" aria-label="יום הבא">
                    יום הבא ←
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-ink-900">{{ $weekdayName }}, {{ $currentDate->day }} {{ $monthName }} {{ $currentDate->year }}</h1>
                    <p class="text-sm text-ink-500 mt-1">· {{ $hebrewDate }} {{ $hebrewYear }}</p>
                </div>
            </div>

            <div class="card p-4 sm:p-6">
                @if ($holidays || $allDayEvents)
                    <div class="flex flex-wrap items-center gap-2 pb-4 mb-4 border-b border-[var(--color-border)]">
                        @foreach ($holidays as $holiday)
                            <span class="chip chip-holiday">
                                {{ $holiday['title'] ?? $holiday['hebrew'] ?? 'חג' }}
                            </span>
                        @endforeach

                        @foreach ($allDayEvents as $event)
                            @php
                                $chipClass = 'chip-event';
                                if ($event->event_type === 'birthday') $chipClass = 'chip-birthday';
                                elseif ($event->event_type === 'anniversary') $chipClass = 'chip-anniversary';
                            @endphp
                            <span class="chip {{ $chipClass }}" title="{{ $event->display_title ?? $event->title }}">
                                {{ $event->display_title ?? $event->title }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div dir="ltr" class="flex">
                    {{-- Hour gutter --}}
                    <div class="relative shrink-0" style="width: 3.5rem;">
                        @for ($hour = 0; $hour < 24; $hour++)
                            <div class="absolute text-xs tabular-nums text-ink-500 leading-none"
                                style="top: {{ $hour * 64 }}px; right: 0; transform: translateY(-50%);">
                                {{ sprintf('%02d:00', $hour) }}
                            </div>
                        @endfor
                    </div>

                    {{-- Grid body --}}
                    <div class="relative flex-1 border-s border-[var(--color-border)]" style="height: 1536px;">
                        @for ($hour = 0; $hour < 24; $hour++)
                            <a href="{{ $createUrl }}?date={{ $currentDate->format('Y-m-d') }}&time={{ sprintf('%02d:00', $hour) }}"
                                class="absolute w-full border-t border-[var(--color-border)] transition-colors hover:bg-[var(--color-accent-soft)]"
                                style="top: {{ $hour * 64 }}px; height: 64px;"
                                aria-label="הוסף אירוע בשעה {{ sprintf('%02d:00', $hour) }}"></a>
                        @endfor

                        @foreach ($positionedEvents as $event)
                            @php
                                $colors = match ($event['event']->event_type) {
                                    'birthday' => ['bg' => '#F3E8FF', 'fg' => '#6B21A8', 'border' => '#E9D5FF'],
                                    'anniversary' => ['bg' => '#E0F2FE', 'fg' => '#0369A1', 'border' => '#BAE6FD'],
                                    default => ['bg' => '#FFEDD5', 'fg' => '#C2410C', 'border' => '#FED7AA'],
                                };
                            @endphp
                            <div class="absolute p-0.5"
                                style="top: {{ $event['top'] }}%; height: {{ $event['height'] }}%; left: {{ $event['left'] }}%; width: {{ $event['width'] }}%;">
                                <div class="h-full overflow-hidden rounded-md border px-2 py-1"
                                    style="background-color: {{ $colors['bg'] }}; border-color: {{ $colors['border'] }}; color: {{ $colors['fg'] }};">
                                    <div class="text-[11px] font-semibold leading-tight truncate" title="{{ $event['title'] }}">
                                        {{ $event['title'] }}
                                    </div>
                                    <div class="text-[10px] leading-tight opacity-80 tabular-nums">
                                        {{ $event['start_label'] }}–{{ $event['end_label'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if ($isToday)
                            <div id="dayViewNowLine" class="absolute left-0 w-full z-10 pointer-events-none"
                                style="top: {{ $nowTop }}%; height: 0; border-top: 2px solid #DC2626;">
                                <span class="absolute -top-[5px] w-2.5 h-2.5 rounded-full"
                                    style="left: 0; background-color: #DC2626;"></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ $createUrl }}?date={{ $currentDate->format('Y-m-d') }}"
        class="btn btn-primary rounded-full w-14 h-14 p-0"
        style="position: fixed; left: 1.5rem; bottom: 1.5rem; z-index: 40;"
        aria-label="הוסף אירוע">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
    </a>

    @if ($isToday)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const line = document.getElementById('dayViewNowLine');
                if (!line) return;

                const total = 24 * 60;
                const update = function () {
                    const now = new Date();
                    const minutes = now.getHours() * 60 + now.getMinutes();
                    line.style.top = (minutes / total * 100) + '%';
                };

                update();
                setInterval(update, 60000);
            });
        </script>
    @endif
</x-app-layout>
