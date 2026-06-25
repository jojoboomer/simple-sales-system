<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\CreateOrderAction;
use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\HtmlString;
use Override;

class CreateOrder extends CreateRecord
{
    protected static ?string $title = 'Create Order';

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
        $formatted = e(number_format((float) $total, 2));

        return new HtmlString("
            <div class='flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 mt-1'>
                <span>Total amount:</span>
                <span class='text-base font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/50 px-2.5 py-0.5 rounded-full ring-1 ring-primary-600/10'>
                    \${$formatted}
                </span>
            </div>
        ");
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $this->status ?? OrderStatus::PENDING->value;
        $data['user_id'] = auth()->id();
        $data['total'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            $items = $this->form->getState()['orderProducts'] ?? [];
            app(CreateOrderAction::class)->execute($this->record, $items);
        } catch (\Throwable $e) {
            $this->record = null;

            Notification::make()
                ->title('Error creating order')
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
