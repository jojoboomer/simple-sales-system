<?php

namespace App\Actions;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function execute(Order $order, array $items): Order
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('Order must contain at least one product.');
        }

        $productIds = array_unique(array_column($items, 'product_id'));
        $products = Product::findMany($productIds)->keyBy('id');

        return DB::transaction(function () use ($order, $items, $products) {
            $total = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (! $product) {
                    throw InsufficientStockException::forProduct(
                        "Unknown (ID: {$item['product_id']})", 0, $item['quantity']
                    );
                }

                $this->validateStock($product, (int) $item['quantity']);

                $price = $product->price;
                $quantity = (int) $item['quantity'];
                $subtotal = OrderProduct::calculateSubtotal($price, $quantity);

                $order->orderProducts()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'product_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;

                $product->decrement('stock', $quantity);
            }

            $order->update(['total' => $total]);

            return $order->fresh('orderProducts');
        });
    }

    private function validateStock(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw InsufficientStockException::forProduct(
                $product->name, $product->stock, $quantity
            );
        }
    }
}
