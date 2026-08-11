<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;

class VisitsTrendChart extends ChartWidget
{
    protected ?string $heading = 'Visitas de los últimos 14 días';

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $i) => today()->subDays($i));

        $visits = Visit::query()
            ->whereDate('departed_base_at', '>=', $days->first())
            ->get()
            ->groupBy(fn (Visit $v) => $v->departed_base_at->toDateString());

        $clientCounts = $days->map(fn (Carbon $d) => $visits->get($d->toDateString(), collect())
            ->where('type', Visit::TYPE_CLIENT_VISIT)->count());

        $machineCounts = $days->map(fn (Carbon $d) => $visits->get($d->toDateString(), collect())
            ->where('type', Visit::TYPE_MACHINE_JOB)->count());

        return [
            'datasets' => [
                [
                    'label' => 'Visitas a cliente',
                    'data' => $clientCounts->values(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Trabajos con máquina',
                    'data' => $machineCounts->values(),
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $days->map(fn (Carbon $d) => $d->format('d/m'))->values(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]],
            ],
        ];
    }
}
