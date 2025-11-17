@extends('layouts.app')
@section('title', 'Stock In - ATIN Admin')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <b>Stock In</b>
            </h2>
            <a href="{{ route('stock-ins.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                New Stock In
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Search & Clear -->
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <form action="{{ route('stock-ins.index') }}" method="GET" class="d-flex flex-grow-1 me-2">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <input type="hidden" name="direction" value="{{ $direction }}">
                            <div class="input-group search-box w-100">
                                <input type="text" class="form-control" name="search" placeholder="Search by reference or product..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        @if(request('search'))
                            <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-danger flex-shrink-0" title="Clear search">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Sort -->
                <div class="col-md-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-sort-down me-1"></i>Sort
                                @if($sort)
                                    <small class="ms-1">({{ $direction == 'asc' ? '↑' : '↓' }})</small>
                                @endif
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item {{ $sort == 'id' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort == 'id' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    ID @if($sort == 'id') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'stock_in_date' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort' => 'stock_in_date', 'direction' => $sort == 'stock_in_date' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Date @if($sort == 'stock_in_date') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                                <li><a class="dropdown-item {{ $sort == 'reference_no' ? 'active' : '' }}" 
                                       href="{{ request()->fullUrlWithQuery(['sort' => 'reference_no', 'direction' => $sort == 'reference_no' && $direction == 'asc' ? 'desc' : 'asc']) }}">
                                    Reference @if($sort == 'reference_no') <i class="bi bi-arrow-{{ $direction == 'asc' ? 'up' : 'down' }} float-end"></i> @endif
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock In Table -->
    <div class="table-container">    
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="text-muted mb-3">
                @if(request('search'))
                    Displaying {{ $stockIns->count() }} of {{ $stockIns->total() }} results for "{{ request('search') }}"
                @else
                    Displaying {{ $stockIns->count() }} of {{ $stockIns->total() }} stock in records
                @endif
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Reference No</th>
                        <th>Supplier</th>
                        <th>Received By</th>
                        <th>Items</th>
                        <th>Total Quantity</th>
                        <th>Total Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockIns as $stockIn)
                    <tr>
                        <td>{{ $stockIn->id }}</td>
                        <td>{{ $stockIn->stock_in_date->format('Y-m-d H:i') }}</td>
                        <td>{{ $stockIn->reference_no ?? 'N/A' }}</td>
                        <td>{{ $stockIn->supplier->supplier_name ?? 'N/A' }}</td>
                        <td>{{ $stockIn->receivedBy ? $stockIn->receivedBy->full_name : 'Unknown User' }}</td>
                        <td>{{ $stockIn->items->count() }}</td>
                        <td>{{ $stockIn->items->sum('quantity_received') }}</td>
                        <td>₱{{ number_format($stockIn->items->sum(function($item) { return $item->quantity_received * $item->actual_unit_cost; }), 2) }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-stock-in" data-id="{{ $stockIn->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            No stock in records found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $stockIns->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- View Stock In Modal -->
    <div class="modal fade" id="viewStockInModal" tabindex="-1" aria-labelledby="viewStockInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewStockInModalLabel">
                        <i class="bi bi-box-arrow-in-down me-2"></i>
                        Stock In Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Stock In ID:</small>
                                    <span class="fw-semibold" id="viewStockInId"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Date:</small>
                                    <span class="fw-semibold" id="viewStockInDate"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Reference No:</small>
                                    <span class="fw-semibold" id="viewReferenceNo">N/A</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Supplier:</small>
                                    <span class="fw-semibold" id="viewSupplier"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Received By:</small>
                                    <span class="fw-semibold" id="viewReceivedBy"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Total Items:</small>
                                    <span class="fw-semibold" id="viewTotalItems"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Total Quantity:</small>
                                    <span class="fw-semibold" id="viewTotalQuantity"></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <small class="text-muted">Total Cost:</small>
                                    <span class="fw-semibold" id="viewTotalCost"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mt-4">
                        <h6 class="mb-3">Items Received</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                        <th>Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody id="viewItemsTable">
                                    <!-- Items will be populated here -->
                                </tbody>
                            </table>
                        </div>
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
        // View Stock In
        document.querySelectorAll('.view-stock-in').forEach(button => {
            button.addEventListener('click', function() {
                const stockInId = this.getAttribute('data-id');
                
                fetch(`/stock-ins/${stockInId}`)
                    .then(response => response.json())
                    .then(stockIn => {
                        document.getElementById('viewStockInId').textContent = stockIn.id;
                        document.getElementById('viewStockInDate').textContent = new Date(stockIn.stock_in_date).toLocaleString();
                        document.getElementById('viewReferenceNo').textContent = stockIn.reference_no || 'N/A';
                        document.getElementById('viewSupplier').textContent = stockIn.supplier ? stockIn.supplier.supplier_name : 'N/A';
                        document.getElementById('viewReceivedBy').textContent = stockIn.received_by.full_name;
                        document.getElementById('viewTotalItems').textContent = stockIn.items.length;
                        
                        // Calculate totals
                        const totalQuantity = stockIn.items.reduce((sum, item) => sum + parseInt(item.quantity_received), 0);
                        const totalCost = stockIn.items.reduce((sum, item) => sum + (item.quantity_received * item.actual_unit_cost), 0);
                        
                        document.getElementById('viewTotalQuantity').textContent = totalQuantity;
                        document.getElementById('viewTotalCost').textContent = '₱' + parseFloat(totalCost).toFixed(2);
                        
                        // Populate items table
                        const itemsTable = document.getElementById('viewItemsTable');
                        itemsTable.innerHTML = '';
                        
                        stockIn.items.forEach(item => {
                            const row = document.createElement('tr');
                            const totalCost = item.quantity_received * item.actual_unit_cost;
                            row.innerHTML = `
                                <td>${item.product.name}</td>
                                <td>${item.quantity_received}</td>
                                <td>₱${parseFloat(item.actual_unit_cost).toFixed(2)}</td>
                                <td>₱${parseFloat(totalCost).toFixed(2)}</td>
                            `;
                            itemsTable.appendChild(row);
                        });
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewStockInModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching stock in:', error);
                    });
            });
        });
    </script>
    @endpush
@endsection