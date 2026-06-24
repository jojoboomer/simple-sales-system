<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DeleteOrderAction
{
    public function execute(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('orderProducts');

            $this->restoreStock($order);

            $order->orderProducts()->delete();
            $order->delete();
        });
    }

    private function restoreStock(Order $order): void
    {
        $productIds = $order->orderProducts->pluck('product_id')->toArray();
        $products = Product::findMany($productIds)->keyBy('id');

        foreach ($order->orderProducts as $item) {
            $product = $products->get($item->product_id);
            if ($product) {
                $product->increment('stock', $item->quantity);
            }
        }
    }
}
