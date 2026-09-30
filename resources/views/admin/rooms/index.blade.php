@extends('layouts.office')

@section('title', 'Rooms — Sanjay PG Hostel')
@section('page_title', 'Rooms')

@push('styles')
<style>
    /* ═══════════════════════════════════════════
       ROOM MANAGEMENT
    ═══════════════════════════════════════════ */

    .rm-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .rm-page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .rm-page-title i { color: var(--sanjay-gold); }
    .rm-page-subtitle {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0.25rem 0 0 0;
    }

    /* ── Toolbar ── */
    .rm-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .rm-search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }
    .rm-search-box input {
        width: 100%;
        padding: 0.55rem 1rem 0.55rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        transition: all 0.2s;
        color: #374151;
    }
    .rm-search-box input:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .rm-search-box i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .rm-filter-select {
        padding: 0.55rem 2rem 0.55rem 0.85rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        color: #374151;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
    }
    .rm-filter-select:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }

    .rm-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
        text-decoration: none;
        white-space: nowrap;
    }
    .rm-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(10, 30, 63, 0.25);
        color: white;
    }

    /* ── Grid ── */
    .rm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
    }

    /* ── Cards ── */
    .rm-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .rm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.08);
        border-color: rgba(197, 160, 40, 0.3);
    }
    .rm-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--sanjay-gold), var(--sanjay-primary));
        opacity: 0;
        transition: opacity 0.3s;
    }
    .rm-card:hover::before { opacity: 1; }

    .rm-card-head {
        padding: 1rem 1.15rem 0.75rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .rm-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(197, 160, 40, 0.12), rgba(10, 30, 63, 0.08));
        color: var(--sanjay-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .rm-card-title-wrap { flex: 1; min-width: 0; }
    .rm-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0 0 0.15rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rm-card-hostel {
        font-size: 0.68rem;
        color: #9ca3af;
        font-family: 'DM Mono', monospace;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .rm-card-hostel i { font-size: 0.65rem; }
    .rm-card-menu { position: relative; }
    .rm-card-menu-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.85rem;
    }
    .rm-card-menu-btn:hover {
        background: #f9fafb;
        border-color: var(--sanjay-gold);
        color: var(--sanjay-gold);
    }

    .rm-card-body {
        padding: 0 1.15rem 1rem;
        flex: 1;
    }
    .rm-card-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin-bottom: 0.85rem;
    }
    .rm-stat-item {
        background: #f8fafc;
        border-radius: 9px;
        padding: 0.5rem 0.6rem;
        border: 1px solid #f1f5f9;
        text-align: center;
    }
    .rm-stat-label {
        font-size: 0.6rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 0.15rem;
    }
    .rm-stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        font-family: 'DM Mono', monospace;
        line-height: 1.1;
    }
    .rm-stat-value.normal { color: #3b82f6; }
    .rm-stat-value.bunker { color: #8b5cf6; }
    .rm-stat-value.orange { color: #f59e0b; }

    .rm-card-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #6b7280;
        padding: 0.3rem 0;
    }
    .rm-card-detail i {
        color: var(--sanjay-gold);
        font-size: 0.8rem;
        width: 14px;
        flex-shrink: 0;
    }
    .rm-card-detail span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Bed chips ── */
    .rm-bed-grid {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        padding: 0.5rem 0;
    }
    .rm-bed-chip {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        font-size: 0.62rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'DM Mono', monospace;
        cursor: help;
        transition: transform 0.15s;
        position: relative;
    }
    .rm-bed-chip:hover { transform: scale(1.15); }
    .rm-bed-chip.vacant   { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .rm-bed-chip.occupied { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .rm-bed-chip.blocked  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
    .rm-bed-chip.bunker::after {
        content: '';
        position: absolute;
        top: -2px;
        right: -2px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #8b5cf6;
    }

    /* ── Badges ── */
    .rm-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .rm-badge.vacant      { background: #dcfce7; color: #166534; }
    .rm-badge.partial     { background: #fef3c7; color: #92400e; }
    .rm-badge.full        { background: #fee2e2; color: #991b1b; }
    .rm-badge.maintenance { background: #f3f4f6; color: #4b5563; }
    .rm-badge-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    /* ── Card Footer ── */
    .rm-card-footer {
        padding: 0.7rem 1.15rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #fafbfc;
    }
    .rm-card-actions { display: flex; gap: 0.35rem; }
    .rm-icon-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.8rem;
    }
    .rm-icon-btn:hover { transform: translateY(-1px); }
    .rm-icon-btn.edit:hover   { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    .rm-icon-btn.delete:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }

    /* ── Empty State ── */
    .rm-empty {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        border: 2px dashed #e5e7eb;
        grid-column: 1 / -1;
    }
    .rm-empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(197, 160, 40, 0.1), rgba(10, 30, 63, 0.05));
        color: var(--sanjay-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        margin: 0 auto 1.25rem;
    }
    .rm-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .rm-empty p  { color: #6b7280; font-size: 0.85rem; margin-bottom: 1.5rem; }

    /* ═══════════════════════════════════════════
       MODAL
    ═══════════════════════════════════════════ */
    .rm-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.15);
    }
    .rm-modal .modal-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        padding: 1.1rem 1.5rem;
    }
    .rm-modal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .rm-modal .modal-title i { color: var(--sanjay-gold); }
    .rm-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .rm-modal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    .rm-modal .modal-body::-webkit-scrollbar { width: 4px; }
    .rm-modal .modal-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .rm-modal .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f3f4f6;
        background: #fafbfc;
    }

    /* ── Form ── */
    .rm-form-section { margin-bottom: 1.5rem; }
    .rm-form-section:last-child { margin-bottom: 0; }
    .rm-form-section-title {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--sanjay-gold);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .rm-form-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(197, 160, 40, 0.3), transparent);
    }
    .rm-form-group { margin-bottom: 0.9rem; }
    .rm-form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .rm-form-label .required { color: #ef4444; margin-left: 2px; }
    .rm-form-control {
        width: 100%;
        padding: 0.6rem 0.85rem;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        font-size: 0.82rem;
        color: #374151;
        background: white;
        transition: all 0.2s;
        font-family: inherit;
    }
    .rm-form-control:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .rm-form-control::placeholder { color: #9ca3af; }
    .rm-form-control.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .rm-form-error {
        font-size: 0.7rem;
        color: #ef4444;
        margin-top: 0.25rem;
        display: none;
    }
    .rm-form-error.show { display: block; }
    select.rm-form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.85rem center;
        padding-right: 2.2rem;
    }

    .rm-form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.9rem;
    }
    @media (max-width: 576px) {
        .rm-form-row { grid-template-columns: 1fr; }
    }

    /* ── Info box ── */
    .rm-info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 9px;
        padding: 0.6rem 0.85rem;
        font-size: 0.75rem;
        color: #1e40af;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    .rm-info-box.warning {
        background: #fffbeb;
        border-color: #fcd34d;
        color: #92400e;
    }
    .rm-info-box.success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }
    .rm-info-box strong { font-family: 'DM Mono', monospace; }

    /* ── Buttons ── */
    .rm-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.6rem 1.25rem;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        white-space: nowrap;
    }
    .rm-btn-gold {
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
    }
    .rm-btn-gold:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(197, 160, 40, 0.35);
    }
    .rm-btn-gold:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .rm-btn-outline {
        background: white;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }
    .rm-btn-outline:hover { background: #f9fafb; color: #374151; }
    .rm-btn-danger { background: #ef4444; color: white; }
    .rm-btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }
    .rm-btn-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.7rem;
    }

    /* ── Spinner ── */
    .rm-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: rm-spin 0.6s linear infinite;
        display: inline-block;
    }
    @keyframes rm-spin { to { transform: rotate(360deg); } }

    /* ── Delete Modal ── */
    .rm-delete-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #fef2f2;
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1rem;
    }

    /* ── Context Menu ── */
    .rm-context-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.6rem 1rem;
        border: none;
        background: white;
        color: #374151;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        text-align: left;
    }
    .rm-context-item:hover { background: #f9fafb; color: var(--sanjay-primary); }
    .rm-context-item.danger { color: #ef4444; }
    .rm-context-item.danger:hover { background: #fef2f2; }
    .rm-context-item i { font-size: 0.85rem; width: 16px; }

    @media (max-width: 768px) {
        .rm-page-header { flex-direction: column; align-items: flex-start; }
        .rm-toolbar { flex-direction: column; align-items: stretch; }
        .rm-search-box { max-width: none; }
        .rm-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- PAGE HEADER --}}
<div class="rm-page-header">
    <div>
        <h2 class="rm-page-title">
            <i class="bi bi-door-open"></i>
            Room Management
        </h2>
        <p class="rm-page-subtitle">Manage rooms — beds auto-create based on Normal + Bunker split</p>
    </div>
    <button type="button" class="rm-btn-primary" onclick="openCreateModal()">
        <i class="bi bi-plus-lg"></i>
        Add Room
    </button>
</div>

{{-- TOOLBAR --}}
<div class="rm-toolbar">
    <div class="rm-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="rmSearchInput" placeholder="Search by room no...">
    </div>

    <select class="rm-filter-select" id="rmHostelFilter">
        <option value="">All Hostels</option>
        @foreach($hostels as $hostel)
            <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
        @endforeach
    </select>

    <select class="rm-filter-select" id="rmStatusFilter">
        <option value="">All Status</option>
        <option value="VACANT">Vacant</option>
        <option value="PARTIAL">Partial</option>
        <option value="FULL">Full</option>
        <option value="MAINTENANCE">Maintenance</option>
    </select>

    <span style="margin-left:auto; font-size:0.75rem; color:#9ca3af;" id="rmCountLabel">
        {{ $rooms->count() }} rooms
    </span>
</div>

{{-- ROOM GRID --}}
<div class="rm-grid" id="rmGrid">
    @forelse($rooms as $room)
    <div class="rm-card"
         data-id="{{ $room->id }}"
         data-room-no="{{ strtolower($room->room_no) }}"
         data-hostel-id="{{ $room->hostel_id }}"
         data-status="{{ $room->status }}">

        <div class="rm-card-head">
            <div class="rm-card-icon">
                <i class="bi bi-door-open"></i>
            </div>
            <div class="rm-card-title-wrap">
                <h3 class="rm-card-title" title="Room {{ $room->room_no }}">
                    Room {{ $room->room_no }}
                </h3>
                <div class="rm-card-hostel">
                    <i class="bi bi-building"></i>
                    {{ $room->hostel->hostel_name ?? 'N/A' }}
                </div>
            </div>
            <div class="rm-card-menu">
                <button type="button" class="rm-card-menu-btn"
                        onclick="toggleCardMenu(event, {{ $room->id }})">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
            </div>
        </div>

        <div class="rm-card-body">
            <div style="display:flex; gap:0.35rem; flex-wrap:wrap; margin-bottom:0.85rem;">
                <span class="rm-badge {{ strtolower($room->status) }}">
                    <span class="rm-badge-dot"></span>
                    {{ $room->status }}
                </span>
                <span class="rm-badge" style="background:#fef3c7; color:#92400e;">
                    <i class="bi bi-tags"></i>
                    {{ $room->roomType->room_type_name ?? 'N/A' }}
                </span>
            </div>

            {{-- 3-col stats: Normal / Bunker / Occupied --}}
            <div class="rm-card-stats">
                <div class="rm-stat-item">
                    <div class="rm-stat-label">Normal</div>
                    <div class="rm-stat-value normal">{{ $room->normal_cot_count }}</div>
                </div>
                <div class="rm-stat-item">
                    <div class="rm-stat-label">Bunker</div>
                    <div class="rm-stat-value bunker">{{ $room->bunker_cot_count }}</div>
                </div>
                <div class="rm-stat-item">
                    <div class="rm-stat-label">Occupied</div>
                    <div class="rm-stat-value orange">{{ $room->residents_count ?? 0 }}</div>
                </div>
            </div>

            {{-- Bed Chips --}}
            <div style="font-size:0.62rem; color:#9ca3af; text-transform:uppercase; font-weight:600; letter-spacing:0.5px; margin-bottom:0.35rem;">
                Bed Map
            </div>
            <div class="rm-bed-grid">
                @foreach($room->beds as $bed)
                    <div class="rm-bed-chip {{ strtolower($bed->status) }} {{ strtolower($bed->bed_type) }}"
                         title="Bed {{ $bed->bed_no }} — {{ $bed->status }} ({{ $bed->bed_type }})">
                        {{ $bed->bed_no }}
                    </div>
                @endforeach
            </div>

            {{-- Pricing --}}
            <div class="rm-card-detail" style="margin-top:0.6rem;">
                <i class="bi bi-currency-rupee"></i>
                <span>₹{{ number_format($room->roomType->monthly_rent ?? 0, 0) }} / month</span>
            </div>
        </div>

        <div class="rm-card-footer">
            <span style="font-size:0.68rem; color:#9ca3af;">
                <i class="bi bi-clock"></i>
                {{ $room->created_at->format('d M Y') }}
            </span>
            <div class="rm-card-actions">
                <button type="button" class="rm-icon-btn edit"
                        onclick="openEditModal({{ $room->id }})"
                        title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="rm-icon-btn delete"
                        onclick="openDeleteModal({{ $room->id }}, '{{ addslashes($room->room_no) }}')"
                        title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="rm-empty">
        <div class="rm-empty-icon">
            <i class="bi bi-door-open"></i>
        </div>
        <h5>No Rooms Yet</h5>
        <p>Create rooms — beds will be auto-generated based on Normal + Bunker split.</p>
        <button type="button" class="rm-btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i>
            Add Your First Room
        </button>
    </div>
    @endforelse
</div>

{{-- CREATE / EDIT MODAL --}}
<div class="modal fade rm-modal" id="roomModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalTitle">
                    <i class="bi bi-door-open"></i>
                    Add New Room
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="roomForm" autocomplete="off">
                @csrf
                <input type="hidden" id="roomId" name="id" value="">

                <div class="modal-body">
                    <div class="rm-form-section">
                        <div class="rm-form-section-title">
                            <i class="bi bi-info-circle"></i>
                            Room Details
                        </div>

                        <div class="rm-form-group">
                            <label class="rm-form-label">
                                Hostel <span class="required">*</span>
                            </label>
                            <select class="rm-form-control" id="hostel_id" name="hostel_id" required>
                                <option value="">Select Hostel</option>
                                @foreach($hostels as $hostel)
                                    <option value="{{ $hostel->id }}">
                                        {{ $hostel->hostel_name }} ({{ $hostel->hostel_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="rm-form-error" id="error_hostel_id"></div>
                        </div>

                        <div class="rm-form-row">
                            <div class="rm-form-group">
                                <label class="rm-form-label">
                                    Room Number <span class="required">*</span>
                                </label>
                                <input type="text" class="rm-form-control"
                                       id="room_no" name="room_no"
                                       placeholder="e.g., 101, A-01" required>
                                <div class="rm-form-error" id="error_room_no"></div>
                            </div>

                            <div class="rm-form-group">
                                <label class="rm-form-label">
                                    Room Type <span class="required">*</span>
                                </label>
                                <select class="rm-form-control" id="room_type_id" name="room_type_id" required disabled>
                                    <option value="">Select Hostel First</option>
                                </select>
                                <div class="rm-form-error" id="error_room_type_id"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Bed Configuration --}}
                    <div class="rm-form-section" id="bedConfigSection" style="display:none;">
                        <div class="rm-form-section-title">
                            <i class="bi bi-grid-3x3-gap"></i>
                            Bed Configuration
                        </div>

                        <div class="rm-form-row">
                            <div class="rm-form-group">
                                <label class="rm-form-label">
                                    Normal Cot Count <span class="required">*</span>
                                </label>
                                <input type="number" class="rm-form-control"
                                       id="normal_cot_count" name="normal_cot_count"
                                       min="0" value="0" required>
                                <div class="rm-form-error" id="error_normal_cot_count"></div>
                            </div>

                            <div class="rm-form-group">
                                <label class="rm-form-label">
                                    Bunker Cot Count <span class="required">*</span>
                                </label>
                                <input type="number" class="rm-form-control"
                                       id="bunker_cot_count" name="bunker_cot_count"
                                       min="0" value="0" required>
                                <div class="rm-form-error" id="error_bunker_cot_count"></div>
                            </div>
                        </div>

                        <div class="rm-info-box" id="bedInfoBox">
                            <span>
                                <i class="bi bi-info-circle"></i>
                                Total beds: <strong id="totalBedsDisplay">0</strong>
                                <span id="sharingCountHint"></span>
                            </span>
                            <button type="button" class="rm-btn rm-btn-outline rm-btn-sm"
                                    onclick="autoSplitBeds()">
                                <i class="bi bi-magic"></i> Auto Split
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="rm-btn rm-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                        Cancel
                    </button>
                    <button type="submit" class="rm-btn rm-btn-gold" id="roomSubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="roomSubmitText">Save Room</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade rm-modal" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:2rem 1.5rem;">
                <div class="rm-delete-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 style="color:var(--sanjay-primary); font-weight:700; margin-bottom:0.5rem;">
                    Delete Room?
                </h5>
                <p style="color:#6b7280; font-size:0.85rem; margin-bottom:0.25rem;">
                    You are about to delete:
                </p>
                <p style="color:var(--sanjay-primary); font-weight:600; font-size:0.95rem; margin-bottom:1rem;"
                   id="deleteRoomName"></p>
                <p style="color:#ef4444; font-size:0.75rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    All beds in this room will also be deleted. Cannot delete if any bed is occupied.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:0.5rem;">
                <button type="button" class="rm-btn rm-btn-outline" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>
                <button type="button" class="rm-btn rm-btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    <span id="deleteBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CONTEXT MENU --}}
<div id="cardContextMenu" style="display:none; position:fixed; z-index:9999; background:white; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); border:1px solid #e5e7eb; min-width:170px; overflow:hidden;">
    <button type="button" class="rm-context-item" onclick="contextEdit()">
        <i class="bi bi-pencil"></i> Edit Room
    </button>
    <button type="button" class="rm-context-item danger" onclick="contextDelete()">
        <i class="bi bi-trash"></i> Delete Room
    </button>
</div>

@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL = "{{ url('admin/rooms') }}";
let currentDeleteId = null;
let currentContextId = null;
let currentRoomOriginalBeds = 0;
let allRoomTypes = @json($roomTypes);

function showToast(message, type = 'success') {
    if (typeof showFlashMessage === 'function') {
        showFlashMessage(message, type);
    } else {
        alert(message);
    }
}

function loadRoomTypesForHostel(hostelId, selectedRoomTypeId = null) {
    const $select = document.getElementById('room_type_id');
    $select.innerHTML = '<option value="">Loading...</option>';
    $select.disabled = true;

    if (!hostelId) {
        $select.innerHTML = '<option value="">Select Hostel First</option>';
        document.getElementById('bedConfigSection').style.display = 'none';
        return;
    }

    const filtered = allRoomTypes.filter(rt => rt.hostel_id == hostelId);

    if (filtered.length === 0) {
        $select.innerHTML = '<option value="">No room types for this hostel</option>';
        document.getElementById('bedConfigSection').style.display = 'none';
        return;
    }

    let html = '<option value="">Select Room Type</option>';
    filtered.forEach(rt => {
        const selected = selectedRoomTypeId && rt.id == selectedRoomTypeId ? 'selected' : '';
        html += `<option value="${rt.id}" data-sharing="${rt.sharing_count}" ${selected}>
            ${rt.room_type_name} (${rt.sharing_count} sharing)
        </option>`;
    });
    $select.innerHTML = html;
    $select.disabled = false;

    if (selectedRoomTypeId) {
        $select.dispatchEvent(new Event('change'));
    }
}

function autoSplitBeds() {
    const opt = document.getElementById('room_type_id').options[document.getElementById('room_type_id').selectedIndex];
    const sharing = parseInt(opt?.dataset?.sharing || 0);

    if (sharing < 1) {
        showToast('Select a room type first', 'error');
        return;
    }

    const normal = Math.ceil(sharing / 2);
    const bunker = sharing - normal;

    document.getElementById('normal_cot_count').value = normal;
    document.getElementById('bunker_cot_count').value = bunker;
    updateTotalBedsDisplay();
}

function updateTotalBedsDisplay() {
    const normal = parseInt(document.getElementById('normal_cot_count').value || 0);
    const bunker = parseInt(document.getElementById('bunker_cot_count').value || 0);
    const total = normal + bunker;

    document.getElementById('totalBedsDisplay').textContent = total;

    const opt = document.getElementById('room_type_id').options[document.getElementById('room_type_id').selectedIndex];
    const sharing = parseInt(opt?.dataset?.sharing || 0);

    const hintEl = document.getElementById('sharingCountHint');
    const infoBox = document.getElementById('bedInfoBox');

    infoBox.classList.remove('warning', 'success');

    if (sharing > 0 && total !== sharing) {
        hintEl.textContent = ` (must be ${sharing})`;
        hintEl.style.color = '#ef4444';
        hintEl.style.fontWeight = '600';
        infoBox.classList.add('warning');
    } else if (sharing > 0) {
        hintEl.textContent = ` ✓ matches ${sharing}-sharing`;
        hintEl.style.color = '#10b981';
        hintEl.style.fontWeight = '600';
        infoBox.classList.add('success');
    } else {
        hintEl.textContent = '';
    }
}

function openCreateModal() {
    resetForm();
    currentRoomOriginalBeds = 0;

    document.getElementById('roomModalTitle').innerHTML =
        '<i class="bi bi-door-open"></i> Add New Room';
    document.getElementById('roomSubmitText').textContent = 'Save Room';
    document.getElementById('roomId').value = '';
    document.getElementById('room_type_id').disabled = true;
    document.getElementById('room_type_id').innerHTML = '<option value="">Select Hostel First</option>';
    document.getElementById('bedConfigSection').style.display = 'none';

    new bootstrap.Modal(document.getElementById('roomModal')).show();
}

async function openEditModal(id) {
    resetForm();

    try {
        const response = await fetch(`${BASE_URL}/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const data = await response.json();

        if (data.success) {
            const room = data.room;

            document.getElementById('roomModalTitle').innerHTML =
                '<i class="bi bi-pencil-square"></i> Edit Room';
            document.getElementById('roomSubmitText').textContent = 'Update Room';
            document.getElementById('roomId').value = room.id;

            document.getElementById('hostel_id').value = room.hostel_id;
            document.getElementById('room_no').value = room.room_no;
            document.getElementById('normal_cot_count').value = room.normal_cot_count;
            document.getElementById('bunker_cot_count').value = room.bunker_cot_count;

            currentRoomOriginalBeds = (room.normal_cot_count || 0) + (room.bunker_cot_count || 0);

            loadRoomTypesForHostel(room.hostel_id, room.room_type_id);
            document.getElementById('bedConfigSection').style.display = 'block';
            updateTotalBedsDisplay();

            new bootstrap.Modal(document.getElementById('roomModal')).show();
        } else {
            showToast('Failed to load room', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to load room', 'error');
    }
}

function resetForm() {
    document.getElementById('roomForm').reset();
    document.getElementById('roomId').value = '';
    currentRoomOriginalBeds = 0;

    document.querySelectorAll('.rm-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.rm-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.getElementById('bedConfigSection').style.display = 'none';
}

document.getElementById('hostel_id').addEventListener('change', function() {
    loadRoomTypesForHostel(this.value);
    document.getElementById('bedConfigSection').style.display = 'none';
});

document.getElementById('room_type_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];

    if (!opt.value) {
        document.getElementById('bedConfigSection').style.display = 'none';
        return;
    }

    document.getElementById('bedConfigSection').style.display = 'block';

    const sharing = parseInt(opt.dataset.sharing || 0);

    // Create mode: auto-split
    if (!currentRoomOriginalBeds) {
        const normal = Math.ceil(sharing / 2);
        const bunker = sharing - normal;
        document.getElementById('normal_cot_count').value = normal;
        document.getElementById('bunker_cot_count').value = bunker;
    }

    updateTotalBedsDisplay();
});

document.getElementById('normal_cot_count').addEventListener('input', updateTotalBedsDisplay);
document.getElementById('bunker_cot_count').addEventListener('input', updateTotalBedsDisplay);

document.getElementById('roomForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('roomSubmitBtn');
    const submitText = document.getElementById('roomSubmitText');
    const originalText = submitText.textContent;
    const roomId = document.getElementById('roomId').value;
    const isEdit = roomId !== '';

    document.querySelectorAll('.rm-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.rm-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="rm-spinner"></span> Saving...';

    const formData = new FormData(this);
    const url = isEdit ? `${BASE_URL}/${roomId}` : BASE_URL;

    if (isEdit) formData.append('_method', 'PUT');

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('roomModal')).hide();
            setTimeout(() => window.location.reload(), 700);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errEl = document.getElementById(`error_${field}`);
                    const inputEl = document.getElementById(field);

                    if (errEl) {
                        errEl.textContent = data.errors[field][0];
                        errEl.classList.add('show');
                    }
                    if (inputEl) inputEl.classList.add('is-invalid');
                });
                showToast('Please fix the errors', 'error');
            } else {
                showToast(data.message || 'Something went wrong', 'error');
            }
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    } finally {
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
});

function openDeleteModal(id, roomNo) {
    currentDeleteId = id;
    document.getElementById('deleteRoomName').textContent = `Room ${roomNo}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!currentDeleteId) return;

    const btn = this;
    const btnText = document.getElementById('deleteBtnText');
    const originalText = btnText.textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="rm-spinner"></span> Deleting...';

    try {
        const response = await fetch(`${BASE_URL}/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();

            const card = document.querySelector(`.rm-card[data-id="${currentDeleteId}"]`);
            if (card) {
                card.style.transition = 'all 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => { card.remove(); updateCountLabel(); }, 300);
            }
        } else {
            showToast(data.message || 'Failed to delete', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false;
        btnText.textContent = originalText;
        currentDeleteId = null;
    }
});

const searchInput = document.getElementById('rmSearchInput');
const hostelFilter = document.getElementById('rmHostelFilter');
const statusFilter = document.getElementById('rmStatusFilter');

function applyFilters() {
    const search = searchInput.value.toLowerCase().trim();
    const hostelId = hostelFilter.value;
    const status = statusFilter.value;

    let visibleCount = 0;

    document.querySelectorAll('.rm-card').forEach(card => {
        const roomNo = card.getAttribute('data-room-no');
        const cardHostelId = card.getAttribute('data-hostel-id');
        const cardStatus = card.getAttribute('data-status');

        const matchesSearch = !search || roomNo.includes(search);
        const matchesHostel = !hostelId || cardHostelId === hostelId;
        const matchesStatus = !status || cardStatus === status;

        if (matchesSearch && matchesHostel && matchesStatus) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    updateCountLabel(visibleCount);
}

function updateCountLabel(count) {
    const total = document.querySelectorAll('.rm-card').length;
    const label = document.getElementById('rmCountLabel');
    if (count !== undefined && count !== total) {
        label.textContent = `${count} of ${total} rooms`;
    } else {
        label.textContent = `${total} rooms`;
    }
}

searchInput.addEventListener('input', applyFilters);
hostelFilter.addEventListener('change', applyFilters);
statusFilter.addEventListener('change', applyFilters);

const contextMenu = document.getElementById('cardContextMenu');

function toggleCardMenu(event, id) {
    event.stopPropagation();
    currentContextId = id;

    const rect = event.currentTarget.getBoundingClientRect();
    contextMenu.style.display = 'block';
    contextMenu.style.left = Math.min(rect.right - 170, window.innerWidth - 180) + 'px';
    contextMenu.style.top = (rect.bottom + 4) + 'px';
}

function hideContextMenu() { contextMenu.style.display = 'none'; }

function contextEdit() {
    if (currentContextId) openEditModal(currentContextId);
    hideContextMenu();
}

function contextDelete() {
    if (currentContextId) {
        const card = document.querySelector(`.rm-card[data-id="${currentContextId}"]`);
        if (card) {
            const roomNo = card.querySelector('.rm-card-title').textContent.replace('Room ', '').trim();
            openDeleteModal(currentContextId, roomNo);
        }
    }
    hideContextMenu();
}

document.addEventListener('click', hideContextMenu);
document.addEventListener('scroll', hideContextMenu, true);

document.addEventListener('DOMContentLoaded', () => updateCountLabel());
</script>
@endpush
