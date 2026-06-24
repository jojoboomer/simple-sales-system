<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'total', 'status'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => MoneyCast::class,
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'product_price', 'subtotal'])
            ->withTimestamps();
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->relationLoaded('orderProducts')
            ? $this->orderProducts->sum('quantity')
            : $this->orderProducts()->sum('quantity');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::REFUNDED);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
