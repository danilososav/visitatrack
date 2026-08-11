<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VisitExportService
{
    /**
     * Flat rows shared by every export format, so Excel and PDF never
     * diverge in how a figure (distance, duration) is computed.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(Builder $query): Collection
    {
        return $query
            ->with(['worker', 'company', 'machine', 'activities', 'trackPoints'])
            ->get()
            ->map(fn (Visit $visit) => [
                'worker' => $visit->worker?->name,
                'type' => $visit->type === Visit::TYPE_CLIENT_VISIT ? 'Visita a cliente' : 'Trabajo con máquina',
                'status' => $this->statusLabel($visit->status),
                'company_or_machine' => $visit->company?->name ?? $visit->machine?->name,
                'ov_number' => $visit->ov_number,
                'ot_number' => $visit->ot_number,
                'activities' => $visit->activities->pluck('name')->join(', '),
                'departed_base_at' => $visit->departed_base_at?->format('d/m/Y H:i'),
                'arrived_client_at' => $visit->arrived_client_at?->format('d/m/Y H:i'),
                'departed_client_at' => $visit->departed_client_at?->format('d/m/Y H:i'),
                'arrived_base_at' => $visit->arrived_base_at?->format('d/m/Y H:i'),
                'distance_km' => $visit->totalTrackDistanceKm(),
                'duration_at_site_min' => $visit->durationAtSiteMinutes(),
                'total_trip_min' => $visit->totalTripMinutes(),
            ])
            ->values();
    }

    private function statusLabel(string $status): string
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
