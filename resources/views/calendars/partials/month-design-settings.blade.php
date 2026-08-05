<div class="lg:col-span-1">
    <details {{ $errors->any() ? 'open' : '' }} class="card group overflow-hidden lg:sticky lg:top-4">
        <summary
            class="flex items-center justify-between gap-2 p-5 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
            <h3 class="text-base font-semibold text-[#1A1A1E]">הגדרות עיצוב</h3>
            <svg class="w-4 h-4 text-[#6B6B75] transition-transform duration-200 group-open:rotate-180" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </summary>

        <div class="px-5 pb-5 pt-1">
            <form action="{{ route('month-pages.update', [$calendar, $monthPage]) }}" method="POST"
                enctype="multipart/form-data" id="designSettingsForm" class="space-y-4">
            @csrf
            @method('PUT')

            @php
                $fontOptions = [
                    'default' => 'ברירת מחדל',
                    'modern' => 'מודרני',
                    'traditional' => 'מסורתי',
                    'elegant' => 'אלגנטי',
                ];
            @endphp

            <div>
                <label for="weekday_color" class="label">צבע ימי השבוע</label>
                <input type="color" name="weekday_color" id="weekday_color"
                    class="w-full h-9 rounded-lg border border-[#E5E5E8] p-1 cursor-pointer bg-white"
                    value="{{ $monthPage->weekday_color ?? '#6B6B75' }}">
            </div>

            <div>
                <label for="font_choice" class="label">גופן הלוח</label>
                <select name="font_choice" id="font_choice" class="input">
                    @foreach ($fontOptions as $value => $label)
                        <option value="{{ $value }}"
                            {{ $monthPage->font_choice === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="custom_image_path" class="label">תמונת רקע לחודש</label>
                <input type="file" name="custom_image_path" id="custom_image_path"
                    class="input pt-1.5 cursor-pointer" accept="image/*">
                <img id="customImagePreview" class="hidden mt-2 h-12 w-16 object-cover rounded border border-[#E5E5E8]"
                    alt="תצוגה מקדימה">
                <div class="mt-2 flex items-center justify-between p-2 bg-[#F7F7F8] border border-[#E5E5E8] rounded-lg">
                    @if ($monthPage->custom_image_path)
                        <img src="{{ asset('storage/'.$monthPage->custom_image_path) }}" alt="תמונת רקע נוכחית"
                            class="h-12 w-16 object-cover rounded border border-[#E5E5E8]">
                        <button type="submit" form="removeImageForm"
                            onclick="return confirm('האם אתה בטוח שברצונך להסיר את התמונה?')"
                            class="text-xs font-medium text-[#DC2626] hover:text-[#B91C1C] transition-colors">
                            הסר תמונה
                        </button>
                    @else
                        <span class="text-xs text-[#6B6B75]">אין תמונת רקע</span>
                        <span class="text-xs text-[#6B6B75]">עדיין לא נבחרה</span>
                    @endif
                </div>
            </div>

            <div>
                <label class="label">רקע מהספרייה</label>
                <input type="hidden" name="background_media_id" id="background_media_id"
                    value="{{ $monthPage->background_media_id }}">

                <div id="selectedMediaPreview"
                    class="flex items-center justify-between p-2 bg-[#F7F7F8] border border-[#E5E5E8] rounded-lg">
                    @if ($monthPage->background_media_id && $monthPage->backgroundMedia)
                        <img src="{{ $monthPage->backgroundMedia->getUrl('thumb') }}" alt="הרקע הנבחר מהספרייה"
                            class="h-12 w-16 object-cover rounded border border-[#E5E5E8]">
                        <button type="button" onclick="clearSelectedMedia()"
                            class="text-xs font-medium text-[#DC2626] hover:text-[#B91C1C] transition-colors">
                            הסר בחירה
                        </button>
                    @else
                        <span class="text-xs text-[#6B6B75]">לא נבחר רקע מהספרייה</span>
                    @endif
                </div>

                @if ($userMedia->isNotEmpty())
                    <details class="mt-2">
                        <summary
                            class="text-xs font-medium text-[#4F46E5] cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                            בחירת תמונה מהספרייה
                        </summary>
                        <div class="mt-2 grid grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                            @foreach ($userMedia as $media)
                                <button type="button" onclick="pickMedia({{ $media->id }}, '{{ $media->getUrl('thumb') }}')"
                                    class="relative rounded border overflow-hidden transition-colors {{ $monthPage->background_media_id === $media->id ? 'border-[#4F46E5] ring-2 ring-[#4F46E5]/30' : 'border-[#E5E5E8] hover:border-[#4F46E5]' }}">
                                    <img src="{{ $media->getUrl('thumb') }}" alt="{{ $media->name }}"
                                        class="h-16 w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    </details>
                @else
                    <a href="{{ route('media.index') }}"
                        class="mt-2 inline-block text-xs font-medium text-[#4F46E5] hover:text-[#4338CA] transition-colors">
                        הוספת תמונות לספרייה
                    </a>
                @endif
            </div>

            <div>
                <label for="overlay_opacity" class="label flex justify-between">
                    <span>אטימות שכבת העל</span>
                    <span class="text-xs font-normal text-[#6B6B75]"><span id="overlayValue">{{ $monthPage->overlay_opacity ?? 30 }}</span>%</span>
                </label>
                <input type="range" name="overlay_opacity" id="overlay_opacity" min="0" max="100" step="5"
                    class="w-full accent-[#4F46E5]" value="{{ $monthPage->overlay_opacity ?? 30 }}">
                <div class="flex justify-between text-[11px] text-[#6B6B75] mt-1">
                    <span>בהיר</span>
                    <span>כהה</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="day_box_bg_color" class="label">רקע תא יום</label>
                    <input type="color" name="day_box_bg_color" id="day_box_bg_color"
                        class="w-full h-9 rounded-lg border border-[#E5E5E8] p-1 cursor-pointer bg-white"
                        value="{{ $monthPage->day_box_bg_color ?? '#FFFFFF' }}">
                </div>
                <div>
                    <label for="day_box_font_color" class="label">טקסט תא יום</label>
                    <input type="color" name="day_box_font_color" id="day_box_font_color"
                        class="w-full h-9 rounded-lg border border-[#E5E5E8] p-1 cursor-pointer bg-white"
                        value="{{ $monthPage->day_box_font_color ?? '#2B2E3A' }}">
                </div>
            </div>

            <div>
                <label for="day_box_bg_opacity" class="label flex justify-between">
                    <span>אטימות רקע תא יום</span>
                    <span class="text-xs font-normal text-[#6B6B75]"><span id="dayBoxOpacityValue">{{ $monthPage->day_box_bg_opacity ?? 100 }}</span>%</span>
                </label>
                <input type="range" name="day_box_bg_opacity" id="day_box_bg_opacity" min="0" max="100" step="5"
                    class="w-full accent-[#4F46E5]" value="{{ $monthPage->day_box_bg_opacity ?? 100 }}">
                <div class="flex justify-between text-[11px] text-[#6B6B75] mt-1">
                    <span>שקוף</span>
                    <span>מוצק</span>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="show_adjacent_month_days" value="0">
                    <input type="checkbox" name="show_adjacent_month_days" id="show_adjacent_month_days" value="1"
                        class="rounded border-[#E5E5E8] text-[#4F46E5] focus:ring-[#4F46E5]"
                        {{ $monthPage->show_adjacent_month_days ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-[#1A1A1E]">הצג ימים מהחודש הקודם/הבא</span>
                </label>
            </div>
        </form>

        @if ($monthPage->custom_image_path)
            <form action="{{ route('month-pages.remove-image', [$calendar, $monthPage]) }}" method="POST"
                id="removeImageForm">
                @csrf
                @method('DELETE')
            </form>
        @endif
        </div>
    </details>

    <button type="submit" form="designSettingsForm" id="saveSettingsCta"
        class="hidden fixed inset-x-0 bottom-0 z-40 bg-white/95 backdrop-blur border-t border-[#E5E5E8] px-4 py-3">
        <span class="btn btn-primary w-full justify-center">
            שמור הגדרות
        </span>
    </button>
</div>

<script>
    function pickMedia(id, url) {
        document.getElementById('background_media_id').value = id;
        document.getElementById('selectedMediaPreview').innerHTML =
            '<img src="' + url + '" alt="הרקע הנבחר מהספרייה" class="h-12 w-16 object-cover rounded border border-[#E5E5E8]">' +
            '<button type="button" onclick="clearSelectedMedia()" class="text-xs font-medium text-[#DC2626] hover:text-[#B91C1C] transition-colors">הסר בחירה</button>';
    }

    function clearSelectedMedia() {
        document.getElementById('background_media_id').value = '';
        document.getElementById('selectedMediaPreview').innerHTML =
            '<span class="text-xs text-[#6B6B75]">לא נבחר רקע מהספרייה</span>';
    }
</script>
