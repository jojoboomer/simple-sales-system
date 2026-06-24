<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Facades\FilamentView;
use Override;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class CreateOrder extends CreateRecord
{

    protected static string $resource = OrderResource::class;

    protected static bool $canCreateAnother = false;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Order created successfully';
    }

    public function getSubheading(): ?HtmlString
    {
        $total = $this->data['total'] ?? 0;

        return new HtmlString("
            <div class='flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 mt-1'>
                <span>Total amount:</span>
                <span class='text-base font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/50 px-2.5 py-0.5 rounded-full ring-1 ring-primary-600/10'>
                    $" . number_format((float) $total, 2) . "
                </span>
            </div>
        ");
    }

    #[Override]
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Save as Pending')
                ->color('warning')
                ->action(fn() => app(OrderService::class)->create($this->form->getState())),

            $this->getCreateFormAction()
                ->label('Confirm order')
                ->color('success')
                ->action(fn() => app(OrderService::class)->create([...$this->form->getState(), 'status' => OrderStatus::COMPLETED])),

            $this->getCancelFormAction(),
        ];
    }
}
