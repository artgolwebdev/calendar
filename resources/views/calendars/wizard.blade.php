<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-ink-900">צור לוח שנה חדש</h1>
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900 transition-colors">
                → חזור ללוח הבקרה
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            <div class="card p-6 sm:p-8 max-w-3xl mx-auto"
                x-data="calendarWizard({
                    submitUrl: @js(route('calendars.wizard.store')),
                    month: @js($month),
                    year: @js($year),
                    themes: @js(config('themes'))
                })">

                {{-- Progress stepper --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-ink-950" x-text="stepLabel"></h2>
                        <span class="text-xs font-semibold text-ink-500" x-text="'שלב ' + (step + 1) + ' מתוך ' + steps.length"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-for="(s, index) in steps" :key="s.key">
                            <template x-if="index > 0">
                                <div class="flex-1 h-1 rounded-full transition-colors"
                                    :class="index <= step ? 'bg-ink-900' : 'bg-ink-200'"></div>
                            </template>

                            <button type="button" @click="goToStep(index)"
                                :aria-label="'שלב ' + (index + 1) + ': ' + s.label"
                                :aria-current="index === step ? 'step' : undefined"
                                :class="index < step
                                    ? 'w-8 h-8 rounded-full bg-volt text-ink-950 flex items-center justify-center cursor-pointer transition-transform hover:scale-110'
                                    : index === step
                                        ? 'w-8 h-8 rounded-full bg-ink-900 text-volt flex items-center justify-center ring-4 ring-volt/30 cursor-default'
                                        : 'w-8 h-8 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center cursor-pointer hover:bg-ink-200 transition-colors'">
                                <template x-if="index < step">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="index >= step">
                                    <span class="text-xs font-bold" x-text="index + 1"></span>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Step 1: calendar name --}}
                <div x-show="currentStep === 'name'" x-cloak class="py-4">
                    <div class="flex flex-col items-center text-center mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-volt text-ink-950 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-ink-950">איך תקראו ללוח השנה?</h3>
                        <p class="text-sm text-ink-500 mt-1">בחרו שם שיהפוך את הלוח לשלכם — תוכלו לשנותו בכל עת</p>
                    </div>

                    <div class="max-w-md mx-auto">
                        <label for="calendarName" class="label">שם לוח השנה</label>
                        <input type="text" id="calendarName" x-model="calendarName" maxlength="255"
                            class="input text-center text-lg"
                            :class="nameTouched && !nameValid ? '!border-danger' : ''"
                            placeholder="לדוגמה: לוח שנה 2026"
                            @keydown.enter="next()"
                            @blur="nameTouched = true">
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xs text-ink-400" x-text="calendarName.trim().length + ' / 255 תווים'"></span>
                            <span x-show="nameTouched && !nameValid" x-cloak
                                class="text-xs font-medium text-danger"
                                x-text="calendarNameError || 'שם לוח השנה נדרש'"></span>
                        </div>
                    </div>
                </div>

                {{-- Step 2: cover image --}}
                <div x-show="currentStep === 'cover'" x-cloak class="py-4">
                    <div class="flex flex-col items-center text-center mb-6">
                        <h3 class="text-xl font-bold text-ink-950">תמונת כריכה</h3>
                        <p class="text-sm text-ink-500 mt-1">תמונת כריכה הופכת את הלוח למיוחד — אפשר גם לדלג</p>
                    </div>

                    <div @cover-staged="onCoverStaged($event)" @cover-cleared="onCoverCleared()">
                        <x-cover-upload />
                    </div>
                </div>

                {{-- Step 3: family members --}}
                <div x-show="currentStep === 'members'" x-cloak class="py-4">
                    {{-- Hub: choose to add members or skip --}}
                    <div x-show="!memberPanelOpen">
                        <div class="flex flex-col items-center text-center mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-volt text-ink-950 flex items-center justify-center mb-4">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-2a3 3 0 10-3-3" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-ink-950">האם להוסיף בני משפחה?</h3>
                            <p class="text-sm text-ink-500 mt-1">תוכלו להוסיף בני משפחה גם אחר כך, מכל לוח שנה</p>
                        </div>

                        <div class="max-w-md mx-auto space-y-3">
                            <template x-if="members.length">
                                <div class="space-y-2">
                                    <template x-for="(member, index) in members" :key="index">
                                        <div class="card flex items-center gap-3 p-3">
                                            <template x-if="member.imageUrl">
                                                <img :src="member.imageUrl" alt="" class="w-10 h-10 rounded-full object-cover border border-ink-200 shrink-0">
                                            </template>
                                            <template x-if="!member.imageUrl">
                                                <div class="w-10 h-10 rounded-full bg-ink-100 text-ink-500 flex items-center justify-center shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            </template>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-ink-950 truncate" x-text="member.name"></p>
                                                <p class="text-xs text-ink-500" x-text="'נולד: ' + member.birthDate"></p>
                                                <p class="text-xs text-ink-500" x-show="member.anniversaryDate" x-cloak x-text="'יום נישואין: ' + member.anniversaryDate"></p>
                                            </div>
                                            <button type="button" @click="editMember(index)" title="עריכה"
                                                class="btn-icon-ghost btn-icon-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="removeMember(index)" title="הסרה"
                                                class="btn-icon-ghost btn-icon-sm hover:text-danger">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <button type="button" @click="startMemberForm()" :disabled="!canAddMember"
                                class="w-full flex items-center justify-center gap-2 h-12 rounded-xl border-2 border-dashed border-ink-300 text-sm font-bold text-ink-700 hover:border-ink-900 hover:text-ink-950 hover:bg-volt/5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-ink-300 disabled:hover:text-ink-700 disabled:hover:bg-transparent transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                <span x-text="canAddMember ? 'הוספת בן משפחה' : 'ניתן להוסיף עד 20 בני משפחה'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Member form panel --}}
                    <template x-if="memberPanelOpen">
                        <div x-cloak>
                            <div class="flex flex-col items-center text-center mb-6">
                                <h3 class="text-xl font-bold text-ink-950" x-text="_editingIndex != null ? 'עריכת בן משפחה' : 'הוספת בן משפחה'"></h3>
                                <p class="text-sm text-ink-500 mt-1">פרטי הבן משפחה ישולבו באירועי הלוח — שדות התחביבים הם אופציונליים</p>
                            </div>

                            <div class="max-w-md mx-auto space-y-5">
                                <div class="flex flex-col items-center">
                                    <div class="relative mb-3">
                                        <template x-if="memberForm.imageUrl">
                                            <img :src="memberForm.imageUrl" alt="" class="w-24 h-24 rounded-full object-cover border border-ink-300 shadow-sm">
                                        </template>
                                        <template x-if="!memberForm.imageUrl && memberForm.existingImageUrl">
                                            <img :src="memberForm.existingImageUrl" alt="" class="w-24 h-24 rounded-full object-cover border border-ink-300 shadow-sm">
                                        </template>
                                        <template x-if="!memberForm.imageUrl && !memberForm.existingImageUrl">
                                            <div class="w-24 h-24 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center mb-3">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </template>
                                        <button type="button" @click="clearMemberImage()"
                                            x-show="memberForm.imageUrl || memberForm.existingImageUrl"
                                            x-cloak
                                            class="btn-icon-ghost btn-icon-sm absolute -top-1 -right-1 bg-ink-950/70 text-white hover:bg-danger"
                                            aria-label="הסר תמונה">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round">
                                                <path d="M6 6l12 12M18 6L6 18" />
                                            </svg>
                                        </button>
                                    </div>

                                    <label class="text-xs font-semibold text-ink-600 hover:text-ink-950 cursor-pointer transition-colors">
                                        <span x-text="_editingIndex != null ? 'החלפת תמונת פרופיל' : 'בחירת תמונת פרופיל'"></span>
                                        <span class="text-ink-400 font-normal">(אופציונלי)</span>
                                        <input type="file" x-ref="memberImageInput" accept="image/jpeg,image/png,image/webp" class="hidden"
                                            @change="onMemberImage($event)">
                                    </label>
                                    <p x-show="memberForm.imageError" x-cloak class="mt-1 text-xs font-medium text-danger" x-text="memberForm.imageError"></p>
                                </div>

                                <div>
                                    <label for="memberName" class="label">שם מלא</label>
                                    <input type="text" id="memberName" x-model="memberForm.name" maxlength="255"
                                        class="input"
                                        :class="!memberForm.name.trim() ? '!border-danger' : ''"
                                        placeholder="לדוגמה: דנה כהן"
                                        @keydown.enter="saveMember()">
                                    <p x-show="!memberForm.name.trim()" x-cloak class="mt-1 text-xs font-medium text-danger">שם נדרש</p>
                                </div>

                                <div>
                                    <label for="memberBirthDate" class="label">תאריך לידה</label>
                                    <input type="date" id="memberBirthDate" x-model="memberForm.birthDate"
                                        class="input"
                                        :class="!memberForm.birthDate ? '!border-danger' : ''"
                                        @keydown.enter="saveMember()">
                                    <p x-show="!memberForm.birthDate" x-cloak class="mt-1 text-xs font-medium text-danger">תאריך לידה נדרש</p>
                                </div>

                                <div>
                                    <label for="memberAnniversaryDate" class="label">תאריך יום נישואין <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                                    <input type="date" id="memberAnniversaryDate" x-model="memberForm.anniversaryDate"
                                        class="input"
                                        @keydown.enter="saveMember()">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-tag-input label="תחביבים" liveValues="memberForm.hobbies"
                                        @tag-change="onMemberTags('hobbies', $event)" />
                                    <x-tag-input label="ספורט אהוב" liveValues="memberForm.favorite_sports"
                                        @tag-change="onMemberTags('favorite_sports', $event)" />
                                    <x-tag-input label="מוזיקה אהובה" liveValues="memberForm.favorite_music"
                                        @tag-change="onMemberTags('favorite_music', $event)" />
                                    <x-tag-input label="אוכל אהוב" liveValues="memberForm.favorite_food"
                                        @tag-change="onMemberTags('favorite_food', $event)" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <button type="button" @click="saveMemberAndContinue()" :disabled="!memberFormValid"
                                        class="btn btn-outline w-full">שמירה והוספה של עוד</button>
                                    <button type="button" @click="saveMember()" :disabled="!memberFormValid"
                                        class="btn btn-primary w-full">שמירת הפרטים</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Step 4: events --}}
                <div x-show="currentStep === 'events'" x-cloak class="py-4">
                    {{-- Hub: auto-generated events, manual events and add button --}}
                    <div x-show="!eventPanelOpen">
                        <div class="flex flex-col items-center text-center mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-volt text-ink-950 flex items-center justify-center mb-4">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-ink-950">רוצים להוסיף אירועים?</h3>
                            <p class="text-sm text-ink-500 mt-1">אירועי יום הולדת ויום נישואין נוצרים אוטומטית מבני המשפחה — אפשר גם לדלג</p>
                        </div>

                        <div class="max-w-md mx-auto space-y-4">
                            <template x-if="autoEvents.length">
                                <div>
                                    <p class="text-xs font-bold text-ink-700 mb-2">אירועים אוטומטיים</p>
                                    <div class="space-y-2">
                                        <template x-for="autoEvent in autoEvents" :key="autoEvent.id">
                                            <div class="card flex items-center gap-3 p-3">
                                                <div class="w-10 h-10 rounded-full bg-volt/20 text-ink-700 flex items-center justify-center shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold text-ink-950 truncate" x-text="autoEvent.title"></p>
                                                    <p class="text-xs text-ink-500" x-text="autoEvent.date"></p>
                                                </div>
                                                <span class="chip bg-volt/20 text-ink-700">אוטומטי</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="events.length">
                                <div>
                                    <p class="text-xs font-bold text-ink-700 mb-2">האירועים שהוספתם</p>
                                    <div class="space-y-2">
                                        <template x-for="(event, index) in events" :key="index">
                                            <div class="card flex items-center gap-3 p-3">
                                                <template x-if="event.coverUrl">
                                                    <img :src="event.coverUrl" alt="" class="w-12 h-9 object-cover rounded-lg border border-ink-200 shrink-0">
                                                </template>
                                                <template x-if="!event.coverUrl">
                                                    <div class="w-12 h-9 rounded-lg bg-ink-100 text-ink-400 flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </template>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold text-ink-950 truncate" x-text="event.title"></p>
                                                    <p class="text-xs text-ink-500">
                                                        <span x-text="event.eventDate"></span>
                                                        <template x-if="event.familyMemberKey && memberNameByKey[event.familyMemberKey]">
                                                            <span x-text="' · ' + memberNameByKey[event.familyMemberKey]"></span>
                                                        </template>
                                                    </p>
                                                </div>
                                                <button type="button" @click="editEvent(index)" title="עריכה"
                                                    class="btn-icon-ghost btn-icon-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button type="button" @click="removeEvent(index)" title="הסרה"
                                                    class="btn-icon-ghost btn-icon-sm hover:text-danger">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="startEventForm()" :disabled="!canAddEvent"
                                class="w-full flex items-center justify-center gap-2 h-12 rounded-xl border-2 border-dashed border-ink-300 text-sm font-bold text-ink-700 hover:border-ink-900 hover:text-ink-950 hover:bg-volt/5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-ink-300 disabled:hover:text-ink-700 disabled:hover:bg-transparent transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                <span x-text="canAddEvent ? 'הוספת אירוע' : 'ניתן להוסיף עד 50 אירועים'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Event form panel --}}
                    <template x-if="eventPanelOpen">
                        <div x-cloak>
                            <div class="flex flex-col items-center text-center mb-6">
                                <h3 class="text-xl font-bold text-ink-950" x-text="_eventEditingIndex != null ? 'עריכת אירוע' : 'הוספת אירוע'"></h3>
                                <p class="text-sm text-ink-500 mt-1">פרטי האירוע ישולבו בלוח השנה בתאריך שבחרתם</p>
                            </div>

                            <div class="max-w-md mx-auto space-y-5">
                                <div>
                                    <label for="eventTitle" class="label">כותרת האירוע</label>
                                    <input type="text" id="eventTitle" x-model="eventForm.title" maxlength="255"
                                        class="input"
                                        :class="!eventForm.title.trim() ? '!border-danger' : ''"
                                        placeholder="לדוגמה: יום הולדת 30 לדני"
                                        @keydown.enter="saveEvent()">
                                    <p x-show="!eventForm.title.trim()" x-cloak class="mt-1 text-xs font-medium text-danger">כותרת נדרשת</p>
                                </div>

                                <div>
                                    <label for="eventDescription" class="label">תיאור <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                                    <textarea id="eventDescription" x-model="eventForm.description" rows="3" maxlength="1000"
                                        class="input"
                                        placeholder="פרטים נוספים על האירוע"></textarea>
                                </div>

                                <div>
                                    <label for="eventDate" class="label">תאריך האירוע</label>
                                    <input type="date" id="eventDate" x-model="eventForm.eventDate"
                                        class="input"
                                        :class="!eventForm.eventDate ? '!border-danger' : ''"
                                        @keydown.enter="saveEvent()">
                                    <p x-show="!eventForm.eventDate" x-cloak class="mt-1 text-xs font-medium text-danger">תאריך נדרש</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="eventStartTime" class="label">שעת התחלה <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                                        <input type="time" id="eventStartTime" x-model="eventForm.startTime" class="input">
                                    </div>
                                    <div>
                                        <label for="eventEndTime" class="label">שעת סיום <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                                        <input type="time" id="eventEndTime" x-model="eventForm.endTime" class="input">
                                    </div>
                                </div>

                                <div>
                                    <label for="eventFamilyMember" class="label">שיוך לחבר משפחה <span class="text-xs text-ink-500 font-normal">(אופציונלי)</span></label>
                                    <select id="eventFamilyMember" x-model="eventForm.familyMemberKey" class="input">
                                        <option value="">ללא שיוך לחבר משפחה</option>
                                        <template x-for="(member, index) in members" :key="index">
                                            <option :value="member.key" x-text="member.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div @cover-staged="onEventCoverStaged($event)" @cover-cleared="onEventCoverCleared()">
                                    <x-cover-upload name="event_cover_image_path" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <button type="button" @click="saveEventAndContinue()" :disabled="!eventFormValid"
                                        class="btn btn-outline w-full">שמירה והוספה של עוד</button>
                                    <button type="button" @click="saveEvent()" :disabled="!eventFormValid"
                                        class="btn btn-primary w-full">שמירת האירוע</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Step 5: review --}}
                <div x-show="currentStep === 'review'" x-cloak class="py-4">
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-volt text-ink-950 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-ink-950">הכול מוכן?</h3>
                        <p class="text-sm text-ink-500 mt-1">סקרו את הפרטים ולחצו ליצירת לוח השנה</p>
                    </div>

                    <div class="max-w-2xl mx-auto space-y-6">
                        {{-- Palette switcher (first thing on this step, above the preview) --}}
                        <div class="flex flex-col items-center text-center">
                            <h4 class="text-xs font-semibold text-ink-500 mb-3">ערכת צבעים</h4>
                            <div class="flex flex-wrap items-center justify-center gap-3">
                                <button type="button" @click="selectTheme('')"
                                    title="ברירת מחדל" aria-label="ברירת מחדל"
                                    :class="selectedThemeKey === '' ? 'ring-2 ring-ink-900 ring-offset-2' : 'border-ink-300 hover:border-ink-900'"
                                    class="w-9 h-9 rounded-full border bg-white transition-colors"></button>
                                @foreach (config('themes') as $themeKey => $theme)
                                    <button type="button" @click="selectTheme('{{ $themeKey }}')"
                                        title="{{ $theme['name'] }}" aria-label="{{ $theme['name'] }}"
                                        :class="selectedThemeKey === '{{ $themeKey }}' ? 'ring-2 ring-ink-900 ring-offset-2' : 'border-ink-200 hover:border-ink-900'"
                                        class="w-9 h-9 rounded-full border transition-colors"
                                        style="background-color: {{ $theme['day_box_bg_color'] }};"></button>
                                @endforeach
                            </div>
                            <p class="text-xs text-ink-500 mt-3">נבחר: <span class="font-semibold text-ink-700" x-text="selectedThemeKey ? themes[selectedThemeKey].name : 'ברירת מחדל'"></span></p>
                        </div>

                        {{-- Live preview of the current month --}}
                        <div>
                            <div class="flex flex-col items-center text-center mb-5">
                                <h4 class="text-lg font-bold text-ink-950">
                                    {{ $monthNames[$month] }} {{ $year }}
                                    <span class="text-sm font-medium text-ink-500">· {{ $hebrewMonthName }} {{ $hebrewYear }}</span>
                                </h4>
                            </div>

                            <div class="relative" x-data="calendarPreviewScroll()">
                                <div dir="ltr" x-ref="scroller" @scroll.passive="onScroll()"
                                    class="overflow-x-auto overscroll-x-contain scroll-smooth md:overflow-visible">
                                    <div class="min-w-[34rem] md:min-w-0">
                                        <div id="previewGrid" x-ref="previewGrid" class="card p-3 sm:p-6 relative overflow-hidden"
                                            style="font-family: {{ $styles['fontFamily'] }};">
                                            <div id="previewOverlay" x-ref="previewOverlay"
                                                class="absolute inset-0 rounded-lg pointer-events-none"
                                                style="{{ $styles['overlay'] }}"></div>

                                            <div class="relative">
                                                <div class="grid grid-cols-7 gap-1.5 sm:gap-2 mb-4">
                                                    @foreach (['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'] as $day)
                                                        <div class="weekday-header text-center text-sm font-medium"
                                                            style="color: {{ $styles['weekdayColor'] }};">{{ $day }}</div>
                                                    @endforeach
                                                </div>

                                                @php
                                                    $hebrewDateService = app(\App\Services\HebrewDateService::class);
                                                    $firstDayOfMonth = \Carbon\Carbon::create($year, $month, 1);
                                                    $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
                                                    $startDayOfWeek = $firstDayOfMonth->dayOfWeek;
                                                    $totalDays = $lastDayOfMonth->day;
                                                    $today = \Carbon\Carbon::today();
                                                @endphp

                                                <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                                                    @for ($i = 0; $i < 42; $i++)
                                                        @php
                                                            $dayNumber = $i - $startDayOfWeek + 1;
                                                            $isCurrentMonth = $dayNumber >= 1 && $dayNumber <= $totalDays;
                                                            $isToday = false;
                                                            $hebrewDate = '';
                                                            $currentDate = null;
                                                            $dateKey = '';

                                                            if ($isCurrentMonth) {
                                                                $currentDate = \Carbon\Carbon::create($year, $month, $dayNumber);
                                                                $isToday = $currentDate->isSameDay($today);
                                                                $hebrewDate = $hebrewDateService->toHebrewDayMonthString($currentDate);
                                                                $dateKey = $currentDate->format('Y-m-d');
                                                            }
                                                        @endphp

                                                        @if ($isCurrentMonth)
                                                            <div class="day-cell rounded-lg border p-1.5 sm:p-2 min-h-16 sm:min-h-20 {{ $isToday ? 'day-cell-today' : '' }}"
                                                                data-date="{{ $dateKey }}"
                                                                @if ($isToday)
                                                                    style="background-color: var(--color-primary); border-color: var(--color-accent); color: var(--color-accent);"
                                                                @else
                                                                    style="background-color: {{ $styles['dayBox']['backgroundColor'] }}; border-color: var(--color-border); color: {{ $styles['dayBox']['fontColor'] }};"
                                                                @endif>
                                                                <div class="flex justify-between items-start mb-1">
                                                                    <span class="day-number text-lg font-bold">{{ $dayNumber }}</span>
                                                                    @if ($isToday)
                                                                        <span class="text-xs font-medium" style="color: var(--color-white);">היום</span>
                                                                    @endif
                                                                </div>
                                                                <div class="day-hebrew text-xs mb-2" style="opacity: 0.7;">{{ $hebrewDate }}</div>
                                                                <div class="day-events space-y-1">
                                                                    @foreach ($holidaysByDate[$dateKey] ?? [] as $holiday)
                                                                        <div class="chip chip-holiday text-xs truncate">{{ $holiday['title'] ?? $holiday['hebrew'] ?? 'חג' }}</div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="min-h-16 sm:min-h-20 rounded-lg"></div>
                                                        @endif
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Mobile scroll affordances (hidden on desktop) --}}
                                <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-white to-transparent md:hidden" aria-hidden="true"></div>
                                <div class="pointer-events-none absolute inset-y-0 left-0 w-8 bg-gradient-to-r from-white to-transparent md:hidden" aria-hidden="true"></div>

                                <div x-cloak x-show="showIndicator" x-transition.opacity.duration.200ms
                                    class="absolute inset-y-0 right-2 flex items-center pointer-events-none md:hidden" aria-hidden="true">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-ink-950/85 text-volt shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="max-w-md mx-auto space-y-3">
                            <div class="card flex items-center gap-3 p-4">
                            <template x-if="coverUrl">
                                <img :src="coverUrl" alt="" class="h-14 w-24 object-cover rounded-lg border border-ink-200 shrink-0">
                            </template>
                            <template x-if="!coverUrl">
                                <div class="flex items-center justify-center h-14 w-24 rounded-lg bg-gradient-to-br from-ink-100 to-ink-200 text-ink-400 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </template>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-ink-950 truncate" x-text="calendarName"></p>
                                <p class="text-xs text-ink-500" x-text="coverFile ? 'עם תמונת כריכה' : 'ללא תמונת כריכה'"></p>
                            </div>
                        </div>

                        <div class="card p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-bold text-ink-950">בני משפחה</p>
                                <span class="chip bg-ink-100 text-ink-700" x-text="memberCount === 1 ? 'בן משפחה אחד' : memberCount + ' בני משפחה'"></span>
                            </div>

                            <template x-if="!members.length">
                                <p class="text-sm text-ink-500">לא נוספו בני משפחה — ניתן להוסיף אותם מאוחר יותר</p>
                            </template>

                            <div class="space-y-2">
                                <template x-for="(member, index) in members" :key="index">
                                    <div class="flex items-center gap-3">
                                        <template x-if="member.imageUrl">
                                            <img :src="member.imageUrl" alt="" class="w-8 h-8 rounded-full object-cover border border-ink-200 shrink-0">
                                        </template>
                                        <template x-if="!member.imageUrl">
                                            <div class="w-8 h-8 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-ink-950 truncate" x-text="member.name"></p>
                                            <p class="text-xs text-ink-500" x-text="'נולד: ' + member.birthDate"></p>
                                            <p class="text-xs text-ink-500" x-show="member.anniversaryDate" x-cloak x-text="'יום נישואין: ' + member.anniversaryDate"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="card p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-bold text-ink-950">אירועים</p>
                                <span class="chip bg-ink-100 text-ink-700" x-text="eventCount === 1 ? 'אירוע אחד' : eventCount + ' אירועים'"></span>
                            </div>

                            <template x-if="!events.length">
                                <p class="text-sm text-ink-500">לא נוספו אירועים ידניים — ניתן להוסיף אותם מאוחר יותר</p>
                            </template>

                            <div class="space-y-2">
                                <template x-for="(event, index) in events" :key="index">
                                    <div class="flex items-center gap-3">
                                        <template x-if="event.coverUrl">
                                            <img :src="event.coverUrl" alt="" class="w-8 h-10 object-cover rounded border border-ink-200 shrink-0">
                                        </template>
                                        <template x-if="!event.coverUrl">
                                            <div class="w-8 h-10 rounded bg-ink-100 text-ink-400 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-ink-950 truncate" x-text="event.title"></p>
                                            <p class="text-xs text-ink-500">
                                                <span x-text="event.eventDate"></span>
                                                <template x-if="event.familyMemberKey && memberNameByKey[event.familyMemberKey]">
                                                    <span x-text="' · ' + memberNameByKey[event.familyMemberKey]"></span>
                                                </template>
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p x-show="autoEvents.length" x-cloak class="text-xs text-ink-500 mt-3">
                                בני המשפחה ייצרו <span x-text="autoEvents.length"></span> אירועים אוטומטיים של יום הולדת ויום נישואין
                            </p>
                        </div>

                        <p x-show="!nameValid" x-cloak class="text-xs font-medium text-danger text-center">
                            נא להזין שם לוח שנה כדי לסיים
                        </p>

                        <button type="button" @click="submit()" :disabled="!canSubmit"
                            class="btn btn-primary btn-lg w-full justify-center">
                            <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-show="!submitting" x-cloak>יצירת לוח השנה</span>
                            <span x-show="submitting" x-cloak x-text="'יוצרים את הלוח... ' + progress + '%'"></span>
                        </button>

                        <div x-show="submitting" x-cloak class="h-2 rounded-full bg-ink-100 overflow-hidden">
                            <div class="h-full rounded-full bg-volt transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                        </div>
                        </div>
                    </div>
                </div>

                {{-- Global submit error --}}
                <div x-show="submitError" x-cloak class="mt-6">
                    <div class="alert alert-error whitespace-pre-line" x-text="submitError"></div>
                </div>

                {{-- Footer navigation --}}
                <div class="mt-8 pt-6 border-t border-ink-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" @click="back()" x-show="!isFirstStep" x-cloak
                        class="btn btn-outline w-full sm:w-auto">
                        → חזרה
                    </button>
                    <span x-show="isFirstStep" x-cloak></span>

                    <button type="button"
                        x-show="currentStep === 'name' || currentStep === 'cover'"
                        x-cloak @click="next()" :disabled="!canProceed" class="btn btn-primary w-full sm:w-auto">
                        המשך ←
                    </button>

                    <div x-show="currentStep === 'members' && !memberPanelOpen" x-cloak class="flex flex-col gap-3 w-full sm:w-auto sm:flex-row sm:items-center">
                        <button type="button" @click="goToStep(3)" class="btn btn-primary w-full sm:w-auto">
                            המשך לאירועים ←
                        </button>
                        <button type="button" @click="goToStep(4)" class="btn btn-outline w-full sm:w-auto">
                            דילוג לסיכום ←
                        </button>
                    </div>

                    <span x-show="currentStep === 'members' && memberPanelOpen" x-cloak></span>

                    <button type="button"
                        x-show="currentStep === 'events' && !eventPanelOpen"
                        x-cloak @click="goToStep(4)" class="btn btn-primary w-full sm:w-auto">
                        דילוג לסיכום ←
                    </button>

                    <span x-show="currentStep === 'events' && eventPanelOpen" x-cloak></span>

                    <span x-show="currentStep === 'review'" x-cloak></span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
