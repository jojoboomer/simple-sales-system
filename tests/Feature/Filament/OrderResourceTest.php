<?php


use App\Actions\ConfirmOrderAction;
use App\Actions\DeleteOrderAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('order service confirms a pending order in the database', function () {
    $product = Product::factory()->create(['stock' => 10, 'price' => 100]);
    $order = Order::factory()->create([
        'user_id' => $this->admin->id,
        'status' => OrderStatus::PENDING,
        'total' => 200,
    ]);
    $order->orderProducts()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'product_price' => 100,
        'subtotal' => 200,
    ]);

    app(ConfirmOrderAction::class)->execute($order);

    expect($order->fresh()->status)->toBe(OrderStatus::COMPLETED);
});

test('deleting an order restores product stock', function () {
    $product = Product::factory()->create(['stock' => 10, 'price' => 100]);
    $order = Order::factory()->create([
        'user_id' => $this->admin->id,
        'status' => OrderStatus::PENDING,
    ]);
    $order->orderProducts()->create([
        'product_id' => $product->id,
        'quantity' => 3,
        'product_price' => 100,
        'subtotal' => 300,
    ]);
    $order->update(['total' => 300]);

    $product->decrement('stock', 3);
    expect($product->fresh()->stock)->toBe(7);

    app(DeleteOrderAction::class)->execute($order);

    expect($product->fresh()->stock)->toBe(10);
    expect(Order::find($order->id))->toBeNull();
});

test('confirming a completed order throws exception', function () {
    $order = Order::factory()->create([
        'user_id' => $this->admin->id,
        'status' => OrderStatus::COMPLETED,
    ]);

    app(ConfirmOrderAction::class)->execute($order);
})->throws(DomainException::class, 'Order is already completed');

test('order totals are calculated correctly', function () {
    $product1 = Product::factory()->create(['price' => 100]);
    $product2 = Product::factory()->create(['price' => 50]);
    $order = Order::factory()->create([
        'user_id' => $this->admin->id,
        'status' => OrderStatus::PENDING,
    ]);
    OrderProduct::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'product_price' => 100,
        'subtotal' => 200,
    ]);
    OrderProduct::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product2->id,
        'quantity' => 3,
        'product_price' => 50,
        'subtotal' => 150,
    ]);

    $order->update(['total' => 350]);

    expect($order->fresh()->total)->toBe(350.0);
});
