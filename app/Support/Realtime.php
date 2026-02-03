<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Loket;
use App\Models\QueueEvent;
use App\Support\Settings;

class Realtime
{
    public static function push(string $type, array $payload = [], array $meta = []): QueueEvent
    {
        return QueueEvent::create([
            'type' => $type,
            'ticket_no' => $meta['ticket_no'] ?? null,
            'service_code' => $meta['service_code'] ?? null,
            'loket_code' => $meta['loket_code'] ?? null,
            'status' => $meta['status'] ?? null,
            'payload' => $payload ?: null,
            'occurred_at' => now(),
        ]);
    }

    public static function broadcastAnnouncements(): QueueEvent
    {
        $announcements = Announcement::active()
            ->get(['id', 'title', 'type', 'body', 'media_path', 'video_url', 'is_active', 'priority', 'starts_at', 'ends_at'])
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'type' => $a->type,
                    'body' => $a->body,
                    'media_path' => $a->media_path,
                    'video_url' => $a->video_url,
                    'starts_at' => $a->starts_at?->toIso8601String(),
                    'ends_at' => $a->ends_at?->toIso8601String(),
                ];
            })
            ->values();

        return self::push('ANNOUNCEMENT_UPDATED', ['announcements' => $announcements]);
    }

    public static function broadcastOrganization(): QueueEvent
    {
        $org = Settings::organization();

        return self::push('ORGANIZATION_UPDATED', [
            'organization' => [
                'name' => $org->name,
                'tagline' => $org->tagline,
                'logo_path' => $org->logo_path,
                'address' => $org->address,
                'contact_phone' => $org->contact_phone,
                'contact_email' => $org->contact_email,
                'service_hours' => $org->service_hours,
                'general_notice' => $org->general_notice,
            ],
        ]);
    }

    public static function broadcastCounters(): QueueEvent
    {
        $counters = Loket::query()
            ->with('service:id,code,name')
            ->orderBy('code')
            ->get()
            ->map(function ($loket) {
                return [
                    'code' => $loket->code,
                    'name' => $loket->name,
                    'is_active' => (bool) $loket->is_active,
                    'service' => [
                        'code' => $loket->service?->code,
                        'name' => $loket->service?->name,
                    ],
                ];
            });

        return self::push('COUNTER_STATUS_UPDATED', ['counters' => $counters]);
    }
}
