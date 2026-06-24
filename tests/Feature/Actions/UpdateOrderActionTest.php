<?php


use App\Actions\CreateOrderAction;
use App\Actions\UpdateOrderAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('update order action adjusts stock when increasing quantity', function () {
    $product = Product::factory()->create(['stock' => 10, 'price' => 50]);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PENDING,
        'total' => 0,
    ]);

    app(CreateOrderAction::class)->execute($order, [
        ['product_id' => $product->id, 'quantity' => 2],
    ]);

    expect($product->fresh()->stock)->toBe(8);

    $updatedOrder = app(UpdateOrderAction::class)->execute($order, [
        ['product_id' => $product->id, 'quantity' => 5],
    ]);

    expect($updatedOrder->orderProducts->first()->quantity)->toBe(5);
    expect($updatedOrder->total)->toEqual(250.0);
    expect($product->fresh()->stock)->toBe(5);
});

test('update order action restores stock when removing a product', function () {
    $product1 = Product::factory()->create(['stock' => 10, 'price' => 50]);
    $product2 = Product::factory()->create(['stock' => 10, 'price' => 30]);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PENDING,
        'total' => 0,
    ]);

    app(CreateOrderAction::class)->execute($order, [
        ['product_id' => $product1->id, 'quantity' => 2],
        ['product_id' => $product2->id, 'quantity' => 3],
    ]);

    expect($product1->fresh()->stock)->toBe(8);
    expect($product2->fresh()->stock)->toBe(7);

    $updatedOrder = app(UpdateOrderAction::class)->execute($order, [
        ['product_id' => $product1->id, 'quantity' => 2],
    ]);

    expect($updatedOrder->orderProducts)->toHaveCount(1);
    expect($updatedOrder->total)->toEqual(100.0);
    expect($product2->fresh()->stock)->toBe(10);
});
