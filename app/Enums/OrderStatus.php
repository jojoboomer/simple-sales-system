<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REFUNDED  = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending order',
            self::COMPLETED => 'Completed order',
            self::REFUNDED => 'Payment refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::REFUNDED => 'gray',
        };
    }
}
