@extends('layouts.office')

@section('title', 'Hostel Allocation — Sanjay PG Hostel')
@section('page_title', 'Hostel Allocation')

@push('styles')
<style>
    /* ═══════════════════════════════════════════
       HOSTEL ALLOCATION PAGE
    ═══════════════════════════════════════════ */

    .ha-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .ha-page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ha-page-title i { color: var(--sanjay-gold); }
    .ha-page-subtitle {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0.25rem 0 0 0;
    }

    /* ── Toolbar ── */
    .ha-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .ha-search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }
    .ha-search-box input {
        width: 100%;
        padding: 0.55rem 1rem 0.55rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        transition: all 0.2s;
        color: #374151;
    }
    .ha-search-box input:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .ha-search-box i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .ha-filter-select {
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
    .ha-filter-select:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }

    /* ── Buttons ── */
    .ha-btn-primary {
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
    .ha-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(10, 30, 63, 0.25);
        color: white;
    }

    /* ── Grid ── */
    .ha-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1rem;
    }

    /* ── Cards ── */
    .ha-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .ha-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.08);
        border-color: rgba(197, 160, 40, 0.3);
    }
    .ha-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--sanjay-gold), var(--sanjay-primary));
        opacity: 0;
        transition: opacity 0.3s;
    }
    .ha-card:hover::before { opacity: 1; }

    .ha-card-head {
        padding: 1rem 1.15rem 0.75rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .ha-card-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .ha-card-title-wrap { flex: 1; min-width: 0; }
    .ha-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0 0 0.15rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ha-card-email {
        font-size: 0.72rem;
        color: #9ca3af;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ha-card-body {
        padding: 0 1.15rem 1rem;
        flex: 1;
    }

    .ha-card-badges {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
        margin-bottom: 0.85rem;
    }

    .ha-card-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #6b7280;
        padding: 0.3rem 0;
    }
    .ha-card-detail i {
        color: var(--sanjay-gold);
        font-size: 0.8rem;
        width: 14px;
        flex-shrink: 0;
    }

    /* ── Allocated Hostels ── */
    .ha-allocated-section {
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px dashed #e5e7eb;
    }
    .ha-allocated-label {
        font-size: 0.62rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .ha-allocated-label i { color: var(--sanjay-gold); font-size: 0.7rem; }
    .ha-hostel-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .ha-hostel-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        background: linear-gradient(135deg, #fef9e7, #fffbf0);
        border: 1px solid rgba(197, 160, 40, 0.25);
        border-radius: 6px;
        font-size: 0.68rem;
        color: var(--sanjay-primary);
        font-weight: 600;
    }
    .ha-hostel-tag i { font-size: 0.6rem; color: var(--sanjay-gold); }
    .ha-no-hostel {
        font-size: 0.72rem;
        color: #9ca3af;
        font-style: italic;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* ── Badges ── */
    .ha-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .ha-badge.active   { background: #dcfce7; color: #166534; }
    .ha-badge.inactive { background: #fee2e2; color: #991b1b; }
    .ha-badge.role     { background: #fef3c7; color: #92400e; }
    .ha-badge-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    /* ── Card Footer ── */
    .ha-card-footer {
        padding: 0.7rem 1.15rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #fafbfc;
    }
    .ha-card-actions {
        display: flex;
        gap: 0.35rem;
    }
    .ha-icon-btn {
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
    .ha-icon-btn:hover { transform: translateY(-1px); }
    .ha-icon-btn.allocate:hover { background: #f0fdf4; border-color: #22c55e; color: #16a34a; }
    .ha-icon-btn.edit:hover   { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    .ha-icon-btn.delete:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
    .ha-icon-btn.toggle:hover { background: #fefce8; border-color: #eab308; color: #ca8a04; }

    /* ── Empty State ── */
    .ha-empty {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        border: 2px dashed #e5e7eb;
        grid-column: 1 / -1;
    }
    .ha-empty-icon {
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
    .ha-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .ha-empty p  { color: #6b7280; font-size: 0.85rem; margin-bottom: 1.5rem; }

    /* ═══════════════════════════════════════════
       MODAL
    ═══════════════════════════════════════════ */
    .ha-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.15);
    }
    .ha-modal .modal-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        padding: 1.1rem 1.5rem;
    }
    .ha-modal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ha-modal .modal-title i { color: var(--sanjay-gold); }
    .ha-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .ha-modal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    .ha-modal .modal-body::-webkit-scrollbar { width: 4px; }
    .ha-modal .modal-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .ha-modal .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f3f4f6;
        background: #fafbfc;
    }

    /* ── Form ── */
    .ha-form-section { margin-bottom: 1.5rem; }
    .ha-form-section:last-child { margin-bottom: 0; }
    .ha-form-section-title {
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
    .ha-form-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(197, 160, 40, 0.3), transparent);
    }
    .ha-form-group { margin-bottom: 0.9rem; }
    .ha-form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .ha-form-label .required { color: #ef4444; margin-left: 2px; }
    .ha-form-control {
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
    .ha-form-control:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .ha-form-control::placeholder { color: #9ca3af; }
    .ha-form-control.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .ha-form-error {
        font-size: 0.7rem;
        color: #ef4444;
        margin-top: 0.25rem;
        display: none;
    }
    .ha-form-error.show { display: block; }
    select.ha-form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.85rem center;
        padding-right: 2.2rem;
    }

    .ha-form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.9rem;
    }
    @media (max-width: 576px) {
        .ha-form-row { grid-template-columns: 1fr; }
    }

    /* ── Hostel Checkbox Grid ── */
    .ha-hostel-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 0.6rem;
        max-height: 280px;
        overflow-y: auto;
        padding: 0.6rem;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #f1f5f9;
    }
    .ha-hostel-grid::-webkit-scrollbar { width: 4px; }
    .ha-hostel-grid::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    .ha-hostel-checkbox {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.55rem 0.75rem;
        background: white;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.78rem;
        user-select: none;
    }
    .ha-hostel-checkbox:hover {
        border-color: var(--sanjay-gold);
        background: #fffbf0;
    }
    .ha-hostel-checkbox input[type="checkbox"] {
        accent-color: var(--sanjay-gold);
        width: 16px;
        height: 16px;
        cursor: pointer;
        flex-shrink: 0;
    }
    .ha-hostel-checkbox.checked {
        background: #fffbf0;
        border-color: var(--sanjay-gold);
    }
    .ha-hostel-checkbox-info {
        flex: 1;
        min-width: 0;
    }
    .ha-hostel-checkbox-name {
        font-weight: 600;
        color: var(--sanjay-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.78rem;
    }
    .ha-hostel-checkbox-code {
        font-size: 0.65rem;
        color: #9ca3af;
        font-family: 'DM Mono', monospace;
    }

    /* ── Select All Bar ── */
    .ha-select-all-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.6rem;
        padding: 0.5rem 0.75rem;
        background: #fffbf0;
        border-radius: 8px;
        border: 1px solid rgba(197, 160, 40, 0.2);
    }
    .ha-select-all-bar label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--sanjay-primary);
        cursor: pointer;
        margin: 0;
    }
    .ha-select-all-bar input[type="checkbox"] {
        accent-color: var(--sanjay-gold);
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .ha-selected-count {
        font-size: 0.72rem;
        color: #92400e;
        font-weight: 600;
    }

    /* ── Switch ── */
    .ha-switch {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    .ha-switch input { display: none; }
    .ha-switch-track {
        width: 40px;
        height: 22px;
        background: #e5e7eb;
        border-radius: 20px;
        position: relative;
        transition: all 0.3s;
    }
    .ha-switch-track::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .ha-switch input:checked + .ha-switch-track {
        background: var(--sanjay-gold);
    }
    .ha-switch input:checked + .ha-switch-track::after {
        transform: translateX(18px);
    }
    .ha-switch-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #374151;
    }

    /* ── Buttons ── */
    .ha-btn {
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
    .ha-btn-gold {
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
    }
    .ha-btn-gold:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(197, 160, 40, 0.35);
    }
    .ha-btn-gold:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .ha-btn-outline {
        background: white;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }
    .ha-btn-outline:hover { background: #f9fafb; color: #374151; }
    .ha-btn-danger { background: #ef4444; color: white; }
    .ha-btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }

    /* ── Spinner ── */
    .ha-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: ha-spin 0.6s linear infinite;
        display: inline-block;
    }
    @keyframes ha-spin { to { transform: rotate(360deg); } }

    /* ── Delete Modal ── */
    .ha-delete-icon {
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

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .ha-page-header { flex-direction: column; align-items: flex-start; }
        .ha-toolbar { flex-direction: column; align-items: stretch; }
        .ha-search-box { max-width: none; }
        .ha-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════ --}}
<div class="ha-page-header">
    <div>
        <h2 class="ha-page-title">
            <i class="bi bi-diagram-3"></i>
            Hostel Allocation
        </h2>
        <p class="ha-page-subtitle">Allocate one or more hostels to account users</p>
    </div>
    <button type="button" class="ha-btn-primary" onclick="openCreateModal()">
        <i class="bi bi-person-plus"></i>
        Add Account User
    </button>
</div>

{{-- ═══════════════════════════════════════════
     TOOLBAR
═══════════════════════════════════════════ --}}
<div class="ha-toolbar">
    <div class="ha-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="haSearchInput" placeholder="Search by name, email or phone...">
    </div>

    <select class="ha-filter-select" id="haStatusFilter">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>

    <select class="ha-filter-select" id="haAllocationFilter">
        <option value="">All Allocations</option>
        <option value="allocated">Has Hostels</option>
        <option value="unallocated">No Hostels</option>
    </select>

    <span style="margin-left:auto; font-size:0.75rem; color:#9ca3af;" id="haCountLabel">
        {{ $users->total() }} users
    </span>
</div>

{{-- ═══════════════════════════════════════════
     USER GRID
═══════════════════════════════════════════ --}}
<div class="ha-grid" id="haGrid">
    @forelse($users as $user)
        @php
            $assignedHostelIds = is_array($user->hostel_ids) ? $user->hostel_ids : [];
            $allocatedCount = count($assignedHostelIds);
            $status = $user->is_active ? 'active' : 'inactive';
        @endphp
        <div class="ha-card"
             data-id="{{ $user->id }}"
             data-name="{{ strtolower($user->name) }}"
             data-email="{{ strtolower($user->email) }}"
             data-phone="{{ $user->phone }}"
             data-status="{{ $status }}"
             data-allocated="{{ $allocatedCount > 0 ? 'allocated' : 'unallocated' }}">

            {{-- Card Head --}}
            <div class="ha-card-head">
                <div class="ha-card-avatar">
                    {{ $user->initials }}
                </div>
                <div class="ha-card-title-wrap">
                    <h3 class="ha-card-title" title="{{ $user->name }}">
                        {{ $user->name }}
                    </h3>
                    <div class="ha-card-email" title="{{ $user->email }}">
                        {{ $user->email }}
                    </div>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="ha-card-body">
                {{-- Badges --}}
                <div class="ha-card-badges">
                    <span class="ha-badge {{ $status }}">
                        <span class="ha-badge-dot"></span>
                        {{ ucfirst($status) }}
                    </span>
                    <span class="ha-badge role">
                        <i class="bi bi-person-badge"></i>
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                {{-- Phone --}}
                @if($user->phone)
                <div class="ha-card-detail">
                    <i class="bi bi-telephone"></i>
                    <span>{{ $user->phone }}</span>
                </div>
                @endif

                {{-- Allocated Hostels --}}
                <div class="ha-allocated-section">
                    <div class="ha-allocated-label">
                        <i class="bi bi-building"></i>
                        Allocated Hostels ({{ $allocatedCount }})
                    </div>
                    <div class="ha-hostel-tags" id="hostelTags-{{ $user->id }}">
                        @if($allocatedCount > 0)
                            @foreach($assignedHostelIds as $hid)
                                @if(isset($hostelMap[$hid]))
                                    <span class="ha-hostel-tag">
                                        <i class="bi bi-building-check"></i>
                                        {{ $hostelMap[$hid] }}
                                    </span>
                                @endif
                            @endforeach
                        @else
                            <span class="ha-no-hostel">
                                <i class="bi bi-dash-circle"></i>
                                No hostels allocated
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="ha-card-footer">
                <span style="font-size:0.68rem; color:#9ca3af;">
                    <i class="bi bi-clock"></i>
                    {{ $user->created_at->format('d M Y') }}
                </span>
                <div class="ha-card-actions">
                    <button type="button" class="ha-icon-btn allocate"
                            onclick="openAllocateModal({{ $user->id }})"
                            title="Allocate Hostels">
                        <i class="bi bi-building-gear"></i>
                    </button>
                    <button type="button" class="ha-icon-btn edit"
                            onclick="openEditModal({{ $user->id }})"
                            title="Edit User">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="ha-icon-btn toggle"
                            onclick="toggleStatus({{ $user->id }})"
                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                        <i class="bi bi-{{ $user->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                    </button>
                    <button type="button" class="ha-icon-btn delete"
                            onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                            title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    @empty
    <div class="ha-empty">
        <div class="ha-empty-icon">
            <i class="bi bi-people"></i>
        </div>
        <h5>No Account Users Yet</h5>
        <p>Get started by adding your first account user to allocate hostels.</p>
        <button type="button" class="ha-btn-primary" onclick="openCreateModal()">
            <i class="bi bi-person-plus"></i>
            Add First Account User
        </button>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($users->hasPages())
<div style="margin-top:1.5rem;">
    {{ $users->links() }}
</div>
@endif

{{-- ═══════════════════════════════════════════
     CREATE / EDIT USER MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade ha-modal" id="userModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">
                    <i class="bi bi-person-plus"></i>
                    Add Account User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="userForm" autocomplete="off">
                @csrf
                <input type="hidden" id="userId" name="id" value="">

                <div class="modal-body">
                    {{-- Basic Info --}}
                    <div class="ha-form-section">
                        <div class="ha-form-section-title">
                            <i class="bi bi-person"></i>
                            Basic Information
                        </div>

                        <div class="ha-form-row">
                            <div class="ha-form-group">
                                <label class="ha-form-label">
                                    Full Name <span class="required">*</span>
                                </label>
                                <input type="text" class="ha-form-control"
                                       id="name" name="name"
                                       placeholder="e.g., John Doe" required>
                                <div class="ha-form-error" id="error_name"></div>
                            </div>

                            <div class="ha-form-group">
                                <label class="ha-form-label">
                                    Phone <span class="required">*</span>
                                </label>
                                <input type="text" class="ha-form-control"
                                       id="phone" name="phone"
                                       placeholder="+91 98765 43210" required>
                                <div class="ha-form-error" id="error_phone"></div>
                            </div>
                        </div>

                        <div class="ha-form-row">
                            <div class="ha-form-group">
                                <label class="ha-form-label">
                                    Email <span class="required">*</span>
                                </label>
                                <input type="email" class="ha-form-control"
                                       id="email" name="email"
                                       placeholder="user@example.com" required>
                                <div class="ha-form-error" id="error_email"></div>
                            </div>

                            <div class="ha-form-group">
                                <label class="ha-form-label">
                                    Role <span class="required">*</span>
                                </label>
                                <select class="ha-form-control" id="role" name="role" required>
                                    <option value="account">Account</option>
                                    <option value="admin">Admin</option>
                                    <option value="stay">Resident</option>
                                </select>
                                <div class="ha-form-error" id="error_role"></div>
                            </div>
                        </div>

                        <div class="ha-form-row">
                            <div class="ha-form-group">
                                <label class="ha-form-label">
                                    Password <span class="required" id="passwordRequired">*</span>
                                </label>
                                <input type="password" class="ha-form-control"
                                       id="password" name="password"
                                       placeholder="Minimum 6 characters">
                                <div class="ha-form-error" id="error_password"></div>
                                <small style="font-size:0.68rem; color:#9ca3af;" id="passwordHint">
                                    Leave blank to keep current password
                                </small>
                            </div>

                            <div class="ha-form-group">
                                <label class="ha-form-label">Status</label>
                                <div style="padding-top:0.35rem;">
                                    <label class="ha-switch">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                                        <span class="ha-switch-track"></span>
                                        <span class="ha-switch-label">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hostel Allocation --}}
                    <div class="ha-form-section">
                        <div class="ha-form-section-title">
                            <i class="bi bi-building"></i>
                            Hostel Allocation
                        </div>

                        @if($hostels->count() > 0)
                            <div class="ha-select-all-bar">
                                <label>
                                    <input type="checkbox" id="selectAllHostels">
                                    <span>Select All Hostels</span>
                                </label>
                                <span class="ha-selected-count" id="selectedCount">
                                    0 selected
                                </span>
                            </div>

                            <div class="ha-hostel-grid" id="hostelGrid">
                                @foreach($hostels as $hostel)
                                    <label class="ha-hostel-checkbox" data-hostel-id="{{ $hostel->id }}">
                                        <input type="checkbox"
                                               name="hostel_ids[]"
                                               value="{{ $hostel->id }}"
                                               class="hostel-checkbox">
                                        <div class="ha-hostel-checkbox-info">
                                            <div class="ha-hostel-checkbox-name" title="{{ $hostel->hostel_name }}">
                                                {{ $hostel->hostel_name }}
                                            </div>
                                            <div class="ha-hostel-checkbox-code">
                                                {{ $hostel->hostel_code }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align:center; padding:1.5rem; background:#f8fafc; border-radius:10px; border:1px dashed #e5e7eb;">
                                <i class="bi bi-building-x" style="font-size:1.5rem; color:#9ca3af;"></i>
                                <p style="font-size:0.78rem; color:#6b7280; margin:0.5rem 0 0;">
                                    No active hostels available. Please add hostels first.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="ha-btn ha-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                        Cancel
                    </button>
                    <button type="submit" class="ha-btn ha-btn-gold" id="userSubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="userSubmitText">Save User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     QUICK ALLOCATE MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade ha-modal" id="allocateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-building-gear"></i>
                    Allocate Hostels
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="allocateForm">
                @csrf
                <input type="hidden" id="allocateUserId" name="user_id">

                <div class="modal-body">
                    <div style="margin-bottom:1rem; padding:0.75rem 1rem; background:#fffbf0; border-radius:10px; border:1px solid rgba(197, 160, 40, 0.2);">
                        <div style="font-size:0.72rem; color:#92400e; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                            Allocating to
                        </div>
                        <div style="font-size:0.92rem; font-weight:700; color:var(--sanjay-primary);" id="allocateUserName">—</div>
                        <div style="font-size:0.72rem; color:#6b7280;" id="allocateUserEmail">—</div>
                    </div>

                    @if($hostels->count() > 0)
                        <div class="ha-select-all-bar">
                            <label>
                                <input type="checkbox" id="allocateSelectAll">
                                <span>Select All Hostels</span>
                            </label>
                            <span class="ha-selected-count" id="allocateSelectedCount">
                                0 selected
                            </span>
                        </div>

                        <div class="ha-hostel-grid" id="allocateHostelGrid">
                            @foreach($hostels as $hostel)
                                <label class="ha-hostel-checkbox" data-hostel-id="{{ $hostel->id }}">
                                    <input type="checkbox"
                                           name="hostel_ids[]"
                                           value="{{ $hostel->id }}"
                                           class="allocate-hostel-checkbox">
                                    <div class="ha-hostel-checkbox-info">
                                        <div class="ha-hostel-checkbox-name" title="{{ $hostel->hostel_name }}">
                                            {{ $hostel->hostel_name }}
                                        </div>
                                        <div class="ha-hostel-checkbox-code">
                                            {{ $hostel->hostel_code }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align:center; padding:1.5rem; background:#f8fafc; border-radius:10px; border:1px dashed #e5e7eb;">
                            <i class="bi bi-building-x" style="font-size:1.5rem; color:#9ca3af;"></i>
                            <p style="font-size:0.78rem; color:#6b7280; margin:0.5rem 0 0;">
                                No active hostels available.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="ha-btn ha-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                        Cancel
                    </button>
                    <button type="submit" class="ha-btn ha-btn-gold" id="allocateSubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="allocateSubmitText">Save Allocation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     DELETE MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade ha-modal" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:2rem 1.5rem;">
                <div class="ha-delete-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 style="color:var(--sanjay-primary); font-weight:700; margin-bottom:0.5rem;">
                    Delete User?
                </h5>
                <p style="color:#6b7280; font-size:0.85rem; margin-bottom:0.25rem;">
                    You are about to delete:
                </p>
                <p style="color:var(--sanjay-primary); font-weight:600; font-size:0.95rem; margin-bottom:1rem;"
                   id="deleteUserName"></p>
                <p style="color:#ef4444; font-size:0.75rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:0.5rem;">
                <button type="button" class="ha-btn ha-btn-outline" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>
                <button type="button" class="ha-btn ha-btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    <span id="deleteBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════
// HOSTEL ALLOCATION - JS
// ═══════════════════════════════════════════

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const USERS_BASE_URL = "{{ url('admin/users') }}";
let currentDeleteId = null;

// ── Toast ──
function showToast(message, type = 'success') {
    if (typeof showFlashMessage === 'function') {
        showFlashMessage(message, type);
    } else {
        alert(message);
    }
}

// ═══════════════════════════════════════════
// CREATE / EDIT USER MODAL
// ═══════════════════════════════════════════

function openCreateModal() {
    resetUserForm();
    document.getElementById('userModalTitle').innerHTML =
        '<i class="bi bi-person-plus"></i> Add Account User';
    document.getElementById('userSubmitText').textContent = 'Save User';
    document.getElementById('userId').value = '';
    document.getElementById('passwordRequired').style.display = '';
    document.getElementById('passwordHint').style.display = 'none';
    document.getElementById('password').setAttribute('required', 'required');
    document.getElementById('role').value = 'account';

    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

async function openEditModal(id) {
    resetUserForm();

    try {
        const response = await fetch(`${USERS_BASE_URL}/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const data = await response.json();

        if (data.success) {
            const user = data.user;

            document.getElementById('userModalTitle').innerHTML =
                '<i class="bi bi-pencil-square"></i> Edit User';
            document.getElementById('userSubmitText').textContent = 'Update User';
            document.getElementById('userId').value = user.id;

            document.getElementById('name').value = user.name || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('role').value = user.role || 'account';
            document.getElementById('is_active').checked = user.is_active == 1;

            // Password optional on edit
            document.getElementById('password').value = '';
            document.getElementById('password').removeAttribute('required');
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('passwordHint').style.display = '';

            // Check allocated hostels
            const hostelIds = Array.isArray(user.hostel_ids) ? user.hostel_ids.map(Number) : [];
            document.querySelectorAll('#hostelGrid .hostel-checkbox').forEach(cb => {
                cb.checked = hostelIds.includes(Number(cb.value));
                cb.closest('.ha-hostel-checkbox').classList.toggle('checked', cb.checked);
            });
            updateSelectedCount();
            updateSelectAllState();

            const modal = new bootstrap.Modal(document.getElementById('userModal'));
            modal.show();
        } else {
            showToast('Failed to load user', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to load user', 'error');
    }
}

function resetUserForm() {
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('is_active').checked = true;

    document.querySelectorAll('#userForm .ha-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('#userForm .ha-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });

    document.querySelectorAll('#hostelGrid .hostel-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('.ha-hostel-checkbox').classList.remove('checked');
    });
    const selectAll = document.getElementById('selectAllHostels');
    if (selectAll) selectAll.checked = false;
    updateSelectedCount();
}

// ── Hostel checkbox change ──
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('hostel-checkbox')) {
        e.target.closest('.ha-hostel-checkbox').classList.toggle('checked', e.target.checked);
        updateSelectedCount();
        updateSelectAllState();
    }
    if (e.target.id === 'selectAllHostels') {
        const checkboxes = document.querySelectorAll('#hostelGrid .hostel-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = e.target.checked;
            cb.closest('.ha-hostel-checkbox').classList.toggle('checked', e.target.checked);
        });
        updateSelectedCount();
    }
});

function updateSelectedCount() {
    const checked = document.querySelectorAll('#hostelGrid .hostel-checkbox:checked').length;
    const total = document.querySelectorAll('#hostelGrid .hostel-checkbox').length;
    const el = document.getElementById('selectedCount');
    if (el) el.textContent = `${checked} selected`;
    const selectAll = document.getElementById('selectAllHostels');
    if (selectAll) {
        selectAll.checked = (checked > 0 && checked === total);
        selectAll.indeterminate = (checked > 0 && checked < total);
    }
}

function updateSelectAllState() {
    const checked = document.querySelectorAll('#hostelGrid .hostel-checkbox:checked').length;
    const total = document.querySelectorAll('#hostelGrid .hostel-checkbox').length;
    const selectAll = document.getElementById('selectAllHostels');
    if (selectAll) {
        selectAll.checked = (checked > 0 && checked === total);
        selectAll.indeterminate = (checked > 0 && checked < total);
    }
}

// ── Submit User Form ──
document.getElementById('userForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('userSubmitBtn');
    const submitText = document.getElementById('userSubmitText');
    const originalText = submitText.textContent;
    const userId = document.getElementById('userId').value;
    const isEdit = userId !== '';

    // Clear errors
    document.querySelectorAll('#userForm .ha-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('#userForm .ha-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="ha-spinner"></span> Saving...';

    const formData = new FormData(this);

    // Ensure hostel_ids is always sent (even empty)
    const checkedHostels = document.querySelectorAll('#hostelGrid .hostel-checkbox:checked');
    formData.delete('hostel_ids[]');
    checkedHostels.forEach(cb => {
        formData.append('hostel_ids[]', cb.value);
    });

    // Boolean is_active
    formData.set('is_active', document.getElementById('is_active').checked ? '1' : '0');

    const url = isEdit ? `${USERS_BASE_URL}/${userId}` : USERS_BASE_URL;

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
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
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

// ═══════════════════════════════════════════
// QUICK ALLOCATE MODAL
// ═══════════════════════════════════════════

async function openAllocateModal(id) {
    try {
        const response = await fetch(`${USERS_BASE_URL}/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const data = await response.json();

        if (data.success) {
            const user = data.user;

            document.getElementById('allocateUserId').value = user.id;
            document.getElementById('allocateUserName').textContent = user.name;
            document.getElementById('allocateUserEmail').textContent = user.email;

            const hostelIds = Array.isArray(user.hostel_ids) ? user.hostel_ids.map(Number) : [];

            document.querySelectorAll('#allocateHostelGrid .allocate-hostel-checkbox').forEach(cb => {
                cb.checked = hostelIds.includes(Number(cb.value));
                cb.closest('.ha-hostel-checkbox').classList.toggle('checked', cb.checked);
            });

            updateAllocateCount();

            const modal = new bootstrap.Modal(document.getElementById('allocateModal'));
            modal.show();
        } else {
            showToast('Failed to load user', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to load user', 'error');
    }
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('allocate-hostel-checkbox')) {
        e.target.closest('.ha-hostel-checkbox').classList.toggle('checked', e.target.checked);
        updateAllocateCount();
    }
    if (e.target.id === 'allocateSelectAll') {
        const checkboxes = document.querySelectorAll('#allocateHostelGrid .allocate-hostel-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = e.target.checked;
            cb.closest('.ha-hostel-checkbox').classList.toggle('checked', e.target.checked);
        });
        updateAllocateCount();
    }
});

function updateAllocateCount() {
    const checked = document.querySelectorAll('#allocateHostelGrid .allocate-hostel-checkbox:checked').length;
    const total = document.querySelectorAll('#allocateHostelGrid .allocate-hostel-checkbox').length;
    const el = document.getElementById('allocateSelectedCount');
    if (el) el.textContent = `${checked} selected`;
    const selectAll = document.getElementById('allocateSelectAll');
    if (selectAll) {
        selectAll.checked = (checked > 0 && checked === total);
        selectAll.indeterminate = (checked > 0 && checked < total);
    }
}

// ── Submit Allocation ──
document.getElementById('allocateForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('allocateSubmitBtn');
    const submitText = document.getElementById('allocateSubmitText');
    const originalText = submitText.textContent;
    const userId = document.getElementById('allocateUserId').value;

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="ha-spinner"></span> Saving...';

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('_token', CSRF_TOKEN);

    // Get user details to preserve other fields
    const userResponse = await fetch(`${USERS_BASE_URL}/${userId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    });
    const userData = await userResponse.json();
    const user = userData.user;

    // Preserve existing user data
    formData.append('name', user.name);
    formData.append('email', user.email);
    formData.append('phone', user.phone);
    formData.append('role', user.role);
    formData.append('is_active', user.is_active ? '1' : '0');

    // Add selected hostels
    const checkedHostels = document.querySelectorAll('#allocateHostelGrid .allocate-hostel-checkbox:checked');
    checkedHostels.forEach(cb => {
        formData.append('hostel_ids[]', cb.value);
    });

    try {
        const response = await fetch(`${USERS_BASE_URL}/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast('Hostels allocated successfully.', 'success');
            bootstrap.Modal.getInstance(document.getElementById('allocateModal')).hide();
            setTimeout(() => window.location.reload(), 700);
        } else {
            showToast(data.message || 'Failed to allocate', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    } finally {
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
});

// ═══════════════════════════════════════════
// TOGGLE STATUS
// ═══════════════════════════════════════════

async function toggleStatus(id) {
    try {
        const response = await fetch(`${USERS_BASE_URL}/${id}/toggle-status`, {
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

            const card = document.querySelector(`.ha-card[data-id="${id}"]`);
            if (card) {
                const badge = card.querySelector('.ha-badge');
                const toggleBtn = card.querySelector('.ha-icon-btn.toggle');

                if (data.status === 'active') {
                    badge.className = 'ha-badge active';
                    badge.innerHTML = '<span class="ha-badge-dot"></span> Active';
                    toggleBtn.title = 'Deactivate';
                    toggleBtn.innerHTML = '<i class="bi bi-pause-circle"></i>';
                } else {
                    badge.className = 'ha-badge inactive';
                    badge.innerHTML = '<span class="ha-badge-dot"></span> Inactive';
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

// ═══════════════════════════════════════════
// DELETE
// ═══════════════════════════════════════════

function openDeleteModal(id, name) {
    currentDeleteId = id;
    document.getElementById('deleteUserName').textContent = `"${name}"`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!currentDeleteId) return;

    const btn = this;
    const btnText = document.getElementById('deleteBtnText');
    const originalText = btnText.textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="ha-spinner"></span> Deleting...';

    try {
        const response = await fetch(`${USERS_BASE_URL}/${currentDeleteId}`, {
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

            const card = document.querySelector(`.ha-card[data-id="${currentDeleteId}"]`);
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

// ═══════════════════════════════════════════
// SEARCH & FILTER
// ═══════════════════════════════════════════

const searchInput = document.getElementById('haSearchInput');
const statusFilter = document.getElementById('haStatusFilter');
const allocationFilter = document.getElementById('haAllocationFilter');

function applyFilters() {
    const search = searchInput.value.toLowerCase().trim();
    const status = statusFilter.value;
    const allocation = allocationFilter.value;

    let visibleCount = 0;

    document.querySelectorAll('.ha-card').forEach(card => {
        const name = card.getAttribute('data-name');
        const email = card.getAttribute('data-email');
        const phone = card.getAttribute('data-phone');
        const cardStatus = card.getAttribute('data-status');
        const cardAllocation = card.getAttribute('data-allocated');

        const matchesSearch = !search
            || name.includes(search)
            || email.includes(search)
            || phone.includes(search);
        const matchesStatus = !status || cardStatus === status;
        const matchesAllocation = !allocation || cardAllocation === allocation;

        if (matchesSearch && matchesStatus && matchesAllocation) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    updateCountLabel(visibleCount);
}

function updateCountLabel(count) {
    const total = document.querySelectorAll('.ha-card').length;
    const label = document.getElementById('haCountLabel');
    if (count !== undefined && count !== total) {
        label.textContent = `${count} of ${total} users`;
    } else {
        label.textContent = `${total} users`;
    }
}

searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);
allocationFilter.addEventListener('change', applyFilters);

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    updateSelectedCount();
    updateAllocateCount();
    updateCountLabel();
});
</script>
@endpush
