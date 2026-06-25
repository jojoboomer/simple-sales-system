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
        $todayOrders = Order::whereDate('created_at', Carbon::today())->count();
        $pendingOrders = Order::where('status', OrderStatus::PENDING)->count();
        $todayRevenue = Order::whereDate('created_at', Carbon::today())->sum('total');

        return [
            Stat::make('Orders of Today', $todayOrders),
            Stat::make('Orders not Processed', $pendingOrders),
            Stat::make('Today\'s Revenue', '$'.number_format($todayRevenue, 2))
                ->description('Revenue generated today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
