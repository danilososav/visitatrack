<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Resources\Visits\VisitResource;
use App\Models\Visit;
use Filament\Resources\Pages\Page;

class VisitsMap extends Page
{
    protected static string $resource = VisitResource::class;

    protected string $view = 'filament.resources.visits.pages.visits-map';

    protected static ?string $title = 'Mapa';

    public function getMapDataProperty(): array
    {
        return Visit::query()
            ->whereNotNull('departed_base_lat')
            ->with(['worker', 'company', 'machine', 'trackPoints'])
            ->latest('departed_base_at')
            ->limit(150)
            ->get()
            ->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'worker' => $visit->worker?->name,
                'destination' => $visit->destinationName(),
                'status' => $visit->status,
                'statusLabel' => self::statusLabel($visit->status),
                'checkpoints' => [
                    'departed_base' => self::point($visit->departed_base_lat, $visit->departed_base_lng, $visit->departed_base_at),
                    'arrived_client' => self::point($visit->arrived_client_lat, $visit->arrived_client_lng, $visit->arrived_client_at),
                    'departed_client' => self::point($visit->departed_client_lat, $visit->departed_client_lng, $visit->departed_client_at),
                    'arrived_base' => self::point($visit->arrived_base_lat, $visit->arrived_base_lng, $visit->arrived_base_at),
                ],
                'legs' => [
                    'to_client' => $visit->trackPoints->where('leg', 'to_client')->map(fn ($p) => [(float) $p->lat, (float) $p->lng])->values(),
                    'to_base' => $visit->trackPoints->where('leg', 'to_base')->map(fn ($p) => [(float) $p->lat, (float) $p->lng])->values(),
                ],
            ])
            ->values()
            ->all();
    }

    private static function point(mixed $lat, mixed $lng, mixed $time): ?array
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        return ['lat' => (float) $lat, 'lng' => (float) $lng, 'time' => $time?->format('d/m H:i')];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            Visit::STATUS_TRAVELING_TO => 'Viajando al destino',
            Visit::STATUS_AT_CLIENT => 'En el destino',
            Visit::STATUS_TRAVELING_BACK => 'Volviendo a base',
            Visit::STATUS_PENDING_APPROVAL => 'Pendiente de aprobación',
            Visit::STATUS_COMPLETED => 'Completada',
            Visit::STATUS_CANCELLED => 'Cancelada',
            default => $status,
        };
    }
}
