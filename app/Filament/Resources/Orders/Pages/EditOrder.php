<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\HtmlString;
use Override;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    public string $status = 'pending';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Order updated successfully';
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

    protected array $oldItemsData = [];

    protected function beforeSave(): void
    {
        $this->oldItemsData = $this->record
            ->orderProducts
            ->pluck('quantity', 'product_id')
            ->toArray();

        $newItems = collect($this->form->getState()['orderProducts'] ?? [])
            ->pluck('quantity', 'product_id')
            ->toArray();

        try {
            app(OrderService::class)
                ->compareStock($this->oldItemsData, $newItems);
        } catch (\DomainException $e) {

            Notification::make()
                ->title('Stock validation failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw (new Halt())->rollBackDatabaseTransaction();
        }
    }

    protected function afterSave(): void
    {
        try {
            $order = $this->record->fresh('orderProducts');
            $newItems = $order->orderProducts
                ->pluck('quantity', 'product_id')
                ->toArray();

            app(OrderService::class)
                ->calculateStockAfterUpdate($this->oldItemsData, $newItems);
        } catch (\Exception $e) {

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
            $this->getSaveFormAction()
                ->label('Save Changes')
                ->color('primary'),
            $this->getCancelFormAction(),
        ];
    }
}
