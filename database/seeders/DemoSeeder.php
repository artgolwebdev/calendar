<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'משתמש דמו',
                'password' => Hash::make('password'),
            ]
        );

        // Create family members
        $father = FamilyMember::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'אבא'],
            [
                'birth_date' => '1980-05-15',
                'anniversary_date' => '2010-06-20',
                'notes' => 'אבא של המשפחה',
            ]
        );

        $mother = FamilyMember::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'אמא'],
            [
                'birth_date' => '1982-08-22',
                'anniversary_date' => '2010-06-20',
                'notes' => 'אמא של המשפחה',
            ]
        );

        $child = FamilyMember::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'ילד'],
            [
                'birth_date' => '2015-03-10',
                'notes' => 'הילד הראשון',
            ]
        );

        // Create a calendar
        $calendar = Calendar::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'לוח שנה משפחתי 2026'],
        );

        // Create 12 month pages for the calendar
        for ($month = 1; $month <= 12; $month++) {
            $calendar->monthPages()->firstOrCreate(
                ['month_number' => $month],
                [
                    'font_choice' => 'default',
                    'overlay_opacity' => 30,
                    'day_box_bg_color' => '#FFFFFF',
                    'day_box_font_color' => '#2B2E3A',
                    'day_box_bg_opacity' => 100,
                    'show_adjacent_month_days' => true,
                ]
            );
        }

        // Create sample events
        CalendarEvent::firstOrCreate(
            [
                'calendar_id' => $calendar->id,
                'title' => 'יום הולדת של אבא',
                'event_date' => '2026-05-15',
            ],
            [
                'family_member_id' => $father->id,
                'event_type' => 'birthday',
            ]
        );

        CalendarEvent::firstOrCreate(
            [
                'calendar_id' => $calendar->id,
                'title' => 'יום הולדת של אמא',
                'event_date' => '2026-08-22',
            ],
            [
                'family_member_id' => $mother->id,
                'event_type' => 'birthday',
            ]
        );

        CalendarEvent::firstOrCreate(
            [
                'calendar_id' => $calendar->id,
                'title' => 'יום הולדת של הילד',
                'event_date' => '2026-03-10',
            ],
            [
                'family_member_id' => $child->id,
                'event_type' => 'birthday',
            ]
        );

        CalendarEvent::firstOrCreate(
            [
                'calendar_id' => $calendar->id,
                'title' => 'יום הנישואין של ההורים',
                'event_date' => '2026-06-20',
            ],
            [
                'family_member_id' => $father->id,
                'event_type' => 'anniversary',
            ]
        );

        CalendarEvent::firstOrCreate(
            [
                'calendar_id' => $calendar->id,
                'title' => 'פסח',
                'event_date' => '2026-04-13',
            ],
            [
                'family_member_id' => null,
                'event_type' => 'custom',
            ]
        );

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Email: demo@example.com');
        $this->command->info('Password: password');
    }
}
