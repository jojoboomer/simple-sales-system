<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{

    public function updateTotal(Order $order): void
    {
        $subtotal = $order->orderProducts->sum('subtotal');

        $total = $subtotal;

        $order->update([
            'total' => $total,
        ]);
    }

    /**
     * Validate stock before creating an order.
     */
    public function checkProductStock(Order $order): void
    {
        $order->loadMissing('orderProducts.product');

        foreach ($order->orderProducts as $item) {
            $product = $item->product;

            if ($product->stock < $item->quantity) {
                throw new \DomainException(
                    "Insufficient stock for product {$product->name}. Available: {$product->stock}, Requested: {$item->quantity}"
                );
            }
        }
    }

    /**
     * Calculate stock after creating an order.
     */
    public function calculateStock(Order $order): void
    {
        $order->loadMissing('orderProducts.product');

        foreach ($order->orderProducts as $item) {
            $product = $item->product;

            if ($product->stock < $item->quantity) {
                throw new \Exception("Insufficient stock for product {$product->name}. Available: {$product->stock}, Requested: {$item->quantity}");
            }

            $product->decrement('stock', $item->quantity);
        }
    }



    public function compareStock(array $currentData, array $newData): void
    {

        $productIds = array_unique(
            array_merge(array_keys($currentData), array_keys($newData))
        );

        foreach ($productIds as $productId) {

            $product = Product::find($productId);

            if (! $product) {
                continue; // Skip if the product doesn't exist
            }


            $oldQty = $currentData[$productId] ?? 0;
            $newQty = $newData[$productId] ?? 0;
            $diff = $newQty - $oldQty;

            if ($diff > 0 && $product->stock < $diff) {
                $max = $product->stock + $oldQty;

                throw new \DomainException(
                    "Insufficient stock for '{$product->name}'. Max allowed: {$max}, available stock: {$product->stock}"
                );
            }
        }
    }

    public function calculateStockAfterUpdate(array $currentData, array $newData): void
    {
        $allProductIds = array_unique(
            array_merge(array_keys($currentData), array_keys($newData))
        );

        foreach ($allProductIds as $productId) {

            $product = Product::find($productId);

            if (! $product) {
                continue;
            }

            $oldQty = $currentData[$productId] ?? 0;
            $newQty = $newData[$productId] ?? 0;

            $product->increment('stock', $oldQty);
            $product->decrement('stock', $newQty);
        }
    }
}
