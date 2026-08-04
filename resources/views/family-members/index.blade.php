<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-[#1A1A1E]">חברי משפחה</h1>
                <p class="text-xs text-[#6B6B75] mt-1">ניהול פרטי חברי המשפחה, ימי הולדת ותאריכי נישואין</p>
            </div>
            <a href="{{ route('family-members.create') }}" class="btn btn-primary">
                + הוסף חבר משפחה
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            @if (session('success'))
                <div class="mb-6 p-4 rounded-lg bg-[#F0FDF4] border border-[#DCFCE7] text-[#15803D] text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5E5E8]">
                        <thead class="bg-[#F7F7F8]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-[#6B6B75] uppercase tracking-wider">
                                    שם
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-[#6B6B75] uppercase tracking-wider">
                                    תאריך לידה
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-[#6B6B75] uppercase tracking-wider">
                                    תאריך נישואין
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-[#6B6B75] uppercase tracking-wider">
                                    פעולות
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#E5E5E8]">
                            @forelse ($familyMembers as $familyMember)
                                <tr class="hover:bg-[#F7F7F8]/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('family-members.show', $familyMember) }}" class="text-sm font-semibold text-[#1A1A1E] hover:text-[#4F46E5] transition-colors">
                                            {{ $familyMember->name }}
                                        </a>
                                        @if ($familyMember->notes)
                                            <div class="text-xs text-[#6B6B75] mt-0.5">{{ Str::limit($familyMember->notes, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-[#1A1A1E] font-medium">{{ $familyMember->birth_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-[#1A1A1E]">
                                            {{ $familyMember->anniversary_date ? $familyMember->anniversary_date->format('d/m/Y') : '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('family-members.edit', $familyMember) }}" class="text-xs font-medium text-[#4F46E5] hover:text-[#4338CA] transition-colors">
                                                ערוך
                                            </a>
                                            <span class="text-[#E5E5E8]">|</span>
                                            <form action="{{ route('family-members.destroy', $familyMember) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-[#DC2626] hover:text-[#B91C1C] transition-colors"
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
                                        <p class="text-sm text-[#6B6B75] mb-4">עדיין לא הוספת חברי משפחה</p>
                                        <a href="{{ route('family-members.create') }}" class="btn btn-primary btn-sm inline-flex">
                                            + הוסף חבר משפחה
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