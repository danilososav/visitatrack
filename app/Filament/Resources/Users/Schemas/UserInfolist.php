<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Nombre'),
                TextEntry::make('email')->label('Email'),
                TextEntry::make('role')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'admin' ? 'Administrador' : 'Trabajador'),
                TextEntry::make('phone')->label('Teléfono')->placeholder('-'),
                TextEntry::make('base_lat')->label('Latitud base')->numeric()->placeholder('-'),
                TextEntry::make('base_lng')->label('Longitud base')->numeric()->placeholder('-'),
                TextEntry::make('created_at')->label('Creado')->dateTime()->placeholder('-'),
            ]);
    }
}
