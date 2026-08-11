@php
    $profileActive = request()->routeIs('profile.*', 'calendars.wizard');

    $profileSubLinks = [
        ['label' => 'הגדרות פרופיל', 'route' => 'profile.edit', 'pattern' => 'profile.*', 'icon' => 'user'],
        ['label' => 'לוח שנה חדש', 'route' => 'calendars.wizard', 'pattern' => 'calendars.wizard', 'icon' => 'calendar'],
    ];

    $eventsCalendar = Auth::user()->mainCalendar();
@endphp

<div x-data="{ profileOpen: {{ $profileActive ? 'true' : 'false' }} }" class="flex flex-col h-full bg-white">
    <!-- User chip -->
    <div class="side-user flex items-center gap-3 px-4 py-4 border-b border-ink-200">
        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-ink-900 text-volt font-semibold text-sm shrink-0">
            {{ strtoupper(Str::substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div class="side-user-text min-w-0">
            <div class="text-sm font-medium text-ink-900 truncate">{{ Auth::user()->name }}</div>
            <div class="text-xs text-ink-500 truncate">{{ Auth::user()->email }}</div>
        </div>
    </div>

    <!-- Nav links -->
    <nav class="flex-1 overflow-y-auto py-4">
        <div class="px-3 space-y-1">
            <!-- Profile dropdown -->
            <div>
                <button type="button"
                        @click="if (window.innerWidth >= 1024 && $store.sidebar.collapsed) { $store.sidebar.toggle(); profileOpen = true; } else { profileOpen = !profileOpen; }"
                        class="side-item relative flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $profileActive ? 'bg-ink-100 text-ink-900' : 'text-ink-500 hover:bg-ink-100 hover:text-ink-900' }}"
                        :aria-expanded="String(profileOpen)"
                        title="פרופיל">
                    @if ($profileActive)
                        <span class="side-active-bar absolute inset-y-2 start-0 w-1 rounded-full bg-volt" aria-hidden="true"></span>
                        <span class="side-active-dot absolute inset-y-0 start-1 items-center" aria-hidden="true">
                            <span class="w-1.5 h-1.5 rounded-full bg-volt"></span>
                        </span>
                    @endif
                    <span class="shrink-0 {{ $profileActive ? 'text-ink-900' : 'text-ink-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <span class="side-label flex-1 text-start">פרופיל</span>
                    <svg class="side-chevron w-4 h-4 shrink-0 transition-transform duration-200 {{ $profileActive ? 'text-ink-900' : 'text-ink-400' }}" :class="profileOpen ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-cloak x-show="profileOpen" x-collapse>
                    <div class="side-submenu relative mt-1 ms-4 ps-4 border-s border-ink-200 space-y-1">
                        @foreach ($profileSubLinks as $link)
                            @if (Route::has($link['route']))
                                @php
                                    $isActive = request()->routeIs($link['pattern']);
                                @endphp
                                <a href="{{ route($link['route']) }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ $isActive ? 'text-ink-900' : 'text-ink-500 hover:bg-ink-100 hover:text-ink-900' }}">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $isActive ? 'bg-volt' : 'bg-ink-300' }}" aria-hidden="true"></span>
                                    <span class="shrink-0 {{ $isActive ? 'text-ink-900' : 'text-ink-400' }}">
                                        @switch($link['icon'])
                                            @case('calendar')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                @break
                                            @case('user')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                @break
                                        @endswitch
                                    </span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Events (standalone) -->
            @if ($eventsCalendar)
                @php
                    $eventsActive = request()->routeIs('calendar-events.*');
                @endphp
                <a href="{{ route('calendar-events.index', $eventsCalendar) }}"
                   class="side-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $eventsActive ? 'bg-ink-100 text-ink-900' : 'text-ink-500 hover:bg-ink-100 hover:text-ink-900' }}"
                   title="אירועים">
                    @if ($eventsActive)
                        <span class="side-active-bar absolute inset-y-2 start-0 w-1 rounded-full bg-volt" aria-hidden="true"></span>
                        <span class="side-active-dot absolute inset-y-0 start-1 items-center" aria-hidden="true">
                            <span class="w-1.5 h-1.5 rounded-full bg-volt"></span>
                        </span>
                    @endif
                    <span class="shrink-0 {{ $eventsActive ? 'text-ink-900' : 'text-ink-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4" />
                        </svg>
                    </span>
                    <span class="side-label">אירועים</span>
                </a>
            @endif

            <!-- Media (standalone) -->
            @if (Route::has('media.index'))
                @php
                    $mediaActive = request()->routeIs('media.*');
                @endphp
                <a href="{{ route('media.index') }}"
                   class="side-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $mediaActive ? 'bg-ink-100 text-ink-900' : 'text-ink-500 hover:bg-ink-100 hover:text-ink-900' }}"
                   title="הספרייה שלי">
                    @if ($mediaActive)
                        <span class="side-active-bar absolute inset-y-2 start-0 w-1 rounded-full bg-volt" aria-hidden="true"></span>
                        <span class="side-active-dot absolute inset-y-0 start-1 items-center" aria-hidden="true">
                            <span class="w-1.5 h-1.5 rounded-full bg-volt"></span>
                        </span>
                    @endif
                    <span class="shrink-0 {{ $mediaActive ? 'text-ink-900' : 'text-ink-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="side-label">הספרייה שלי</span>
                </a>
            @endif
        </div>
    </nav>

    <!-- Logout -->
    <div class="p-3 border-t border-ink-200">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="side-item flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-ink-500 hover:text-danger hover:bg-danger-light transition-colors" title="התנתק">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="side-label">התנתק</span>
            </button>
        </form>
    </div>

    <!-- Desktop collapse toggle -->
    <div class="hidden lg:block p-3 border-t border-ink-200">
        <button type="button" @click="$store.sidebar.toggle()"
                class="side-item flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-ink-500 hover:bg-ink-100 hover:text-ink-900 transition-colors"
                :aria-label="$store.sidebar.collapsed ? 'הרחבת תפריט' : 'כיווץ תפריט'">
            <svg x-cloak x-show="!$store.sidebar.collapsed" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5l7 7-7 7" />
            </svg>
            <svg x-cloak x-show="$store.sidebar.collapsed" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 19l-7-7 7-7" />
            </svg>
            <span class="side-label flex-1 text-start" x-text="$store.sidebar.collapsed ? 'הרחבת תפריט' : 'כיווץ תפריט'"></span>
        </button>
    </div>
</div>
