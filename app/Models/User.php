<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithMedia, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }

    /**
     * Resolve the user's main calendar: the explicitly marked one, falling
     * back to the oldest calendar so the dashboard always has something.
     */
    public function mainCalendar(): ?Calendar
    {
        return $this->calendars()
            ->where('is_main', true)
            ->orderBy('id')
            ->first()
            ?? $this->calendars()
                ->orderBy('created_at')
                ->orderBy('id')
                ->first();
    }

    /**
     * Register the media collection used as the user's personal media library.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('user_media')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif']);
    }

    /**
     * Register thumbnail conversions for the user's media library.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(600)
            ->nonQueued();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
