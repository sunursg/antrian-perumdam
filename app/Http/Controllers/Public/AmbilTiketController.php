<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\TakeTicketRequest;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AmbilTiketController extends Controller
{
    public function page()
    {
        return view('pages.ambil-tiket');
    }

    public function take(TakeTicketRequest $request, QueueService $queue): JsonResponse
    {
        try {
            $service = Service::query()->where('code', $request->string('service_code'))->firstOrFail();
            $ticket = $queue->takeTicket($service);

            $waitingCount = $service->tickets()
                ->where('date_key', now()->format('Ymd'))
                ->where('status', \App\Enums\TicketStatus::MENUNGGU->value)
                ->count();

            $estimateMinutes = max(1, $waitingCount) * (int) $service->avg_service_minutes;

            return response()->json([
                'success' => true,
                'message' => 'Tiket berhasil dibuat.',
                'data' => [
                    'ticket_no' => $ticket->ticket_no,
                    'service' => ['code' => $service->code, 'name' => $service->name],
                    'taken_at' => $ticket->created_at->toIso8601String(),
                    'estimate_minutes' => $estimateMinutes,
                    'ticket_id' => $ticket->id,
                    'qr_value' => $ticket->ticket_no,
                ],
                'errors' => null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengambil tiket.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
