<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthPage extends Model
{
    protected $fillable = [
        'calendar_id',
        'month_number',
        'font_choice',
        'background_image_path',
        'custom_image_path',
        'overlay_opacity',
        'day_box_bg_color',
        'day_box_font_color',
        'day_box_bg_opacity',
        'weekday_color',
        'show_adjacent_month_days',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }
}
