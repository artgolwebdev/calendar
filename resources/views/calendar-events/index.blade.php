<x-app-layout>
    <div class="py-8">
        <div class="container">
            {{-- Header: back link --}}
            <a href="{{ route('calendars.show', $calendar) }}"
                class="inline-flex items-center gap-1.5 mb-4 text-sm font-semibold text-ink-500 hover:text-ink-900 transition-colors">
                → חזרה ללוח השנה
            </a>

            {{-- Header: title + actions --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-ink-950 tracking-tight">
                        אירועי {{ $calendar->name }}
                    </h1>
                    <p class="mt-1 text-sm text-ink-500">
                        כל האירועים של הלוח — גם אלה שנוצרים אוטומטית מחברי המשפחה
                    </p>
                </div>
                <a href="{{ route('calendar-events.create', $calendar) }}"
                    class="btn btn-primary h-11 px-6 rounded-xl">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    אירוע חדש
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $autoCount = $events->where('is_auto_generated', true)->count();
                $manualCount = $events->count() - $autoCount;
            @endphp

            {{-- Summary --}}
            <div class="flex flex-wrap items-center gap-2 mb-6 text-xs font-medium text-ink-500">
                <span class="chip chip-event">{{ $manualCount }} אירועים ידניים</span>
                <span class="chip chip-birthday">{{ $autoCount }} אוטומטיים</span>
                <span class="text-ink-400">· אירועים אוטומטיים נגזרים מתאריך הולדת / נישואין של חבר המשפחה</span>
            </div>

            {{-- Grid --}}
            @if ($events->isEmpty())
                <div class="card p-12 text-center">
                    <div class="mx-auto w-12 h-12 rounded-xl bg-ink-100 text-ink-400 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-ink-900 mb-1">אין אירועים עדיין</h2>
                    <p class="text-sm text-ink-500 mb-5">הוסף אירוע ידני, או הוסף חבר משפחה עם תאריך הולדת כדי ליצור אירועים אוטומטית</p>
                    <a href="{{ route('calendar-events.create', $calendar) }}" class="btn btn-primary">
                        הוסף אירוע ראשון
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($events as $event)
                        @php
                            $badge = match ($event->event_type) {
                                'birthday' => [
                                    'label' => 'יום הולדת',
                                    'chip' => 'chip-birthday',
                                    'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/><path d="M2 21h20"/><path d="M7 8v3"/><path d="M12 8v3"/><path d="M17 8v3"/><path d="M7 4h.01"/><path d="M12 4h.01"/><path d="M17 4h.01"/></svg>',
                                    'placeholder' => ['from-purple-100 to-ink-100', 'text-purple-400'],
                                    'placeholder_icon' => '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/><path d="M2 21h20"/><path d="M7 8v3"/><path d="M12 8v3"/><path d="M17 8v3"/><path d="M7 4h.01"/><path d="M12 4h.01"/><path d="M17 4h.01"/></svg>',
                                ],
                                'anniversary' => [
                                    'label' => 'יום נישואין',
                                    'chip' => 'chip-anniversary',
                                    'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
                                    'placeholder' => ['from-sky-100 to-ink-100', 'text-sky-400'],
                                    'placeholder_icon' => '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
                                ],
                                default => [
                                    'label' => 'אירוע',
                                    'chip' => 'chip-event',
                                    'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.287 1.288L3 12l5.8 1.9a2 2 0 0 1 1.288 1.287L12 21l1.9-5.8a2 2 0 0 1 1.287-1.288L21 12l-5.8-1.9a2 2 0 0 1-1.288-1.287Z"/></svg>',
                                    'placeholder' => ['from-orange-100 to-ink-100', 'text-orange-400'],
                                    'placeholder_icon' => '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.287 1.288L3 12l5.8 1.9a2 2 0 0 1 1.288 1.287L12 21l1.9-5.8a2 2 0 0 1 1.287-1.288L21 12l-5.8-1.9a2 2 0 0 1-1.288-1.287Z"/></svg>',
                                ],
                            };
                            $timeLabel = $event->start_time
                                ? ' · ' . \Carbon\Carbon::parse($event->start_time)->format('H:i')
                                : '';
                            $coverUrl = $event->cover_image_path ? asset('storage/' . $event->cover_image_path) : null;
                        @endphp

                        <div class="card overflow-hidden flex flex-col">
                            <a href="{{ route('calendar-events.edit', [$calendar, $event]) }}" class="block">
                                @if ($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $event->title }}"
                                        class="w-full aspect-[21/9] object-cover">
                                @else
                                    <div class="w-full aspect-[21/9] flex items-center justify-center bg-gradient-to-br {{ $badge['placeholder'][0] }} {{ $badge['placeholder'][1] }}">
                                        {!! $badge['placeholder_icon'] !!}
                                    </div>
                                @endif
                            </a>

                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="chip {{ $badge['chip'] }} gap-1.5">
                                        {!! $badge['icon'] !!}
                                        {{ $badge['label'] }}
                                    </span>
                                    @if ($event->is_auto_generated)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-400 shrink-0"
                                            title="אירוע אוטומטי — נוצר מתאריך ההולדת או יום הנישואין של חבר המשפחה. ניתן לערוך את הכותרת, התיאור ותמונת הכריכה.">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <path d="M12 16v-4M12 8h.01" />
                                            </svg>
                                            אוטומטי
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-semibold text-ink-900">{{ $event->title }}</h3>

                                <p class="text-xs text-ink-500 mt-0.5 flex flex-wrap items-center gap-x-2">
                                    <span>{{ $event->event_date->format('d/m/Y') }}</span>
                                    @if ($timeLabel)
                                        <span dir="ltr">{{ $timeLabel }}</span>
                                    @endif
                                    @if ($event->familyMember)
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z" />
                                            </svg>
                                            {{ $event->familyMember->name }}
                                        </span>
                                    @endif
                                </p>

                                @if ($event->description)
                                    <p class="mt-2 text-xs text-ink-500 line-clamp-2 leading-relaxed">{{ $event->description }}</p>
                                @endif

                                <div class="mt-auto pt-3 mt-4 border-t border-ink-100 flex items-center gap-2">
                                    <a href="{{ route('calendar-events.edit', [$calendar, $event]) }}"
                                        class="btn btn-outline h-9 px-4">
                                        עריכה
                                    </a>
                                    @if (! $event->is_auto_generated)
                                        <form action="{{ route('calendar-events.destroy', [$calendar, $event]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-danger h-9 px-4"
                                                onclick="return confirm('האם אתה בטוח שברצונך למחוק את האירוע?')">
                                                מחיקה
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
