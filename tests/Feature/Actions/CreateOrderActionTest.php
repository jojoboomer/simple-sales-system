<?php

use App\Actions\CreateOrderAction;
use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('create order action creates products and decrements stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 50]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::COMPLETED,
        'total' => 0,
    ]);

    $result = app(CreateOrderAction::class)->execute($order, [
        ['product_id' => $product->id, 'quantity' => 3],
    ]);

    expect($result->orderProducts)->toHaveCount(1);
    expect($result->orderProducts->first()->quantity)->toBe(3);
    expect($result->orderProducts->first()->product_price)->toEqual(50.0);
    expect($result->orderProducts->first()->subtotal)->toEqual(150.0);
    expect($result->total)->toEqual(150.0);
    expect($product->fresh()->stock)->toBe(7);
});

test('create order action throws when stock is insufficient', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 1, 'price' => 50]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'total' => 0,
    ]);

    app(CreateOrderAction::class)->execute($order, [
        ['product_id' => $product->id, 'quantity' => 10],
    ]);
})->throws(InsufficientStockException::class);
