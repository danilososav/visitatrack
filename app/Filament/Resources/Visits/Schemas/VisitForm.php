<?php

namespace App\Filament\Resources\Visits\Schemas;

use App\Models\Visit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos generales')
                    ->columns(2)
                    ->components([
                        Select::make('worker_id')
                            ->label('Trabajador')
                            ->relationship('worker', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                Visit::TYPE_CLIENT_VISIT => 'Visita a cliente',
                                Visit::TYPE_MACHINE_JOB => 'Trabajo con máquina',
                            ])
                            ->live()
                            ->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                Visit::STATUS_TRAVELING_TO => 'Viajando al destino',
                                Visit::STATUS_AT_CLIENT => 'En el destino',
                                Visit::STATUS_TRAVELING_BACK => 'Volviendo a la base',
                                Visit::STATUS_PENDING_APPROVAL => 'Pendiente de aprobación',
                                Visit::STATUS_COMPLETED => 'Completada',
                                Visit::STATUS_CANCELLED => 'Cancelada',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('El estado avanza solo. Lo actualiza el trabajador al confirmar cada paso, o la acción Aprobar/Cancelar del admin.')
                            ->required(),
                        Select::make('company_id')
                            ->label('Empresa')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->visible(fn ($get) => $get('type') === Visit::TYPE_CLIENT_VISIT),
                        Select::make('machine_id')
                            ->label('Máquina')
                            ->relationship('machine', 'name')
                            ->searchable()
                            ->visible(fn ($get) => $get('type') === Visit::TYPE_MACHINE_JOB),
                        TextInput::make('ov_number')->label('N° OV'),
                        TextInput::make('ot_number')->label('N° OT'),
                        Textarea::make('notes')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ]),

                Section::make('Checkpoints GPS')
                    ->description('Se registran automáticamente desde el celular del trabajador en cada paso — no son editables.')
                    ->columns(1)
                    ->collapsed()
                    ->components([
                        Grid::make(3)->components([
                            DateTimePicker::make('departed_base_at')->label('Salida de base')->disabled(),
                            TextInput::make('departed_base_lat')->label('Lat.')->numeric()->disabled(),
                            TextInput::make('departed_base_lng')->label('Lng.')->numeric()->disabled(),
                        ]),
                        Grid::make(3)->components([
                            DateTimePicker::make('arrived_client_at')->label('Llegada al destino')->disabled(),
                            TextInput::make('arrived_client_lat')->label('Lat.')->numeric()->disabled(),
                            TextInput::make('arrived_client_lng')->label('Lng.')->numeric()->disabled(),
                        ]),
                        Grid::make(3)->components([
                            DateTimePicker::make('departed_client_at')->label('Salida del destino')->disabled(),
                            TextInput::make('departed_client_lat')->label('Lat.')->numeric()->disabled(),
                            TextInput::make('departed_client_lng')->label('Lng.')->numeric()->disabled(),
                        ]),
                        Grid::make(3)->components([
                            DateTimePicker::make('arrived_base_at')->label('Llegada a base')->disabled(),
                            TextInput::make('arrived_base_lat')->label('Lat.')->numeric()->disabled(),
                            TextInput::make('arrived_base_lng')->label('Lng.')->numeric()->disabled(),
                        ]),
                    ]),
            ]);
    }
}
