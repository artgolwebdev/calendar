@props(['existing' => null])

<div x-data="coverCrop({ existingUrl: @js($existing) })" class="space-y-3">
    <div>
        <label for="cover_image_path" class="label">תמונת כריכה <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>

        {{-- Current / staged preview --}}
        <div class="mt-2 flex items-center gap-3 p-3 bg-ink-50 border border-ink-200 rounded-xl">
            <template x-if="stagedUrl">
                <img :src="stagedUrl" alt="תמונת הכריכה החדשה" class="h-16 w-28 object-cover rounded-lg border border-ink-300 shrink-0">
            </template>
            <template x-if="!stagedUrl && existingUrl">
                <img :src="existingUrl" alt="תמונת הכריכה הנוכחית" class="h-16 w-28 object-cover rounded-lg border border-ink-300 shrink-0">
            </template>
            <template x-if="!stagedUrl && !existingUrl">
                <div class="flex items-center justify-center h-16 w-28 shrink-0 rounded-lg border border-ink-200 bg-gradient-to-br from-ink-200 to-ink-100 text-ink-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </template>

            <div class="min-w-0">
                <template x-if="stagedUrl">
                    <div>
                        <p class="text-xs font-bold text-ink-950">תמונה חדשה נבחרה</p>
                        <p class="text-xs text-ink-500 mt-0.5">הכריכה תוחלף בתמונה החתוכה בעת השמירה</p>
                        <button type="button" @click="revert()"
                            class="mt-1 text-xs font-medium text-danger hover:text-danger-hover transition-colors">
                            ביטול בחירה
                        </button>
                    </div>
                </template>
                <template x-if="!stagedUrl && existingUrl">
                    <div>
                        <p class="text-xs font-bold text-ink-950">תמונת כריכה נוכחית</p>
                        <p class="text-xs text-ink-500 mt-0.5">בחירת תמונה חדשה תחליף אותה</p>
                    </div>
                </template>
                <template x-if="!stagedUrl && !existingUrl">
                    <div>
                        <p class="text-xs font-bold text-ink-950">אין כריכה עדיין</p>
                        <p class="text-xs text-ink-500 mt-0.5">מומלץ קובץ רחב — נחתך ליחס 21:9</p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Dropzone --}}
        <label for="cover_image_path" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
            @drop.prevent="onDrop($event)"
            :class="dragOver
                ? 'mt-3 flex flex-col items-center justify-center gap-3 w-full border-2 border-dashed border-ink-900 bg-volt/10 rounded-2xl p-8 cursor-pointer transition-colors'
                : 'mt-3 flex flex-col items-center justify-center gap-3 w-full border-2 border-dashed border-ink-300 bg-ink-50/50 hover:border-ink-900 hover:bg-volt/5 rounded-2xl p-8 cursor-pointer transition-colors'">
            <span
                :class="dragOver ? 'flex items-center justify-center w-12 h-12 rounded-2xl bg-ink-900 text-volt' : 'flex items-center justify-center w-12 h-12 rounded-2xl bg-ink-100 text-ink-600'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <span class="text-sm text-ink-600" x-text="dragOver ? 'שחררו כדי לבחור' : 'גררו תמונה לכאן או בחרו מהמכשיר'"></span>
            <span class="text-xs text-ink-400">JPG · PNG · WEBP · עד 50MB — נחתך אוטומטית ליחס 21:9</span>
            <span class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-xl bg-ink-900 text-volt font-bold text-sm pointer-events-none">בחירת קובץ</span>
            <input type="file" name="cover_image_path" id="cover_image_path" x-ref="fileInput"
                accept="image/jpeg,image/png,image/webp" class="hidden" @change="onSelect($event)">
        </label>

        <p x-show="error" x-cloak class="mt-2 text-xs font-medium text-danger" x-text="error"></p>
    </div>

    {{-- Crop modal --}}
    <div x-cloak x-show="open" @keydown.escape.window="cancel()" role="dialog" aria-modal="true" aria-label="חיתוך תמונת הכריכה"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-ink-950/60" @click="cancel()"></div>

        <div x-cloak x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="relative card w-full max-w-3xl overflow-hidden max-h-[92vh] flex flex-col">

            {{-- Header --}}
            <div class="shrink-0 px-5 py-4 flex items-center justify-between border-b border-ink-200">
                <h3 class="text-lg font-bold text-ink-950">חיתוך תמונת הכריכה</h3>
                <button type="button" @click="cancel()"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-full text-ink-500 hover:bg-ink-100 hover:text-ink-900 transition-colors"
                    aria-label="סגור">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            {{-- Crop area --}}
            <div class="flex-1 min-h-0 overflow-y-auto p-5">
                <div class="relative w-full flex items-center justify-center rounded-xl overflow-hidden bg-ink-950">
                    <img x-ref="cropImg" :src="rawUrl" alt="התמונה לחיתוך" class="block max-w-full max-h-[52vh] w-auto">
                </div>

                <div class="mt-4 flex items-center justify-center gap-2 flex-wrap">
                    <button type="button" @click="zoomOut()" aria-label="הקטן"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-ink-200 text-ink-600 hover:bg-ink-100 hover:text-ink-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round">
                            <path d="M6 12h12" />
                        </svg>
                    </button>
                    <button type="button" @click="resetCrop()"
                        class="h-10 px-4 rounded-full text-xs font-bold text-ink-600 hover:bg-ink-100 hover:text-ink-900 transition-colors">
                        איפוס
                    </button>
                    <button type="button" @click="zoomIn()" aria-label="הגדל"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-ink-200 text-ink-600 hover:bg-ink-100 hover:text-ink-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round">
                            <path d="M12 6v12M6 12h12" />
                        </svg>
                    </button>
                </div>

                <p class="mt-3 text-center text-xs text-ink-400">
                    גררו את התמונה, הגדילו בעזרת הגלגלת או הכפתורים, והזיזו את המסגרת — היחס נשמר אוטומטית
                </p>
            </div>

            {{-- Footer --}}
            <div class="shrink-0 px-5 py-4 flex items-center justify-end gap-3 border-t border-ink-200">
                <button type="button" class="btn btn-secondary" @click="cancel()">ביטול</button>
                <button type="button" class="btn btn-primary" @click="confirm()">אישור</button>
            </div>
        </div>
    </div>
</div>
