<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Database\Factories\OrderProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'product_id',
    'quantity',
    'product_price',
    'subtotal',
])]
class OrderProduct extends Model
{
    /** @use HasFactory<OrderProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'product_price' => MoneyCast::class,
            'subtotal' => MoneyCast::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function calculateSubtotal(float $price, int $quantity): float
    {
        return $price * $quantity;
    }
}
