<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {

            $order = Order::create([
                'user_id' =>  $data['user_id'] ?? auth()->id(),
                'status' => $data['status'] ?? OrderStatus::PENDING,
            ]);

            $this->attachProducts($order, $data['products']);

            $this->recalculateTotals($order);

            return $order;
        });
    }

    public function confirm(Order $order): Order
    {
        if ($order->status === OrderStatus::COMPLETED) {
            throw new \Exception('Order already confirmed');
        }

        return DB::transaction(function () use ($order) {

            $order->update([
                'status' => OrderStatus::COMPLETED,
            ]);

            // opcional: lock precios o recalcular final
            $this->recalculateTotals($order);

            return $order;
        });
    }

    public function update(Order $order, array $data): Order
    {
        if ($order->status === OrderStatus::COMPLETED) {
            throw new \Exception('Cannot edit confirmed order');
        }

        return DB::transaction(function () use ($order, $data) {

            $order->update($data);

            $order->orderProducts()->delete();

            $this->attachProducts($order, $data['products']);

            $this->recalculateTotals($order);

            return $order;
        });
    }

    /**
     * Agregar productos a la orden
     */
    public function attachProducts(Order $order, array $products): void
    {
        foreach ($products as $item) {

            $product = Product::findOrFail($item['product_id']);

            $quantity = $item['quantity'];

            $unitPrice = $product->price;

            $order->orderProducts()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'product_price' => $unitPrice,
                'subtotal' => $unitPrice * $quantity,
            ]);
        }
    }

    /**
     * Recalcular totales de la orden
     */
    public function recalculateTotals(Order $order): void
    {
        $subtotal = $order->orderProducts->sum('subtotal');

        $total = $subtotal;

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }
}
