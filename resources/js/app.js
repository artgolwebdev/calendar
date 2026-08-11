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

    steps: [
        { key: 'name', label: 'שם לוח השנה' },
        { key: 'cover', label: 'תמונת כריכה' },
        { key: 'members', label: 'בני משפחה' },
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
    memberForm: {
        name: '',
        birthDate: '',
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

    isStepDone(index) {
        return index < this.step;
    },

    next() {
        if (this.memberPanelOpen) {
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

        this.submitError = '';
        this.step = target;
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
        this.memberForm = {
            name: member.name,
            birthDate: member.birthDate,
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
            this.members.splice(this._editingIndex, 1, data);
        } else {
            this.members.push(data);
        }
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
        if (member.imageUrl) {
            URL.revokeObjectURL(member.imageUrl);
        }
        this.members.splice(index, 1);
    },

    resetMemberForm() {
        this.clearMemberImage();
        this.memberForm = {
            name: '',
            birthDate: '',
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
        if (this.$refs.memberImageInput) {
            this.$refs.memberImageInput.value = '';
        }
    },

    onMemberImage(event) {
        const file = event.target.files[0];
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
            formData.append(`members[${index}][name]`, member.name);
            formData.append(`members[${index}][birth_date]`, member.birthDate);
            member.hobbies.forEach((tag) => formData.append(`members[${index}][hobbies][]`, tag));
            member.sports.forEach((tag) => formData.append(`members[${index}][favorite_sports][]`, tag));
            member.music.forEach((tag) => formData.append(`members[${index}][favorite_music][]`, tag));
            member.food.forEach((tag) => formData.append(`members[${index}][favorite_food][]`, tag));
            if (member.image) {
                formData.append(`members[${index}][image]`, member.image);
            }
        });

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
