{{-- Scope: 'month' applies to the current month only; 'year' applies to all months --}}
@php
    $isMonthScope = ($themesScope ?? 'month') === 'month';
@endphp

{{-- Backdrop --}}
<div x-cloak x-show="themesOpen" @click="themesOpen = false"
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-ink-950/40"></div>

{{-- Bottom offcanvas (mobile) / right-side offcanvas (desktop) panel --}}
<div x-cloak x-show="themesOpen" role="dialog" aria-modal="true" aria-labelledby="themesTitle"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full lg:translate-y-0 lg:translate-x-full"
     x-transition:enter-end="translate-y-0 lg:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 lg:translate-x-0"
     x-transition:leave-end="translate-y-full lg:translate-y-0 lg:translate-x-full"
     class="fixed inset-x-0 bottom-0 z-50 flex flex-col bg-white rounded-t-3xl max-h-[80vh] shadow-2xl lg:inset-x-auto lg:top-0 lg:right-0 lg:bottom-auto lg:h-full lg:max-h-none lg:w-[26rem] lg:max-w-[90vw] lg:rounded-t-none lg:rounded-l-3xl">

    {{-- Drag handle (mobile only) --}}
    <div class="shrink-0 flex justify-center pt-3 pb-2 lg:hidden" aria-hidden="true">
        <span class="block w-10 h-1.5 rounded-full bg-ink-300"></span>
    </div>

    {{-- Sticky header --}}
    <div class="shrink-0 px-6 pb-4 flex items-center justify-between border-b border-ink-200">
        <div>
            <h3 id="themesTitle" class="text-lg font-bold text-ink-950">נושאים</h3>
            <p class="text-xs text-ink-500 mt-0.5">
                {{ $isMonthScope ? 'עיצוב לחודש זה בלבד' : 'עיצוב אחיד לכל חודשי הלוח' }}
            </p>
        </div>
        <button type="button" @click="themesOpen = false"
            class="inline-flex items-center justify-center w-9 h-9 rounded-full text-ink-500 hover:bg-ink-100 hover:text-ink-900 transition-colors"
            aria-label="סגור">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>

    {{-- Scrollable body --}}
    <div class="flex-1 min-h-0 overflow-y-auto px-6 py-5 space-y-3">
        @foreach (config('themes', []) as $key => $theme)
            <button type="button"
                @click="selectTheme('{{ $key }}')"
                class="w-full text-right rounded-xl border border-ink-200 bg-white p-4 transition-colors hover:border-volt-500 hover:shadow-sm"
                :class="pendingTheme === '{{ $key }}' ? 'ring-2 ring-volt-500 border-volt-500' : ''"
                aria-label="החל נושא {{ $theme['name'] }}">
                <span class="flex items-center gap-4">
                    <span class="shrink-0 w-14 h-14 rounded-lg border border-ink-200 flex flex-col items-center justify-center gap-1"
                        style="background-color: {{ $theme['day_box_bg_color'] }};">
                        <span class="block h-1.5 w-8 rounded-full"
                            style="background-color: {{ $theme['day_box_font_color'] }};"></span>
                        <span class="block h-1.5 w-5 rounded-full opacity-60"
                            style="background-color: {{ $theme['day_box_font_color'] }};"></span>
                    </span>
                    <span class="flex-1">
                        <span class="block text-sm font-bold text-ink-950">{{ $theme['name'] }}</span>
                        <span class="mt-2 flex items-center gap-1.5">
                            @foreach ([$theme['day_box_bg_color'], $theme['weekday_color'], $theme['day_box_font_color']] as $color)
                                <span class="inline-block w-5 h-5 rounded-full border border-ink-200"
                                    style="background-color: {{ $color }};"></span>
                            @endforeach
                        </span>
                    </span>
                </span>
            </button>
        @endforeach

        <p class="pt-2 text-xs text-ink-400 leading-relaxed">
            @if ($isMonthScope)
                הנושא יחול על החודש הנוכחי בלבד ויחליף את הגדרות העיצוב שלו.
                תמונת הרקע האישית של החודש תישמר.
            @else
                הנושא יחול על כל חודשי הלוח ויחליף את הגדרות העיצוב הנוכחיות.
                תמונות רקע אישיות של חודשים יישמרו.
            @endif
        </p>
    </div>

    {{-- Sticky confirm footer --}}
    <div x-cloak x-show="pendingTheme" class="shrink-0 px-6 py-4 border-t border-ink-200 bg-white">
        <p class="text-sm font-bold text-ink-950">
            @if ($isMonthScope)
                להחיל את הנושא "<span x-text="pendingName"></span>" על החודש הזה?
            @else
                להחיל את הנושא "<span x-text="pendingName"></span>" על כל החודשים?
            @endif
        </p>
        <p class="mt-1 text-xs text-ink-500">
            @if ($isMonthScope)
                פעולה זו תחליף את הגדרות העיצוב הנוכחיות של החודש הנוכחי בלבד.
            @else
                פעולה זו תחליף את הגדרות העיצוב הנוכחיות של כל 12 החודשים.
            @endif
        </p>
        <div class="mt-3 flex gap-2">
            <button type="button" @click="cancelTheme()" :disabled="applying"
                class="btn btn-secondary flex-1 font-bold">
                בטל
            </button>
            <button type="button" id="applyThemeBtn" @click="applyTheme(pendingTheme)" :disabled="applying"
                class="flex-1 btn bg-ink-950 text-white font-bold hover:bg-ink-800">
                <span x-show="applying" x-cloak>מיישם...</span>
                <span x-show="!applying" x-cloak>החל נושא</span>
            </button>
        </div>
        <p x-cloak x-show="applyError" class="mt-2 text-xs font-medium text-danger" x-text="applyError"></p>
    </div>
</div>
