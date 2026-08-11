{{-- Backdrop --}}
<div x-cloak x-show="settingsOpen" @click="settingsOpen = false"
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-ink-950/40"></div>

{{-- Bottom offcanvas (mobile) / right-side offcanvas (desktop) panel --}}
<div x-cloak x-show="settingsOpen" role="dialog" aria-modal="true" aria-labelledby="designSettingsTitle"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full lg:translate-y-0 lg:translate-x-full"
     x-transition:enter-end="translate-y-0 lg:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 lg:translate-x-0"
     x-transition:leave-end="translate-y-full lg:translate-y-0 lg:translate-x-full"
     class="fixed inset-x-0 bottom-0 z-50 flex flex-col bg-white rounded-t-3xl max-h-[40vh] shadow-2xl lg:inset-x-auto lg:top-0 lg:right-0 lg:bottom-auto lg:h-full lg:max-h-none lg:w-[26rem] lg:max-w-[90vw] lg:rounded-t-none lg:rounded-l-3xl">

    {{-- Drag handle (mobile only) --}}
    <div class="shrink-0 flex justify-center pt-3 pb-2 lg:hidden" aria-hidden="true">
        <span class="block w-10 h-1.5 rounded-full bg-ink-300"></span>
    </div>

    {{-- Sticky header --}}
    <div class="shrink-0 px-6 pb-4 flex items-center justify-between border-b border-ink-200">
        <h3 id="designSettingsTitle" class="text-lg font-bold text-ink-900">הגדרות עיצוב</h3>
        <button type="button" @click="settingsOpen = false"
            class="inline-flex items-center justify-center w-9 h-9 rounded-full text-ink-500 hover:bg-ink-100 hover:text-ink-900 transition-colors"
            aria-label="סגור">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>

    {{-- Scrollable body --}}
    <div class="flex-1 min-h-0 overflow-y-auto px-6 py-5">
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
                class="w-full h-9 rounded-lg border border-ink-200 p-1 cursor-pointer bg-white"
                value="{{ $monthPage->weekday_color ?? '#71717A' }}">
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
            <img id="customImagePreview" class="hidden mt-2 h-12 w-16 object-cover rounded border border-ink-200"
                alt="תצוגה מקדימה">
            <div class="mt-2 flex items-center justify-between p-2 bg-ink-50 border border-ink-200 rounded-lg">
                @if ($monthPage->custom_image_path)
                    <img src="{{ asset('storage/'.$monthPage->custom_image_path) }}" alt="תמונת רקע נוכחית"
                        class="h-12 w-16 object-cover rounded border border-ink-200">
                    <button type="submit" form="removeImageForm"
                        onclick="return confirm('האם אתה בטוח שברצונך להסיר את התמונה?')"
                        class="text-xs font-medium text-danger hover:text-danger-hover transition-colors">
                        הסר תמונה
                    </button>
                @elseif ($monthPage->auto_background_media_id && $monthPage->autoBackgroundMedia)
                    <img src="{{ $monthPage->autoBackgroundMedia->getUrl('thumb') }}" alt="תמונת רקע אוטומטית"
                        class="h-12 w-16 object-cover rounded border border-ink-200">
                    <span class="text-xs text-ink-500">
                        הוגדר אוטומטית מתמונת {{ $monthPage->autoBackgroundMember?->name }} (חודש יומולדת)
                    </span>
                @else
                    <span class="text-xs text-ink-500">אין תמונת רקע</span>
                    <span class="text-xs text-ink-500">עדיין לא נבחרה</span>
                @endif
            </div>
        </div>

        <div>
            <label class="label">רקע מהספרייה</label>
            <input type="hidden" name="background_media_id" id="background_media_id"
                value="{{ $monthPage->background_media_id }}">

            <div id="selectedMediaPreview"
                class="flex items-center justify-between p-2 bg-ink-50 border border-ink-200 rounded-lg">
                @if ($monthPage->background_media_id && $monthPage->backgroundMedia)
                    <img src="{{ $monthPage->backgroundMedia->getUrl('thumb') }}" alt="הרקע הנבחר מהספרייה"
                        class="h-12 w-16 object-cover rounded border border-ink-200">
                    <button type="button" onclick="clearSelectedMedia()"
                        class="text-xs font-medium text-danger hover:text-danger-hover transition-colors">
                        הסר בחירה
                    </button>
                @else
                    <span class="text-xs text-ink-500">לא נבחר רקע מהספרייה</span>
                @endif
            </div>

            @if ($userMedia->isNotEmpty())
                <details class="mt-2">
                    <summary
                        class="text-xs font-medium text-ink-950 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                        בחירת תמונה מהספרייה
                    </summary>
                    <div class="mt-2 grid grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                        @foreach ($userMedia as $media)
                            <button type="button" onclick="pickMedia({{ $media->id }}, '{{ $media->getUrl('thumb') }}')"
                                class="relative rounded border overflow-hidden transition-colors {{ $monthPage->background_media_id === $media->id ? 'border-ink-950 ring-2 ring-ink-950/30' : 'border-ink-200 hover:border-ink-950' }}">
                                <img src="{{ $media->getUrl('thumb') }}" alt="{{ $media->name }}"
                                    class="h-16 w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </details>
            @else
                <a href="{{ route('media.index') }}"
                    class="mt-2 inline-block text-xs font-medium text-ink-950 hover:text-ink-900 transition-colors">
                    הוספת תמונות לספרייה
                </a>
            @endif
        </div>

        <div>
            <label for="overlay_opacity" class="label flex justify-between">
                <span>אטימות שכבת העל</span>
                <span class="text-xs font-normal text-ink-500"><span id="overlayValue">{{ $monthPage->overlay_opacity ?? 30 }}</span>%</span>
            </label>
            <input type="range" name="overlay_opacity" id="overlay_opacity" min="0" max="100" step="5"
                class="w-full accent-ink-950" value="{{ $monthPage->overlay_opacity ?? 30 }}">
            <div class="flex justify-between text-[11px] text-ink-500 mt-1">
                <span>בהיר</span>
                <span>כהה</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="day_box_bg_color" class="label">רקע תא יום</label>
                <input type="color" name="day_box_bg_color" id="day_box_bg_color"
                    class="w-full h-9 rounded-lg border border-ink-200 p-1 cursor-pointer bg-white"
                    value="{{ $monthPage->day_box_bg_color ?? '#FFFFFF' }}">
            </div>
            <div>
                <label for="day_box_font_color" class="label">טקסט תא יום</label>
                <input type="color" name="day_box_font_color" id="day_box_font_color"
                    class="w-full h-9 rounded-lg border border-ink-200 p-1 cursor-pointer bg-white"
                    value="{{ $monthPage->day_box_font_color ?? '#2B2E3A' }}">
            </div>
        </div>

        <div>
            <label for="day_box_bg_opacity" class="label flex justify-between">
                <span>אטימות רקע תא יום</span>
                <span class="text-xs font-normal text-ink-500"><span id="dayBoxOpacityValue">{{ $monthPage->day_box_bg_opacity ?? 100 }}</span>%</span>
            </label>
            <input type="range" name="day_box_bg_opacity" id="day_box_bg_opacity" min="0" max="100" step="5"
                class="w-full accent-ink-950" value="{{ $monthPage->day_box_bg_opacity ?? 100 }}">
            <div class="flex justify-between text-[11px] text-ink-500 mt-1">
                <span>שקוף</span>
                <span>מוצק</span>
            </div>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="show_adjacent_month_days" value="0">
                <input type="checkbox" name="show_adjacent_month_days" id="show_adjacent_month_days" value="1"
                    class="rounded border-ink-200 text-ink-950 focus:ring-ink-950"
                    {{ $monthPage->show_adjacent_month_days ? 'checked' : '' }}>
                <span class="text-sm font-medium text-ink-900">הצג ימים מהחודש הקודם/הבא</span>
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

    {{-- Sticky footer --}}
    <div class="shrink-0 px-6 py-4 border-t border-ink-200 bg-white">
        <button type="submit" form="designSettingsForm" id="saveSettingsCta"
            class="hidden w-full btn btn-primary font-bold justify-center"
            style="height: 3rem; font-size: 1rem;">
            שמור הגדרות
        </button>
    </div>
</div>

<script>
    function pickMedia(id, url) {
        document.getElementById('background_media_id').value = id;
        document.getElementById('selectedMediaPreview').innerHTML =
            '<img src="' + url + '" alt="הרקע הנבחר מהספרייה" class="h-12 w-16 object-cover rounded border border-ink-200">' +
            '<button type="button" onclick="clearSelectedMedia()" class="text-xs font-medium text-danger hover:text-danger-hover transition-colors">הסר בחירה</button>';
    }

    function clearSelectedMedia() {
        document.getElementById('background_media_id').value = '';
        document.getElementById('selectedMediaPreview').innerHTML =
            '<span class="text-xs text-ink-500">לא נבחר רקע מהספרייה</span>';
    }
</script>
