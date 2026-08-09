<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $fillable = [
        'calendar_id',
        'family_member_id',
        'title',
        'description',
        'event_date',
        'event_type',
        'start_time',
        'end_time',
        'is_auto_generated',
        'cover_image_path',
        'title_customized',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_auto_generated' => 'boolean',
        'title_customized' => 'boolean',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($calendarEvent) {
            if ($calendarEvent->cover_image_path) {
                \Storage::disk('public')->delete($calendarEvent->cover_image_path);
            }
        });
    }
}
