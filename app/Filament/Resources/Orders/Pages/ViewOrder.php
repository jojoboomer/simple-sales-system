<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\ConfirmOrderAction;
use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

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
                ->label('Mark as complete')
                ->color('success')
                ->visible(fn ($record) => $record->status === OrderStatus::PENDING)
                ->requiresConfirmation()
                ->action(function ($record) {
                    app(ConfirmOrderAction::class)->execute($record);
                })
                ->successNotificationTitle('Order confirmed successfully')
                ->successRedirectUrl(fn () => route('filament.admin.resources.orders.index')),

        ];
    }
}
