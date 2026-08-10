<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    protected $fillable = [
        'user_id',
        'calendar_id',
        'parent_id',
        'name',
        'family_member_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function isLinkedToMember(): bool
    {
        return $this->family_member_id !== null;
    }

    public function isCalendarRoot(): bool
    {
        return $this->calendar_id !== null && $this->parent_id === null;
    }
}
