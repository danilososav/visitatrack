<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Auditoría';

    protected static ?string $navigationLabel = 'Logs';

    protected static ?string $modelLabel = 'log';

    protected static ?string $pluralModelLabel = 'logs';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('causer.name')->label('Actor')->placeholder('Sistema'),
                TextColumn::make('subject_type')
                    ->label('Entidad')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-'),
                TextColumn::make('event')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('description')->label('Descripción'),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i:s')->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalle del cambio')
                    ->modalSubmitAction(false)
                    ->schema([
                        Section::make()->columns(1)->components([
                            TextEntry::make('causer.name')->label('Actor')->placeholder('Sistema'),
                            TextEntry::make('subject_type')->label('Entidad')->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-'),
                            TextEntry::make('event')->label('Acción')->badge(),
                            TextEntry::make('description')->label('Descripción'),
                            TextEntry::make('attribute_changes')
                                ->label('Cambios')
                                ->formatStateUsing(fn ($state) => $state && $state->isNotEmpty() ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                                ->placeholder('-'),
                            TextEntry::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i:s'),
                        ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
