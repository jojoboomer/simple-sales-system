<?php


namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('Product ID')
                    ->copyable(),
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('stock')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Last update')
                    ->placeholder('-'),
            ]);
    }
}
