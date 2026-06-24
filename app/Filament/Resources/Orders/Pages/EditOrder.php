<?php


namespace App\Filament\Resources\Orders\Pages;

use App\Actions\UpdateOrderAction;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderProduct;
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
                    $".number_format((float) $total, 2).'
                </span>
            </div>
        ');
    }

    #[Override]
    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['orderProducts'] = $this->record->orderProducts
            ->map(fn (OrderProduct $item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product_price' => $item->product_price,
                'subtotal' => $item->subtotal,
            ])
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total'] = 0;

        return $data;
    }

    protected function afterSave(): void
    {
        try {
            $newItems = $this->form->getState()['orderProducts'] ?? [];
            app(UpdateOrderAction::class)->execute($this->record, $newItems);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error updating order')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
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
