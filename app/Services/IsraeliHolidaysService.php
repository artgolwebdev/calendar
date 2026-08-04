<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IsraeliHolidaysService
{
    /**
     * Get Israeli holidays for a specific year
     * Results are cached for 6 months
     */
    public function getHolidaysForYear(int $year): array
    {
        $cacheKey = "israeli_holidays_{$year}";
        
        return Cache::remember($cacheKey, now()->addMonths(6), function () use ($year) {
            return $this->fetchHolidaysFromAPI($year);
        });
    }

    /**
     * Fetch holidays from Hebcal API
     */
    protected function fetchHolidaysFromAPI(int $year): array
    {
        $url = "https://www.hebcal.com/hebcal?v=1&cfg=json&year={$year}&maj=on&min=on&mod=on&i=on";
        
        $response = Http::timeout(10)->get($url);
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['items'] ?? [];
        }
        
        // Log error if needed
        \Log::error("Failed to fetch Israeli holidays from Hebcal API", [
            'year' => $year,
            'status' => $response->status(),
        ]);
        
        return [];
    }

    /**
     * Get holidays for a specific month
     */
    public function getHolidaysForMonth(int $year, int $month): array
    {
        $allHolidays = $this->getHolidaysForYear($year);
        
        return array_filter($allHolidays, function ($holiday) use ($month) {
            $date = $holiday['date'] ?? null;
            if (!$date) {
                return false;
            }
            
            $holidayDate = \Carbon\Carbon::parse($date);
            return $holidayDate->month === $month;
        });
    }

    /**
     * Get holidays for a specific date range
     */
    public function getHolidaysForDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $years = [];
        $currentYear = $startDate->year;
        $endYear = $endDate->year;
        
        for ($year = $currentYear; $year <= $endYear; $year++) {
            $years[] = $year;
        }
        
        $allHolidays = [];
        foreach ($years as $year) {
            $allHolidays = array_merge($allHolidays, $this->getHolidaysForYear($year));
        }
        
        return array_filter($allHolidays, function ($holiday) use ($startDate, $endDate) {
            $date = $holiday['date'] ?? null;
            if (!$date) {
                return false;
            }
            
            $holidayDate = \Carbon\Carbon::parse($date);
            return $holidayDate->between($startDate, $endDate);
        });
    }
}
