<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{

    /**
     * Confirm an order
     */
    public function confirm(Order $order): Order
    {
        if ($order->status === OrderStatus::COMPLETED) {
            throw new \Exception('Order already confirmed');
        }

        return DB::transaction(function () use ($order) {

            $order->update([
                'status' => OrderStatus::COMPLETED,
            ]);

            return $order;
        });
    }
}
