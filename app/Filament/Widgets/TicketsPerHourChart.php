<?php

namespace App\Filament\Widgets;

use App\Models\QueueTicket;
use Filament\Widgets\ChartWidget;

class TicketsPerHourChart extends ChartWidget
{
    protected ?string $heading = 'Tiket per Jam (Hari Ini)';

    protected function getData(): array
    {
        $rows = QueueTicket::query()
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = $rows->pluck('hour')
            ->map(fn ($h) => str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00')
            ->all();

        $data = $rows->pluck('total')->all();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Tiket',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}