<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pre-built month design themes
    |--------------------------------------------------------------------------
    |
    | Each theme defines a complete set of values for every themeable field in
    | the month_pages design-settings schema (see MonthPageStyleService and the
    | month-design-settings offcanvas form). Background image fields
    | (custom_image_path / background_media_id) are per-month user content and
    | are intentionally never touched by themes.
    |
    | Add new themes by appending another keyed entry — the key is the slug used
    | in the apply request and in the theme picker.
    |
    */

    'greenish' => [
        'name' => 'ירוק',
        'font_choice' => 'modern',
        'overlay_opacity' => 20,
        'day_box_bg_color' => '#DCFCE7',
        'day_box_font_color' => '#14532D',
        'day_box_bg_opacity' => 95,
        'weekday_color' => '#166534',
        'show_adjacent_month_days' => true,
    ],

    'bluish' => [
        'name' => 'כחול',
        'font_choice' => 'default',
        'overlay_opacity' => 20,
        'day_box_bg_color' => '#DBEAFE',
        'day_box_font_color' => '#1E3A8A',
        'day_box_bg_opacity' => 95,
        'weekday_color' => '#1D4ED8',
        'show_adjacent_month_days' => true,
    ],

    'blackish' => [
        'name' => 'שחור',
        'font_choice' => 'elegant',
        'overlay_opacity' => 65,
        'day_box_bg_color' => '#1F2937',
        'day_box_font_color' => '#F9FAFB',
        'day_box_bg_opacity' => 90,
        'weekday_color' => '#D1D5DB',
        'show_adjacent_month_days' => false,
    ],

    'pinkish' => [
        'name' => 'ורוד',
        'font_choice' => 'modern',
        'overlay_opacity' => 20,
        'day_box_bg_color' => '#FCE7F3',
        'day_box_font_color' => '#831843',
        'day_box_bg_opacity' => 95,
        'weekday_color' => '#BE185D',
        'show_adjacent_month_days' => true,
    ],

    'yellowish' => [
        'name' => 'צהוב',
        'font_choice' => 'default',
        'overlay_opacity' => 15,
        'day_box_bg_color' => '#FEF9C3',
        'day_box_font_color' => '#713F12',
        'day_box_bg_opacity' => 95,
        'weekday_color' => '#A16207',
        'show_adjacent_month_days' => true,
    ],
];
