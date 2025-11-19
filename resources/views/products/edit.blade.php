@extends('layouts.app')
@section('title', 'Edit Product - ATIN Admin')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
<style>
    .supplier-item {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .primary-supplier {
        border-color: #28a745;
        background-color: #f8fff9;
    }
    .remove-supplier {
        color: #dc3545;
        cursor: pointer;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 5px;
    }
    .set-primary-btn {
        font-size: 0.8em;
        padding: 4px 8px;
        margin-right: 8px;
    }
</style>
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <a href="{{ route('products.index') }}" class="text-decoration-none text-dark">
                    <b class="underline">Products</b>
                </a> 
                > Edit Product
            </h2>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Products
            </a>
        </div>
    </div>

    <!-- Product Form -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Left Column: Basic Information -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                
                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="e.g. Lenovo Wireless Mouse M240"
                                   value="{{ old('name', $product->name) }}" required maxlength="150">
                            <div class="form-text">Max 150 characters</div>
                        </div>
                
                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      placeholder="Optional – short details like color, size, or features"
                                      rows="4" maxlength="500">{{ old('description', $product->description) }}</textarea>
                            <div class="form-text">Max 500 characters</div>
                        </div>
                    </div>
                
                    <!-- Right Column: Product Details + Inventory -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-box me-2"></i>Product Details & Inventory</h5>
                
                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                
                        <!-- SKU (Readonly) -->
                        <div class="mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" value="{{ $product->sku }}" readonly
                                   style="background-color: #e9ecef;">
                            <div class="form-text">SKU cannot be changed after product creation.</div>
                        </div>
                
                        <!-- Manufacturer Barcode -->
                        <div class="mb-3">
                            <label for="manufacturer_barcode" class="form-label">Manufacturer Barcode</label>
                            <input type="text" class="form-control" id="manufacturer_barcode" name="manufacturer_barcode" 
                                   value="{{ old('manufacturer_barcode', $product->manufacturer_barcode) }}" 
                                   maxlength="20" inputmode="numeric" pattern="[0-9]{12,20}"
                                   placeholder="Scan or type barcode..." 
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                        <label for="image" class="form-label">Product Image</label>

                        <!-- Product Image -->
                        <div class="mb-3">
                            @if($product->image_path)
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset($product->image_path) }}" alt="Current Image" class="image-preview">
                                </div>
                            @endif
                            <input type="file" class="form-control mt-2" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
                            <div class="form-text">JPEG, PNG, JPG, GIF, WEBP — Max 2MB</div>
                            <div id="imagePreview" class="mt-2 position-relative d-inline-block"></div>
                            <div id="imageError" class="text-danger small mt-1"></div>
                        </div>
                        
                        <input type="hidden" id="delete_image" name="delete_image" value="0">
                
                        <!-- Reorder Level -->
                        <div class="mb-3">
                            <label for="reorder_level" class="form-label">Reorder Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="reorder_level" name="reorder_level" 
                                   value="{{ old('reorder_level', $product->reorder_level) }}" min="0" max="99999" required>
                            <div class="form-text">Alert when stock falls below this level.</div>
                        </div>
                
                        <!-- Hidden Primary Supplier (for edit) -->
                        <input type="hidden" id="default_supplier_id" name="default_supplier_id" 
                               value="{{ old('default_supplier_id', $product->default_supplier_id) }}">
                    </div>
                </div>
                

                <!-- Suppliers Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="bi bi-truck me-2"></i>Suppliers</h5>
                        
                        <!-- Primary Supplier Display -->
                        <div class="row supplier-item primary-supplier mb-3 g-0">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Primary Supplier:</strong>
                                        <span id="primarySupplierName" class="ms-2">
                                            @if($product->defaultSupplier)
                                                {{ $product->defaultSupplier->supplier_name }}
                                            @else
                                                Not set
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="form-text text-muted mt-1">
                                    The primary supplier cannot be removed. To change the primary supplier, set another supplier as primary first.
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3">Alternate Suppliers</h6>
                        <div id="suppliers-container">
                            <!-- Alternate suppliers will be populated dynamically -->
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" class="btn btn-outline-primary" id="add-supplier">
                                <i class="bi bi-plus-circle me-1"></i>
                                Add Alternate Supplier
                            </button>
                            
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                <i class="bi bi-building me-1"></i>
                                Create New Supplier
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">
                        <i class="bi bi-building me-2"></i>
                        Quick Add Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quickSupplierForm">
                        @csrf
                        <div class="mb-3">
                            <label for="quick_supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_supplier_name" name="supplier_name" required maxlength="150">
                        </div>
                        <div class="mb-3">
                            <label for="quick_contactNO" class="form-label">Contact Information</label>
                            <input type="text" class="form-control" id="quick_contactNO" name="contactNO" maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="quick_address" class="form-label">Address</label>
                            <textarea class="form-control" id="quick_address" name="address" rows="3" maxlength="255"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitQuickSupplier">Add Supplier</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const ALL_SUPPLIERS = @json($suppliers->map(function($s) {
            return ['id' => $s->id, 'supplier_name' => $s->supplier_name];
        }));
    
        let sharedSupplierOptions = [];
        let supplierCount = 0;
        let currentSupplierModalContext = null;
    
        // Set primary supplier - KEEP THIS FUNCTION
        function setPrimarySupplier(supplierId, supplierName) {
            document.getElementById('default_supplier_id').value = supplierId;
            document.getElementById('primarySupplierName').textContent = supplierName;
    
            document.querySelectorAll('.set-primary-btn').forEach(btn => {
                if (btn.dataset.supplierId == supplierId) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-star-fill me-1"></i>Primary';
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-success');
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-star me-1"></i>Set Primary';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }
            });
        }

        function clearImage() {
            document.getElementById('image').value = '';
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('imageError').textContent = '';
        }
    
        // Image preview
            document.getElementById('image').addEventListener('change', function(e) {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = '';
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        
                        const clearBtn = document.createElement('button');
                        clearBtn.type = 'button';
                        clearBtn.className = 'btn btn-danger btn-sm position-absolute';
                        clearBtn.style.top = '5px';
                        clearBtn.style.right = '5px';
                        clearBtn.innerHTML = '<i class="bi bi-x"></i>';
                        clearBtn.onclick = clearImage;
                        
                        preview.appendChild(img);
                        preview.appendChild(clearBtn);
                    };
                    reader.readAsDataURL(this.files[0]);
                }
});
    
        // Add supplier row 
        function addSupplierRow(supplierId = '', isPrimary = false) {
            supplierCount++;
            const container = document.getElementById('suppliers-container');
    
            const supplierHtml = `
                <div class="supplier-item" id="supplier-${supplierCount}">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select supplier-select" name="suppliers[${supplierCount}][id]" data-supplier-id="${supplierCount}">
                                        <!-- Options filled by JS -->
                                    </select>
                                    <button type="button" class="btn btn-outline-success quick-add-supplier"
                                            data-bs-toggle="modal" data-bs-target="#addSupplierModal"
                                            data-context="alternate-${supplierCount}"
                                            title="Quickly create a new supplier">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Actions</label>
                                <div class="d-flex gap-1">
                                    <button type="button"
                                            class="btn btn-success set-primary-btn"
                                            data-supplier-id="${supplierCount}"
                                            onclick="setPrimaryFromRow(${supplierCount})"
                                            ${isPrimary ? 'disabled' : ''}>
                                        <i class="bi ${isPrimary ? 'bi-star-fill' : 'bi-star'} me-1"></i>
                                        ${isPrimary ? 'Primary' : 'Set Primary'}
                                    </button>
                                    <button type="button" class="btn btn-outline-danger remove-supplier" 
                                            onclick="removeSupplier(${supplierCount})" ${isPrimary ? 'disabled' : ''}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    
            container.insertAdjacentHTML('beforeend', supplierHtml);
    
            // Set modal context
            const quickBtn = document.querySelector(`[data-context="alternate-${supplierCount}"]`);
            quickBtn.addEventListener('click', () => {
                currentSupplierModalContext = quickBtn.getAttribute('data-context');
            });
    
            // Change event for supplier select
            const select = document.querySelector(`select[data-supplier-id="${supplierCount}"]`);
            select.addEventListener('change', () => updateSetPrimaryButton(supplierCount));
    
            // Style primary
            if (isPrimary) {
                document.getElementById(`supplier-${supplierCount}`).classList.add('primary-supplier');
            }
    
            // Populate dropdown from shared list
            const newSelect = document.querySelector(`select[data-supplier-id="${supplierCount}"]`);
            newSelect.innerHTML = '<option value="">Select Supplier</option>';
            sharedSupplierOptions.forEach(opt => {
                const isSelected = String(opt.value) === String(supplierId);
                newSelect.add(new Option(opt.text, opt.value, false, isSelected));
            });
        }
    
        // Set primary from row - KEEP THIS
        function setPrimaryFromRow(rowId) {
            const select = document.querySelector(`select[data-supplier-id="${rowId}"]`);
            if (select.value) {
                setPrimarySupplier(select.value, select.options[select.selectedIndex].text);
            }
        }
    
        // Update set primary button - KEEP THIS
        function updateSetPrimaryButton(rowId) {
            const select = document.querySelector(`select[data-supplier-id="${rowId}"]`);
            const btn = document.querySelector(`button[data-supplier-id="${rowId}"]`);
            const currentPrimaryId = document.getElementById('default_supplier_id').value;
            
            if (select.value === currentPrimaryId) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-star-fill me-1"></i>Primary';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-star me-1"></i>Set Primary';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }
        }
    
        // Remove supplier - KEEP THIS
        function removeSupplier(id) {
            const currentPrimaryId = document.getElementById('default_supplier_id').value;
            const select = document.querySelector(`select[data-supplier-id="${id}"]`);
            if (select.value === currentPrimaryId) {
                alert('Cannot remove the primary supplier. Set another supplier as primary first.');
                return;
            }
            document.getElementById(`supplier-${id}`)?.remove();
        }
    
        // Add supplier button
        document.getElementById('add-supplier').addEventListener('click', () => addSupplierRow());
    
        // Collect shared options
        function collectSharedSupplierOptions() {
            sharedSupplierOptions = ALL_SUPPLIERS.map(s => ({
                value: s.id,
                text: s.supplier_name
            }));
        }
    
        // Quick add supplier - KEEP THIS
        document.getElementById('submitQuickSupplier').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('quickSupplierForm'));
            fetch('{{ route("suppliers.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP ${response.status}: ${text}`); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // 1. Add to shared list
                    sharedSupplierOptions.push({
                        value: data.supplier.id,
                        text: data.supplier.supplier_name
                    });
    
                    // 2. Update all existing dropdowns
                    document.querySelectorAll('.supplier-select').forEach(select => {
                        if (!Array.from(select.options).some(opt => opt.value == data.supplier.id)) {
                            select.add(new Option(data.supplier.supplier_name, data.supplier.id));
                        }
                    });
    
                    // 3. Auto-select in correct row
                    if (currentSupplierModalContext?.startsWith('alternate-')) {
                        const rowId = currentSupplierModalContext.split('-')[1];
                        const targetSelect = document.querySelector(`select[data-supplier-id="${rowId}"]`);
                        if (targetSelect) {
                            targetSelect.value = data.supplier.id;
                            updateSetPrimaryButton(rowId);
                        }
                    }
    
                    // 4. Close modal & reset
                    bootstrap.Modal.getInstance(document.getElementById('addSupplierModal')).hide();
                    document.getElementById('quickSupplierForm').reset();
                    currentSupplierModalContext = null;
                    alert('Supplier added successfully!');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error adding supplier: ' + error.message);
            });
        });
    
        // Page load: Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            collectSharedSupplierOptions();
            
            // Load existing alternate suppliers
            @foreach($product->suppliers as $supplier)
                @if($supplier->id != $product->default_supplier_id)
                    addSupplierRow({{ $supplier->id }}, false);
                @endif
            @endforeach

            const existingImage = document.querySelector('.image-preview');
            if (existingImage) {
                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'btn btn-danger btn-sm position-absolute';
                clearBtn.style.top = '5px';
                clearBtn.style.right = '5px';
                clearBtn.innerHTML = '<i class="bi bi-x"></i>';
                clearBtn.onclick = function() {
                    existingImage.remove();
                    clearBtn.remove();
                    document.getElementById('delete_image').value = '1';
                };
                
                existingImage.parentElement.classList.add('position-relative');
                existingImage.parentElement.appendChild(clearBtn);
            }
    
            // Add empty row if none
            if (supplierCount === 0) {
                addSupplierRow();
            }
            
    
            // Set primary supplier name
            const primarySupplierId = document.getElementById('default_supplier_id').value;
            if (primarySupplierId) {
                @foreach($suppliers as $supplier)
                    if ({{ $supplier->id }} == primarySupplierId) {
                        document.getElementById('primarySupplierName').textContent = '{{ $supplier->supplier_name }}';
                    }
                @endforeach
            }
    
            // Prevent leading zeros in reorder level
            ["reorder_level"].forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener("input", function () {
                        this.value = this.value.replace(/^0+(?=\d)/, '');
                    });
                }
            });
        });
    </script>
    @endpush
@endsection