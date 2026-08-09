<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('calendars.show', $calendar) }}" class="btn btn-secondary btn-sm">
                    → חזור ללוח
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendars.month', [$calendar, $previousMonth->month, $previousMonth->year]) }}"
                    class="btn btn-secondary btn-sm" aria-label="חודש קודם">
                    → חודש קודם
                </a>
                <a href="{{ route('calendars.month', [$calendar, now()->month, now()->year]) }}"
                    class="btn btn-secondary btn-sm" aria-label="היום">
                    היום
                </a>
                <a href="{{ route('calendars.month', [$calendar, $nextMonth->month, $nextMonth->year]) }}"
                    class="btn btn-secondary btn-sm" aria-label="חודש הבא">
                    חודש הבא ←
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $styleService = app(\App\Services\MonthPageStyleService::class);
        $styles = $styleService->resolve($monthPage);

        $monthNames = [
            1 => 'ינואר', 2 => 'פברואר', 3 => 'מרץ', 4 => 'אפריל',
            5 => 'מאי', 6 => 'יוני', 7 => 'יולי', 8 => 'אוגוסט',
            9 => 'ספטמבר', 10 => 'אוקטובר', 11 => 'נובמבר', 12 => 'דצמבר'
        ];
        $hebrewDateService = app(\App\Services\HebrewDateService::class);
        $hebrewMonthName = $hebrewDateService->hebrewMonthName(
            \Carbon\Carbon::create($year, $monthPage->month_number, 1)
        );
        $hebrewYear = $hebrewDateService->toHebrewArray(
            \Carbon\Carbon::create($year, $monthPage->month_number, 1)
        )['year'];
    @endphp

    <div class="py-8">
        <div class="container">
            @if (session('success'))
                <div class="mb-4 card p-4" style="background-color: #F0FFF4; border-color: #C6F6D5;">
                    <p style="color: #276749;">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 card p-4" style="background-color: #FFF5F5; border-color: #FEB2B2;">
                    <ul class="list-disc pr-5" style="color: #C53030;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($calendar->cover_image_path)
                <div class="rounded-xl overflow-hidden mb-6 relative"
                    style="height: 140px; background-image: url('{{ asset('storage/' . $calendar->cover_image_path) }}'); background-size: cover; background-position: center;">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-white text-2xl font-bold px-4 py-1.5 bg-black/50 rounded-lg">
                                {{ $monthNames[$monthPage->month_number] }} {{ $year }}
                            </p>
                            <p class="text-white/90 text-sm mt-1.5">· {{ $hebrewMonthName }} {{ $hebrewYear }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-xl overflow-hidden mb-6 relative bg-gradient-to-r from-[#4F46E5] to-[#6366F1]"
                    style="height: 140px;">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-white text-2xl font-bold px-4 py-1.5 bg-white/15 rounded-lg">
                                {{ $monthNames[$monthPage->month_number] }} {{ $year }}
                            </p>
                            <p class="text-indigo-100 text-sm mt-1.5">· {{ $hebrewMonthName }} {{ $hebrewYear }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                <!-- Calendar Grid -->
                <div class="lg:col-span-3">
                    <div id="calendarGrid" dir="ltr" class="card p-3 sm:p-6 relative overflow-hidden"
                        style="font-family: {{ $styles['fontFamily'] }}; {{ $styles['gridBackground'] }} min-height: 600px;">
                        <div id="gridOverlay" class="absolute inset-0 rounded-lg pointer-events-none"
                            style="{{ $styles['overlay'] }}"></div>

                        <div class="relative overflow-x-auto">
                            <div class="min-w-[640px]">
                            <div class="grid grid-cols-7 gap-1.5 sm:gap-2 mb-4">
                                @foreach (['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'] as $day)
                                    <div class="weekday-header text-center text-sm font-medium"
                                        style="color: {{ $styles['weekdayColor'] }};">
                                        {{ $day }}
                                    </div>
                                @endforeach
                            </div>

                            @php
                                $hebrewDateService = app(\App\Services\HebrewDateService::class);
                                $firstDayOfMonth = \Carbon\Carbon::create($year, $monthPage->month_number, 1);
                                $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
                                $startDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 = Sunday, 6 = Saturday
                                $totalDays = $lastDayOfMonth->day;

                                // Previous month days
                                $previousMonth = $firstDayOfMonth->copy()->subMonth();
                                $previousMonthDays = $previousMonth->daysInMonth;

                                // Next month days
                                $nextMonth = $firstDayOfMonth->copy()->addMonth();

                                // Today's date for highlighting
                                $today = \Carbon\Carbon::today();

                                // Group events by date
                                $eventsByDate = [];
                                foreach ($events as $event) {
                                    $dateKey = $event->display_date->format('Y-m-d');
                                    if (!isset($eventsByDate[$dateKey])) {
                                        $eventsByDate[$dateKey] = [];
                                    }
                                    $eventsByDate[$dateKey][] = $event;
                                }

                                // Group holidays by date
                                $holidaysByDate = [];
                                foreach ($holidays as $holiday) {
                                    $date = \Carbon\Carbon::parse($holiday['date']);
                                    $dateKey = $date->format('Y-m-d');
                                    if (!isset($holidaysByDate[$dateKey])) {
                                        $holidaysByDate[$dateKey] = [];
                                    }
                                    $holidaysByDate[$dateKey][] = $holiday;
                                }
                            @endphp

                            <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                                @for ($i = 0; $i < 42; $i++)
                                    @php
                                        $dayNumber = $i - $startDayOfWeek + 1;
                                        $isCurrentMonth = $dayNumber >= 1 && $dayNumber <= $totalDays;
                                        $isToday = false;
                                        $hebrewDate = '';
                                        $dayEvents = [];
                                        $dayHolidays = [];
                                        $displayDay = '';
                                        $currentDate = null;

                                        if ($isCurrentMonth) {
                                            $currentDate = \Carbon\Carbon::create($year, $monthPage->month_number, $dayNumber);
                                            $isToday = $currentDate->isSameDay($today);
                                            $hebrewDate = $hebrewDateService->toHebrewDayMonthString($currentDate);
                                            $dateKey = $currentDate->format('Y-m-d');
                                            $dayEvents = $eventsByDate[$dateKey] ?? [];
                                            $dayHolidays = $holidaysByDate[$dateKey] ?? [];
                                            $displayDay = $dayNumber;
                                        } elseif ($dayNumber < 1) {
                                            // Previous month
                                            if ($monthPage->show_adjacent_month_days) {
                                                $prevDay = $previousMonthDays + $dayNumber;
                                                $currentDate = \Carbon\Carbon::create($previousMonth->year, $previousMonth->month, $prevDay);
                                                $hebrewDate = $hebrewDateService->toHebrewDayMonthString($currentDate);
                                                $displayDay = $prevDay;
                                            }
                                        } else {
                                            // Next month
                                            if ($monthPage->show_adjacent_month_days) {
                                                $nextDay = $dayNumber - $totalDays;
                                                $currentDate = \Carbon\Carbon::create($nextMonth->year, $nextMonth->month, $nextDay);
                                                $hebrewDate = $hebrewDateService->toHebrewDayMonthString($currentDate);
                                                $displayDay = $nextDay;
                                            }
                                        }
                                    @endphp

                                    @if ($isCurrentMonth || ($monthPage->show_adjacent_month_days && !$isCurrentMonth))
                                        <a href="{{ route('calendars.day', [$calendar, $currentDate->format('Y-m-d')]) }}"
                                            class="day-cell min-h-16 sm:min-h-24 p-1.5 sm:p-2 rounded-lg cursor-pointer transition-colors border block {{ $isToday ? 'day-cell-today' : '' }} {{ !$isCurrentMonth ? 'day-cell-adjacent' : '' }}"
                                            @if ($isCurrentMonth) data-day="{{ $displayDay }}" @endif
                                            @if ($isToday)
                                                style="background-color: var(--color-accent); border-color: var(--color-accent); color: var(--color-white);"
                                            @elseif ($isCurrentMonth)
                                                style="background-color: {{ $styles['dayBox']['backgroundColor'] }}; border-color: var(--color-border); color: {{ $styles['dayBox']['fontColor'] }};"
                                            @else
                                                style="background-color: var(--color-bg); border-color: transparent; opacity: 0.6; color: var(--color-text-secondary);"
                                            @endif>

                                            <div class="flex justify-between items-start mb-1">
                                                <span class="day-number text-lg font-bold">{{ $displayDay }}</span>
                                                @if ($isToday)
                                                    <span class="text-xs font-medium" style="color: var(--color-white);">היום</span>
                                                @endif
                                            </div>

                                            <div class="day-hebrew text-xs mb-2" style="opacity: 0.7;">
                                                {{ $hebrewDate }}
                                            </div>

                                            @if ($isCurrentMonth && (!empty($dayHolidays) || !empty($dayEvents)))
                                                <div class="space-y-1">
                                                    @foreach ($dayHolidays as $holiday)
                                                        <div class="chip chip-holiday text-xs truncate" title="{{ $holiday['title'] ?? $holiday['hebrew'] ?? 'חג' }}">
                                                            {{ $holiday['title'] ?? $holiday['hebrew'] ?? 'חג' }}
                                                        </div>
                                                    @endforeach

                                                    @foreach ($dayEvents as $event)
                                                        @php
                                                            $chipClass = 'chip-event';
                                                            if ($event->event_type === 'birthday') $chipClass = 'chip-birthday';
                                                            elseif ($event->event_type === 'anniversary') $chipClass = 'chip-anniversary';
                                                        @endphp
                                                        <div class="chip {{ $chipClass }} text-xs truncate" title="{{ $event->display_title }}">
                                                            {{ $event->display_title }}
                                                        </div>
                                                    @endforeach

                                                    @if (count($dayHolidays) + count($dayEvents) > 3)
                                                        <div class="text-xs font-medium" style="color: var(--color-accent);">
                                                            +{{ count($dayHolidays) + count($dayEvents) - 3 }} עוד
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </a>
                                    @else
                                        <div class="min-h-24 rounded-lg"></div>
                                    @endif
                                @endfor
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customization Panel -->
                @include('calendars.partials.month-design-settings')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const grid = document.getElementById('calendarGrid');
            const overlay = document.getElementById('gridOverlay');
            const dayCells = document.querySelectorAll('.day-cell:not(.day-cell-today):not(.day-cell-adjacent)');

            const fontSelect = document.getElementById('font_choice');
            const overlaySlider = document.getElementById('overlay_opacity');
            const overlayValue = document.getElementById('overlayValue');
            const bgColor = document.getElementById('day_box_bg_color');
            const fontColor = document.getElementById('day_box_font_color');
            const weekdayColor = document.getElementById('weekday_color');
            const weekdayHeaders = document.querySelectorAll('.weekday-header');
            const bgOpacity = document.getElementById('day_box_bg_opacity');
            const dayBoxOpacityValue = document.getElementById('dayBoxOpacityValue');
            const fileInput = document.getElementById('custom_image_path');
            const filePreview = document.getElementById('customImagePreview');

            function hexToRgba(hex, alpha) {
                hex = hex.replace('#', '');
                const r = parseInt(hex.substr(0, 2), 16);
                const g = parseInt(hex.substr(2, 2), 16);
                const b = parseInt(hex.substr(4, 2), 16);
                return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
            }

            function applyDayBoxStyle() {
                const bg = bgColor ? bgColor.value : '#FFFFFF';
                const font = fontColor ? fontColor.value : '#2B2E3A';
                const opacity = bgOpacity ? bgOpacity.value : 100;
                const bgRgba = hexToRgba(bg, opacity / 100);

                dayCells.forEach(function (cell) {
                    cell.style.backgroundColor = bgRgba;
                    cell.style.color = font;
                    const number = cell.querySelector('.day-number');
                    const hebrew = cell.querySelector('.day-hebrew');
                    if (number) number.style.color = font;
                    if (hebrew) hebrew.style.color = font;
                });
            }

            if (fontSelect && grid) {
                const FONT_MAP = {
                    'default': "'Heebo', sans-serif",
                    'modern': "'Assistant', sans-serif",
                    'traditional': "'Frank Ruhl Libre', serif",
                    'elegant': "'Rubik', sans-serif"
                };

                const applyFont = function () {
                    grid.style.fontFamily = FONT_MAP[fontSelect.value] || FONT_MAP['default'];
                };

                fontSelect.addEventListener('input', applyFont);
                fontSelect.addEventListener('change', applyFont);
                applyFont();
            }

            if (overlaySlider && overlay) {
                overlaySlider.addEventListener('input', function () {
                    if (overlayValue) overlayValue.textContent = this.value;
                    overlay.style.backgroundColor = 'rgba(0, 0, 0, ' + (this.value / 100) + ')';
                });
            }

            if (bgColor) bgColor.addEventListener('input', applyDayBoxStyle);
            if (fontColor) fontColor.addEventListener('input', applyDayBoxStyle);

            if (weekdayColor) {
                weekdayColor.addEventListener('input', function () {
                    weekdayHeaders.forEach(function (header) {
                        header.style.color = this.value;
                    }.bind(this));
                });
            }

            if (bgOpacity) {
                bgOpacity.addEventListener('input', function () {
                    if (dayBoxOpacityValue) dayBoxOpacityValue.textContent = this.value;
                    applyDayBoxStyle();
                });
            }

            if (fileInput && grid) {
                fileInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (!file) return;

                    const url = URL.createObjectURL(file);
                    grid.style.backgroundImage = 'url(' + url + ')';
                    grid.style.backgroundSize = 'cover';
                    grid.style.backgroundPosition = 'center';
                    grid.style.backgroundRepeat = 'no-repeat';

                    if (filePreview) {
                        filePreview.src = url;
                        filePreview.classList.remove('hidden');
                    }
                });
            }

            const settingsForm = document.getElementById('designSettingsForm');
            const saveCta = document.getElementById('saveSettingsCta');

            function collectFormState(form) {
                const state = {};
                form.querySelectorAll('input, select, textarea').forEach(function (field) {
                    if (field.type === 'checkbox') {
                        state[field.name] = field.checked ? field.value : '';
                    } else if (field.type === 'file') {
                        state[field.name] = field.files.length;
                    } else {
                        state[field.name] = field.value;
                    }
                });
                return state;
            }

            if (settingsForm && saveCta) {
                const initialFormState = collectFormState(settingsForm);

                const syncSaveCta = function () {
                    const current = collectFormState(settingsForm);
                    let changed = false;
                    for (const key in current) {
                        if (current[key] !== initialFormState[key]) {
                            changed = true;
                            break;
                        }
                    }
                    saveCta.classList.toggle('hidden', !changed);
                };

                settingsForm.addEventListener('input', syncSaveCta);
                settingsForm.addEventListener('change', syncSaveCta);
            }

            // Scroll to a day requested via ?day= (from the dashboard day scroller)
            const requestedDay = new URLSearchParams(window.location.search).get('day');
            if (requestedDay) {
                const cell = document.querySelector('.day-cell[data-day="' + requestedDay + '"]');
                if (cell) {
                    cell.scrollIntoView({ block: 'nearest', inline: 'center' });
                    cell.style.boxShadow = '0 0 0 3px var(--color-accent-ring)';
                    setTimeout(function () {
                        cell.style.boxShadow = '';
                    }, 2000);
                }
            }
        });
    </script>
</x-app-layout>
