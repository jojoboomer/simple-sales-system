<?php

namespace App\Filament\Widgets;

use App\Models\Product as ModelsProduct;
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
        $lowStockCount = ModelsProduct::where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $outOfStockCount = ModelsProduct::where('stock', 0)->count();

        return [
            Stat::make('Total Products', ModelsProduct::count()),
            Stat::make('Out of Stock', "{$outOfStockCount}"),
        ];
    }
}
