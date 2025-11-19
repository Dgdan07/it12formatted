@extends('layouts.app')
@section('title', 'Product Prices - ATIN Admin')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .product-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
    .no-price {
        color: #6c757d;
        font-style: italic;
    }
</style>
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Product Prices</b>
            </h2>
            <div>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-box me-1"></i>
                    View Products
                </a>
                <a href="{{ route('stock-ins.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    New Stock In
                </a>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <form action="{{ route('product-prices.index') }}" method="GET" class="d-flex flex-grow-1 me-2">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <input type="hidden" name="direction" value="{{ $direction }}">
                            <div class="input-group search-box w-100">
                                <input type="text" class="form-control" name="search" placeholder="Search by product name or SKU..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        @if(request('search'))
                            <a href="{{ route('product-prices.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Sort & Filters -->
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-end">
                        <!-- Price Status Filter -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-filter me-1"></i>Price Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item {{ !request('price_status') ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['price_status' => null]) }}">
                                    All Products
                                </a></li>
                                <li><a class="dropdown-item {{ request('price_status') == 'with_price' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['price_status' => 'with_price']) }}">
                                    With Price
                                </a></li>
                                <li><a class="dropdown-item {{ request('price_status') == 'no_price' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['price_status' => 'no_price']) }}">
                                    No Price Set
                                </a></li>
                            </ul>
                        </div>

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
                                <li><a class="dropdown-item {{ $sort == 'retail_price' ? 'active' : '' }}" 
                                    href="{{ request()->fullUrlWithQuery(['sort' => 'retail_price', 'direction' => $sort == 'retail_price' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Price @if($sort == 'retail_price') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'quantity_in_stock' ? 'active' : '' }}" 
                                    href="{{ request()->fullUrlWithQuery(['sort' => 'quantity_in_stock', 'direction' => $sort == 'quantity_in_stock' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Stock @if($sort == 'quantity_in_stock') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Prices Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="text-muted mb-3">
                @if(request('search'))
                    Displaying {{ $products->count() }} of {{ $products->total() }} results for "{{ request('search') }}"
                    @if(request('price_status'))
                        ({{ request('price_status') == 'with_price' ? 'With Price' : 'No Price Set' }})
                    @endif
                @else
                    Displaying {{ $products->count() }} of {{ $products->total() }} products
                    @if(request('price_status'))
                        ({{ request('price_status') == 'with_price' ? 'With Price' : 'No Price Set' }})
                    @endif
                @endif
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Current Stock</th>
                        <th>Retail Price</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td><code>{{ $product->sku }}</code></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image me-2">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->manufacturer_barcode)
                                        <br><small class="text-muted">Barcode: {{ $product->manufacturer_barcode }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-semibold {{ $product->quantity_in_stock == 0 ? 'text-danger' : ($product->quantity_in_stock <= $product->reorder_level ? 'text-warning' : 'text-success') }}">
                                {{ $product->quantity_in_stock }}
                            </span>
                            @if($product->quantity_in_stock <= $product->reorder_level)
                                <br><small class="text-danger">Low Stock</small>
                            @endif
                        </td>
                        <td>
                            @if($product->latestProductPrice)
                                <span class="fw-bold text-success">₱{{ number_format($product->latestProductPrice->retail_price, 2) }}</span>
                            @else
                                <span class="no-price">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($product->latestProductPrice)
                                {{ $product->latestProductPrice->updated_at->format('Y-m-d') }}
                                <br>
                                <small class="text-muted">
                                    by {{ $product->latestProductPrice->updatedBy->full_name ?? 'System' }}
                                </small>
                            @else
                                <span class="no-price">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($product->productPrice)
                                    <button class="btn btn-sm btn-outline-info view-price-history" 
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            title="View Price History">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-warning edit-price" 
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-current-price="{{ $product->productPrice ? $product->productPrice->retail_price : '' }}"
                                        title="Edit Price">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            No products found
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

    <!-- Edit Price Modal -->
    <div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPriceForm" method="POST" action="{{ route('product-prices.update') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPriceModalLabel">
                            <i class="bi bi-currency-dollar me-2"></i>
                            Update Product Price
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="editProductName" readonly>
                            <input type="hidden" id="editProductId" name="product_id">
                        </div>
                        <div class="mb-3">
                            <label for="retail_price" class="form-label">Retail Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="retail_price" name="retail_price" step="0.01" min="0" required>
                            </div>
                            <div class="form-text">Enter the new selling price for this product</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            This will create a new price record in the price history.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Price History Modal -->
    <div class="modal fade" id="priceHistoryModal" tabindex="-1" aria-labelledby="priceHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="priceHistoryModalLabel">
                        <i class="bi bi-clock-history me-2"></i>
                        Price History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="historyProductName" class="mb-3"></h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Retail Price</th>
                                    <th>Updated By</th>
                                    <th>Stock In Reference</th>
                                    <th>Date Updated</th>
                                </tr>
                            </thead>
                            <tbody id="priceHistoryTable">
                                <!-- Price history will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Edit Price
        document.querySelectorAll('.edit-price').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                const currentPrice = this.getAttribute('data-current-price');
                
                document.getElementById('editProductId').value = productId;
                document.getElementById('editProductName').value = productName;
                document.getElementById('retail_price').value = currentPrice;
                document.getElementById('editPriceForm').action = '{{ route("product-prices.update") }}';
                
                const modal = new bootstrap.Modal(document.getElementById('editPriceModal'));
                modal.show();
            });
        });

        // View Price History
        document.querySelectorAll('.view-price-history').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                
                document.getElementById('historyProductName').textContent = productName;
                
                // Fetch price history
                fetch(`/api/product-prices/${productId}/history`)
                    .then(response => response.json())
                    .then(history => {
                        const table = document.getElementById('priceHistoryTable');
                        table.innerHTML = '';
                        
                        if (history.length > 0) {
                            history.forEach(price => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td class="fw-bold">₱${parseFloat(price.retail_price).toFixed(2)}</td>
                                    <td>${price.updated_by ? price.updated_by.full_name : 'System'}</td>
                                    <td>${price.stock_in ? price.stock_in.reference_no : 'Manual Update'}</td>
                                    <td>${new Date(price.updated_at).toLocaleString()}</td>
                                `;
                                table.appendChild(row);
                            });
                        } else {
                            table.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No price history found</td></tr>';
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('priceHistoryModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching price history:', error);
                        alert('Error loading price history');
                    });
            });
        });

        // Price form submission
        document.getElementById('editPriceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Price updated successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('editPriceModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update price'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error occurred');
            });
        });
    </script>
    @endpush
@endsection