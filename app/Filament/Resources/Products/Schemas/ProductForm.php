<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('price')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('$')
                    ->required(),
                TextInput::make('stock')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
