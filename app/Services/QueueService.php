<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Loket;
use App\Models\QueueEvent;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\TicketCounter;
use App\Models\User;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QueueService
{
    private const EVENT_CREATED = 'CREATED';
    private const EVENT_CALLED = 'CALLED';
    private const EVENT_RECALLED = 'RECALLED';
    private const EVENT_SKIPPED = 'SKIPPED';
    private const EVENT_SERVED = 'SERVED';

    public function takeTicket(Service $service, ?User $actor = null, ?string $ip = null): QueueTicket
    {
        $now = now();
        $dateKey = $now->format('Y-m-d');

        if (!$service->is_active) {
            throw ValidationException::withMessages(['service' => 'Layanan sedang tidak aktif.']);
        }

        $open = Carbon::createFromTimeString($service->open_at);
        $close = Carbon::createFromTimeString($service->close_at);
        if ($now->lt($open) || $now->gt($close)) {
            throw ValidationException::withMessages([
                'service' => "Layanan tutup. Jam layanan: {$service->open_at}-{$service->close_at}.",
            ]);
        }

        $countToday = QueueTicket::query()
            ->where('date_key', $dateKey)
            ->where('service_id', $service->id)
            ->count();

        if ($countToday >= $service->daily_quota) {
            throw ValidationException::withMessages([
                'quota' => 'Kuota harian layanan ini sudah penuh. Silakan datang besok atau pilih layanan lain.',
            ]);
        }

        return DB::transaction(function () use ($service, $dateKey, $actor, $ip) {
            $counter = TicketCounter::query()
                ->where('date_key', $dateKey)
                ->where('service_id', $service->id)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                try {
                    $counter = TicketCounter::create([
                        'date_key' => $dateKey,
                        'service_id' => $service->id,
                        'last_seq' => 0,
                    ]);
                } catch (QueryException $e) {
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }

                    $counter = TicketCounter::query()
                        ->where('date_key', $dateKey)
                        ->where('service_id', $service->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }
            }

            $nextSeq = $counter->last_seq + 1;

            if ($nextSeq > 999) {
                throw ValidationException::withMessages(['seq' => 'Nomor antrian hari ini sudah mencapai batas.']);
            }

            $ticketNo = sprintf('%s-%d', $service->code, $nextSeq);

            $ticket = QueueTicket::create([
                'date_key' => $dateKey,
                'service_id' => $service->id,
                'seq' => $nextSeq,
                'ticket_no' => $ticketNo,
                'status' => TicketStatus::MENUNGGU->value,
            ]);

            $counter->update(['last_seq' => $nextSeq]);

            $this->logEvent(self::EVENT_CREATED, $ticket, null, $actor, $ip);

            return $ticket;
        });
    }

    public function callNext(Loket $loket, ?User $actor = null, ?string $ip = null): ?QueueTicket
    {
        $dateKey = now()->format('Y-m-d');

        if (!$loket->is_active) {
            throw ValidationException::withMessages([
                'loket' => 'Loket sedang tidak aktif.',
            ]);
        }

        if (!$loket->service?->is_active) {
            throw ValidationException::withMessages([
                'service' => 'Layanan untuk loket ini sedang tidak aktif.',
            ]);
        }

        return DB::transaction(function () use ($loket, $dateKey, $actor, $ip) {
            $ticket = QueueTicket::query()
                ->where('date_key', $dateKey)
                ->where('service_id', $loket->service_id)
                ->where('status', TicketStatus::MENUNGGU->value)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$ticket) {
                return null;
            }

            $ticket->update([
                'status' => TicketStatus::DIPANGGIL->value,
                'loket_id' => $loket->id,
                'called_at' => now(),
            ]);

            $this->logEvent(self::EVENT_CALLED, $ticket, $loket, $actor, $ip);

            return $ticket->refresh();
        });
    }

    public function recall(QueueTicket $ticket, ?User $actor = null, ?string $ip = null): QueueTicket
    {
        if ($ticket->status !== TicketStatus::DIPANGGIL->value) {
            throw ValidationException::withMessages([
                'ticket' => 'Hanya tiket berstatus DIPANGGIL yang bisa dipanggil ulang.',
            ]);
        }

        $ticket->update(['called_at' => now()]);
        $this->logEvent(self::EVENT_RECALLED, $ticket, $ticket->loket, $actor, $ip);

        return $ticket->refresh();
    }

    public function markNoShow(QueueTicket $ticket, ?User $actor = null, ?string $ip = null): QueueTicket
    {
        if (!in_array($ticket->status, [TicketStatus::DIPANGGIL->value, TicketStatus::MENUNGGU->value], true)) {
            throw ValidationException::withMessages([
                'ticket' => 'Tiket ini tidak bisa diubah menjadi NO_SHOW.',
            ]);
        }

        $ticket->update([
            'status' => TicketStatus::NO_SHOW->value,
            'noshow_at' => now(),
        ]);

        $this->logEvent(self::EVENT_SKIPPED, $ticket, $ticket->loket, $actor, $ip);

        return $ticket->refresh();
    }

    public function serve(QueueTicket $ticket, ?User $actor = null, ?string $ip = null): QueueTicket
    {
        if ($ticket->status !== TicketStatus::DIPANGGIL->value) {
            throw ValidationException::withMessages([
                'ticket' => 'Hanya tiket DIPANGGIL yang bisa diselesaikan.',
            ]);
        }

        $ticket->update([
            'status' => TicketStatus::SELESAI->value,
            'served_at' => now(),
        ]);

        $this->logEvent(self::EVENT_SERVED, $ticket, $ticket->loket, $actor, $ip);

        return $ticket->refresh();
    }

    public function logEvent(
        string $type,
        QueueTicket $ticket,
        ?Loket $loket,
        ?User $actor = null,
        ?string $ip = null,
    ): QueueEvent {
        $ticket->loadMissing('service', 'loket');

        $event = QueueEvent::create([
            'type' => $type,
            'ticket_no' => $ticket->ticket_no,
            'service_code' => $ticket->service->code,
            'loket_code' => $loket?->code ?? $ticket->loket?->code,
            'status' => $ticket->status,
            'payload' => [
                'ticket_id' => $ticket->id,
                'service_name' => $ticket->service->name,
                'loket_name' => $loket?->name ?? $ticket->loket?->name,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'ip' => $ip,
            ],
            'occurred_at' => now(),
        ]);

        ActivityLogger::log(
            "queue.{$type}",
            "Event {$type} untuk tiket {$ticket->ticket_no}",
            $ticket,
            $actor,
            [
                'loket' => $loket?->code ?? $ticket->loket?->code,
                'status' => $ticket->status,
            ],
            $ip
        );

        return $event;
    }
}
