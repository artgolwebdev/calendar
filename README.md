<p align="center">
    <span style="font-size: 2.5rem; font-weight: 700; color: #4F46E5;">לוח שנה משפחתי</span>
</p>

<p align="center"><strong>Family Calendar</strong> — a Hebrew RTL web application for building and customizing family calendars.</p>

## About

לוח שנה משפחתי lets you create multiple calendars, style each month individually, track family birthdays and marriage anniversaries, and see Jewish holidays — all in one place.

## Features

- **Multiple calendars** per user, each with its own cover image
- **Yearly overview** — month tiles with background images and chips for events and Jewish holidays
- **Monthly grid** — Gregorian and Hebrew dates, holidays, and events per day, with previous/next/“today” navigation
- **Design settings per month** — font, overlay opacity, day-box background/text colors and opacity, weekday color, background image, and an adjacent-month days toggle, in a collapsible accordion
- **Family members** — birthdays and marriage anniversaries automatically become recurring events on all of the user’s calendars, labeled with the member’s age or years married
- **Calendar events** — birthdays, anniversaries, and custom events
- **Password reset** — Hebrew RTL “forgot/reset password” pages with Hebrew validation messages and a branded Hebrew reset email sent via Resend
- **Jewish holidays** — fetched from the Hebcal API and grouped per month
- **Hebrew date conversion** — built-in service with leap-year support (אדר א׳ / אדר ב׳)
- Fully **RTL** Hebrew UI, responsive down to mobile

## Tech Stack

- PHP 8.3+
- Laravel 13
- Blade + Tailwind CSS (RTL) + Alpine.js
- Vite
- SQLite (default)
- PHPUnit
- [Resend](https://resend.com) for transactional email (`resend/resend-laravel`)
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

- **Auto-generated family events** — creating or updating a `FamilyMember` fires `FamilyMemberObserver`, which uses `FamilyEventGeneratorService` to upsert one canonical birthday/anniversary event per date-type on every calendar owned by the user. Deleting a member removes their auto events. Events are stored with their original date, and the month/year views resolve them against the displayed year (Gregorian recurrence), handling February 29 gracefully and showing the member’s age or years married.

- **Jewish holidays** — `IsraeliHolidaysService` queries the Hebcal API per year (cached) and the calendar views filter for major holidays.

- **Password reset** — Breeze routes/controllers dispatch a `ResetPasswordNotification` that sends a branded Hebrew RTL email (`PasswordResetMail`) through the Resend mailer (`MAIL_MAILER=resend`, API key in `RESEND_KEY`). UI labels, inline validation errors, and broker status messages are translated via `lang/he`.

- **Design settings** — each of a calendar’s 12 month pages stores its own style; `MonthPageStyleService` resolves defaults and per-page overrides.

## Project Structure

```
app/
├── Http/Controllers/       Auth, Calendar, MonthPage, CalendarEvent, FamilyMember
├── Mail/                   PasswordResetMail (branded Hebrew RTL email)
├── Models/                 Calendar, CalendarEvent, FamilyMember, MonthPage, User
├── Notifications/          ResetPasswordNotification
├── Observers/              FamilyMemberObserver
├── Requests/               Form requests with validation
├── Policies/               FamilyMember, Calendar
└── Services/
    ├── FamilyEventGeneratorService
    ├── HebrewDateService
    ├── IsraeliHolidaysService
    └── MonthPageStyleService
lang/he/                       Hebrew translations (validation, passwords, auth)
resources/views/
├── auth/                  Login, register, forgot/reset password (Hebrew RTL)
├── calendars/              Yearly + monthly views, design settings partial
├── calendar-events/        Event create/edit
├── emails/                 password-reset.blade.php (RTL inline-styled)
├── family-members/         Member CRUD
└── dashboard.blade.php     Dashboard
tests/
├── Feature/                Auth, Calendar, MonthPageSettings, FamilyEventGeneration, Dashboard
└── Unit/                   HebrewDateService
```

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
