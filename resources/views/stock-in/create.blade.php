@extends('layouts.app')
@section('title', 'New Stock In - ATIN Admin')

@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .stockin-panel {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: #f8f9fa;
    }
    .item-row {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: white;
    }
    .remove-item {
        color: #dc3545;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
    @include('components.alerts')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <a href="{{ route('stock-ins.index') }}" class="text-decoration-none text-dark">
                    <b class="underline">Stock In</b>
                </a>
                > Process New Stock In
            </h2>
            <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-secondary">
                Back to Stock In
            </a>
        </div>
    </div>

    <!-- Stock In Panels Container -->
    <div id="stockin-panels-container">
        <!-- Panels added dynamically -->
    </div>

    <!-- Add New Shipment Button -->
    <div class="d-flex justify-content-center mb-4">
        <button type="button" class="btn btn-outline-primary" id="add-new-shipment">
            Process New Invoice
        </button>
    </div>

    <!-- Post All Button -->
    <div class="d-flex justify-content-end mt-4">
        <button type="button" class="btn btn-success" id="post-all-shipments">
            Post All Shipments
        </button>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Confirm all stock in
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-box-seam text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Are you sure you want to post all pending shipments?</h5>
                    <p class="text-muted">This action will permanently update inventory.</p>
                    <div class="alert alert-warning mt-3">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <div id="confirmationSummary" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmPostAll">Confirm and Post</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let panelCount = 0;
        const addedProducts = new Map(); // Track products per panel

        // Add new shipment panel
        document.getElementById('add-new-shipment').addEventListener('click', function() {
            panelCount++;
            addedProducts.set(panelCount, new Set());

            const container = document.getElementById('stockin-panels-container');

            const panelHtml = `
                <div class="stockin-panel" id="panel-${panelCount}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Shipment #${panelCount}</h5>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePanel(${panelCount})" ${panelCount === 1 ? 'disabled' : ''}>
                            Remove
                        </button>
                    </div>

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select supplier-select" name="panels[${panelCount}][supplier_id]" required onchange="handleSupplierChange(${panelCount}, this.value)">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- IMPORTANT NOTE -->
                            <div class="alert alert-info mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bi bi-info-circle" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <strong>Important:</strong> Each Stock In transaction is for <strong>one supplier only</strong>.
                                        <br>
                                        <small class="text-muted">
                                            If you have products from multiple suppliers, create separate Stock In transactions for each supplier.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reference No. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="panels[${panelCount}][reference_no]" placeholder="Invoice/Delivery Receipt Number" required>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stock In Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="panels[${panelCount}][stock_in_date]" value="{{ now()->format('Y-m-d\TH:i') }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Received By</label>
                                <input type="text" class="form-control" value="{{ session('user_name') ?? 'Current User' }}" readonly>
                                <input type="hidden" name="panels[${panelCount}][received_by_user_id]" value="{{ session('user_id') ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Items</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-item-${panelCount}" onclick="addItemRow(${panelCount})">
                                Add Item
                            </button>
                        </div>
                        <div id="items-container-${panelCount}"></div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', panelHtml);
        });

        // Handle supplier changes
        function handleSupplierChange(panelId, supplierId) {
            const itemsContainer = document.getElementById(`items-container-${panelCount}`);
            const productSelects = itemsContainer.querySelectorAll('.product-select');
            
            if (supplierId) {
                // Filter products based on selected supplier
                filterProductsBySupplier(panelId, supplierId);
            } else {
                // Reset all product selects to show all products
                productSelects.forEach(select => {
                    select.innerHTML = `
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    `;
                });
            }
        }

        // Filter products by supplier
        function filterProductsBySupplier(panelId, supplierId) {
            const itemsContainer = document.getElementById(`items-container-${panelId}`);
            const productSelects = itemsContainer.querySelectorAll('.product-select');
            
            // Fetch products for this supplier
            fetch(`/api/suppliers/${supplierId}/products`)
                .then(response => response.json())
                .then(products => {
                    productSelects.forEach(select => {
                        const currentValue = select.value;
                        select.innerHTML = '<option value="">Select Product</option>';
                        
                        products.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.id;
                            option.textContent = product.name;
                            select.appendChild(option);
                        });
                        
                        // Restore previous selection if it's still valid
                        if (currentValue && products.some(p => p.id == currentValue)) {
                            select.value = currentValue;
                        } else {
                            select.value = '';
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching supplier products:', error);
                    // Fallback to all products if API fails
                    productSelects.forEach(select => {
                        select.innerHTML = `
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        `;
                    });
                });
        }

        // Add item
        function addItemRow(panelId) {
            const container = document.getElementById(`items-container-${panelId}`);
            const supplierSelect = document.querySelector(`#panel-${panelId} .supplier-select`);
            const supplierId = supplierSelect ? supplierSelect.value : '';
            
            if (!supplierId) {
                alert('Please select a supplier first before adding products.');
                return;
            }

            const itemId = Date.now();

            container.insertAdjacentHTML('beforeend', `
                <div class="item-row" id="item-${panelId}-${itemId}">
                    <div class="row">
                        <div class="col-md-4">
                            <select class="form-select product-select" name="panels[${panelId}][items][${itemId}][product_id]" required onchange="checkDuplicateProduct(${panelId}, this.value, ${itemId})">
                                <option value="">Select Product</option>
                                <!-- Products will be filtered by JavaScript -->
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control" name="panels[${panelId}][items][${itemId}][quantity_received]" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="panels[${panelId}][items][${itemId}][actual_unit_cost]" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeItem(${panelId}, ${itemId})">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            `);
            
            // Filter products for the new row
            if (supplierId) {
                filterProductsBySupplier(panelId, supplierId);
            }
        }

        function checkDuplicateProduct(panelId, productId, itemId) {
            if (!productId) return;
            if (addedProducts.get(panelId).has(parseInt(productId))) {
                alert('Product already added.');
                document.querySelector(`#item-${panelId}-${itemId} .product-select`).value = '';
                return;
            }
            addedProducts.get(panelId).add(parseInt(productId));
        }

        function removeItem(panelId, itemId) {
            const row = document.getElementById(`item-${panelId}-${itemId}`);
            const select = row.querySelector('.product-select');
            if (select?.value) addedProducts.get(panelId).delete(parseInt(select.value));
            row.remove();
        }

        function removePanel(panelId) {
            document.getElementById(`panel-${panelId}`).remove();
            addedProducts.delete(panelId);
        }

        // Post All
        document.getElementById('post-all-shipments').addEventListener('click', function() {
            const panels = document.querySelectorAll('.stockin-panel');
            if (!panels.length) return alert('No shipments.');

            const totalItems = [...panels].reduce((sum, p) => sum + p.querySelectorAll('.item-row').length, 0);
            document.getElementById('confirmationSummary').innerHTML = `<strong>${panels.length} shipment(s), ${totalItems} item(s)</strong>`;

            new bootstrap.Modal(document.getElementById('confirmationModal')).show();
        });

        document.getElementById('confirmPostAll').addEventListener('click', function() {
            const formData = new FormData();
            let hasErrors = false;
            
            // Validate each panel before submitting
            document.querySelectorAll('.stockin-panel').forEach((panel, i) => {
                console.log(`Processing panel ${i}:`, panel);
                
                // Check if supplier is selected
                const supplier = panel.querySelector('.supplier-select')?.value;
                if (!supplier) {
                    alert(`Shipment #${i+1}: Please select a supplier`);
                    hasErrors = true;
                    return;
                }

                // Check if items exist
                const items = panel.querySelectorAll('.item-row');
                if (items.length === 0) {
                    alert(`Shipment #${i+1}: Please add at least one item`);
                    hasErrors = true;
                    return;
                }

                // Check each item
                items.forEach((item, j) => {
                    const productId = item.querySelector('input[name*="product_id"], select[name*="product_id"]')?.value;
                    const quantity = item.querySelector('input[name*="quantity_received"]')?.value;
                    const cost = item.querySelector('input[name*="actual_unit_cost"]')?.value;
                    
                    if (!productId || !quantity || !cost) {
                        alert(`Shipment #${i+1}, Item #${j+1}: Please fill all fields`);
                        hasErrors = true;
                    }
                });

                if (hasErrors) return;

                // Build form data
                const data = {};
                
                if (supplier) data.supplier_id = supplier;

                const ref = panel.querySelector('input[name*="reference_no"]')?.value;
                if (ref) data.reference_no = ref;

                const date = panel.querySelector('input[name*="stock_in_date"]')?.value;
                if (date) data.stock_in_date = date;

                const user = panel.querySelector('input[name*="received_by_user_id"]')?.value;
                if (user) data.received_by_user_id = user;

                const itemsData = [];
                panel.querySelectorAll('.item-row').forEach(row => {
                    const item = {};
                    const pid = row.querySelector('input[name*="product_id"], select[name*="product_id"]')?.value;
                    const qty = row.querySelector('input[name*="quantity_received"]')?.value;
                    const cost = row.querySelector('input[name*="actual_unit_cost"]')?.value;
                    
                    if (pid && qty && cost) {
                        item.product_id = pid;
                        item.quantity_received = qty;
                        item.actual_unit_cost = cost;
                        itemsData.push(item);
                    }
                });
                data.items = itemsData;

                // Add to FormData with debugging
                console.log(`Panel ${i} data:`, data);
                
                Object.keys(data).forEach(k => {
                    if (k === 'items') {
                        data[k].forEach((it, j) => {
                            Object.keys(it).forEach(key => {
                                formData.append(`panels[${i}][${k}][${j}][${key}]`, it[key]);
                            });
                        });
                    } else {
                        formData.append(`panels[${i}][${k}]`, data[k]);
                    }
                });
            });

            if (hasErrors) {
                console.log('Validation errors found, stopping submission');
                return;
            }

            // Debug: Show what's being sent
            console.log('FormData contents:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Make the request with better error handling
            fetch('{{ route("stock-ins.store") }}', {
                method: 'POST',
                body: formData,
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    // Try to get error message from response
                    return response.text().then(text => {
                        console.error('Server response text:', text);
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Success response:', data);
                if (data.success) {
                    alert('Posted successfully!');
                    window.location = "{{ route('stock-ins.index') }}";
                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                console.error('Full error details:', error);
                alert('Network error details: ' + error.message);
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('add-new-shipment').click();
        });
    </script>
    @endpush
@endsection