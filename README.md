<p align="center">
    <span style="font-size: 2.5rem; font-weight: 700; color: #4F46E5;">לוח שנה משפחתי</span>
</p>

<p align="center"><strong>Family Calendar</strong> — a Hebrew RTL web application for building and customizing family calendars.</p>

## About

לוח שנה משפחתי lets you create multiple calendars, style each month individually, track family birthdays and marriage anniversaries, and see Jewish holidays — all in one place.

## Features

- **Multiple calendars** per user, each with its own cover image
- **Calendar creation wizard** — a guided 4-step flow (`/calendars/wizard`, the single entry point for creating a calendar from the dashboard and side menu): name → cover (reuses the shared crop component) → family members → review. The member hub lets you add, edit, or skip members up front (photo, name, birth date, and the four tag lists), with client-side validation and an XHR submit that reports upload progress. Members are scoped to the new calendar and their photos land in the calendar's auto-synced media folder. Creating a calendar from the classic form still works and shares the same `CalendarCreationService`
- **Cover image cropping** — selecting a cover opens a Cropper.js editor that locks the crop to a 21:9 ratio (zoom in/out, reset, drag to reframe) and stages a preview before the cropped JPEG is uploaded; the shared `<x-cover-upload>` component powers both the create and edit forms, and edit lets you replace the current cover or keep it untouched
- **Main calendar** — users can mark one calendar as their main one (falling back to the oldest); it powers the dashboard day scroller
- **Dashboard day scroller** — a 14-day RTL horizontal strip for the main calendar with prev/next arrow buttons, click-and-drag scrolling, and automatic smooth centering on today on load
- **Yearly overview** — month tiles with background images and chips for events and Jewish holidays
- **Monthly grid** — Gregorian and Hebrew dates, holidays, and events per day, with previous/next/“today” navigation and a cover banner (blue gradient placeholder when the calendar has no cover image)
- **Day view** — click any day in the dashboard scroller or the monthly grid to open a single-day hour grid (00:00–23:00). Timed events are positioned server-side as overlapping side-by-side columns, all-day events and holidays appear as chips, a red current-time line marks “now” on today, and clicking an hour row opens the event form prefilled with that date and time
- **Design settings per month** — font, overlay opacity, day-box background/text colors and opacity, weekday color, background image, and an adjacent-month days toggle, in a collapsible accordion
- **Calendar themes** — five pre-built design themes (ירוק / כחול / שחור / ורוד / צהוב) defined in `config/themes.php`. The monthly view's theme picker applies a theme to the current month only, re-rendering the grid in place; the yearly overview's picker applies it to all 12 months at once in a single transaction-backed bulk update. Personal month background images are never touched.
- **Media library** — a personal, per-user image library (`spatie/laravel-medialibrary`) with bulk upload, live previews and per-file progress bars, rename/delete, and a picker that lets you reuse a library image as a month background alongside the existing direct upload
- **Media folders** — organize library images into folders via drag-and-drop or a per-image dropdown; manual folders are user-global, while each calendar and its family members get an auto-synced folder tree (calendar root → member subfolders), and deleting a folder never deletes its media
- **Family members** — each calendar has its own family members, managed from the calendar's edit page. Birthdays and marriage anniversaries automatically become recurring events on the member's calendar, labeled with the member’s age or years married. Every member can carry four multi-value tag lists (תחביבים, ספורט אהוב, מוזיקה אהובה, אוכל אהוב) powered by a reusable `<x-tag-input>` chip component
- **Calendar events** — birthdays, anniversaries, and custom events, with optional start/end times that place events on the day view
- **Transactional emails** — a welcome email and a branded password-reset email, both in Hebrew RTL and both built on one shared, RTL-safe layout (`resources/views/emails/layouts/transactional.blade.php`), sent via Resend. The welcome email fires on the `Registered` event after sign-up (dispatched through the queue, so it sends immediately when `QUEUE_CONNECTION=sync`); the reset email is sent synchronously by the password-reset notification
- **Jewish holidays** — fetched from the Hebcal API and grouped per month
- **Hebrew date conversion** — built-in service with leap-year support (אדר א׳ / אדר ב׳)
- **Side navigation** — a stripped top navbar (brand + “Dashboard”) with the remaining links (New Calendar, Media, Profile, Log out) in a permanent side panel on desktop (`lg:`) and a blurred slide-in offcanvas menu on mobile/tablet (close via backdrop click, Escape, or navigating)
- **Public welcome page** — a marketing landing page for guests showing a live preview of the current month (Gregorian + Hebrew dates with holiday chips). On mobile the LTR day-grid keeps its readable cell size inside a horizontally scrollable strip with edge fades and a volt scroll indicator; the hero CTAs stay inline, side-by-side
- Fully **RTL** Hebrew UI, responsive down to mobile

## Tech Stack

- PHP 8.3+
- Laravel 13
- Blade + Tailwind CSS (`tailwindcss-rtl`) + Alpine.js, with a custom `ink`/`volt` design-token scale and Rubik as the UI font
- Vite
- SQLite (default)
- PHPUnit
- [Resend](https://resend.com) for transactional email (`resend/resend-laravel`)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary) (`spatie/laravel-medialibrary`) for the user media library
- [Cropper.js](https://fengyuanchen.github.io/cropperjs/) (`cropperjs`) for the calendar cover crop editor
- [Hebcal API](https://www.hebcal.com/home/developer-apis) for Jewish holidays

## Requirements

- PHP 8.3+
- Composer
- Node.js + npm
- SQLite (or a database of your choice)

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Create the environment file and generate an application key
cp .env.example .env
php artisan key:generate

# 3. Configure .env (database connection, etc.), then run migrations
php artisan migrate

# 4. (Optional) Seed demo data
php artisan db:seed
#   Login: demo@example.com / password

# 5. Install and build frontend assets
npm install
npm run build
#   or, during development: npm run dev

# 6. Serve the application
php artisan serve
```

## Running Tests

```bash
php artisan test --compact
```

Code style is enforced with Laravel Pint:

```bash
vendor/bin/pint --dirty
```

## How It Works

- **Auto-generated family events** — creating or updating a `FamilyMember` fires `FamilyMemberObserver`, which uses `FamilyEventGeneratorService` to upsert one canonical birthday/anniversary event per date-type on the member's calendar. Moving a member to a different calendar purges the old calendar's auto events and re-syncs them onto the new one. Deleting a member removes their auto events. Events are stored with their original date, and the month/year views resolve them against the displayed year (Gregorian recurrence), handling February 29 gracefully and showing the member’s age or years married.

- **Calendar creation wizard** — `GET/POST /calendars/wizard` is served by `CalendarWizardController` (`calendars.wizard` / `calendars.wizard.store`, registered before the `calendars` resource so the binding doesn't swallow it). `create()` renders the `calendarWizard` Alpine component (4 steps: name, cover, members, review) in `resources/views/calendars/wizard.blade.php`; `store()` runs `CalendarCreationService::create` plus the member/photo loop inside a single `DB::transaction`, and returns a JSON redirect so the view can submit via XHR with a progress bar. Validation lives in `StoreCalendarWizardRequest`, which throws a 422 JSON response (the app's global exception handler only JSON-formats `api/*` errors). Calendar + 12 month pages are created by `CalendarCreationService`, shared with the classic `CalendarController::store`; member photos are attached via `FamilyMemberMediaService::storeImages`, the same service the member CRUD uses.

- **Welcome page** — `WelcomeController` fetches the current month's holidays (Hebcal, cached), builds a per-date holiday map, and renders the guest landing page. The month preview's LTR grid is wrapped in an `overflow-x-auto` strip that only scrolls below `md:`, keeping a `min-w-[34rem]` floor so day cells never compress below a readable size; the `calendarPreviewScroll` Alpine component drives a volt chevron indicator that shows while content remains off-screen, and CSS edge fades hint at the scroll. On desktop the wrapper is `md:overflow-visible`, so the layout is unchanged.

- **Jewish holidays** — `IsraeliHolidaysService` queries the Hebcal API per year (cached) and the calendar views filter for major holidays.

- **Transactional emails** — every email sets RTL explicitly and inline so clients like Gmail, Outlook, and Apple Mail render it right-to-left: `dir="rtl"` on the outer `<html>` and `<body>`, `direction:rtl; text-align:right;` on every block-level element (not just inherited), centered CTA buttons, and `dir="ltr"` + `unicode-bidi:isolate` spans so URLs and email addresses read correctly inside Hebrew sentences. Email clients ignore most `<style>` blocks and custom web fonts, so there is no Tailwind or `@font-face` in the mail views — all styling is inline and the font stack falls back to `'Segoe UI', Tahoma, Arial, sans-serif` for Hebrew-legible rendering. New transactional emails should `@extends('emails.layouts.transactional')` rather than build their own markup.

- **Welcome email** — `RegisteredUserController` fires Laravel's `Registered` event; the auto-discovered `SendWelcomeEmail` listener sends the queued `WelcomeEmail` mailable (`QUEUE_CONNECTION=database` needs a queue worker, `QUEUE_CONNECTION=sync` sends it inline). It personalizes the greeting with the user's name and links to the dashboard.

- **Password reset** — Breeze routes/controllers dispatch a `ResetPasswordNotification` that sends a branded Hebrew RTL email (`PasswordResetMail`) through the Resend mailer (`MAIL_MAILER=resend`, API key in `RESEND_KEY`). The email is personalized with the recipient’s name and uses bidi-isolated spans so mixed Hebrew/English renders correctly. UI labels, inline validation errors, and broker status messages are translated via `lang/he`. After a successful reset the user is redirected to the login page.

- **Day view** — `CalendarController::showDay` resolves the requested date (aborting 404 on invalid input), reuses `CalendarMonthDataService` to fetch the day's events and holidays, then splits events into all-day (no `start_time`) and timed ones. `DayViewLayoutService` clamps times to the day, enforces a 30-minute minimum duration, groups transitively-overlapping events into clusters, and assigns side-by-side columns via first-free-column placement — returning `top/height/left/width` as percentages of the 1440-minute day for the view's absolute-positioned blocks. The current-time line renders only when viewing today and updates every 60 seconds via a small Alpine/JS interval.

- **Design settings** — each of a calendar’s 12 month pages stores its own style; `MonthPageStyleService` resolves defaults and per-page overrides.

- **Calendar themes** — `config/themes.php` defines the themes, each mapping to the seven month-page design fields (font, overlay opacity, day-box background/text colors and opacity, weekday color, adjacent-month toggle). `POST /calendars/{calendar}/themes/apply` (`CalendarThemeController::apply`, validated by `ApplyThemeRequest`) updates either a single month page — when a `month` (1–12) is supplied, as the monthly view does — or every month page at once in one transaction, as the yearly view does. The monthly view then re-applies the returned fields to the live grid via `window.__monthGrid.applyThemeFields`, reloading only when the theme flips `show_adjacent_month_days`; the yearly view just toasts. The shared offcanvas picker (`resources/views/calendars/partials/themes-picker.blade.php`) takes a `themesScope` (`'month'` or `'year'`) to tailor its copy, and the `themePicker` Alpine component in `resources/js/app.js` drives both views (the monthly one wraps it as `monthPage` alongside the design-settings state).

- **Media library** — the `User` model uses `InteractsWithMedia` with a `user_media` collection and a `thumb` conversion. A `MediaPolicy` scopes every item to its owner (view/update/delete are forbidden across users), and deleting an item first nulls any `month_pages.background_media_id` references so no orphaned backgrounds remain. A month background can come from either the media library (`background_media_id`, resolved first) or a direct upload / legacy path (`custom_image_path` / `background_image_path`), with a picker in the month design settings.

- **Media folders** — the library is organized into folders (`Folder` model, owned via `FolderPolicy`). Manual folders are user-global (scoped by `user_id`). Each calendar also gets an auto-synced root folder (`calendar_id` set, no parent), and every family member gets a subfolder nested under their calendar's root (`parent_id` set). The index page shows a sidebar with "All media", manual folders, and each calendar's folder tree; images are moved either by drag-and-drop onto a folder or from a per-image dropdown. Deleting a folder never deletes its media — the `folder_id` foreign key is `nullOnDelete`, so items simply return to All Media. Member-linked folders and calendar roots can't be renamed/deleted manually: `FamilyMemberObserver` creates a member's folder on creation, keeps its name in sync on updates, and removes it when the member is deleted, while `CalendarObserver` creates and renames a calendar's root folder. `php artisan folders:backfill` creates folders for any members that predate the feature.

- **Cover image cropping** — `resources/views/components/cover-upload.blade.php` (backed by the `coverCrop` Alpine component in `resources/js/app.js`) wraps the `cover_image_path` file input. Selecting an image (via the picker or drag-and-drop) opens a Cropper.js modal locked to `21/9` with `viewMode: 1`, `dragMode: 'move'`, and wheel/button zoom. On confirm, `getCroppedCanvas({ maxWidth: 1920, fillColor: '#FFFFFF', imageSmoothingQuality: 'high' })` exports a JPEG blob (quality 0.9) that replaces the input's file list through `DataTransfer`, so the normal multipart submit uploads the already-cropped image — no backend changes, and if JS fails the raw file still uploads. Cancel restores the previous staged file or clears the input; edit shows the current cover until replaced. The single stored file is referenced directly by the calendar show banner, the month-view banner, and the dashboard card, so no per-month copies are needed.

## Project Structure

```
app/
├── Http/Controllers/       Auth, Calendar, CalendarEvent, CalendarTheme, CalendarWizard, Dashboard, FamilyMember, Media, Folder, MonthPage
├── Mail/                   PasswordResetMail, WelcomeEmail (branded Hebrew RTL emails)
├── Models/                 Calendar, CalendarEvent, FamilyMember, Folder, Media, MonthPage, User
├── Notifications/          ResetPasswordNotification
├── Listeners/              SendWelcomeEmail (sends the welcome email on the Registered event)
├── Observers/              CalendarObserver, FamilyMemberObserver
├── Requests/               Form requests with validation
├── Policies/               FamilyMember, Calendar, Media, Folder
└── Services/
    ├── CalendarCreationService
    ├── CalendarMonthDataService
    ├── DayViewLayoutService
    ├── FamilyEventGeneratorService
    ├── FamilyMemberMediaService
    ├── FolderSyncService
    ├── HebrewDateService
    ├── IsraeliHolidaysService
    └── MonthPageStyleService
lang/he/                       Hebrew translations (validation, passwords, auth)
resources/views/
├── auth/                  Login, register, forgot/reset password (Hebrew RTL)
├── calendars/              Yearly, monthly and single-day views, creation wizard, design-settings + theme picker partials
├── calendar-events/        Event create/edit (with start/end time)
├── components/             cover-upload.blade.php (21:9 crop editor), tag-input.blade.php (multi-value tag/chip fields)
├── components/dashboard/   Dashboard day-scroller (RTL strip, arrows, drag, auto-scroll)
├── emails/                 Shared RTL-safe layout (layouts/transactional.blade.php), welcome.blade.php, password-reset.blade.php
├── family-members/         Member CRUD (nested under a calendar)
├── layouts/                App layout, navigation (navbar + side panel), side-menu partial
├── media/                  Media library index (sidebar with folder tree, bulk upload, rename/delete/move)
├── dashboard.blade.php     Dashboard
└── welcome.blade.php       Guest landing page (public current-month preview)
tests/
├── Feature/                Auth, Calendar, CalendarTheme, CalendarWizard, Dashboard, DayView, FamilyEventGeneration, MainCalendar, MediaFolder, MediaLibrary, MonthPageSettings, WelcomePage
└── Unit/                   DayViewLayoutService, HebrewDateService
```

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
