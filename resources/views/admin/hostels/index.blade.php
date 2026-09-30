@extends('layouts.office')

@section('title', 'Hostel Management — Sanjay PG Hostel')
@section('page_title', 'Hostel Management')

@push('styles')
<style>
    /* ═══════════════════════════════════════════
       HOSTEL MANAGEMENT
    ═══════════════════════════════════════════ */

    .hm-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .hm-page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .hm-page-title i { color: var(--sanjay-gold); }
    .hm-page-subtitle {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0.25rem 0 0 0;
    }

    /* ── Toolbar ── */
    .hm-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .hm-search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }
    .hm-search-box input {
        width: 100%;
        padding: 0.55rem 1rem 0.55rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        transition: all 0.2s;
        color: #374151;
    }
    .hm-search-box input:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .hm-search-box i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .hm-filter-select {
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
    .hm-filter-select:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }

    /* ── Buttons ── */
    .hm-btn-primary {
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
    .hm-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(10, 30, 63, 0.25);
        color: white;
    }

    /* ── Grid ── */
    .hm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
    }

    /* ── Cards ── */
    .hm-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .hm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.08);
        border-color: rgba(197, 160, 40, 0.3);
    }
    .hm-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--sanjay-gold), var(--sanjay-primary));
        opacity: 0;
        transition: opacity 0.3s;
    }
    .hm-card:hover::before { opacity: 1; }

    .hm-card-head {
        padding: 1rem 1.15rem 0.75rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .hm-card-icon {
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
    .hm-card-title-wrap { flex: 1; min-width: 0; }
    .hm-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0 0 0.15rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .hm-card-code {
        font-size: 0.68rem;
        color: #9ca3af;
        font-family: 'DM Mono', monospace;
        letter-spacing: 0.3px;
    }
    .hm-card-menu { position: relative; }
    .hm-card-menu-btn {
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
    .hm-card-menu-btn:hover {
        background: #f9fafb;
        border-color: var(--sanjay-gold);
        color: var(--sanjay-gold);
    }

    .hm-card-body {
        padding: 0 1.15rem 1rem;
        flex: 1;
    }
    .hm-card-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
        margin-bottom: 0.85rem;
    }
    .hm-stat-item {
        background: #f8fafc;
        border-radius: 9px;
        padding: 0.5rem 0.7rem;
        border: 1px solid #f1f5f9;
    }
    .hm-stat-label {
        font-size: 0.62rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 0.15rem;
    }
    .hm-stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        font-family: 'DM Mono', monospace;
        line-height: 1.1;
    }
    .hm-card-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #6b7280;
        padding: 0.3rem 0;
    }
    .hm-card-detail i {
        color: var(--sanjay-gold);
        font-size: 0.8rem;
        width: 14px;
        flex-shrink: 0;
    }
    .hm-card-detail span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Badges ── */
    .hm-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .hm-badge.active   { background: #dcfce7; color: #166534; }
    .hm-badge.inactive { background: #fee2e2; color: #991b1b; }
    .hm-badge.male     { background: #dbeafe; color: #1e40af; }
    .hm-badge.female   { background: #fce7f3; color: #9d174d; }
    .hm-badge.co-ed    { background: #f3e8ff; color: #6b21a8; }
    .hm-badge-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    /* ── Card Footer ── */
    .hm-card-footer {
        padding: 0.7rem 1.15rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #fafbfc;
    }
    .hm-card-actions {
        display: flex;
        gap: 0.35rem;
    }
    .hm-icon-btn {
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
    .hm-icon-btn:hover { transform: translateY(-1px); }
    .hm-icon-btn.edit:hover   { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    .hm-icon-btn.delete:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
    .hm-icon-btn.toggle:hover { background: #fefce8; border-color: #eab308; color: #ca8a04; }

    /* ── Empty State ── */
    .hm-empty {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        border: 2px dashed #e5e7eb;
        grid-column: 1 / -1;
    }
    .hm-empty-icon {
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
    .hm-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .hm-empty p  { color: #6b7280; font-size: 0.85rem; margin-bottom: 1.5rem; }

    /* ═══════════════════════════════════════════
       MODAL
    ═══════════════════════════════════════════ */
    .hm-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.15);
    }
    .hm-modal .modal-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        padding: 1.1rem 1.5rem;
    }
    .hm-modal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .hm-modal .modal-title i { color: var(--sanjay-gold); }
    .hm-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .hm-modal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    .hm-modal .modal-body::-webkit-scrollbar { width: 4px; }
    .hm-modal .modal-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .hm-modal .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f3f4f6;
        background: #fafbfc;
    }

    /* ── Form ── */
    .hm-form-section { margin-bottom: 1.5rem; }
    .hm-form-section:last-child { margin-bottom: 0; }
    .hm-form-section-title {
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
    .hm-form-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(197, 160, 40, 0.3), transparent);
    }
    .hm-form-group { margin-bottom: 0.9rem; }
    .hm-form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .hm-form-label .required { color: #ef4444; margin-left: 2px; }
    .hm-form-control {
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
    .hm-form-control:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .hm-form-control::placeholder { color: #9ca3af; }
    .hm-form-control.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .hm-form-error {
        font-size: 0.7rem;
        color: #ef4444;
        margin-top: 0.25rem;
        display: none;
    }
    .hm-form-error.show { display: block; }
    select.hm-form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.85rem center;
        padding-right: 2.2rem;
    }

    .hm-form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.9rem;
    }
    @media (max-width: 576px) {
        .hm-form-row { grid-template-columns: 1fr; }
    }

    /* ── Buttons ── */
    .hm-btn {
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
    .hm-btn-gold {
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
    }
    .hm-btn-gold:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(197, 160, 40, 0.35);
    }
    .hm-btn-gold:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .hm-btn-outline {
        background: white;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }
    .hm-btn-outline:hover { background: #f9fafb; color: #374151; }
    .hm-btn-danger { background: #ef4444; color: white; }
    .hm-btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }

    /* ── Spinner ── */
    .hm-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: hm-spin 0.6s linear infinite;
        display: inline-block;
    }
    @keyframes hm-spin { to { transform: rotate(360deg); } }

    /* ── Delete Modal ── */
    .hm-delete-icon {
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
    .context-menu-item {
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
    .context-menu-item:hover { background: #f9fafb; color: var(--sanjay-primary); }
    .context-menu-item.danger { color: #ef4444; }
    .context-menu-item.danger:hover { background: #fef2f2; }
    .context-menu-item i { font-size: 0.85rem; width: 16px; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .hm-page-header { flex-direction: column; align-items: flex-start; }
        .hm-toolbar { flex-direction: column; align-items: stretch; }
        .hm-search-box { max-width: none; }
        .hm-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════ --}}
<div class="hm-page-header">
    <div>
        <h2 class="hm-page-title">
            <i class="bi bi-building"></i>
            Hostel Management
        </h2>
        <p class="hm-page-subtitle">Manage all hostels, their details, and biometric settings</p>
    </div>
    <button type="button" class="hm-btn-primary" onclick="openCreateModal()">
        <i class="bi bi-plus-lg"></i>
        Add New Hostel
    </button>
</div>

{{-- ═══════════════════════════════════════════
     TOOLBAR
═══════════════════════════════════════════ --}}
<div class="hm-toolbar">
    <div class="hm-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="hmSearchInput" placeholder="Search hostels by name or code...">
    </div>

    <select class="hm-filter-select" id="hmTypeFilter">
        <option value="">All Types</option>
        <option value="male">Men</option>
        <option value="female">Women</option>
        <option value="co-ed">Co-ed</option>
    </select>

    <select class="hm-filter-select" id="hmStatusFilter">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>

    <span style="margin-left:auto; font-size:0.75rem; color:#9ca3af;" id="hmCountLabel">
        {{ $hostels->count() }} hostels
    </span>
</div>

{{-- ═══════════════════════════════════════════
     HOSTEL GRID
═══════════════════════════════════════════ --}}
<div class="hm-grid" id="hmGrid">
    @forelse($hostels as $hostel)
    <div class="hm-card"
         data-id="{{ $hostel->id }}"
         data-name="{{ strtolower($hostel->hostel_name) }}"
         data-code="{{ strtolower($hostel->hostel_code) }}"
         data-type="{{ $hostel->hostel_type }}"
         data-status="{{ $hostel->status }}">

        {{-- Card Head --}}
        <div class="hm-card-head">
            <div class="hm-card-icon">
                <i class="bi bi-{{ $hostel->type_icon }}"></i>
            </div>
            <div class="hm-card-title-wrap">
                <h3 class="hm-card-title" title="{{ $hostel->hostel_name }}">
                    {{ $hostel->hostel_name }}
                </h3>
                <div class="hm-card-code">{{ $hostel->hostel_code }}</div>
            </div>
            <div class="hm-card-menu">
                <button type="button" class="hm-card-menu-btn"
                        onclick="toggleCardMenu(event, {{ $hostel->id }})">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
            </div>
        </div>

        {{-- Card Body --}}
        <div class="hm-card-body">
            {{-- Badges --}}
            <div style="display:flex; gap:0.35rem; flex-wrap:wrap; margin-bottom:0.85rem;">
                <span class="hm-badge {{ $hostel->status }}">
                    <span class="hm-badge-dot"></span>
                    {{ ucfirst($hostel->status) }}
                </span>
                <span class="hm-badge {{ $hostel->hostel_type }}">
                    <i class="bi bi-{{ $hostel->type_icon }}"></i>
                    {{ $hostel->type_label }}
                </span>
            </div>

            {{-- Stats --}}
            <div class="hm-card-stats">
                <div class="hm-stat-item">
                    <div class="hm-stat-label">Rooms</div>
                    <div class="hm-stat-value">{{ $hostel->rooms_count ?? 0 }}</div>
                </div>
                <div class="hm-stat-item">
                    <div class="hm-stat-label">Residents</div>
                    <div class="hm-stat-value">{{ $hostel->residents_count ?? 0 }}</div>
                </div>
            </div>

            {{-- Details --}}
            @if($hostel->phone)
            <div class="hm-card-detail">
                <i class="bi bi-telephone"></i>
                <span>{{ $hostel->phone }}</span>
            </div>
            @endif

            @if($hostel->email)
            <div class="hm-card-detail">
                <i class="bi bi-envelope"></i>
                <span>{{ $hostel->email }}</span>
            </div>
            @endif

            @if($hostel->address)
            <div class="hm-card-detail">
                <i class="bi bi-geo-alt"></i>
                <span>{{ \Illuminate\Support\Str::limit($hostel->address, 40) }}</span>
            </div>
            @endif

            @if($hostel->biometric_device_name)
            <div class="hm-card-detail">
                <i class="bi bi-fingerprint"></i>
                <span>{{ $hostel->biometric_device_name }}</span>
            </div>
            @endif
        </div>

        {{-- Card Footer --}}
        <div class="hm-card-footer">
            <span style="font-size:0.68rem; color:#9ca3af;">
                <i class="bi bi-clock"></i>
                {{ $hostel->created_at->format('d M Y') }}
            </span>
            <div class="hm-card-actions">
                <button type="button" class="hm-icon-btn toggle"
                        onclick="toggleStatus({{ $hostel->id }})"
                        title="{{ $hostel->status === 'active' ? 'Deactivate' : 'Activate' }}">
                    <i class="bi bi-{{ $hostel->status === 'active' ? 'pause-circle' : 'play-circle' }}"></i>
                </button>
                <button type="button" class="hm-icon-btn edit"
                        onclick="openEditModal({{ $hostel->id }})"
                        title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="hm-icon-btn delete"
                        onclick="openDeleteModal({{ $hostel->id }}, '{{ addslashes($hostel->hostel_name) }}')"
                        title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="hm-empty">
        <div class="hm-empty-icon">
            <i class="bi bi-building-add"></i>
        </div>
        <h5>No Hostels Yet</h5>
        <p>Get started by adding your first hostel to the system.</p>
        <button type="button" class="hm-btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i>
            Add Your First Hostel
        </button>
    </div>
    @endforelse
</div>

{{-- ═══════════════════════════════════════════
     CREATE / EDIT MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade hm-modal" id="hostelModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hostelModalTitle">
                    <i class="bi bi-building-add"></i>
                    Add New Hostel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="hostelForm" autocomplete="off">
                @csrf
                <input type="hidden" id="hostelId" name="id" value="">

                <div class="modal-body">
                    {{-- Basic Info --}}
                    <div class="hm-form-section">
                        <div class="hm-form-section-title">
                            <i class="bi bi-info-circle"></i>
                            Basic Information
                        </div>

                        <div class="hm-form-row">
                            <div class="hm-form-group">
                                <label class="hm-form-label">
                                    Hostel Code <span class="required">*</span>
                                </label>
                                <input type="text" class="hm-form-control"
                                       id="hostel_code" name="hostel_code"
                                       placeholder="e.g., SPGH-001" required>
                                <div class="hm-form-error" id="error_hostel_code"></div>
                            </div>

                            <div class="hm-form-group">
                                <label class="hm-form-label">
                                    Hostel Name <span class="required">*</span>
                                </label>
                                <input type="text" class="hm-form-control"
                                       id="hostel_name" name="hostel_name"
                                       placeholder="e.g., Sanjay PG Hostel - Main" required>
                                <div class="hm-form-error" id="error_hostel_name"></div>
                            </div>
                        </div>

                        <div class="hm-form-row">
                            <div class="hm-form-group">
                                <label class="hm-form-label">
                                    Hostel Type <span class="required">*</span>
                                </label>
                                <select class="hm-form-control" id="hostel_type" name="hostel_type" required>
                                    <option value="">Select Type</option>
                                    <option value="male">Men</option>
                                    <option value="female">Women</option>
                                    <option value="co-ed">Co-ed</option>
                                </select>
                                <div class="hm-form-error" id="error_hostel_type"></div>
                            </div>

                            <div class="hm-form-group">
                                <label class="hm-form-label">
                                    Status <span class="required">*</span>
                                </label>
                                <select class="hm-form-control" id="status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="hm-form-error" id="error_status"></div>
                            </div>
                        </div>

                        <div class="hm-form-group">
                            <label class="hm-form-label">Address</label>
                            <textarea class="hm-form-control" id="address" name="address"
                                      rows="2" placeholder="Full address of the hostel"></textarea>
                            <div class="hm-form-error" id="error_address"></div>
                        </div>

                        <div class="hm-form-row">
                            <div class="hm-form-group">
                                <label class="hm-form-label">Phone</label>
                                <input type="text" class="hm-form-control"
                                       id="phone" name="phone" placeholder="+91 98765 43210">
                                <div class="hm-form-error" id="error_phone"></div>
                            </div>

                            <div class="hm-form-group">
                                <label class="hm-form-label">Email</label>
                                <input type="email" class="hm-form-control"
                                       id="email" name="email" placeholder="hostel@example.com">
                                <div class="hm-form-error" id="error_email"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Biometric --}}
                    <div class="hm-form-section">
                        <div class="hm-form-section-title">
                            <i class="bi bi-fingerprint"></i>
                            Biometric Device Settings
                        </div>

                        <div class="hm-form-row">
                            <div class="hm-form-group">
                                <label class="hm-form-label">Device ID</label>
                                <input type="text" class="hm-form-control"
                                       id="biometric_device_id" name="biometric_device_id"
                                       placeholder="e.g., BIO-001">
                            </div>

                            <div class="hm-form-group">
                                <label class="hm-form-label">Device Name</label>
                                <input type="text" class="hm-form-control"
                                       id="biometric_device_name" name="biometric_device_name"
                                       placeholder="e.g., Main Gate Scanner">
                            </div>
                        </div>

                        <div class="hm-form-row">
                            <div class="hm-form-group">
                                <label class="hm-form-label">IP Address</label>
                                <input type="text" class="hm-form-control"
                                       id="biometric_ip_address" name="biometric_ip_address"
                                       placeholder="e.g., 192.168.1.100">
                            </div>

                            <div class="hm-form-group">
                                <label class="hm-form-label">Port</label>
                                <input type="number" class="hm-form-control"
                                       id="biometric_port" name="biometric_port"
                                       placeholder="e.g., 4370" min="1" max="65535">
                            </div>
                        </div>

                        <div class="hm-form-row">
                            <div class="hm-form-group">
                                <label class="hm-form-label">Location Code</label>
                                <input type="text" class="hm-form-control"
                                       id="biometric_location_code" name="biometric_location_code"
                                       placeholder="e.g., MAIN-GATE">
                            </div>

                            <div class="hm-form-group">
                                <label class="hm-form-label">Employee Code Prefix</label>
                                <input type="text" class="hm-form-control"
                                       id="employee_code_prefix" name="employee_code_prefix"
                                       placeholder="e.g., EMP-">
                            </div>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="hm-form-section">
                        <div class="hm-form-section-title">
                            <i class="bi bi-credit-card"></i>
                            Payment Settings
                        </div>

                        <div class="hm-form-row">
                            <div class="hm-form-group">
                                <label class="hm-form-label">UPI ID</label>
                                <input type="text" class="hm-form-control"
                                       id="upi_id" name="upi_id"
                                       placeholder="e.g., sanjaypg@upi">
                            </div>

                            <div class="hm-form-group">
                                <label class="hm-form-label">UPI Payee Name</label>
                                <input type="text" class="hm-form-control"
                                       id="upi_payee_name" name="upi_payee_name"
                                       placeholder="e.g., Sanjay PG Hostel">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="hm-btn hm-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                        Cancel
                    </button>
                    <button type="submit" class="hm-btn hm-btn-gold" id="hostelSubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="hostelSubmitText">Save Hostel</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     DELETE MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade hm-modal" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:2rem 1.5rem;">
                <div class="hm-delete-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 style="color:var(--sanjay-primary); font-weight:700; margin-bottom:0.5rem;">
                    Delete Hostel?
                </h5>
                <p style="color:#6b7280; font-size:0.85rem; margin-bottom:0.25rem;">
                    You are about to delete:
                </p>
                <p style="color:var(--sanjay-primary); font-weight:600; font-size:0.95rem; margin-bottom:1rem;"
                   id="deleteHostelName"></p>
                <p style="color:#ef4444; font-size:0.75rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:0.5rem;">
                <button type="button" class="hm-btn hm-btn-outline" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>
                <button type="button" class="hm-btn hm-btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    <span id="deleteBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CONTEXT MENU --}}
<div id="cardContextMenu" style="display:none; position:fixed; z-index:9999; background:white; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); border:1px solid #e5e7eb; min-width:160px; overflow:hidden;">
    <button type="button" class="context-menu-item" onclick="contextEdit()">
        <i class="bi bi-pencil"></i> Edit Hostel
    </button>
    <button type="button" class="context-menu-item" onclick="contextToggle()">
        <i class="bi bi-arrow-repeat"></i> Toggle Status
    </button>
    <button type="button" class="context-menu-item danger" onclick="contextDelete()">
        <i class="bi bi-trash"></i> Delete Hostel
    </button>
</div>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════
// HOSTEL MANAGEMENT - JS
// ═══════════════════════════════════════════

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL = "{{ url('admin/hostels') }}";
let currentDeleteId = null;
let currentContextId = null;

// ── Toast ──
function showToast(message, type = 'success') {
    if (typeof showFlashMessage === 'function') {
        showFlashMessage(message, type);
    } else {
        alert(message);
    }
}

// ── Open Create Modal ──
function openCreateModal() {
    resetForm();
    document.getElementById('hostelModalTitle').innerHTML =
        '<i class="bi bi-building-add"></i> Add New Hostel';
    document.getElementById('hostelSubmitText').textContent = 'Save Hostel';
    document.getElementById('hostelId').value = '';

    const modal = new bootstrap.Modal(document.getElementById('hostelModal'));
    modal.show();
}

// ── Open Edit Modal ──
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
            const hostel = data.hostel;

            document.getElementById('hostelModalTitle').innerHTML =
                '<i class="bi bi-pencil-square"></i> Edit Hostel';
            document.getElementById('hostelSubmitText').textContent = 'Update Hostel';
            document.getElementById('hostelId').value = hostel.id;

            const fields = [
                'hostel_code', 'hostel_name', 'hostel_type', 'status',
                'address', 'phone', 'email',
                'biometric_device_id', 'biometric_device_name',
                'biometric_ip_address', 'biometric_port',
                'biometric_location_code', 'employee_code_prefix',
                'upi_id', 'upi_payee_name'
            ];

            fields.forEach(field => {
                const el = document.getElementById(field);
                if (el) el.value = hostel[field] || '';
            });

            const modal = new bootstrap.Modal(document.getElementById('hostelModal'));
            modal.show();
        } else {
            showToast('Failed to load hostel', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to load hostel', 'error');
    }
}

// ── Reset Form ──
function resetForm() {
    document.getElementById('hostelForm').reset();
    document.getElementById('hostelId').value = '';

    document.querySelectorAll('.hm-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.hm-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

// ── Submit ──
document.getElementById('hostelForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('hostelSubmitBtn');
    const submitText = document.getElementById('hostelSubmitText');
    const originalText = submitText.textContent;
    const hostelId = document.getElementById('hostelId').value;
    const isEdit = hostelId !== '';

    document.querySelectorAll('.hm-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.hm-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="hm-spinner"></span> Saving...';

    const formData = new FormData(this);
    const url = isEdit ? `${BASE_URL}/${hostelId}` : BASE_URL;

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
            bootstrap.Modal.getInstance(document.getElementById('hostelModal')).hide();
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

// ── Open Delete Modal ──
function openDeleteModal(id, name) {
    currentDeleteId = id;
    document.getElementById('deleteHostelName').textContent = `"${name}"`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Confirm Delete ──
document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!currentDeleteId) return;

    const btn = this;
    const btnText = document.getElementById('deleteBtnText');
    const originalText = btnText.textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="hm-spinner"></span> Deleting...';

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

            const card = document.querySelector(`.hm-card[data-id="${currentDeleteId}"]`);
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

// ── Toggle Status ──
async function toggleStatus(id) {
    try {
        const response = await fetch(`${BASE_URL}/${id}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');

            const card = document.querySelector(`.hm-card[data-id="${id}"]`);
            if (card) {
                const badge = card.querySelector('.hm-badge');
                const toggleBtn = card.querySelector('.hm-icon-btn.toggle');

                if (data.status === 'active') {
                    badge.className = 'hm-badge active';
                    badge.innerHTML = '<span class="hm-badge-dot"></span> Active';
                    toggleBtn.title = 'Deactivate';
                    toggleBtn.innerHTML = '<i class="bi bi-pause-circle"></i>';
                } else {
                    badge.className = 'hm-badge inactive';
                    badge.innerHTML = '<span class="hm-badge-dot"></span> Inactive';
                    toggleBtn.title = 'Activate';
                    toggleBtn.innerHTML = '<i class="bi bi-play-circle"></i>';
                }
                card.setAttribute('data-status', data.status);
            }
        } else {
            showToast(data.message || 'Failed to update', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    }
}

// ── Search & Filter ──
const searchInput = document.getElementById('hmSearchInput');
const typeFilter = document.getElementById('hmTypeFilter');
const statusFilter = document.getElementById('hmStatusFilter');

function applyFilters() {
    const search = searchInput.value.toLowerCase().trim();
    const type = typeFilter.value;
    const status = statusFilter.value;

    let visibleCount = 0;

    document.querySelectorAll('.hm-card').forEach(card => {
        const name = card.getAttribute('data-name');
        const code = card.getAttribute('data-code');
        const cardType = card.getAttribute('data-type');
        const cardStatus = card.getAttribute('data-status');

        const matchesSearch = !search || name.includes(search) || code.includes(search);
        const matchesType = !type || cardType === type;
        const matchesStatus = !status || cardStatus === status;

        if (matchesSearch && matchesType && matchesStatus) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    updateCountLabel(visibleCount);
}

function updateCountLabel(count) {
    const total = document.querySelectorAll('.hm-card').length;
    const label = document.getElementById('hmCountLabel');
    if (count !== undefined && count !== total) {
        label.textContent = `${count} of ${total} hostels`;
    } else {
        label.textContent = `${total} hostels`;
    }
}

searchInput.addEventListener('input', applyFilters);
typeFilter.addEventListener('change', applyFilters);
statusFilter.addEventListener('change', applyFilters);

// ── Context Menu ──
const contextMenu = document.getElementById('cardContextMenu');

function toggleCardMenu(event, id) {
    event.stopPropagation();
    currentContextId = id;

    const rect = event.currentTarget.getBoundingClientRect();
    contextMenu.style.display = 'block';
    contextMenu.style.left = Math.min(rect.right - 160, window.innerWidth - 170) + 'px';
    contextMenu.style.top = (rect.bottom + 4) + 'px';
}

function hideContextMenu() { contextMenu.style.display = 'none'; }

function contextEdit() {
    if (currentContextId) openEditModal(currentContextId);
    hideContextMenu();
}

function contextToggle() {
    if (currentContextId) toggleStatus(currentContextId);
    hideContextMenu();
}

function contextDelete() {
    if (currentContextId) {
        const card = document.querySelector(`.hm-card[data-id="${currentContextId}"]`);
        if (card) {
            const name = card.querySelector('.hm-card-title').textContent.trim();
            openDeleteModal(currentContextId, name);
        }
    }
    hideContextMenu();
}

document.addEventListener('click', hideContextMenu);
document.addEventListener('scroll', hideContextMenu, true);

// ── Init ──
document.addEventListener('DOMContentLoaded', () => updateCountLabel());
</script>
@endpush
