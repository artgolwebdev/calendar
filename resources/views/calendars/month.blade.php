<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('calendars.show', $calendar) }}" class="text-sm font-medium text-[#6B6B75] hover:text-[#1A1A1E] transition-colors">
                    ← חזור ללוח
                </a>
                <h1 class="text-2xl font-semibold text-[#1A1A1E]">
                    @php
                        $monthNames = [
                            1 => 'ינואר', 2 => 'פברואר', 3 => 'מרץ', 4 => 'אפריל',
                            5 => 'מאי', 6 => 'יוני', 7 => 'יולי', 8 => 'אוגוסט',
                            9 => 'ספטמבר', 10 => 'אוקטובר', 11 => 'נובמבר', 12 => 'דצמבר'
                        ];
                    @endphp
                    {{ $monthNames[$monthPage->month_number] }} {{ $year }}
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendars.month', [$calendar, $previousMonth->month, $previousMonth->year]) }}" 
                   class="btn btn-secondary w-9 h-9 !p-0 justify-center" aria-label="חודש קודם">
                    <svg class="w-4 h-4 text-[#1A1A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <a href="{{ route('calendars.month', [$calendar, $nextMonth->month, $nextMonth->year]) }}" 
                   class="btn btn-secondary w-9 h-9 !p-0 justify-center" aria-label="חודש הבא">
                    <svg class="w-4 h-4 text-[#1A1A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $styleService = app(\App\Services\MonthPageStyleService::class);
        $styles = $styleService->resolve($monthPage);
    @endphp

    <!-- Calendar Cover Image -->
    @if ($calendar->cover_image_path)
        <div class="h-48 bg-cover bg-center relative" style="background-image: url('{{ asset('storage/' . $calendar->cover_image_path) }}'); margin-top: -3rem; margin-bottom: 2rem;">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%);"></div>
        </div>
    @endif

    <style>
        .calendar-grid-container {
            font-family: {{ $styles['fontFamily'] }} !important;
        }
        
        .calendar-grid-background {
            {{ $styles['gridBackgroundStyle'] }}
            position: relative;
            min-height: 600px;
            background-color: var(--color-white);
        }
        
        .calendar-grid-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            {{ $styles['overlayStyle'] }}
            pointer-events: none;
            z-index: 1;
        }
        
        .calendar-grid-content {
            position: relative;
            z-index: 2;
        }
    </style>

    <div class="py-12">
        <div class="container">
            @if (session('success'))
                <div class="mb-4 card p-4" style="background-color: #F0FFF4; border-color: #C6F6D5;">
                    <p style="color: #276749;">{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Calendar Grid -->
                <div class="lg:col-span-3">
                    <div class="p-6 calendar-grid-container" 
                         style="min-height: 600px; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #E8E6E1; position: relative; font-family: {{ $styles['fontFamily'] }}; {{ $styles['gridBackgroundStyle'] }}">
                        <div class="calendar-grid-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; {{ $styles['overlayStyle'] }} pointer-events: none; z-index: 1;"></div>
                        <div class="calendar-grid-content" style="position: relative; z-index: 2;">
                        <!-- Weekday Headers -->
                        <div class="grid grid-cols-7 gap-2 mb-4">
                            @foreach(['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'] as $day)
                                <div class="text-center text-sm font-medium" style="color: var(--color-text-secondary);">
                                    {{ $day }}
                                </div>
                            @endforeach
                        </div>

                        <!-- Calendar Grid -->
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
                                $dateKey = $event->event_date->format('Y-m-d');
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

                        <div class="grid grid-cols-7 gap-2">
                            @for ($i = 0; $i < 42; $i++)
                                @php
                                    $dayNumber = $i - $startDayOfWeek + 1;
                                    $isCurrentMonth = $dayNumber >= 1 && $dayNumber <= $totalDays;
                                    $isToday = false;
                                    $hebrewDate = '';
                                    $dayEvents = [];
                                    $dayHolidays = [];
                                    
                                    if ($isCurrentMonth) {
                                        $currentDate = \Carbon\Carbon::create($year, $monthPage->month_number, $dayNumber);
                                        $isToday = $currentDate->isSameDay($today);
                                        $hebrewDate = $hebrewDateService->toHebrewString($currentDate);
                                        $dateKey = $currentDate->format('Y-m-d');
                                        $dayEvents = $eventsByDate[$dateKey] ?? [];
                                        $dayHolidays = $holidaysByDate[$dateKey] ?? [];
                                    } elseif ($dayNumber < 1) {
                                        // Previous month
                                        if ($monthPage->show_adjacent_month_days) {
                                            $prevDay = $previousMonthDays + $dayNumber;
                                            $currentDate = \Carbon\Carbon::create($previousMonth->year, $previousMonth->month, $prevDay);
                                            $hebrewDate = $hebrewDateService->toHebrewString($currentDate);
                                        }
                                    } else {
                                        // Next month
                                        if ($monthPage->show_adjacent_month_days) {
                                            $nextDay = $dayNumber - $totalDays;
                                            $currentDate = \Carbon\Carbon::create($nextMonth->year, $nextMonth->month, $nextDay);
                                            $hebrewDate = $hebrewDateService->toHebrewString($currentDate);
                                        }
                                    }
                                @endphp

                                @if ($isCurrentMonth || ($monthPage->show_adjacent_month_days && !$isCurrentMonth))
                                    <a href="{{ route('calendar-events.create', $calendar) }}?date={{ $currentDate->format('Y-m-d') }}"
                                       class="min-h-24 p-2 rounded-lg cursor-pointer transition-colors border block"
                                       @if ($isCurrentMonth)
                                           @if ($isToday)
                                               style="background-color: var(--color-accent); border-color: var(--color-accent); color: var(--color-white);"
                                           @else
                                               style="background-color: {{ $styles['dayBoxStyle']['backgroundColor'] }}; border-color: var(--color-border); color: {{ $styles['dayBoxStyle']['fontColor'] }}; hover: border-color: var(--color-accent);"
                                           @endif
                                       @else
                                           style="background-color: var(--color-bg); border-color: transparent; opacity: 0.6; color: var(--color-text-secondary);"
                                       @endif>
                                        
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-lg font-bold" 
                                                  @if ($isToday) style="color: var(--color-white);" @else style="color: {{ $styles['dayBoxStyle']['fontColor'] }};" @endif>
                                                {{ $isCurrentMonth ? $dayNumber : ($dayNumber < 1 ? $previousMonthDays + $dayNumber : $dayNumber - $totalDays) }}
                                            </span>
                                            @if ($isToday)
                                                <span class="text-xs font-medium" style="color: var(--color-white);">היום</span>
                                            @endif
                                        </div>
                                        
                                        <div class="text-xs mb-2" 
                                             @if ($isToday) style="color: rgba(255, 255, 255, 0.9);" @else style="color: {{ $styles['dayBoxStyle']['fontColor'] }}; opacity: 0.7;" @endif>
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
                                                    <div class="chip {{ $chipClass }} text-xs truncate" title="{{ $event->title }}">
                                                        {{ $event->title }}
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
                                    <div class="min-h-24 p-2 rounded-lg border" style="background-color: transparent; border-color: transparent;"></div>
                                @endif
                            @endfor
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Customization Panel -->
                <div class="lg:col-span-1">
                    <div class="card p-5 sticky top-4">
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-[#E5E5E8]">
                            <h3 class="text-base font-semibold text-[#1A1A1E]">הגדרות עיצוב</h3>
                            <button onclick="toggleCustomizePanel()" class="text-xs font-medium text-[#4F46E5] hover:text-[#4338CA] transition-colors">
                                הסתר
                            </button>
                        </div>
                        
                        <div id="customizePanel" style="display: block;">
                            <form action="{{ route('month-pages.update', [$calendar, $monthPage]) }}" method="POST" enctype="multipart/form-data" id="customizeForm" class="space-y-4">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                
                                <div>
                                    <label for="font_choice" class="label">גופן הלוח</label>
                                    <select name="font_choice" id="font_choice" class="input">
                                        <option value="default" {{ $monthPage->font_choice === 'default' ? 'selected' : '' }}>ברירת מחדל</option>
                                        <option value="modern" {{ $monthPage->font_choice === 'modern' ? 'selected' : '' }}>מודרני</option>
                                        <option value="traditional" {{ $monthPage->font_choice === 'traditional' ? 'selected' : '' }}>מסורתי</option>
                                        <option value="elegant" {{ $monthPage->font_choice === 'elegant' ? 'selected' : '' }}>אלגנטי</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="custom_image_path" class="label">תמונה מותאמת אישית</label>
                                    <input type="file" name="custom_image_path" id="custom_image_path" class="input pt-1.5 cursor-pointer" accept="image/*">
                                    @if ($monthPage->custom_image_path)
                                        <div class="mt-2 flex items-center justify-between p-2 bg-[#F7F7F8] border border-[#E5E5E8] rounded-lg">
                                            <img src="{{ asset('storage/' . $monthPage->custom_image_path) }}" alt="תמונה נוכחית" class="h-12 w-16 object-cover rounded border border-[#E5E5E8]">
                                            <a href="{{ route('month-pages.remove-image', [$calendar, $monthPage]) }}" class="text-xs font-medium text-[#DC2626] hover:text-[#B91C1C] transition-colors">
                                                הסר תמונה
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label for="overlay_opacity" class="label flex justify-between">
                                        <span>אטימת רקע</span>
                                        <span class="text-xs font-normal text-[#6B6B75]"><span id="opacityValue">{{ $monthPage->overlay_opacity ?? 30 }}</span>%</span>
                                    </label>
                                    <input type="range" name="overlay_opacity" id="overlay_opacity" min="0" max="100" step="5"
                                        class="w-full accent-[#4F46E5]"
                                        value="{{ $monthPage->overlay_opacity ?? 30 }}"
                                        oninput="document.getElementById('opacityValue').textContent = this.value">
                                    <div class="flex justify-between text-[11px] text-[#6B6B75] mt-1">
                                        <span>בהיר</span>
                                        <span>כהה</span>
                                    </div>
                                </div>

                                <div>
                                    <label for="day_box_bg_color" class="label">צבע רקע לתא היום</label>
                                    <input type="color" name="day_box_bg_color" id="day_box_bg_color" class="w-full h-9 rounded-lg border border-[#E5E5E8] p-1 cursor-pointer bg-white"
                                        value="{{ $monthPage->day_box_bg_color ?? '#FFFFFF' }}">
                                </div>

                                <div>
                                    <label for="day_box_font_color" class="label">צבע טקסט לתא היום</label>
                                    <input type="color" name="day_box_font_color" id="day_box_font_color" class="w-full h-9 rounded-lg border border-[#E5E5E8] p-1 cursor-pointer bg-white"
                                        value="{{ $monthPage->day_box_font_color ?? '#2B2E3A' }}">
                                </div>

                                <div>
                                    <label for="day_box_bg_opacity" class="label flex justify-between">
                                        <span>אטימת רקע תא היום</span>
                                        <span class="text-xs font-normal text-[#6B6B75]"><span id="dayBoxOpacityValue">{{ $monthPage->day_box_bg_opacity ?? 100 }}</span>%</span>
                                    </label>
                                    <input type="range" name="day_box_bg_opacity" id="day_box_bg_opacity" min="0" max="100" step="5"
                                        class="w-full accent-[#4F46E5]"
                                        value="{{ $monthPage->day_box_bg_opacity ?? 100 }}"
                                        oninput="document.getElementById('dayBoxOpacityValue').textContent = this.value">
                                    <div class="flex justify-between text-[11px] text-[#6B6B75] mt-1">
                                        <span>שקוף</span>
                                        <span>מוצק</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="show_adjacent_month_days" id="show_adjacent_month_days" class="rounded border-[#E5E5E8] text-[#4F46E5] focus:ring-[#4F46E5]"
                                            {{ $monthPage->show_adjacent_month_days ? 'checked' : '' }}>
                                        <span class="text-sm font-medium text-[#1A1A1E]">הצג ימים מהחודש הקודם/הבא</span>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary w-full justify-center mt-2">
                                    עדכן הגדרות
                                </button>
                            </form>

                            @if ($monthPage->background_image_path || $monthPage->custom_image_path)
                                <div class="mt-4 p-3 rounded-lg bg-[#F7F7F8] border border-[#E5E5E8]">
                                    <p class="text-xs font-medium text-[#6B6B75] mb-2">תמונת רקע נוכחית:</p>
                                    @if ($monthPage->custom_image_path)
                                        <img src="{{ asset('storage/' . $monthPage->custom_image_path) }}" alt="תמונה מותאמת אישית" class="w-full h-28 object-cover rounded-md border border-[#E5E5E8]">
                                    @elseif ($monthPage->background_image_path)
                                        <img src="{{ asset('storage/' . $monthPage->background_image_path) }}" alt="תמונת רקע" class="w-full h-28 object-cover rounded-md border border-[#E5E5E8]">
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDayModal(date) {
            // Direct redirect to create form with date pre-filled
            const url = "{{ route('calendar-events.create', $calendar) }}?date=" + encodeURIComponent(date);
            window.location.href = url;
        }
        
        function toggleCustomizePanel() {
            const panel = document.getElementById('customizePanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
        
        // Real-time opacity preview
        document.addEventListener('DOMContentLoaded', function() {
            // Overlay opacity preview
            const overlaySlider = document.getElementById('overlay_opacity');
            if (overlaySlider) {
                overlaySlider.addEventListener('input', function() {
                    const overlay = document.querySelector('.calendar-grid-overlay');
                    if (overlay) {
                        overlay.style.backgroundColor = 'rgba(0, 0, 0, ' + (this.value / 100) + ')';
                    }
                });
            }
            
            // Day box opacity preview
            const dayBoxOpacitySlider = document.getElementById('day_box_bg_opacity');
            const dayBoxBgColorPicker = document.getElementById('day_box_bg_color');
            
            if (dayBoxOpacitySlider) {
                dayBoxOpacitySlider.addEventListener('input', function() {
                    console.log('Day box opacity changed to:', this.value);
                    updateDayBoxOpacity(this.value);
                });
            }
            
            // Also update when color changes
            if (dayBoxBgColorPicker) {
                dayBoxBgColorPicker.addEventListener('input', function() {
                    const opacity = dayBoxOpacitySlider ? dayBoxOpacitySlider.value : 100;
                    updateDayBoxOpacity(opacity);
                });
            }
            
            // Log checkbox change
            const adjacentDaysCheckbox = document.getElementById('show_adjacent_month_days');
            if (adjacentDaysCheckbox) {
                adjacentDaysCheckbox.addEventListener('change', function() {
                    console.log('Adjacent days checkbox changed to:', this.checked);
                });
            }
            
            function updateDayBoxOpacity(opacity) {
                const bgColor = dayBoxBgColorPicker ? dayBoxBgColorPicker.value : '#FFFFFF';
                const rgba = hexToRgba(bgColor, opacity / 100);
                console.log('Updating day boxes with color:', bgColor, 'and opacity:', opacity, 'result:', rgba);
                
                // Find all day cells in the calendar grid
                const dayCells = document.querySelectorAll('.calendar-grid-content a');
                console.log('Found', dayCells.length, 'day cells');
                
                dayCells.forEach(function(cell) {
                    // Get current background color
                    const currentBg = cell.style.backgroundColor;
                    console.log('Current background:', currentBg);
                    
                    // Skip if it's today (accent color) or other month (gray)
                    if (currentBg.includes('var(--color-accent)') || currentBg.includes('var(--color-bg)')) {
                        console.log('Skipping special cell');
                        return;
                    }
                    
                    // Apply the new background color with opacity
                    cell.style.backgroundColor = rgba;
                    console.log('Applied new background:', rgba);
                });
            }
            
            function hexToRgba(hex, alpha) {
                // Remove hash if present
                hex = hex.replace('#', '');
                
                // Parse hex
                const r = parseInt(hex.substr(0, 2), 16);
                const g = parseInt(hex.substr(2, 2), 16);
                const b = parseInt(hex.substr(4, 2), 16);
                
                return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
            }
        });
    </script>
</x-app-layout>