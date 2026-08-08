@extends('layouts.office')

@section('title', 'Room Management')
@section('page_title', 'Room Management')

@push('styles')
<style>
    .room-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        position: relative;
    }
    .room-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
    .room-card .card-checkbox {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 1;
    }
    .room-card .card-checkbox input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .room-header {
        background: linear-gradient(135deg, #1a3a6b, var(--sanjay-primary));
        padding: 1rem 1.25rem;
        padding-left: 3rem;
        color: white;
    }
    .room-body { padding: 1rem 1.25rem; }
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
    .status-badge.partial { background: #fef3c7; color: #92400e; }
    .status-badge.full { background: #fce4ec; color: #c62828; }
    .status-badge.maintenance { background: #e3f2fd; color: #0d47a1; }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.vacant .dot { background: #22c55e; }
    .status-badge.partial .dot { background: #f59e0b; }
    .status-badge.full .dot { background: #ef4444; }
    .status-badge.maintenance .dot { background: #3b82f6; }
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
    .occupancy-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    .occupancy-bar .fill {
        height: 100%;
        background: var(--sanjay-gold);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    .cot-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .cot-indicator.normal { background: #e3f2fd; color: #0d47a1; }
    .cot-indicator.bunker { background: #f3e5f5; color: #4a148c; }
    .cot-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
    }
    .cot-badge.vacant { background: #dcfce7; color: #166534; }
    .cot-badge.occupied { background: #fef3c7; color: #92400e; }
    .cot-badge.blocked { background: #fee2e2; color: #991b1b; }
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
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
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
        font-size: 0.6rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-card .icon {
        font-size: 1.2rem;
        margin-bottom: 0.25rem;
    }
    .stat-card.vacant .number { color: #22c55e; }
    .stat-card.partial .number { color: #f59e0b; }
    .stat-card.full .number { color: #ef4444; }
    .stat-card.maintenance .number { color: #3b82f6; }
    .stat-card.occupancy .number { color: #92400e; }
    .stat-card.occupancy { background: linear-gradient(135deg, #fef3c7, #fde68a); }
    .filter-section {
        display: flex;
        gap: 0.75rem;
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
        background: rgba(255,255,255,0.2);
        color: white;
    }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Room Management</h1>
        <p class="ol-page-sub">Manage rooms with normal and bunker cots</p>
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
        <button type="button" class="rv-submit" id="addRoomBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Add Room
        </button>
    </div>
</div>

{{-- Statistics --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon">🏠</div>
        <div class="number">{{ $stats['total'] }}</div>
        <div class="label">Total Rooms</div>
    </div>
    <div class="stat-card vacant">
        <div class="icon">✅</div>
        <div class="number">{{ $stats['vacant'] }}</div>
        <div class="label">Vacant</div>
    </div>
    <div class="stat-card partial">
        <div class="icon">🟡</div>
        <div class="number">{{ $stats['partial'] }}</div>
        <div class="label">Partial</div>
    </div>
    <div class="stat-card full">
        <div class="icon">🔴</div>
        <div class="number">{{ $stats['full'] }}</div>
        <div class="label">Full</div>
    </div>
    <div class="stat-card maintenance">
        <div class="icon">🔵</div>
        <div class="number">{{ $stats['maintenance'] }}</div>
        <div class="label">Maintenance</div>
    </div>
    <div class="stat-card occupancy">
        <div class="icon">📈</div>
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
        <option value="PARTIAL">Partial</option>
        <option value="FULL">Full</option>
        <option value="MAINTENANCE">Maintenance</option>
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
            <option value="PARTIAL">Partial</option>
            <option value="FULL">Full</option>
            <option value="MAINTENANCE">Maintenance</option>
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
        <select id="filterRoomType">
            <option value="">All Room Types</option>
            @foreach($roomTypes as $roomType)
                <option value="{{ $roomType->id }}">{{ $roomType->room_type_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchRoom" placeholder="Search by room no, hostel...">
    </div>
    <button class="btn-action" onclick="clearFilters()" style="padding:0.35rem 1rem;">
        <i class="bi bi-arrow-counterclockwise"></i> Clear
    </button>
</div>

{{-- Rooms Grid --}}
<div id="roomsContainer">
    @if($rooms->count() > 0)
        <div class="row g-4" id="roomsGrid">
            @foreach($rooms as $room)
                <div class="col-xl-3 col-lg-4 col-md-6" data-id="{{ $room->id }}"
                     data-status="{{ $room->status }}"
                     data-hostel="{{ $room->hostel_id }}"
                     data-roomtype="{{ $room->room_type_id }}"
                     data-name="{{ strtolower($room->room_no) }}">
                    <div class="room-card">
                        <div class="card-checkbox">
                            <input type="checkbox" class="room-checkbox" value="{{ $room->id }}" onclick="updateBulkActions()">
                        </div>
                        <div class="room-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size:0.6rem; opacity:0.8;">
                                        <span class="hostel-tag">{{ $room->hostel->hostel_code ?? 'N/A' }}</span>
                                    </div>
                                    <h5 style="margin: 4px 0 0 0; color: white; font-weight: 700;">Room #{{ $room->room_no }}</h5>
                                </div>
                                <span style="font-size:0.65rem; background:rgba(255,255,255,0.2); padding:2px 10px; border-radius:12px;">
                                    {{ $room->roomType->room_type_name ?? 'N/A' }}
                                </span>
                            </div>
                            @if($room->floor_no)
                                <div style="font-size:0.7rem; opacity:0.8; margin-top:4px;">
                                    <i class="bi bi-layers"></i> Floor {{ $room->floor_no }}
                                </div>
                            @endif
                        </div>
                        <div class="room-body">
                            {{-- Cot Summary --}}
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div style="font-size:0.65rem; color:#6b7280;">Normal Cots</div>
                                    <div>
                                        <span class="cot-indicator normal">
                                            <i class="bi bi-bed"></i> {{ $room->normol_cot_count ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="font-size:0.65rem; color:#6b7280;">Bunker Cots</div>
                                    <div>
                                        <span class="cot-indicator bunker">
                                            <i class="bi bi-layers"></i> {{ $room->bunker_cot_count ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Occupancy --}}
                            <div class="d-flex justify-content-between mb-1">
                                <div>
                                    <div style="font-size:0.7rem; color:#6b7280;">Occupancy</div>
                                    <div style="font-weight:600;">
                                        {{ $room->occupied_cots }}/{{ $room->total_cots }}
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size:0.7rem; color:#6b7280;">Vacant</div>
                                    <div style="font-weight:600; color:#22c55e;">
                                        {{ $room->vacant_cots }}
                                    </div>
                                </div>
                            </div>
                            <div class="occupancy-bar">
                                <div class="fill" style="width: {{ $room->occupancy_percentage ?? 0 }}%;"></div>
                            </div>

                            {{-- Quick Bed Status --}}
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach($room->beds as $bed)
                                    <span class="cot-badge {{ strtolower($bed->status) }}" title="Bed #{{ $bed->bed_no }} ({{ $bed->bed_type }})">
                                        {{ $bed->bed_no }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="status-badge {{ $room->status_badge }}" onclick="toggleStatus({{ $room->id }})">
                                    <span class="dot"></span>
                                    {{ $room->status_label }}
                                </button>
                                <div class="d-flex gap-1">
                                    <button class="btn-action" onclick="editRoom({{ $room->id }})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action text-danger" onclick="deleteRoom({{ $room->id }})" title="Delete">
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
                <h5>No rooms found</h5>
                <p class="text-muted">Create rooms with normal and bunker cots.</p>
                <button type="button" class="rv-submit" onclick="openAddModal()" style="width:auto; display:inline-flex; padding:0 1.5rem; height:38px; border-radius:9px; align-items:center; gap:6px; animation:none;">
                    <i class="bi bi-plus-circle"></i>
                    Add Room
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roomForm">
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
                            <label class="form-label">Room Type <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <select name="room_type_id" id="room_type_id" class="rv-input" required>
                                    <option value="">Select Room Type</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="room_type_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Number <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-hash rv-input-icon"></i>
                                <input type="text" name="room_no" id="room_no" class="rv-input" placeholder="101, 102, A-1" required>
                            </div>
                            <div class="invalid-feedback" id="room_no_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Floor Number</label>
                            <div class="rv-input-box">
                                <i class="bi bi-layers rv-input-icon"></i>
                                <input type="text" name="floor_no" id="floor_no" class="rv-input" placeholder="Ground, 1, 2">
                            </div>
                            <div class="invalid-feedback" id="floor_no_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Normal Cots <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-bed rv-input-icon"></i>
                                <input type="number" name="normol_cot_count" id="normol_cot_count" class="rv-input" placeholder="0" min="0" max="20" required>
                            </div>
                            <div class="invalid-feedback" id="normol_cot_count_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bunker Cots <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-layers rv-input-icon"></i>
                                <input type="number" name="bunker_cot_count" id="bunker_cot_count" class="rv-input" placeholder="0" min="0" max="20" required>
                            </div>
                            <div class="invalid-feedback" id="bunker_cot_count_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-toggle-on rv-input-icon"></i>
                                <select name="status" id="status" class="rv-input" required>
                                    <option value="VACANT">✅ Vacant</option>
                                    <option value="PARTIAL">🟡 Partial</option>
                                    <option value="FULL">🔴 Full</option>
                                    <option value="MAINTENANCE">🔵 Maintenance</option>
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

<!-- Toast Container -->
<div class="toast-container" id="flashMessageContainer"></div>

<script>
$(document).ready(function() {
    var roomModal = new bootstrap.Modal(document.getElementById('roomModal'), {
        backdrop: 'static',
        keyboard: true
    });

    $('#addRoomBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    $('#roomModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    $('#roomForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Load room types when hostel changes
    $('#hostel_id').on('change', function() {
        let hostelId = $(this).val();
        if (hostelId) {
            $.ajax({
                url: '/admin/rooms/hostel/' + hostelId + '/types',
                type: 'GET',
                success: function(response) {
                    let select = $('#room_type_id');
                    select.empty().append('<option value="">Select Room Type</option>');
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(key, type) {
                            select.append('<option value="' + type.id + '">' + type.room_type_name + ' (' + type.sharing_count + ' Sharing) - ₹' + type.monthly_rent + '</option>');
                        });
                    } else {
                        select.append('<option value="">No room types available</option>');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    }
                }
            });
        }
    });

    // Filter changes
    $('#filterStatus, #filterHostel, #filterRoomType').on('change', function() {
        applyFilters();
    });

    // Search functionality
    let searchTimeout;
    $('#searchRoom').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
});

function applyFilters() {
    var status = $('#filterStatus').val();
    var hostel = $('#filterHostel').val();
    var roomType = $('#filterRoomType').val();
    var search = $('#searchRoom').val().toLowerCase();

    $('#roomsGrid .col-xl-3').each(function() {
        var show = true;
        var itemStatus = $(this).data('status');
        var itemHostel = $(this).data('hostel');
        var itemRoomType = $(this).data('roomtype');
        var itemName = $(this).data('name') || '';

        if (status && itemStatus !== status) show = false;
        if (hostel && itemHostel != hostel) show = false;
        if (roomType && itemRoomType != roomType) show = false;
        if (search && !itemName.includes(search)) show = false;

        $(this).toggle(show);
    });
}

function clearFilters() {
    $('#filterStatus, #filterHostel, #filterRoomType').val('');
    $('#searchRoom').val('');
    applyFilters();
}

function updateBulkActions() {
    var checked = $('.room-checkbox:checked');
    var count = checked.length;

    if (count > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(count);
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    $('.room-checkbox').prop('checked', false);
    updateBulkActions();
}

function getSelectedIds() {
    var ids = [];
    $('.room-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function bulkStatusUpdate() {
    var ids = getSelectedIds();
    var status = $('#bulkStatusSelect').val();

    if (ids.length === 0 || !status) {
        showToast('Please select rooms and a status', 'error');
        return;
    }

    Swal.fire({
        title: 'Update Status?',
        text: "Are you sure you want to update " + ids.length + " rooms to " + status + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, update them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.rooms.bulk-status') }}",
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
        title: 'Delete Rooms?',
        text: "Are you sure you want to delete " + ids.length + " rooms? This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.rooms.bulk-delete') }}",
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
    window.location.href = "{{ route('admin.rooms.export') }}";
}

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Room';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('roomModal'));
    modal.show();
}

function resetForm() {
    const form = document.getElementById('roomForm');
    form.reset();
    $('#room_type_id').empty().append('<option value="">Select Room Type</option>');
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Room';
}

function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.rooms.store') }}";
    let formData = new FormData(document.getElementById('roomForm'));

    if (id) {
        url = "{{ url('admin/rooms') }}/" + id;
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
                var modal = bootstrap.Modal.getInstance(document.getElementById('roomModal'));
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

function editRoom(id) {
    $.ajax({
        url: "{{ url('admin/rooms') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Room';
                document.getElementById('editId').value = data.id;
                document.getElementById('hostel_id').value = data.hostel_id;
                document.getElementById('room_no').value = data.room_no;
                document.getElementById('floor_no').value = data.floor_no || '';
                document.getElementById('normol_cot_count').value = data.normol_cot_count || 0;
                document.getElementById('bunker_cot_count').value = data.bunker_cot_count || 0;
                document.getElementById('status').value = data.status;
                document.getElementById('saveBtnText').textContent = 'Update';

                // Load room types for this hostel
                $.ajax({
                    url: '/admin/rooms/hostel/' + data.hostel_id + '/types',
                    type: 'GET',
                    success: function(typeResponse) {
                        let select = $('#room_type_id');
                        select.empty().append('<option value="">Select Room Type</option>');
                        if (typeResponse.success && typeResponse.data.length > 0) {
                            $.each(typeResponse.data, function(key, type) {
                                let selected = type.id == data.room_type_id ? 'selected' : '';
                                select.append('<option value="' + type.id + '" ' + selected + '>' + type.room_type_name + ' (' + type.sharing_count + ' Sharing) - ₹' + type.monthly_rent + '</option>');
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                        }
                    }
                });

                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('roomModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load room data', 'error');
            }
        }
    });
}

function deleteRoom(id) {
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
                url: "{{ url('admin/rooms') }}/" + id,
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
        text: "Change room status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/rooms') }}/" + id + "/toggle-status",
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
