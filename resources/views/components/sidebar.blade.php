<!-- Sidebar Only -->
<nav class="sidebar">
    <div class="sidebar-content">
        <div class="logo-container">
            <div class="d-flex align-items-center">
                <img src="{{ asset('images/atin_logo.png') }}" alt="Company Logo" class="img-fluid me-3" style="max-height: 50px;">
                <div>
                    <div class="fw-bold fs-4 mb-0" style="color: var(--congress-blue);">ATIN</div>
                    <div class="small text-muted mb-0" style="line-height: 1.2;">
                        Industrial Hardware<br>Supply Inc.
                    </div>
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pos.index') }}" class="nav-link {{ request()->is('pos') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack me-3"></i>
                    <span>POS</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#collapseUser" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseUser">
                    <i class="bi bi-people me-3"></i>
                    <span class="pe-2">User Management</span>
                    <i class="bi bi-chevron-down ms-auto chevron"></i> 
                </a>
                <div class="collapse {{ request()->is('users*') || request()->is('roles*') ? 'show' : '' }}" id="collapseUser">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                                <i class="bi bi-people me-3"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->is('roles*') ? 'active' : '' }}">
                                <i class="bi bi-person-badge me-3"></i>
                                <span>Roles</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="#collapseInventory" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseInventory">
                    <i class="bi bi-boxes me-3"></i>
                    <span class="pe-2">Products</span>
                    <i class="bi bi-chevron-down ms-auto chevron"></i> 
                </a>
                
                <div class="collapse {{ request()->is('products*') || request()->is('categories*') || request()->is('suppliers*') ? 'show' : '' }}" id="collapseInventory">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="{{ route('products.index') }}" class="nav-link {{ request()->is('products*') ? 'active' : '' }}">
                                <i class="bi bi-tag me-3"></i>
                                <span>Product List</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">
                                <i class="bi bi-funnel me-3"></i>
                                <span>Categories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}">
                                <i class="bi bi-truck me-3"></i>
                                <span>Suppliers</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-ins.index') }}" class="nav-link {{ request()->is('stock-ins*') ? 'active' : '' }}">
                    <i class="bi bi-cart me-3"></i>
                    <span>Stock In</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="" class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                    <i class="bi bi-file-text me-3"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="#" class="nav-link {{ request()->is('settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear me-3"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div> <!-- End sidebar-content -->

    <!-- Logout Section -->
    <div class="p-4 border-top border-secondary">
        <div class="d-flex align-items-center">
            <div class="user-avatar me-3">
                {{ strtoupper(substr(session('user_name'), 0, 1)) }}
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold" style="color: var(--congress-blue);">{{ session('user_name') }}</div>
                <small class="text-muted">{{ session('user_role') }}</small>
            </div>
        </div>
        <form action="/logout" method="POST" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                <i class="bi bi-box-arrow-right me-1"></i>
                Logout
            </button>
        </form>
    </div>
</nav>