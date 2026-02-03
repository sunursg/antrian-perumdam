<?php

namespace App\Observers;

use App\Models\Loket;
use App\Support\ActivityLogger;
use App\Support\Realtime;
use Illuminate\Support\Facades\Auth;

class LoketObserver
{
    public function saved(Loket $loket): void
    {
        ActivityLogger::log(
            'loket.saved',
            'Loket diperbarui.',
            $loket,
            Auth::user(),
            ['code' => $loket->code, 'is_active' => $loket->is_active]
        );

        Realtime::broadcastCounters();
    }

    public function deleted(Loket $loket): void
    {
        ActivityLogger::log(
            'loket.deleted',
            'Loket dihapus.',
            $loket,
            Auth::user(),
            ['code' => $loket->code]
        );

        Realtime::broadcastCounters();
    }
}
