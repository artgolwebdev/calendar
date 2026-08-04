<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-[#1A1A1E]">{{ $familyMember->name }}</h1>
                <p class="text-xs text-[#6B6B75] mt-1">פרופיל חבר משפחה ואירועים שיוכו אליו</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('family-members.edit', $familyMember) }}" class="btn btn-secondary btn-sm">
                    ערוך פרטים
                </a>
                <form action="{{ route('family-members.destroy', $familyMember) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-destructive btn-sm"
                        onclick="return confirm('האם אתה בטוח שברצונך למחוק את חבר המשפחה?')">
                        מחק
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Family Member Details -->
                <div class="lg:col-span-1">
                    <div class="card p-5">
                        <h3 class="text-base font-semibold text-[#1A1A1E] mb-4 pb-3 border-b border-[#E5E5E8]">פרטים אישיים</h3>
                        
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="text-xs font-medium text-[#6B6B75] block mb-0.5">שם מלא</span>
                                <p class="font-semibold text-[#1A1A1E]">{{ $familyMember->name }}</p>
                            </div>
                            
                            <div>
                                <span class="text-xs font-medium text-[#6B6B75] block mb-0.5">תאריך לידה</span>
                                <p class="font-medium text-[#1A1A1E]">{{ $familyMember->birth_date->format('d/m/Y') }}</p>
                            </div>
                            
                            @if ($familyMember->anniversary_date)
                                <div>
                                    <span class="text-xs font-medium text-[#6B6B75] block mb-0.5">תאריך נישואין</span>
                                    <p class="font-medium text-[#1A1A1E]">{{ $familyMember->anniversary_date->format('d/m/Y') }}</p>
                                </div>
                            @endif
                            
                            @if ($familyMember->notes)
                                <div>
                                    <span class="text-xs font-medium text-[#6B6B75] block mb-0.5">הערות</span>
                                    <p class="text-[#1A1A1E] bg-[#F7F7F8] p-3 rounded-lg border border-[#E5E5E8] text-xs leading-relaxed">{{ $familyMember->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="lg:col-span-2">
                    <div class="card p-5">
                        <h3 class="text-base font-semibold text-[#1A1A1E] mb-4 pb-3 border-b border-[#E5E5E8]">אירועים קרובים</h3>
                        
                        @php
                            $upcomingEvents = $familyMember->calendarEvents()
                                ->where('event_date', '>=', now())
                                ->orderBy('event_date')
                                ->take(10)
                                ->get();
                        @endphp
                        
                        @if ($upcomingEvents->isEmpty())
                            <div class="py-8 text-center text-sm text-[#6B6B75]">
                                אין אירועים עתידיים משויכים לחבר משפחה זה
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($upcomingEvents as $event)
                                    <div class="p-4 rounded-lg bg-[#F7F7F8] border border-[#E5E5E8] flex justify-between items-center">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="font-semibold text-sm text-[#1A1A1E]">{{ $event->title }}</h4>
                                                <span class="chip text-xs
                                                    @if ($event->event_type === 'birthday') chip-birthday
                                                    @elseif ($event->event_type === 'anniversary') chip-anniversary
                                                    @else chip-event
                                                    @endif">
                                                    {{ $event->event_type === 'birthday' ? 'יום הולדת' : ($event->event_type === 'anniversary' ? 'יום נישואין' : 'מותאם אישית') }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-[#6B6B75]">{{ $event->event_date->format('d/m/Y') }}</p>
                                        </div>
                                        <div>
                                            <a href="{{ route('calendar-events.edit', [$event->calendar, $event]) }}" class="text-xs font-medium text-[#4F46E5] hover:text-[#4338CA] transition-colors">
                                                ערוך אירוע
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>