<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'cover_image_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function monthPages(): HasMany
    {
        return $this->hasMany(MonthPage::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($calendar) {
            // Delete cover image if exists
            if ($calendar->cover_image_path) {
                \Storage::disk('public')->delete($calendar->cover_image_path);
            }
            
            $calendar->calendarEvents()->delete();
            $calendar->monthPages()->delete();
        });
    }
}
