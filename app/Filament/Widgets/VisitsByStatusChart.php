<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Widgets\ChartWidget;

class VisitsByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Visitas por estado';

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $statuses = [
            Visit::STATUS_TRAVELING_TO => ['Viajando al destino', '#3b82f6'],
            Visit::STATUS_AT_CLIENT => ['En el destino', '#06b6d4'],
            Visit::STATUS_TRAVELING_BACK => ['Volviendo a base', '#f97316'],
            Visit::STATUS_PENDING_APPROVAL => ['Pendiente de aprobación', '#eab308'],
            Visit::STATUS_COMPLETED => ['Completada', '#22c55e'],
            Visit::STATUS_CANCELLED => ['Cancelada', '#ef4444'],
        ];

        $counts = Visit::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [[
                'data' => collect($statuses)->map(fn ($s, $key) => $counts->get($key, 0))->values(),
                'backgroundColor' => collect($statuses)->map(fn ($s) => $s[1])->values(),
            ]],
            'labels' => collect($statuses)->map(fn ($s) => $s[0])->values(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
        ];
    }
}
