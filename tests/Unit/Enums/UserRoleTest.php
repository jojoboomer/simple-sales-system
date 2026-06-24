<?php

use App\Enums\UserRole;

test('user role enum has expected cases', function () {
    expect(UserRole::ADMIN->value)->toBe('admin');
    expect(UserRole::USER->value)->toBe('user');
});
