<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class OrderForm
{
    /**
     * Calculate and set order total based on provided get/set callables.
     *
     * @param callable $get Receives a path and returns the state at that path
     * @param callable $set Receives a path and a value to set
     * @param bool $isRepeaterLevel Whether the callables are scoped to repeater level
     * @return void
     */
    public static function handleTotal(callable $get, callable $set, bool $isRepeaterLevel = false): void
    {
        $orderProductsPath = $isRepeaterLevel ? 'orderProducts' : '../../orderProducts';
        $totalPath = $isRepeaterLevel ? 'total' : '../../total';

        $orderProducts = $get($orderProductsPath) ?? [];

        $total = 0;
        foreach ($orderProducts as $item) {
            $total += (float) ($item['subtotal'] ?? 0);
        }

        $set($totalPath, $total);
    }

    /**
     * Configure the order form schema.
     *
     * @param Schema $schema The schema instance to configure
     * @return Schema The configured schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('total')->default(0),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->default(fn() => auth()->id())
                    ->required(),
                Select::make('status')
                ->label('Order Status')
                    ->options(OrderStatus::class)
                    ->default(OrderStatus::PENDING)
                    ->required(),
                Repeater::make('orderProducts')
                    ->hiddenLabel()
                    ->relationship('orderProducts')
                    ->dehydrated(true)
                    ->table([
                        TableColumn::make('Product'),
                        TableColumn::make('Quantity'),
                        TableColumn::make('Price'),
                        TableColumn::make('Subtotal'),
                    ])
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->searchDebounce(300)
                            ->loadingMessage('Loading products...')
                            ->noSearchResultsMessage('No products found.')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $product = Product::find($state);

                                if ($product) {
                                    $set('product_price', $product->price);
                                    $set('subtotal', $product->price);
                                }

                                self::handleTotal($get, $set);
                            }),
                        TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $productPrice = (float) $get('product_price');
                                $set('subtotal', $state * $productPrice);

                                self::handleTotal($get, $set);
                            }),
                        TextInput::make('product_price')
                            ->readOnly()
                            ->default(0)
                            ->numeric()
                            ->live(),

                        TextInput::make('subtotal')
                            ->readOnly()
                            ->default(0)
                            ->numeric()
                            ->live(),
                    ])
                    ->addActionLabel('New product')
                    ->columnSpan('full')
                    ->required()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        self::handleTotal($get, $set, true);
                    })

            ]);
    }
}
