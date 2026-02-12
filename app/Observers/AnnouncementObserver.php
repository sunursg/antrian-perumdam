<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Support\ActivityLogger;
use App\Support\Realtime;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;

class AnnouncementObserver
{
    public function saved(Announcement $announcement): void
    {
        Settings::forgetActiveAnnouncementsCache();

        ActivityLogger::log(
            'announcement.saved',
            'Pengumuman disimpan/diubah.',
            $announcement,
            Auth::user(),
            ['title' => $announcement->title, 'type' => $announcement->type]
        );

        Realtime::broadcastAnnouncements();
    }

    public function deleted(Announcement $announcement): void
    {
        Settings::forgetActiveAnnouncementsCache();

        ActivityLogger::log(
            'announcement.deleted',
            'Pengumuman dihapus.',
            $announcement,
            Auth::user(),
            ['title' => $announcement->title]
        );

        Realtime::broadcastAnnouncements();
    }
}
