<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $activeCount = Visit::query()->whereIn('status', Visit::ACTIVE_STATUSES)->count();
        $pendingCount = Visit::query()->where('status', Visit::STATUS_PENDING_APPROVAL)->count();
        $todayCount = Visit::query()->whereDate('departed_base_at', today())->count();
        $workersCount = User::query()->where('role', 'worker')->count();

        return [
            Stat::make('En ruta ahora', $activeCount)
                ->description('Trabajadores viajando o en destino')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Pendientes de aprobación', $pendingCount)
                ->description('Esperando revisión del admin')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->url(fn () => \App\Filament\Resources\Visits\VisitResource::getUrl('index', ['activeTab' => 'pendientes'])),

            Stat::make('Visitas hoy', $todayCount)
                ->description('Salidas registradas en el día')
                ->descriptionIcon('heroicon-m-calendar'),

            Stat::make('Trabajadores', $workersCount)
                ->description('Cuentas activas en el equipo')
                ->descriptionIcon('heroicon-m-users')
                ->url(fn () => \App\Filament\Resources\Users\UserResource::getUrl('index')),
        ];
    }
}
