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

window.Alpine = Alpine;

Alpine.start();
