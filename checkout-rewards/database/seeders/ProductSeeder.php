<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Mouse', 'price_cents' => 1299, 'stock' => 50],
            ['name' => 'Mechanical Keyboard', 'price_cents' => 5999, 'stock' => 20],
            ['name' => 'USB-C Cable', 'price_cents' => 799, 'stock' => 100],
            ['name' => 'Laptop Stand', 'price_cents' => 2499, 'stock' => 10],
            ['name' => 'Webcam', 'price_cents' => 3999, 'stock' => 2],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                [
                    'price_cents' => $product['price_cents'],
                    'stock' => $product['stock'],
                ]
            );
        }
    }
}
