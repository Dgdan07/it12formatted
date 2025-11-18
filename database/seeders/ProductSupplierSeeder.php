<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get all products
        $products = DB::table('products')->get();

        $productSuppliers = [];

        foreach ($products as $product) {
            // Always attach the default supplier (which is Supplier ID 1 for all products)
            $productSuppliers[] = [
                'product_id' => $product->id,
                'supplier_id' => $product->default_supplier_id, // This is 1 for all products
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_suppliers')->insert($productSuppliers);
    }
}
