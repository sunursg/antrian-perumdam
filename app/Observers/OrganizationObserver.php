<?php

namespace App\Observers;

use App\Models\Organization;
use App\Support\ActivityLogger;
use App\Support\Realtime;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;

class OrganizationObserver
{
    public function saved(Organization $organization): void
    {
        Settings::forgetOrganizationCache();

        ActivityLogger::log(
            'organization.saved',
            'Pengaturan organisasi diperbarui.',
            $organization,
            Auth::user(),
            ['name' => $organization->name]
        );

        Realtime::broadcastOrganization();
    }
}
