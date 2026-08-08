@extends('layouts.office')

@section('title', 'Bed Management')
@section('page_title', 'Bed Management')

@push('styles')
<style>
    .bed-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        position: relative;
    }
    .bed-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
    .bed-card .card-checkbox {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 1;
    }
    .bed-card .card-checkbox input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .bed-card .bed-type-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.6rem;
        font-weight: 600;
        z-index: 1;
    }
    .bed-type-badge.normal { background: #e3f2fd; color: #0d47a1; }
    .bed-type-badge.bunker { background: #f3e5f5; color: #4a148c; }
    .bed-header {
        padding: 1rem 1.25rem;
        padding-left: 3rem;
        background: var(--sanjay-primary);
        color: white;
    }
    .bed-body { padding: 1rem 1.25rem; }
    .bed-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--sanjay-primary);
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
    .status-badge.vacant { background: #dcfce7; color: #166534; }
    .status-badge.occupied { background: #fef3c7; color: #92400e; }
    .status-badge.blocked { background: #fee2e2; color: #991b1b; }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.vacant .dot { background: #22c55e; }
    .status-badge.occupied .dot { background: #f59e0b; }
    .status-badge.blocked .dot { background: #ef4444; }
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
    .resident-info {
        font-size: 0.75rem;
        color: #6b7280;
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: 6px;
        margin-top: 0.5rem;
    }
    .resident-info .name {
        font-weight: 600;
        color: var(--sanjay-primary);
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
    select.rv-input[multiple] { min-height: 120px; }
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
    .bulk-create-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        border: 1px dashed #d1d5db;
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
    .bed-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .bed-stat {
        text-align: center;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .bed-stat .number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .bed-stat .label {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: uppercase;
    }
    .bed-stat.vacant .number { color: #22c55e; }
    .bed-stat.occupied .number { color: #f59e0b; }
    .bed-stat.blocked .number { color: #ef4444; }
    .bed-stat.total .number { color: var(--sanjay-primary); }
    .bed-stat.occupancy .number { color: #92400e; }
    .bed-stat.occupancy { background: linear-gradient(135deg, #fef3c7, #fde68a); }
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
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Bed Management</h1>
        <p class="ol-page-sub">Manage bed allocations and status across all rooms</p>
        @if($user->role != 'admin')
            <p class="ol-page-sub" style="color: var(--sanjay-gold); font-size:0.8rem;">
                <i class="bi bi-info-circle"></i> You have access to {{ $rooms->pluck('hostel_id')->unique()->count() }} hostel(s)
            </p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="rv-submit" onclick="exportData()" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-download"></i>
            Export
        </button>
        <button type="button" class="rv-submit" id="bulkCreateBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-plus-circle"></i>
            Bulk Create
        </button>
        <button type="button" class="rv-submit" id="addBedBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Add Bed
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="bed-stats">
    <div class="bed-stat total">
        <div class="number">{{ $stats['total'] }}</div>
        <div class="label">Total Beds</div>
    </div>
    <div class="bed-stat vacant">
        <div class="number">{{ $stats['vacant'] }}</div>
        <div class="label">Vacant</div>
    </div>
    <div class="bed-stat occupied">
        <div class="number">{{ $stats['occupied'] }}</div>
        <div class="label">Occupied</div>
    </div>
    <div class="bed-stat blocked">
        <div class="number">{{ $stats['blocked'] }}</div>
        <div class="label">Blocked</div>
    </div>
    <div class="bed-stat occupancy">
        <div class="number">{{ $stats['occupancy_rate'] }}%</div>
        <div class="label">Occupancy Rate</div>
    </div>
</div>

{{-- Bulk Actions --}}
<div class="bulk-actions" id="bulkActions">
    <span><i class="bi bi-check-square"></i> <span class="count" id="selectedCount">0</span> selected</span>
    <span style="color:#6b7280;">|</span>
    <select id="bulkStatusSelect" style="padding:0.2rem 0.5rem; border-radius:4px; border:1px solid #d1d5db; font-size:0.75rem;">
        <option value="">Change Status</option>
        <option value="VACANT">Vacant</option>
        <option value="OCCUPIED">Occupied</option>
        <option value="BLOCKED">Blocked</option>
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
            <option value="VACANT">Vacant</option>
            <option value="OCCUPIED">Occupied</option>
            <option value="BLOCKED">Blocked</option>
        </select>
    </div>
    <div class="filter-group">
        <select id="filterType">
            <option value="">All Types</option>
            <option value="NORMAL">Normal</option>
            <option value="BUNKER">Bunker</option>
        </select>
    </div>
    <div class="filter-group">
        <select id="filterHostel">
            <option value="">All Hostels</option>
            @php
                $uniqueHostels = $rooms->pluck('hostel')->unique('id');
            @endphp
            @foreach($uniqueHostels as $hostel)
                @if($hostel)
                    <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <select id="filterRoom">
            <option value="">All Rooms</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}">Room #{{ $room->room_no }} - {{ $room->hostel->hostel_name ?? '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchBed" placeholder="Search by bed no, resident...">
    </div>
    <button class="btn-action" onclick="clearFilters()" style="padding:0.4rem 1rem;">
        <i class="bi bi-arrow-counterclockwise"></i> Clear
    </button>
</div>

{{-- Beds Grid --}}
<div id="bedsContainer">
    @if($beds->count() > 0)
        <div class="row g-4" id="bedsGrid">
            @foreach($beds as $bed)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6" data-id="{{ $bed->id }}"
                     data-status="{{ $bed->status }}"
                     data-type="{{ $bed->bed_type }}"
                     data-room="{{ $bed->room_id }}"
                     data-hostel="{{ $bed->room->hostel_id ?? '' }}"
                     data-bedno="{{ strtolower($bed->bed_no) }}">
                    <div class="bed-card">
                        <div class="card-checkbox">
                            <input type="checkbox" class="bed-checkbox" value="{{ $bed->id }}" onclick="updateBulkActions()">
                        </div>
                        <span class="bed-type-badge {{ strtolower($bed->bed_type) }}">
                            <i class="bi {{ $bed->bed_type == 'NORMAL' ? 'bi-bed' : 'bi-layers' }}"></i>
                            {{ $bed->bed_type_label }}
                        </span>
                        <div class="bed-header">
                            <div style="font-size:0.6rem; opacity:0.7;">{{ $bed->room->hostel->hostel_code ?? 'N/A' }}</div>
                            <div style="font-size:0.8rem; font-weight:600;">Room #{{ $bed->room->room_no ?? 'N/A' }}</div>
                        </div>
                        <div class="bed-body text-center">
                            <div class="bed-number">#{{ $bed->bed_no }}</div>
                            <div style="font-size:0.7rem; color:#6b7280; margin-bottom:0.5rem;">
                                {{ $bed->room->roomType->room_type_name ?? '' }}
                            </div>
                            @if($bed->resident)
                                <div class="resident-info">
                                    <div><i class="bi bi-person-circle"></i> <span class="name">{{ $bed->resident->name }}</span></div>
                                    <div style="font-size:0.65rem; color:#9ca3af; margin-top:2px;">
                                        {{ $bed->resident->resident_code ?? '' }}
                                    </div>
                                </div>
                            @else
                                <div style="font-size:0.7rem; color:#9ca3af; padding:0.5rem;">
                                    <i class="bi bi-person"></i> No resident
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="status-badge {{ $bed->status_badge }}" onclick="toggleStatus({{ $bed->id }})">
                                    <span class="dot"></span>
                                    {{ $bed->status_label }}
                                </button>
                                <div class="d-flex gap-1">
                                    <button class="btn-action text-primary" onclick="editBed({{ $bed->id }})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action text-danger" onclick="deleteBed({{ $bed->id }})" title="Delete">
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
                <i class="bi bi-bed"></i>
                <h5>No beds found</h5>
                <p class="text-muted">Add beds to rooms for resident allocation.</p>
                <button type="button" class="rv-submit" onclick="openAddModal()" style="width:auto; display:inline-flex; padding:0 1.5rem; height:38px; border-radius:9px; align-items:center; gap:6px; animation:none;">
                    <i class="bi bi-plus-circle"></i>
                    Add Bed
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="bedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Bed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bedForm">
                @csrf
                <input type="hidden" id="editId" name="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Room <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-door-open rv-input-icon"></i>
                                <select name="room_id" id="room_id" class="rv-input" required>
                                    <option value="">Select Room</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            {{ $room->hostel->hostel_name ?? 'N/A' }} - Room #{{ $room->room_no }}
                                            ({{ $room->roomType->room_type_name ?? 'N/A' }})
                                            [N:{{ $room->normol_cot_count ?? 0 }} | B:{{ $room->bunker_cot_count ?? 0 }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="invalid-feedback" id="room_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bed Number <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-hash rv-input-icon"></i>
                                <input type="text" name="bed_no" id="bed_no" class="rv-input" placeholder="1, 2, A, B" required>
                            </div>
                            <div class="invalid-feedback" id="bed_no_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bed Type <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-layers rv-input-icon"></i>
                                <select name="bed_type" id="bed_type" class="rv-input" required>
                                    <option value="NORMAL">🛏️ Normal</option>
                                    <option value="BUNKER">🪜 Bunker</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="bed_type_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-toggle-on rv-input-icon"></i>
                                <select name="status" id="status" class="rv-input" required>
                                    <option value="VACANT">✅ Vacant</option>
                                    <option value="OCCUPIED">🟡 Occupied</option>
                                    <option value="BLOCKED">🔴 Blocked</option>
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

{{-- Bulk Create Modal --}}
<div class="modal fade" id="bulkCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Create Beds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkCreateForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Room <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-door-open rv-input-icon"></i>
                                <select name="room_id" id="bulk_room_id" class="rv-input" required>
                                    <option value="">Select Room</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            {{ $room->hostel->hostel_name ?? 'N/A' }} - Room #{{ $room->room_no }}
                                            (N:{{ $room->normol_cot_count ?? 0 }} | B:{{ $room->bunker_cot_count ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="invalid-feedback" id="bulk_room_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Normal Cots <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-bed rv-input-icon"></i>
                                <input type="number" name="normal_count" id="normal_count" class="rv-input" placeholder="0" min="0" max="20" required>
                            </div>
                            <div class="invalid-feedback" id="normal_count_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bunker Cots <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-layers rv-input-icon"></i>
                                <input type="number" name="bunker_count" id="bunker_count" class="rv-input" placeholder="0" min="0" max="20" required>
                            </div>
                            <div class="invalid-feedback" id="bunker_count_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-toggle-on rv-input-icon"></i>
                                <select name="status" id="bulk_status" class="rv-input" required>
                                    <option value="VACANT">✅ Vacant</option>
                                    <option value="OCCUPIED">🟡 Occupied</option>
                                    <option value="BLOCKED">🔴 Blocked</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="bulk_status_error"></div>
                        </div>
                        <div class="col-12">
                            <div class="bulk-create-section">
                                <div style="font-size:0.8rem; color:#6b7280;">
                                    <i class="bi bi-info-circle"></i>
                                    This will create multiple beds at once. Bed numbers will be auto-generated (N-1, N-2, B-1, B-2, etc.)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="bulkSaveBtn" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
                        <i class="bi bi-plus-circle"></i>
                        Create Beds
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
    var bedModal = new bootstrap.Modal(document.getElementById('bedModal'), {
        backdrop: 'static',
        keyboard: true
    });
    var bulkModal = new bootstrap.Modal(document.getElementById('bulkCreateModal'), {
        backdrop: 'static',
        keyboard: true
    });

    $('#addBedBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    $('#bulkCreateBtn').on('click', function(e) {
        e.preventDefault();
        openBulkModal();
    });

    $('#bedModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    $('#bulkCreateModal').on('hidden.bs.modal', function() {
        resetBulkForm();
    });

    $('#bedForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    $('#bulkCreateForm').on('submit', function(e) {
        e.preventDefault();
        submitBulkForm();
    });

    // Filters
    $('#filterStatus, #filterType, #filterHostel, #filterRoom').on('change', function() {
        applyFilters();
    });

    // Search
    let searchTimeout;
    $('#searchBed').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
});

function applyFilters() {
    var status = $('#filterStatus').val();
    var type = $('#filterType').val();
    var hostel = $('#filterHostel').val();
    var room = $('#filterRoom').val();
    var search = $('#searchBed').val().toLowerCase();

    $('#bedsGrid .col-xl-2').each(function() {
        var show = true;
        var bedStatus = $(this).data('status');
        var bedType = $(this).data('type');
        var bedHostel = $(this).data('hostel');
        var bedRoom = $(this).data('room');
        var bedNo = $(this).data('bedno') || '';
        var residentName = $(this).find('.resident-info .name').text().toLowerCase() || '';

        if (status && bedStatus !== status) show = false;
        if (type && bedType !== type) show = false;
        if (hostel && bedHostel != hostel) show = false;
        if (room && bedRoom != room) show = false;
        if (search) {
            var match = bedNo.includes(search) || residentName.includes(search);
            if (!match) show = false;
        }

        $(this).toggle(show);
    });
}

function clearFilters() {
    $('#filterStatus, #filterType, #filterHostel, #filterRoom').val('');
    $('#searchBed').val('');
    applyFilters();
}

function updateBulkActions() {
    var checked = $('.bed-checkbox:checked');
    var count = checked.length;

    if (count > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(count);
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    $('.bed-checkbox').prop('checked', false);
    updateBulkActions();
}

function getSelectedIds() {
    var ids = [];
    $('.bed-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function bulkStatusUpdate() {
    var ids = getSelectedIds();
    var status = $('#bulkStatusSelect').val();

    if (ids.length === 0 || !status) {
        showToast('Please select beds and a status', 'error');
        return;
    }

    Swal.fire({
        title: 'Update Status?',
        text: "Are you sure you want to update " + ids.length + " beds to " + status + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, update them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.beds.bulk-status') }}",
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
        title: 'Delete Beds?',
        text: "Are you sure you want to delete " + ids.length + " beds? This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.beds.bulk-delete') }}",
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
    window.location.href = "{{ route('admin.beds.export') }}";
}

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Bed';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('bedModal'));
    modal.show();
}

function openBulkModal() {
    resetBulkForm();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('bulkCreateModal'));
    modal.show();
}

function resetForm() {
    const form = document.getElementById('bedForm');
    form.reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Bed';
}

function resetBulkForm() {
    const form = document.getElementById('bulkCreateForm');
    form.reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
}

function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.beds.store') }}";
    let formData = new FormData(document.getElementById('bedForm'));

    if (id) {
        url = "{{ url('admin/beds') }}/" + id;
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
                var modal = bootstrap.Modal.getInstance(document.getElementById('bedModal'));
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

function submitBulkForm() {
    let url = "{{ route('admin.beds.bulk-create') }}";
    let formData = new FormData(document.getElementById('bulkCreateForm'));

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#bulkSaveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Creating...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('bulkCreateModal'));
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
                        let fieldId = field === 'normal_count' || field === 'bunker_count' ? field : 'bulk_' + field;
                        $('#' + fieldId).closest('.rv-input-box').addClass('is-invalid');
                        $('#' + fieldId + '_error').text(messages[0]);
                    });
                    showToast('Please fix validation errors', 'error');
                }
            } else {
                showToast(xhr.responseJSON?.message || 'Something went wrong!', 'error');
            }
        },
        complete: function() {
            $('#bulkSaveBtn').prop('disabled', false).html('<i class="bi bi-plus-circle"></i> Create Beds');
        }
    });
}

function editBed(id) {
    $.ajax({
        url: "{{ url('admin/beds') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Bed';
                document.getElementById('editId').value = data.id;
                document.getElementById('room_id').value = data.room_id;
                document.getElementById('bed_no').value = data.bed_no;
                document.getElementById('bed_type').value = data.bed_type;
                document.getElementById('status').value = data.status;
                document.getElementById('saveBtnText').textContent = 'Update';
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('bedModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load bed data', 'error');
            }
        }
    });
}

function deleteBed(id) {
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
                url: "{{ url('admin/beds') }}/" + id,
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
        text: "Change bed status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/beds') }}/" + id + "/toggle-status",
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
