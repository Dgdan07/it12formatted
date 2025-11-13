@extends('layouts.app')
@section('title', 'Roles - ATIN Admin')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')

    <div class="page-header">
        <h2 class="mb-0">
            <b>Role Management</b>
        </h2>
    </div>

    <!-- Roles Table -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <!-- Search Bar -->
            <form action="{{ route('roles.index') }}" method="GET" class="d-flex">
                <div class="input-group search-box">
                    <input type="text" class="form-control" name="search" placeholder="Search roles..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
    
            <!-- Add New Role Button -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="bi bi-plus-circle me-1"></i>
                Add New Role
            </button>
        </div>
    
        <div class="table-responsive">
            <!-- Results Count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    @if(request('search'))
                        Displaying {{ $roles->count() }} of {{ $roles->total() }} results for "{{ request('search') }}"
                    @else
                        Displaying {{ $roles->count() }} of {{ $roles->total() }} roles
                    @endif
                </div>
            </div>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Created Date</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>{{ $role->name }}</td>
                        <td class="description-cell" title="{{ $role->description }}">{{ $role->description ?? 'No description' }}</td>
                        <td>{{ $role->created_at->format('Y-m-d') }}</td>
                        <td>{{ $role->updated_at->format('Y-m-d') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning btn-action edit-role" data-id="{{ $role->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action delete-role" data-id="{{ $role->id }}" data-name="{{ $role->name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            No roles found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $roles->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRoleModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add New Role
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="roleName" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roleName" name="name" placeholder="Enter role name"  maxlength="50" required>
                            <div class="form-text">Maximum 50 characters</div>
                        </div>
                        <div class="mb-3">
                            <label for="roleDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="roleDescription" name="description" rows="3" placeholder="Enter role description" maxlength="255"></textarea>
                            <div class="form-text">Maximum 255 characters</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editRoleForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRoleModalLabel">
                            <i class="bi bi-pencil me-2"></i>
                            Edit Role
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editRoleName" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editRoleName" name="name" maxlength="50" required>
                            <div class="form-text">Maximum 50 characters</div>
                        </div>
                        <div class="mb-3">
                            <label for="editRoleDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editRoleDescription" name="description" rows="3" maxlength="255"></textarea>
                            <div class="form-text">Maximum 255 characters</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteRoleForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteRoleModalLabel">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Are you sure you want to delete this role?</h5>
                            <p class="text-muted">Role: <strong id="deleteRoleName"></strong></p>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> This action cannot be undone. Users associated with this role may be affected.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
    
    <script>
        // Edit Role
        document.querySelectorAll('.edit-role').forEach(button => {
            button.addEventListener('click', function() {
                const roleId = this.getAttribute('data-id');
                
                fetch(`/roles/${roleId}/edit`)
                    .then(response => response.json())
                    .then(role => {
                        document.getElementById('editRoleName').value = role.name;
                        document.getElementById('editRoleDescription').value = role.description || '';
                        document.getElementById('editRoleForm').action = `/roles/${roleId}`;
                        
                        const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
                        modal.show();
                    });
            });
        });
        
        // Delete Role
        document.querySelectorAll('.delete-role').forEach(button => {
            button.addEventListener('click', function() {
                const roleId = this.getAttribute('data-id');
                const roleName = this.getAttribute('data-name');
                
                document.getElementById('deleteRoleName').textContent = roleName;
                document.getElementById('deleteRoleForm').action = `/roles/${roleId}`;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
                modal.show();
            });
        });
    </script>
    @endpush
@endsection