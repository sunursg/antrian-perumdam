<?php

namespace App\Filament\Widgets;

use App\Enums\TicketStatus;
use App\Models\QueueTicket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QueueKpiWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    protected function getStats(): array
    {
        $dateKey = now()->format('Y-m-d');

        $total = QueueTicket::query()->where('date_key', $dateKey)->count();
        $served = QueueTicket::query()->where('date_key', $dateKey)->where('status', TicketStatus::SELESAI->value)->count();
        $noshow = QueueTicket::query()->where('date_key', $dateKey)->where('status', TicketStatus::NO_SHOW->value)->count();

        $avgSeconds = QueueTicket::query()
            ->where('date_key', $dateKey)
            ->whereNotNull('called_at')
            ->whereNotNull('served_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, called_at, served_at)) as avg_sec')
            ->value('avg_sec');

        $avgMinutes = $avgSeconds ? round($avgSeconds / 60, 1) : 0;

        return [
            Stat::make('Total Hari Ini', $total),
            Stat::make('Served', $served),
            Stat::make('No-show', $noshow),
            Stat::make('Rata-rata Waktu Layanan', $avgMinutes . ' mnt'),
        ];
    }
}
