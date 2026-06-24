<?php


use App\Filament\Pages\Auth\LoginResponse;
use App\Models\User;
use Illuminate\Http\Request;

test('non-admin user is redirected to orders after login', function () {
    $user = User::factory()->create();
    $request = Request::create('/admin/login', 'POST');

    $this->actingAs($user);
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response->getTargetUrl())->toContain('/admin/orders');
});

test('admin user is redirected to intended filament url after login', function () {
    $admin = User::factory()->admin()->create();
    $request = Request::create('/admin/login', 'POST');

    $this->actingAs($admin);
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response->getTargetUrl())->toContain('/admin');
});
