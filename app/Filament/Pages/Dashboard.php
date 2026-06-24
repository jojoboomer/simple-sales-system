<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 1,
            'xl' => 3,
        ];
    }
}
