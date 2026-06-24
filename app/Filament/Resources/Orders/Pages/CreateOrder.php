<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Override;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected static bool $canCreateAnother = false;

    protected ?bool $hasDatabaseTransactions = true;

    public string $status = 'pending';

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $this->status ?? OrderStatus::PENDING->value;
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            $order = $this->record;
            app(InventoryService::class)->checkProductStock($order);
            app(InventoryService::class)->calculateStock($order);
        } catch (\Exception $e) {
            $this->record = null;

            Notification::make()
                ->title('Error calculating stock')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw (new Halt())->rollBackDatabaseTransaction();
        }
    }

    #[Override]
    protected function getFormActions(): array
    {
        return [
            Action::make('save_as_completed')
                ->label('Save and Complete')
                ->color('primary')
                ->action(function () {
                    $this->status = OrderStatus::COMPLETED->value;
                    $this->create(another: false);
                }),

            Action::make('save_as_pending')
                ->label('Save as Pending')
                ->color('gray')
                ->action(function () {
                    $this->status = OrderStatus::PENDING->value;
                    $this->create(another: false);
                }),

            $this->getCancelFormAction(),
        ];
    }
}
