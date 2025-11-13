@extends('layouts.app')
@section('title', 'Users - ATIN Admin')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')
    
    <!-- Page Header -->
    <div class="page-header">
        <h2 class="mb-0">
            <b>Users Management</b>
        </h2>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <!-- Search Bar -->
            <form action="{{ route('users.index') }}" method="GET" class="d-flex flex-grow-1 me-3" style="max-width: 400px;">
                <input type="hidden" name="archived" value="{{ $showArchived ? 'true' : '' }}">
                <div class="input-group search-box w-100">
                    <input type="text" class="form-control" name="search" placeholder="Search users..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
    
            <!-- Action Buttons (Archive / Add) -->
            <div class="d-flex justify-content-end align-items-center gap-2">
                @if($showArchived)
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Active Users
                    </a>
                @else
                    <a href="{{ route('users.index', ['archived' => true]) }}" class="btn btn-outline-warning">
                        <i class="bi bi-archive me-1"></i>
                        Archive
                    </a>
                @endif
    
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add New User
                </button>
            </div>
        </div>
        
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    @if(request('search'))
                        Displaying {{ $users->count() }} of {{ $users->total() }} results for "{{ request('search') }}"
                    @else
                        Displaying {{ $users->count() }} of {{ $users->total() }} {{ $showArchived ? 'archived' : 'active' }} users
                    @endif
                </div>
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Contact No.</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <strong>{{ $user->username }}</strong>
                            @if(!$user->is_active)
                                <span class="badge bg-warning ms-1">Archived</span>
                            @endif
                        </td>
                        <td>{{ $user->full_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->contactNo ?? 'N/A' }}</td>
                        <td class="primary">{{ $user->role->name }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-warning">Archived</span>
                                @if($user->date_disabled)
                                    <br><small class="text-muted">{{ $user->date_disabled->format('M j, Y') }}</small>
                                @endif
                            @endif
                        </td>
                        <td>{{ $user->updated_at->format('Y-m-d') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info btn-action view-user" data-id="{{ $user->id }}" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($user->is_active)
                                <button class="btn btn-sm btn-outline-warning btn-action edit-user" data-id="{{ $user->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-action archive-user" data-id="{{ $user->id }}" data-name="{{ $user->full_name }}" title="Archive">
                                    <i class="bi bi-archive"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-success btn-action restore-user" data-id="{{ $user->id }}" data-name="{{ $user->full_name }}" title="Restore">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            No {{ $showArchived ? 'archived' : 'active' }} users found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add New User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" maxlength="50" required>
                                </div>
                                <div class="mb-3">
                                    <label for="f_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="f_name" name="f_name" placeholder="Enter first name" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label for="m_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="m_name" name="m_name" placeholder="Enter middle name" maxlength="100">
                                </div>
                                <div class="mb-3">
                                    <label for="l_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="l_name" name="l_name" placeholder="Enter last name" maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactNo" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contactNo" name="contactNo" placeholder="Enter contact number" maxlength="50">
                                </div>
                                <div class="mb-3">
                                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role_id" name="role_id" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">
                            <i class="bi bi-pencil me-2"></i>
                            Edit User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editUsername" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editUsername" name="username" maxlength="50" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editFName" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editFName" name="f_name" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editMName" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="editMName" name="m_name" maxlength="100">
                                </div>
                                <div class="mb-3">
                                    <label for="editLName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editLName" name="l_name" maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="editEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editContactNo" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="editContactNo" name="contactNo" maxlength="50">
                                </div>
                                <div class="mb-3">
                                    <label for="editRoleId" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editRoleId" name="role_id" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="editPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="editPassword" name="password" placeholder="Leave blank to keep current password">
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                                <div class="mb-3">
                                    <label for="editPasswordConfirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="editPasswordConfirmation" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal - Compact -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel">
                    <i class="bi bi-person-circle me-2"></i>
                    User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- User Info in a more compact list -->
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Username:</small>
                        <span class="fw-semibold" id="viewUsername"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Full Name:</small>
                        <span class="fw-semibold" id="viewFullName"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Email:</small>
                        <span class="fw-semibold" id="viewEmail"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Contact:</small>
                        <span class="fw-semibold" id="viewContactNo">N/A</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Role:</small>
                        <span class="fw-semibold" id="viewRole"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Status:</small>
                        <span class="badge" id="viewStatusBadge"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Created:</small>
                        <span class="fw-semibold" id="viewCreatedAt"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <small class="text-muted">Updated:</small>
                        <span class="fw-semibold" id="viewUpdatedAt"></span>
                    </div>
                </div>

                <!-- Archive Info -->
                <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded" id="archiveInfo" style="display: none;">
                    <small class="text-muted d-block">Archive Information</small>
                    <div class="d-flex justify-content-between">
                        <small>Date:</small>
                        <small class="fw-semibold" id="viewDateDisabled"></small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small>By:</small>
                        <small class="fw-semibold" id="viewDisabledBy"></small>
                    </div>
                </div>

                <!-- Password -->
                <div class="mt-3">
                    <small class="text-muted">Password</small>
                    <div class="input-group input-group-sm mt-1">
                        <input type="password" class="form-control" id="viewPassword" readonly value="••••••••">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
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
    <div class="modal fade" id="archiveUserModal" tabindex="-1" aria-labelledby="archiveUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="archiveUserForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="archiveUserModalLabel">
                            <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                            Confirm Archive
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-archive text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to archive this user?</h5>
                            <p class="text-muted">User: <strong id="archiveUserName"></strong></p>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Archived users cannot log in to the system but their data is preserved.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Archive User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreUserModal" tabindex="-1" aria-labelledby="restoreUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="restoreUserForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="restoreUserModalLabel">
                            <i class="bi bi-arrow-clockwise me-2 text-success"></i>
                            Confirm Restore
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-arrow-clockwise text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to restore this user?</h5>
                            <p class="text-muted">User: <strong id="restoreUserName"></strong></p>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                The user will be able to log in to the system again.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Restore User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Edit User
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                
                fetch(`/users/${userId}/edit`)
                    .then(response => response.json())
                    .then(user => {
                        document.getElementById('editUsername').value = user.username;
                        document.getElementById('editFName').value = user.f_name;
                        document.getElementById('editMName').value = user.m_name || '';
                        document.getElementById('editLName').value = user.l_name;
                        document.getElementById('editEmail').value = user.email;
                        document.getElementById('editContactNo').value = user.contactNo || '';
                        document.getElementById('editRoleId').value = user.role_id;
                        
                        document.getElementById('editUserForm').action = `/users/${userId}`;
                        
                        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                        modal.show();
                    });
            });
        });
        
        // View User
        document.querySelectorAll('.view-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                
                fetch(`/users/${userId}`)
                    .then(response => response.json())
                    .then(user => {
                        document.getElementById('viewUsername').textContent = user.username;
                        document.getElementById('viewFullName').textContent = user.full_name;
                        document.getElementById('viewEmail').textContent = user.email;
                        document.getElementById('viewContactNo').textContent = user.contactNo || 'N/A';
                        document.getElementById('viewRole').textContent = user.role.name;
                        document.getElementById('viewCreatedAt').textContent = new Date(user.created_at).toLocaleString();
                        document.getElementById('viewUpdatedAt').textContent = new Date(user.updated_at).toLocaleString();
                        
                        // Status
                        const statusBadge = document.getElementById('viewStatusBadge');
                        if (user.is_active) {
                            statusBadge.textContent = 'Active';
                            statusBadge.className = 'badge bg-success';
                            document.getElementById('archiveInfo').style.display = 'none';
                        } else {
                            statusBadge.textContent = 'Archived';
                            statusBadge.className = 'badge bg-warning';
                            document.getElementById('archiveInfo').style.display = 'block';
                            document.getElementById('viewDateDisabled').textContent = user.date_disabled ? new Date(user.date_disabled).toLocaleString() : 'N/A';
                            document.getElementById('viewDisabledBy').textContent = user.disabled_by ? user.disabled_by.full_name : 'N/A';
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                        modal.show();
                    });
            });
        });
        
        // Archive User
        document.querySelectorAll('.archive-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                
                document.getElementById('archiveUserName').textContent = userName;
                document.getElementById('archiveUserForm').action = `/users/${userId}/archive`;
                
                const modal = new bootstrap.Modal(document.getElementById('archiveUserModal'));
                modal.show();
            });
        });
        
        // Restore User
        document.querySelectorAll('.restore-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                
                document.getElementById('restoreUserName').textContent = userName;
                document.getElementById('restoreUserForm').action = `/users/${userId}/restore`;
                
                const modal = new bootstrap.Modal(document.getElementById('restoreUserModal'));
                modal.show();
            });
        });
        
        // Toggle password visibility in view modal
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('viewPassword');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'bi bi-eye-slash';
                this.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
            } else {
                passwordField.type = 'password';
                icon.className = 'bi bi-eye';
                this.innerHTML = '<i class="bi bi-eye"></i> Show';
            }
        });
        
        // Role search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelects = document.querySelectorAll('select[name="role_id"]');
            roleSelects.forEach(select => {
                const originalOptions = Array.from(select.options);
                
                select.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const filteredOptions = originalOptions.filter(option => 
                        option.text.toLowerCase().includes(searchTerm)
                    );
                    
                    // Clear and repopulate
                    select.innerHTML = '';
                    filteredOptions.forEach(option => {
                        select.appendChild(option.cloneNode(true));
                    });
                });
            });
        });
    </script>
    @endpush
@endsection