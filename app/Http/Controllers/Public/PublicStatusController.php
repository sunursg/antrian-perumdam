<?php

namespace App\Http\Controllers\Public;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Loket;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;

class PublicStatusController extends Controller
{
    private function buildStatusData(): array
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
                'service' => ['code' => $loket->service?->code, 'name' => $loket->service?->name],
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

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $serviceStats = $services->map(function ($service) use ($dateKey) {
            $waitingCount = QueueTicket::query()
                ->where('date_key', $dateKey)
                ->where('service_id', $service->id)
                ->where('status', TicketStatus::MENUNGGU->value)
                ->count();

            $called = QueueTicket::query()
                ->where('date_key', $dateKey)
                ->where('service_id', $service->id)
                ->where('status', TicketStatus::DIPANGGIL->value)
                ->latest('called_at')
                ->first();

            $estimateMinutes = $waitingCount > 0
                ? $waitingCount * (int) $service->avg_service_minutes
                : 0;

            return [
                'id' => $service->id,
                'code' => $service->code,
                'name' => $service->name,
                'description' => $service->description,
                'requires_confirmation' => (bool) $service->requires_confirmation,
                'current_ticket' => $called?->ticket_no ?? '-',
                'waiting' => $waitingCount,
                'estimated_minutes' => $estimateMinutes,
            ];
        });

        return [
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
            'services' => $serviceStats,
            'waiting_by_service' => $serviceStats->mapWithKeys(fn ($s) => [$s['code'] => $s['waiting']]),
        ];
    }

    public function index(): JsonResponse
    {
        $data = $this->buildStatusData();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $data,
            'errors' => null,
        ]);
    }

    public function displayState(): JsonResponse
    {
        $data = $this->buildStatusData();
        $dateKey = now()->format('Y-m-d');

        $latestCalled = QueueTicket::query()
            ->with(['loket:id,name', 'service:id,name'])
            ->where('date_key', $dateKey)
            ->where('status', TicketStatus::DIPANGGIL->value)
            ->latest('called_at')
            ->latest('id')
            ->first();

        $nextQueue = QueueTicket::query()
            ->with('service:id,name')
            ->where('date_key', $dateKey)
            ->where('status', TicketStatus::MENUNGGU->value)
            ->orderBy('seq')
            ->limit(10)
            ->get()
            ->map(fn (QueueTicket $ticket) => [
                'ticket_no' => $ticket->ticket_no,
                'service' => $ticket->service?->name ?? '-',
            ]);

        $activeAnnouncements = collect($data['announcements'])
            ->map(fn (array $item) => [
                'title' => $item['title'] ?? '',
                'active' => true,
                'video_url' => $item['video_url'] ?? null,
            ])
            ->values();

        return response()->json([
            'company' => [
                'name' => $data['organization']['name'],
                'slogan' => $data['organization']['tagline'],
                'logo_url' => $data['organization']['logo_path']
                    ? asset('storage/' . $data['organization']['logo_path'])
                    : asset('logo.png'),
            ],
            'now_serving' => $latestCalled
                ? [
                    'counter' => $latestCalled->loket?->name ?? 'Loket -',
                    'ticket_no' => $latestCalled->ticket_no,
                    'service' => $latestCalled->service?->name ?? '-',
                ]
                : null,
            'next_queue' => $nextQueue,
            'counters' => collect($data['counters'])
                ->map(fn (array $counter) => [
                    'name' => $counter['loket']['name'] ?? '-',
                    'active' => (bool) ($counter['is_active'] ?? false),
                    'current_ticket' => $counter['sedang_dipanggil'] ?? null,
                ])
                ->values(),
            'announcements' => $activeAnnouncements,
        ]);
    }
}
