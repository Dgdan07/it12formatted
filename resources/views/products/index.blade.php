@extends('layouts.app')
@section('title', 'Products - ATIN Admin')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    .low-stock {
        background-color: #fff3cd !important;
    }
    .out-of-stock {
        background-color: #f8d7da !important;
    }
</style>
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Products</b>
                @if($showArchived)
                    <span class="text-secondary small ms-2">(Archive View)</span>
                @endif
            </h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Add New Product
            </a>
        </div>
    </div>
    

    <!-- Search & Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <form action="{{ route('products.index') }}" method="GET" class="d-flex flex-grow-1 me-2">
                            @if($showArchived)
                                <input type="hidden" name="archived" value="true">
                            @endif
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <input type="hidden" name="direction" value="{{ $direction }}">
                            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                            <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            <div class="input-group search-box w-100">
                                <input type="text" class="form-control" name="search" placeholder="Search products..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        @if(request('search'))
                            @if($showArchived)
                                <a href="{{ route('products.index', ['archived' => true]) }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                            @else
                                <a href="{{ route('products.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                            @endif
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-2">
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['category_id' => null]) }}">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ request()->fullUrlWithQuery(['category_id' => $category->id]) }}" 
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Range -->
                <div class="col-md-3">
                    <form action="{{ route('products.index') }}" method="GET" class="d-flex gap-2 bg-light p-2 rounded">
                        @if($showArchived)
                            <input type="hidden" name="archived" value="true">
                        @endif
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <input type="hidden" name="direction" value="{{ $direction }}">
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        
                        <input type="number" class="form-control" name="min_price" placeholder="Min price" 
                            value="{{ request('min_price') }}" step="0.01" min="0">
                        <input type="number" class="form-control" name="max_price" placeholder="Max price" 
                            value="{{ request('max_price') }}" step="0.01" min="0">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-filter"></i>
                        </button>
                    </form>
                </div>

                <!-- Archive Toggle & Sort -->
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-between">
                        <!-- Sort Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-sort-down me-1"></i>Sort
                                @if($sort)
                                    <small class="ms-1">({{ $direction == 'asc' ? '↑' : '↓' }})</small>
                                @endif
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item {{ $sort == 'name' ? 'active' : '' }}" 
                                    href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $sort == 'name' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Name @if($sort == 'name') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'price' ? 'active' : '' }}" 
                                    href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => $sort == 'price' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Price @if($sort == 'price') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'quantity_in_stock' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'quantity_in_stock', 'direction' => $sort == 'quantity_in_stock' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Stock @if($sort == 'quantity_in_stock') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                            </ul>
                        </div>
                        <!-- Archive Toggle -->
                        @if($showArchived)
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back to Active
                            </a>
                        @else
                            <a href="{{ route('products.index', ['archived' => true]) }}" class="btn btn-outline-warning">
                                <i class="bi bi-archive me-1"></i>
                                View Archive
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Products Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count and Current Sort -->
            <div class="text-muted">
                @if(request('search'))
                    Displaying {{ $products->count() }} of {{ $products->total() }} results for "{{ request('search') }}"
                    @if(request('category_id'))
                        in {{ $categories->where('id', request('category_id'))->first()->name ?? 'Unknown' }} category
                    @endif
                    @if(request('min_price') || request('max_price'))
                        (Price: 
                        @if(request('min_price'))₱{{ number_format(request('min_price'), 2) }}@endif
                        @if(request('min_price') && request('max_price')) - @endif
                        @if(request('max_price'))₱{{ number_format(request('max_price'), 2) }}@endif
                        )
                    @endif
                @else
                    Displaying {{ $products->count() }} of {{ $products->total() }} {{ $showArchived ? 'archived' : 'active' }} products
                    @if(request('category_id'))
                        in {{ $categories->where('id', request('category_id'))->first()->name ?? 'Unknown' }} category
                    @endif
                    @if(request('min_price') || request('max_price'))
                        (Price range: 
                        @if(request('min_price'))₱{{ number_format(request('min_price'), 2) }}@endif
                        @if(request('min_price') && request('max_price')) - @endif
                        @if(request('max_price'))₱{{ number_format(request('max_price'), 2) }}@endif
                        )
                    @endif
                @endif
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Suppliers</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="{{ $product->quantity_in_stock == 0 ? 'out-of-stock' : ($product->quantity_in_stock <= $product->reorder_level ? 'low-stock' : '') }}">
                        <td><code>{{ $product->sku }}</code></td>
                        <td>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                        </td>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            @if($product->manufacturer_barcode)
                                <br><small class="text-muted">{{ $product->manufacturer_barcode }}</small>
                            @endif
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td>₱{{ number_format($product->price, 2) }}</td>
                        <td>
                            <span class="text-{{ $product->quantity_in_stock == 0 ? 'danger' : ($product->quantity_in_stock <= $product->reorder_level ? 'danger' : 'success') }} fw-semibold">
                                {{ $product->quantity_in_stock }}
                            </span>
                        
                            @if($product->quantity_in_stock <= $product->reorder_level)
                                <br>
                                <small class="text-danger fw-bold">
                                    LOW STOCK
                                </small>
                            @endif
                        </td>                        
                        <td>
                            <span>
                                {{ $product->suppliers->count() }} supplier{{ $product->suppliers->count() != 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td>{{ $product->updated_at->format('Y-m-d') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-product" data-id="{{ $product->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($product->is_active)
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action archive-product" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->name }}"
                                        data-has-sales="{{ $product->hasSales() ? 'true' : 'false' }}"
                                        title="Archive">
                                    <i class="bi bi-archive"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-success btn-action restore-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Restore">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            No {{ $showArchived ? 'archived' : 'active' }} products found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- View Product Modal -->
    <div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewProductModalLabel">
                        <i class="bi bi-box me-2"></i>
                        Product Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="viewProductImage" src="" alt="Product Image" class="img-fluid rounded mb-3" style="max-height: 200px;">
                        </div>
                        <div class="col-md-8">
                            <!-- Compact grid layout -->
                            <div class="row g-2">
                                <div class="col-5">
                                    <small class="text-muted">Product Name:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-break" id="viewProductName"></span>
                                </div>

                                <div class="col-5">
                                    <small class="text-muted">SKU:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-break" id="viewProductSku"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Description:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-break" id="viewProductDescription">N/A</span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Category:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductCategory"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Barcode:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductBarcode">N/A</span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Price:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductPrice"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Stock:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductStock"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Reorder Level:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductReorder"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Last Unit Cost:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewProductCost"></span>
                                </div>
                                
                                <div class="col-5">
                                    <small class="text-muted">Primary Supplier:</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold" id="viewPrimarySupplier">N/A</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suppliers Section -->
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2">
                            <i class="bi bi-truck me-2"></i>
                            Suppliers (<span id="viewSuppliersCount">0</span>)
                        </h6>
                        <div id="viewSuppliersList" class="mt-2">
                            <!-- Suppliers will be populated here -->
                        </div>
                    </div>

                    <!-- Archive Info -->
                    <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded" id="archiveInfo" style="display: none;">
                        <small class="text-muted d-block">Archive Information</small>
                        <div class="row g-2">
                            <div class="col-3">
                                <small>Date:</small>
                            </div>
                            <div class="col-9">
                                <small class="fw-semibold" id="viewDateDisabled"></small>
                            </div>
                            <div class="col-3">
                                <small>By:</small>
                            </div>
                            <div class="col-9">
                                <small class="fw-semibold" id="viewDisabledBy"></small>
                            </div>
                        </div>
                    </div>               
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal fade" id="archiveProductModal" tabindex="-1" aria-labelledby="archiveProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="archiveProductForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="archiveProductModalLabel">
                            <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                            Confirm Archive
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-archive text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to archive this product?</h5>
                            <p class="text-muted">Product: <strong id="archiveProductName"></strong></p>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Archived products will be hidden from active lists but their data is preserved.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="archiveSubmitBtn">Archive Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreProductModal" tabindex="-1" aria-labelledby="restoreProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="restoreProductForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="restoreProductModalLabel">
                            <i class="bi bi-arrow-clockwise me-2 text-success"></i>
                            Confirm Restore
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-arrow-clockwise text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to restore this product?</h5>
                            <p class="text-muted">Product: <strong id="restoreProductName"></strong></p>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                The product will be visible in active lists again.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Restore Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // View Product
        document.querySelectorAll('.view-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch(`/products/${productId}`)
                    .then(response => response.json())
                    .then(product => {                           
                        document.getElementById('viewProductName').textContent = product.name;
                        document.getElementById('viewProductSku').textContent = product.sku;
                        document.getElementById('viewProductDescription').textContent = product.description || 'N/A';
                        document.getElementById('viewProductCategory').textContent = product.category.name;
                        document.getElementById('viewProductBarcode').textContent = product.manufacturer_barcode || 'N/A';
                        document.getElementById('viewProductPrice').textContent = '₱' + parseFloat(product.price).toFixed(2);
                        document.getElementById('viewProductStock').textContent = product.quantity_in_stock;
                        document.getElementById('viewProductReorder').textContent = product.reorder_level;
                        document.getElementById('viewProductCost').textContent = '₱' + parseFloat(product.last_unit_cost).toFixed(2);
                        
                        const imageElement = document.getElementById('viewProductImage');
                        if (product.image_url) {
                            imageElement.src = product.image_url;
                            imageElement.alt = product.name;
                            imageElement.style.display = 'block';
                        } else {
                            imageElement.src = '/images/no-image.jpg';
                            imageElement.alt = 'No image available';
                        }
                        
                        // Handle suppliers display
                        const suppliersList = document.getElementById('viewSuppliersList');
                        const suppliersCount = document.getElementById('viewSuppliersCount');
                        const primarySupplier = document.getElementById('viewPrimarySupplier');
                        
                        if (product.suppliers && product.suppliers.length > 0) {
                            suppliersCount.textContent = product.suppliers.length;
                            
                            // Find primary supplier
                            const primary = product.suppliers.find(supplier => supplier.id === product.default_supplier_id);
                            if (primary) {
                                primarySupplier.textContent = `${primary.supplier_name} (₱${parseFloat(primary.pivot.default_unit_cost).toFixed(2)})`;
                                primarySupplier.innerHTML;
                            } else {
                                primarySupplier.textContent = 'Not set';
                            }
                            
                            // Build suppliers list
                            let suppliersHtml = '';
                            product.suppliers.forEach(supplier => {
                                const isPrimary = supplier.id === product.default_supplier_id;
                                suppliersHtml += `
                                    <div class="supplier-item border rounded p-2 mb-2 ${isPrimary ? 'bg-success bg-opacity-10 border-success' : ''}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${supplier.supplier_name}</strong>
                                                ${isPrimary ? '<span>(Primary)</span>' : ''}
                                                <br>
                                                <small class="text-muted">Unit Cost: ₱${parseFloat(supplier.pivot.default_unit_cost).toFixed(2)}</small>
                                            </div>
                                            <div class="text-end">
                                                ${supplier.contactNO ? `<small class="text-muted d-block">${supplier.contactNO}</small>` : ''}
                                                ${supplier.address ? `<small class="text-muted">${supplier.address}</small>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            suppliersList.innerHTML = suppliersHtml;
                        } else {
                            suppliersCount.textContent = '0';
                            primarySupplier.textContent = 'No suppliers';
                            suppliersList.innerHTML = '<div class="text-center text-muted py-3">No suppliers assigned to this product</div>';
                        }
                        
                        // Handle archive info
                        if (product.is_active) {
                            document.getElementById('archiveInfo').style.display = 'none';
                        } else {
                            document.getElementById('archiveInfo').style.display = 'block';
                            
                            // Date disabled
                            if (product.date_disabled) {
                                document.getElementById('viewDateDisabled').textContent = 
                                    new Date(product.date_disabled).toLocaleString();
                            } else {
                                document.getElementById('viewDateDisabled').textContent = 'N/A';
                            }
                            
                            // Disabled by
                            if (product.disabled_by && product.disabled_by.full_name) {
                                document.getElementById('viewDisabledBy').textContent = product.disabled_by.full_name;
                            } else if (product.disabled_by_user_id) {
                                document.getElementById('viewDisabledBy').textContent = 'User #' + product.disabled_by_user_id;
                            } else {
                                document.getElementById('viewDisabledBy').textContent = 'System';
                            }
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewProductModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching product:', error);
                    });
            });
        });
        
        // Archive Product
        document.querySelectorAll('.archive-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                const hasSales = this.getAttribute('data-has-sales') === 'true';
                
                document.getElementById('archiveProductName').textContent = productName;
                document.getElementById('archiveProductForm').action = `/products/${productId}/archive`;
                
                const submitBtn = document.getElementById('archiveSubmitBtn');
                
                const modal = new bootstrap.Modal(document.getElementById('archiveProductModal'));
                modal.show();
            });
        });
        
        // Restore Product
        document.querySelectorAll('.restore-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                
                document.getElementById('restoreProductName').textContent = productName;
                document.getElementById('restoreProductForm').action = `/products/${productId}/restore`;
                
                const modal = new bootstrap.Modal(document.getElementById('restoreProductModal'));
                modal.show();
            });
        });
    </script>
    @endpush
@endsection