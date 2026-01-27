<?php

namespace App\Http\Controllers\Public;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Loket;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;

class PublicStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $dateKey = now()->format('Ymd');

        $lokets = Loket::query()
            ->with('service:id,code,name,avg_service_minutes')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $data = $lokets->map(function ($loket) use ($dateKey) {
            $called = QueueTicket::query()
                ->where('date_key', $dateKey)
                ->where('loket_id', $loket->id)
                ->where('status', TicketStatus::DIPANGGIL->value)
                ->latest('called_at')
                ->first();

            $waiting = QueueTicket::query()
                ->where('date_key', $dateKey)
                ->where('service_id', $loket->service_id)
                ->where('status', TicketStatus::MENUNGGU->value)
                ->count();

            $est = $waiting * (int) $loket->service->avg_service_minutes;

            return [
                'loket' => ['code' => $loket->code, 'name' => $loket->name],
                'service' => ['code' => $loket->service->code, 'name' => $loket->service->name],
                'sedang_dipanggil' => $called?->ticket_no,
                'jumlah_menunggu' => $waiting,
                'estimasi_menit' => $est,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $data,
            'errors' => null,
        ]);
    }
}
