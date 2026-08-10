<?php

namespace App\Http\Controllers;

use App\Services\HebrewDateService;
use App\Services\IsraeliHolidaysService;
use Carbon\Carbon;

class WelcomeController extends Controller
{
    public function __invoke(
        IsraeliHolidaysService $holidaysService,
        HebrewDateService $hebrewDateService
    ) {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        $holidays = $holidaysService->getHolidaysForMonth($year, $month);

        $monthNames = [
            1 => 'ינואר', 2 => 'פברואר', 3 => 'מרץ', 4 => 'אפריל',
            5 => 'מאי', 6 => 'יוני', 7 => 'יולי', 8 => 'אוגוסט',
            9 => 'ספטמבר', 10 => 'אוקטובר', 11 => 'נובמבר', 12 => 'דצמבר',
        ];

        $hebrewMonthName = $hebrewDateService->hebrewMonthName($now->startOfMonth());
        $hebrewYear = $hebrewDateService->toHebrewArray($now->startOfMonth())['year'];

        $holidaysByDate = [];
        foreach ($holidays as $holiday) {
            $date = Carbon::parse($holiday['date']);
            $dateKey = $date->format('Y-m-d');
            if (! isset($holidaysByDate[$dateKey])) {
                $holidaysByDate[$dateKey] = [];
            }
            $holidaysByDate[$dateKey][] = $holiday;
        }

        return view('welcome', compact(
            'year',
            'month',
            'monthNames',
            'hebrewMonthName',
            'hebrewYear',
            'holidaysByDate'
        ));
    }
}
