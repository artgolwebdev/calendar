<p align="center">
    <span style="font-size: 2.5rem; font-weight: 700; color: #4F46E5;">לוח שנה משפחתי</span>
</p>

<p align="center"><strong>Family Calendar</strong> — a Hebrew RTL web application for building and customizing family calendars.</p>

## About

לוח שנה משפחתי lets you create multiple calendars, style each month individually, track family birthdays and marriage anniversaries, and see Jewish holidays — all in one place.

## Features

- **Multiple calendars** per user, each with its own cover image
- **Cover image cropping** — selecting a cover opens a Cropper.js editor that locks the crop to a 21:9 ratio (zoom in/out, reset, drag to reframe) and stages a preview before the cropped JPEG is uploaded; the shared `<x-cover-upload>` component powers both the create and edit forms, and edit lets you replace the current cover or keep it untouched
- **Main calendar** — users can mark one calendar as their main one (falling back to the oldest); it powers the dashboard day scroller
- **Dashboard day scroller** — a 14-day RTL horizontal strip for the main calendar with prev/next arrow buttons, click-and-drag scrolling, and automatic smooth centering on today on load
- **Yearly overview** — month tiles with background images and chips for events and Jewish holidays
- **Monthly grid** — Gregorian and Hebrew dates, holidays, and events per day, with previous/next/“today” navigation and a cover banner (blue gradient placeholder when the calendar has no cover image)
- **Day view** — click any day in the dashboard scroller or the monthly grid to open a single-day hour grid (00:00–23:00). Timed events are positioned server-side as overlapping side-by-side columns, all-day events and holidays appear as chips, a red current-time line marks “now” on today, and clicking an hour row opens the event form prefilled with that date and time
- **Design settings per month** — font, overlay opacity, day-box background/text colors and opacity, weekday color, background image, and an adjacent-month days toggle, in a collapsible accordion
- **Media library** — a personal, per-user image library (`spatie/laravel-medialibrary`) with bulk upload, live previews and per-file progress bars, rename/delete, and a picker that lets you reuse a library image as a month background alongside the existing direct upload
- **Media folders** — organize library images into folders via drag-and-drop or a per-image dropdown; every family member gets an auto-synced folder, and deleting a folder never deletes its media
- **Family members** — birthdays and marriage anniversaries automatically become recurring events on all of the user’s calendars — regardless of whether the member or the calendar was created first — labeled with the member’s age or years married
- **Calendar events** — birthdays, anniversaries, and custom events, with optional start/end times that place events on the day view
- **Transactional emails** — a welcome email and a branded password-reset email, both in Hebrew RTL and both built on one shared, RTL-safe layout (`resources/views/emails/layouts/transactional.blade.php`), sent via Resend. The welcome email fires on the `Registered` event after sign-up (dispatched through the queue, so it sends immediately when `QUEUE_CONNECTION=sync`); the reset email is sent synchronously by the password-reset notification
- **Jewish holidays** — fetched from the Hebcal API and grouped per month
- **Hebrew date conversion** — built-in service with leap-year support (אדר א׳ / אדר ב׳)
- **Side navigation** — a stripped top navbar (brand + “Dashboard”) with the remaining links (New Calendar, Family Members, Media, Profile, Log out) in a permanent side panel on desktop (`lg:`) and a blurred slide-in offcanvas menu on mobile/tablet (close via backdrop click, Escape, or navigating)
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

- **Auto-generated family events** — creating or updating a `FamilyMember` fires `FamilyMemberObserver`, which uses `FamilyEventGeneratorService` to upsert one canonical birthday/anniversary event per date-type on every calendar owned by the user. Creating a calendar fires `CalendarObserver`, which syncs all existing members onto it, so events are generated regardless of creation order. Deleting a member removes their auto events. Events are stored with their original date, and the month/year views resolve them against the displayed year (Gregorian recurrence), handling February 29 gracefully and showing the member’s age or years married.

- **Jewish holidays** — `IsraeliHolidaysService` queries the Hebcal API per year (cached) and the calendar views filter for major holidays.

- **Transactional emails** — every email sets RTL explicitly and inline so clients like Gmail, Outlook, and Apple Mail render it right-to-left: `dir="rtl"` on the outer `<html>` and `<body>`, `direction:rtl; text-align:right;` on every block-level element (not just inherited), centered CTA buttons, and `dir="ltr"` + `unicode-bidi:isolate` spans so URLs and email addresses read correctly inside Hebrew sentences. Email clients ignore most `<style>` blocks and custom web fonts, so there is no Tailwind or `@font-face` in the mail views — all styling is inline and the font stack falls back to `'Segoe UI', Tahoma, Arial, sans-serif` for Hebrew-legible rendering. New transactional emails should `@extends('emails.layouts.transactional')` rather than build their own markup.

- **Welcome email** — `RegisteredUserController` fires Laravel's `Registered` event; the auto-discovered `SendWelcomeEmail` listener sends the queued `WelcomeEmail` mailable (`QUEUE_CONNECTION=database` needs a queue worker, `QUEUE_CONNECTION=sync` sends it inline). It personalizes the greeting with the user's name and links to the dashboard.

- **Password reset** — Breeze routes/controllers dispatch a `ResetPasswordNotification` that sends a branded Hebrew RTL email (`PasswordResetMail`) through the Resend mailer (`MAIL_MAILER=resend`, API key in `RESEND_KEY`). The email is personalized with the recipient’s name and uses bidi-isolated spans so mixed Hebrew/English renders correctly. UI labels, inline validation errors, and broker status messages are translated via `lang/he`. After a successful reset the user is redirected to the login page.

- **Day view** — `CalendarController::showDay` resolves the requested date (aborting 404 on invalid input), reuses `CalendarMonthDataService` to fetch the day's events and holidays, then splits events into all-day (no `start_time`) and timed ones. `DayViewLayoutService` clamps times to the day, enforces a 30-minute minimum duration, groups transitively-overlapping events into clusters, and assigns side-by-side columns via first-free-column placement — returning `top/height/left/width` as percentages of the 1440-minute day for the view's absolute-positioned blocks. The current-time line renders only when viewing today and updates every 60 seconds via a small Alpine/JS interval.

- **Design settings** — each of a calendar’s 12 month pages stores its own style; `MonthPageStyleService` resolves defaults and per-page overrides.

- **Media library** — the `User` model uses `InteractsWithMedia` with a `user_media` collection and a `thumb` conversion. A `MediaPolicy` scopes every item to its owner (view/update/delete are forbidden across users), and deleting an item first nulls any `month_pages.background_media_id` references so no orphaned backgrounds remain. A month background can come from either the media library (`background_media_id`, resolved first) or a direct upload / legacy path (`custom_image_path` / `background_image_path`), with a picker in the month design settings.

- **Media folders** — the library is organized into folders (`Folder` model, unique `user_id`+`name`, owned via `FolderPolicy`). The index page shows a sidebar with "All media" plus every folder; images are moved either by drag-and-drop onto a folder or from a per-image dropdown. Deleting a folder never deletes its media — the `folder_id` foreign key is `nullOnDelete`, so items simply return to All Media. Every family member gets an auto-synced folder: `FamilyMemberObserver` creates it on member creation, keeps its name in sync on updates (member-linked folders can't be renamed/deleted manually), and removes it when the member is deleted. `php artisan folders:backfill` creates folders for any members that predate the feature.

- **Cover image cropping** — `resources/views/components/cover-upload.blade.php` (backed by the `coverCrop` Alpine component in `resources/js/app.js`) wraps the `cover_image_path` file input. Selecting an image (via the picker or drag-and-drop) opens a Cropper.js modal locked to `21/9` with `viewMode: 1`, `dragMode: 'move'`, and wheel/button zoom. On confirm, `getCroppedCanvas({ maxWidth: 1920, fillColor: '#FFFFFF', imageSmoothingQuality: 'high' })` exports a JPEG blob (quality 0.9) that replaces the input's file list through `DataTransfer`, so the normal multipart submit uploads the already-cropped image — no backend changes, and if JS fails the raw file still uploads. Cancel restores the previous staged file or clears the input; edit shows the current cover until replaced. The single stored file is referenced directly by the calendar show banner, the month-view banner, and the dashboard card, so no per-month copies are needed.

## Project Structure

```
app/
├── Http/Controllers/       Auth, Calendar, CalendarEvent, Dashboard, FamilyMember, Media, Folder, MonthPage
├── Mail/                   PasswordResetMail, WelcomeEmail (branded Hebrew RTL emails)
├── Models/                 Calendar, CalendarEvent, FamilyMember, Folder, Media, MonthPage, User
├── Notifications/          ResetPasswordNotification
├── Listeners/              SendWelcomeEmail (sends the welcome email on the Registered event)
├── Observers/              CalendarObserver, FamilyMemberObserver
├── Requests/               Form requests with validation
├── Policies/               FamilyMember, Calendar, Media, Folder
└── Services/
    ├── CalendarMonthDataService
    ├── DayViewLayoutService
    ├── FamilyEventGeneratorService
    ├── FolderSyncService
    ├── HebrewDateService
    ├── IsraeliHolidaysService
    └── MonthPageStyleService
lang/he/                       Hebrew translations (validation, passwords, auth)
resources/views/
├── auth/                  Login, register, forgot/reset password (Hebrew RTL)
├── calendars/              Yearly, monthly and single-day views, design settings partial
├── calendar-events/        Event create/edit (with start/end time)
├── components/             cover-upload.blade.php (21:9 crop editor for calendar covers)
├── components/dashboard/   Dashboard day-scroller (RTL strip, arrows, drag, auto-scroll)
├── emails/                 Shared RTL-safe layout (layouts/transactional.blade.php), welcome.blade.php, password-reset.blade.php
├── family-members/         Member CRUD
├── layouts/                App layout, navigation (navbar + side panel), side-menu partial
├── media/                  Media library index (sidebar with folders, bulk upload, rename/delete/move)
└── dashboard.blade.php     Dashboard
tests/
├── Feature/                Auth, Calendar, Dashboard, DayView, FamilyEventGeneration, MainCalendar, MediaFolder, MediaLibrary, MonthPageSettings
└── Unit/                   DayViewLayoutService, HebrewDateService
```

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
