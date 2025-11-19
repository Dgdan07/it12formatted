<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class POSController extends Controller
{
    /**
     * Show the POS page
     */
    public function index()
    {
        return view('pos.index');
    }

    /**
     * Search products by name, SKU, or barcode
     */
    public function searchProduct(Request $request)
{
    try {
        $searchTerm = $request->input('search_term');

        $products = Product::with('latestProductPrice')
            ->where('is_active', true)
            ->where('quantity_in_stock', '>', 0)
            ->whereHas('latestProductPrice') // <-- ensures product has at least one price
            ->where(function($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                      ->orWhere('manufacturer_barcode', 'like', '%' . $searchTerm . '%');
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);

    } catch (\Exception $e) {
        // Return JSON with the error
        return response()->json([
            'success' => false,
            'products' => [],
            'message' => $e->getMessage()
        ], 500);
    }
}

    

    /**
     * Complete the sale: create Sale, SaleItems, and Payment in DB
     */
    public function completeSale(Request $request)
    {
        try {
            DB::beginTransaction();

            $items = $request->input('items'); // frontend sends full cart
            $paymentMethod = $request->input('payment_method');
            $amountTendered = $request->input('amount_tendered');
            $referenceNo = $request->input('reference_no');
            $customerName = $request->input('customer_name');
            $customerContact = $request->input('customer_contact');

            if (empty($items)) {
                throw new Exception('Cart is empty.');
            }

            // Calculate total
            $total = 0;
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product']['id']);
                $qty = $item['quantity_sold'];
                $price = $product->latestProductPrice->retail_price;

                if ($product->quantity_in_stock < $qty) {
                    throw new Exception("Insufficient stock for {$product->name}");
                }

                $total += $qty * $price;
            }

            // Validate payment
            if ($paymentMethod === 'Cash') {
                if ($amountTendered < $total) {
                    throw new Exception('Amount tendered must be greater than or equal to total.');
                }
                $change = $amountTendered - $total;
            } else {
                // GCash or Card
                if ($amountTendered != $total) {
                    throw new Exception('Amount tendered must equal total for ' . $paymentMethod);
                }
                if (empty($referenceNo)) {
                    throw new Exception('Reference number is required for ' . $paymentMethod);
                }
                $change = 0;
            }

            // Create Sale
            $sale = Sale::create([
                'user_id' => session('user_id'),
                'sale_date' => now(),
                'customer_name' => $customerName,
                'customer_contact' => $customerContact
            ]);

            // Create SaleItems & update stock
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product']['id']);
                $qty = $item['quantity_sold'];
                $price = $product->latestProductPrice->retail_price;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity_sold' => $qty,
                    'unit_price' => $price
                ]);

                $product->decrement('quantity_in_stock', $qty);
            }

            // Create Payment
            Payment::create([
                'sale_id' => $sale->id,
                'payment_date' => now(),
                'payment_method' => $paymentMethod,
                'amount_tendered' => $amountTendered,
                'change_given' => $change,
                'reference_no' => $paymentMethod === 'Cash' ? null : $referenceNo
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'sale' => $sale->load('items.product', 'payment'),
                'change' => $change,
                'message' => 'Sale completed successfully'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Download receipt PDF
     */
    public function downloadReceiptPDF(Sale $sale)
    {
        $sale->load(['items.product', 'payment', 'user']);

        if (!$sale->payment) {
            abort(404, "Payment not found.");
        }

        return Pdf::loadView('pos.receipt', compact('sale'))
                  ->download("receipt-{$sale->id}.pdf");
    }
}
