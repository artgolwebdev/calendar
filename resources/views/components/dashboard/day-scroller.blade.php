@props([
    'days',
    'mainCalendar',
    'month',
    'year',
])

@if ($mainCalendar && $days->isNotEmpty())
    <div class="day-scroller" x-data="dayScroller()" x-init="init()">
        <div class="card relative overflow-hidden p-3 sm:p-4">
            <div class="flex items-center justify-between gap-2 px-1 mb-3">
                <span class="chip chip-event text-[10px] shrink-0">
                    לוח ראשי: {{ $mainCalendar->name }}
                </span>
                <a href="{{ route('calendars.month', [$mainCalendar, $month, $year]) }}"
                    class="btn btn-ghost btn-sm shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    החודש המלא
                </a>
            </div>

            <div class="relative">
                <button type="button" aria-label="ימים מוקדמים יותר"
                    @click="scrollDayStrip('prev')"
                    class="day-scroller-arrow hidden sm:flex absolute right-1.5 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-[#E5E5E8] shadow-sm items-center justify-center text-[#6B6B75] hover:text-[#4F46E5] hover:border-[#D4D4D8] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <button type="button" aria-label="ימים מאוחרים יותר"
                    @click="scrollDayStrip('next')"
                    class="day-scroller-arrow hidden sm:flex absolute left-1.5 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-[#E5E5E8] shadow-sm items-center justify-center text-[#6B6B75] hover:text-[#4F46E5] hover:border-[#D4D4D8] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div x-ref="track"
                    class="day-scroller-track bg-[#F3F4F6] rounded-xl flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-smooth px-10 py-2.5">
                    @foreach ($days as $day)
                        <a href="{{ route('calendars.month', [$mainCalendar, $day['month'], $day['year']]) }}?day={{ $day['day'] }}"
                            class="day-scroller-card shrink-0 w-36 sm:w-40 md:w-44 h-44 sm:h-48 md:h-52 lg:h-56 snap-center rounded-lg flex flex-col items-stretch p-3 sm:p-4 transition-colors {{ $day['is_today'] ? 'bg-[#4F46E5] shadow-md shadow-[#4F46E5]/25' : 'bg-white' }}"
                            @if ($day['is_today']) data-day-scroll-target="today" @endif>
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-sm font-semibold {{ $day['is_today'] ? 'text-white/85' : 'text-[#6B6B75]' }}">{{ $day['weekday'] }}</span>
                                @if ($day['is_today'])
                                    <span class="text-xs font-medium text-white">היום</span>
                                @endif
                            </div>

                            <div class="flex flex-col items-center justify-center flex-1 min-h-0">
                                <span class="text-4xl sm:text-5xl font-bold leading-none {{ $day['is_today'] ? 'text-white' : 'text-[#1A1A1E]' }}">{{ $day['day'] }}</span>
                                <span class="day-hebrew text-xs sm:text-sm mt-1.5 {{ $day['is_today'] ? 'text-white/75' : 'text-[#6B6B75]' }}">{{ $day['hebrew_date'] }}</span>
                            </div>

                            <div class="flex flex-col gap-1 min-h-14 justify-start mt-2">
                                @foreach ($day['items']->take(2) as $item)
                                    @php
                                        $chipClass = match ($item['type']) {
                                            'holiday' => 'chip-holiday',
                                            'birthday' => 'chip-birthday',
                                            'anniversary' => 'chip-anniversary',
                                            default => 'chip-event',
                                        };
                                    @endphp
                                    <span class="chip {{ $chipClass }} text-xs truncate w-full justify-center" title="{{ $item['title'] }}">
                                        {{ $item['title'] }}
                                    </span>
                                @endforeach

                                @if ($day['total'] > 2)
                                    <span class="text-xs font-medium text-center {{ $day['is_today'] ? 'text-white/80' : 'text-[#4F46E5]' }}">
                                        +{{ $day['total'] - 2 }} עוד
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<style>
    .day-scroller-track {
        cursor: grab;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .day-scroller-track.is-dragging {
        cursor: grabbing;
    }

    .day-scroller-track::-webkit-scrollbar {
        display: none;
    }

    .day-scroller-card {
        will-change: transform, opacity;
    }

    @media (prefers-reduced-motion: reduce) {
        .day-scroller-track {
            scroll-behavior: auto;
        }
    }
</style>

<script>
    function dayScroller() {
        return {
            track: null,
            usesNegativeScroll: false,
            frame: null,
            drag: null,
            suppressClick: false,
            init() {
                this.track = this.$refs.track;
                this.usesNegativeScroll = this.detectsNegativeScroll();

                // Desktop-only click-and-drag scrolling; touch is left to native swipe.
                this.pointerMove = (e) => this.onPointerMove(e);
                this.pointerUp = (e) => this.onPointerUp(e);
                this.track.addEventListener('pointerdown', (e) => this.onPointerDown(e));
                this.track.addEventListener('click', (e) => this.onTrackClick(e), true);

                this.$nextTick(() => {
                    this.centerOnToday();
                    this.track.addEventListener('scroll', () => this.onScroll(), { passive: true });
                    this.applyFocus();
                });
            },
            maxScroll() {
                return this.track.scrollWidth - this.track.clientWidth;
            },
            detectsNegativeScroll() {
                const track = this.track;
                const behavior = track.style.scrollBehavior;
                const snap = track.style.scrollSnapType;

                // Disable snap while probing: with scroll-snap-type active, the
                // browser snaps a scrollLeft of -1 to 0, corrupting the result.
                track.style.scrollBehavior = 'auto';
                track.style.scrollSnapType = 'none';
                track.scrollLeft = -1;
                const usesNegative = track.scrollLeft === -1;
                track.scrollLeft = 0;
                track.style.scrollBehavior = behavior;
                track.style.scrollSnapType = snap;

                return usesNegative;
            },
            // Physical cursor delta -> container scrollLeft delta.
            //
            // Diagnosed in Edge and Firefox (native wheel, no custom JS): this
            // strip's RTL scrollLeft runs from 0 (start / earliest days) down to
            // -max (end / latest days), and mirrors the input directly
            // (scrolling right adds to scrollLeft, back toward 0). So a rightward
            // drag (positive delta) increases scrollLeft and reveals earlier
            // days; a leftward drag decreases it and reveals later days.
            // Legacy non-negative implementations mirror the sign.
            physicalDeltaToScrollLeft(delta) {
                return this.usesNegativeScroll ? delta : -delta;
            },
            // Centers the current day. Uses scrollIntoView, which is
            // direction-agnostic: the browser resolves the center in its own
            // scrollLeft convention, so no manual sign math is involved.
            centerOnToday() {
                const target = this.track.querySelector('[data-day-scroll-target="today"]');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            },
            // direction: 'next' = later days (left side in RTL), 'prev' = earlier
            // days (right side). Picks the card at the strip's leading edge and
            // centers it via scrollIntoView, keeping a single sign-agnostic path
            // for both arrow buttons (and the same primitive the auto-scroll uses).
            scrollDayStrip(direction) {
                const cards = Array.from(this.track.querySelectorAll('.day-scroller-card'));
                const viewport = this.track.getBoundingClientRect();

                let target = null;
                let bestDist = Infinity;

                for (const card of cards) {
                    const rect = card.getBoundingClientRect();
                    const center = rect.left + rect.width / 2;
                    const dist = direction === 'next' ? center - viewport.left : viewport.right - center;
                    if (dist < 0) continue;
                    if (dist < bestDist) {
                        bestDist = dist;
                        target = card;
                    }
                }

                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            },
            onPointerDown(e) {
                this.suppressClick = false;

                const isMouse = !e.pointerType || e.pointerType === 'mouse';
                if (!isMouse || e.button !== 0) return;
                if (this.maxScroll() <= 0) return;

                e.preventDefault();

                this.drag = {
                    pointerId: e.pointerId,
                    startX: e.clientX,
                    startScrollLeft: this.track.scrollLeft,
                    moved: false,
                };

                this.track.classList.add('is-dragging');
                this.track.style.userSelect = 'none';
                this.track.style.scrollBehavior = 'auto';
                this.track.style.scrollSnapType = 'none';
                this.track.setPointerCapture(e.pointerId);

                this.track.addEventListener('pointermove', this.pointerMove);
                this.track.addEventListener('pointerup', this.pointerUp);
                this.track.addEventListener('pointercancel', this.pointerUp);
            },
            onPointerMove(e) {
                const drag = this.drag;
                if (!drag || e.pointerId !== drag.pointerId) return;

                const delta = e.clientX - drag.startX;
                if (Math.abs(delta) > 4) {
                    drag.moved = true;
                }

                // Mirrors the confirmed native RTL convention: a rightward drag
                // (positive delta) increases scrollLeft toward 0 and reveals
                // earlier days; a leftward drag reveals later days.
                this.track.scrollLeft = drag.startScrollLeft + this.physicalDeltaToScrollLeft(delta);
            },
            onPointerUp(e) {
                const drag = this.drag;
                if (!drag || e.pointerId !== drag.pointerId) return;

                const moved = drag.moved;
                this.endDrag();

                if (moved) {
                    this.suppressClick = true;
                }
            },
            endDrag() {
                const pointerId = this.drag ? this.drag.pointerId : null;

                this.track.classList.remove('is-dragging');
                this.track.style.userSelect = '';
                this.track.style.scrollBehavior = '';
                this.track.style.scrollSnapType = '';
                this.track.removeEventListener('pointermove', this.pointerMove);
                this.track.removeEventListener('pointerup', this.pointerUp);
                this.track.removeEventListener('pointercancel', this.pointerUp);

                if (pointerId !== null && this.track.hasPointerCapture(pointerId)) {
                    this.track.releasePointerCapture(pointerId);
                }

                this.drag = null;

                // Re-engage mandatory snap so the strip settles on the nearest card.
                requestAnimationFrame(() => {
                    const left = this.track.scrollLeft;
                    this.track.scrollLeft = left;
                });
            },
            onTrackClick(e) {
                if (this.suppressClick) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.suppressClick = false;
                }
            },
            onScroll() {
                if (this.frame) return;
                this.frame = requestAnimationFrame(() => {
                    this.frame = null;
                    this.applyFocus();
                });
            },
            applyFocus() {
                const track = this.track;
                const viewportCenter = track.getBoundingClientRect().left + track.clientWidth / 2;
                const half = track.clientWidth / 2;

                track.querySelectorAll('.day-scroller-card').forEach((card) => {
                    const rect = card.getBoundingClientRect();
                    const cardCenter = rect.left + rect.width / 2;
                    const ratio = Math.min(1, Math.abs(cardCenter - viewportCenter) / half);

                    const isToday = card.hasAttribute('data-day-scroll-target');
                    const scale = isToday ? 1 : 1 - ratio * 0.05;
                    const opacity = isToday ? 1 : 1 - ratio * 0.25;

                    card.style.transform = `scale(${scale})`;
                    card.style.opacity = String(opacity);
                });
            },
        };
    }
</script>
