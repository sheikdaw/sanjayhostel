{{-- resources/views/admin/employees/index.blade.php --}}
@extends('layouts.office')

@section('title', 'Employee Management')
@section('page_title', 'Employee Management')

@push('styles')
<style>
    .employee-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        position: relative;
    }
    .employee-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
    .employee-card .card-checkbox {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 1;
    }
    .employee-card .card-checkbox input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .employee-card .status-badge-top {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.6rem;
        font-weight: 600;
        z-index: 1;
    }
    .status-badge-top.active { background: #dcfce7; color: #166534; }
    .status-badge-top.inactive { background: #fee2e2; color: #991b1b; }
    .employee-header {
        padding: 1rem 1.25rem;
        padding-left: 3rem;
        background: var(--sanjay-primary);
        color: white;
    }
    .employee-body { padding: 1rem 1.25rem; }
    .employee-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--sanjay-primary);
    }
    .employee-code {
        font-size: 0.75rem;
        color: #6b7280;
    }
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
    .employee-info {
        font-size: 0.75rem;
        color: #6b7280;
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: 6px;
        margin-top: 0.5rem;
    }
    .employee-info .label {
        color: #9ca3af;
        font-size: 0.6rem;
        text-transform: uppercase;
    }
    .employee-info .value {
        font-weight: 600;
        color: #1f2937;
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
    .filter-section {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    .filter-section .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .filter-section select {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        font-size: 0.8rem;
        background: white;
    }
    .employee-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .employee-stat {
        text-align: center;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .employee-stat .number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .employee-stat .label {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: uppercase;
    }
    .employee-stat.active .number { color: #22c55e; }
    .employee-stat.inactive .number { color: #ef4444; }
    .employee-stat.total .number { color: var(--sanjay-primary); }
    .employee-stat.salary .number { color: #92400e; }
    .employee-stat.salary { background: linear-gradient(135deg, #fef3c7, #fde68a); }
    .bulk-actions {
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }
    .bulk-actions.show { display: flex; }
    .bulk-actions .count {
        font-weight: 600;
        color: var(--sanjay-primary);
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
    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }
    .pagination-wrapper .pagination {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Employee Management</h1>
        <p class="ol-page-sub">Manage staff members and their details</p>
        @if($user->role != 'admin')
            <p class="ol-page-sub" style="color: var(--sanjay-gold); font-size:0.8rem;">
                <i class="bi bi-info-circle"></i> You have access to {{ $hostels->count() }} hostel(s)
            </p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="rv-submit" onclick="exportData()" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-download"></i>
            Export
        </button>
        <button type="button" class="rv-submit" id="addEmployeeBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Add Employee
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="employee-stats">
    <div class="employee-stat total">
        <div class="number">{{ $stats['total'] }}</div>
        <div class="label">Total Employees</div>
    </div>
    <div class="employee-stat active">
        <div class="number">{{ $stats['active'] }}</div>
        <div class="label">Active</div>
    </div>
    <div class="employee-stat inactive">
        <div class="number">{{ $stats['inactive'] }}</div>
        <div class="label">Inactive</div>
    </div>
    <div class="employee-stat salary">
        <div class="number">₹{{ number_format($stats['total_salary']) }}</div>
        <div class="label">Total Salary</div>
    </div>
</div>

{{-- Bulk Actions --}}
<div class="bulk-actions" id="bulkActions">
    <span><i class="bi bi-check-square"></i> <span class="count" id="selectedCount">0</span> selected</span>
    <span style="color:#6b7280;">|</span>
    <select id="bulkStatusSelect" style="padding:0.2rem 0.5rem; border-radius:4px; border:1px solid #d1d5db; font-size:0.75rem;">
        <option value="">Change Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
    <button class="btn-action text-primary" onclick="bulkStatusUpdate()" title="Update Status">
        <i class="bi bi-check-circle"></i> Apply
    </button>
    <button class="btn-action text-danger" onclick="bulkDelete()" title="Delete Selected">
        <i class="bi bi-trash"></i> Delete
    </button>
    <button class="btn-action" onclick="clearSelection()" title="Clear Selection">
        <i class="bi bi-x"></i> Clear
    </button>
</div>

{{-- Filter Section --}}
<div class="filter-section">
    <div class="filter-group">
        <label style="font-size:0.8rem; font-weight:600;">Filter:</label>
    </div>
    <div class="filter-group">
        <select id="filterStatus">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
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
        <input type="text" id="searchEmployee" placeholder="Search by name, code, mobile...">
    </div>
    <button class="btn-action" onclick="clearFilters()" style="padding:0.4rem 1rem;">
        <i class="bi bi-arrow-counterclockwise"></i> Clear
    </button>
</div>

{{-- Employees Grid --}}
<div id="employeesContainer">
    @if($employees->count() > 0)
        <div class="row g-4" id="employeesGrid">
            @foreach($employees as $employee)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" 
                     data-id="{{ $employee->id }}"
                     data-status="{{ $employee->status }}"
                     data-hostel="{{ $employee->hostel_id }}"
                     data-name="{{ strtolower($employee->name) }}"
                     data-code="{{ strtolower($employee->employee_code) }}"
                     data-mobile="{{ $employee->mobile ?? '' }}">
                    <div class="employee-card">
                        <div class="card-checkbox">
                            <input type="checkbox" class="employee-checkbox" value="{{ $employee->id }}" onclick="updateBulkActions()">
                        </div>
                        <span class="status-badge-top {{ $employee->status }}">
                            {{ ucfirst($employee->status) }}
                        </span>
                        <div class="employee-header">
                            <div style="font-size:0.6rem; opacity:0.7;">{{ $employee->hostel->hostel_code ?? 'N/A' }}</div>
                            <div style="font-size:0.8rem; font-weight:600;">{{ $employee->employee_code }}</div>
                        </div>
                        <div class="employee-body">
                            <div class="employee-name">{{ $employee->name }}</div>
                            <div class="employee-code">
                                <i class="bi bi-person-badge"></i> {{ $employee->designation ?? 'No Designation' }}
                            </div>
                            
                            <div class="employee-info">
                                <div class="row g-1">
                                    <div class="col-6">
                                        <div class="label">Department</div>
                                        <div class="value">{{ $employee->department ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="label">Mobile</div>
                                        <div class="value">{{ $employee->mobile ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="label">Joining Date</div>
                                        <div class="value">{{ $employee->joining_date ? date('d M Y', strtotime($employee->joining_date)) : '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="label">Salary</div>
                                        <div class="value">₹{{ number_format($employee->salary ?? 0, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="status-badge {{ $employee->status }}" onclick="toggleStatus({{ $employee->id }})">
                                    <span class="dot"></span>
                                    {{ ucfirst($employee->status) }}
                                </button>
                                <div class="d-flex gap-1">
                                    <button class="btn-action text-primary" onclick="viewEmployee({{ $employee->id }})" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn-action text-primary" onclick="editEmployee({{ $employee->id }})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action text-danger" onclick="deleteEmployee({{ $employee->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="pagination-wrapper">
            {{ $employees->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h5>No employees found</h5>
                <p class="text-muted">Add employees to manage your staff.</p>
                <button type="button" class="rv-submit" onclick="openAddModal()" style="width:auto; display:inline-flex; padding:0 1.5rem; height:38px; border-radius:9px; align-items:center; gap:6px; animation:none;">
                    <i class="bi bi-plus-circle"></i>
                    Add Employee
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="employeeForm">
                @csrf
                <input type="hidden" id="editId" name="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-hash rv-input-icon"></i>
                                <input type="text" name="employee_code" id="employee_code" class="rv-input" placeholder="EMP001" required>
                            </div>
                            <div class="invalid-feedback" id="employee_code_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-person rv-input-icon"></i>
                                <input type="text" name="name" id="name" class="rv-input" placeholder="John Doe" required>
                            </div>
                            <div class="invalid-feedback" id="name_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hostel</label>
                            <div class="rv-input-box">
                                <i class="bi bi-building rv-input-icon"></i>
                                <select name="hostel_id" id="hostel_id" class="rv-input">
                                    <option value="">Select Hostel</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="invalid-feedback" id="hostel_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <div class="rv-input-box">
                                <i class="bi bi-phone rv-input-icon"></i>
                                <input type="text" name="mobile" id="mobile" class="rv-input" placeholder="9876543210">
                            </div>
                            <div class="invalid-feedback" id="mobile_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <div class="rv-input-box">
                                <i class="bi bi-building rv-input-icon"></i>
                                <input type="text" name="department" id="department" class="rv-input" placeholder="IT">
                            </div>
                            <div class="invalid-feedback" id="department_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Designation</label>
                            <div class="rv-input-box">
                                <i class="bi bi-briefcase rv-input-icon"></i>
                                <input type="text" name="designation" id="designation" class="rv-input" placeholder="Manager">
                            </div>
                            <div class="invalid-feedback" id="designation_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Joining Date</label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar rv-input-icon"></i>
                                <input type="date" name="joining_date" id="joining_date" class="rv-input">
                            </div>
                            <div class="invalid-feedback" id="joining_date_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Salary <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" step="0.01" name="salary" id="salary" class="rv-input" placeholder="0.00" min="0" required>
                            </div>
                            <div class="invalid-feedback" id="salary_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Advance Amount</label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" step="0.01" name="advance_amount" id="advance_amount" class="rv-input" placeholder="0.00" min="0">
                            </div>
                            <div class="invalid-feedback" id="advance_amount_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Advance Deduct</label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" step="0.01" name="advance_deduct" id="advance_deduct" class="rv-input" placeholder="0.00" min="0">
                            </div>
                            <div class="invalid-feedback" id="advance_deduct_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-toggle-on rv-input-icon"></i>
                                <select name="status" id="status" class="rv-input" required>
                                    <option value="active">✅ Active</option>
                                    <option value="inactive">❌ Inactive</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="status_error"></div>
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

{{-- View Modal --}}
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewContent">
                <div class="text-center py-4">
                    <i class="bi bi-spinner bi-spin" style="font-size: 2rem;"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="flashMessageContainer"></div>

<script>
$(document).ready(function() {
    var employeeModal = new bootstrap.Modal(document.getElementById('employeeModal'), {
        backdrop: 'static',
        keyboard: true
    });
    var viewModal = new bootstrap.Modal(document.getElementById('viewModal'), {
        backdrop: 'static',
        keyboard: true
    });

    $('#addEmployeeBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    $('#employeeModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    $('#employeeForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Filters
    $('#filterStatus, #filterHostel').on('change', function() {
        applyFilters();
    });

    // Search
    let searchTimeout;
    $('#searchEmployee').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
});

function applyFilters() {
    var status = $('#filterStatus').val();
    var hostel = $('#filterHostel').val();
    var search = $('#searchEmployee').val().toLowerCase();

    $('#employeesGrid .col-xl-3').each(function() {
        var show = true;
        var empStatus = $(this).data('status');
        var empHostel = $(this).data('hostel');
        var empName = $(this).data('name') || '';
        var empCode = $(this).data('code') || '';
        var empMobile = $(this).data('mobile') || '';

        if (status && empStatus !== status) show = false;
        if (hostel && empHostel != hostel) show = false;
        if (search) {
            var match = empName.includes(search) || empCode.includes(search) || empMobile.includes(search);
            if (!match) show = false;
        }

        $(this).toggle(show);
    });
}

function clearFilters() {
    $('#filterStatus, #filterHostel').val('');
    $('#searchEmployee').val('');
    applyFilters();
}

function updateBulkActions() {
    var checked = $('.employee-checkbox:checked');
    var count = checked.length;

    if (count > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(count);
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    $('.employee-checkbox').prop('checked', false);
    updateBulkActions();
}

function getSelectedIds() {
    var ids = [];
    $('.employee-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function bulkStatusUpdate() {
    var ids = getSelectedIds();
    var status = $('#bulkStatusSelect').val();

    if (ids.length === 0 || !status) {
        showToast('Please select employees and a status', 'error');
        return;
    }

    Swal.fire({
        title: 'Update Status?',
        text: "Are you sure you want to update " + ids.length + " employees to " + status + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, update them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.employees.bulk-status') }}",
                type: 'POST',
                data: {
                    ids: ids,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to update!', 'error');
                    }
                }
            });
        }
    });
}

function bulkDelete() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;

    Swal.fire({
        title: 'Delete Employees?',
        text: "Are you sure you want to delete " + ids.length + " employees? This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.employees.bulk-delete') }}",
                type: 'POST',
                data: {
                    ids: ids,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to delete!', 'error');
                    }
                }
            });
        }
    });
}

function exportData() {
    var params = new URLSearchParams(window.location.search);
    var url = "{{ route('admin.employees.export') }}";
    var queryString = params.toString();
    if (queryString) {
        url += '?' + queryString;
    }
    window.location.href = url;
}

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Employee';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    modal.show();
}

function resetForm() {
    const form = document.getElementById('employeeForm');
    form.reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Employee';
}

function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.employees.store') }}";
    let formData = new FormData(document.getElementById('employeeForm'));

    if (id) {
        url = "{{ url('admin/employees') }}/" + id;
        formData.append('_method', 'PUT');
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
                var modal = bootstrap.Modal.getInstance(document.getElementById('employeeModal'));
                if (modal) modal.hide();
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else if (xhr.status === 422) {
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

function editEmployee(id) {
    $.ajax({
        url: "{{ url('admin/employees') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Employee';
                document.getElementById('editId').value = data.id;
                document.getElementById('employee_code').value = data.employee_code;
                document.getElementById('name').value = data.name;
                document.getElementById('hostel_id').value = data.hostel_id || '';
                document.getElementById('department').value = data.department || '';
                document.getElementById('designation').value = data.designation || '';
                document.getElementById('mobile').value = data.mobile || '';
                document.getElementById('joining_date').value = data.joining_date || '';
                document.getElementById('salary').value = data.salary || 0;
                document.getElementById('advance_amount').value = data.advance_amount || 0;
                document.getElementById('advance_deduct').value = data.advance_deduct || 0;
                document.getElementById('status').value = data.status;
                document.getElementById('saveBtnText').textContent = 'Update';
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('employeeModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load employee data', 'error');
            }
        }
    });
}

function viewEmployee(id) {
    $('#viewContent').html('<div class="text-center py-4"><i class="bi bi-spinner bi-spin" style="font-size: 2rem;"></i></div>');
    var modal = new bootstrap.Modal(document.getElementById('viewModal'));
    modal.show();

    $.ajax({
        url: "{{ url('admin/employees') }}/" + id,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                let html = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Employee Code</div>
                                <div class="fw-bold">${data.employee_code}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Full Name</div>
                                <div class="fw-bold">${data.name}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Hostel</div>
                                <div class="fw-bold">${data.hostel?.hostel_name || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Mobile</div>
                                <div class="fw-bold">${data.mobile || '-'}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Department</div>
                                <div class="fw-bold">${data.department || '-'}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Designation</div>
                                <div class="fw-bold">${data.designation || '-'}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Joining Date</div>
                                <div class="fw-bold">${data.joining_date ? new Date(data.joining_date).toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'numeric'}) : '-'}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Salary</div>
                                <div class="fw-bold">₹${Number(data.salary).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Advance Amount</div>
                                <div class="fw-bold">₹${Number(data.advance_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Advance Deduct</div>
                                <div class="fw-bold">₹${Number(data.advance_deduct).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card bg-light p-3">
                                <div class="text-muted small">Status</div>
                                <div><span class="status-badge ${data.status}"><span class="dot"></span> ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span></div>
                            </div>
                        </div>
                    </div>
                `;
                $('#viewContent').html(html);
            }
        },
        error: function(xhr) {
            $('#viewContent').html('<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i><br>Failed to load employee details</div>');
        }
    });
}

function deleteEmployee(id) {
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
                url: "{{ url('admin/employees') }}/" + id,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to delete!', 'error');
                    }
                }
            });
        }
    });
}

function toggleStatus(id) {
    Swal.fire({
        title: 'Toggle Status?',
        text: "Change employee status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/employees') }}/" + id + "/toggle-status",
                type: 'POST',
                data: {
                    _method: 'PATCH',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to update status!', 'error');
                    }
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