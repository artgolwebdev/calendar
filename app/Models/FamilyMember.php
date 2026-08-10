<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FamilyMember extends Model
{
    protected $fillable = [
        'calendar_id',
        'name',
        'birth_date',
        'anniversary_date',
        'notes',
        'hobbies',
        'favorite_sports',
        'favorite_music',
        'favorite_food',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'anniversary_date' => 'date',
        'hobbies' => 'array',
        'favorite_sports' => 'array',
        'favorite_music' => 'array',
        'favorite_food' => 'array',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function folder(): HasOne
    {
        return $this->hasOne(Folder::class);
    }
}
