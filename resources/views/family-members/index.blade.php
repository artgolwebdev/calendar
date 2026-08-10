<x-app-layout>
    <div class="py-8">
        <div class="container">
            <a href="{{ route('calendars.edit', $calendar) }}"
                class="inline-flex items-center gap-1.5 mb-4 text-sm font-semibold text-ink-500 hover:text-ink-900 transition-colors">
                → חזרה לעריכת לוח השנה
            </a>

            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Header: title + CTA --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-ink-950 tracking-tight">חברי משפחה</h1>
                    <p class="mt-1 text-sm text-ink-500">
                        {{ $calendar->name }} — ניהול פרטי חברי המשפחה, ימי הולדת, תאריכי נישואין והתמונות שלהם
                    </p>
                </div>
                <a href="{{ route('family-members.create', $calendar) }}"
                    class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14m-7-7h14" />
                    </svg>
                    הוסף חבר משפחה
                </a>
            </div>

            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-200">
                        <thead class="bg-ink-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wider">
                                    שם
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wider">
                                    תאריך לידה
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wider">
                                    תאריך נישואין
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wider">
                                    פעולות
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-ink-200">
                            @forelse ($familyMembers as $familyMember)
                                <tr class="hover:bg-ink-50/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('family-members.show', [$calendar, $familyMember]) }}" class="text-sm font-semibold text-ink-900 hover:text-ink-950 hover:underline transition-colors">
                                                {{ $familyMember->name }}
                                            </a>
                                            <span class="chip bg-ink-100 text-ink-500" data-media-count="{{ $familyMember->folder?->media_count ?? 0 }}">
                                                <svg class="w-3 h-3 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $familyMember->folder?->media_count ?? 0 }}
                                            </span>
                                        </div>
                                        @if ($familyMember->notes)
                                            <div class="text-xs text-ink-500 mt-0.5">{{ Str::limit($familyMember->notes, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-ink-900 font-medium">{{ $familyMember->birth_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-ink-900">
                                            {{ $familyMember->anniversary_date ? $familyMember->anniversary_date->format('d/m/Y') : '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('family-members.edit', [$calendar, $familyMember]) }}" class="text-xs font-medium text-ink-900 hover:text-ink-950 hover:underline transition-colors">
                                                ערוך
                                            </a>
                                            <span class="text-ink-200">|</span>
                                            <form action="{{ route('family-members.destroy', [$calendar, $familyMember]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-danger hover:text-danger-hover transition-colors"
                                                    onclick="return confirm('האם אתה בטוח שברצונך למחוק את חבר המשפחה?')">
                                                    מחק
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <p class="text-sm text-ink-500 mb-4">עדיין לא הוספת חברי משפחה ללוח הזה</p>
                                        <a href="{{ route('family-members.create', $calendar) }}"
                                            class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-ink-900 text-volt font-bold text-sm transition-colors hover:bg-ink-800 active:bg-ink-950">
                                            הוסף חבר משפחה
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
