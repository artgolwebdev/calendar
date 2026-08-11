<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CalendarThemeController;
use App\Http\Controllers\CalendarWizardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MonthPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class);

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Calendar creation wizard (must be registered before the calendars
    // resource so /calendars/wizard is not swallowed by the {calendar} binding)
    Route::get('/calendars/wizard', [CalendarWizardController::class, 'create'])->name('calendars.wizard');
    Route::post('/calendars/wizard', [CalendarWizardController::class, 'store'])->name('calendars.wizard.store');

    // Calendar resource routes
    Route::resource('calendars', CalendarController::class);

    // Calendar month page routes
    Route::get('/calendars/{calendar}/month/{monthNumber}/{year?}', [CalendarController::class, 'showMonth'])
        ->name('calendars.month');

    // Calendar day view route
    Route::get('/calendars/{calendar}/day/{date}', [CalendarController::class, 'showDay'])
        ->name('calendars.day');

    // Calendar event routes (nested under calendars)
    Route::prefix('calendars/{calendar}')->group(function () {
        Route::resource('events', CalendarEventController::class, [
            'names' => 'calendar-events',
            'parameters' => ['events' => 'calendarEvent'],
        ]);

        // Family member routes (nested under calendars)
        Route::resource('members', FamilyMemberController::class, [
            'names' => 'family-members',
            'parameters' => ['members' => 'familyMember'],
        ]);
    });

    // Media library routes
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::get('/media/upload', [MediaController::class, 'create'])->name('media.create');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::put('/media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::put('/media/{media}/folder', [MediaController::class, 'moveToFolder'])->name('media.move');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    // Media folder routes
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    // Month page update routes
    Route::put('/calendars/{calendar}/month-pages/{monthPage}', [MonthPageController::class, 'update'])
        ->name('month-pages.update');
    Route::delete('/calendars/{calendar}/month-pages/{monthPage}', [MonthPageController::class, 'removeImage'])
        ->name('month-pages.remove-image');

    // Global calendar theme routes
    Route::post('/calendars/{calendar}/themes/apply', [CalendarThemeController::class, 'apply'])
        ->name('calendars.themes.apply');
});

require __DIR__.'/auth.php';
