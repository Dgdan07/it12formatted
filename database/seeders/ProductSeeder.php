<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now(); 

        // 1. Get Category IDs and Prefixes (Assumes CategorySeeder ran first)
        $categories = DB::table('categories')->pluck('id', 'sku_prefix')->toArray();

        // 2. Get Supplier IDs (Assumes SupplierSeeder ran first)
        $default_supplier_id = 1;

        $products = [
            // --- FASTENERS (FSTNR - ID 1) ---
            [
                'sku' => 'FSTNR-00001',
                'name' => 'Wood Screw 1 inch (per piece)',
                'description' => 'Flat head wood screws, 1-inch length, zinc coating.',
                'category_id' => $categories['FSTNR'],
                'manufacturer_barcode' => null,
                'default_supplier_id' => $default_supplier_id, 
                'quantity_in_stock' => 0, // Will be updated by stock-in
                'reorder_level' => 1000,
            ],
            [
                'sku' => 'FSTNR-00002',
                'name' => 'Hex Bolt M6 x 20mm',
                'description' => 'Standard M6 hexagonal bolt, 20mm length.',
                'category_id' => $categories['FSTNR'],
                'manufacturer_barcode' => null,
                'default_supplier_id' => $default_supplier_id, 
                'quantity_in_stock' => 0, // Will be updated by stock-in
                'reorder_level' => 500,
            ],

            // --- ELECTRICAL (ELEC - ID 5) ---
            [
                'sku' => 'ELEC-00001',
                'name' => 'Electrical Wire 14 AWG (per meter)',
                'description' => 'Solid copper 14 gauge electrical wire, sold by the meter.',
                'category_id' => $categories['ELEC'],
                'manufacturer_barcode' => null,
                'default_supplier_id' => $default_supplier_id, 
                'quantity_in_stock' => 0, // Will be updated by stock-in
                'reorder_level' => 100,
            ],
            [
                'sku' => 'ELEC-00002',
                'name' => 'Wall Outlet Switch (Single)',
                'description' => 'Basic single gang wall outlet switch, white.',
                'category_id' => $categories['ELEC'],
                'manufacturer_barcode' => null,
                'default_supplier_id' => $default_supplier_id, 
                'quantity_in_stock' => 0, // Will be updated by stock-in
                'reorder_level' => 20,
            ],
            
            // --- HAND TOOLS (HNDTL - ID 2) ---
            [
                'sku' => 'HNDTL-00001',
                'name' => 'Measuring Tape 5 Meter',
                'description' => 'Retractable steel measuring tape, 5 meter length.',
                'category_id' => $categories['HNDTL'],
                'manufacturer_barcode' => null,
                'default_supplier_id' => $default_supplier_id, 
                'quantity_in_stock' => 0, // Will be updated by stock-in
                'reorder_level' => 10,
            ],
        ];

        // Add timestamps and default fields
        $products = array_map(function ($product) use ($now) {
            $product['image_path'] = null;
            $product['is_active'] = true;
            $product['date_disabled'] = null;
            $product['disabled_by_user_id'] = null;
            $product['created_at'] = $now;
            $product['updated_at'] = $now;
            return $product;
        }, $products);

        DB::table('products')->insert($products);

        // Now create stock-in records for these products
        $this->createStockInRecords();
    }

    /**
     * Create stock-in records for the seeded products
     */
    private function createStockInRecords(): void
    {
        $now = Carbon::now();
        $default_supplier_id = 1;
        $received_by_user_id = 1; // Assuming user ID 1 exists

        // Get all product IDs we just created
        $productIds = DB::table('products')->pluck('id', 'sku')->toArray();

        // Define stock-in data with unit costs and retail prices
        $stockInData = [
            // Product data: [quantity, unit_cost, retail_price]
            'FSTNR-00001' => [5000, 0.75, 1.50],    // Wood Screw
            'FSTNR-00002' => [2000, 2.50, 5.00],    // Hex Bolt
            'ELEC-00001'  => [500, 18.00, 36.00],   // Electrical Wire
            'ELEC-00002'  => [200, 60.00, 120.00],  // Wall Outlet Switch
            'HNDTL-00001' => [100, 125.00, 250.00], // Measuring Tape
        ];

        // Create stock-in header
        $stockInId = DB::table('stock_ins')->insertGetId([
            'stock_in_date' => $now,
            'reference_no' => 'SEED-001',
            'received_by_user_id' => $received_by_user_id,
            'status' => 'completed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create stock-in items and product prices
        foreach ($stockInData as $sku => $data) {
            list($quantity, $unitCost, $retailPrice) = $data;
            
            $productId = $productIds[$sku];

            // Create stock-in item
            DB::table('stock_in_items')->insert([
                'stock_in_id' => $stockInId,
                'product_id' => $productId,
                'supplier_id' => $default_supplier_id,  
                'quantity_received' => $quantity,
                'actual_unit_cost' => $unitCost,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create product price
            DB::table('product_prices')->insert([
                'product_id' => $productId,
                'retail_price' => $retailPrice,
                'stock_in_id' => $stockInId,
                'updated_by_user_id' => $received_by_user_id, 
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Update product quantity_in_stock
            DB::table('products')
                ->where('id', $productId)
                ->update([
                    'quantity_in_stock' => $quantity,
                    'latest_unit_cost' => $unitCost, 
                    'updated_at' => $now,
                ]);
        }
    }
}