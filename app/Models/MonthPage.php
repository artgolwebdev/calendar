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
        'background_media_id',
        'custom_image_path',
        'auto_background_media_id',
        'auto_background_family_member_id',
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

    public function backgroundMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'background_media_id');
    }

    public function autoBackgroundMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'auto_background_media_id');
    }

    public function autoBackgroundMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'auto_background_family_member_id');
    }
}
