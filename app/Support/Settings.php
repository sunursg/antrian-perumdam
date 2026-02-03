<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const ORG_CACHE_KEY = 'org:current';

    public static function organization(): Organization
    {
        return Cache::remember(self::ORG_CACHE_KEY, now()->addMinutes(10), function () {
            return Organization::query()->first() ?? new Organization([
                'name' => config('app.name', 'Sistem Antrian'),
                'tagline' => 'Pelayanan prima untuk semua pelanggan',
                'service_hours' => 'Senin–Jumat 08.00–15.00',
            ]);
        });
    }

    public static function forgetOrganizationCache(): void
    {
        Cache::forget(self::ORG_CACHE_KEY);
    }

    public static function activeAnnouncements(): Collection
    {
        return Announcement::active()->get();
    }
}
