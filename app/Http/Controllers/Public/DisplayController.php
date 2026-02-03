<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Loket;
use App\Models\QueueTicket;
use App\Enums\TicketStatus;
use App\Support\Settings;

class DisplayController extends Controller
{
    public function page()
    {
        $organization = Settings::organization();
        $announcements = Settings::activeAnnouncements();
        $dateKey = now()->format('Y-m-d');
        $counters = Loket::query()
            ->with('service:id,code,name')
            ->orderBy('code')
            ->get()
            ->map(function ($loket) use ($dateKey) {
                $called = QueueTicket::query()
                    ->where('date_key', $dateKey)
                    ->where('loket_id', $loket->id)
                    ->where('status', TicketStatus::DIPANGGIL->value)
                    ->latest('called_at')
                    ->first();

                return [
                    'loket' => ['code' => $loket->code, 'name' => $loket->name],
                    'service' => ['code' => $loket->service?->code, 'name' => $loket->service?->name],
                    'is_active' => (bool) $loket->is_active,
                    'sedang_dipanggil' => $called?->ticket_no,
                ];
            });

        return view('pages.display', [
            'organization' => $organization,
            'announcements' => $announcements,
            'counters' => $counters,
        ]);
    }
}
