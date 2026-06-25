<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('total')
                    ->money(),
                TextEntry::make('status')->badge()->color(fn (OrderStatus $state) => $state->color()),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),

                RepeatableEntry::make('orderProducts')
                    ->columnSpan(3)
                    ->label('Products')
                    ->table([
                        TableColumn::make('Name'),
                        TableColumn::make('Quantity'),
                        TableColumn::make('Price'),
                        TableColumn::make('Total'),
                    ])
                    ->columns(2)
                    ->grid(2)
                    ->schema([
                        TextEntry::make('product.name')->label('Producto'),
                        TextEntry::make('quantity')->numeric(),
                        TextEntry::make('product_price')->money(),
                        TextEntry::make('subtotal')->money(),
                    ]),
            ]);
    }
}
