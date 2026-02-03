<?php

namespace App\Http\Controllers\Public;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Loket;
use App\Models\QueueTicket;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;

class PublicStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $dateKey = now()->format('Y-m-d');

        $lokets = Loket::query()
            ->with('service:id,code,name')
            ->orderBy('code')
            ->get();

        $counters = $lokets->map(function ($loket) use ($dateKey) {
            $called = QueueTicket::query()
                ->where('date_key', $dateKey)
                ->where('loket_id', $loket->id)
                ->where('status', TicketStatus::DIPANGGIL->value)
                ->latest('called_at')
                ->first();

            return [
                'loket' => ['code' => $loket->code, 'name' => $loket->name],
                'service' => ['code' => $loket->service->code, 'name' => $loket->service->name],
                'is_active' => (bool) $loket->is_active,
                'sedang_dipanggil' => $called?->ticket_no,
            ];
        });

        $announcements = Settings::activeAnnouncements()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'type' => $a->type,
                'body' => $a->body,
                'media_path' => $a->media_path,
                'video_url' => $a->video_url,
                'starts_at' => $a->starts_at?->toIso8601String(),
                'ends_at' => $a->ends_at?->toIso8601String(),
            ]);

        $org = Settings::organization();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'organization' => [
                    'name' => $org->name,
                    'tagline' => $org->tagline,
                    'logo_path' => $org->logo_path,
                    'service_hours' => $org->service_hours,
                    'address' => $org->address,
                    'contact_phone' => $org->contact_phone,
                    'contact_email' => $org->contact_email,
                    'general_notice' => $org->general_notice,
                ],
                'announcements' => $announcements,
                'counters' => $counters,
            ],
            'errors' => null,
        ]);
    }
}
