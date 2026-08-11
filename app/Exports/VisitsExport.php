<?php

namespace App\Exports;

use App\Services\VisitExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VisitsExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Builder $query) {}

    public function collection(): Collection
    {
        return app(VisitExportService::class)
            ->rows($this->query)
            ->map(fn (array $row) => array_values($row));
    }

    public function headings(): array
    {
        return [
            'Trabajador', 'Tipo', 'Estado', 'Empresa / Máquina', 'N° OV', 'N° OT', 'Actividades',
            'Salida de base', 'Llegada al destino', 'Salida del destino', 'Llegada a base',
            'Distancia (km)', 'Tiempo en destino (min)', 'Duración total (min)',
        ];
    }
}
