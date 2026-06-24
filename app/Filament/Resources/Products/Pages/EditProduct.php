<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
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

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Product updated successfully';
    }

    #[Override]
    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');;
    }
}
