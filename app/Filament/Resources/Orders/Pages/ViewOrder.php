<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->color('gray')
                ->url(OrderResource::getUrl('index')),
            EditAction::make(),
            Action::make('confirm')
                ->label('Confirm Order')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn($record) => $record->status === OrderStatus::PENDING)
                ->requiresConfirmation()
                ->action(function ($record) {
                    app(OrderService::class)->confirm($record);
                }),

        ];
    }
}
