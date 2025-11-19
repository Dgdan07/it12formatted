<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockIn::with([
            'receivedBy' => function($q) {
                $q->withDefault([
                    'f_name' => 'Unknown',
                    'l_name' => 'User'
                ]);
            },
            'items.product',
            'items.supplier'
        ]);

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->search . '%')
                ->orWhereHas('items.product', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('items.supplier', function($q) use ($request) { 
                    $q->where('supplier_name', 'like', '%' . $request->search . '%');
                });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'stock_in_date');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['id', 'stock_in_date', 'reference_no', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('stock_in_date', 'desc');
        }

        $stockIns = $query->paginate(10);

        return view('stock-in.index', compact('stockIns', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::active()->get();
        $products = Product::active()->with('suppliers')->get(); 
        
        return view('stock-in.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('StockIn Store Request:', $request->all());
            
            // Validate the request structure
            $validated = $request->validate([
                'panels' => 'required|array',
                'panels.*.supplier_id' => 'required|exists:suppliers,id',
                'panels.*.reference_no' => 'required|string|max:255',
                'panels.*.stock_in_date' => 'required|date',
                'panels.*.received_by_user_id' => 'required|exists:users,id',
                'panels.*.items' => 'required|array|min:1',
                'panels.*.items.*.product_id' => 'required|exists:products,id',
                'panels.*.items.*.quantity_received' => 'required|integer|min:1',
                'panels.*.items.*.actual_unit_cost' => 'required|numeric|min:0',
                'panels.*.items.*.retail_price' => 'required|numeric|min:0',
            ]);
    
            Log::info('Validated data:', $validated);
    
            // Process each panel
            foreach ($validated['panels'] as $panelData) {
                Log::info('Processing panel:', $panelData);
                
                // Create stock in record
                $stockIn = StockIn::create([
                    'supplier_id' => $panelData['supplier_id'],
                    'reference_no' => $panelData['reference_no'],
                    'stock_in_date' => $panelData['stock_in_date'],
                    'received_by_user_id' => $panelData['received_by_user_id'],
                    'status' => 'completed',
                ]);
    
                // Process items
                foreach ($panelData['items'] as $itemData) {
                    Log::info('Processing item:', $itemData);
                    
                    // Create stock in item
                    StockInItem::create([
                        'stock_in_id' => $stockIn->id,
                        'product_id' => $itemData['product_id'],
                        'quantity_received' => $itemData['quantity_received'],
                        'actual_unit_cost' => $itemData['actual_unit_cost'],
                    ]);
    
                    // Update product stock
                    $product = Product::find($itemData['product_id']);
                    $product->increment('quantity_in_stock', $itemData['quantity_received']);
                    $product->update(['latest_unit_cost' => $itemData['actual_unit_cost']]);

                    $productPrice = \App\Models\ProductPrice::updateOrCreate(
                        ['product_id' => $itemData['product_id']],
                        [
                            'retail_price' => $itemData['retail_price'],
                            'stock_in_id' => $stockIn->id,
                            'updated_by_user_id' => $panelData['received_by_user_id']
                        ]
                    );
                }
            }
    
            Log::info('StockIn processed successfully');
    
            return response()->json([
                'success' => true,
                'message' => 'Stock In processed successfully',
                'data' => $validated
            ]);
    
        } catch (\Exception $e) {
            Log::error('StockIn Store Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
    
            return response()->json([
                'success' => false,
                'message' => 'Error processing stock in: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockIn $stockIn)
    {
        $stockIn->load(['receivedBy', 'items.supplier', 'items.product']);
        return response()->json($stockIn);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}