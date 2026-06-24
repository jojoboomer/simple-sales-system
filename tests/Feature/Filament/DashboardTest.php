<?php

use App\Filament\Pages\Dashboard;
use App\Models\User;

test('admin can access the dashboard', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    expect(Dashboard::canAccess())->toBeTrue();
});

test('non-admin cannot access the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(Dashboard::canAccess())->toBeFalse();
});
