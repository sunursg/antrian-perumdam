<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Loket;
use App\Models\Organization;
use App\Models\Service;
use App\Observers\AnnouncementObserver;
use App\Observers\LoketObserver;
use App\Observers\OrganizationObserver;
use App\Observers\ServiceObserver;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Support\Settings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        Announcement::observe(AnnouncementObserver::class);
        Organization::observe(OrganizationObserver::class);
        Loket::observe(LoketObserver::class);
        Service::observe(ServiceObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);

        // Share once per request to avoid repeated resolution on every partial view render.
        View::share('appOrganization', Settings::organization());

        // Permit SUPER_ADMIN to do anything
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('SUPER_ADMIN') ? true : null;
        });
    }
}
