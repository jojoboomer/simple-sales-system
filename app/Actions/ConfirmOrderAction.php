<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use DomainException;

class ConfirmOrderAction
{
    public function execute(Order $order): Order
    {
        if ($order->status === OrderStatus::COMPLETED) {
            throw new DomainException('Order is already completed');
        }

        $order->update(['status' => OrderStatus::COMPLETED]);

        return $order->fresh();
    }
}
