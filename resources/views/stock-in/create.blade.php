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
    .autofill-highlight {
        background-color: #e8f5e8 !important;
        border-color: #28a745 !important;
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
                <i class="bi bi-arrow-left me-1"></i>
                Back to Stock In
            </a>
        </div>
    </div>

    <!-- Single Stock In Panel -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Reference No. <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" placeholder="Invoice/Delivery Receipt Number" required>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Stock In Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="stock_in_date" name="stock_in_date" value="{{ now()->format('Y-m-d\TH:i') }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Received By</label>
                        <input type="text" class="form-control" value="{{ session('user_name') ?? 'Current User' }}" readonly>
                        <input type="hidden" id="received_by_user_id" name="received_by_user_id" value="{{ session('user_id') ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Items</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">
                        <i class="bi bi-plus-circle me-1"></i> Add Item
                    </button>
                </div>
                <div id="items-container">
                    <!-- Items will be added here dynamically -->
                </div>
            </div>

            <!-- Post Button -->
            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-success" id="post-shipment">
                    Post Shipment
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Confirm Stock In
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-box-seam text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Are you sure you want to post this shipment?</h5>
                    <p class="text-muted">This action will permanently update inventory and pricing.</p>
                    <div class="alert alert-warning mt-3">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <div id="confirmationSummary" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmPost">Confirm and Post</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let itemCount = 0;
        const addedProducts = new Set();

        // Product data from Laravel
        const PRODUCTS_DATA = @php 
    echo json_encode($products->map(function($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'latest_unit_cost' => $product->latest_unit_cost,
            'default_supplier_id' => $product->default_supplier_id,
            'default_supplier_name' => $product->defaultSupplier ? $product->defaultSupplier->supplier_name : null,
            'current_retail_price' => $product->productPrice ? $product->productPrice->retail_price : null
        ];
    }));
@endphp;

        // Suppliers data from Laravel
        const SUPPLIERS_DATA = @json($suppliers->map(function($supplier) {
            return [
                'id' => $supplier->id,
                'supplier_name' => $supplier->supplier_name
            ];
        }));

        // Add item row
        function addItemRow(productId = '') {
            itemCount++;
            const container = document.getElementById('items-container');

            const itemHtml = `
                <div class="item-row" id="item-${itemCount}">
                    <div class="row">
                        <!-- Product Selection -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select class="form-select product-select" name="items[${itemCount}][product_id]" required onchange="handleProductChange(${itemCount}, this.value)">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-cost="{{ $product->latest_unit_cost }}" data-supplier="{{ $product->default_supplier_id }}" data-price="{{ $product->productPrice ? $product->productPrice->retail_price : '' }}">
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Supplier Selection -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select supplier-select" name="items[${itemCount}][supplier_id]" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-1">
                            <div class="mb-3">
                                <label class="form-label">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="items[${itemCount}][quantity_received]" min="1" required>
                            </div>
                        </div>

                        <!-- Unit Cost -->
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Unit Cost <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control unit-cost" name="items[${itemCount}][actual_unit_cost]" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Retail Price -->
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Retail Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control retail-price" name="items[${itemCount}][retail_price]" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Remove Button -->
                        <div class="col-md-1">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger w-100" onclick="removeItem(${itemCount})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', itemHtml);

            // Auto-select product if provided
            if (productId) {
                const select = document.querySelector(`#item-${itemCount} .product-select`);
                select.value = productId;
                handleProductChange(itemCount, productId);
            }
        }

        // Handle product selection change
        function handleProductChange(itemId, productId) {
            if (!productId) return;

            // Check for duplicate product
            if (addedProducts.has(parseInt(productId))) {
                alert('This product has already been added to the shipment.');
                document.querySelector(`#item-${itemId} .product-select`).value = '';
                return;
            }
            addedProducts.add(parseInt(productId));

            // Find product data
            const product = PRODUCTS_DATA.find(p => p.id == productId);
            if (!product) return;

            const itemRow = document.getElementById(`item-${itemId}`);
            
            // Auto-fill supplier
            const supplierSelect = itemRow.querySelector('.supplier-select');
            if (product.default_supplier_id) {
                supplierSelect.value = product.default_supplier_id;
            }

            // Auto-fill unit cost
            const unitCostInput = itemRow.querySelector('.unit-cost');
            if (product.latest_unit_cost) {
                unitCostInput.value = product.latest_unit_cost;
                unitCostInput.classList.add('autofill-highlight');
                setTimeout(() => unitCostInput.classList.remove('autofill-highlight'), 2000);
            }

            // Auto-fill retail price
            const retailPriceInput = itemRow.querySelector('.retail-price');
            if (product.current_retail_price) {
                retailPriceInput.value = product.current_retail_price;
                retailPriceInput.classList.add('autofill-highlight');
                setTimeout(() => retailPriceInput.classList.remove('autofill-highlight'), 2000);
            }
        }

        function removeItem(itemId) {
            const row = document.getElementById(`item-${itemId}`);
            const select = row.querySelector('.product-select');
            if (select?.value) addedProducts.delete(parseInt(select.value));
            row.remove();
        }

        // Add item button
        document.getElementById('add-item').addEventListener('click', () => addItemRow());

        // Post Shipment
        document.getElementById('post-shipment').addEventListener('click', function() {
            const items = document.querySelectorAll('.item-row');
            if (items.length === 0) {
                alert('Please add at least one item.');
                return;
            }

            const referenceNo = document.getElementById('reference_no').value;
            if (!referenceNo) {
                alert('Please enter a reference number.');
                return;
            }

            // Build confirmation summary
            let summary = `<strong>Reference:</strong> ${referenceNo}<br>`;
            summary += `<strong>Items:</strong> ${items.length}<br><br>`;
            
            items.forEach((item, index) => {
                const productSelect = item.querySelector('.product-select');
                const supplierSelect = item.querySelector('.supplier-select');
                const quantity = item.querySelector('input[name*="quantity_received"]').value;
                const cost = item.querySelector('.unit-cost').value;
                const price = item.querySelector('.retail-price').value;
                
                if (productSelect.value && supplierSelect.value) {
                    const productName = productSelect.options[productSelect.selectedIndex].text;
                    const supplierName = supplierSelect.options[supplierSelect.selectedIndex].text;
                    summary += `<strong>Item ${index + 1}:</strong> ${productName}<br>`;
                    summary += `Supplier: ${supplierName} | Qty: ${quantity} | Cost: ₱${cost} | Price: ₱${price}<br><br>`;
                }
            });

            document.getElementById('confirmationSummary').innerHTML = summary;
            new bootstrap.Modal(document.getElementById('confirmationModal')).show();
        });

        document.getElementById('confirmPost').addEventListener('click', function() {
            const formData = new FormData();
            let hasErrors = false;

            // Basic data
            formData.append('reference_no', document.getElementById('reference_no').value);
            formData.append('stock_in_date', document.getElementById('stock_in_date').value);
            formData.append('received_by_user_id', document.getElementById('received_by_user_id').value);

            // Items data
            document.querySelectorAll('.item-row').forEach((item, index) => {
                const productId = item.querySelector('.product-select').value;
                const supplierId = item.querySelector('.supplier-select').value;
                const quantity = item.querySelector('input[name*="quantity_received"]').value;
                const actualUnitCost = item.querySelector('.unit-cost').value;
                const retailPrice = item.querySelector('.retail-price').value;

                if (!productId || !supplierId || !quantity || !actualUnitCost || !retailPrice) {
                    alert(`Item ${index + 1} has missing fields.`);
                    hasErrors = true;
                    return;
                }

                formData.append(`items[${index}][product_id]`, productId);
                formData.append(`items[${index}][supplier_id]`, supplierId);
                formData.append(`items[${index}][quantity_received]`, quantity);
                formData.append(`items[${index}][actual_unit_cost]`, actualUnitCost);
                formData.append(`items[${index}][retail_price]`, retailPrice);
            });

            if (hasErrors) return;

            // Submit the form
            fetch('{{ route("stock-ins.store") }}', {
                method: 'POST',
                body: formData,
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Stock In posted successfully!');
                    window.location = "{{ route('stock-ins.index') }}";
                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error: ' + error.message);
            });
        });

        // Initialize with one empty row
        document.addEventListener('DOMContentLoaded', () => {
            addItemRow();
        });
    </script>
    @endpush
@endsection