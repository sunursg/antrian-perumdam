<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Loket;
use App\Models\QueueEvent;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\TicketCounter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QueueService
{
    public function takeTicket(Service $service): QueueTicket
    {
        $now = now();
        $dateKey = $now->format('Ymd');

        if (!$service->is_active) {
            throw ValidationException::withMessages(['service' => 'Layanan sedang tidak aktif.']);
        }

        $open = Carbon::parse($service->open_at);
        $close = Carbon::parse($service->close_at);
        if ($now->lt($open) || $now->gt($close)) {
            throw ValidationException::withMessages([
                'service' => "Layanan tutup. Jam layanan: {$service->open_at}–{$service->close_at}.",
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

        return DB::transaction(function () use ($service, $dateKey) {
            $counter = TicketCounter::query()
                ->where('date_key', $dateKey)
                ->where('service_id', $service->id)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = TicketCounter::create([
                    'date_key' => $dateKey,
                    'service_id' => $service->id,
                    'last_seq' => 0,
                ]);
                $counter->refresh();
            }

            $nextSeq = $counter->last_seq + 1;

            if ($nextSeq > 999) {
                throw ValidationException::withMessages(['seq' => 'Nomor antrian hari ini sudah mencapai batas.']);
            }

            $ticketNo = sprintf('%s-%03d', $service->code, $nextSeq);

            $ticket = QueueTicket::create([
                'date_key' => $dateKey,
                'service_id' => $service->id,
                'seq' => $nextSeq,
                'ticket_no' => $ticketNo,
                'status' => TicketStatus::MENUNGGU->value,
            ]);

            $counter->update(['last_seq' => $nextSeq]);

            $this->logEvent('queue_update', $ticket, null);

            return $ticket;
        });
    }

    public function callNext(Loket $loket, int $operatorUserId): ?QueueTicket
    {
        $dateKey = now()->format('Ymd');

        return DB::transaction(function () use ($loket, $dateKey) {
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

            $this->logEvent('queue_update', $ticket, $loket);

            return $ticket->refresh();
        });
    }

    public function recall(QueueTicket $ticket): QueueTicket
    {
        if ($ticket->status !== TicketStatus::DIPANGGIL->value) {
            throw ValidationException::withMessages([
                'ticket' => 'Hanya tiket berstatus DIPANGGIL yang bisa dipanggil ulang.',
            ]);
        }

        $ticket->update(['called_at' => now()]);
        $this->logEvent('queue_update', $ticket, $ticket->loket);

        return $ticket->refresh();
    }

    public function markNoShow(QueueTicket $ticket): QueueTicket
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

        $this->logEvent('queue_update', $ticket, $ticket->loket);

        return $ticket->refresh();
    }

    public function serve(QueueTicket $ticket): QueueTicket
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

        $this->logEvent('queue_update', $ticket, $ticket->loket);

        return $ticket->refresh();
    }

    public function logEvent(string $type, QueueTicket $ticket, ?Loket $loket): QueueEvent
    {
        $ticket->loadMissing('service', 'loket');

        return QueueEvent::create([
            'type' => $type,
            'ticket_no' => $ticket->ticket_no,
            'service_code' => $ticket->service->code,
            'loket_code' => $loket?->code ?? $ticket->loket?->code,
            'status' => $ticket->status,
            'payload' => [
                'ticket_id' => $ticket->id,
                'service_name' => $ticket->service->name,
                'loket_name' => $loket?->name ?? $ticket->loket?->name,
            ],
            'occurred_at' => now(),
        ]);
    }
}
