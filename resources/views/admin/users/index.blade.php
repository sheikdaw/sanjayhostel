@extends('layouts.office')

@section('title', 'User Management')
@section('page_title', 'User Management')

@push('styles')
<style>
    .user-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        position: relative;
    }
    .user-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
    .user-header {
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--sanjay-gold);
        color: var(--sanjay-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .user-body { padding: 1rem 1.25rem; }
    .user-email {
        font-size: 0.75rem;
        color: #6b7280;
    }
    .user-phone {
        font-size: 0.75rem;
        color: #6b7280;
    }
    .user-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .user-role-badge.admin { background: #fee2e2; color: #991b1b; }
    .user-role-badge.account { background: #fef3c7; color: #92400e; }
    .user-role-badge.stay { background: #f3f4f6; color: #4b5563; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
    }
    .status-badge:hover { opacity: 0.8; transform: scale(1.05); }
    .status-badge.active { background: #dcfce7; color: #166534; }
    .status-badge.inactive { background: #fee2e2; color: #991b1b; }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.active .dot { background: #22c55e; }
    .status-badge.inactive .dot { background: #ef4444; }
    .btn-action {
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: white;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
    .btn-action:hover { background: #f3f4f6; }
    .btn-action.text-danger:hover { background: #fee2e2; border-color: #fca5a5; }
    .btn-action.text-primary:hover { background: #e3f2fd; border-color: #90caf9; }
    .user-hostel-tag {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 4px;
        font-size: 0.6rem;
        background: #f3f4f6;
        color: #4b5563;
        margin: 2px;
    }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header {
        background: var(--sanjay-primary);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 1rem 1.5rem;
    }
    .modal-header .btn-close { filter: brightness(0) invert(1); }
    .modal-body { padding: 1.5rem; }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; }
    .rv-input-box {
        position: relative;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fafafa;
        transition: all 0.2s;
    }
    .rv-input-box:focus-within {
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
        background: white;
    }
    .rv-input-box.is-invalid { border-color: #dc2626; }
    .rv-input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.9rem;
        pointer-events: none;
    }
    .rv-input {
        width: 100%;
        padding: 0.6rem 0.8rem 0.6rem 2.4rem;
        border: none;
        background: transparent;
        outline: none;
        font-size: 0.85rem;
        color: #1f2937;
    }
    select.rv-input { appearance: none; padding-right: 2rem; cursor: pointer; }
    select.rv-input[multiple] { min-height: 100px; }
    select.rv-input[multiple] option { padding: 0.3rem 0.5rem; }
    .form-label { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; }
    .form-label .required { color: #dc2626; margin-left: 2px; }
    .invalid-feedback { font-size: 0.75rem; color: #dc2626; margin-top: 0.25rem; }
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-state i { font-size: 4rem; color: #d1d5db; margin-bottom: 1rem; }
    .toast-container {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    }
    .toast-custom {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        border-left: 4px solid #10b981;
        margin-bottom: 0.75rem;
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .toast-custom.error { border-left-color: #dc2626; }
    .toast-custom .message { flex: 1; font-size: 0.85rem; color: #1f2937; }
    .toast-custom .close-btn {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0 0.25rem;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        text-align: center;
        transition: all 0.3s;
    }
    .stat-card:hover {
        border-color: var(--sanjay-gold);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--sanjay-primary);
    }
    .stat-card .label {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: uppercase;
    }
    .stat-card .icon {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }
    .stat-card.admin .number { color: #dc2626; }
    .stat-card.account .number { color: #f59e0b; }
    .stat-card.stay .number { color: #22c55e; }
    .stat-card.active .number { color: #22c55e; }
    .stat-card.inactive .number { color: #ef4444; }
    .stat-card.total .number { color: var(--sanjay-primary); }
    .filter-section {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 1.5rem;
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }
    .filter-section .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .filter-section select {
        padding: 0.35rem 0.8rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        font-size: 0.8rem;
        background: white;
    }
    .search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .search-box input {
        width: 100%;
        padding: 0.35rem 0.8rem 0.35rem 2rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        font-size: 0.8rem;
        background: white;
    }
    .search-box i {
        position: absolute;
        left: 0.6rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
    .password-hint {
        font-size: 0.7rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">User Management</h1>
        <p class="ol-page-sub">Manage system users and their roles</p>
    </div>
    <div>
        <button type="button" class="rv-submit" id="addUserBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Add User
        </button>
    </div>
</div>

{{-- Statistics --}}
<div class="stats-grid">
    <div class="stat-card total">
        <div class="icon">👥</div>
        <div class="number">{{ $stats['total'] }}</div>
        <div class="label">Total Users</div>
    </div>
    <div class="stat-card active">
        <div class="icon">✅</div>
        <div class="number">{{ $stats['active'] }}</div>
        <div class="label">Active</div>
    </div>
    <div class="stat-card inactive">
        <div class="icon">❌</div>
        <div class="number">{{ $stats['inactive'] }}</div>
        <div class="label">Inactive</div>
    </div>
    <div class="stat-card admin">
        <div class="icon">🛡️</div>
        <div class="number">{{ $stats['admin'] }}</div>
        <div class="label">Admins</div>
    </div>
    <div class="stat-card account">
        <div class="icon">💰</div>
        <div class="number">{{ $stats['account'] }}</div>
        <div class="label">Accounts</div>
    </div>
    <div class="stat-card stay">
        <div class="icon">👤</div>
        <div class="number">{{ $stats['stay'] }}</div>
        <div class="label">Residents</div>
    </div>
</div>

{{-- Filter Section --}}
<div class="filter-section">
    <div class="filter-group">
        <label style="font-size:0.8rem; font-weight:600;">Filter:</label>
    </div>
    <div class="filter-group">
        <select id="filterRole">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="account">Account</option>
            <option value="stay">Resident</option>
        </select>
    </div>
    <div class="filter-group">
        <select id="filterStatus">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>
    <div class="filter-group">
        <select id="filterHostel">
            <option value="">All Hostels</option>
            @foreach($hostels as $hostel)
                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchUser" placeholder="Search by name, email, phone...">
    </div>
    <button class="btn-action" onclick="clearFilters()" style="padding:0.35rem 1rem;">
        <i class="bi bi-arrow-counterclockwise"></i> Clear
    </button>
</div>

{{-- Users Grid --}}
<div id="usersContainer">
    @if($users->count() > 0)
        <div class="row g-4" id="usersGrid">
            @foreach($users as $user)
                <div class="col-xl-3 col-lg-4 col-md-6" data-id="{{ $user->id }}"
                     data-role="{{ $user->role }}"
                     data-status="{{ $user->is_active }}"
                     data-name="{{ strtolower($user->name) }}"
                     data-email="{{ strtolower($user->email) }}"
                     data-phone="{{ $user->phone }}">
                    <div class="user-card">
                        <div class="user-header">
                            <div class="user-avatar">
                                {{ $user->initials }}
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:600; font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $user->name }}
                                </div>
                                <div style="font-size:0.7rem; opacity:0.8;">
                                    <i class="{{ $user->role_icon }}"></i>
                                    <span class="user-role-badge {{ $user->role }}">
                                        {{ $user->role_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="user-body">
                            <div class="user-email">
                                <i class="bi bi-envelope"></i> {{ $user->email }}
                            </div>
                            <div class="user-phone">
                                <i class="bi bi-phone"></i> {{ $user->phone }}
                            </div>
                            @php
                                $assignedHostels = $user->getAssignedHostels();
                            @endphp
                            @if($assignedHostels->count() > 0)
                                <div style="margin-top:0.5rem;">
                                    @foreach($assignedHostels as $hostel)
                                        <span class="user-hostel-tag">
                                            <i class="bi bi-building"></i> {{ $hostel->hostel_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div style="font-size:0.7rem; color:#9ca3af; margin-top:0.5rem;">
                                    <i class="bi bi-building"></i> No hostels assigned
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <button class="status-badge {{ $user->status_badge }}" onclick="toggleStatus({{ $user->id }})">
                                    <span class="dot"></span>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </button>
                                <div class="d-flex gap-1">
                                    <button class="btn-action text-primary" onclick="editUser({{ $user->id }})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($user->role != 'admin')
                                        <button class="btn-action text-danger" onclick="deleteUser({{ $user->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="ds-card">
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h5>No users found</h5>
                <p class="text-muted">Create user accounts with different roles.</p>
                <button type="button" class="rv-submit" onclick="openAddModal()" style="width:auto; display:inline-flex; padding:0 1.5rem; height:38px; border-radius:9px; align-items:center; gap:6px; animation:none;">
                    <i class="bi bi-plus-circle"></i>
                    Add User
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                @csrf
                <input type="hidden" id="editId" name="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-person rv-input-icon"></i>
                                <input type="text" name="name" id="name" class="rv-input" placeholder="Full name" required>
                            </div>
                            <div class="invalid-feedback" id="name_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-envelope rv-input-icon"></i>
                                <input type="email" name="email" id="email" class="rv-input" placeholder="user@email.com" required>
                            </div>
                            <div class="invalid-feedback" id="email_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-phone rv-input-icon"></i>
                                <input type="text" name="phone" id="phone" class="rv-input" placeholder="+91 98765 43210" required>
                            </div>
                            <div class="invalid-feedback" id="phone_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-shield rv-input-icon"></i>
                                <select name="role" id="role" class="rv-input" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">🛡️ Admin</option>
                                    <option value="account">💰 Account</option>
                                    <option value="stay">👤 Resident</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="role_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="required" id="passwordRequired">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-lock rv-input-icon"></i>
                                <input type="password" name="password" id="password" class="rv-input" placeholder="Min 8 characters">
                            </div>
                            <div class="invalid-feedback" id="password_error"></div>
                            <div class="password-hint" id="passwordHint">Minimum 8 characters required</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="required" id="confirmRequired">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-lock rv-input-icon"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="rv-input" placeholder="Confirm password">
                            </div>
                            <div class="invalid-feedback" id="password_confirmation_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Assign Hostels</label>
                            <div class="rv-input-box">
                                <i class="bi bi-building rv-input-icon" style="top:16px; transform:none;"></i>
                                <select name="hostel_ids[]" id="hostel_ids" class="rv-input" multiple style="min-height:100px;">
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }} ({{ $hostel->hostel_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="font-size:0.7rem; color:#6b7280; margin-top:4px;">
                                Hold Ctrl/Cmd to select multiple hostels. Leave empty for admin users.
                            </div>
                            <div class="invalid-feedback" id="hostel_ids_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <div class="rv-input-box">
                                <i class="bi bi-toggle-on rv-input-icon"></i>
                                <select name="is_active" id="is_active" class="rv-input">
                                    <option value="1">✅ Active</option>
                                    <option value="0">❌ Inactive</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="is_active_error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="saveBtn" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; display:inline-flex; align-items:center; gap:6px; animation:none;">
                        <i class="bi bi-check-circle"></i>
                        <span id="saveBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="flashMessageContainer"></div>

<script>
$(document).ready(function() {
    var userModal = new bootstrap.Modal(document.getElementById('userModal'), {
        backdrop: 'static',
        keyboard: true
    });

    $('#addUserBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    $('#userModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Role change handling for password requirement
    $('#role').on('change', function() {
        updatePasswordRequirement();
    });

    // Search functionality
    let searchTimeout;
    $('#searchUser').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });

    // Filter changes
    $('#filterRole, #filterStatus, #filterHostel').on('change', function() {
        applyFilters();
    });
});

function updatePasswordRequirement() {
    var isEdit = $('#editId').val() !== '';
    var role = $('#role').val();

    if (isEdit) {
        $('#passwordRequired').html('<span style="color:#6b7280; font-weight:400;">(Optional)</span>');
        $('#confirmRequired').html('<span style="color:#6b7280; font-weight:400;">(Optional)</span>');
        $('#password').prop('required', false);
        $('#password_confirmation').prop('required', false);
        $('#passwordHint').text('Leave blank to keep current password');
    } else {
        $('#passwordRequired').text('*');
        $('#confirmRequired').text('*');
        $('#password').prop('required', true);
        $('#password_confirmation').prop('required', true);
        $('#passwordHint').text('Minimum 8 characters required');
    }
}

function applyFilters() {
    var role = $('#filterRole').val();
    var status = $('#filterStatus').val();
    var hostel = $('#filterHostel').val();
    var search = $('#searchUser').val().toLowerCase();

    $('#usersGrid .col-xl-3').each(function() {
        var show = true;
        var userRole = $(this).data('role');
        var userStatus = $(this).data('status');
        var userName = $(this).data('name') || '';
        var userEmail = $(this).data('email') || '';
        var userPhone = $(this).data('phone') || '';

        if (role && userRole !== role) show = false;
        if (status !== '' && userStatus != status) show = false;
        if (search) {
            var match = userName.includes(search) ||
                       userEmail.includes(search) ||
                       userPhone.includes(search);
            if (!match) show = false;
        }

        $(this).toggle(show);
    });
}

function clearFilters() {
    $('#filterRole, #filterStatus, #filterHostel').val('');
    $('#searchUser').val('');
    applyFilters();
}

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add User';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('#password').prop('required', true);
    $('#password_confirmation').prop('required', true);
    $('#passwordRequired').text('*');
    $('#confirmRequired').text('*');
    $('#passwordHint').text('Minimum 8 characters required');
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

function resetForm() {
    const form = document.getElementById('userForm');
    form.reset();
    $('#hostel_ids').val([]);
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add User';
}

function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.users.store') }}";
    let formData = new FormData(document.getElementById('userForm'));

    if (id) {
        url = "{{ url('admin/users') }}/" + id;
        formData.append('_method', 'PUT');
    }

    // If password is empty in edit mode, remove it from form data
    if (id && !formData.get('password')) {
        formData.delete('password');
        formData.delete('password_confirmation');
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#saveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
                if (modal) modal.hide();
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                if (xhr.responseJSON.message) {
                    showToast(xhr.responseJSON.message, 'error');
                } else {
                    $.each(errors, function(field, messages) {
                        $('#' + field).closest('.rv-input-box').addClass('is-invalid');
                        $('#' + field + '_error').text(messages[0]);
                    });
                    showToast('Please fix validation errors', 'error');
                }
            } else {
                showToast(xhr.responseJSON?.message || 'Something went wrong!', 'error');
            }
        },
        complete: function() {
            let id = document.getElementById('editId').value;
            let text = id ? 'Update' : 'Save';
            $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">' + text + '</span>');
        }
    });
}

function editUser(id) {
    $.ajax({
        url: "{{ url('admin/users') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('editId').value = data.id;
                document.getElementById('name').value = data.name;
                document.getElementById('email').value = data.email;
                document.getElementById('phone').value = data.phone;
                document.getElementById('role').value = data.role;
                document.getElementById('is_active').value = data.is_active ? '1' : '0';
                document.getElementById('saveBtnText').textContent = 'Update';

                // Update password requirement for edit mode
                updatePasswordRequirement();

                // Select assigned hostels
                if (data.hostel_ids) {
                    $('#hostel_ids').val(data.hostel_ids);
                }

                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('userModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            showToast('Failed to load user data', 'error');
        }
    });
}

function deleteUser(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/users') }}/" + id,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to delete!', 'error');
                }
            });
        }
    });
}

function toggleStatus(id) {
    Swal.fire({
        title: 'Toggle Status?',
        text: "Change user status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/users') }}/" + id + "/toggle-status",
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to update status!', 'error');
                }
            });
        }
    });
}

function showToast(message, type = 'success') {
    let container = document.getElementById('flashMessageContainer');
    if (!container) {
        const newContainer = document.createElement('div');
        newContainer.id = 'flashMessageContainer';
        newContainer.className = 'toast-container';
        document.body.appendChild(newContainer);
        container = newContainer;
    }

    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    const color = type === 'success' ? '#10b981' : '#dc2626';

    const toast = document.createElement('div');
    toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '');
    toast.innerHTML = `
        <i class="bi ${icon}" style="color: ${color}; font-size: 1.25rem;"></i>
        <div class="message">${message}</div>
        <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}
</script>

@endsection
