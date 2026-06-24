<?php

use App\Actions\ConfirmOrderAction;
use App\Actions\CreateOrderAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('confirm order action changes status to completed', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 50]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'total' => 0,
    ]);

    app(CreateOrderAction::class)->execute($order, [
        ['product_id' => $product->id, 'quantity' => 2],
    ]);

    $result = app(ConfirmOrderAction::class)->execute($order);

    expect($result->status)->toBe(OrderStatus::COMPLETED);
});

test('confirm order action throws when already completed', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 50]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::COMPLETED,
        'total' => 0,
    ]);

    app(ConfirmOrderAction::class)->execute($order);
})->throws(DomainException::class, 'Order is already completed');
