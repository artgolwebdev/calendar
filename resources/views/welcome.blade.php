<!DOCTYPE html>
<html lang="he" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'לוח שנה משפחתי') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&family=Heebo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-ink-50 font-sans antialiased text-ink-900">
        <div class="min-h-screen flex flex-col">
            <!-- Navigation -->
            <nav class="bg-white border-b border-ink-200">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <a href="/" class="flex items-center gap-2 font-bold text-ink-950 whitespace-nowrap shrink-0">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-ink-950 text-volt">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <path d="M16 2v4M8 2v4M3 10h18" />
                                </svg>
                            </span>
                            לוח שנה משפחתי
                        </a>
                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                                    לוח בקרה
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="link">התחברות</a>
                                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                                    הרשמה
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero -->
            <main class="flex-grow py-12 sm:py-16">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-10">
                        <h1 class="page-title text-3xl sm:text-4xl font-bold mb-3">
                            לוח שנה משפחתי בעברית
                        </h1>
                        <p class="page-subtitle max-w-xl mx-auto">
                            ימי הולדת, ימי נישואין וחגים ישראליים — הכל במקום אחד
                        </p>
                        @guest
                            <div class="flex flex-wrap justify-center items-center gap-3 mt-6">
                                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                                    צור חשבון
                                </a>
                                <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
                                    התחברות
                                </a>
                            </div>
                        @endauth
                    </div>

                    {{-- Public holidays-only month preview --}}
                    <div class="card p-4 sm:p-6 max-w-4xl mx-auto">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h2 class="section-title">
                                {{ $monthNames[$month] }} {{ $year }}
                            </h2>
                            <span class="text-sm font-medium text-ink-500 whitespace-nowrap">
                                · {{ $hebrewMonthName }} {{ $hebrewYear }}
                            </span>
                        </div>

                        <div class="relative" x-data="calendarPreviewScroll()">
                            <div dir="ltr"
                                x-ref="scroller"
                                @scroll.passive="onScroll()"
                                class="overflow-x-auto overscroll-x-contain scroll-smooth md:overflow-visible">
                                <div class="min-w-[34rem] md:min-w-0">
                                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2 mb-4">
                                @foreach (['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'] as $day)
                                    <div class="text-center text-sm font-medium text-ink-500">{{ $day }}</div>
                                @endforeach
                            </div>

                            @php
                                $firstDayOfMonth = \Carbon\Carbon::create($year, $month, 1);
                                $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
                                $startDayOfWeek = $firstDayOfMonth->dayOfWeek;
                                $totalDays = $lastDayOfMonth->day;
                                $today = \Carbon\Carbon::today();
                                $hebrewDateService = app(\App\Services\HebrewDateService::class);
                            @endphp

                            <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                                @for ($i = 0; $i < 42; $i++)
                                    @php
                                        $dayNumber = $i - $startDayOfWeek + 1;
                                        $isCurrentMonth = $dayNumber >= 1 && $dayNumber <= $totalDays;
                                        $isToday = false;
                                        $hebrewDate = '';
                                        $dayHolidays = [];

                                        if ($isCurrentMonth) {
                                            $currentDate = \Carbon\Carbon::create($year, $month, $dayNumber);
                                            $isToday = $currentDate->isSameDay($today);
                                            $hebrewDate = $hebrewDateService->toHebrewDayMonthString($currentDate);
                                            $dateKey = $currentDate->format('Y-m-d');
                                            $dayHolidays = $holidaysByDate[$dateKey] ?? [];
                                        }
                                    @endphp

                                    @if ($isCurrentMonth)
                                        <div class="rounded-lg border p-1.5 sm:p-2 min-h-16 sm:min-h-20 {{ $isToday ? 'bg-ink-950 border-ink-950' : 'bg-white border-ink-200' }}">
                                            <div class="flex justify-between items-start mb-1">
                                                <span class="text-lg font-bold {{ $isToday ? 'text-volt' : 'text-ink-900' }}">{{ $dayNumber }}</span>
                                                @if ($isToday)
                                                    <span class="text-xs font-medium text-volt">היום</span>
                                                @endif
                                            </div>
                                            <div class="text-xs mb-2 text-ink-500">
                                                {{ $hebrewDate }}
                                            </div>
                                            @if (!empty($dayHolidays))
                                                <div class="space-y-1">
                                                    @foreach ($dayHolidays as $holiday)
                                                        <div class="chip chip-holiday text-xs truncate" title="{{ $holiday['title'] ?? $holiday['hebrew'] ?? 'חג' }}">
                                                            {{ $holiday['title'] ?? $holiday['hebrew'] ?? 'חג' }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="min-h-16 sm:min-h-20 rounded-lg"></div>
                                    @endif
                                @endfor
                            </div>
                                </div>

                                {{-- Mobile scroll affordances (hidden on desktop) --}}
                                <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-white to-transparent md:hidden" aria-hidden="true"></div>
                                <div class="pointer-events-none absolute inset-y-0 left-0 w-8 bg-gradient-to-r from-white to-transparent md:hidden" aria-hidden="true"></div>

                                <div x-cloak x-show="showIndicator" x-transition.opacity.duration.200ms
                                    class="absolute inset-y-0 right-2 flex items-center pointer-events-none md:hidden" aria-hidden="true">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-ink-950/85 text-volt shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-ink-200 py-6">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <p class="text-center text-sm text-ink-500">
                        © {{ date('Y') }} לוח שנה משפחתי. כל הזכויות שמורות.
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>
