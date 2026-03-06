<?php

namespace Modules\Cart\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\Cart;
use Modules\Products\Models\Product;


class CartItemDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (range(1, 50) as $index) {
            $cart = Cart::inRandomOrder()->first('id');
            $product = Product::inRandomOrder()->first('id');
            $quantity = rand(1, 5);

            DB::table('cart_items')->insert([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price ?? 100,
                'total' => $product->price * $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
