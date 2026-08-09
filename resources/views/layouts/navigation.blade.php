<div x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="bg-white">
    <!-- Top Navbar -->
    <nav class="sticky top-0 z-50 bg-white border-b border-ink-200">
        <div class="flex items-center justify-between h-16 px-4 lg:px-6 lg:ps-72">
            <div class="flex items-center gap-2 sm:gap-6 min-w-0">
                <!-- Brand -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-ink-900 text-volt text-sm font-bold">ל</span>
                    <span class="text-base font-semibold text-ink-900 whitespace-nowrap">לוח שנה משפחתי</span>
                </a>

                <!-- Dashboard Link -->
                <a href="{{ route('dashboard') }}"
                   class="hidden sm:inline-flex items-center h-16 border-b-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'border-volt text-ink-900' : 'border-transparent text-ink-500 hover:text-ink-900' }}">
                    לוח בקרה
                </a>
            </div>

            <!-- Hamburger (mobile/tablet) -->
            <button type="button" @click="menuOpen = !menuOpen"
                    class="lg:hidden inline-flex items-center justify-center p-2 -me-2 rounded-lg text-ink-500 hover:bg-ink-100 hover:text-ink-900 transition-colors"
                    aria-label="תפריט">
                <svg x-show="!menuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="menuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
    </nav>

    <!-- Desktop Permanent Side Panel -->
    <aside class="hidden lg:block lg:fixed lg:inset-y-0 lg:right-0 lg:w-72 lg:bg-white lg:border-s lg:border-ink-200 lg:z-40">
        <div class="h-full pt-20">
            @include('layouts.partials.side-menu-content')
        </div>
    </aside>

    <!-- Mobile Offcanvas Backdrop -->
    <div x-cloak x-show="menuOpen" @click="menuOpen = false"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-ink-950/40 backdrop-blur-sm lg:hidden"></div>

    <!-- Mobile Offcanvas Panel -->
    <div x-cloak x-show="menuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 z-50 w-72 max-w-[85vw] lg:hidden shadow-2xl">
        @include('layouts.partials.side-menu-content')
    </div>
</div>
