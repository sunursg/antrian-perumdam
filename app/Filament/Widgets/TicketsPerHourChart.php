<?php

namespace App\Filament\Widgets;

use App\Models\QueueTicket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TicketsPerHourChart extends ChartWidget
{
    protected static ?string $heading = 'Tiket per Jam (Hari Ini)';

    protected function getData(): array
    {
        $dateKey = now()->format('Ymd');

        $rows = QueueTicket::query()
            ->select(DB::raw("HOUR(created_at) as jam"), DB::raw('COUNT(*) as total'))
            ->where('date_key', $dateKey)
            ->groupBy('jam')
            ->orderBy('jam')
            ->get();

        $labels = [];
        $data = [];

        for ($h = 8; $h <= 15; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $found = $rows->firstWhere('jam', $h);
            $data[] = $found?->total ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tiket',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
