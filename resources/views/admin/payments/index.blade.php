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

    .payment-adjustments {
        display: flex;
        gap: 0.5rem;
        font-size: 0.65rem;
        margin-bottom: 0.5rem;
    }
    .payment-adjustments .discount { color: #22c55e; }
    .payment-adjustments .fine { color: #ef4444; }

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
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.pending .dot { background: #ef4444; }
    .status-badge.partial .dot { background: #f59e0b; }
    .status-badge.paid .dot { background: #22c55e; }

    .btn-action { padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #e5e7eb; background: white; font-size: 0.75rem; transition: all 0.2s; }
    .btn-action:hover { background: #f3f4f6; }
    .btn-action.text-danger:hover { background: #fee2e2; border-color: #fca5a5; }
    .btn-action.text-primary:hover { background: #e3f2fd; border-color: #90caf9; }
    .btn-action.text-success:hover { background: #dcfce7; border-color: #86efac; }

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
    select.rv-input[multiple] option { padding: 0.3rem 0.5rem; }

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

    .summary-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 1.1rem;
        margin-bottom: 1.25rem;
    }
    .summary-card h6 {
        margin-bottom: 0.75rem;
        color: var(--sanjay-primary);
        font-size: 0.85rem;
        font-weight: 700;
    }
    .summary-card table { width: 100%; font-size: 0.8rem; }
    .summary-card th {
        text-align: left;
        padding: 0.5rem;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .summary-card td { padding: 0.5rem; border-bottom: 1px solid #f3f4f6; }
    .summary-card tr:last-child td { border-bottom: none; }
    .summary-card .hostel-name { font-weight: 600; color: var(--sanjay-primary); }

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
    .bulk-actions .count { font-weight: 600; color: var(--sanjay-primary); }

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

    .dropdown-menu { border-radius: 12px !important; border: 1px solid #e5e7eb !important; box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
    .dropdown-item { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; border-radius: 6px !important; }
    .dropdown-item:hover { background: #f3f4f6 !important; }
    .dropdown-item i { width: 20px; }
    .dropdown-header { font-size: 0.7rem; color: #6b7280; padding: 0.5rem 1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
    .stat-card .icon { font-size: 1.3rem; margin-bottom: 0.25rem; }
    .stat-card .number { font-size: 1.3rem; font-weight: 700; color: var(--sanjay-primary); }
    .stat-card .label { font-size: 0.65rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.15rem; }

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
    .toast-custom .icon { font-size: 1.25rem; }
    .toast-custom .message { flex: 1; font-size: 0.85rem; color: #1f2937; }
    .toast-custom .close-btn { background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0 0.25rem; }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    .filter-section .form-select-sm,
    .filter-section .form-control-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        background-color: #fafafa;
        width: 100%;
    }
    .filter-section .form-select-sm:focus,
    .filter-section .form-control-sm:focus {
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
        background-color: white;
    }

    .partial-details-container {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 12px;
        padding: 1rem;
        margin: 0.75rem 0;
    }
    .partial-details-container .txn-id-badge {
        background: #f3f4f6;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.75rem;
        margin: 2px;
        display: inline-block;
    }
    .partial-details-container .method-box {
        text-align: center;
        padding: 0.4rem;
        border-radius: 6px;
        font-size: 0.75rem;
    }
    .partial-details-container .method-box.cash { background: #dcfce7; }
    .partial-details-container .method-box.upi { background: #dbeafe; }
    .partial-details-container .method-box.card { background: #f3e8ff; }
    .partial-details-container .method-box.bank { background: #fef3c7; }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Payment Management</h1>
        <p class="ol-page-sub">Manage monthly rent payments and receipts</p>
        @if($user->role != 'admin')
            <p class="ol-page-sub" style="color: var(--sanjay-gold); font-size:0.8rem;">
                <i class="bi bi-info-circle"></i> You have access to {{ $hostels->count() }} hostel(s)
            </p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="rv-submit dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
                <i class="bi bi-download"></i>
                Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="exportDropdown" style="min-width:320px; padding:0.5rem;">
                <li class="dropdown-header">📊 Payment Reports</li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.export.all') }}"><i class="bi bi-file-earmark-text me-2 text-primary"></i> All Payments (CSV)</a></li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.pdf.all') }}"><i class="bi bi-file-pdf me-2 text-danger"></i> All Payments (PDF)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">🏢 Hostel Wise Reports</li>
                @foreach($hostels as $hostel)
                    <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.export.hostel-wise') }}" data-hostel-id="{{ $hostel->id }}" style="font-size:0.8rem; padding:0.3rem 1rem;"><i class="bi bi-building me-2 text-warning"></i> {{ $hostel->hostel_name }} (CSV)</a></li>
                    <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.pdf.hostel-wise') }}" data-hostel-id="{{ $hostel->id }}" style="font-size:0.8rem; padding:0.3rem 1rem;"><i class="bi bi-file-pdf me-2 text-danger"></i> {{ $hostel->hostel_name }} (PDF)</a></li>
                @endforeach
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">📈 Summary</li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.export.summary') }}"><i class="bi bi-bar-chart me-2 text-info"></i> Payment Summary (CSV)</a></li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.pdf.summary') }}"><i class="bi bi-file-pdf me-2 text-danger"></i> Payment Summary (PDF)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">🔴 Unpaid Reports</li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.export.unpaid') }}"><i class="bi bi-exclamation-circle me-2 text-danger"></i> Unpaid (CSV)</a></li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.pdf.unpaid') }}"><i class="bi bi-file-pdf me-2 text-danger"></i> Unpaid (PDF)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">✅ Paid Reports</li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.export.paid') }}"><i class="bi bi-check-circle me-2 text-success"></i> Paid (CSV)</a></li>
                <li><a class="dropdown-item export-link" href="#" data-url="{{ route('admin.payments.pdf.paid') }}"><i class="bi bi-file-pdf me-2 text-danger"></i> Paid (PDF)</a></li>
            </ul>
        </div>
        <button type="button" class="rv-submit" id="bulkPaymentBtn"
            style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-collection"></i>
            Bulk Payment
        </button>
        <button type="button" class="rv-submit" id="addPaymentBtn"
            style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Add Payment
        </button>
    </div>
</div>

{{-- Pending Alert --}}
@if($pendingPayments->count() > 0)
    <div class="pending-alert">
        <div>
            <i class="bi bi-exclamation-triangle-fill" style="color:#991b1b;"></i>
            <span style="font-weight:600; color:#991b1b;">Pending Payments:</span>
            <span class="count">{{ $pendingPayments->count() }}</span> payments pending for {{ date('F Y') }}.
            Total pending: <span class="count">₹{{ number_format($pendingPayments->sum('balance_amount'), 2) }}</span>
        </div>
        <div>
            <button class="btn btn-sm btn-danger" onclick="filterPending()">View Pending</button>
        </div>
    </div>
@endif

{{-- Statistics --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon">📊</div>
        <div class="number">{{ $stats['total'] }}</div>
        <div class="label">Total Transactions</div>
    </div>
    <div class="stat-card">
        <div class="icon">⏳</div>
        <div class="number" style="color:#ef4444;">{{ $stats['pending'] }}</div>
        <div class="label">Pending</div>
    </div>
    <div class="stat-card">
        <div class="icon">🟡</div>
        <div class="number" style="color:#f59e0b;">{{ $stats['partial'] }}</div>
        <div class="label">Partial</div>
    </div>
    <div class="stat-card">
        <div class="icon">✅</div>
        <div class="number" style="color:#22c55e;">{{ $stats['paid'] }}</div>
        <div class="label">Paid</div>
    </div>
    <div class="stat-card">
        <div class="icon">💰</div>
        <div class="number">₹{{ number_format($stats['total_collected'] ?? 0, 0) }}</div>
        <div class="label">Total Collected</div>
    </div>
</div>

{{-- Monthly Summary --}}
@if($monthlySummary->count() > 0)
    <div class="summary-card">
        <h6><i class="bi bi-calendar3"></i> Monthly Summary</h6>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Count</th>
                        <th>Total Rent</th>
                        <th>Collected</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlySummary as $summary)
                        <tr>
                            <td>{{ date('F', mktime(0,0,0,$summary->month,1)) }}</td>
                            <td>{{ $summary->year }}</td>
                            <td>{{ $summary->count }}</td>
                            <td>₹{{ number_format($summary->total_rent, 0) }}</td>
                            <td><strong>₹{{ number_format($summary->total_collected ?? 0, 0) }}</strong></td>
                            <td>₹{{ number_format($summary->total_balance ?? 0, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Hostel Wise Summary --}}
@if($hostelSummary->count() > 0)
    <div class="summary-card">
        <h6><i class="bi bi-building"></i> Hostel Wise Summary</h6>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Hostel</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Pending</th>
                        <th>Partial</th>
                        <th>Total Rent</th>
                        <th>Collected</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hostelSummary as $summary)
                        <tr>
                            <td class="hostel-name">{{ $summary['hostel_name'] }}</td>
                            <td>{{ $summary['total_count'] }}</td>
                            <td><span style="color:#22c55e;">{{ $summary['paid_count'] }}</span></td>
                            <td><span style="color:#ef4444;">{{ $summary['pending_count'] }}</span></td>
                            <td><span style="color:#f59e0b;">{{ $summary['partial_count'] }}</span></td>
                            <td>₹{{ number_format($summary['total_rent'], 0) }}</td>
                            <td><strong>₹{{ number_format($summary['total_collected'], 0) }}</strong></td>
                            <td>₹{{ number_format($summary['total_balance'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

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
    <button class="btn-action text-primary" onclick="bulkStatusUpdate()" title="Update Status"><i class="bi bi-check-circle"></i> Apply</button>
    <button class="btn-action text-danger" onclick="bulkDelete()" title="Delete Selected"><i class="bi bi-trash"></i> Delete</button>
    <button class="btn-action" onclick="clearSelection()" title="Clear Selection"><i class="bi bi-x"></i> Clear</button>
</div>

{{-- Enhanced Filter Section --}}
<div class="filter-section">
    <div class="row g-2 w-100">
        <div class="col-md-3">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchPayment" placeholder="Search by name, receipt, txn ID...">
            </div>
        </div>
        <div class="col-md-2">
            <div class="filter-group">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="PENDING">⏳ Pending</option>
                    <option value="PARTIAL">🟡 Partial</option>
                    <option value="PAID">✅ Paid</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="filter-group">
                <select id="filterHostel" class="form-select form-select-sm">
                    <option value="">All Hostels</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="filter-group">
                <select id="filterRoom" class="form-select form-select-sm">
                    <option value="">All Rooms</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="d-flex gap-2">
                <div class="filter-group" style="flex:1;">
                    <input type="month" id="filterMonthYear" class="form-control form-control-sm"
                           value="{{ date('Y-m') }}">
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()" title="Clear Filters">
                    <i class="bi bi-x-circle"></i>
                </button>
                <button class="btn btn-sm btn-primary" onclick="applyFilters()" title="Apply Filters">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Advanced Filter Section --}}
<div class="filter-section" style="background:#f8fafc; border-color:#d1d5db; margin-top:-0.5rem;">
    <div class="row g-2 w-100 align-items-end">
        <div class="col-md-2">
            <div class="filter-group">
                <label class="filter-label">Payment Date From</label>
                <input type="date" id="filterDateFrom" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-md-2">
            <div class="filter-group">
                <label class="filter-label">Payment Date To</label>
                <input type="date" id="filterDateTo" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-md-2">
            <div class="filter-group">
                <label class="filter-label">Min Amount (₹)</label>
                <input type="number" id="filterMinAmount" class="form-control form-control-sm" placeholder="0" min="0">
            </div>
        </div>
        <div class="col-md-2">
            <div class="filter-group">
                <label class="filter-label">Max Amount (₹)</label>
                <input type="number" id="filterMaxAmount" class="form-control form-control-sm" placeholder="10000" min="0">
            </div>
        </div>
        <div class="col-md-2">
            <div class="filter-group">
                <label class="filter-label">Payment Mode</label>
                <select id="filterPaymentMode" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="cash">Cash Only</option>
                    <option value="upi">UPI Only</option>
                    <option value="both">Both</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="d-flex gap-1 flex-wrap">
                <button class="btn btn-sm btn-outline-secondary quick-filter-btn" onclick="filterThisMonth()">
                    <i class="bi bi-calendar"></i> This Month
                </button>
                <button class="btn btn-sm btn-outline-secondary quick-filter-btn" onclick="filterByAmountRange(0, 5000)">
                    <i class="bi bi-currency-rupee"></i> ≤ ₹5000
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Filter Status --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <span style="font-size:0.75rem; color:#6b7280;">
        <i class="bi bi-funnel"></i>
        <span id="visibleCount">{{ $payments->count() }}</span> of
        <span id="totalCount">{{ $payments->count() }}</span> payments shown
    </span>
</div>

{{-- Payments Grid --}}
<div id="paymentsContainer">
    @if($payments->count() > 0)
        <div class="row g-4" id="paymentsGrid">
            @foreach($payments as $payment)
                <div class="col-xl-4 col-lg-6 payment-card-item"
                    data-id="{{ $payment->id }}"
                    data-status="{{ $payment->status }}"
                    data-hostel="{{ $payment->resident->hostel_id ?? '' }}"
                    data-room="{{ $payment->resident->room_id ?? '' }}"
                    data-room_no="{{ $payment->resident->room->room_no ?? '' }}"
                    data-month="{{ $payment->month }}"
                    data-year="{{ $payment->year }}"
                    data-receipt="{{ strtolower($payment->receipt_no) }}"
                    data-resident="{{ strtolower($payment->resident->name ?? '') }}"
                    data-payment-date="{{ $payment->payment_date->format('Y-m-d') }}"
                    data-rent="{{ $payment->rent_amount }}"
                    data-paid="{{ $payment->cash_paid_amount + $payment->upi_paid_amount }}"
                    data-balance="{{ $payment->balance_amount }}">
                    <div class="payment-card">
                        <div class="card-checkbox">
                            <input type="checkbox" class="payment-checkbox" value="{{ $payment->id }}" onclick="updateBulkActions()">
                        </div>
                        <div class="payment-header">
                            <div style="font-size:0.65rem; opacity:0.7; font-family: monospace;">{{ $payment->receipt_no }}</div>
                            <h5 style="margin: 4px 0 0 0; color: white; font-weight: 700; font-size:1rem;">{{ $payment->resident->name ?? 'N/A' }}</h5>
                            <span class="payment-status-badge {{ strtolower($payment->status) }}">
                                <span class="dot"></span>
                                {{ $payment->status }}
                            </span>
                        </div>
                        <div class="payment-body">
                            <div class="payment-meta">
                                <i class="bi bi-calendar-month"></i>
                                {{ date('F', mktime(0,0,0,$payment->month,1)) }} {{ $payment->year }}
                            </div>
                            <div class="payment-meta">
                                <i class="bi bi-building"></i>
                                {{ $payment->resident->hostel->hostel_name ?? 'N/A' }} — Room #{{ $payment->resident->room->room_no ?? 'N/A' }}
                            </div>
                            <div class="payment-meta">
                                <i class="bi bi-calendar3"></i>
                                Paid on {{ $payment->payment_date->format('d M Y') }}
                            </div>
                            @if($payment->transaction_id)
                                <div class="payment-meta">
                                    <i class="bi bi-hash"></i>
                                    Txn ID: {{ $payment->transaction_id }}
                                </div>
                            @endif

                            @if($payment->discount_amount > 0 || $payment->fine_amount > 0)
                                <div class="payment-adjustments">
                                    @if($payment->discount_amount > 0)
                                        <span class="discount"><i class="bi bi-tag"></i> Discount: -₹{{ number_format($payment->discount_amount, 0) }}</span>
                                    @endif
                                    @if($payment->fine_amount > 0)
                                        <span class="fine"><i class="bi bi-exclamation-triangle"></i> Fine: +₹{{ number_format($payment->fine_amount, 0) }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="payment-stats">
                                <div class="payment-stat-item">
                                    <div class="number">₹{{ number_format($payment->rent_amount, 0) }}</div>
                                    <div class="label">Rent</div>
                                </div>
                                <div class="payment-stat-item">
                                    <div class="number">₹{{ number_format($payment->cash_paid_amount + $payment->upi_paid_amount, 0) }}</div>
                                    <div class="label">Paid</div>
                                </div>
                                <div class="payment-stat-item">
                                    <div class="number {{ $payment->balance_amount > 0 ? 'balance-due' : 'balance-clear' }}">
                                        ₹{{ number_format($payment->balance_amount, 0) }}
                                    </div>
                                    <div class="label">Balance</div>
                                </div>
                            </div>

                            <div style="font-size:0.7rem; color:#6b7280; margin-bottom:0.5rem;">
                                <i class="bi bi-cash" style="color:var(--sanjay-gold);"></i> Cash: ₹{{ number_format($payment->cash_paid_amount, 0) }}
                                &nbsp;·&nbsp;
                                <i class="bi bi-phone" style="color:var(--sanjay-gold);"></i> UPI: ₹{{ number_format($payment->upi_paid_amount, 0) }}
                                @if($payment->card_paid_amount > 0)
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-credit-card" style="color:var(--sanjay-gold);"></i> Card: ₹{{ number_format($payment->card_paid_amount, 0) }}
                                @endif
                                @if($payment->bank_paid_amount > 0)
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-bank" style="color:var(--sanjay-gold);"></i> Bank: ₹{{ number_format($payment->bank_paid_amount, 0) }}
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="status-badge {{ strtolower($payment->status) }}">
                                    <span class="dot"></span>
                                    {{ $payment->status_label ?? $payment->status }}
                                </span>
                                <div class="d-flex gap-1">
                                    @if($payment->status != 'PAID')
                                        <button class="btn-action text-success" onclick="markAsPaid({{ $payment->id }})" title="Mark as Paid">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @endif
                                    <button class="btn-action" style="color:#25D366;" onclick="sendWhatsAppBill({{ $payment->id }})" title="Send Bill via WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                    <button class="btn-action text-primary" onclick="editPayment({{ $payment->id }})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action text-danger" onclick="deletePayment({{ $payment->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div style="font-size:0.65rem; color:#9ca3af; margin-top:0.5rem;">
                                {{ $payment->created_at->format('d M Y h:i A') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="ds-card">
            <div class="empty-state">
                <i class="bi bi-credit-card"></i>
                <h5>No payments found</h5>
                <p class="text-muted">Record monthly rent payments.</p>
                <button type="button" class="rv-submit" onclick="openAddModal()" style="width:auto; display:inline-flex; padding:0 1.5rem; height:38px; border-radius:9px; align-items:center; gap:6px; animation:none;">
                    <i class="bi bi-plus-circle"></i>
                    Add Payment
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                @csrf
                <input type="hidden" id="editId" name="edit_id">
                <input type="hidden" id="partialPaymentId" name="partial_payment_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Hostel <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-building rv-input-icon"></i>
                                <select id="modal_hostel_id" class="rv-input" required>
                                    <option value="">Select Hostel</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="invalid-feedback" id="modal_hostel_id_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Room <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-door-open rv-input-icon"></i>
                                <select id="modal_room_id" class="rv-input" required disabled>
                                    <option value="">Select Hostel First</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="modal_room_id_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Resident <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-person rv-input-icon"></i>
                                <select name="resident_id" id="resident_id" class="rv-input" required disabled>
                                    <option value="">Select Room First</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="resident_id_error"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Month <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-month rv-input-icon"></i>
                                <select name="month" id="month" class="rv-input" required>
                                    <option value="">Select Month</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="month_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Year <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar3 rv-input-icon"></i>
                                <select name="year" id="year" class="rv-input" required>
                                    <option value="">Select Year</option>
                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="year_error"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Rent Amount (₹) <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" name="rent_amount" id="rent_amount" class="rv-input" placeholder="0.00" step="0.01" min="0" required>
                            </div>
                            <div class="invalid-feedback" id="rent_amount_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Discount (₹)</label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <input type="number" name="discount_amount" id="discount_amount" class="rv-input" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div class="invalid-feedback" id="discount_amount_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fine (₹)</label>
                            <div class="rv-input-box">
                                <i class="bi bi-exclamation-triangle rv-input-icon"></i>
                                <input type="number" name="fine_amount" id="fine_amount" class="rv-input" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div class="invalid-feedback" id="fine_amount_error"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cash Paid (₹) <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-cash rv-input-icon"></i>
                                <input type="number" name="cash_paid_amount" id="cash_paid_amount" class="rv-input" placeholder="0.00" step="0.01" min="0" required>
                            </div>
                            <div class="invalid-feedback" id="cash_paid_amount_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">UPI Paid (₹) <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-phone rv-input-icon"></i>
                                <input type="number" name="upi_paid_amount" id="upi_paid_amount" class="rv-input" placeholder="0.00" step="0.01" min="0" required>
                            </div>
                            <div class="invalid-feedback" id="upi_paid_amount_error"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Date <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar3 rv-input-icon"></i>
                                <input type="date" name="payment_date" id="payment_date" class="rv-input" required>
                            </div>
                            <div class="invalid-feedback" id="payment_date_error"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Transaction ID</label>
                            <div class="rv-input-box">
                                <i class="bi bi-hash rv-input-icon"></i>
                                <input type="text" name="transaction_id" id="transaction_id" class="rv-input" placeholder="e.g. UPI Ref / Txn No. (optional)">
                            </div>
                            <div class="invalid-feedback" id="transaction_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-toggle-on rv-input-icon"></i>
                                <select name="status" id="status" class="rv-input" required>
                                    <option value="PENDING">⏳ Pending</option>
                                    <option value="PARTIAL">🟡 Partial</option>
                                    <option value="PAID">✅ Paid</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="status_error"></div>
                        </div>
                    </div>
                    
                    <!-- Partial Payment Details Container -->
                    <div id="partialDetailsContainer"></div>
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

{{-- Bulk Payment Modal --}}
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Payment Creation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Select Residents <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-people rv-input-icon" style="top:16px; transform:none;"></i>
                                <select name="resident_ids[]" id="resident_ids" class="rv-input" multiple style="min-height:150px;" required>
                                    @foreach($residents as $resident)
                                        <option value="{{ $resident->id }}">
                                            {{ $resident->name }} ({{ $resident->resident_code }})
                                            - {{ $resident->hostel->hostel_name ?? 'N/A' }}
                                            - ₹{{ number_format($resident->rent_amount ?? 0, 0) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="font-size:0.7rem; color:#6b7280; margin-top:4px;">Hold Ctrl/Cmd to select multiple residents</div>
                            <div class="invalid-feedback" id="resident_ids_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Month <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar-month rv-input-icon"></i>
                                <select name="month" id="bulk_month" class="rv-input" required>
                                    <option value="">Select Month</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="bulk_month_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Year <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar3 rv-input-icon"></i>
                                <select name="year" id="bulk_year" class="rv-input" required>
                                    <option value="">Select Year</option>
                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="invalid-feedback" id="bulk_year_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Date <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar3 rv-input-icon"></i>
                                <input type="date" name="payment_date" id="bulk_payment_date" class="rv-input" required>
                            </div>
                            <div class="invalid-feedback" id="bulk_payment_date_error"></div>
                        </div>
                        <div class="col-12">
                            <div style="background:#fef3c7; padding:0.75rem; border-radius:8px; font-size:0.8rem; color:#92400e;">
                                <i class="bi bi-info-circle"></i>
                                This will create pending payments for selected residents with their current rent amount.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="bulkSaveBtn" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
                        <i class="bi bi-collection"></i>
                        Create Payments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="flashMessageContainer"></div>

@push('scripts')
<script>
$(document).ready(function() {
    var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'), { backdrop: 'static', keyboard: true });
    var bulkModal = new bootstrap.Modal(document.getElementById('bulkPaymentModal'), { backdrop: 'static', keyboard: true });

    $('#payment_date, #bulk_payment_date').val(new Date().toISOString().split('T')[0]);

    $('#addPaymentBtn').on('click', function(e) { e.preventDefault(); openAddModal(); });
    $('#bulkPaymentBtn').on('click', function(e) { e.preventDefault(); openBulkModal(); });

    $('#paymentModal').on('hidden.bs.modal', function() { resetForm(); });
    $('#bulkPaymentModal').on('hidden.bs.modal', function() { resetBulkForm(); });

    $('#paymentForm').on('submit', function(e) { e.preventDefault(); submitForm(); });
    $('#bulkPaymentForm').on('submit', function(e) { e.preventDefault(); submitBulkForm(); });

    $('#rent_amount, #discount_amount, #fine_amount, #cash_paid_amount, #upi_paid_amount').on('input', function() {
        calculateBalance();
    });

    // Filter event listeners
    $('#filterStatus, #filterHostel, #filterRoom, #filterPaymentMode').on('change', function() {
        applyFilters();
    });

    $('#filterMonthYear, #filterDateFrom, #filterDateTo, #filterMinAmount, #filterMaxAmount').on('change', function() {
        applyFilters();
    });

    $('#searchPayment').on('keyup', function() {
        debouncedApplyFilters();
    });

    $('#searchPayment').on('keypress', function(e) {
        if (e.which === 13) {
            applyFilters();
        }
    });

    // Hostel -> Room filter
    $('#filterHostel').on('change', function() {
        var hostelId = $(this).val();
        var roomSelect = $('#filterRoom');
        roomSelect.empty().append('<option value="">All Rooms</option>');

        if (hostelId) {
            $.ajax({
                url: '/admin/rooms/hostel/' + hostelId + '/rooms',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(key, room) {
                            roomSelect.append('<option value="' + room.id + '">Room #' + room.room_no + '</option>');
                        });
                    }
                }
            });
        }
        applyFilters();
    });

    // Modal cascading
    $('#modal_hostel_id').on('change', function() {
        var hostelId = $(this).val();
        var roomSelect = $('#modal_room_id');
        var residentSelect = $('#resident_id');

        roomSelect.empty().append('<option value="">Select Room</option>').prop('disabled', true);
        residentSelect.empty().append('<option value="">Select Room First</option>').prop('disabled', true);
        $('#rent_amount').val('');
        $('#pendingWarning').remove();
        $('#partialDetailsContainer').empty();
        $('#saveBtn').prop('disabled', false);

        if (!hostelId) {
            roomSelect.empty().append('<option value="">Select Hostel First</option>');
            return;
        }

        $.ajax({
            url: '/admin/rooms/hostel/' + hostelId + '/rooms',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    roomSelect.prop('disabled', false);
                    $.each(response.data, function(key, room) {
                        roomSelect.append('<option value="' + room.id + '">Room #' + room.room_no + '</option>');
                    });
                } else {
                    roomSelect.append('<option value="">No rooms available</option>');
                }
            }
        });
    });

    $('#modal_room_id').on('change', function() {
        var roomId = $(this).val();
        var residentSelect = $('#resident_id');

        residentSelect.empty().append('<option value="">Select Resident</option>').prop('disabled', true);
        $('#rent_amount').val('');
        $('#pendingWarning').remove();
        $('#partialDetailsContainer').empty();
        $('#saveBtn').prop('disabled', false);

        if (!roomId) {
            residentSelect.empty().append('<option value="">Select Room First</option>');
            return;
        }

        $.ajax({
            url: '/admin/payments/room/' + roomId + '/residents',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    residentSelect.prop('disabled', false);
                    $.each(response.data, function(key, resident) {
                        residentSelect.append('<option value="' + resident.id + '">' + resident.name + ' (' + resident.resident_code + ')</option>');
                    });
                } else {
                    residentSelect.append('<option value="">No residents in this room</option>');
                }
            }
        });
    });

    // Resident selection with partial payment check
    $('#resident_id').on('change', function() {
        let residentId = $(this).val();
        if (residentId) {
            // Get rent amount
            $.ajax({
                url: '/admin/payments/resident/' + residentId + '/rent',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#rent_amount').val(response.data.rent_amount);
                        calculateBalance();
                    }
                }
            });

            // Check for partial payment
            let month = $('#month').val();
            let year = $('#year').val();
            if (month && year) {
                checkPartialPayment(residentId, month, year);
                checkPendingPrevious(residentId, month, year);
            }
        }
    });

    // Month/Year change with partial payment check
    $('#month, #year').on('change', function() {
        let residentId = $('#resident_id').val();
        let month = $('#month').val();
        let year = $('#year').val();
        if (residentId && month && year) {
            checkPartialPayment(residentId, month, year);
            checkPendingPrevious(residentId, month, year);
        }
    });

    // Apply initial filters
    applyFilters();
});

// ========== PARTIAL PAYMENT FUNCTIONS ==========

function checkPartialPayment(residentId, month, year) {
    $.ajax({
        url: '/admin/payments/resident/' + residentId + '/partial-details/' + month + '/' + year,
        type: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                showPartialPaymentDetails(response.data);
            } else {
                $('#partialDetailsContainer').empty();
                $('#completionNote').remove();
                $('#saveBtn').prop('disabled', false);
                $('#partialPaymentId').val('');
            }
        },
        error: function() {
            $('#partialDetailsContainer').empty();
            $('#completionNote').remove();
            $('#saveBtn').prop('disabled', false);
            $('#partialPaymentId').val('');
        }
    });
}

function showPartialPaymentDetails(data) {
    $('#partialDetailsContainer').empty();
    $('#completionNote').remove();
    
    let totalPaid = data.total_paid;
    let remaining = data.balance_amount;
    
    // Create transaction IDs display
    let txnDisplay = '';
    if (data.transaction_ids && data.transaction_ids.length > 0) {
        txnDisplay = data.transaction_ids.map((id, index) => {
            return `<span class="txn-id-badge">#${index + 1}: ${id}</span>`;
        }).join(' ');
    } else {
        txnDisplay = '<span style="color: #6b7280; font-style: italic;">N/A</span>';
    }
    
    // Create payment methods breakdown
    let methodsHtml = '';
    let hasCash = data.cash_paid > 0;
    let hasUpi = data.upi_paid > 0;
    let hasCard = data.card_paid > 0;
    let hasBank = data.bank_paid > 0;
    
    if (hasCash || hasUpi || hasCard || hasBank) {
        methodsHtml = `
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
                ${hasCash ? `<div class="method-box cash"><div style="color:#6b7280; font-size:0.6rem;">Cash</div><div style="font-weight:700; color:#166534;">₹${Number(data.cash_paid).toFixed(2)}</div></div>` : ''}
                ${hasUpi ? `<div class="method-box upi"><div style="color:#6b7280; font-size:0.6rem;">UPI</div><div style="font-weight:700; color:#1e40af;">₹${Number(data.upi_paid).toFixed(2)}</div></div>` : ''}
                ${hasCard ? `<div class="method-box card"><div style="color:#6b7280; font-size:0.6rem;">Card</div><div style="font-weight:700; color:#6b21a5;">₹${Number(data.card_paid).toFixed(2)}</div></div>` : ''}
                ${hasBank ? `<div class="method-box bank"><div style="color:#6b7280; font-size:0.6rem;">Bank</div><div style="font-weight:700; color:#92400e;">₹${Number(data.bank_paid).toFixed(2)}</div></div>` : ''}
            </div>
        `;
    }
    
    let html = `
        <div class="partial-details-container" data-payment-id="${data.payment_id}">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b; font-size: 1.2rem;"></i>
                <strong style="color: #92400e;">Partial Payment Already Exists</strong>
                <span class="status-badge partial" style="margin-left: auto;">
                    <span class="dot"></span> PARTIAL
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem; background: white; border-radius: 8px; padding: 0.75rem;">
                <div>
                    <span style="color: #6b7280;">Receipt:</span>
                    <strong>${data.receipt_no}</strong>
                </div>
                <div>
                    <span style="color: #6b7280;">Payment Date:</span>
                    <strong>${data.payment_date}</strong>
                </div>
                <div>
                    <span style="color: #6b7280;">Rent Amount:</span>
                    <strong>₹${Number(data.rent_amount).toFixed(2)}</strong>
                </div>
                <div>
                    <span style="color: #6b7280;">Discount:</span>
                    <strong style="color: #22c55e;">-₹${Number(data.discount_amount).toFixed(2)}</strong>
                </div>
                <div>
                    <span style="color: #6b7280;">Fine:</span>
                    <strong style="color: #ef4444;">+₹${Number(data.fine_amount).toFixed(2)}</strong>
                </div>
                <div>
                    <span style="color: #6b7280;">Transaction IDs:</span>
                    <div style="margin-top: 2px;">${txnDisplay}</div>
                </div>
            </div>
            
            ${methodsHtml}
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-top: 0.75rem;">
                <div style="text-align: center; padding: 0.5rem; background: #dcfce7; border-radius: 8px;">
                    <div style="font-size: 0.6rem; color: #6b7280; text-transform: uppercase;">Total Paid</div>
                    <div style="font-weight: 700; color: #166534;">₹${Number(totalPaid).toFixed(2)}</div>
                </div>
                <div style="text-align: center; padding: 0.5rem; background: #fee2e2; border-radius: 8px;">
                    <div style="font-size: 0.6rem; color: #6b7280; text-transform: uppercase;">Remaining Balance</div>
                    <div style="font-weight: 700; color: #dc2626;">₹${Number(remaining).toFixed(2)}</div>
                </div>
                <div style="text-align: center; padding: 0.5rem; background: #dbeafe; border-radius: 8px;">
                    <div style="font-size: 0.6rem; color: #6b7280; text-transform: uppercase;">Total Amount</div>
                    <div style="font-weight: 700; color: #1e40af;">₹${Number(data.rent_amount - data.discount_amount + data.fine_amount).toFixed(2)}</div>
                </div>
            </div>
            
            <div style="margin-top: 0.75rem; padding: 0.75rem; background: #fff3cd; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <span style="color: #92400e; font-weight: 600;">Remaining Balance:</span>
                        <span style="color: #dc2626; font-weight: 700; font-size: 1.1rem;">₹${Number(remaining).toFixed(2)}</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm" style="background: #f59e0b; color: white; border: none; padding: 0.25rem 1rem; border-radius: 6px; font-weight: 600;" onclick="fillRemainingAmount()">
                            <i class="bi bi-cash"></i> Complete Payment
                        </button>
                    </div>
                </div>
                <div style="font-size: 0.7rem; color: #92400e; margin-top: 0.25rem;">
                    <i class="bi bi-info-circle"></i> Enter the remaining amount below and submit to complete this payment.
                </div>
            </div>
        </div>
    `;
    
    $('#partialDetailsContainer').html(html);
    $('#partialPaymentId').val(data.payment_id);
    
    // Auto-fill remaining balance
    $('#cash_paid_amount').val(remaining);
    $('#upi_paid_amount').val(0);
    $('#status').val('PAID');
    $('#saveBtn').prop('disabled', false);
    
    // Show completion note
    let note = `
        <div id="completionNote" style="font-size: 0.8rem; color: #166534; margin-top: 0.25rem; background: #dcfce7; padding: 0.25rem 0.75rem; border-radius: 6px; display: inline-block;">
            <i class="bi bi-check-circle-fill"></i> 
            This will complete the partial payment. Balance will be ₹0.00
        </div>
    `;
    $('#cash_paid_amount').closest('.col-md-4').after(note);
}

function fillRemainingAmount() {
    let remainingText = $('#partialDetailsContainer .text-danger').text();
    let remaining = parseFloat(remainingText.replace(/[₹,]/g, '')) || 0;
    
    $('#cash_paid_amount').val(remaining);
    $('#upi_paid_amount').val(0);
    $('#status').val('PAID');
    
    $('#cash_paid_amount').closest('.rv-input-box').css('border-color', '#22c55e');
    $('#upi_paid_amount').closest('.rv-input-box').css('border-color', '#22c55e');
    
    $('#cash_paid_amount').focus();
    
    showToast('Remaining amount ₹' + remaining.toFixed(2) + ' set for payment', 'success');
}

// ========== FILTER FUNCTIONS ==========

function applyFilters() {
    var status = $('#filterStatus').val();
    var hostel = $('#filterHostel').val();
    var room = $('#filterRoom').val();
    var search = $('#searchPayment').val().toLowerCase().trim();
    var monthYear = $('#filterMonthYear').val();
    var dateFrom = $('#filterDateFrom').val();
    var dateTo = $('#filterDateTo').val();
    var minAmount = parseFloat($('#filterMinAmount').val()) || 0;
    var maxAmount = parseFloat($('#filterMaxAmount').val()) || Infinity;
    var paymentMode = $('#filterPaymentMode').val();

    var month = null;
    var year = null;
    if (monthYear) {
        var parts = monthYear.split('-');
        year = parseInt(parts[0]);
        month = parseInt(parts[1]);
    }

    var visibleCount = 0;
    var totalCount = 0;

    $('#paymentsGrid .payment-card-item').each(function() {
        var show = true;
        var $item = $(this);

        var paymentStatus = $item.data('status');
        var paymentHostel = $item.data('hostel');
        var paymentRoom = $item.data('room');
        var paymentMonth = $item.data('month');
        var paymentYear = $item.data('year');
        var paymentReceipt = $item.data('receipt') || '';
        var paymentResident = $item.data('resident') || '';
        var paymentDate = $item.data('payment-date');
        var rent = parseFloat($item.data('rent')) || 0;

        // Get payment mode from card
        var text = $item.find('.payment-body').text();
        var cashMatch = text.match(/Cash:\s*₹([\d,]+)/);
        var upiMatch = text.match(/UPI:\s*₹([\d,]+)/);
        var cashAmount = cashMatch ? parseFloat(cashMatch[1].replace(/,/g, '')) || 0 : 0;
        var upiAmount = upiMatch ? parseFloat(upiMatch[1].replace(/,/g, '')) || 0 : 0;

        if (status && paymentStatus !== status) show = false;
        if (hostel && paymentHostel != hostel) show = false;
        if (room && paymentRoom != room) show = false;
        if (month && paymentMonth != month) show = false;
        if (year && paymentYear != year) show = false;

        if (search) {
            var match = paymentReceipt.includes(search) ||
                       paymentResident.includes(search) ||
                       $item.find('.payment-meta:contains("Txn ID:")').text().toLowerCase().includes(search);
            if (!match) show = false;
        }

        if (paymentDate && dateFrom && paymentDate < dateFrom) show = false;
        if (paymentDate && dateTo && paymentDate > dateTo) show = false;
        if (rent < minAmount || rent > maxAmount) show = false;

        if (paymentMode) {
            if (paymentMode === 'cash' && cashAmount === 0) show = false;
            if (paymentMode === 'upi' && upiAmount === 0) show = false;
            if (paymentMode === 'both' && (cashAmount === 0 || upiAmount === 0)) show = false;
        }

        if (show) {
            $item.show();
            visibleCount++;
        } else {
            $item.hide();
        }
        totalCount++;
    });

    $('#visibleCount').text(visibleCount);
    $('#totalCount').text(totalCount);
}

function clearFilters() {
    $('#filterStatus, #filterHostel, #filterRoom, #filterPaymentMode').val('');
    $('#searchPayment').val('');
    $('#filterDateFrom, #filterDateTo').val('');
    $('#filterMinAmount, #filterMaxAmount').val('');
    $('#filterMonthYear').val('{{ date("Y-m") }}');
    applyFilters();
}

function filterPending() {
    $('#filterStatus').val('PENDING');
    $('#filterMonthYear').val('{{ date("Y-m") }}');
    applyFilters();
    $('html, body').animate({ scrollTop: $('#paymentsContainer').offset().top - 100 }, 500);
}

function filterThisMonth() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    $('#filterMonthYear').val(year + '-' + month);
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    applyFilters();
}

function filterByAmountRange(min, max) {
    $('#filterMinAmount').val(min || '');
    $('#filterMaxAmount').val(max || '');
    applyFilters();
}

let filterTimeout;
function debouncedApplyFilters() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() {
        applyFilters();
    }, 300);
}

// ========== BULK ACTIONS ==========

function updateBulkActions() {
    var checked = $('.payment-checkbox:checked');
    var count = checked.length;
    if (count > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(count);
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
        showToast('Please select payments and a status', 'error');
        return;
    }
    Swal.fire({
        title: 'Update Status?',
        text: "Are you sure you want to update " + ids.length + " payments to " + status + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, update them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.payments.bulk-status') }}",
                type: 'POST',
                data: { ids: ids, status: status, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) { showToast(xhr.responseJSON?.message || 'Failed to update!', 'error'); }
            });
        }
    });
}

function bulkDelete() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;
    Swal.fire({
        title: 'Delete Payments?',
        text: "Are you sure you want to delete " + ids.length + " payments? This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.payments.bulk-delete') }}",
                type: 'POST',
                data: { ids: ids, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) { showToast(xhr.responseJSON?.message || 'Failed to delete!', 'error'); }
            });
        }
    });
}

// ========== MODAL FUNCTIONS ==========

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Payment';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('partialPaymentId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    $('#pendingWarning').remove();
    $('#partialDetailsContainer').empty();
    $('#completionNote').remove();
    $('#saveBtn').prop('disabled', false);
    var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

function openBulkModal() {
    resetBulkForm();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('bulkPaymentModal'));
    modal.show();
}

function resetForm() {
    const form = document.getElementById('paymentForm');
    form.reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('partialPaymentId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Payment';
    $('#payment_date').val(new Date().toISOString().split('T')[0]);
    $('#pendingWarning').remove();
    $('#partialDetailsContainer').empty();
    $('#completionNote').remove();
    $('#saveBtn').prop('disabled', false);
    $('#modal_hostel_id').val('');
    $('#modal_room_id').empty().append('<option value="">Select Hostel First</option>').prop('disabled', true);
    $('#resident_id').empty().append('<option value="">Select Room First</option>').prop('disabled', true);
    $('.rv-input-box').css('border-color', '');
}

function resetBulkForm() {
    const form = document.getElementById('bulkPaymentForm');
    form.reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    $('#bulk_payment_date').val(new Date().toISOString().split('T')[0]);
}

function calculateBalance() {
    let rent = parseFloat($('#rent_amount').val()) || 0;
    let discount = parseFloat($('#discount_amount').val()) || 0;
    let fine = parseFloat($('#fine_amount').val()) || 0;
    let cash = parseFloat($('#cash_paid_amount').val()) || 0;
    let upi = parseFloat($('#upi_paid_amount').val()) || 0;
    let total = rent - discount + fine;
    let paid = cash + upi;
    let balance = total - paid;
    if (balance <= 0) {
        $('#status').val('PAID');
    } else if (paid > 0 && balance > 0) {
        $('#status').val('PARTIAL');
    } else {
        $('#status').val('PENDING');
    }
}

function checkPendingPrevious(residentId, month, year) {
    $.ajax({
        url: '/admin/payments/resident/' + residentId + '/check-pending/' + month + '/' + year,
        type: 'GET',
        success: function(response) {
            if (response.success && response.has_pending) {
                $('#pendingWarning').remove();
                let warning = `
                    <div id="pendingWarning" class="alert alert-warning mt-2" style="font-size:0.8rem; padding:0.5rem 0.75rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Warning:</strong> Previous months have pending payments.
                        Please clear them before adding this payment.
                    </div>
                `;
                $('#resident_id').closest('.col-md-4').after(warning);
                $('#saveBtn').prop('disabled', true);
            } else {
                $('#pendingWarning').remove();
                $('#saveBtn').prop('disabled', false);
            }
        }
    });
}

function submitForm() {
    let id = document.getElementById('editId').value;
    let partialId = document.getElementById('partialPaymentId').value;
    let url = "{{ route('admin.payments.store') }}";
    let formData = new FormData(document.getElementById('paymentForm'));
    
    // If completing a partial payment, use update route with partial ID
    if (partialId) {
        url = "{{ url('admin/payments') }}/" + partialId;
        formData.append('_method', 'PUT');
        formData.append('partial_payment_id', partialId);
    } else if (id) {
        url = "{{ url('admin/payments') }}/" + id;
        formData.append('_method', 'PUT');
    }
    
    // Calculate totals for balance check
    let cash = parseFloat($('#cash_paid_amount').val()) || 0;
    let upi = parseFloat($('#upi_paid_amount').val()) || 0;
    let totalPaid = cash + upi;
    let rent = parseFloat($('#rent_amount').val()) || 0;
    let discount = parseFloat($('#discount_amount').val()) || 0;
    let fine = parseFloat($('#fine_amount').val()) || 0;
    let totalAmount = rent - discount + fine;
    let balance = totalAmount - totalPaid;
    
    // If completing partial payment and balance is 0, force status to PAID
    if (partialId && balance <= 0) {
        formData.set('status', 'PAID');
    }
    
    if ($('#pendingWarning').length > 0 && !partialId) {
        showToast('Please clear previous pending payments first!', 'error');
        return;
    }
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function() {
            $('#saveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                if (modal) modal.hide();
                
                let message = partialId ? 'Partial payment completed successfully!' : response.message;
                showToast(message, 'success');
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
            let text = partialId ? 'Complete' : (id ? 'Update' : 'Save');
            $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">' + text + '</span>');
        }
    });
}

function submitBulkForm() {
    let url = "{{ route('admin.payments.bulk') }}";
    let formData = new FormData(document.getElementById('bulkPaymentForm'));
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function() {
            $('#bulkSaveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Creating...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('bulkPaymentModal'));
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
                        let fieldId = field.includes('resident') ? field : 'bulk_' + field;
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
            $('#bulkSaveBtn').prop('disabled', false).html('<i class="bi bi-collection"></i> Create Payments');
        }
    });
}

function editPayment(id) {
    $.ajax({
        url: "{{ url('admin/payments') }}/" + id + "/edit",
        type: 'GET',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Payment';
                document.getElementById('editId').value = data.id;
                document.getElementById('partialPaymentId').value = '';
                document.getElementById('month').value = data.month;
                document.getElementById('year').value = data.year;
                document.getElementById('rent_amount').value = data.rent_amount;
                document.getElementById('discount_amount').value = data.discount_amount || 0;
                document.getElementById('fine_amount').value = data.fine_amount || 0;
                document.getElementById('cash_paid_amount').value = data.cash_paid_amount;
                document.getElementById('upi_paid_amount').value = data.upi_paid_amount;
                document.getElementById('transaction_id').value = data.transaction_id || '';
                if (data.payment_date) {
                    const paymentDate = new Date(data.payment_date);
                    document.getElementById('payment_date').value = paymentDate.toISOString().split('T')[0];
                }
                document.getElementById('status').value = data.status;
                document.getElementById('saveBtnText').textContent = 'Update';
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                $('#pendingWarning').remove();
                $('#partialDetailsContainer').empty();
                $('#completionNote').remove();
                $('#saveBtn').prop('disabled', false);

                let hostelId = data.resident ? data.resident.hostel_id : null;
                let roomId = data.resident ? data.resident.room_id : null;
                let residentId = data.resident_id;

                $('#modal_hostel_id').val(hostelId || '');
                if (hostelId && roomId) {
                    $.ajax({
                        url: '/admin/rooms/hostel/' + hostelId + '/rooms',
                        type: 'GET',
                        success: function(roomResp) {
                            let roomSelect = $('#modal_room_id');
                            roomSelect.empty().append('<option value="">Select Room</option>').prop('disabled', false);
                            if (roomResp.success) {
                                $.each(roomResp.data, function(key, room) {
                                    roomSelect.append('<option value="' + room.id + '">Room #' + room.room_no + '</option>');
                                });
                            }
                            roomSelect.val(roomId);
                            $.ajax({
                                url: '/admin/payments/room/' + roomId + '/residents',
                                type: 'GET',
                                success: function(resResp) {
                                    let residentSelect = $('#resident_id');
                                    residentSelect.empty().append('<option value="">Select Resident</option>').prop('disabled', false);
                                    if (resResp.success) {
                                        $.each(resResp.data, function(key, resident) {
                                            residentSelect.append('<option value="' + resident.id + '">' + resident.name + ' (' + resident.resident_code + ')</option>');
                                        });
                                    }
                                    residentSelect.val(residentId);
                                }
                            });
                        }
                    });
                }
                var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load payment data', 'error');
            }
        }
    });
}

function deletePayment(id) {
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
                url: "{{ url('admin/payments') }}/" + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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

function markAsPaid(id) {
    Swal.fire({
        title: 'Mark as Paid?',
        text: "This will mark the payment as fully paid.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, mark as paid!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/payments') }}/" + id + "/mark-paid",
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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

function sendWhatsAppBill(id) {
    $.ajax({
        url: "{{ url('admin/payments') }}/" + id + "/edit",
        type: 'GET',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                openWhatsAppBill(response.data);
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load payment details', 'error');
            }
        }
    });
}

function openWhatsAppBill(payment) {
    let resident = payment.resident || {};
    let rawPhone = resident.phone || resident.mobile || resident.contact_number || resident.phone_number || resident.whatsapp_number || '';
    let phone = rawPhone.toString().replace(/\D/g, '');
    if (!phone) {
        showToast('No phone number on file for this resident!', 'error');
        return;
    }
    if (phone.length === 10) {
        phone = '91' + phone;
    }
    let message = buildBillMessage(payment, resident);
    let url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(message);
    window.open(url, '_blank');
}

function buildBillMessage(payment, resident) {
    let monthName = new Date(payment.year, payment.month - 1, 1).toLocaleString('default', { month: 'long' });
    let cash = parseFloat(payment.cash_paid_amount || 0);
    let upi = parseFloat(payment.upi_paid_amount || 0);
    let totalPaid = (cash + upi).toFixed(2);
    let hostelName = resident.hostel ? resident.hostel.hostel_name : '';
    let roomNo = resident.room ? resident.room.room_no : 'N/A';
    let paymentDate = payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-IN') : '';

    // Parse transaction IDs
    let txnDisplay = payment.transaction_id || 'N/A';

    let lines = [
        '🏠 *' + (hostelName || 'Hostel') + '*',
        '------------------------------',
        'Receipt No: ' + payment.receipt_no,
        'Resident: ' + (resident.name || ''),
        'Room: #' + roomNo,
        'Month: ' + monthName + ' ' + payment.year,
        '',
        'Rent: ₹' + parseFloat(payment.rent_amount || 0).toFixed(2),
        'Discount: -₹' + parseFloat(payment.discount_amount || 0).toFixed(2),
        'Fine: +₹' + parseFloat(payment.fine_amount || 0).toFixed(2),
        'Cash Paid: ₹' + cash.toFixed(2),
        'UPI Paid: ₹' + upi.toFixed(2),
        'Total Paid: ₹' + totalPaid,
        'Balance Due: ₹' + parseFloat(payment.balance_amount || 0).toFixed(2),
        'Status: ' + payment.status,
        '',
        'Payment Date: ' + paymentDate
    ];

    if (payment.transaction_id) {
        lines.push('Txn ID: ' + txnDisplay);
    }

    lines.push('', 'Thank you for your payment! 🙏');

    return lines.join('\n');
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

// Export with filters
$(document).on('click', '.export-link', function (e) {
    e.preventDefault();
    var $this = $(this);
    var baseUrl = $this.data('url');
    var overrides = {};
    var hostelId = $this.data('hostel-id');
    if (hostelId) {
        overrides.hostel_id = hostelId;
    }
    window.location.href = buildUrlWithFilters(baseUrl, overrides);
});

function getActiveFilters() {
    var monthYear = $('#filterMonthYear').val();
    var month = '', year = '';
    if (monthYear) {
        var parts = monthYear.split('-');
        year = parts[0];
        month = parts[1];
    }
    return {
        status: $('#filterStatus').val() || '',
        hostel_id: $('#filterHostel').val() || '',
        room_id: $('#filterRoom').val() || '',
        month: month,
        year: year,
        date_from: $('#filterDateFrom').val() || '',
        date_to: $('#filterDateTo').val() || '',
        min_amount: $('#filterMinAmount').val() || '',
        max_amount: $('#filterMaxAmount').val() || '',
        payment_mode: $('#filterPaymentMode').val() || '',
        search: $('#searchPayment').val() || ''
    };
}

function buildUrlWithFilters(baseUrl, overrides) {
    var filters = $.extend({}, getActiveFilters(), overrides || {});
    var parts = [];
    $.each(filters, function (k, v) {
        if (v !== '' && v !== null && typeof v !== 'undefined') {
            parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
        }
    });
    if (!parts.length) return baseUrl;
    return baseUrl + (baseUrl.indexOf('?') === -1 ? '?' : '&') + parts.join('&');
}
</script>
@endpush

@endsection