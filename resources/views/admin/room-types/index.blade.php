@extends('layouts.office')

@section('title', 'Room Types — Sanjay PG Hostel')
@section('page_title', 'Room Types')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* ═══════════════════════════════════════════
       ROOM TYPE MANAGEMENT
    ═══════════════════════════════════════════ */

    .rt-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .rt-page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .rt-page-title i { color: var(--sanjay-gold); }
    .rt-page-subtitle {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0.25rem 0 0 0;
    }

    /* ── Toolbar ── */
    .rt-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .rt-search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }
    .rt-search-box input {
        width: 100%;
        padding: 0.55rem 1rem 0.55rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        transition: all 0.2s;
        color: #374151;
    }
    .rt-search-box input:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .rt-search-box i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .rt-filter-select {
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
    .rt-filter-select:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }

    /* ── Buttons ── */
    .rt-btn-primary {
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
    .rt-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(10, 30, 63, 0.25);
        color: white;
    }

    /* ── Grid ── */
    .rt-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
    }

    /* ── Cards ── */
    .rt-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .rt-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.08);
        border-color: rgba(197, 160, 40, 0.3);
    }
    .rt-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--sanjay-gold), var(--sanjay-primary));
        opacity: 0;
        transition: opacity 0.3s;
    }
    .rt-card:hover::before { opacity: 1; }

    .rt-card-head {
        padding: 1rem 1.15rem 0.75rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .rt-card-icon {
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
    .rt-card-title-wrap { flex: 1; min-width: 0; }
    .rt-card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0 0 0.15rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rt-card-hostel {
        font-size: 0.68rem;
        color: #9ca3af;
        font-family: 'DM Mono', monospace;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .rt-card-hostel i { font-size: 0.65rem; }
    .rt-card-menu { position: relative; }
    .rt-card-menu-btn {
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
    .rt-card-menu-btn:hover {
        background: #f9fafb;
        border-color: var(--sanjay-gold);
        color: var(--sanjay-gold);
    }

    .rt-card-body {
        padding: 0 1.15rem 1rem;
        flex: 1;
    }
    .rt-card-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
        margin-bottom: 0.85rem;
    }
    .rt-stat-item {
        background: #f8fafc;
        border-radius: 9px;
        padding: 0.5rem 0.7rem;
        border: 1px solid #f1f5f9;
    }
    .rt-stat-label {
        font-size: 0.62rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 0.15rem;
    }
    .rt-stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        font-family: 'DM Mono', monospace;
        line-height: 1.1;
    }
    .rt-stat-value.rent { color: #10b981; }
    .rt-stat-value.deposit { color: #3b82f6; }

    .rt-card-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #6b7280;
        padding: 0.3rem 0;
    }
    .rt-card-detail i {
        color: var(--sanjay-gold);
        font-size: 0.8rem;
        width: 14px;
        flex-shrink: 0;
    }
    .rt-card-detail span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Badges ── */
    .rt-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .rt-badge.active   { background: #dcfce7; color: #166534; }
    .rt-badge.inactive { background: #fee2e2; color: #991b1b; }
    .rt-badge-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    /* ── Card Footer ── */
    .rt-card-footer {
        padding: 0.7rem 1.15rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #fafbfc;
    }
    .rt-card-actions {
        display: flex;
        gap: 0.35rem;
    }
    .rt-icon-btn {
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
    .rt-icon-btn:hover { transform: translateY(-1px); }
    .rt-icon-btn.edit:hover   { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    .rt-icon-btn.delete:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
    .rt-icon-btn.toggle:hover { background: #fefce8; border-color: #eab308; color: #ca8a04; }

    /* ── Empty State ── */
    .rt-empty {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        border: 2px dashed #e5e7eb;
        grid-column: 1 / -1;
    }
    .rt-empty-icon {
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
    .rt-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .rt-empty p  { color: #6b7280; font-size: 0.85rem; margin-bottom: 1.5rem; }

    /* ═══════════════════════════════════════════
       MODAL
    ═══════════════════════════════════════════ */
    .rt-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.15);
    }
    .rt-modal .modal-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        padding: 1.1rem 1.5rem;
    }
    .rt-modal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .rt-modal .modal-title i { color: var(--sanjay-gold); }
    .rt-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .rt-modal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    .rt-modal .modal-body::-webkit-scrollbar { width: 4px; }
    .rt-modal .modal-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .rt-modal .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f3f4f6;
        background: #fafbfc;
    }

    /* ── Form ── */
    .rt-form-section { margin-bottom: 1.5rem; }
    .rt-form-section:last-child { margin-bottom: 0; }
    .rt-form-section-title {
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
    .rt-form-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(197, 160, 40, 0.3), transparent);
    }
    .rt-form-group { margin-bottom: 0.9rem; }
    .rt-form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .rt-form-label .required { color: #ef4444; margin-left: 2px; }
    .rt-form-control {
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
    .rt-form-control:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .rt-form-control::placeholder { color: #9ca3af; }
    .rt-form-control.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .rt-form-error {
        font-size: 0.7rem;
        color: #ef4444;
        margin-top: 0.25rem;
        display: none;
    }
    .rt-form-error.show { display: block; }
    select.rt-form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.85rem center;
        padding-right: 2.2rem;
    }

    .rt-form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.9rem;
    }
    @media (max-width: 576px) {
        .rt-form-row { grid-template-columns: 1fr; }
    }

    /* ── Toggle Switch ── */
    .rt-switch {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        user-select: none;
        padding: 0.5rem 0;
    }
    .rt-switch input { display: none; }
    .rt-switch-slider {
        width: 42px;
        height: 24px;
        background: #e5e7eb;
        border-radius: 20px;
        position: relative;
        transition: background 0.25s;
        flex-shrink: 0;
    }
    .rt-switch-slider::after {
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
    .rt-switch input:checked + .rt-switch-slider {
        background: var(--sanjay-gold);
    }
    .rt-switch input:checked + .rt-switch-slider::after {
        transform: translateX(18px);
    }
    .rt-switch-label {
        font-size: 0.82rem;
        color: #374151;
        font-weight: 500;
    }

    /* ── Buttons ── */
    .rt-btn {
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
    .rt-btn-gold {
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
    }
    .rt-btn-gold:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(197, 160, 40, 0.35);
    }
    .rt-btn-gold:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .rt-btn-outline {
        background: white;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }
    .rt-btn-outline:hover { background: #f9fafb; color: #374151; }
    .rt-btn-danger { background: #ef4444; color: white; }
    .rt-btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }

    /* ── Spinner ── */
    .rt-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: rt-spin 0.6s linear infinite;
        display: inline-block;
    }
    @keyframes rt-spin { to { transform: rotate(360deg); } }

    /* ── Delete Modal ── */
    .rt-delete-icon {
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
    .rt-context-item {
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
    .rt-context-item:hover { background: #f9fafb; color: var(--sanjay-primary); }
    .rt-context-item.danger { color: #ef4444; }
    .rt-context-item.danger:hover { background: #fef2f2; }
    .rt-context-item i { font-size: 0.85rem; width: 16px; }

    /* ═══════════════════════════════════════════
       SELECT2 CUSTOM STYLING
    ═══════════════════════════════════════════ */
    .select2-container--default .select2-selection--single {
        height: auto;
        padding: 0.6rem 0.85rem;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        background: white;
        font-size: 0.82rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151;
        padding: 0;
        line-height: 1.4;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        top: 0;
        right: 0.5rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #6b7280 transparent transparent transparent;
        border-width: 5px 4px 0 4px;
    }

    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent var(--sanjay-gold) transparent;
        border-width: 0 4px 5px 4px;
    }

    /* Dropdown panel */
    .select2-dropdown {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-top: 4px;
    }

    .select2-container--default .select2-search--dropdown {
        padding: 0.6rem;
        background: #fafbfc;
        border-bottom: 1px solid #f3f4f6;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.82rem;
        outline: none;
        transition: all 0.2s;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }

    .select2-results {
        max-height: 340px;
    }

    .select2-container--default .select2-results__option {
        padding: 0.5rem 0.85rem;
        font-size: 0.82rem;
        transition: all 0.15s;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: var(--sanjay-gold);
        color: var(--sanjay-primary);
        font-weight: 600;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background: rgba(197, 160, 40, 0.15);
        color: var(--sanjay-primary);
        font-weight: 600;
    }

    /* optgroup styling */
    .select2-container--default .select2-results__group {
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--sanjay-gold);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 0.55rem 0.85rem 0.35rem;
        background: #fafbfc;
        border-top: 1px solid #f3f4f6;
    }

    .select2-container--default .select2-results__group:first-child {
        border-top: none;
    }

    .select2-container--default .select2-results__option--group {
        padding: 0;
    }

    .select2-container--default .select2-results__option--group .select2-results__option {
        padding-left: 1.5rem;
    }

    /* Invalid state */
    .select2-container--default.is-invalid .select2-selection--single {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .rt-page-header { flex-direction: column; align-items: flex-start; }
        .rt-toolbar { flex-direction: column; align-items: stretch; }
        .rt-search-box { max-width: none; }
        .rt-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════ --}}
<div class="rt-page-header">
    <div>
        <h2 class="rt-page-title">
            <i class="bi bi-tags"></i>
            Room Type Management
        </h2>
        <p class="rt-page-subtitle">Manage room categories, rent, and deposit per hostel</p>
    </div>
    <button type="button" class="rt-btn-primary" onclick="openCreateModal()">
        <i class="bi bi-plus-lg"></i>
        Add Room Type
    </button>
</div>

{{-- ═══════════════════════════════════════════
     TOOLBAR
═══════════════════════════════════════════ --}}
<div class="rt-toolbar">
    <div class="rt-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="rtSearchInput" placeholder="Search room types...">
    </div>

    <select class="rt-filter-select" id="rtHostelFilter">
        <option value="">All Hostels</option>
        @foreach($hostels as $hostel)
            <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
        @endforeach
    </select>

    <select class="rt-filter-select" id="rtStatusFilter">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>

    <span style="margin-left:auto; font-size:0.75rem; color:#9ca3af;" id="rtCountLabel">
        {{ $roomTypes->count() }} room types
    </span>
</div>

{{-- ═══════════════════════════════════════════
     ROOM TYPE GRID
═══════════════════════════════════════════ --}}
<div class="rt-grid" id="rtGrid">
    @forelse($roomTypes as $roomType)
    <div class="rt-card"
         data-id="{{ $roomType->id }}"
         data-name="{{ strtolower($roomType->room_type_name) }}"
         data-hostel-id="{{ $roomType->hostel_id }}"
         data-status="{{ $roomType->is_active ? '1' : '0' }}">

        {{-- Card Head --}}
        <div class="rt-card-head">
            <div class="rt-card-icon">
                <i class="bi bi-tags"></i>
            </div>
            <div class="rt-card-title-wrap">
                <h3 class="rt-card-title" title="{{ $roomType->room_type_name }}">
                    {{ $roomType->room_type_name }}
                </h3>
                <div class="rt-card-hostel">
                    <i class="bi bi-building"></i>
                    {{ $roomType->hostel->hostel_name ?? 'N/A' }}
                </div>
            </div>
            <div class="rt-card-menu">
                <button type="button" class="rt-card-menu-btn"
                        onclick="toggleCardMenu(event, {{ $roomType->id }})">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
            </div>
        </div>

        {{-- Card Body --}}
        <div class="rt-card-body">
            {{-- Badges --}}
            <div style="display:flex; gap:0.35rem; flex-wrap:wrap; margin-bottom:0.85rem;">
                <span class="rt-badge {{ $roomType->is_active ? 'active' : 'inactive' }}">
                    <span class="rt-badge-dot"></span>
                    {{ $roomType->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="rt-badge" style="background:#fef3c7; color:#92400e;">
                    <i class="bi bi-people"></i>
                    {{ $roomType->sharing_count }}-Sharing
                </span>
            </div>

            {{-- Stats --}}
            <div class="rt-card-stats">
                <div class="rt-stat-item">
                    <div class="rt-stat-label">Monthly Rent</div>
                    <div class="rt-stat-value rent">₹{{ number_format($roomType->monthly_rent, 0) }}</div>
                </div>
                <div class="rt-stat-item">
                    <div class="rt-stat-label">Deposit</div>
                    <div class="rt-stat-value deposit">₹{{ number_format($roomType->deposit_amount, 0) }}</div>
                </div>
            </div>

            {{-- Details --}}
            <div class="rt-card-detail">
                <i class="bi bi-door-open"></i>
                <span>{{ $roomType->rooms_count ?? 0 }} room(s) using this type</span>
            </div>

            <div class="rt-card-detail">
                <i class="bi bi-people"></i>
                <span>{{ $roomType->sharing_count }} person(s) per room</span>
            </div>
        </div>

        {{-- Card Footer --}}
        <div class="rt-card-footer">
            <span style="font-size:0.68rem; color:#9ca3af;">
                <i class="bi bi-clock"></i>
                {{ $roomType->created_at->format('d M Y') }}
            </span>
            <div class="rt-card-actions">
                <button type="button" class="rt-icon-btn toggle"
                        onclick="toggleStatus({{ $roomType->id }})"
                        title="{{ $roomType->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi bi-{{ $roomType->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                </button>
                <button type="button" class="rt-icon-btn edit"
                        onclick="openEditModal({{ $roomType->id }})"
                        title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="rt-icon-btn delete"
                        onclick="openDeleteModal({{ $roomType->id }}, '{{ addslashes($roomType->room_type_name) }}')"
                        title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="rt-empty">
        <div class="rt-empty-icon">
            <i class="bi bi-tags"></i>
        </div>
        <h5>No Room Types Yet</h5>
        <p>Create room types to categorize your rooms (Single, Double, Triple sharing with AC/Non-AC options).</p>
        <button type="button" class="rt-btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i>
            Add Your First Room Type
        </button>
    </div>
    @endforelse
</div>

{{-- ═══════════════════════════════════════════
     CREATE / EDIT MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade rt-modal" id="roomTypeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomTypeModalTitle">
                    <i class="bi bi-tags"></i>
                    Add New Room Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="roomTypeForm" autocomplete="off">
                @csrf
                <input type="hidden" id="roomTypeId" name="id" value="">
                <input type="hidden" id="bed_type" name="bed_type" value="NORMAL">
                <input type="hidden" id="ac_type" name="ac_type" value="NON_AC">

                <div class="modal-body">
                    {{-- Basic Info --}}
                    <div class="rt-form-section">
                        <div class="rt-form-section-title">
                            <i class="bi bi-info-circle"></i>
                            Basic Information
                        </div>

                        <div class="rt-form-group">
                            <label class="rt-form-label">
                                Hostel <span class="required">*</span>
                            </label>
                            <select class="rt-form-control" id="hostel_id" name="hostel_id" required>
                                <option value="">Select Hostel</option>
                                @foreach($hostels as $hostel)
                                    <option value="{{ $hostel->id }}">
                                        {{ $hostel->hostel_name }} ({{ $hostel->hostel_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="rt-form-error" id="error_hostel_id"></div>
                        </div>

                        {{-- SEARCHABLE ROOM TYPE SELECT --}}
                        <div class="rt-form-group">
                            <label class="rt-form-label">
                                Room Type <span class="required">*</span>
                            </label>
                            <select class="rt-form-control" id="room_type_name" name="room_type_name" required>
                                <option value="">— Search & Select Room Type —</option>
                                @foreach($roomTypeOptions as $groupLabel => $options)
                                    <optgroup label="━━ {{ $groupLabel }} ━━">
                                        @foreach($options as $opt)
                                            <option value="{{ $opt['value'] }}"
                                                    data-sharing="{{ $opt['sharing_count'] }}"
                                                    data-bed="{{ $opt['bed_type'] }}"
                                                    data-ac="{{ $opt['ac_type'] }}">
                                                {{ $opt['label'] }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="rt-form-error" id="error_room_type_name"></div>
                            <small style="color:#9ca3af; font-size:0.68rem; margin-top:4px; display:block;">
                                <i class="bi bi-search"></i> Type to search. Sharing count auto-fills from selection.
                            </small>
                        </div>
                    </div>

                    {{-- Pricing --}}
                    <div class="rt-form-section">
                        <div class="rt-form-section-title">
                            <i class="bi bi-currency-rupee"></i>
                            Capacity & Pricing
                        </div>

                        <div class="rt-form-row">
                            <div class="rt-form-group">
                                <label class="rt-form-label">
                                    Sharing Count <span class="required">*</span>
                                </label>
                                <input type="number" class="rt-form-control"
                                       id="sharing_count" name="sharing_count"
                                       min="1" max="8"
                                       readonly
                                       style="background:#f3f4f6; cursor:not-allowed;"
                                       placeholder="Auto-filled" required>
                                <div class="rt-form-error" id="error_sharing_count"></div>
                            </div>

                            <div class="rt-form-group">
                                <label class="rt-form-label">
                                    Monthly Rent (₹) <span class="required">*</span>
                                </label>
                                <input type="number" step="0.01" class="rt-form-control"
                                       id="monthly_rent" name="monthly_rent"
                                       placeholder="e.g., 6000" min="0" required>
                                <div class="rt-form-error" id="error_monthly_rent"></div>
                            </div>
                        </div>

                        <div class="rt-form-group">
                            <label class="rt-form-label">Deposit Amount (₹)</label>
                            <input type="number" step="0.01" class="rt-form-control"
                                   id="deposit_amount" name="deposit_amount"
                                   placeholder="e.g., 3000" min="0" value="0">
                            <div class="rt-form-error" id="error_deposit_amount"></div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="rt-form-section">
                        <div class="rt-form-section-title">
                            <i class="bi bi-toggle-on"></i>
                            Status
                        </div>

                        <label class="rt-switch">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <span class="rt-switch-slider"></span>
                            <span class="rt-switch-label">Active (visible for assigning to rooms)</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="rt-btn rt-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                        Cancel
                    </button>
                    <button type="submit" class="rt-btn rt-btn-gold" id="roomTypeSubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="roomTypeSubmitText">Save Room Type</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     DELETE MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade rt-modal" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:2rem 1.5rem;">
                <div class="rt-delete-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 style="color:var(--sanjay-primary); font-weight:700; margin-bottom:0.5rem;">
                    Delete Room Type?
                </h5>
                <p style="color:#6b7280; font-size:0.85rem; margin-bottom:0.25rem;">
                    You are about to delete:
                </p>
                <p style="color:var(--sanjay-primary); font-weight:600; font-size:0.95rem; margin-bottom:1rem;"
                   id="deleteRoomTypeName"></p>
                <p style="color:#ef4444; font-size:0.75rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:0.5rem;">
                <button type="button" class="rt-btn rt-btn-outline" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>
                <button type="button" class="rt-btn rt-btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    <span id="deleteBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CONTEXT MENU --}}
<div id="cardContextMenu" style="display:none; position:fixed; z-index:9999; background:white; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); border:1px solid #e5e7eb; min-width:170px; overflow:hidden;">
    <button type="button" class="rt-context-item" onclick="contextEdit()">
        <i class="bi bi-pencil"></i> Edit Room Type
    </button>
    <button type="button" class="rt-context-item" onclick="contextToggle()">
        <i class="bi bi-arrow-repeat"></i> Toggle Status
    </button>
    <button type="button" class="rt-context-item danger" onclick="contextDelete()">
        <i class="bi bi-trash"></i> Delete Room Type
    </button>
</div>

@endsection

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// ═══════════════════════════════════════════
// ROOM TYPE MANAGEMENT - JS
// ═══════════════════════════════════════════

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL = "{{ url('admin/room-types') }}";
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

// ═══════════════════════════════════════════
// SELECT2 INITIALIZATION
// ═══════════════════════════════════════════
function initRoomTypeSelect2() {
    const $el = $('#room_type_name');

    // Destroy if already initialized
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }

    $el.select2({
        placeholder: '— Search & Select Room Type —',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#roomTypeModal'),
        matcher: function(params, data) {
            // If no search term, return all
            if ($.trim(params.term) === '') {
                return data;
            }

            // Skip optgroup text check
            if (typeof data.text === 'undefined') {
                return null;
            }

            // Case-insensitive match
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();

            if (text.indexOf(term) > -1) {
                return data;
            }

            return null;
        }
    });
}

// ── Auto-fill on select change ──
$(document).on('select2:select', '#room_type_name', function(e) {
    const data = e.params.data;
    const $option = $(data.element);

    const sharing = $option.data('sharing');
    const bedType = $option.data('bed');
    const acType = $option.data('ac');

    if (sharing) {
        document.getElementById('sharing_count').value = sharing;
    }

    const bedInput = document.getElementById('bed_type');
    if (bedInput) bedInput.value = bedType || 'NORMAL';

    const acInput = document.getElementById('ac_type');
    if (acInput) acInput.value = acType || 'NON_AC';
});

// ── Clear on deselect ──
$(document).on('select2:clear', '#room_type_name', function() {
    document.getElementById('sharing_count').value = '';
    const bedInput = document.getElementById('bed_type');
    if (bedInput) bedInput.value = 'NORMAL';
    const acInput = document.getElementById('ac_type');
    if (acInput) acInput.value = 'NON_AC';
});

// ── Open Create Modal ──
function openCreateModal() {
    resetForm();
    document.getElementById('roomTypeModalTitle').innerHTML =
        '<i class="bi bi-tags"></i> Add New Room Type';
    document.getElementById('roomTypeSubmitText').textContent = 'Save Room Type';
    document.getElementById('roomTypeId').value = '';
    document.getElementById('is_active').checked = true;

    // Show modal first, then init select2 (important for positioning)
    const modal = new bootstrap.Modal(document.getElementById('roomTypeModal'));
    modal.show();

    // Re-init Select2 after modal is shown
    document.getElementById('roomTypeModal').addEventListener('shown.bs.modal', function handler() {
        initRoomTypeSelect2();
        this.removeEventListener('shown.bs.modal', handler);
    });
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
            const rt = data.roomType;

            document.getElementById('roomTypeModalTitle').innerHTML =
                '<i class="bi bi-pencil-square"></i> Edit Room Type';
            document.getElementById('roomTypeSubmitText').textContent = 'Update Room Type';
            document.getElementById('roomTypeId').value = rt.id;

            document.getElementById('hostel_id').value = rt.hostel_id;
            document.getElementById('sharing_count').value = rt.sharing_count;
            document.getElementById('monthly_rent').value = rt.monthly_rent;
            document.getElementById('deposit_amount').value = rt.deposit_amount;
            document.getElementById('is_active').checked = !!rt.is_active;

            // Hidden fields
            const bedInput = document.getElementById('bed_type');
            if (bedInput) bedInput.value = rt.bed_type || 'NORMAL';
            const acInput = document.getElementById('ac_type');
            if (acInput) acInput.value = rt.ac_type || 'NON_AC';

            // Show modal first
            const modal = new bootstrap.Modal(document.getElementById('roomTypeModal'));
            modal.show();

            // Init Select2 and set value AFTER modal is shown
            document.getElementById('roomTypeModal').addEventListener('shown.bs.modal', function handler() {
                initRoomTypeSelect2();

                // Set the value
                $('#room_type_name').val(rt.room_type_name).trigger('change.select2');

                this.removeEventListener('shown.bs.modal', handler);
            });
        } else {
            showToast('Failed to load room type', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to load room type', 'error');
    }
}

// ── Reset Form ──
function resetForm() {
    document.getElementById('roomTypeForm').reset();
    document.getElementById('roomTypeId').value = '';
    document.getElementById('sharing_count').value = '';
    document.getElementById('deposit_amount').value = 0;

    // Reset Select2
    if ($('#room_type_name').hasClass('select2-hidden-accessible')) {
        $('#room_type_name').val(null).trigger('change');
    } else {
        document.getElementById('room_type_name').value = '';
    }

    // Reset hidden inputs
    const bedInput = document.getElementById('bed_type');
    if (bedInput) bedInput.value = 'NORMAL';
    const acInput = document.getElementById('ac_type');
    if (acInput) acInput.value = 'NON_AC';

    document.querySelectorAll('.rt-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.rt-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

// ── Submit ──
document.getElementById('roomTypeForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('roomTypeSubmitBtn');
    const submitText = document.getElementById('roomTypeSubmitText');
    const originalText = submitText.textContent;
    const roomTypeId = document.getElementById('roomTypeId').value;
    const isEdit = roomTypeId !== '';

    document.querySelectorAll('.rt-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.rt-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="rt-spinner"></span> Saving...';

    const formData = new FormData(this);
    const url = isEdit ? `${BASE_URL}/${roomTypeId}` : BASE_URL;

    formData.set('is_active', document.getElementById('is_active').checked ? 1 : 0);

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
            bootstrap.Modal.getInstance(document.getElementById('roomTypeModal')).hide();
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
    document.getElementById('deleteRoomTypeName').textContent = `"${name}"`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Confirm Delete ──
document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!currentDeleteId) return;

    const btn = this;
    const btnText = document.getElementById('deleteBtnText');
    const originalText = btnText.textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="rt-spinner"></span> Deleting...';

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

            const card = document.querySelector(`.rt-card[data-id="${currentDeleteId}"]`);
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

            const card = document.querySelector(`.rt-card[data-id="${id}"]`);
            if (card) {
                const badge = card.querySelector('.rt-badge');
                const toggleBtn = card.querySelector('.rt-icon-btn.toggle');

                if (data.is_active) {
                    badge.className = 'rt-badge active';
                    badge.innerHTML = '<span class="rt-badge-dot"></span> Active';
                    toggleBtn.title = 'Deactivate';
                    toggleBtn.innerHTML = '<i class="bi bi-pause-circle"></i>';
                    card.setAttribute('data-status', '1');
                } else {
                    badge.className = 'rt-badge inactive';
                    badge.innerHTML = '<span class="rt-badge-dot"></span> Inactive';
                    toggleBtn.title = 'Activate';
                    toggleBtn.innerHTML = '<i class="bi bi-play-circle"></i>';
                    card.setAttribute('data-status', '0');
                }
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
const searchInput = document.getElementById('rtSearchInput');
const hostelFilter = document.getElementById('rtHostelFilter');
const statusFilter = document.getElementById('rtStatusFilter');

function applyFilters() {
    const search = searchInput.value.toLowerCase().trim();
    const hostelId = hostelFilter.value;
    const status = statusFilter.value;

    let visibleCount = 0;

    document.querySelectorAll('.rt-card').forEach(card => {
        const name = card.getAttribute('data-name');
        const cardHostelId = card.getAttribute('data-hostel-id');
        const cardStatus = card.getAttribute('data-status');

        const matchesSearch = !search || name.includes(search);
        const matchesHostel = !hostelId || cardHostelId === hostelId;
        const matchesStatus = status === '' || cardStatus === status;

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
    const total = document.querySelectorAll('.rt-card').length;
    const label = document.getElementById('rtCountLabel');
    if (count !== undefined && count !== total) {
        label.textContent = `${count} of ${total} room types`;
    } else {
        label.textContent = `${total} room types`;
    }
}

searchInput.addEventListener('input', applyFilters);
hostelFilter.addEventListener('change', applyFilters);
statusFilter.addEventListener('change', applyFilters);

// ── Context Menu ──
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

function contextToggle() {
    if (currentContextId) toggleStatus(currentContextId);
    hideContextMenu();
}

function contextDelete() {
    if (currentContextId) {
        const card = document.querySelector(`.rt-card[data-id="${currentContextId}"]`);
        if (card) {
            const name = card.querySelector('.rt-card-title').textContent.trim();
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
