<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static ?string $title = 'Create Product';

    protected static string $resource = ProductResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Product created successfully';
    }
}
