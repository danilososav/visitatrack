<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('role')
                    ->options([
                        'admin' => 'Administrador',
                        'worker' => 'Trabajador',
                    ])
                    ->required()
                    ->default('worker'),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel(),
                TextInput::make('base_lat')
                    ->label('Latitud base')
                    ->numeric(),
                TextInput::make('base_lng')
                    ->label('Longitud base')
                    ->numeric(),
                TextInput::make('password')
                    ->password()
                    ->label('Contraseña')
                    ->helperText('Dejar en blanco para no cambiarla.')
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create'),
            ]);
    }
}
