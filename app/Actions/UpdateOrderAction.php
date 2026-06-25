<?php

namespace App\Actions;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateOrderAction
{
    public function execute(Order $order, array $newItems): Order
    {
        if (empty($newItems)) {
            throw new \InvalidArgumentException('Order must contain at least one product.');
        }

        $oldItems = $order->orderProducts->keyBy('product_id');

        $productIds = array_unique(
            array_merge(
                $oldItems->keys()->toArray(),
                array_column($newItems, 'product_id')
            )
        );

        $products = Product::findMany($productIds)->keyBy('id');

        return DB::transaction(function () use ($order, $newItems, $oldItems, $products) {
            $newTotal = 0;
            $processedProductIds = [];

            foreach ($newItems as $item) {
                $productId = $item['product_id'];
                $product = $products->get($productId);

                if (! $product) {
                    throw ProductNotFoundException::withId((string) $productId);
                }

                $oldQty = (int) ($oldItems->get($productId)?->quantity ?? 0);
                $newQty = (int) ($item['quantity'] ?? 0);

                $this->validateStockForUpdate($product, $oldQty, $newQty);

                $price = $product->price;
                $subtotal = OrderProduct::calculateSubtotal($price, $newQty);

                $order->orderProducts()->updateOrCreate(
                    ['product_id' => $productId],
                    [
                        'quantity' => $newQty,
                        'product_price' => $price,
                        'subtotal' => $subtotal,
                    ]
                );

                $this->updateProductStock($product, $oldQty, $newQty);

                $newTotal += $subtotal;
                $processedProductIds[] = $productId;
            }

            $this->removeDeletedItems($order, $oldItems, $processedProductIds, $products);

            $order->update(['total' => $newTotal]);

            return $order->fresh('orderProducts');
        });
    }

    private function validateStockForUpdate(Product $product, int $oldQty, int $newQty): void
    {
        $diff = $newQty - $oldQty;

        if ($diff > 0 && $product->stock < $diff) {
            throw InsufficientStockException::forProduct(
                $product->name, $product->stock, $diff
            );
        }
    }

    private function updateProductStock(Product $product, int $oldQty, int $newQty): void
    {
        $diff = $newQty - $oldQty;

        //$product->increment('stock', $oldQty);
        $product->decrement('stock', $diff);
    }

    private function removeDeletedItems(
        Order $order,
        Collection $oldItems,
        array $processedProductIds,
        Collection $products
    ): void {
        $removedProductIds = $oldItems->keys()->diff(collect($processedProductIds));

        foreach ($removedProductIds as $removedProductId) {
            $product = $products->get($removedProductId);
            if ($product) {
                $oldQty = (int) ($oldItems->get($removedProductId)?->quantity ?? 0);
                $product->increment('stock', $oldQty);
            }
            $order->orderProducts()->where('product_id', $removedProductId)->delete();
        }
    }
}
