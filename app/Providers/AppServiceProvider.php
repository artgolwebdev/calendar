<?php

namespace App\Providers;

use App\Models\Calendar;
use App\Models\FamilyMember;
use App\Models\Folder;
use App\Models\Media;
use App\Observers\CalendarObserver;
use App\Observers\FamilyMemberObserver;
use App\Policies\FolderPolicy;
use App\Policies\MediaPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Calendar::observe(CalendarObserver::class);
        FamilyMember::observe(FamilyMemberObserver::class);

        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Folder::class, FolderPolicy::class);
    }
}
