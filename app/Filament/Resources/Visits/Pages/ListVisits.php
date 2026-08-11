<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Exports\VisitsExport;
use App\Filament\Resources\Visits\VisitResource;
use App\Models\Visit;
use App\Services\VisitExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewMap')
                ->label('Ver mapa')
                ->icon('heroicon-o-map')
                ->color('gray')
                ->url(fn () => VisitResource::getUrl('map')),
            Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(fn () => Excel::download(
                    new VisitsExport($this->getFilteredTableQuery()),
                    'visitas-'.now()->format('Y-m-d').'.xlsx',
                )),
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(function () {
                    $rows = app(VisitExportService::class)->rows($this->getFilteredTableQuery());

                    return response()->streamDownload(
                        fn () => print(Pdf::loadView('exports.visits-pdf', ['rows' => $rows])->output()),
                        'visitas-'.now()->format('Y-m-d').'.pdf',
                    );
                }),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas'),
            'visitas' => Tab::make('Visitas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Visit::TYPE_CLIENT_VISIT)),
            'maquinaria' => Tab::make('Maquinaria')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Visit::TYPE_MACHINE_JOB)),
            'pendientes' => Tab::make('Pendientes de aprobación')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Visit::STATUS_PENDING_APPROVAL))
                ->badge(Visit::query()->where('status', Visit::STATUS_PENDING_APPROVAL)->count()),
        ];
    }
}
