@extends('layouts.office')

@section('title', 'Residents — Sanjay PG Hostel')
@section('page_title', 'Residents')

@push('styles')
<style>
    .rs-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .rs-page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .rs-page-title i { color: var(--sanjay-gold); }
    .rs-page-subtitle { font-size: 0.8rem; color: #6b7280; margin: 0.25rem 0 0 0; }

    .rs-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .rs-search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }
    .rs-search-box input {
        width: 100%;
        padding: 0.55rem 1rem 0.55rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        color: #374151;
    }
    .rs-search-box input:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .rs-search-box i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
    .rs-filter-select {
        padding: 0.55rem 2rem 0.55rem 0.85rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        color: #374151;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
    }
    .rs-filter-select:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .rs-filter-select.active-filter {
        border-color: var(--sanjay-gold);
        background-color: #fffbeb;
    }

    .rs-btn-primary {
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
        white-space: nowrap;
    }
    .rs-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(10, 30, 63, 0.25);
        color: white;
    }

    .rs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1rem;
    }

    .rs-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .rs-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.08);
        border-color: rgba(197, 160, 40, 0.3);
    }
    .rs-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--sanjay-gold), var(--sanjay-primary));
        opacity: 0;
        transition: opacity 0.3s;
    }
    .rs-card:hover::before { opacity: 1; }

    .rs-card.vacated {
        opacity: 0.75;
        border-color: #fecaca;
    }
    .rs-card.vacated::before {
        background: linear-gradient(90deg, #ef4444, #b91c1c);
        opacity: 1;
    }

    .rs-card-head {
        padding: 1rem 1.15rem 0.75rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .rs-avatar {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
        background: linear-gradient(135deg, rgba(197, 160, 40, 0.15), rgba(10, 30, 63, 0.08));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        font-family: 'DM Mono', monospace;
        border: 2px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .rs-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rs-card-title-wrap { flex: 1; min-width: 0; }
    .rs-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0 0 0.15rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rs-card-code {
        font-size: 0.68rem;
        color: #9ca3af;
        font-family: 'DM Mono', monospace;
    }
    .rs-card-menu { position: relative; }
    .rs-card-menu-btn {
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
    .rs-card-menu-btn:hover {
        background: #f9fafb;
        border-color: var(--sanjay-gold);
        color: var(--sanjay-gold);
    }

    .rs-card-body {
        padding: 0 1.15rem 1rem;
        flex: 1;
    }

    .rs-badges {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
        margin-bottom: 0.75rem;
    }
    .rs-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .rs-badge.active        { background: #dcfce7; color: #166534; }
    .rs-badge.vacated       { background: #fee2e2; color: #991b1b; }
    .rs-badge.with_food     { background: #fef3c7; color: #92400e; }
    .rs-badge.without_food  { background: #f3f4f6; color: #4b5563; }
    .rs-badge.biometric     { background: #dbeafe; color: #1e40af; }
    .rs-badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    .rs-card-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #6b7280;
        padding: 0.25rem 0;
    }
    .rs-card-detail i {
        color: var(--sanjay-gold);
        font-size: 0.8rem;
        width: 14px;
        flex-shrink: 0;
    }
    .rs-card-detail span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rs-rent-box {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.04));
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 9px;
        padding: 0.6rem 0.8rem;
        margin-top: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .rs-rent-label {
        font-size: 0.62rem;
        color: #059669;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    .rs-rent-value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #059669;
        font-family: 'DM Mono', monospace;
    }
    .rs-rent-value small {
        font-size: 0.65rem;
        color: #6b7280;
        font-weight: 400;
    }

    .rs-card-footer {
        padding: 0.7rem 1.15rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #fafbfc;
    }
    .rs-card-actions { display: flex; gap: 0.35rem; }
    .rs-icon-btn {
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
    .rs-icon-btn:hover { transform: translateY(-1px); }
    .rs-icon-btn.edit:hover       { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    .rs-icon-btn.delete:hover     { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
    .rs-icon-btn.vacate:hover     { background: #fef3c7; border-color: #f59e0b; color: #d97706; }
    .rs-icon-btn.reactivate:hover { background: #f0fdf4; border-color: #10b981; color: #059669; }

    .rs-empty {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        border: 2px dashed #e5e7eb;
        grid-column: 1 / -1;
    }
    .rs-empty-icon {
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
    .rs-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .rs-empty p  { color: #6b7280; font-size: 0.85rem; margin-bottom: 1.5rem; }

    .rs-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.15);
    }
    .rs-modal .modal-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        padding: 1.1rem 1.5rem;
    }
    .rs-modal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .rs-modal .modal-title i { color: var(--sanjay-gold); }
    .rs-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .rs-modal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    .rs-modal .modal-body::-webkit-scrollbar { width: 4px; }
    .rs-modal .modal-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .rs-modal .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f3f4f6;
        background: #fafbfc;
    }

    .rs-form-section { margin-bottom: 1.5rem; }
    .rs-form-section:last-child { margin-bottom: 0; }
    .rs-form-section-title {
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
    .rs-form-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(197, 160, 40, 0.3), transparent);
    }
    .rs-form-group { margin-bottom: 0.9rem; }
    .rs-form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .rs-form-label .required { color: #ef4444; margin-left: 2px; }
    .rs-form-control {
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
    .rs-form-control:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .rs-form-control::placeholder { color: #9ca3af; }
    .rs-form-control.is-invalid { border-color: #ef4444; }
    .rs-form-error { font-size: 0.7rem; color: #ef4444; margin-top: 0.25rem; display: none; }
    .rs-form-error.show { display: block; }
    select.rs-form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.85rem center;
        padding-right: 2.2rem;
    }
    .rs-form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.9rem;
    }
    @media (max-width: 576px) {
        .rs-form-row { grid-template-columns: 1fr; }
    }

    .rs-file-input {
        padding: 0.5rem;
        font-size: 0.75rem;
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 9px;
        cursor: pointer;
        width: 100%;
    }
    .rs-file-input:hover {
        border-color: var(--sanjay-gold);
        background: #fffbeb;
    }

    .rs-image-preview {
        width: 70px;
        height: 70px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid #e5e7eb;
        margin-top: 0.5rem;
        display: none;
    }
    .rs-image-preview.show { display: block; }

    .rs-switch {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        user-select: none;
        padding: 0.5rem 0;
    }
    .rs-switch input { display: none; }
    .rs-switch-slider {
        width: 42px;
        height: 24px;
        background: #e5e7eb;
        border-radius: 20px;
        position: relative;
        transition: background 0.25s;
        flex-shrink: 0;
    }
    .rs-switch-slider::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: white;
        transition: transform 0.25s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .rs-switch input:checked + .rs-switch-slider { background: var(--sanjay-gold); }
    .rs-switch input:checked + .rs-switch-slider::after { transform: translateX(18px); }
    .rs-switch-label { font-size: 0.82rem; color: #374151; font-weight: 500; }

    .rs-btn {
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
    .rs-btn-gold {
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
    }
    .rs-btn-gold:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(197, 160, 40, 0.35); }
    .rs-btn-gold:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
    .rs-btn-outline { background: white; color: #6b7280; border: 1px solid #e5e7eb; }
    .rs-btn-outline:hover { background: #f9fafb; color: #374151; }
    .rs-btn-danger { background: #ef4444; color: white; }
    .rs-btn-danger:hover { background: #dc2626; transform: translateY(-1px); }
    .rs-btn-warning { background: #f59e0b; color: white; }
    .rs-btn-warning:hover { background: #d97706; }
    .rs-btn-success { background: #10b981; color: white; }
    .rs-btn-success:hover { background: #059669; }

    .rs-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: rs-spin 0.6s linear infinite;
        display: inline-block;
    }
    @keyframes rs-spin { to { transform: rotate(360deg); } }

    .rs-delete-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1rem;
    }
    .rs-delete-icon.danger { background: #fef2f2; color: #ef4444; }
    .rs-delete-icon.warning { background: #fef3c7; color: #f59e0b; }

    .rs-context-item {
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
    .rs-context-item:hover { background: #f9fafb; color: var(--sanjay-primary); }
    .rs-context-item.danger { color: #ef4444; }
    .rs-context-item.danger:hover { background: #fef2f2; }
    .rs-context-item.warning { color: #f59e0b; }
    .rs-context-item.warning:hover { background: #fffbeb; }
    .rs-context-item.success { color: #10b981; }
    .rs-context-item.success:hover { background: #f0fdf4; }
    .rs-context-item i { font-size: 0.85rem; width: 16px; }

    @media (max-width: 768px) {
        .rs-page-header { flex-direction: column; align-items: flex-start; }
        .rs-toolbar { flex-direction: column; align-items: stretch; }
        .rs-search-box { max-width: none; }
        .rs-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="rs-page-header">
    <div>
        <h2 class="rs-page-title">
            <i class="bi bi-people"></i>
            Resident Management
        </h2>
        <p class="rs-page-subtitle">Manage residents — vacated are hidden by default (use Status filter to view)</p>
    </div>
    <button type="button" class="rs-btn-primary" onclick="openCreateModal()">
        <i class="bi bi-person-plus"></i>
        Add Resident
    </button>
</div>

<div class="rs-toolbar">
    <div class="rs-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="rsSearchInput" placeholder="Search name, code, phone...">
    </div>

    <select class="rs-filter-select" id="rsHostelFilter">
        <option value="">All Hostels</option>
        @foreach($hostels as $hostel)
            <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
        @endforeach
    </select>

    <select class="rs-filter-select {{ $statusFilter === 'vacated' ? 'active-filter' : '' }}"
            id="rsStatusFilter">
        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
        <option value="vacated" {{ $statusFilter === 'vacated' ? 'selected' : '' }}>Vacated Only</option>
        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Residents</option>
    </select>

    <select class="rs-filter-select" id="rsFoodFilter">
        <option value="">All Food Status</option>
        <option value="WITH_FOOD">With Food</option>
        <option value="WITHOUT_FOOD">Without Food</option>
    </select>

    <span style="margin-left:auto; font-size:0.75rem; color:#9ca3af;" id="rsCountLabel">
        {{ $residents->count() }} residents
    </span>
</div>

<div class="rs-grid" id="rsGrid">
    @forelse($residents as $resident)
    <div class="rs-card {{ strtolower($resident->status) }}"
         data-id="{{ $resident->id }}"
         data-name="{{ strtolower($resident->name) }}"
         data-code="{{ strtolower($resident->resident_code) }}"
         data-phone="{{ $resident->phone }}"
         data-hostel-id="{{ $resident->hostel_id }}"
         data-status="{{ $resident->status }}"
         data-food="{{ $resident->food_status }}">

        <div class="rs-card-head">
            <div class="rs-avatar">
                @if($resident->profile_image && file_exists(public_path('assets/residents/' . $resident->profile_image)))
                    <img src="{{ asset('assets/residents/' . $resident->profile_image) }}"
                         alt="{{ $resident->name }}">
                @else
                    {{ strtoupper(substr($resident->name, 0, 2)) }}
                @endif
            </div>

            <div class="rs-card-title-wrap">
                <h3 class="rs-card-title" title="{{ $resident->name }}">
                    {{ $resident->name }}
                </h3>
                <div class="rs-card-code">{{ $resident->resident_code }}</div>
            </div>

            <div class="rs-card-menu">
                <button type="button" class="rs-card-menu-btn"
                        onclick="toggleCardMenu(event, {{ $resident->id }})">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
            </div>
        </div>

        <div class="rs-card-body">
            <div class="rs-badges">
                <span class="rs-badge {{ strtolower($resident->status) }}">
                    <span class="rs-badge-dot"></span>
                    {{ ucfirst(strtolower($resident->status)) }}
                </span>
                <span class="rs-badge {{ strtolower($resident->food_status) }}">
                    <i class="bi bi-{{ $resident->food_status === 'WITH_FOOD' ? 'egg-fried' : 'cup-hot' }}"></i>
                    {{ $resident->food_status === 'WITH_FOOD' ? 'With Food' : 'Without Food' }}
                </span>
                @if($resident->biometric_access)
                    <span class="rs-badge biometric">
                        <i class="bi bi-fingerprint"></i> Bio
                    </span>
                @endif
            </div>

            <div class="rs-card-detail">
                <i class="bi bi-telephone"></i>
                <span>{{ $resident->phone }}</span>
            </div>

            <div class="rs-card-detail">
                <i class="bi bi-building"></i>
                <span>{{ $resident->hostel->hostel_name ?? 'N/A' }}</span>
            </div>

            <div class="rs-card-detail">
                <i class="bi bi-door-open"></i>
                <span>Room {{ $resident->room->room_no ?? 'N/A' }} • Bed {{ $resident->bed->bed_no ?? 'N/A' }}</span>
            </div>

            <div class="rs-card-detail">
                <i class="bi bi-calendar-event"></i>
                <span>Joined {{ $resident->joining_date ? $resident->joining_date->format('d M Y') : 'N/A' }}</span>
            </div>

            @if($resident->status === 'VACATED' && $resident->vacate_date)
            <div class="rs-card-detail">
                <i class="bi bi-box-arrow-right" style="color:#ef4444;"></i>
                <span style="color:#ef4444;">Vacated {{ $resident->vacate_date->format('d M Y') }}</span>
            </div>
            @endif

            <div class="rs-rent-box">
                <div>
                    <div class="rs-rent-label">Monthly Rent</div>
                    <div class="rs-rent-value">₹{{ number_format($resident->rent_amount, 0) }}<small> /mo</small></div>
                </div>
                <div style="text-align:right;">
                    <div class="rs-rent-label">Deposit</div>
                    <div class="rs-rent-value" style="color:#3b82f6;">₹{{ number_format($resident->deposit_amount, 0) }}</div>
                </div>
            </div>
        </div>

        <div class="rs-card-footer">
            <span style="font-size:0.68rem; color:#9ca3af;">
                <i class="bi bi-clock"></i>
                {{ $resident->created_at->format('d M Y') }}
            </span>
            <div class="rs-card-actions">
                @if($resident->status === 'ACTIVE')
                    <button type="button" class="rs-icon-btn vacate"
                            onclick="openVacateModal({{ $resident->id }}, '{{ addslashes($resident->name) }}')"
                            title="Vacate">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                @else
                    <button type="button" class="rs-icon-btn reactivate"
                            onclick="reactivateResident({{ $resident->id }}, '{{ addslashes($resident->name) }}')"
                            title="Reactivate">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                @endif
                <button type="button" class="rs-icon-btn edit"
                        onclick="openEditModal({{ $resident->id }})"
                        title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="rs-icon-btn delete"
                        onclick="openDeleteModal({{ $resident->id }}, '{{ addslashes($resident->name) }}')"
                        title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="rs-empty">
        <div class="rs-empty-icon">
            <i class="bi bi-people"></i>
        </div>
        <h5>No Residents</h5>
        <p>
            @if($statusFilter === 'active')
                No active residents. Try changing the Status filter to "All Residents".
            @elseif($statusFilter === 'vacated')
                No vacated residents found.
            @else
                Get started by adding your first resident.
            @endif
        </p>
        <button type="button" class="rs-btn-primary" onclick="openCreateModal()">
            <i class="bi bi-person-plus"></i>
            Add Resident
        </button>
    </div>
    @endforelse
</div>

{{-- CREATE / EDIT MODAL --}}
<div class="modal fade rs-modal" id="residentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="residentModalTitle">
                    <i class="bi bi-person-plus"></i>
                    Add New Resident
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="residentForm" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="residentId" name="id" value="">

                <div class="modal-body">
                    <div class="rs-form-section">
                        <div class="rs-form-section-title">
                            <i class="bi bi-person"></i>
                            Basic Information
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Resident Code <span class="required">*</span></label>
                                <input type="text" class="rs-form-control" id="resident_code" name="resident_code" placeholder="e.g., RES-001" required>
                                <div class="rs-form-error" id="error_resident_code"></div>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Full Name <span class="required">*</span></label>
                                <input type="text" class="rs-form-control" id="name" name="name" placeholder="Full name" required>
                                <div class="rs-form-error" id="error_name"></div>
                            </div>
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Phone <span class="required">*</span></label>
                                <input type="text" class="rs-form-control" id="phone" name="phone" placeholder="+91 98765 43210" required>
                                <div class="rs-form-error" id="error_phone"></div>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Parent's Phone</label>
                                <input type="text" class="rs-form-control" id="parentsphone" name="parentsphone" placeholder="Emergency contact">
                                <div class="rs-form-error" id="error_parentsphone"></div>
                            </div>
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Email</label>
                                <input type="email" class="rs-form-control" id="email" name="email" placeholder="resident@example.com">
                                <div class="rs-form-error" id="error_email"></div>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Date of Birth <span class="required">*</span></label>
                                <input type="date" class="rs-form-control" id="dob" name="dob" required>
                                <div class="rs-form-error" id="error_dob"></div>
                            </div>
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Aadhaar Number</label>
                                <input type="text" class="rs-form-control" id="aadhaar_no" name="aadhaar_no" placeholder="12-digit Aadhaar">
                                <div class="rs-form-error" id="error_aadhaar_no"></div>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Employee Code</label>
                                <input type="text" class="rs-form-control" id="employee_code" name="employee_code" placeholder="e.g., EMP-001">
                                <div class="rs-form-error" id="error_employee_code"></div>
                            </div>
                        </div>

                        <div class="rs-form-group">
                            <label class="rs-form-label">Address</label>
                            <textarea class="rs-form-control" id="address" name="address" rows="2" placeholder="Full address"></textarea>
                            <div class="rs-form-error" id="error_address"></div>
                        </div>
                    </div>

                    <div class="rs-form-section">
                        <div class="rs-form-section-title">
                            <i class="bi bi-house-door"></i>
                            Room & Bed Assignment
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Hostel <span class="required">*</span></label>
                                <select class="rs-form-control" id="hostel_id" name="hostel_id" required>
                                    <option value="">Select Hostel</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }} ({{ $hostel->hostel_code }})</option>
                                    @endforeach
                                </select>
                                <div class="rs-form-error" id="error_hostel_id"></div>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Room <span class="required">*</span></label>
                                <select class="rs-form-control" id="room_id" name="room_id" required disabled>
                                    <option value="">Select Hostel First</option>
                                </select>
                                <div class="rs-form-error" id="error_room_id"></div>
                            </div>
                        </div>

                        <div class="rs-form-group">
                            <label class="rs-form-label">Bed <span class="required">*</span></label>
                            <select class="rs-form-control" id="bed_id" name="bed_id" required disabled>
                                <option value="">Select Room First</option>
                            </select>
                            <div class="rs-form-error" id="error_bed_id"></div>
                            <small style="color:#9ca3af; font-size:0.68rem; margin-top:4px; display:block;">
                                <i class="bi bi-info-circle"></i> Only vacant beds are shown
                            </small>
                        </div>
                    </div>

                    <div class="rs-form-section">
                        <div class="rs-form-section-title">
                            <i class="bi bi-calendar-check"></i>
                            Stay & Payment
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Joining Date <span class="required">*</span></label>
                                <input type="date" class="rs-form-control" id="joining_date" name="joining_date" value="{{ now()->format('Y-m-d') }}" required>
                                <div class="rs-form-error" id="error_joining_date"></div>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Vacate Date</label>
                                <input type="date" class="rs-form-control" id="vacate_date" name="vacate_date">
                                <div class="rs-form-error" id="error_vacate_date"></div>
                            </div>
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Monthly Rent (₹) <span class="required">*</span></label>
                                <input type="number" step="0.01" class="rs-form-control" id="rent_amount" name="rent_amount" placeholder="e.g., 6000" min="0" required>
                                <div class="rs-form-error" id="error_rent_amount"></div>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Deposit Amount (₹)</label>
                                <input type="number" step="0.01" class="rs-form-control" id="deposit_amount" name="deposit_amount" placeholder="e.g., 3000" min="0" value="0">
                                <div class="rs-form-error" id="error_deposit_amount"></div>
                            </div>
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Food Status <span class="required">*</span></label>
                                <select class="rs-form-control" id="food_status" name="food_status" required>
                                    <option value="WITH_FOOD">With Food</option>
                                    <option value="WITHOUT_FOOD">Without Food</option>
                                </select>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Status</label>
                                <select class="rs-form-control" id="status" name="status">
                                    <option value="ACTIVE">Active</option>
                                    <option value="VACATED">Vacated</option>
                                </select>
                            </div>
                        </div>

                        <label class="rs-switch">
                            <input type="checkbox" id="biometric_access" name="biometric_access" checked>
                            <span class="rs-switch-slider"></span>
                            <span class="rs-switch-label">Biometric Access Enabled</span>
                        </label>
                    </div>

                    <div class="rs-form-section">
                        <div class="rs-form-section-title">
                            <i class="bi bi-file-earmark-image"></i>
                            Documents & Photos
                        </div>

                        <div class="rs-form-group">
                            <label class="rs-form-label">Profile Photo</label>
                            <input type="file" class="rs-file-input" id="profile_image" name="profile_image" accept="image/*">
                            <img id="profilePreview" class="rs-image-preview" alt="Preview">
                            <small style="color:#9ca3af; font-size:0.68rem; margin-top:4px; display:block;">
                                JPG, PNG, WEBP • Max 2MB
                            </small>
                        </div>

                        <div class="rs-form-row">
                            <div class="rs-form-group">
                                <label class="rs-form-label">Aadhaar Document</label>
                                <input type="file" class="rs-file-input" id="aadhar_document" name="aadhar_document" accept="image/*,application/pdf">
                                <small style="color:#9ca3af; font-size:0.68rem; margin-top:4px; display:block;">JPG, PNG, PDF • Max 5MB</small>
                            </div>
                            <div class="rs-form-group">
                                <label class="rs-form-label">Application Document</label>
                                <input type="file" class="rs-file-input" id="application_document" name="application_document" accept="image/*,application/pdf">
                                <small style="color:#9ca3af; font-size:0.68rem; margin-top:4px; display:block;">JPG, PNG, PDF • Max 5MB</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="rs-btn rs-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button type="submit" class="rs-btn rs-btn-gold" id="residentSubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="residentSubmitText">Save Resident</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- VACATE MODAL --}}
<div class="modal fade rs-modal" id="vacateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:2rem 1.5rem;">
                <div class="rs-delete-icon warning">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <h5 style="color:var(--sanjay-primary); font-weight:700; margin-bottom:0.5rem;">Vacate Resident?</h5>
                <p style="color:#6b7280; font-size:0.85rem; margin-bottom:1rem;">
                    <strong id="vacateResidentName"></strong> will be marked as VACATED and their bed will be freed.
                </p>
                <p style="color:#f59e0b; font-size:0.75rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    The resident will be hidden from the default view.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:0.5rem;">
                <button type="button" class="rs-btn rs-btn-outline" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Cancel
                </button>
                <button type="button" class="rs-btn rs-btn-warning" id="confirmVacateBtn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span id="vacateBtnText">Vacate</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade rs-modal" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:2rem 1.5rem;">
                <div class="rs-delete-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 style="color:var(--sanjay-primary); font-weight:700; margin-bottom:0.5rem;">Delete Resident?</h5>
                <p style="color:#6b7280; font-size:0.85rem; margin-bottom:0.25rem;">You are about to delete:</p>
                <p style="color:var(--sanjay-primary); font-weight:600; font-size:0.95rem; margin-bottom:1rem;" id="deleteResidentName"></p>
                <p style="color:#ef4444; font-size:0.75rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    Cannot delete if payment history exists. Use "Vacate" instead.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:0.5rem;">
                <button type="button" class="rs-btn rs-btn-outline" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Cancel
                </button>
                <button type="button" class="rs-btn rs-btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    <span id="deleteBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CONTEXT MENU --}}
<div id="cardContextMenu" style="display:none; position:fixed; z-index:9999; background:white; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); border:1px solid #e5e7eb; min-width:180px; overflow:hidden;">
    <button type="button" class="rs-context-item" onclick="contextEdit()">
        <i class="bi bi-pencil"></i> Edit Resident
    </button>
    <button type="button" class="rs-context-item warning" onclick="contextVacate()" id="ctxVacateBtn">
        <i class="bi bi-box-arrow-right"></i> Vacate
    </button>
    <button type="button" class="rs-context-item success" onclick="contextReactivate()" id="ctxReactivateBtn" style="display:none;">
        <i class="bi bi-arrow-clockwise"></i> Reactivate
    </button>
    <button type="button" class="rs-context-item danger" onclick="contextDelete()">
        <i class="bi bi-trash"></i> Delete Resident
    </button>
</div>

@endsection

@push('scripts')
<script>
const allRooms = {!! json_encode($roomsJson) !!};

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL = "{{ url('admin/residents') }}";
let currentDeleteId = null;
let currentVacateId = null;
let currentContextId = null;

function showToast(message, type = 'success') {
    if (typeof showFlashMessage === 'function') {
        showFlashMessage(message, type);
    } else {
        alert(message);
    }
}

function loadRoomsForHostel(hostelId, selectedRoomId = null) {
    const $roomSelect = document.getElementById('room_id');
    $roomSelect.innerHTML = '<option value="">Select Room</option>';
    $roomSelect.disabled = false;

    if (!hostelId) {
        $roomSelect.innerHTML = '<option value="">Select Hostel First</option>';
        $roomSelect.disabled = true;
        document.getElementById('bed_id').innerHTML = '<option value="">Select Room First</option>';
        document.getElementById('bed_id').disabled = true;
        return;
    }

    const rooms = allRooms.filter(r => r.hostel_id == hostelId);

    if (rooms.length === 0) {
        $roomSelect.innerHTML = '<option value="">No rooms in this hostel</option>';
        return;
    }

    rooms.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.id;
        opt.textContent = 'Room ' + r.room_no;
        if (selectedRoomId && r.id == selectedRoomId) opt.selected = true;
        $roomSelect.appendChild(opt);
    });

    if (selectedRoomId) {
        loadBedsForRoom(selectedRoomId);
    }
}

function loadBedsForRoom(roomId) {
    const $bedSelect = document.getElementById('bed_id');
    $bedSelect.innerHTML = '<option value="">Select Bed</option>';

    if (!roomId) {
        $bedSelect.innerHTML = '<option value="">Select Room First</option>';
        $bedSelect.disabled = true;
        return;
    }

    const room = allRooms.find(r => r.id == roomId);
    if (!room || room.beds.length === 0) {
        $bedSelect.innerHTML = '<option value="">No vacant beds</option>';
        return;
    }

    room.beds.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = 'Bed ' + b.bed_no + ' (' + b.bed_type + ')';
        $bedSelect.appendChild(opt);
    });
    $bedSelect.disabled = false;
}

function openCreateModal() {
    resetForm();
    document.getElementById('residentModalTitle').innerHTML = '<i class="bi bi-person-plus"></i> Add New Resident';
    document.getElementById('residentSubmitText').textContent = 'Save Resident';
    document.getElementById('residentId').value = '';
    document.getElementById('biometric_access').checked = true;
    document.getElementById('deposit_amount').value = 0;
    document.getElementById('status').value = 'ACTIVE';
    document.getElementById('joining_date').value = '{{ now()->format("Y-m-d") }}';

    new bootstrap.Modal(document.getElementById('residentModal')).show();
}

async function openEditModal(id) {
    resetForm();

    try {
        const response = await fetch(BASE_URL + '/' + id, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
        });
        const data = await response.json();

        if (data.success) {
            const r = data.resident;

            document.getElementById('residentModalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Edit Resident';
            document.getElementById('residentSubmitText').textContent = 'Update Resident';
            document.getElementById('residentId').value = r.id;

            var fields = ['resident_code', 'name', 'phone', 'parentsphone', 'email',
                          'aadhaar_no', 'employee_code', 'address', 'food_status', 'status',
                          'deposit_amount', 'rent_amount'];

            fields.forEach(function(field) {
                var el = document.getElementById(field);
                if (el) el.value = r[field] || '';
            });

            document.getElementById('dob').value = r.dob ? r.dob.substring(0, 10) : '';
            document.getElementById('joining_date').value = r.joining_date ? r.joining_date.substring(0, 10) : '';
            document.getElementById('vacate_date').value = r.vacate_date ? r.vacate_date.substring(0, 10) : '';

            document.getElementById('biometric_access').checked = !!r.biometric_access;

            document.getElementById('hostel_id').value = r.hostel_id;
            loadRoomsForHostel(r.hostel_id, r.room_id);

            // Manually add current bed (it's occupied, so not in list)
            setTimeout(function() {
                var $bedSelect = document.getElementById('bed_id');
                if ($bedSelect && r.bed) {
                    var opt = document.createElement('option');
                    opt.value = r.bed.id;
                    opt.textContent = 'Bed ' + r.bed.bed_no + ' (' + r.bed.bed_type + ') — current';
                    opt.selected = true;
                    $bedSelect.appendChild(opt);
                    $bedSelect.disabled = false;
                }
            }, 100);

            new bootstrap.Modal(document.getElementById('residentModal')).show();
        } else {
            showToast('Failed to load resident', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to load resident', 'error');
    }
}

function resetForm() {
    document.getElementById('residentForm').reset();
    document.getElementById('residentId').value = '';

    document.getElementById('room_id').innerHTML = '<option value="">Select Hostel First</option>';
    document.getElementById('room_id').disabled = true;
    document.getElementById('bed_id').innerHTML = '<option value="">Select Room First</option>';
    document.getElementById('bed_id').disabled = true;

    document.getElementById('profilePreview').classList.remove('show');
    document.getElementById('profilePreview').src = '';

    document.querySelectorAll('.rs-form-error').forEach(function(el) {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.rs-form-control').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
}

document.getElementById('hostel_id').addEventListener('change', function() {
    loadRoomsForHostel(this.value);
});

document.getElementById('room_id').addEventListener('change', function() {
    loadBedsForRoom(this.value);
});

document.getElementById('profile_image').addEventListener('change', function(e) {
    var file = e.target.files[0];
    var preview = document.getElementById('profilePreview');

    if (file) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            preview.classList.add('show');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.remove('show');
    }
});

document.getElementById('residentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    var submitBtn = document.getElementById('residentSubmitBtn');
    var submitText = document.getElementById('residentSubmitText');
    var originalText = submitText.textContent;
    var residentId = document.getElementById('residentId').value;
    var isEdit = residentId !== '';

    document.querySelectorAll('.rs-form-error').forEach(function(el) {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.rs-form-control').forEach(function(el) {
        el.classList.remove('is-invalid');
    });

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="rs-spinner"></span> Saving...';

    var formData = new FormData(this);
    formData.set('biometric_access', document.getElementById('biometric_access').checked ? 1 : 0);

    var url = isEdit ? BASE_URL + '/' + residentId : BASE_URL;
    if (isEdit) formData.append('_method', 'PUT');

    try {
        var response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: formData
        });

        var data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('residentModal')).hide();
            setTimeout(function() { window.location.reload(); }, 700);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(function(field) {
                    var errEl = document.getElementById('error_' + field);
                    var inputEl = document.getElementById(field);
                    if (errEl) { errEl.textContent = data.errors[field][0]; errEl.classList.add('show'); }
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

function openVacateModal(id, name) {
    currentVacateId = id;
    document.getElementById('vacateResidentName').textContent = name;
    new bootstrap.Modal(document.getElementById('vacateModal')).show();
}

document.getElementById('confirmVacateBtn').addEventListener('click', async function() {
    if (!currentVacateId) return;

    var btn = this;
    var btnText = document.getElementById('vacateBtnText');
    var originalText = btnText.textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="rs-spinner"></span> Vacating...';

    try {
        var response = await fetch(BASE_URL + '/' + currentVacateId + '/vacate', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        });
        var data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('vacateModal')).hide();
            setTimeout(function() { window.location.reload(); }, 700);
        } else {
            showToast(data.message || 'Failed to vacate', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false;
        btnText.textContent = originalText;
        currentVacateId = null;
    }
});

async function reactivateResident(id, name) {
    if (!confirm('Reactivate "' + name + '"? Make sure their bed is still vacant.')) return;

    try {
        var response = await fetch(BASE_URL + '/' + id + '/reactivate', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        });
        var data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(function() { window.location.reload(); }, 700);
        } else {
            showToast(data.message || 'Failed to reactivate', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    }
}

function openDeleteModal(id, name) {
    currentDeleteId = id;
    document.getElementById('deleteResidentName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!currentDeleteId) return;

    var btn = this;
    var btnText = document.getElementById('deleteBtnText');
    var originalText = btnText.textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="rs-spinner"></span> Deleting...';

    try {
        var response = await fetch(BASE_URL + '/' + currentDeleteId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        });
        var data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();

            var card = document.querySelector('.rs-card[data-id="' + currentDeleteId + '"]');
            if (card) {
                card.style.transition = 'all 0.3s';
                card.style.opacity = '0';
                setTimeout(function() { card.remove(); updateCountLabel(); }, 300);
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

// Status filter (server-side)
var statusFilter = document.getElementById('rsStatusFilter');
statusFilter.addEventListener('change', function() {
    var url = new URL(window.location.href);
    url.searchParams.set('status', this.value);
    window.location.href = url.toString();
});

// Client-side filters
var searchInput = document.getElementById('rsSearchInput');
var hostelFilter = document.getElementById('rsHostelFilter');
var foodFilter = document.getElementById('rsFoodFilter');

function applyClientFilters() {
    var search = searchInput.value.toLowerCase().trim();
    var hostelId = hostelFilter.value;
    var food = foodFilter.value;

    var visible = 0;

    document.querySelectorAll('.rs-card').forEach(function(card) {
        var name = card.getAttribute('data-name');
        var code = card.getAttribute('data-code');
        var phone = card.getAttribute('data-phone');
        var cardHostelId = card.getAttribute('data-hostel-id');
        var cardFood = card.getAttribute('data-food');

        var matchSearch = !search || name.indexOf(search) > -1 || code.indexOf(search) > -1 || phone.indexOf(search) > -1;
        var matchHostel = !hostelId || cardHostelId === hostelId;
        var matchFood = !food || cardFood === food;

        if (matchSearch && matchHostel && matchFood) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    updateCountLabel(visible);
}

function updateCountLabel(count) {
    var total = document.querySelectorAll('.rs-card').length;
    var label = document.getElementById('rsCountLabel');
    if (count !== undefined && count !== total) {
        label.textContent = count + ' of ' + total + ' residents';
    } else {
        label.textContent = total + ' residents';
    }
}

searchInput.addEventListener('input', applyClientFilters);
hostelFilter.addEventListener('change', applyClientFilters);
foodFilter.addEventListener('change', applyClientFilters);

// Context menu
var contextMenu = document.getElementById('cardContextMenu');

function toggleCardMenu(event, id) {
    event.stopPropagation();
    currentContextId = id;

    var card = document.querySelector('.rs-card[data-id="' + id + '"]');
    var status = card ? card.getAttribute('data-status') : null;

    document.getElementById('ctxVacateBtn').style.display = status === 'ACTIVE' ? '' : 'none';
    document.getElementById('ctxReactivateBtn').style.display = status === 'VACATED' ? '' : 'none';

    var rect = event.currentTarget.getBoundingClientRect();
    contextMenu.style.display = 'block';
    contextMenu.style.left = Math.min(rect.right - 180, window.innerWidth - 190) + 'px';
    contextMenu.style.top = (rect.bottom + 4) + 'px';
}

function hideContextMenu() { contextMenu.style.display = 'none'; }

function contextEdit() {
    if (currentContextId) openEditModal(currentContextId);
    hideContextMenu();
}

function contextVacate() {
    if (currentContextId) {
        var card = document.querySelector('.rs-card[data-id="' + currentContextId + '"]');
        var name = card ? card.querySelector('.rs-card-title').textContent.trim() : '';
        openVacateModal(currentContextId, name);
    }
    hideContextMenu();
}

function contextReactivate() {
    if (currentContextId) {
        var card = document.querySelector('.rs-card[data-id="' + currentContextId + '"]');
        var name = card ? card.querySelector('.rs-card-title').textContent.trim() : '';
        reactivateResident(currentContextId, name);
    }
    hideContextMenu();
}

function contextDelete() {
    if (currentContextId) {
        var card = document.querySelector('.rs-card[data-id="' + currentContextId + '"]');
        var name = card ? card.querySelector('.rs-card-title').textContent.trim() : '';
        openDeleteModal(currentContextId, name);
    }
    hideContextMenu();
}

document.addEventListener('click', hideContextMenu);
document.addEventListener('scroll', hideContextMenu, true);

document.addEventListener('DOMContentLoaded', function() { updateCountLabel(); });
</script>
@endpush
