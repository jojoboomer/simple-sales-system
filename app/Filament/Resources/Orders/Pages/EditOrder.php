<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    #[Override]
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save as Pending')
                ->color('warning')
                ->action(fn() => app(OrderService::class)->create($this->form->getState())),

            $this->getSaveFormAction()
                ->label('Confirm order')
                ->color('success')
                ->action(fn() => app(OrderService::class)->create([...$this->form->getState(), 'status' => OrderStatus::COMPLETED])),

            $this->getCancelFormAction(),
        ];
    }
}
