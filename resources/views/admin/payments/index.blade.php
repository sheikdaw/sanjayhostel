@extends('layouts.office')

@section('title', 'Payment Management')
@section('page_title', 'Payment Management')

@push('styles')
<style>
    .payment-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        position: relative;
    }
    .payment-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }

    .payment-card .card-checkbox {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
    }
    .payment-card .card-checkbox input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .payment-header {
        background: var(--sanjay-primary);
        padding: 1.25rem;
        padding-left: 3rem;
        color: white;
        position: relative;
        min-height: 80px;
    }
    .payment-status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .payment-status-badge.pending { background: #ef4444; color: white; }
    .payment-status-badge.partial { background: #f59e0b; color: white; }
    .payment-status-badge.paid { background: #22c55e; color: white; }
    .payment-status-badge.unpaid { background: #6b7280; color: white; }
    .payment-status-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: white; display: inline-block; }

    .payment-body { padding: 1rem 1.25rem; }

    .payment-meta {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .payment-meta i { width: 16px; color: var(--sanjay-gold); }

    .payment-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin: 0.75rem 0;
    }
    .payment-stat-item {
        text-align: center;
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: 8px;
    }
    .payment-stat-item .number { font-size: 0.95rem; font-weight: 700; color: var(--sanjay-primary); }
    .payment-stat-item .number.balance-due { color: #ef4444; }
    .payment-stat-item .number.balance-clear { color: #22c55e; }
    .payment-stat-item .label { font-size: 0.6rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

    .remark-box {
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: #f8fafc;
        border-radius: 6px;
        font-size: 0.7rem;
        border-left: 3px solid var(--sanjay-gold);
        word-break: break-word;
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
    .status-badge.pending { background: #fee2e2; color: #991b1b; }
    .status-badge.partial { background: #fef3c7; color: #92400e; }
    .status-badge.paid { background: #dcfce7; color: #166534; }
    .status-badge.unpaid { background: #e5e7eb; color: #4b5563; }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.pending .dot { background: #ef4444; }
    .status-badge.partial .dot { background: #f59e0b; }
    .status-badge.paid .dot { background: #22c55e; }
    .status-badge.unpaid .dot { background: #6b7280; }

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
    .btn-action.text-success:hover { background: #dcfce7; border-color: #86efac; }
    .btn-action.text-primary:hover { background: #dbeafe; border-color: #93c5fd; }

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
    select.rv-input:disabled { cursor: not-allowed; opacity: 0.6; }
    select.rv-input[multiple] { min-height: 120px; }

    .form-label { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; }
    .form-label .required { color: #dc2626; margin-left: 2px; }
    .invalid-feedback { font-size: 0.75rem; color: #dc2626; margin-top: 0.25rem; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-state i { font-size: 4rem; color: #d1d5db; margin-bottom: 1rem; }

    .pending-alert {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 12px;
        padding: 0.85rem 1.1rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .pending-alert .count { font-weight: 700; color: #991b1b; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.85rem;
        margin-bottom: 1.25rem;
    }
    .stat-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
    }
    .stat-card .number { font-size: 1.3rem; font-weight: 700; color: var(--sanjay-primary); }
    .stat-card .label { font-size: 0.65rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

    .filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.85rem;
        margin-bottom: 1.25rem;
    }
    .filter-group select,
    .filter-group input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
        background: #fafafa;
        width: 100%;
    }
    .search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.85rem;
    }
    .search-box input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 0.4rem 0.6rem 0.4rem 2rem;
        font-size: 0.8rem;
        background: #fafafa;
    }

    .bulk-actions {
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }
    .bulk-actions.show { display: flex; }

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
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .current-month-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .discount-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
    }
    .discount-badge.applied { background: #dcfce7; color: #166534; }
    .discount-badge.not-applied { background: #fee2e2; color: #991b1b; }

    .payments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1rem;
    }
    @media (max-width: 768px) {
        .payments-grid {
            grid-template-columns: 1fr;
        }
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9998;
    }
    .loading-overlay.show {
        display: flex;
    }
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #e5e7eb;
        border-top: 4px solid var(--sanjay-gold);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .filter-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.85rem;
        color: #6b7280;
        background: #f8fafc;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .filter-info .label {
        font-weight: 600;
        color: #374151;
    }
    .filter-info .badge-filter {
        background: #e5e7eb;
        padding: 0.15rem 0.6rem;
        border-radius: 12px;
        font-size: 0.75rem;
        color: #374151;
    }
    .filter-info .count-badge {
        margin-left: auto;
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Status legend */
    .status-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.75rem;
    }
    .status-legend .legend-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .status-legend .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-legend .legend-dot.unpaid { background: #6b7280; }
    .status-legend .legend-dot.pending { background: #ef4444; }
    .status-legend .legend-dot.partial { background: #f59e0b; }
    .status-legend .legend-dot.paid { background: #22c55e; }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Payment Management</h1>
        <p class="ol-page-sub">Manage monthly rent payments and receipts</p>
        <div class="d-flex align-items-center gap-3 mt-2">
            <span class="current-month-badge" id="currentMonthBadge">
                <i class="bi bi-calendar-check"></i>
                <span id="currentMonthText">{{ date('F Y') }}</span>
            </span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="rv-submit dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="exportDropdown" style="min-width:350px; padding:0.5rem;">
                <li class="dropdown-header">📊 Filtered Reports</li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportWithType('filtered')">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i> Filtered Payments (CSV)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportWithType('pdf')">
                        <i class="bi bi-file-pdf me-2 text-danger"></i> Filtered Payments (PDF)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">📋 Payment Status Reports</li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportPaymentStatus('csv')">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i> Payment Status (CSV)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportPaymentStatus('pdf')">
                        <i class="bi bi-file-pdf me-2 text-danger"></i> Payment Status (PDF)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">📈 Summary Reports</li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportWithType('summary')">
                        <i class="bi bi-bar-chart me-2 text-info"></i> Payment Summary (PDF)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">🏢 Hostel Wise Reports</li>
                @foreach($hostels as $hostel)
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportHostelWise({{ $hostel->id }})" style="font-size:0.75rem; padding:0.25rem 1rem;">
                            <i class="bi bi-building me-2 text-warning"></i> {{ $hostel->hostel_name }}
                        </a>
                    </li>
                @endforeach
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">🔴 Unpaid Reports</li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportUnpaid('csv')">
                        <i class="bi bi-file-earmark-text me-2 text-danger"></i> Unpaid Summary (CSV)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportUnpaid('pdf')">
                        <i class="bi bi-file-pdf me-2 text-danger"></i> Unpaid Summary (PDF)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">✅ Paid Reports</li>
                <li>
                    <a class="dropdown-item" href="#" onclick="exportPaid()">
                        <i class="bi bi-file-earmark-text me-2 text-success"></i> Paid Payments (CSV)
                    </a>
                </li>
            </ul>
        </div>
        <button type="button" class="rv-submit" id="bulkPaymentBtn"
            style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-collection"></i> Bulk
        </button>
        <button type="button" class="rv-submit" id="addPaymentBtn"
            style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i> Add Payment
        </button>
    </div>
</div>

{{-- Status Legend --}}
<div class="status-legend">
    <span class="legend-item"><span class="legend-dot unpaid"></span> UNPAID - No payment for this month</span>
    <span class="legend-item"><span class="legend-dot pending"></span> PENDING - Previous month(s) pending</span>
    <span class="legend-item"><span class="legend-dot partial"></span> PARTIAL - Partial payment this month</span>
    <span class="legend-item"><span class="legend-dot paid"></span> PAID - Fully paid this month</span>
</div>

{{-- Pending Alert --}}
@php
    $unpaidCount = collect($combinedData)->filter(function($item) { return $item->status == 'UNPAID'; })->count();
    $pendingCount = collect($combinedData)->filter(function($item) { return $item->status == 'PENDING'; })->count();
    $partialCount = collect($combinedData)->filter(function($item) { return $item->status == 'PARTIAL'; })->count();
    $paidCount = collect($combinedData)->filter(function($item) { return $item->status == 'PAID'; })->count();

    $totalPendingAmount = collect($combinedData)->filter(function($item) {
        return in_array($item->status, ['UNPAID', 'PENDING', 'PARTIAL']);
    })->sum('balance_amount');

    $currentPendingTotal = collect($combinedData)->filter(function($item) {
        return in_array($item->status, ['UNPAID', 'PENDING', 'PARTIAL']);
    })->sum('current_balance_amount');

    $prevPendingTotal = collect($combinedData)->filter(function($item) {
        return in_array($item->status, ['UNPAID', 'PENDING', 'PARTIAL']);
    })->sum('previous_pending_amount');
@endphp

<div class="pending-alert" id="pendingAlert" style="{{ ($pendingCount + $unpaidCount + $partialCount) > 0 ? '' : 'display:none;' }}">
    <div>
        <i class="bi bi-exclamation-triangle-fill" style="color:#991b1b;"></i>
        <span style="font-weight:600; color:#991b1b;">Payment Status Summary:</span>
        <div style="font-size:0.85rem; margin-top:4px;">
            <span style="color:#6b7280;">⬜ Unpaid:</span> <span class="count" style="color:#6b7280;">{{ $unpaidCount }}</span>
            <span style="color:#6b7280; margin:0 4px;">|</span>
            <span style="color:#ef4444;">🔴 Pending (Previous):</span> <span class="count" style="color:#ef4444;">{{ $pendingCount }}</span>
            <span style="color:#6b7280; margin:0 4px;">|</span>
            <span style="color:#f59e0b;">🟡 Partial:</span> <span class="count" style="color:#f59e0b;">{{ $partialCount }}</span>
            <span style="color:#6b7280; margin:0 4px;">|</span>
            <span style="color:#22c55e;">✅ Paid:</span> <span class="count" style="color:#22c55e;">{{ $paidCount }}</span>
            <br>
            <span style="color:#6b7280;">💰 Total Due:</span> <span class="count" style="color:#991b1b;">₹{{ number_format($totalPendingAmount, 2) }}</span>
            <span style="color:#6b7280; margin:0 4px;">|</span>
            <span style="color:#f59e0b;">📅 Current Balance:</span> <span class="count" style="color:#f59e0b;">₹{{ number_format($currentPendingTotal, 2) }}</span>
            <span style="color:#6b7280; margin:0 4px;">|</span>
            <span style="color:#ef4444;">📅 Previous Pending:</span> <span class="count" style="color:#ef4444;">₹{{ number_format($prevPendingTotal, 2) }}</span>
        </div>
    </div>
    <button class="btn btn-sm btn-danger" onclick="filterPending()">View Pending</button>
</div>

{{-- Statistics --}}
<div class="stats-grid" id="statsGrid">
    <div class="stat-card">
        <div class="number" id="statTotal">{{ $stats['total'] }}</div>
        <div class="label">Total</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#ef4444;" id="statPending">{{ $stats['pending'] }}</div>
        <div class="label">Pending (Previous)</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#f59e0b;" id="statPartial">{{ $stats['partial'] }}</div>
        <div class="label">Partial</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#22c55e;" id="statPaid">{{ $stats['paid'] }}</div>
        <div class="label">Paid</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#6b7280;" id="statUnpaid">{{ $stats['unpaid'] ?? 0 }}</div>
        <div class="label">Unpaid</div>
    </div>
    <div class="stat-card">
        <div class="number" id="statCollected">₹{{ number_format($stats['total_collected'] ?? 0, 2) }}</div>
        <div class="label">Collected</div>
    </div>
</div>

{{-- Filter Info --}}
<div class="filter-info" id="filterInfo">
    <span><i class="bi bi-funnel"></i> <span class="label">Filters:</span></span>
    <span><i class="bi bi-calendar3"></i> <span id="filterMonthDisplay">{{ $filterMonthName }}</span> <span id="filterYearDisplay">{{ $filterYear }}</span></span>
    @if($filterHostelId)
        <span><i class="bi bi-building"></i> <span id="filterHostelDisplay">{{ $filterHostelName }}</span></span>
    @endif
    @if($filterStatus)
        <span><i class="bi bi-tag"></i> <span id="filterStatusDisplay">{{ $filterStatus }}</span></span>
    @endif
    @if($search)
        <span><i class="bi bi-search"></i> "<span id="filterSearchDisplay">{{ $search }}</span>"</span>
    @endif
    <span class="count-badge">
        <i class="bi bi-info-circle"></i> <span id="recordCount">{{ count($combinedData) }}</span> records found
    </span>
</div>

{{-- Bulk Actions --}}
<div class="bulk-actions" id="bulkActions">
    <span><i class="bi bi-check-square"></i> <span class="count" id="selectedCount">0</span> selected</span>
    <span style="color:#6b7280;">|</span>
    <select id="bulkStatusSelect" style="padding:0.2rem 0.5rem; border-radius:4px; border:1px solid #d1d5db; font-size:0.75rem;">
        <option value="">Change Status</option>
        <option value="PAID">Paid</option>
        <option value="PARTIAL">Partial</option>
        <option value="PENDING">Pending</option>
    </select>
    <button class="btn-action text-primary" onclick="bulkStatusUpdate()"><i class="bi bi-check-circle"></i> Apply</button>
    <button class="btn-action text-danger" onclick="bulkDelete()"><i class="bi bi-trash"></i> Delete</button>
    <button class="btn-action" onclick="clearSelection()"><i class="bi bi-x"></i> Clear</button>
</div>

{{-- Filter Section --}}
<div class="filter-section">
    <div class="row g-2 w-100">
        <div class="col-md-3">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchPayment" placeholder="Search by name, receipt..." value="{{ $search ?? '' }}">
            </div>
        </div>
        <div class="col-md-2">
            <select id="filterStatus" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="UNPAID" {{ $filterStatus == 'UNPAID' ? 'selected' : '' }}>⬜ Unpaid</option>
                <option value="PENDING" {{ $filterStatus == 'PENDING' ? 'selected' : '' }}>🔴 Pending (Previous)</option>
                <option value="PARTIAL" {{ $filterStatus == 'PARTIAL' ? 'selected' : '' }}>🟡 Partial</option>
                <option value="PAID" {{ $filterStatus == 'PAID' ? 'selected' : '' }}>✅ Paid</option>
            </select>
        </div>
        <div class="col-md-2">
            <select id="filterHostel" class="form-select form-select-sm">
                <option value="">All Hostels</option>
                @foreach($hostels as $hostel)
                    <option value="{{ $hostel->id }}" {{ $filterHostelId == $hostel->id ? 'selected' : '' }}>
                        {{ $hostel->hostel_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select id="filterMonth" class="form-select form-select-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $filterMonth == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <select id="filterYear" class="form-select form-select-sm">
                @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-sm btn-outline-secondary w-100" onclick="applyFiltersAjax()">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</div>

{{-- PAYMENT CARDS GRID --}}
<div class="payments-grid" id="paymentsGrid">
    @if(count($combinedData) > 0)
        @foreach($combinedData as $payment)
            <div class="payment-card" id="payment-card-{{ $payment->id ?? 'new-' . $loop->index }}" data-resident-id="{{ $payment->resident_id }}" data-status="{{ $payment->status }}">
                <div class="card-checkbox">
                    @if($payment->id)
                        <input type="checkbox" class="payment-checkbox" value="{{ $payment->id }}" onchange="updateBulkActions()">
                    @endif
                </div>
                <div class="payment-header" style="background: {{ $payment->status == 'PAID' ? '#22c55e' : ($payment->status == 'PARTIAL' ? '#f59e0b' : ($payment->status == 'UNPAID' ? '#6b7280' : '#ef4444')) }};">
                    <div>
                        <strong style="font-size:0.9rem;">{{ $payment->resident->name ?? 'N/A' }}</strong>
                        <div style="font-size:0.65rem; opacity:0.8;">{{ $payment->resident->resident_code ?? '' }} | Room #{{ $payment->resident->room->room_no ?? 'N/A' }}</div>
                    </div>
                    <span class="payment-status-badge {{ strtolower($payment->status) }}">
                        <span class="dot"></span> {{ $payment->status }}
                    </span>
                </div>
                <div class="payment-body">
                    <div class="payment-meta">
                        <i class="bi bi-receipt"></i> Receipt: {{ $payment->receipt_no }}
                    </div>
                    <div class="payment-meta">
                        <i class="bi bi-calendar3"></i> {{ date('F', mktime(0,0,0,$payment->month,1)) }} {{ $payment->year }}
                    </div>
                    <div class="payment-meta">
                        <i class="bi bi-building"></i> {{ $payment->resident->hostel->hostel_name ?? 'N/A' }}
                    </div>
                    @if($payment->payment_type)
                        <div class="payment-meta">
                            <i class="bi bi-credit-card"></i> Method: {{ ucfirst($payment->payment_type) }}
                        </div>
                    @endif

                    <div class="payment-stats">
                        <div class="payment-stat-item">
                            <div class="number">₹{{ number_format($payment->rent_amount, 2) }}</div>
                            <div class="label">Rent</div>
                        </div>
                        <div class="payment-stat-item">
                            <div class="number {{ $payment->balance_amount > 0 ? 'balance-due' : 'balance-clear' }}">
                                ₹{{ number_format($payment->balance_amount, 2) }}
                            </div>
                            <div class="label">Total Due</div>
                        </div>
                        <div class="payment-stat-item">
                            <div class="number">₹{{ number_format($payment->cash_paid_amount + $payment->upi_paid_amount, 2) }}</div>
                            <div class="label">Paid</div>
                        </div>
                    </div>

                    @if($payment->discount_amount > 0)
                        <div class="payment-meta" style="color:#166534;">
                            <i class="bi bi-tag"></i> Discount: ₹{{ number_format($payment->discount_amount, 2) }}
                        </div>
                    @endif

                    @if($payment->fine_amount > 0)
                        <div class="payment-meta" style="color:#dc2626;">
                            <i class="bi bi-exclamation-triangle"></i> Fine: ₹{{ number_format($payment->fine_amount, 2) }}
                        </div>
                    @endif

                    @if($payment->previous_pending_amount > 0)
                        <div class="payment-meta" style="color:#ef4444;">
                            <i class="bi bi-clock-history"></i> Previous Pending: ₹{{ number_format($payment->previous_pending_amount, 2) }}
                        </div>
                    @endif

                    @if($payment->current_balance_amount > 0 && $payment->status != 'PENDING')
                        <div class="payment-meta" style="color:#f59e0b;">
                            <i class="bi bi-calendar"></i> Current Balance: ₹{{ number_format($payment->current_balance_amount, 2) }}
                        </div>
                    @endif

                    @if($payment->remark)
                        <div class="remark-box">
                            <i class="bi bi-chat-left-text" style="color:var(--sanjay-gold);"></i>
                            <span>{{ $payment->remark }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span style="font-size:0.65rem; color:#6b7280;">
                            <i class="bi bi-clock"></i> {{ $payment->payment_date ? date('d M Y', strtotime($payment->payment_date)) : 'N/A' }}
                        </span>
                        <div class="d-flex gap-1">
                            @if($payment->id)
                                <button class="btn-action text-primary" onclick="editPayment({{ $payment->id }})" title="Edit Payment">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-action text-danger" onclick="deletePayment({{ $payment->id }})" title="Delete Payment">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @else
                                <button class="btn-action text-success" onclick="openAddModalForResident({{ $payment->resident_id }})" title="Add Payment">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="bi bi-inbox"></i>
            <h4>No payments found</h4>
            <p style="color: #6b7280;">Try adjusting your filters or create a new payment</p>
        </div>
    @endif
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

{{-- ============================================================ --}}
{{-- PAYMENT MODAL --}}
{{-- ============================================================ --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" novalidate>
                @csrf
                <input type="hidden" id="editId" name="edit_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Hostel <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-building rv-input-icon"></i>
                                <select class="rv-input" id="modal_hostel_id" name="hostel_id" required>
                                    <option value="">Select Hostel</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="invalid-feedback" id="hostel_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-door-open rv-input-icon"></i>
                                <select class="rv-input" id="modal_room_id" name="room_id" required disabled>
                                    <option value="">Select Hostel First</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="room_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Resident <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-person rv-input-icon"></i>
                                <select class="rv-input" id="resident_id" name="resident_id" required disabled>
                                    <option value="">Select Room First</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="resident_id_error"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Month <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-month rv-input-icon"></i>
                                <select class="rv-input" id="month" name="month" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="month_error"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-year rv-input-icon"></i>
                                <select class="rv-input" id="year" name="year" required>
                                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="year_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-date rv-input-icon"></i>
                                <input type="date" class="rv-input" id="payment_date" name="payment_date" required>
                            </div>
                            <div class="invalid-feedback" id="payment_date_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rent Amount</label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" class="rv-input" id="rent_amount" name="rent_amount" step="0.01" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Discount</label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <input type="number" class="rv-input" id="discount_amount" name="discount_amount" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fine</label>
                            <div class="rv-input-box">
                                <i class="bi bi-exclamation-triangle rv-input-icon"></i>
                                <input type="number" class="rv-input" id="fine_amount" name="fine_amount" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <select class="rv-input" id="status" name="status">
                                    <option value="PENDING">Pending</option>
                                    <option value="PARTIAL">Partial</option>
                                    <option value="PAID">Paid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cash Paid <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-cash rv-input-icon"></i>
                                <input type="number" class="rv-input" id="cash_paid_amount" name="cash_paid_amount" step="0.01" value="0" required>
                            </div>
                            <div class="invalid-feedback" id="cash_paid_amount_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">UPI Paid <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-phone rv-input-icon"></i>
                                <input type="number" class="rv-input" id="upi_paid_amount" name="upi_paid_amount" step="0.01" value="0" required>
                            </div>
                            <div class="invalid-feedback" id="upi_paid_amount_error"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Payment Method</label>
                            <div class="rv-input-box">
                                <i class="bi bi-credit-card rv-input-icon"></i>
                                <select class="rv-input" id="payment_type" name="payment_type">
                                    <option value="cash">💵 Cash Only</option>
                                    <option value="upi">📱 UPI Only</option>
                                    <option value="both">💳 Both Cash & UPI</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Transaction ID</label>
                            <div class="rv-input-box">
                                <i class="bi bi-hash rv-input-icon"></i>
                                <input type="text" class="rv-input" id="transaction_id" name="transaction_id" placeholder="Optional">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div id="pendingWarning" style="display:none;"></div>
                            <div id="alreadyPaidWarning" style="display:none;"></div>
                            <div id="remarkPreview" style="display:none; margin-top:8px; padding:8px 12px; background:#eff6ff; border-radius:6px; border-left:3px solid #3b82f6;">
                                <span style="font-size:0.75rem; color:#1e40af;">
                                    <i class="bi bi-info-circle"></i> <span id="remarkPreviewText"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="saveBtn" style="width:auto; padding:0.6rem 1.5rem;">
                        <i class="bi bi-check-circle"></i> <span id="saveBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- BULK PAYMENT MODAL --}}
{{-- ============================================================ --}}
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Payment Creation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkPaymentForm" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Select Residents <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-people rv-input-icon" style="top: 20px;"></i>
                                <select class="rv-input" id="bulk_resident_ids" name="resident_ids[]" multiple required style="min-height:150px; padding-top:0.6rem;">
                                    @foreach($residents as $resident)
                                        <option value="{{ $resident->id }}">
                                            {{ $resident->name }} ({{ $resident->resident_code }}) - {{ $resident->hostel->hostel_name ?? '' }} - Room #{{ $resident->room->room_no ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="invalid-feedback" id="resident_ids_error"></div>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple residents</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Month <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-month rv-input-icon"></i>
                                <select class="rv-input" id="bulk_month" name="month" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="month_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-year rv-input-icon"></i>
                                <select class="rv-input" id="bulk_year" name="year" required>
                                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="year_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Date <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-date rv-input-icon"></i>
                                <input type="date" class="rv-input" id="bulk_payment_date" name="payment_date" required>
                            </div>
                            <div class="invalid-feedback" id="payment_date_error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="bulkSaveBtn" style="width:auto; padding:0.6rem 1.5rem; background:#6b7280;">
                        <i class="bi bi-collection"></i> Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div class="toast-container" id="flashMessageContainer"></div>

@push('scripts')
<script>
$(document).ready(function() {
    var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'), { backdrop: 'static', keyboard: true });
    var bulkModal = new bootstrap.Modal(document.getElementById('bulkPaymentModal'), { backdrop: 'static', keyboard: true });

    $('#payment_date, #bulk_payment_date').val(new Date().toISOString().split('T')[0]);

    $('#addPaymentBtn').on('click', function(e) { e.preventDefault(); openAddModal(); });
    $('#bulkPaymentBtn').on('click', function(e) { e.preventDefault(); openBulkModal(); });

    $('#paymentForm').on('submit', function(e) { e.preventDefault(); submitForm(); });
    $('#bulkPaymentForm').on('submit', function(e) { e.preventDefault(); submitBulkForm(); });

    $('#cash_paid_amount, #upi_paid_amount, #payment_date').on('input change', function() {
        generateRemarkPreview();
    });

    // Cascading selects
    $('#modal_hostel_id').on('change', function() {
        var hostelId = $(this).val();
        var roomSelect = $('#modal_room_id');
        var residentSelect = $('#resident_id');

        roomSelect.empty().append('<option value="">Select Room</option>').prop('disabled', true);
        residentSelect.empty().append('<option value="">Select Room First</option>').prop('disabled', true);
        $('#rent_amount').val('');
        $('#pendingWarning, #alreadyPaidWarning, #remarkPreview').hide();

        if (hostelId) {
            $.ajax({
                url: '/admin/rooms/hostel/' + hostelId + '/rooms',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        roomSelect.prop('disabled', false);
                        $.each(response.data, function(key, room) {
                            roomSelect.append('<option value="' + room.id + '">Room #' + room.room_no + '</option>');
                        });
                    }
                }
            });
        }
    });

    $('#modal_room_id').on('change', function() {
        var roomId = $(this).val();
        var residentSelect = $('#resident_id');
        residentSelect.empty().append('<option value="">Select Resident</option>').prop('disabled', true);
        $('#rent_amount').val('');

        if (roomId) {
            $.ajax({
                url: '/admin/payments/room/' + roomId + '/residents',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        residentSelect.prop('disabled', false);
                        $.each(response.data, function(key, resident) {
                            residentSelect.append('<option value="' + resident.id + '">' + resident.name + ' (' + resident.resident_code + ')</option>');
                        });
                    }
                }
            });
        }
    });

    $('#resident_id, #month, #year').on('change', function() {
        let residentId = $('#resident_id').val();
        let month = $('#month').val();
        let year = $('#year').val();

        if (residentId) {
            $.ajax({
                url: '/admin/payments/resident/' + residentId + '/rent',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#rent_amount').val(response.data.rent_amount);
                    }
                }
            });

            if (month && year) {
                checkAlreadyPaid(residentId, month, year);
                checkPreviousPending(residentId, month, year);
                generateRemarkPreview();
            }
        }
    });

    // ✅ AJAX FILTER - No page refresh
    $('#filterStatus, #filterHostel, #filterMonth, #filterYear').on('change', function() {
        applyFiltersAjax();
    });

    $('#searchPayment').on('keyup', debounce(function() {
        applyFiltersAjax();
    }, 500));
});

// ============================================================
// ✅ AJAX FILTER FUNCTION - NO PAGE REFRESH
// ============================================================

function applyFiltersAjax() {
    var status = $('#filterStatus').val();
    var hostel = $('#filterHostel').val();
    var month = $('#filterMonth').val();
    var year = $('#filterYear').val();
    var search = $('#searchPayment').val();

    // Show loading
    $('#loadingOverlay').addClass('show');

    $.ajax({
        url: '{{ route("admin.payments.filter") }}',
        type: 'POST',
        data: {
            status: status,
            hostel_id: hostel,
            month: month,
            year: year,
            search: search,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // 1. Update payment cards
                renderPaymentCards(response.data);

                // 2. Update statistics
                updateStats(response.stats);

                // 3. Update pending alert
                updatePendingAlert(response.data);

                // 4. Update filter display
                updateFilterDisplay(response.filter_month, response.filter_year, status, hostel, search);

                // 5. Update record count
                $('#recordCount').text(response.data.length);

                // 6. Update current month badge
                $('#currentMonthText').text(response.filter_month + ' ' + response.filter_year);

                // 7. Clear selection
                clearSelection();
            }
        },
        error: function(xhr) {
            showToast('Failed to apply filters', 'error');
        },
        complete: function() {
            $('#loadingOverlay').removeClass('show');
        }
    });
}

// ============================================================
// RENDER PAYMENT CARDS - FIXED WITH PROPER NUMBER FORMATTING
// ============================================================

function renderPaymentCards(data) {
    var grid = $('#paymentsGrid');
    grid.empty();

    if (data.length === 0) {
        grid.html(`
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="bi bi-inbox"></i>
                <h4>No payments found</h4>
                <p style="color: #6b7280;">Try adjusting your filters or create a new payment</p>
            </div>
        `);
        return;
    }

    $.each(data, function(index, payment) {
        var statusColor = payment.status == 'PAID' ? '#22c55e' :
                         (payment.status == 'PARTIAL' ? '#f59e0b' :
                         (payment.status == 'UNPAID' ? '#6b7280' : '#ef4444'));

        // ✅ Parse numbers correctly - handle both string and number
        var rentAmount = parseFloat(payment.rent_amount) || 0;
        var balanceAmount = parseFloat(payment.balance_amount) || 0;
        var totalPaid = parseFloat(payment.total_paid) || 0;
        var discountAmount = parseFloat(payment.discount_amount) || 0;
        var fineAmount = parseFloat(payment.fine_amount) || 0;
        var previousPending = parseFloat(payment.previous_pending_amount) || 0;
        var currentBalance = parseFloat(payment.current_balance_amount) || 0;

        // ✅ Format with 2 decimal places and thousands separator
        var formatCurrency = function(amount) {
            return amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        };

        var card = `
            <div class="payment-card" id="payment-card-${payment.id || 'new-' + index}" data-resident-id="${payment.resident_id}" data-status="${payment.status}">
                <div class="card-checkbox">
                    ${payment.id ? `<input type="checkbox" class="payment-checkbox" value="${payment.id}" onchange="updateBulkActions()">` : ''}
                </div>
                <div class="payment-header" style="background: ${statusColor};">
                    <div>
                        <strong style="font-size:0.9rem;">${payment.resident_name}</strong>
                        <div style="font-size:0.65rem; opacity:0.8;">${payment.resident_code || ''} | Room #${payment.room_no}</div>
                    </div>
                    <span class="payment-status-badge ${payment.status_badge}">
                        <span class="dot"></span> ${payment.status}
                    </span>
                </div>
                <div class="payment-body">
                    <div class="payment-meta">
                        <i class="bi bi-receipt"></i> Receipt: ${payment.receipt_no}
                    </div>
                    <div class="payment-meta">
                        <i class="bi bi-calendar3"></i> ${payment.month_name || ''} ${payment.year}
                    </div>
                    <div class="payment-meta">
                        <i class="bi bi-building"></i> ${payment.hostel_name}
                    </div>
                    ${payment.payment_type ? `<div class="payment-meta"><i class="bi bi-credit-card"></i> Method: ${payment.payment_type.charAt(0).toUpperCase() + payment.payment_type.slice(1)}</div>` : ''}

                    <div class="payment-stats">
                        <div class="payment-stat-item">
                            <div class="number">₹${formatCurrency(rentAmount)}</div>
                            <div class="label">Rent</div>
                        </div>
                        <div class="payment-stat-item">
                            <div class="number ${balanceAmount > 0 ? 'balance-due' : 'balance-clear'}">
                                ₹${formatCurrency(balanceAmount)}
                            </div>
                            <div class="label">Total Due</div>
                        </div>
                        <div class="payment-stat-item">
                            <div class="number">₹${formatCurrency(totalPaid)}</div>
                            <div class="label">Paid</div>
                        </div>
                    </div>

                    ${discountAmount > 0 ? `<div class="payment-meta" style="color:#166534;"><i class="bi bi-tag"></i> Discount: ₹${formatCurrency(discountAmount)}</div>` : ''}
                    ${fineAmount > 0 ? `<div class="payment-meta" style="color:#dc2626;"><i class="bi bi-exclamation-triangle"></i> Fine: ₹${formatCurrency(fineAmount)}</div>` : ''}
                    ${previousPending > 0 ? `<div class="payment-meta" style="color:#ef4444;"><i class="bi bi-clock-history"></i> Previous Pending: ₹${formatCurrency(previousPending)}</div>` : ''}
                    ${currentBalance > 0 && payment.status != 'PENDING' ? `<div class="payment-meta" style="color:#f59e0b;"><i class="bi bi-calendar"></i> Current Balance: ₹${formatCurrency(currentBalance)}</div>` : ''}

                    ${payment.remark ? `<div class="remark-box"><i class="bi bi-chat-left-text" style="color:var(--sanjay-gold);"></i> <span>${payment.remark}</span></div>` : ''}

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span style="font-size:0.65rem; color:#6b7280;">
                            <i class="bi bi-clock"></i> ${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A'}
                        </span>
                        <div class="d-flex gap-1">
                            ${payment.id ? `
                                <button class="btn-action text-primary" onclick="editPayment(${payment.id})" title="Edit Payment">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-action text-danger" onclick="deletePayment(${payment.id})" title="Delete Payment">
                                    <i class="bi bi-trash"></i>
                                </button>
                            ` : `
                                <button class="btn-action text-success" onclick="openAddModalForResident(${payment.resident_id})" title="Add Payment">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            </div>
        `;
        grid.append(card);
    });
}
// ============================================================
// UPDATE STATISTICS
// ============================================================

function updateStats(stats) {
    var formatCurrency = function(amount) {
        return amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    $('#statTotal').text(stats.total || 0);
    $('#statPending').text(stats.pending || 0);
    $('#statPartial').text(stats.partial || 0);
    $('#statPaid').text(stats.paid || 0);
    $('#statUnpaid').text(stats.unpaid || 0);
    $('#statCollected').text('₹' + formatCurrency(stats.total_collected || 0));
}
// ============================================================
// UPDATE PENDING ALERT
// ============================================================

function updatePendingAlert(data) {
    var unpaidCount = 0, pendingCount = 0, partialCount = 0, paidCount = 0;
    var totalBalance = 0, currentBalance = 0, previousPending = 0;

    $.each(data, function(index, item) {
        if (item.status == 'UNPAID') unpaidCount++;
        else if (item.status == 'PENDING') pendingCount++;
        else if (item.status == 'PARTIAL') partialCount++;
        else if (item.status == 'PAID') paidCount++;

        if (['UNPAID', 'PENDING', 'PARTIAL'].includes(item.status)) {
            totalBalance += parseFloat(item.balance_amount) || 0;
            currentBalance += parseFloat(item.current_balance_amount) || 0;
            previousPending += parseFloat(item.previous_pending_amount) || 0;
        }
    });

    var alert = $('#pendingAlert');
    var totalPending = unpaidCount + pendingCount + partialCount;

    // Format currency helper
    var formatCurrency = function(amount) {
        return amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    if (totalPending > 0) {
        alert.show();
        // Update counts
        alert.find('span:contains("Unpaid:")').next('.count').text(unpaidCount);
        alert.find('span:contains("Pending (Previous):")').next('.count').text(pendingCount);
        alert.find('span:contains("Partial:")').next('.count').text(partialCount);
        alert.find('span:contains("Paid:")').next('.count').text(paidCount);

        // Update amounts with 2 decimal places and commas
        alert.find('span:contains("Total Due:")').next('.count').text('₹' + formatCurrency(totalBalance));
        alert.find('span:contains("Current Balance:")').next('.count').text('₹' + formatCurrency(currentBalance));
        alert.find('span:contains("Previous Pending:")').next('.count').text('₹' + formatCurrency(previousPending));
    } else {
        alert.hide();
    }
}

// ============================================================
// UPDATE FILTER DISPLAY
// ============================================================

function updateFilterDisplay(month, year, status, hostel, search) {
    $('#filterMonthDisplay').text(month);
    $('#filterYearDisplay').text(year);

    if (hostel) {
        $('#filterHostelDisplay').text($('#filterHostel option:selected').text());
        $('#filterHostelDisplay').parent().show();
    } else {
        $('#filterHostelDisplay').parent().hide();
    }
    if (status) {
        $('#filterStatusDisplay').text(status);
        $('#filterStatusDisplay').parent().show();
    } else {
        $('#filterStatusDisplay').parent().hide();
    }
    if (search) {
        $('#filterSearchDisplay').text(search);
        $('#filterSearchDisplay').parent().show();
    } else {
        $('#filterSearchDisplay').parent().hide();
    }
}

// ============================================================
// FILTER PENDING
// ============================================================

function filterPending() {
    $('#filterStatus').val('PENDING');
    applyFiltersAjax();
}

// ============================================================
// CHECK FUNCTIONS
// ============================================================

function checkAlreadyPaid(residentId, month, year) {
    var editId = $('#editId').val();

    $.ajax({
        url: '/admin/payments/resident/' + residentId + '/check-paid/' + month + '/' + year,
        type: 'GET',
        success: function(response) {
            if (response.success && response.is_paid) {
                var isEditMode = (editId && editId == response.payment_id);

                if (isEditMode) {
                    $('#alreadyPaidWarning').html(`
                        <div class="mt-2" style="padding:0.75rem 1rem; background:#dbeafe; border:1px solid #93c5fd; border-radius:8px;">
                            <strong style="color:#1e40af;">✏️ Editing Mode</strong>
                            <span style="display:block; font-size:0.8rem; color:#4b5563;">
                                You are editing receipt: ${response.receipt_no} | Amount: ₹${response.amount}
                                <br><small>You can modify the payment details below.</small>
                            </span>
                        </div>
                    `).show();
                    $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">Update</span>');
                    $('#saveBtnText').text('Update');
                } else {
                    $('#alreadyPaidWarning').html(`
                        <div class="mt-2" style="padding:0.75rem 1rem; background:#dcfce7; border:1px solid #86efac; border-radius:8px;">
                            <strong style="color:#166534;">✅ Already Paid!</strong>
                            <span style="display:block; font-size:0.8rem; color:#4b5563;">
                                Receipt: ${response.receipt_no} | Amount: ₹${response.amount}
                                <br><small>This month is already paid. You can edit the existing payment.</small>
                            </span>
                        </div>
                    `).show();
                    $('#saveBtn').prop('disabled', true).html('<i class="bi bi-check-circle"></i> Already Paid');
                }
            } else {
                $('#alreadyPaidWarning').hide();
                $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">Save</span>');
                $('#saveBtnText').text('Save');
            }
        },
        error: function() {
            $('#alreadyPaidWarning').hide();
            $('#saveBtn').prop('disabled', false);
        }
    });
}

function checkPreviousPending(residentId, month, year) {
    $.ajax({
        url: '/admin/payments/resident/' + residentId + '/check-pending/' + month + '/' + year,
        type: 'GET',
        success: function(response) {
            if (response.success && response.has_pending) {
                $('#pendingWarning').html(`
                    <div class="mt-2" style="padding:0.75rem 1rem; background:#eff6ff; border:1px solid #93c5fd; border-radius:8px;">
                        <strong style="color:#1e40af;">⚠️ Previous months have pending payments</strong>
                        <span style="display:block; font-size:0.8rem; color:#1e40af;">
                            Payment will clear previous pending first, then current month.
                        </span>
                    </div>
                `).show();
            } else {
                $('#pendingWarning').hide();
            }
        }
    });
}

// ============================================================
// REMARK PREVIEW
// ============================================================

function generateRemarkPreview() {
    let residentId = $('#resident_id').val();
    let month = $('#month').val();
    let year = $('#year').val();
    let paymentDate = $('#payment_date').val();
    let totalPaid = (parseFloat($('#cash_paid_amount').val()) || 0) + (parseFloat($('#upi_paid_amount').val()) || 0);

    if (!residentId || !month || !year || !paymentDate) {
        $('#remarkPreview').hide();
        return;
    }

    $.ajax({
        url: '/admin/payments/resident/' + residentId + '/payment-details',
        type: 'POST',
        data: {
            month: month,
            year: year,
            payment_date: paymentDate,
            total_paid: totalPaid,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success && response.data) {
                let data = response.data;
                let discountStatus = data.discount_eligible ? '✅ Discount ₹' + data.discount.toFixed(2) : '❌ No discount';
                let remark = discountStatus + ' | Previous: ₹' + data.previous_pending.toFixed(2) +
                           ' | Current: ₹' + data.current_due.toFixed(2) +
                           ' | Paid: ₹' + data.total_paid.toFixed(2);

                $('#remarkPreviewText').text(remark);
                $('#remarkPreview').show();
            }
        }
    });
}

// ============================================================
// EXPORT FUNCTIONS
// ============================================================

function exportWithType(type) {
    var params = getFilterParams();
    var url = '';

    switch(type) {
        case 'filtered':
            url = '{{ route("admin.payments.export.filtered") }}';
            break;
        case 'pdf':
            url = '{{ route("admin.payments.export.pdf") }}';
            break;
        case 'summary':
            url = '{{ route("admin.payments.export.summary") }}';
            break;
        default:
            url = '{{ route("admin.payments.export.filtered") }}';
    }

    window.location.href = url + '?' + params;
}

function exportPaymentStatus(type) {
    var params = getFilterParams();
    var url = type === 'csv'
        ? '{{ route("admin.payments.export.payment-status") }}'
        : '{{ route("admin.payments.export.payment-status-pdf") }}';
    window.location.href = url + '?' + params;
}

function exportUnpaid(type) {
    var params = getFilterParams();
    var url = type === 'csv'
        ? '{{ route("admin.payments.export.unpaid-summary") }}'
        : '{{ route("admin.payments.export.unpaid-pdf") }}';
    window.location.href = url + '?' + params;
}

function exportHostelWise(hostelId) {
    var params = getFilterParams();
    var url = '{{ route("admin.payments.export.hostel-wise") }}?hostel_id=' + hostelId + '&' + params;
    window.location.href = url;
}

function exportPaid() {
    var params = getFilterParams();
    var url = '{{ route("admin.payments.export.paid") }}?' + params;
    window.location.href = url;
}

function getFilterParams() {
    var params = new URLSearchParams();
    var status = $('#filterStatus').val();
    var hostel = $('#filterHostel').val();
    var month = $('#filterMonth').val();
    var year = $('#filterYear').val();
    var search = $('#searchPayment').val();

    if (status) params.append('status', status);
    if (hostel) params.append('hostel_id', hostel);
    if (month) params.append('month', month);
    if (year) params.append('year', year);
    if (search) params.append('search', search);

    return params.toString();
}

// ============================================================
// BULK ACTIONS
// ============================================================

function updateBulkActions() {
    var checked = $('.payment-checkbox:checked');
    if (checked.length > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(checked.length);
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    $('.payment-checkbox').prop('checked', false);
    updateBulkActions();
}

function getSelectedIds() {
    var ids = [];
    $('.payment-checkbox:checked').each(function() { ids.push($(this).val()); });
    return ids;
}

function bulkStatusUpdate() {
    var ids = getSelectedIds();
    var status = $('#bulkStatusSelect').val();
    if (ids.length === 0 || !status) {
        showToast('Select payments and a status', 'error');
        return;
    }

    Swal.fire({
        title: 'Update Status?',
        text: "Update " + ids.length + " payments to " + status + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.payments.bulk-status') }}",
                type: 'POST',
                data: { ids: ids, status: status, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        location.reload();
                    }
                },
                error: function() { showToast('Failed!', 'error'); }
            });
        }
    });
}

function bulkDelete() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;

    Swal.fire({
        title: 'Delete Payments?',
        text: "Delete " + ids.length + " payments? This cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.payments.bulk-delete') }}",
                type: 'POST',
                data: { ids: ids, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        location.reload();
                    }
                },
                error: function() { showToast('Failed!', 'error'); }
            });
        }
    });
}

// ============================================================
// MODAL FUNCTIONS
// ============================================================

function openAddModal() {
    resetForm();
    $('#modalTitle').text('Add Payment');
    $('#saveBtnText').text('Save');
    $('#editId').val('');
    $('#saveBtn').prop('disabled', false);
    $('#saveBtn').html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">Save</span>');
    $('#alreadyPaidWarning').hide();
    $('#pendingWarning').hide();
    $('#remarkPreview').hide();
    $('#paymentModal').modal('show');
}

function openAddModalForResident(residentId) {
    resetForm();
    $('#modalTitle').text('Add Payment');
    $('#saveBtnText').text('Save');
    $('#editId').val('');
    $('#saveBtn').prop('disabled', false);
    $('#saveBtn').html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">Save</span>');
    $('#alreadyPaidWarning').hide();
    $('#pendingWarning').hide();
    $('#remarkPreview').hide();

    if (residentId) {
        $('#resident_id').val(residentId).trigger('change');
    }

    $('#paymentModal').modal('show');
}

function openBulkModal() {
    resetBulkForm();
    $('#bulkPaymentModal').modal('show');
}

function resetForm() {
    $('#paymentForm')[0].reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    $('#pendingWarning, #alreadyPaidWarning, #remarkPreview').hide();
    $('#editId').val('');
    $('#payment_date').val(new Date().toISOString().split('T')[0]);
    $('#modal_hostel_id').val('');
    $('#modal_room_id').empty().append('<option value="">Select Hostel First</option>').prop('disabled', true);
    $('#resident_id').empty().append('<option value="">Select Room First</option>').prop('disabled', true);
    $('#payment_type').val('both');
}

function resetBulkForm() {
    $('#bulkPaymentForm')[0].reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    $('#bulk_payment_date').val(new Date().toISOString().split('T')[0]);
}

// ============================================================
// EDIT PAYMENT FUNCTION
// ============================================================

function editPayment(id) {
    $.ajax({
        url: "{{ url('admin/payments') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                $('#modalTitle').text('Edit Payment');
                $('#editId').val(data.id);
                $('#saveBtnText').text('Update');
                $('#saveBtn').prop('disabled', false);
                $('#saveBtn').html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">Update</span>');

                $('#month').val(data.month);
                $('#year').val(data.year);
                $('#rent_amount').val(data.rent_amount);
                $('#discount_amount').val(data.discount_amount || 0);
                $('#fine_amount').val(data.fine_amount || 0);
                $('#cash_paid_amount').val(data.cash_paid_amount || 0);
                $('#upi_paid_amount').val(data.upi_paid_amount || 0);
                $('#transaction_id').val(data.transaction_id || '');
                $('#status').val(data.status || 'PENDING');
                $('#payment_type').val(data.payment_type || 'both');

                if (data.payment_date) {
                    $('#payment_date').val(data.payment_date.split('T')[0]);
                }

                let residentId = data.resident_id;
                let month = data.month;
                let year = data.year;

                let hostelId = data.resident ? data.resident.hostel_id : null;
                let roomId = data.resident ? data.resident.room_id : null;

                if (hostelId) {
                    $('#modal_hostel_id').val(hostelId);

                    $.ajax({
                        url: '/admin/rooms/hostel/' + hostelId + '/rooms',
                        type: 'GET',
                        success: function(roomResp) {
                            let roomSelect = $('#modal_room_id');
                            roomSelect.empty().append('<option value="">Select Room</option>').prop('disabled', false);

                            if (roomResp.success && roomResp.data.length > 0) {
                                $.each(roomResp.data, function(key, room) {
                                    let selected = (room.id == roomId) ? 'selected' : '';
                                    roomSelect.append('<option value="' + room.id + '" ' + selected + '>Room #' + room.room_no + '</option>');
                                });
                            }

                            if (roomId) {
                                $.ajax({
                                    url: '/admin/payments/room/' + roomId + '/residents',
                                    type: 'GET',
                                    success: function(resResp) {
                                        let residentSelect = $('#resident_id');
                                        residentSelect.empty().append('<option value="">Select Resident</option>').prop('disabled', false);

                                        if (resResp.success && resResp.data.length > 0) {
                                            $.each(resResp.data, function(key, resident) {
                                                let selected = (resident.id == residentId) ? 'selected' : '';
                                                residentSelect.append('<option value="' + resident.id + '" ' + selected + '>' + resident.name + ' (' + resident.resident_code + ')</option>');
                                            });
                                        }

                                        if (residentId && month && year) {
                                            checkAlreadyPaid(residentId, month, year);
                                            checkPreviousPending(residentId, month, year);
                                            generateRemarkPreview();
                                        }
                                    }
                                });
                            }
                        }
                    });
                }

                $('#paymentModal').modal('show');
            }
        },
        error: function() {
            showToast('Failed to load payment data', 'error');
        }
    });
}

// ============================================================
// DELETE PAYMENT
// ============================================================

function deletePayment(id) {
    Swal.fire({
        title: 'Delete?',
        text: "This cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/payments') }}/" + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        location.reload();
                    }
                },
                error: function() { showToast('Failed!', 'error'); }
            });
        }
    });
}

// ============================================================
// FORM SUBMISSIONS
// ============================================================

function submitForm() {
    let id = $('#editId').val();
    let url = "{{ route('admin.payments.store') }}";
    let formData = new FormData(document.getElementById('paymentForm'));

    if (id) {
        url = "{{ url('admin/payments') }}/" + id;
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        beforeSend: function() {
            $('#saveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
        },
        success: function(response) {
            if (response.success) {
                $('#paymentModal').modal('hide');
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $('#' + field).closest('.rv-input-box').addClass('is-invalid');
                    $('#' + field + '_error').text(messages[0]);
                });
                showToast('Please fix errors', 'error');
            } else {
                showToast(xhr.responseJSON?.message || 'Failed!', 'error');
            }
        },
        complete: function() {
            let text = id ? 'Update' : 'Save';
            $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">' + text + '</span>');
        }
    });
}

function submitBulkForm() {
    let formData = new FormData(document.getElementById('bulkPaymentForm'));

    $.ajax({
        url: "{{ route('admin.payments.bulk') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        beforeSend: function() {
            $('#bulkSaveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Creating...');
        },
        success: function(response) {
            if (response.success) {
                $('#bulkPaymentModal').modal('hide');
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    let fieldId = field.includes('resident') ? field : 'bulk_' + field;
                    $('#' + fieldId).closest('.rv-input-box').addClass('is-invalid');
                    $('#' + fieldId + '_error').text(messages[0]);
                });
                showToast('Please fix errors', 'error');
            } else {
                showToast(xhr.responseJSON?.message || 'Failed!', 'error');
            }
        },
        complete: function() {
            $('#bulkSaveBtn').prop('disabled', false).html('<i class="bi bi-collection"></i> Create');
        }
    });
}

// ============================================================
// DEBOUNCE FUNCTION
// ============================================================

function debounce(func, wait) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
}

// ============================================================
// TOAST
// ============================================================

function showToast(message, type = 'success') {
    let container = document.getElementById('flashMessageContainer');
    let icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    let color = type === 'success' ? '#10b981' : '#dc2626';

    let toast = document.createElement('div');
    toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '');
    toast.innerHTML = `
        <i class="bi ${icon}" style="color: ${color}; font-size: 1.25rem;"></i>
        <div class="message">${message}</div>
        <button class="btn-close" style="font-size:0.75rem;" onclick="this.parentElement.remove()"></button>
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
@endpush

@endsection
