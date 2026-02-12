<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const ORG_CACHE_KEY = 'org:current';
    private const ACTIVE_ANNOUNCEMENTS_CACHE_KEY = 'announcements:active';

    private static ?Organization $organization = null;
    private static ?Collection $activeAnnouncements = null;

    public static function organization(): Organization
    {
        if (self::$organization instanceof Organization) {
            return self::$organization;
        }

        self::$organization = Cache::remember(self::ORG_CACHE_KEY, now()->addMinutes(10), function () {
            return Organization::query()->first() ?? new Organization([
                'name' => config('app.name', 'Sistem Antrian'),
                'tagline' => 'Pelayanan prima untuk semua pelanggan',
                'service_hours' => 'Senin-Jumat 08.00-15.00',
            ]);
        });

        return self::$organization;
    }

    public static function forgetOrganizationCache(): void
    {
        Cache::forget(self::ORG_CACHE_KEY);
        self::$organization = null;
    }

    public static function activeAnnouncements(): Collection
    {
        if (self::$activeAnnouncements instanceof Collection) {
            return self::$activeAnnouncements;
        }

        self::$activeAnnouncements = Cache::remember(
            self::ACTIVE_ANNOUNCEMENTS_CACHE_KEY,
            now()->addSeconds(15),
            fn () => Announcement::active()->get()
        );

        return self::$activeAnnouncements;
    }

    public static function forgetActiveAnnouncementsCache(): void
    {
        Cache::forget(self::ACTIVE_ANNOUNCEMENTS_CACHE_KEY);
        self::$activeAnnouncements = null;
    }
}

