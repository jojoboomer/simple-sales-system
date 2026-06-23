<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;


class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('email', '!=', 'admin@example.com')
            ->get();

        $products = Product::all();

        Order::factory()
            ->count(15)
            ->make()
            ->each(function ($order) use ($users, $products) {

                $order->user_id = $users->random()->id;
                $order->status = fake()->randomElement(OrderStatus::class);
                $order->save();

                $total = 0;

                $selectedProducts = $products->random(
                    rand(1, min(5, $products->count()))
                );

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 5);
                    $subtotal = $product->price * $quantity;

                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'product_price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);

                    $total += $subtotal;
                }

                $order->update([
                    'total' => $total
                ]);
            });
    }
}
