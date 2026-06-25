<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStats extends StatsOverviewWidget
{
    protected function getHeading(): ?string
    {
        return 'Product Analytics';
    }

    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $outOfStockCount = Product::where('stock', 0)->count();

        return [
            Stat::make('Total Products', $totalProducts),
            Stat::make('Out of Stock', $outOfStockCount),
        ];
    }
}
