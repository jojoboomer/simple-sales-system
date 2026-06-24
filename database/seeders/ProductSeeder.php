<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()->create([
            'name' => 'Laptop',
            'description' => 'Description for Laptop',
            'price' => 800,
            'stock' => 10,
        ]);

        Product::factory()->create([
            'name' => 'Mouse',
            'description' => 'Description for Mouse',
            'price' => 25,
            'stock' => 5,
        ]);

        Product::factory()->create([
            'name' => 'Keyboard',
            'description' => 'Description for Keyboard',
            'price' => 30,
            'stock' => 3,
        ]);

        Product::factory()->create([
            'name' => 'Earphones',
            'description' => 'Description for Earphones',
            'price' => 15,
            'stock' => 20,
        ]);

        Product::factory()->create([
            'name' => 'Monitor',
            'description' => 'Description for Monitor',
            'price' => 150,
            'stock' => 0,
        ]);
    }
}
