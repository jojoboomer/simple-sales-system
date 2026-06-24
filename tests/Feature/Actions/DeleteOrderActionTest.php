<?php


use App\Actions\CreateOrderAction;
use App\Actions\DeleteOrderAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('delete order action restores stock and removes order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 50]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING,
        'total' => 0,
    ]);

    app(CreateOrderAction::class)->execute($order, [
        ['product_id' => $product->id, 'quantity' => 3],
    ]);

    expect($product->fresh()->stock)->toBe(7);

    app(DeleteOrderAction::class)->execute($order);

    expect($product->fresh()->stock)->toBe(10);
    expect(Order::find($order->id))->toBeNull();
    expect($order->orderProducts()->count())->toBe(0);
});
