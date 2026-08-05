<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FamilyMember extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'birth_date',
        'anniversary_date',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'anniversary_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
