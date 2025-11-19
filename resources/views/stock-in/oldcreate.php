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