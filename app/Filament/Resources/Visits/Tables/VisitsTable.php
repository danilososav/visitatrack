<?php

namespace App\Filament\Resources\Visits\Tables;

use App\Models\Visit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('worker.name')
                    ->label('Trabajador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === Visit::TYPE_CLIENT_VISIT ? 'Visita' : 'Máquina')
                    ->color(fn (string $state) => $state === Visit::TYPE_CLIENT_VISIT ? 'info' : 'gray'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        Visit::STATUS_TRAVELING_TO => 'Viajando al destino',
                        Visit::STATUS_AT_CLIENT => 'En el destino',
                        Visit::STATUS_TRAVELING_BACK => 'Volviendo a base',
                        Visit::STATUS_PENDING_APPROVAL => 'Pendiente de aprobación',
                        Visit::STATUS_COMPLETED => 'Completada',
                        Visit::STATUS_CANCELLED => 'Cancelada',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        Visit::STATUS_PENDING_APPROVAL => 'warning',
                        Visit::STATUS_COMPLETED => 'success',
                        Visit::STATUS_CANCELLED => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('machine.name')
                    ->label('Máquina')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('ov_number')
                    ->label('N° OV')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('ot_number')
                    ->label('N° OT')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('departed_base_at')
                    ->label('Salida')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('arrived_base_at')
                    ->label('Regreso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('worker_id')
                    ->label('Trabajador')
                    ->relationship('worker', 'name'),
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name'),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        Visit::STATUS_TRAVELING_TO => 'Viajando al destino',
                        Visit::STATUS_AT_CLIENT => 'En el destino',
                        Visit::STATUS_TRAVELING_BACK => 'Volviendo a base',
                        Visit::STATUS_PENDING_APPROVAL => 'Pendiente de aprobación',
                        Visit::STATUS_COMPLETED => 'Completada',
                        Visit::STATUS_CANCELLED => 'Cancelada',
                    ]),
                Filter::make('date_range')
                    ->label('Rango de fechas')
                    ->schema([
                        Grid::make(2)->components([
                            \Filament\Forms\Components\DatePicker::make('from')->label('Desde'),
                            \Filament\Forms\Components\DatePicker::make('until')->label('Hasta'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('departed_base_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('departed_base_at', '<=', $date));
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                self::reviewAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function reviewAction(): Action
    {
        return Action::make('review')
            ->label('Revisar')
            ->icon('heroicon-o-eye')
            ->modalHeading(fn (Visit $record) => 'Visita de '.$record->worker?->name)
            ->modalWidth('2xl')
            ->schema([
                Section::make('Detalle')
                    ->columns(2)
                    ->components([
                        TextEntry::make('worker.name')->label('Trabajador'),
                        TextEntry::make('company.name')->label('Empresa')->placeholder('-'),
                        TextEntry::make('machine.name')->label('Máquina')->placeholder('-'),
                        TextEntry::make('ov_number')->label('N° OV')->placeholder('-'),
                        TextEntry::make('ot_number')->label('N° OT')->placeholder('-'),
                        TextEntry::make('notes')->label('Observaciones')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('activities.name')->label('Actividades')->badge()->placeholder('-')->columnSpanFull(),
                    ]),
                Section::make('Recorrido')
                    ->columns(2)
                    ->components([
                        TextEntry::make('departed_base_at')->label('Salida de base')->dateTime('d/m/Y H:i'),
                        TextEntry::make('arrived_client_at')->label('Llegada al destino')->dateTime('d/m/Y H:i')->placeholder('-'),
                        TextEntry::make('departed_client_at')->label('Salida del destino')->dateTime('d/m/Y H:i')->placeholder('-'),
                        TextEntry::make('arrived_base_at')->label('Llegada a base')->dateTime('d/m/Y H:i')->placeholder('-'),
                        TextEntry::make('distance')
                            ->label('Distancia recorrida')
                            ->state(fn (Visit $record) => $record->totalTrackDistanceKm().' km'),
                        TextEntry::make('duration_at_site')
                            ->label('Tiempo en el destino')
                            ->state(fn (Visit $record) => $record->durationAtSiteMinutes() !== null ? $record->durationAtSiteMinutes().' min' : '-'),
                        ViewEntry::make('trackPoints')
                            ->label('Mapa')
                            ->columnSpanFull()
                            ->view('filament.infolists.visit-track-map'),
                    ]),
                Section::make('Fotos')
                    ->visible(fn (Visit $record) => $record->photos->isNotEmpty())
                    ->components([
                        RepeatableEntry::make('photos')
                            ->hiddenLabel()
                            ->schema([
                                ImageEntry::make('url')
                                    ->hiddenLabel()
                                    ->state(fn (\App\Models\VisitPhoto $record) => $record->url()),
                            ])
                            ->columns(4),
                    ]),
                Section::make('Firmas')
                    ->columns(2)
                    ->visible(fn (Visit $record) => $record->status === Visit::STATUS_PENDING_APPROVAL || $record->status === Visit::STATUS_COMPLETED)
                    ->components([
                        ImageEntry::make('workerSignatureUrl')
                            ->label('Firma del trabajador')
                            ->state(fn (Visit $record) => $record->workerSignatureUrl())
                            ->placeholder('Sin firma'),
                        ImageEntry::make('secondSignerUrl')
                            ->label(fn (Visit $record) => 'Firma de '.($record->second_signer_name ?? 'contacto'))
                            ->state(fn (Visit $record) => $record->secondSignerUrl())
                            ->placeholder('Sin firma'),
                    ]),
            ])
            ->modalSubmitAction(false)
            ->modalActions(fn (Visit $record) => $record->status === Visit::STATUS_PENDING_APPROVAL ? [
                Action::make('approve')
                    ->label('Aprobar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Visit $record) {
                        $record->update([
                            'status' => Visit::STATUS_COMPLETED,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Visita aprobada')->success()->send();
                    }),
                Action::make('cancel')
                    ->label('Cancelar visita')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Visit $record) {
                        $record->update(['status' => Visit::STATUS_CANCELLED]);
                        Notification::make()->title('Visita cancelada')->warning()->send();
                    }),
            ] : [
                Action::make('close')->label('Cerrar')->color('gray')->close(),
            ]);
    }
}
