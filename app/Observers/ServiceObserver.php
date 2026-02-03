<?php

namespace App\Observers;

use App\Models\Service;
use App\Support\ActivityLogger;
use App\Support\Realtime;
use Illuminate\Support\Facades\Auth;

class ServiceObserver
{
    public function saved(Service $service): void
    {
        ActivityLogger::log(
            'service.saved',
            'Layanan diperbarui.',
            $service,
            Auth::user(),
            ['code' => $service->code, 'is_active' => $service->is_active]
        );

        // Service changes may affect display labels
        Realtime::broadcastCounters();
    }

    public function deleted(Service $service): void
    {
        ActivityLogger::log(
            'service.deleted',
            'Layanan dihapus.',
            $service,
            Auth::user(),
            ['code' => $service->code]
        );

        Realtime::broadcastCounters();
    }
}
