import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.min.css';

Alpine.plugin(collapse);

Alpine.store('sidebar', {
    collapsed: (() => {
        try {
            return localStorage.getItem('family-calendar-sidebar') === 'collapsed';
        } catch {
            return false;
        }
    })(),
    toggle() {
        this.collapsed = !this.collapsed;
        try {
            localStorage.setItem('family-calendar-sidebar', this.collapsed ? 'collapsed' : 'expanded');
        } catch {
            //
        }
    },
});

Alpine.data('coverCrop', (options = {}) => ({
    existingUrl: options.existingUrl ?? null,
    maxSizeBytes: options.maxSizeBytes ?? 50 * 1024 * 1024,

    open: false,
    rawUrl: null,
    stagedFile: null,
    stagedUrl: null,
    error: '',
    dragOver: false,
    _fileName: null,
    _cropper: null,

    init() {
        this.$watch('open', (value) => {
            if (!value) {
                this.destroyCropper();
            }
        });
    },

    onSelect(event) {
        const file = event.target.files[0];

        if (file) {
            this.openCrop(file);
        }
    },

    onDrop(event) {
        this.dragOver = false;
        const file = event.dataTransfer.files[0];

        if (file) {
            this.openCrop(file);
        }
    },

    openCrop(file) {
        this.error = '';

        if (!file.type.startsWith('image/')) {
            this.error = 'הקובץ שנבחר אינו תמונה';
            this.clearInput();
            return;
        }

        if (file.size > this.maxSizeBytes) {
            this.error = 'הקובץ גדול מדי — גודל מקסימלי 50MB';
            this.clearInput();
            return;
        }

        this._fileName = file.name;
        if (this.rawUrl) {
            URL.revokeObjectURL(this.rawUrl);
        }
        this.rawUrl = URL.createObjectURL(file);
        this.open = true;
        this.$nextTick(() => this.initCropper());
    },

    initCropper() {
        this.destroyCropper();
        const img = this.$refs.cropImg;

        const create = () => {
            if (!this.open) {
                return;
            }

            this._cropper = new Cropper(img, {
                aspectRatio: 21 / 9,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                responsive: true,
                modal: true,
                guides: true,
                highlight: false,
                background: false,
                checkOrientation: true,
                wheelZoomRatio: 0.12,
            });
        };

        if (img.complete && img.naturalWidth > 0) {
            create();
        } else {
            img.addEventListener('load', create, { once: true });
            img.addEventListener('error', () => {
                this.error = 'לא ניתן היה לטעון את התמונה';
                this.cancel();
            }, { once: true });
        }
    },

    destroyCropper() {
        if (this._cropper) {
            this._cropper.destroy();
            this._cropper = null;
        }
    },

    zoomIn() {
        if (this._cropper) {
            this._cropper.zoom(0.15);
        }
    },

    zoomOut() {
        if (this._cropper) {
            this._cropper.zoom(-0.15);
        }
    },

    resetCrop() {
        if (this._cropper) {
            this._cropper.reset();
        }
    },

    confirm() {
        if (!this._cropper) {
            return;
        }

        let canvas;
        try {
            canvas = this._cropper.getCroppedCanvas({
                maxWidth: 1920,
                fillColor: '#FFFFFF',
                imageSmoothingQuality: 'high',
            });
        } catch (error) {
            this.error = 'לא ניתן היה לחתוך את התמונה';
            return;
        }

        canvas.toBlob((blob) => {
            if (!blob) {
                this.error = 'לא ניתן היה לייצר את התמונה החתוכה';
                return;
            }

            const file = new File([blob], this._fileName ?? 'cover.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.fileInput.files = dt.files;

            if (this.stagedUrl) {
                URL.revokeObjectURL(this.stagedUrl);
            }
            this.stagedFile = file;
            this.stagedUrl = URL.createObjectURL(blob);
            this.$dispatch('cover-staged', { file });
            this.closeModal();
        }, 'image/jpeg', 0.9);
    },

    cancel() {
        if (this.stagedFile) {
            const dt = new DataTransfer();
            dt.items.add(this.stagedFile);
            this.$refs.fileInput.files = dt.files;
        } else {
            this.clearInput();
        }
        this.closeModal();
    },

    revert() {
        if (this.stagedUrl) {
            URL.revokeObjectURL(this.stagedUrl);
        }
        this.stagedFile = null;
        this.stagedUrl = null;
        this.clearInput();
        this.$dispatch('cover-cleared');
    },

    clearInput() {
        this.$refs.fileInput.value = '';
    },

    closeModal() {
        this.open = false;
        if (this.rawUrl) {
            URL.revokeObjectURL(this.rawUrl);
            this.rawUrl = null;
        }
    },
}));

const createThemePicker = (options = {}) => ({
    themesOpen: false,
    pendingTheme: null,
    pendingName: '',
    applying: false,
    applyError: '',
    themes: options.themes ?? {},

    init() {
        const pending = sessionStorage.getItem('family-calendar-theme-applied');
        if (pending) {
            sessionStorage.removeItem('family-calendar-theme-applied');
            this.$nextTick(() => this.showToast(pending));
        }
    },

    selectTheme(key) {
        this.applyError = '';
        this.pendingTheme = key;
        this.pendingName = this.themes?.[key]?.name ?? '';
    },

    cancelTheme() {
        this.pendingTheme = null;
    },

    async applyTheme(key) {
        this.applying = true;
        this.applyError = '';

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]');
            const formData = new FormData();
            formData.append('theme', key);
            if (options.month != null) {
                formData.append('month', options.month);
            }

            const res = await fetch(options.applyUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'אירעה שגיאה בהחלת הנושא');
            }

            const message = data.message || '';
            let needsReload = false;

            if (window.__monthGrid) {
                needsReload = window.__monthGrid.applyThemeFields(data.fields || {});
            }

            this.themesOpen = false;
            this.pendingTheme = null;

            if (needsReload) {
                sessionStorage.setItem('family-calendar-theme-applied', message);

                window.location.reload();

                return;
            }

            this.showToast(message);
        } catch (error) {
            this.applyError = error.message || 'אירעה שגיאה בהחלת הנושא';
        } finally {
            this.applying = false;
        }
    },

    showToast(message) {
        let toast = document.getElementById('themeToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'themeToast';
            toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-full bg-ink-950 text-volt text-sm font-bold shadow-2xl transition-opacity duration-300';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.opacity = '1';
        clearTimeout(this._toastTimer);
        this._toastTimer = setTimeout(() => { toast.style.opacity = '0'; }, 3500);
    },
});

Alpine.data('themePicker', (options = {}) => ({
    ...createThemePicker(options),
}));

Alpine.data('monthPage', (options = {}) => ({
    settingsOpen: options.settingsOpen ?? false,
    ...createThemePicker(options),
}));

Alpine.data('tagInput', (options = {}) => ({
    name: options.name ?? '',
    values: options.values ?? [],
    tags: [],
    draft: '',

    init() {
        this.tags = Array.from(this.values ?? [])
            .map((value) => String(value).trim())
            .filter(Boolean);

        this.$watch('tags', (value) => {
            this.$dispatch('tag-change', { tags: [...value] });
        });
    },

    addDraft() {
        const parts = String(this.draft || '')
            .split(',')
            .map((part) => part.trim())
            .filter(Boolean);

        parts.forEach((part) => {
            if (!this.tags.includes(part)) {
                this.tags.push(part);
            }
        });

        this.draft = '';
    },

    removeTag(index) {
        this.tags.splice(index, 1);
    },

    onBackspace() {
        if (this.draft === '' && this.tags.length) {
            this.removeTag(this.tags.length - 1);
        }
    },
}));

Alpine.data('calendarWizard', (options = {}) => ({
    submitUrl: options.submitUrl ?? '',
    themes: options.themes ?? {},
    previewYear: options.year ?? new Date().getFullYear(),
    previewMonth: options.month ?? new Date().getMonth() + 1,
    selectedThemeKey: '',

    steps: [
        { key: 'name', label: 'שם לוח השנה' },
        { key: 'cover', label: 'תמונת כריכה' },
        { key: 'members', label: 'בני משפחה' },
        { key: 'events', label: 'אירועים' },
        { key: 'review', label: 'סיכום' },
    ],

    step: 0,
    submitting: false,
    progress: 0,
    submitError: '',
    calendarName: '',
    nameTouched: false,
    calendarNameError: '',
    coverFile: null,
    coverUrl: null,
    members: [],
    memberPanelOpen: false,
    _editingIndex: null,
    _returnStep: 2,
    memberImageDragOver: false,
    memberForm: {
        name: '',
        birthDate: '',
        anniversaryDate: '',
        image: null,
        imageUrl: null,
        imageRemoved: false,
        existingImageUrl: null,
        imageError: '',
        hobbies: [],
        sports: [],
        music: [],
        food: [],
    },
    events: [],
    eventPanelOpen: false,
    _eventEditingIndex: null,
    _eventReturnStep: 3,
    eventForm: {
        title: '',
        description: '',
        eventDate: '',
        startTime: '',
        endTime: '',
        familyMemberKey: '',
        cover: null,
        coverUrl: null,
        coverError: '',
    },

    get currentStep() {
        return this.steps[this.step].key;
    },

    get stepLabel() {
        return this.steps[this.step].label;
    },

    get isFirstStep() {
        return this.step === 0;
    },

    get isLastStep() {
        return this.step === this.steps.length - 1;
    },

    get nameValid() {
        return this.calendarName.trim().length > 0;
    },

    get canProceed() {
        return this.currentStep !== 'name' || this.nameValid;
    },

    get canSubmit() {
        return this.nameValid && !this.submitting;
    },

    get memberCount() {
        return this.members.length;
    },

    get canAddMember() {
        return this.memberCount < 20;
    },

    get memberFormValid() {
        return this.memberForm.name.trim().length > 0 && this.memberForm.birthDate.length > 0;
    },

    get eventCount() {
        return this.events.length;
    },

    get canAddEvent() {
        return this.eventCount < 50;
    },

    get eventFormValid() {
        return this.eventForm.title.trim().length > 0 && this.eventForm.eventDate.length > 0;
    },

    get memberNameByKey() {
        const map = {};
        this.members.forEach((member) => {
            map[member.key] = member.name;
        });
        return map;
    },

    get autoEvents() {
        const result = [];
        this.members.forEach((member) => {
            if (member.birthDate) {
                result.push({
                    id: 'birth-' + member.key,
                    type: 'birthday',
                    title: 'יום הולדת - ' + member.name,
                    date: member.birthDate,
                    memberKey: member.key,
                });
            }
            if (member.anniversaryDate) {
                result.push({
                    id: 'anniversary-' + member.key,
                    type: 'anniversary',
                    title: 'יום נישואין - ' + member.name,
                    date: member.anniversaryDate,
                    memberKey: member.key,
                });
            }
        });
        return result;
    },

    isStepDone(index) {
        return index < this.step;
    },

    next() {
        if (this.memberPanelOpen || this.eventPanelOpen) {
            return;
        }

        if (!this.canProceed) {
            this.nameTouched = true;
            return;
        }

        this.goToStep(this.step + 1);
    },

    back() {
        if (this.memberPanelOpen) {
            this.cancelMemberForm();
            return;
        }

        if (this.eventPanelOpen) {
            this.cancelEventForm();
            return;
        }

        if (this.step > 0) {
            this.goToStep(this.step - 1);
        }
    },

    goToStep(target) {
        if (target < 0 || target >= this.steps.length) {
            return;
        }

        if (target !== 2) {
            this.memberPanelOpen = false;
        }

        if (target !== 3) {
            this.eventPanelOpen = false;
        }

        this.submitError = '';
        this.step = target;

        if (this.steps[target].key === 'review') {
            this.$nextTick(() => this.populatePreview());
        }
    },

    startMemberForm() {
        this._editingIndex = null;
        this._returnStep = 2;
        this.resetMemberForm();
        this.memberPanelOpen = true;
    },

    editMember(index) {
        const member = this.members[index];
        this._editingIndex = index;
        this._returnStep = this.step;
        this.memberImageDragOver = false;
        this.memberForm = {
            name: member.name,
            birthDate: member.birthDate,
            anniversaryDate: member.anniversaryDate,
            image: null,
            imageUrl: null,
            imageRemoved: false,
            existingImageUrl: member.imageUrl,
            imageError: '',
            hobbies: [...member.hobbies],
            sports: [...member.sports],
            music: [...member.music],
            food: [...member.food],
        };
        this.step = 2;
        this.memberPanelOpen = true;
        this.submitError = '';
    },

    cancelMemberForm() {
        this._editingIndex = null;
        this.clearMemberImage();
        this.resetMemberForm();
        this.memberPanelOpen = false;
        this.step = this._returnStep;
        this._returnStep = 2;
    },

    collectMemberData() {
        return {
            name: this.memberForm.name.trim(),
            birthDate: this.memberForm.birthDate,
            anniversaryDate: this.memberForm.anniversaryDate,
            image: this.memberForm.image,
            imageUrl: this.memberForm.imageUrl,
            hobbies: [...this.memberForm.hobbies],
            sports: [...this.memberForm.sports],
            music: [...this.memberForm.music],
            food: [...this.memberForm.food],
        };
    },

    applyEditPreservation(data) {
        if (this._editingIndex == null) {
            return data;
        }

        const previous = this.members[this._editingIndex];
        if (this.memberForm.image) {
            data.image = this.memberForm.image;
            data.imageUrl = this.memberForm.imageUrl;
        } else if (this.memberForm.imageRemoved) {
            data.image = null;
            data.imageUrl = null;
        } else {
            data.image = previous.image;
            data.imageUrl = previous.imageUrl;
        }

        return data;
    },

    commitMember() {
        let data = this.applyEditPreservation(this.collectMemberData());

        if (this._editingIndex != null) {
            const previous = this.members[this._editingIndex];

            if ((this.memberForm.image || this.memberForm.imageRemoved) && previous.imageUrl) {
                URL.revokeObjectURL(previous.imageUrl);
            }

            data.key = previous.key;
            this.members.splice(this._editingIndex, 1, data);
        } else {
            data.key = this.generateKey();
            this.members.push(data);
        }

        this.memberForm.image = null;
        this.memberForm.imageUrl = null;
    },

    saveMember() {
        if (!this.memberFormValid) {
            return;
        }

        this.commitMember();
        this.closeMemberForm();
    },

    saveMemberAndContinue() {
        if (!this.memberFormValid) {
            return;
        }

        this.commitMember();
        this._editingIndex = null;
        this._returnStep = 2;
        this.resetMemberForm();
    },

    closeMemberForm() {
        this._editingIndex = null;
        this.clearMemberImage();
        this.resetMemberForm();
        this.memberPanelOpen = false;
        this.step = this._returnStep;
        this._returnStep = 2;
    },

    removeMember(index) {
        const member = this.members[index];
        this.events = this.events.filter((event) => event.familyMemberKey !== member.key);
        if (member.imageUrl) {
            URL.revokeObjectURL(member.imageUrl);
        }
        this.members.splice(index, 1);
    },

    resetMemberForm() {
        this.clearMemberImage();
        this.memberImageDragOver = false;
        this.memberForm = {
            name: '',
            birthDate: '',
            anniversaryDate: '',
            image: null,
            imageUrl: null,
            imageRemoved: false,
            existingImageUrl: null,
            imageError: '',
            hobbies: [],
            sports: [],
            music: [],
            food: [],
        };
    },

    clearMemberImage() {
        if (this.memberForm.imageUrl) {
            URL.revokeObjectURL(this.memberForm.imageUrl);
        }
        this.memberForm.image = null;
        this.memberForm.imageUrl = null;
        this.memberForm.existingImageUrl = null;
        this.memberForm.imageRemoved = true;
        if (this.$refs.memberImageInput) {
            this.$refs.memberImageInput.value = '';
        }
    },

    generateKey() {
        if (crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return Date.now().toString(36) + Math.random().toString(36).slice(2);
    },

    startEventForm() {
        this._eventEditingIndex = null;
        this._eventReturnStep = 3;
        this.resetEventForm();
        this.eventPanelOpen = true;
    },

    editEvent(index) {
        const event = this.events[index];
        this._eventEditingIndex = index;
        this._eventReturnStep = this.step;
        this.eventForm = {
            title: event.title,
            description: event.description,
            eventDate: event.eventDate,
            startTime: event.startTime,
            endTime: event.endTime,
            familyMemberKey: event.familyMemberKey,
            cover: null,
            coverUrl: null,
            coverError: '',
        };
        this.step = 3;
        this.eventPanelOpen = true;
        this.submitError = '';
    },

    cancelEventForm() {
        this._eventEditingIndex = null;
        this.clearEventCover();
        this.resetEventForm();
        this.eventPanelOpen = false;
        this.step = this._eventReturnStep;
        this._eventReturnStep = 3;
    },

    collectEventData() {
        return {
            title: this.eventForm.title.trim(),
            description: this.eventForm.description.trim(),
            eventDate: this.eventForm.eventDate,
            startTime: this.eventForm.startTime,
            endTime: this.eventForm.endTime,
            familyMemberKey: this.eventForm.familyMemberKey,
            cover: this.eventForm.cover,
            coverUrl: this.eventForm.coverUrl,
        };
    },

    commitEvent() {
        let data = this.collectEventData();

        if (this._eventEditingIndex != null) {
            const previous = this.events[this._eventEditingIndex];
            if (this.eventForm.cover) {
                data.cover = this.eventForm.cover;
                data.coverUrl = this.eventForm.coverUrl;
                if (previous.coverUrl) {
                    URL.revokeObjectURL(previous.coverUrl);
                }
            } else {
                data.cover = previous.cover;
                data.coverUrl = previous.coverUrl;
            }
            this.events.splice(this._eventEditingIndex, 1, data);
        } else {
            this.events.push(data);
        }

        this.eventForm.cover = null;
        this.eventForm.coverUrl = null;
    },

    saveEvent() {
        if (!this.eventFormValid) {
            return;
        }

        this.commitEvent();
        this.closeEventForm();
    },

    saveEventAndContinue() {
        if (!this.eventFormValid) {
            return;
        }

        this.commitEvent();
        this._eventEditingIndex = null;
        this._eventReturnStep = 3;
        this.resetEventForm();
    },

    closeEventForm() {
        this._eventEditingIndex = null;
        this.clearEventCover();
        this.resetEventForm();
        this.eventPanelOpen = false;
        this.step = this._eventReturnStep;
        this._eventReturnStep = 3;
    },

    removeEvent(index) {
        const event = this.events[index];
        if (event.coverUrl) {
            URL.revokeObjectURL(event.coverUrl);
        }
        this.events.splice(index, 1);
    },

    resetEventForm() {
        this.clearEventCover();
        this.eventForm = {
            title: '',
            description: '',
            eventDate: '',
            startTime: '',
            endTime: '',
            familyMemberKey: '',
            cover: null,
            coverUrl: null,
            coverError: '',
        };
    },

    clearEventCover() {
        if (this.eventForm.coverUrl) {
            URL.revokeObjectURL(this.eventForm.coverUrl);
        }
        this.eventForm.cover = null;
        this.eventForm.coverUrl = null;
    },

    onEventCoverStaged(event) {
        if (this.eventForm.coverUrl) {
            URL.revokeObjectURL(this.eventForm.coverUrl);
        }
        this.eventForm.cover = event.detail.file;
        this.eventForm.coverUrl = URL.createObjectURL(event.detail.file);
    },

    onEventCoverCleared() {
        this.clearEventCover();
    },

    populatePreview() {
        const grid = document.getElementById('previewGrid');

        if (!grid) {
            return;
        }

        const eventsByDate = this.previewEventsByDate();

        grid.querySelectorAll('.day-cell[data-date]').forEach((cell) => {
            cell.querySelectorAll('.wizard-event-chip').forEach((el) => el.remove());

            const container = cell.querySelector('.day-events');
            if (!container) {
                return;
            }

            (eventsByDate[cell.dataset.date] ?? []).forEach((chip) => {
                const el = document.createElement('div');
                el.className = 'wizard-event-chip chip ' + chip.chipClass + ' text-xs truncate';
                el.textContent = chip.title;
                el.title = chip.title;
                container.appendChild(el);
            });
        });
    },

    previewEventsByDate() {
        const year = this.previewYear;
        const month = this.previewMonth;
        const map = {};

        const add = (dateKey, chip) => {
            if (!dateKey) {
                return;
            }
            (map[dateKey] = map[dateKey] ?? []).push(chip);
        };

        this.members.forEach((member) => {
            if (member.birthDate) {
                add(this.recurringDateKey(member.birthDate, year, month), {
                    chipClass: 'chip-birthday',
                    title: 'יום הולדת - ' + member.name,
                });
            }
            if (member.anniversaryDate) {
                add(this.recurringDateKey(member.anniversaryDate, year, month), {
                    chipClass: 'chip-anniversary',
                    title: 'יום נישואין - ' + member.name,
                });
            }
        });

        this.events.forEach((event) => {
            if (!event.eventDate) {
                return;
            }
            const parts = event.eventDate.split('-');
            if (parseInt(parts[0], 10) === year && parseInt(parts[1], 10) === month) {
                add(event.eventDate, { chipClass: 'chip-event', title: event.title });
            }
        });

        return map;
    },

    recurringDateKey(sourceDate, year, month) {
        const parts = sourceDate.split('-');
        const sourceMonth = parseInt(parts[1], 10);
        const sourceDay = parseInt(parts[2], 10);

        if (sourceMonth !== month) {
            return null;
        }

        const daysInMonth = new Date(year, month, 0).getDate();
        const day = String(Math.min(sourceDay, daysInMonth)).padStart(2, '0');
        const mm = String(month).padStart(2, '0');

        return `${year}-${mm}-${day}`;
    },

    selectTheme(key) {
        this.selectedThemeKey = key || '';
        this.applyThemeToPreview(this.selectedThemeKey ? this.themes[this.selectedThemeKey] : null);
    },

    applyThemeToPreview(fields) {
        const grid = document.getElementById('previewGrid');

        if (!grid) {
            return;
        }

        const overlay = document.getElementById('previewOverlay');
        const fontMap = {
            default: "'Heebo', sans-serif",
            modern: "'Assistant', sans-serif",
            traditional: "'Frank Ruhl Libre', serif",
            elegant: "'Rubik', sans-serif",
        };

        const hexToRgba = (hex, alpha) => {
            const h = hex.replace('#', '');
            const r = parseInt(h.substring(0, 2), 16);
            const g = parseInt(h.substring(2, 4), 16);
            const b = parseInt(h.substring(4, 6), 16);

            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        };

        const defaults = {
            fontFamily: fontMap.default,
            overlay: 'rgba(0, 0, 0, 0.3)',
            weekdayColor: '#6B6B75',
            dayBoxBg: 'rgba(255, 255, 255, 1)',
            dayBoxFont: '#2B2E3A',
        };

        const values = fields ? {
            fontFamily: fontMap[fields.font_choice] ?? fontMap.default,
            overlay: `rgba(0, 0, 0, ${fields.overlay_opacity / 100})`,
            weekdayColor: fields.weekday_color,
            dayBoxBg: hexToRgba(fields.day_box_bg_color, fields.day_box_bg_opacity / 100),
            dayBoxFont: fields.day_box_font_color,
        } : defaults;

        grid.style.fontFamily = values.fontFamily;

        if (overlay) {
            overlay.style.backgroundColor = values.overlay;
        }

        grid.querySelectorAll('.weekday-header').forEach((el) => {
            el.style.color = values.weekdayColor;
        });

        grid.querySelectorAll('.day-cell:not(.day-cell-today)').forEach((el) => {
            el.style.backgroundColor = values.dayBoxBg;
            el.style.color = values.dayBoxFont;
        });
    },

    onMemberImage(event) {
        this.handleMemberImageFile(event.target.files[0]);
    },

    onMemberImageDrop(event) {
        event.preventDefault();
        this.memberImageDragOver = false;
        const file = event.dataTransfer.files[0];

        if (file) {
            this.handleMemberImageFile(file);
        }
    },

    handleMemberImageFile(file) {
        this.memberForm.imageError = '';

        if (!file) {
            return;
        }
        if (!file.type.startsWith('image/')) {
            this.memberForm.imageError = 'הקובץ שנבחר אינו תמונה';
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            this.memberForm.imageError = 'הקובץ גדול מדי — גודל מקסימלי 10MB';
            return;
        }

        if (this.memberForm.imageUrl) {
            URL.revokeObjectURL(this.memberForm.imageUrl);
        }
        this.memberForm.image = file;
        this.memberForm.imageUrl = URL.createObjectURL(file);
        this.memberForm.imageRemoved = false;
        this.memberForm.existingImageUrl = null;
    },

    onCoverStaged(event) {
        if (this.coverUrl) {
            URL.revokeObjectURL(this.coverUrl);
        }
        this.coverFile = event.detail.file;
        this.coverUrl = URL.createObjectURL(event.detail.file);
    },

    onCoverCleared() {
        if (this.coverUrl) {
            URL.revokeObjectURL(this.coverUrl);
        }
        this.coverFile = null;
        this.coverUrl = null;
    },

    onMemberTags(field, event) {
        this.memberForm[field] = event.detail.tags;
    },

    submit() {
        if (!this.canSubmit) {
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]');
        const formData = new FormData();
        formData.append('name', this.calendarName.trim());
        if (this.coverFile) {
            formData.append('cover_image_path', this.coverFile);
        }
        this.members.forEach((member, index) => {
            formData.append(`members[${index}][key]`, member.key);
            formData.append(`members[${index}][name]`, member.name);
            formData.append(`members[${index}][birth_date]`, member.birthDate);
            if (member.anniversaryDate) {
                formData.append(`members[${index}][anniversary_date]`, member.anniversaryDate);
            }
            member.hobbies.forEach((tag) => formData.append(`members[${index}][hobbies][]`, tag));
            member.sports.forEach((tag) => formData.append(`members[${index}][favorite_sports][]`, tag));
            member.music.forEach((tag) => formData.append(`members[${index}][favorite_music][]`, tag));
            member.food.forEach((tag) => formData.append(`members[${index}][favorite_food][]`, tag));
            if (member.image) {
                formData.append(`members[${index}][image]`, member.image);
            }
        });
        this.events.forEach((event, index) => {
            formData.append(`events[${index}][title]`, event.title);
            formData.append(`events[${index}][event_date]`, event.eventDate);
            if (event.description) {
                formData.append(`events[${index}][description]`, event.description);
            }
            if (event.startTime) {
                formData.append(`events[${index}][start_time]`, event.startTime);
            }
            if (event.endTime) {
                formData.append(`events[${index}][end_time]`, event.endTime);
            }
            if (event.familyMemberKey) {
                formData.append(`events[${index}][family_member_key]`, event.familyMemberKey);
            }
            if (event.cover) {
                formData.append(`events[${index}][cover_image_path]`, event.cover);
            }
        });
        if (this.selectedThemeKey) {
            formData.append('theme', this.selectedThemeKey);
        }

        this.submitting = true;
        this.progress = 0;
        this.submitError = '';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.submitUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrf ? csrf.getAttribute('content') : '');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) {
                this.progress = Math.round((event.loaded / event.total) * 100);
            }
        });

        xhr.addEventListener('load', () => {
            let data = {};
            try {
                data = JSON.parse(xhr.responseText);
            } catch {
                data = {};
            }

            if (xhr.status >= 200 && xhr.status < 300 && data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            this.submitting = false;
            if (xhr.status === 422 && data.errors) {
                this.routeSubmitErrors(data.errors);
            } else {
                this.submitError = data.message || 'אירעה שגיאה בשליחת הטופס';
            }
        });

        xhr.addEventListener('error', () => {
            this.submitting = false;
            this.submitError = 'אירעה שגיאה בשליחת הטופס. נא לנסות שוב.';
        });

        xhr.send(formData);
    },

    routeSubmitErrors(errors) {
        const keys = Object.keys(errors);

        if (keys.includes('name')) {
            this.step = 0;
            this.nameTouched = true;
            this.calendarNameError = errors.name.join(' ');
            this.submitError = '';
            return;
        }

        if (keys.some((key) => key.startsWith('members'))) {
            this.step = 2;
            this.memberPanelOpen = false;
            this.submitError = 'חלק מפרטי בני המשפחה חסרים או שגויים. נא לבדוק ולשמור שוב.';
            return;
        }

        if (keys.some((key) => key.startsWith('events'))) {
            this.step = 3;
            this.eventPanelOpen = false;
            this.submitError = 'חלק מפרטי האירועים חסרים או שגויים. נא לבדוק ולשמור שוב.';
            return;
        }

        this.submitError = Object.values(errors).flat().join('\n');
    },
}));

Alpine.data('calendarPreviewScroll', () => ({
    showIndicator: false,

    init() {
        this.update = this.update.bind(this);
        this.$nextTick(() => this.update());
        window.addEventListener('resize', this.update);
    },

    destroy() {
        window.removeEventListener('resize', this.update);
    },

    onScroll() {
        this.update();
    },

    update() {
        const el = this.$refs.scroller;

        if (!el) {
            return;
        }

        const canScroll = el.scrollWidth > el.clientWidth;
        const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1;

        this.showIndicator = canScroll && !atEnd;
    },
}));

window.Alpine = Alpine;

Alpine.start();
