<?php

namespace App\Filament\Resources\ErrorLogs;

use App\Filament\Resources\ErrorLogs\Pages\ListErrorLogs;
use App\Models\ErrorLogEntry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ErrorLogResource extends Resource
{
    protected static ?string $model = ErrorLogEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Auditoría';

    protected static ?string $navigationLabel = 'Errores';

    protected static ?string $modelLabel = 'error';

    protected static ?string $pluralModelLabel = 'errores';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('level')
                    ->label('Nivel')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'emergency', 'alert', 'critical', 'error' => 'danger',
                        'warning' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('message')->label('Mensaje')->limit(80)->searchable(),
                TextColumn::make('exception_class')->label('Excepción')->toggleable(),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i:s')->sortable(),
            ])
            ->filters([
                SelectFilter::make('level')->options([
                    'emergency' => 'Emergency', 'alert' => 'Alert', 'critical' => 'Critical',
                    'error' => 'Error', 'warning' => 'Warning', 'notice' => 'Notice',
                ]),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalle del error')
                    ->modalSubmitAction(false)
                    ->schema([
                        Section::make()->columns(1)->components([
                            TextEntry::make('level')->label('Nivel')->badge(),
                            TextEntry::make('message')->label('Mensaje'),
                            TextEntry::make('exception_class')->label('Excepción')->placeholder('-'),
                            TextEntry::make('context')
                                ->label('Contexto')
                                ->formatStateUsing(fn (?array $state) => filled($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                                ->placeholder('-'),
                            TextEntry::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i:s'),
                        ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListErrorLogs::route('/'),
        ];
    }
}
