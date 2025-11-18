<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        
        $query = Product::with(['category', 'disabledBy', 'suppliers']);

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'asc');
        
        $allowedSorts = ['id', 'name', 'quantity_in_stock', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('id', 'asc');
        }

        $products = $query->paginate(10);
        $categories = Category::all();
        $suppliers = Supplier::active()->get();

        return view('products.index', compact('products', 'categories', 'suppliers', 'showArchived', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::active()->get();
        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sku_suffix' => 'required|integer|min:1|max:99999|digits_between:1,5', 
                'name' => 'required|string|max:150',
                'description' => 'nullable|string|max:500',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'manufacturer_barcode' => 'nullable|string|max:30|unique:products,manufacturer_barcode',
                'reorder_level' => 'required|integer|min:0',
                'default_supplier_id' => 'required|exists:suppliers,id',
                'suppliers' => 'nullable|array',
                'suppliers.*.id' => 'nullable|exists:suppliers,id', 
                'suppliers.*.default_unit_cost' => 'nullable|numeric|min:0', 
            ]);
            
            $sku = Product::generateSku($request->category_id, $request->sku_suffix);

            $request->validate([
                'sku_suffix' => 'unique:products,sku',
            ], [
                'sku_suffix.unique' => 'This SKU suffix is already taken for the selected category. Please choose a different number.'
            ]);

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                // Create directory if it doesn't exist
                $directory = public_path('images/products');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
                
                // Move file to public directory
                $request->file('image')->move($directory, $filename);
                $imagePath = 'images/products/' . $filename;
            }

            // Create the product with mandatory supplier fields
            $product = Product::create([
                'sku' => $sku,
                'name' => ucfirst($request->name),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'image_path' => $imagePath,
                'manufacturer_barcode' => $request->manufacturer_barcode,
                'reorder_level' => $request->reorder_level,
                'default_supplier_id' => $request->default_supplier_id,
                'is_active' => true,
            ]);

            // Attach the default supplier first (mandatory)
            $product->suppliers()->attach($request->default_supplier_id, [
                'default_unit_cost' => $request->last_unit_cost
            ]);

            // Attach additional suppliers (optional)
            if ($request->suppliers) {
                foreach ($request->suppliers as $supplierData) {
                    if (!empty($supplierData['id']) && !empty($supplierData['default_unit_cost'])) {
                        // Skip if it's the same as default supplier (already attached)
                        if ($supplierData['id'] != $request->default_supplier_id) {
                            $product->suppliers()->attach($supplierData['id'], [
                                'default_unit_cost' => $supplierData['default_unit_cost']
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('products.index')->with('success', 'Product added successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'disabledBy', 'suppliers', 'saleItems']);
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $suppliers = Supplier::active()->get();
        $product->load('suppliers');
        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:150',
                'description' => 'nullable|string|max:500',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'manufacturer_barcode' => 'nullable|string|max:30|unique:products,manufacturer_barcode,' . $product->id,
                'reorder_level' => 'required|integer|min:0',
                'default_supplier_id' => 'required|exists:suppliers,id',
                'suppliers' => 'nullable|array',
                'suppliers.*.id' => 'nullable|exists:suppliers,id',
                'suppliers.*.default_unit_cost' => 'nullable|numeric|min:0',
            ]);

            // Handle image upload
            $imagePath = $product->image_path;
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($imagePath && file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
                
                // Create directory if it doesn't exist
                $directory = public_path('images/products');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
                
                // Move file to public directory
                $request->file('image')->move($directory, $filename);
                $imagePath = 'images/products/' . $filename;
            }

            $product->update([
                'name' => ucfirst($request->name),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'image_path' => $imagePath,
                'manufacturer_barcode' => $request->manufacturer_barcode,
                'reorder_level' => $request->reorder_level,
                'default_supplier_id' => $request->default_supplier_id,
            ]);

            // Sync suppliers - start with default supplier
            $suppliersData = [
                $request->default_supplier_id => [
                    'default_unit_cost' => $request->last_unit_cost
                ]
            ];

            // Add additional suppliers
            if ($request->suppliers) {
                foreach ($request->suppliers as $supplierData) {
                    if (!empty($supplierData['id']) && !empty($supplierData['default_unit_cost'])) {
                        $suppliersData[$supplierData['id']] = [
                            'default_unit_cost' => $supplierData['default_unit_cost']
                        ];
                    }
                }
            }

            $product->suppliers()->sync($suppliersData);

            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function archive(Product $product)
    {
        try {
            $currentUserId = session('user_id');

            $product->update([
                'is_active' => false,
                'date_disabled' => now(),
                'disabled_by_user_id' => $currentUserId,
            ]);

            return redirect()->route('products.index')->with('success', 'Product archived successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function restore(Product $product)
    {
        try {
            $product->update([
                'is_active' => true,
                'date_disabled' => null,
                'disabled_by_user_id' => null,
            ]);

            return redirect()->route('products.index', ['archived' => true])->with('success', 'Product restored successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index', ['archived' => true])->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function suggestSku($categoryId)
    {
        try {
            $suggestedSuffix = 1;
            
            $latestProduct = Product::where('sku', 'like', Category::find($categoryId)->sku_prefix . '-%')
                ->orderBy('sku', 'desc')
                ->first();

            if ($latestProduct) {
                $prefix = Category::find($categoryId)->sku_prefix;
                $lastSuffix = intval(substr($latestProduct->sku, strlen($prefix) + 1));
                $suggestedSuffix = $lastSuffix + 1;
            }

            return response()->json([
                'suggested_suffix' => $suggestedSuffix
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'suggested_suffix' => 1
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}