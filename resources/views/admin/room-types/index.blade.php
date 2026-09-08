@extends('layouts.office')

@section('title', 'Room Type Management')
@section('page_title', 'Room Type Management')

@push('styles')
<style>
    .room-type-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        position: relative;
    }
    .room-type-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
    .room-type-card .card-checkbox {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 1;
    }
    .room-type-card .card-checkbox input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .room-type-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        padding: 1.25rem;
        color: white;
        padding-left: 3rem;
    }
    .sharing-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255,255,255,0.2);
        color: white;
    }
    .rent-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--sanjay-gold);
    }
    .room-type-body { padding: 1rem 1.25rem; }
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
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: white;
        padding: 0.75rem 1rem;
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
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--sanjay-primary);
    }
    .stat-card .label {
        font-size: 0.65rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-card .icon {
        font-size: 1.2rem;
        margin-bottom: 0.25rem;
    }
    .stat-card.active .number { color: #22c55e; }
    .stat-card.inactive .number { color: #ef4444; }
    .stat-card.rent .number { color: #92400e; }
    .stat-card.rent { background: linear-gradient(135deg, #fef3c7, #fde68a); }
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
    .filter-section select,
    .filter-section input {
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
    .hostel-tag {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 4px;
        font-size: 0.6rem;
        background: #e5e7eb;
        color: #4b5563;
    }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Room Type Management</h1>
        <p class="ol-page-sub">Manage room types, sharing configurations, and rent details</p>
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
        <button type="button" class="rv-submit" id="addRoomTypeBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Add Room Type
        </button>
    </div>
</div>

{{-- Statistics --}}
<div class="stats-grid">
    <div class="stat-card total">
        <div class="icon">📊</div>
        <div class="number">{{ $stats['total'] }}</div>
        <div class="label">Total Room Types</div>
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
    <div class="stat-card rent">
        <div class="icon">💰</div>
        <div class="number">₹{{ number_format($stats['total_rent'] ?? 0, 0) }}</div>
        <div class="label">Total Monthly Rent</div>
    </div>
    <div class="stat-card">
        <div class="icon">👥</div>
        <div class="number">{{ $stats['avg_sharing'] ?? 0 }}</div>
        <div class="label">Avg Sharing</div>
    </div>
</div>

{{-- Bulk Actions --}}
<div class="bulk-actions" id="bulkActions">
    <span><i class="bi bi-check-square"></i> <span class="count" id="selectedCount">0</span> selected</span>
    <span style="color:#6b7280;">|</span>
    <button class="btn-action text-success" onclick="bulkActivate()" title="Activate Selected">
        <i class="bi bi-check-circle"></i> Activate
    </button>
    <button class="btn-action text-warning" onclick="bulkDeactivate()" title="Deactivate Selected">
        <i class="bi bi-x-circle"></i> Deactivate
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
    <div class="filter-group">
        <select id="filterSharing">
            <option value="">All Sharing</option>
            <option value="1">1 (Single)</option>
            <option value="2">2 (Double)</option>
            <option value="3">3 (Triple)</option>
            <option value="4">4 (Quad)</option>
            <option value="6">6 (Six)</option>
            <option value="8">8 (Eight)</option>
            <option value="10">10 (Ten)</option>
        </select>
    </div>
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchRoomType" placeholder="Search by name, hostel...">
    </div>
    <button class="btn-action" onclick="clearFilters()" style="padding:0.35rem 1rem;">
        <i class="bi bi-arrow-counterclockwise"></i> Clear
    </button>
</div>

{{-- Room Types Grid --}}
<div id="roomTypesContainer">
    @if($roomTypes->count() > 0)
        <div class="row g-4" id="roomTypesGrid">
            @foreach($roomTypes as $roomType)
                <div class="col-xl-4 col-lg-6" data-id="{{ $roomType->id }}"
                     data-status="{{ $roomType->is_active ? '1' : '0' }}"
                     data-hostel="{{ $roomType->hostel_id }}"
                     data-sharing="{{ $roomType->sharing_count }}"
                     data-name="{{ strtolower($roomType->room_type_name) }}">
                    <div class="room-type-card">
                        <div class="card-checkbox">
                            <input type="checkbox" class="room-type-checkbox" value="{{ $roomType->id }}" onclick="updateBulkActions()">
                        </div>
                        <div class="room-type-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size:0.65rem; opacity:0.7;">
                                        <span class="hostel-tag" style="background:rgba(255,255,255,0.2); color:white;">
                                            {{ $roomType->hostel->hostel_code ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <h5 style="margin: 4px 0 0 0; color: white; font-weight: 700;">{{ $roomType->room_type_name }}</h5>
                                </div>
                                <span class="sharing-badge">
                                    <i class="bi bi-people"></i> {{ $roomType->sharing_count }} Sharing
                                </span>
                            </div>
                        </div>
                        <div class="room-type-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div style="font-size:0.7rem; color:#6b7280;">Monthly Rent</div>
                                    <div class="rent-amount">₹{{ number_format($roomType->monthly_rent, 2) }}</div>
                                </div>
                                <div class="col-6">
                                    <div style="font-size:0.7rem; color:#6b7280;">Deposit</div>
                                    <div style="font-size:1.1rem; font-weight:600; color:var(--sanjay-primary);">₹{{ number_format($roomType->deposit_amount ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="status-badge {{ $roomType->is_active ? 'active' : 'inactive' }}" onclick="toggleStatus({{ $roomType->id }})">
                                    <span class="dot"></span>
                                    {{ $roomType->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                </button>
                                <div class="d-flex gap-1">
                                    <button class="btn-action" onclick="editRoomType({{ $roomType->id }})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action text-danger" onclick="deleteRoomType({{ $roomType->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
                <i class="bi bi-door-open"></i>
                <h5>No room types found</h5>
                <p class="text-muted">Create room types with different sharing configurations.</p>
                <button type="button" class="rv-submit" onclick="openAddModal()" style="width:auto; display:inline-flex; padding:0 1.5rem; height:38px; border-radius:9px; align-items:center; gap:6px; animation:none;">
                    <i class="bi bi-plus-circle"></i>
                    Add Room Type
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="roomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Room Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roomTypeForm">
                @csrf
                <input type="hidden" id="editId" name="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Hostel <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-building rv-input-icon"></i>
                                <select name="hostel_id" id="hostel_id" class="rv-input" required>
                                    <option value="">Select Hostel</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }} ({{ $hostel->hostel_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="invalid-feedback" id="hostel_id_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Room Type Name <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <input type="text" name="room_type_name" id="room_type_name" class="rv-input" placeholder="e.g., Standard, Deluxe, Premium" required>
                            </div>
                            <div class="invalid-feedback" id="room_type_name_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sharing Count <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-people rv-input-icon"></i>
                                <select name="sharing_count" id="sharing_count" class="rv-input" required>
                                    <option value="">Select</option>
                                    <option value="1">1 (Single)</option>
                                    <option value="2">2 (Double)</option>
                                    <option value="3">3 (Triple)</option>
                                    <option value="4">4 (Quad)</option>
                                    <option value="6">6 (Six)</option>
                                    <option value="8">8 (Eight)</option>
                                    <option value="10">10 (Ten)</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="sharing_count_error"></div>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <label class="form-label">Monthly Rent (₹) <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" name="monthly_rent" id="monthly_rent" class="rv-input" placeholder="0.00" step="0.01" min="0" required>
                            </div>
                            <div class="invalid-feedback" id="monthly_rent_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deposit Amount (₹)</label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" name="deposit_amount" id="deposit_amount" class="rv-input" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div class="invalid-feedback" id="deposit_amount_error"></div>
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
    var roomTypeModal = new bootstrap.Modal(document.getElementById('roomTypeModal'), {
        backdrop: 'static',
        keyboard: true
    });

    $('#addRoomTypeBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    $('#roomTypeModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    $('#roomTypeForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Filter changes
    $('#filterStatus, #filterHostel, #filterSharing').on('change', function() {
        applyFilters();
    });

    // Search functionality
    let searchTimeout;
    $('#searchRoomType').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });

    // Select All checkbox
    $('#selectAll').on('change', function() {
        $('.room-type-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActions();
    });
});

function applyFilters() {
    var status = $('#filterStatus').val();
    var hostel = $('#filterHostel').val();
    var sharing = $('#filterSharing').val();
    var search = $('#searchRoomType').val().toLowerCase();

    $('#roomTypesGrid .col-xl-4').each(function() {
        var show = true;
        var itemStatus = $(this).data('status');
        var itemHostel = $(this).data('hostel');
        var itemSharing = $(this).data('sharing');
        var itemName = $(this).data('name') || '';

        if (status !== '' && itemStatus != status) show = false;
        if (hostel && itemHostel != hostel) show = false;
        if (sharing && itemSharing != sharing) show = false;
        if (search && !itemName.includes(search)) show = false;

        $(this).toggle(show);
    });
}

function clearFilters() {
    $('#filterStatus, #filterHostel, #filterSharing').val('');
    $('#searchRoomType').val('');
    applyFilters();
}

function updateBulkActions() {
    var checked = $('.room-type-checkbox:checked');
    var count = checked.length;

    if (count > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(count);
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    $('.room-type-checkbox').prop('checked', false);
    updateBulkActions();
}

function getSelectedIds() {
    var ids = [];
    $('.room-type-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function bulkActivate() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;

    Swal.fire({
        title: 'Activate Room Types?',
        text: "Are you sure you want to activate " + ids.length + " room types?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, activate them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.room-types.bulk-status') }}",
                type: 'POST',
                data: {
                    ids: ids,
                    status: 'activate',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to update!', 'error');
                }
            });
        }
    });
}

function bulkDeactivate() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;

    Swal.fire({
        title: 'Deactivate Room Types?',
        text: "Are you sure you want to deactivate " + ids.length + " room types?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, deactivate them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.room-types.bulk-status') }}",
                type: 'POST',
                data: {
                    ids: ids,
                    status: 'deactivate',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to update!', 'error');
                }
            });
        }
    });
}

function bulkDelete() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;

    Swal.fire({
        title: 'Delete Room Types?',
        text: "Are you sure you want to delete " + ids.length + " room types? This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.room-types.bulk-delete') }}",
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
                    showToast(xhr.responseJSON?.message || 'Failed to delete!', 'error');
                }
            });
        }
    });
}

function exportData() {
    window.location.href = "{{ route('admin.room-types.export') }}";
}

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Room Type';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('roomTypeModal'));
    modal.show();
}

function resetForm() {
    const form = document.getElementById('roomTypeForm');
    form.reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Room Type';
}

function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.room-types.store') }}";
    let formData = new FormData(document.getElementById('roomTypeForm'));

    if (id) {
        url = "{{ url('admin/room-types') }}/" + id;
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
                var modal = bootstrap.Modal.getInstance(document.getElementById('roomTypeModal'));
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
                $.each(errors, function(field, messages) {
                    $('#' + field).closest('.rv-input-box').addClass('is-invalid');
                    $('#' + field + '_error').text(messages[0]);
                });
                showToast('Please fix validation errors', 'error');
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

function editRoomType(id) {
    $.ajax({
        url: "{{ url('admin/room-types') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Room Type';
                document.getElementById('editId').value = data.id;
                document.getElementById('hostel_id').value = data.hostel_id;
                document.getElementById('room_type_name').value = data.room_type_name;
                document.getElementById('sharing_count').value = data.sharing_count;
                document.getElementById('monthly_rent').value = data.monthly_rent;
                document.getElementById('deposit_amount').value = data.deposit_amount || '';
                document.getElementById('is_active').value = data.is_active ? '1' : '0';
                document.getElementById('saveBtnText').textContent = 'Update';
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('roomTypeModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load room type data', 'error');
            }
        }
    });
}

function deleteRoomType(id) {
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
                url: "{{ url('admin/room-types') }}/" + id,
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
        text: "Change room type status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/room-types') }}/" + id + "/toggle-status",
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
