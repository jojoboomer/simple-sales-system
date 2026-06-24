<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Orders extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getHeading(): ?string
    {
        return 'Order Analytics';
    }

    protected function getStats(): array
    {
        return [
            Stat::make(' Orders of Today ', Order::whereDate('created_at', Carbon::today())->count()),
            Stat::make('Orders not Processed', Order::where('status', OrderStatus::PENDING)->count()),
            Stat::make('Today\'s Revenue', '$'.number_format(Order::whereDate('created_at', Carbon::today())->sum('total'), 2))
                ->description('Revenue generated today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([12, 15, 18, 14, 22, 25, 30])
                ->color('success'),
        ];
    }
}
