@extends('layouts.office')

@section('title', 'Payments — Sanjay PG Hostel')
@section('page_title', 'Payments')

@push('styles')
<style>
    .pm-page-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;
    }
    .pm-page-title { font-size: 1.25rem; font-weight: 700; color: var(--sanjay-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .pm-page-title i { color: var(--sanjay-gold); }
    .pm-page-subtitle { font-size: 0.8rem; color: #6b7280; margin: 0.25rem 0 0 0; }

    .pm-header-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .pm-btn-export {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.55rem 1.1rem; border-radius: 10px;
        font-size: 0.82rem; font-weight: 600; cursor: pointer;
        transition: all 0.25s; white-space: nowrap; border: none;
    }
    .pm-btn-pdf { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
    .pm-btn-pdf:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(220,38,38,0.35); color: white; }
    .pm-btn-excel { background: linear-gradient(135deg, #16a34a, #15803d); color: white; }
    .pm-btn-excel:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(22,163,74,0.35); color: white; }

    .pm-summary-row {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.85rem; margin-bottom: 1.25rem;
    }
    .pm-summary-card {
        background: white; border-radius: 12px; padding: 0.9rem 1rem;
        border: 1px solid #e5e7eb; position: relative; overflow: hidden;
        transition: all 0.25s;
    }
    .pm-summary-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
    .pm-summary-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .pm-summary-card.red::before    { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .pm-summary-card.green::before  { background: linear-gradient(90deg, #10b981, #059669); }
    .pm-summary-card.blue::before   { background: linear-gradient(90deg, #3b82f6, #2563eb); }
    .pm-summary-card.orange::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .pm-summary-card.gray::before   { background: linear-gradient(90deg, #6b7280, #4b5563); }

    .pm-summary-label { font-size: 0.65rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; margin-bottom: 0.2rem; }
    .pm-summary-value { font-size: 1.3rem; font-weight: 700; color: var(--sanjay-primary); font-family: 'DM Mono', monospace; line-height: 1.1; }
    .pm-summary-value.red    { color: #dc2626; }
    .pm-summary-value.green  { color: #059669; }
    .pm-summary-value.orange { color: #d97706; }
    .pm-summary-value.gray   { color: #4b5563; }

    .pm-toolbar {
        display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
        background: white; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 0.85rem; margin-bottom: 1.25rem;
    }
    .pm-search-box { position: relative; flex: 1; min-width: 180px; max-width: 260px; }
    .pm-search-box input {
        width: 100%; padding: 0.55rem 1rem 0.55rem 2.5rem;
        border: 1px solid #e5e7eb; border-radius: 10px; font-size: 0.82rem;
        background: white; color: #374151;
    }
    .pm-search-box input:focus { outline: none; border-color: var(--sanjay-gold); box-shadow: 0 0 0 3px rgba(197,160,40,0.1); }
    .pm-search-box i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }

    .pm-filter {
        padding: 0.55rem 2rem 0.55rem 0.85rem; border: 1px solid #e5e7eb;
        border-radius: 10px; font-size: 0.82rem; background: white; color: #374151;
        appearance: none; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.75rem center;
    }
    .pm-filter:focus { outline: none; border-color: var(--sanjay-gold); }

    .pm-btn-primary {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white; border: none; border-radius: 10px;
        font-size: 0.82rem; font-weight: 600; cursor: pointer;
        transition: all 0.25s; white-space: nowrap;
    }
    .pm-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(10,30,63,0.25); color: white; }

    .pm-table-wrap { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; }
    .pm-table { width: 100%; font-size: 0.8rem; border-collapse: collapse; }
    .pm-table thead th {
        text-align: left; padding: 0.75rem 0.85rem; color: #6b7280;
        font-weight: 700; font-size: 0.68rem; text-transform: uppercase;
        letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb;
        background: #fafbfc;
    }
    .pm-table tbody td {
        padding: 0.75rem 0.85rem; border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .pm-table tbody tr:last-child td { border-bottom: none; }
    .pm-table tbody tr:hover { background: #fafbfc; }
    .pm-table tbody tr.row-pending { background: linear-gradient(90deg, rgba(239,68,68,0.04), transparent); }
    .pm-table tbody tr.row-unpaid  { background: linear-gradient(90deg, rgba(107,114,128,0.06), transparent); }
    .pm-table tbody tr.row-partial { background: linear-gradient(90deg, rgba(245,158,11,0.05), transparent); }

    .pm-resident-cell { display: flex; align-items: center; gap: 0.6rem; }
    .pm-avatar {
        width: 36px; height: 36px; border-radius: 9px; overflow: hidden; flex-shrink: 0;
        background: linear-gradient(135deg, rgba(197,160,40,0.15), rgba(10,30,63,0.08));
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 700; color: var(--sanjay-primary);
        font-family: 'DM Mono', monospace;
    }
    .pm-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pm-resident-info { min-width: 0; }
    .pm-resident-name { font-weight: 700; color: var(--sanjay-primary); font-size: 0.82rem; }
    .pm-resident-sub  { font-size: 0.68rem; color: #9ca3af; font-family: 'DM Mono', monospace; }

    .pm-location { font-size: 0.72rem; display: flex; flex-direction: column; gap: 2px; }
    .pm-location .row1 { font-weight: 600; color: var(--sanjay-primary); }
    .pm-location .row2 { font-family: 'DM Mono', monospace; font-size: 0.68rem; color: #9ca3af; }

    .pm-amount { font-family: 'DM Mono', monospace; font-weight: 700; font-size: 0.8rem; }
    .pm-amount.paid     { color: #059669; }
    .pm-amount.pending  { color: #dc2626; }
    .pm-amount.prev     { color: #ef4444; }
    .pm-amount.bal-ok   { color: #059669; }

    .pm-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 0.62rem; font-weight: 700; text-transform: uppercase;
        white-space: nowrap;
    }
    .pm-pill.paid    { background: #dcfce7; color: #166534; }
    .pm-pill.partial { background: #fef3c7; color: #92400e; }
    .pm-pill.pending { background: #fee2e2; color: #991b1b; }
    .pm-pill.unpaid  { background: #e5e7eb; color: #4b5563; }
    .pm-pill-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    .pm-action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 8px;
        border: 1px solid #e5e7eb; background: white; color: #6b7280;
        cursor: pointer; transition: all 0.2s; font-size: 0.75rem;
    }
    .pm-action-btn:hover { transform: translateY(-1px); }
    .pm-action-btn.pay:hover    { background: #f0fdf4; border-color: #10b981; color: #059669; }
    .pm-action-btn.edit:hover   { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }

    .pm-empty { text-align: center; padding: 3.5rem 2rem; }
    .pm-empty-icon {
        width: 70px; height: 70px; border-radius: 50%;
        background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(5,150,105,0.05));
        color: #10b981; display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; margin: 0 auto 1rem;
    }
    .pm-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.4rem; font-size: 1rem; }
    .pm-empty p  { color: #6b7280; font-size: 0.82rem; margin: 0; }

    .pm-loading { display: flex; align-items: center; justify-content: center; padding: 3rem; color: #9ca3af; font-size: 0.82rem; gap: 0.5rem; }
    .pm-spinner {
        width: 20px; height: 20px; border: 2px solid rgba(197,160,40,0.2);
        border-top-color: var(--sanjay-gold); border-radius: 50%;
        animation: pm-spin 0.7s linear infinite;
    }
    @keyframes pm-spin { to { transform: rotate(360deg); } }

    .pm-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 64px rgba(0,0,0,0.15); }
    .pm-modal .modal-header { background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b); color: white; border: none; padding: 1.1rem 1.5rem; }
    .pm-modal .modal-title { font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
    .pm-modal .modal-title i { color: var(--sanjay-gold); }
    .pm-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .pm-modal .modal-body { padding: 1.5rem; max-height: 75vh; overflow-y: auto; }
    .pm-modal .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; background: #fafbfc; }

    .pm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; }
    @media (max-width: 576px) { .pm-form-row { grid-template-columns: 1fr; } }

    .pm-form-group { margin-bottom: 0.9rem; }
    .pm-form-label { display: block; font-size: 0.72rem; font-weight: 700; color: #374151; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.3px; }
    .pm-form-label .required { color: #ef4444; margin-left: 2px; }
    .pm-form-control {
        width: 100%; padding: 0.6rem 0.85rem;
        border: 1px solid #e5e7eb; border-radius: 9px;
        font-size: 0.82rem; color: #374151; background: white;
        transition: all 0.2s;
    }
    .pm-form-control:focus { outline: none; border-color: var(--sanjay-gold); box-shadow: 0 0 0 3px rgba(197,160,40,0.1); }
    .pm-form-control:disabled { background: #f3f4f6; cursor: not-allowed; opacity: 0.7; }

    .pm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.6rem 1.25rem; border-radius: 9px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; white-space: nowrap; }
    .pm-btn-gold { background: linear-gradient(135deg, var(--sanjay-gold), #d4af37); color: var(--sanjay-primary); }
    .pm-btn-gold:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(197,160,40,0.35); }
    .pm-btn-gold:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .pm-btn-outline { background: white; color: #6b7280; border: 1px solid #e5e7eb; }
    .pm-btn-outline:hover { background: #f9fafb; color: #374151; }

    .pm-pay-summary { background: linear-gradient(135deg, #fafbfc, #f3f4f6); border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem 1.15rem; margin-bottom: 1rem; }
    .pm-pay-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0; font-size: 0.8rem; }
    .pm-pay-summary-row.total { border-top: 1px dashed #d1d5db; margin-top: 0.5rem; padding-top: 0.6rem; font-weight: 700; }
    .pm-pay-summary-row .label { color: #6b7280; }
    .pm-pay-summary-row .value { font-family: 'DM Mono', monospace; font-weight: 600; color: #374151; }

    .pm-divider {
        display: flex; align-items: center; gap: 0.75rem;
        margin: 1rem 0; color: #9ca3af; font-size: 0.7rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .pm-divider::before, .pm-divider::after {
        content: ''; flex: 1; height: 1px;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
    }

    @media (max-width: 768px) {
        .pm-toolbar { flex-direction: column; align-items: stretch; }
        .pm-search-box { max-width: none; }
        .pm-table-wrap { overflow-x: auto; }
        .pm-table { min-width: 1000px; }
        .pm-header-actions { width: 100%; }
        .pm-header-actions .pm-btn-export { flex: 1; justify-content: center; }
    }
</style>
@endpush

@section('content')

{{-- PAGE HEADER --}}
<div class="pm-page-header">
    <div>
        <h2 class="pm-page-title">
            <i class="bi bi-credit-card-2-front"></i>
            Payment Management
        </h2>
        <p class="pm-page-subtitle">Month-wise tracking with dynamic export</p>
    </div>
    <div class="pm-header-actions">
        <button type="button" class="pm-btn-export pm-btn-pdf" onclick="exportData('pdf')">
            <i class="bi bi-file-pdf"></i> Export PDF
        </button>
        <button type="button" class="pm-btn-export pm-btn-excel" onclick="exportData('csv')">
            <i class="bi bi-file-excel"></i> Export Excel
        </button>
        <button type="button" class="pm-btn-primary" onclick="openManualPaymentModal()" style="padding: 0.55rem 1.1rem;">
            <i class="bi bi-plus-lg"></i> Manual Payment
        </button>
    </div>
</div>

{{-- SUMMARY CARDS --}}
<div class="pm-summary-row">
    <div class="pm-summary-card green">
        <div class="pm-summary-label">✅ Fully Paid</div>
        <div class="pm-summary-value green" id="summaryPaidCount">0</div>
        <div style="font-size:0.7rem; color:#059669; font-family:'DM Mono',monospace; margin-top:2px;">
            <span id="summaryPaid">₹0</span> collected
        </div>
    </div>
    <div class="pm-summary-card orange">
        <div class="pm-summary-label">🟡 Partial</div>
        <div class="pm-summary-value orange" id="summaryPartialCount">0</div>
        <div style="font-size:0.7rem; color:#d97706; font-family:'DM Mono',monospace; margin-top:2px;">
            <span id="summaryPartial">₹0</span> balance
        </div>
    </div>
    <div class="pm-summary-card gray">
        <div class="pm-summary-label">⬜ Unpaid This Month</div>
        <div class="pm-summary-value gray" id="summaryUnpaidCount">0</div>
        <div style="font-size:0.7rem; color:#4b5563; font-family:'DM Mono',monospace; margin-top:2px;">
            <span id="summaryUnpaid">₹0</span> pending
        </div>
    </div>
    <div class="pm-summary-card red">
        <div class="pm-summary-label">🔴 Pending (Previous)</div>
        <div class="pm-summary-value red" id="summaryPendingCount">0</div>
        <div style="font-size:0.7rem; color:#dc2626; font-family:'DM Mono',monospace; margin-top:2px;">
            <span id="summaryPending">₹0</span> prev due
        </div>
    </div>
    <div class="pm-summary-card blue">
        <div class="pm-summary-label">📊 Total Residents</div>
        <div class="pm-summary-value" id="summaryTotal">0</div>
        <div style="font-size:0.7rem; color:#6b7280; font-family:'DM Mono',monospace; margin-top:2px;">
            <span id="summaryBalance">₹0</span> total balance
        </div>
    </div>
</div>

{{-- TOOLBAR --}}
<div class="pm-toolbar">
    <div class="pm-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="pmSearch" placeholder="Search name, phone, code...">
    </div>

    <select class="pm-filter" id="pmHostelFilter">
        <option value="">All Hostels</option>
        @foreach($hostels as $h)
            <option value="{{ $h->id }}">{{ $h->hostel_name }}</option>
        @endforeach
    </select>

    <input type="text" class="pm-filter" id="pmRoomFilter" placeholder="Room No" style="max-width:100px; padding: 0.55rem 0.85rem;">
    <input type="text" class="pm-filter" id="pmBedFilter" placeholder="Bed No" style="max-width:90px; padding: 0.55rem 0.85rem;">

    <select class="pm-filter" id="pmMonthFilter">
        @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
            </option>
        @endfor
    </select>
    <select class="pm-filter" id="pmYearFilter" style="max-width:110px;">
        @for($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>

    <select class="pm-filter" id="pmStatusFilter">
        <option value="">All Status</option>
        <option value="PENDING">🔴 Pending (Previous)</option>
        <option value="UNPAID">⬜ Unpaid</option>
        <option value="PARTIAL">🟡 Partial</option>
        <option value="PAID">✅ Paid</option>
    </select>

    <button type="button" class="pm-btn-primary" onclick="loadPayments()" style="padding: 0.55rem 1rem;">
        <i class="bi bi-arrow-clockwise"></i>
    </button>

    <span style="margin-left:auto; font-size:0.75rem; color:#9ca3af;">
        <span id="pmMonthLabel">{{ now()->format('F Y') }}</span> •
        <span id="pmCountLabel">0 records</span>
    </span>
</div>

{{-- TABLE --}}
<div class="pm-table-wrap">
    <div id="pmTableContent">
        <div class="pm-loading">
            <div class="pm-spinner"></div>
            Loading...
        </div>
    </div>
</div>

{{-- PAYMENT MODAL --}}
<div class="modal fade pm-modal" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalTitle">
                    <i class="bi bi-cash-coin"></i>
                    Record Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="paymentForm" autocomplete="off">
                @csrf
                <input type="hidden" id="payResidentId" name="resident_id">
                <input type="hidden" id="payPaymentId" name="payment_id">
                <input type="hidden" id="payEditMode" name="edit_mode" value="0">

                <div class="modal-body">

                    {{-- ✅ Manual Selector (hidden in row-pay mode) --}}
                    <div id="manualResidentSelector" style="display:none;">
                        <div class="pm-form-row">
                            <div class="pm-form-group">
                                <label class="pm-form-label">Hostel <span class="required">*</span></label>
                                <select class="pm-form-control" id="manualHostelId">
                                    <option value="">Select Hostel</option>
                                    @foreach($hostels as $h)
                                        <option value="{{ $h->id }}">{{ $h->hostel_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pm-form-group">
                                <label class="pm-form-label">Room <span class="required">*</span></label>
                                <select class="pm-form-control" id="manualRoomId" disabled>
                                    <option value="">Select Hostel First</option>
                                </select>
                            </div>
                        </div>

                        <div class="pm-form-group">
                            <label class="pm-form-label">Resident <span class="required">*</span></label>
                            <select class="pm-form-control" id="manualResidentDropdown" disabled>
                                <option value="">Select Room First</option>
                            </select>
                        </div>

                        <div class="pm-divider">Payment Details</div>
                    </div>

                    {{-- Summary --}}
                    <div class="pm-pay-summary" id="manualSummaryBox" style="display:none;">
                        <div class="pm-pay-summary-row">
                            <span class="label">Resident</span>
                            <span class="value" id="payResidentName">—</span>
                        </div>
                        <div class="pm-pay-summary-row">
                            <span class="label">Location</span>
                            <span class="value" id="payResidentLocation">—</span>
                        </div>
                        <div class="pm-pay-summary-row">
                            <span class="label">Month</span>
                            <span class="value" id="payMonthLabel">—</span>
                        </div>
                        <div class="pm-pay-summary-row">
                            <span class="label">Required Rent</span>
                            <span class="value" id="payRequiredRent">₹0</span>
                        </div>
                        <div class="pm-pay-summary-row">
                            <span class="label">Previous Pending</span>
                            <span class="value" style="color:#ef4444;" id="payPreviousPending">₹0</span>
                        </div>
                        <div class="pm-pay-summary-row total">
                            <span class="label" style="color:#dc2626;">Total Due</span>
                            <span class="value" id="payTotalDue" style="color:#dc2626;">₹0</span>
                        </div>
                    </div>

                    <div class="pm-form-row">
                        <div class="pm-form-group">
                            <label class="pm-form-label">Month <span class="required">*</span></label>
                            <select class="pm-form-control" id="payMonth" name="month" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="pm-form-group">
                            <label class="pm-form-label">Year <span class="required">*</span></label>
                            <select class="pm-form-control" id="payYear" name="year" required>
                                @for($y = now()->year; $y >= now()->year - 3; $y--)
                                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="pm-form-row">
                        <div class="pm-form-group">
                            <label class="pm-form-label">Rent Amount <span class="required">*</span></label>
                            <input type="number" step="0.01" class="pm-form-control" id="payRent" name="rent_amount" min="0" required>
                        </div>
                        <div class="pm-form-group">
                            <label class="pm-form-label">Discount</label>
                            <input type="number" step="0.01" class="pm-form-control" id="payDiscount" name="discount_amount" min="0" value="0">
                        </div>
                    </div>

                    <div class="pm-form-row">
                        <div class="pm-form-group">
                            <label class="pm-form-label">Fine</label>
                            <input type="number" step="0.01" class="pm-form-control" id="payFine" name="fine_amount" min="0" value="0">
                        </div>
                        <div class="pm-form-group">
                            <label class="pm-form-label">Payment Date <span class="required">*</span></label>
                            <input type="date" class="pm-form-control" id="payDate" name="payment_date" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="pm-form-row">
                        <div class="pm-form-group">
                            <label class="pm-form-label">Cash Paid</label>
                            <input type="number" step="0.01" class="pm-form-control" id="payCash" name="cash_paid_amount" min="0" value="0">
                        </div>
                        <div class="pm-form-group">
                            <label class="pm-form-label">UPI Paid</label>
                            <input type="number" step="0.01" class="pm-form-control" id="payUpi" name="upi_paid_amount" min="0" value="0">
                        </div>
                    </div>

                    <div class="pm-form-group">
                        <label class="pm-form-label">Transaction ID</label>
                        <input type="text" class="pm-form-control" id="payTransactionId" name="transaction_id" placeholder="Optional">
                    </div>

                    <div class="pm-form-group">
                        <label class="pm-form-label">Remark</label>
                        <textarea class="pm-form-control" id="payRemark" name="remark" rows="2" placeholder="Any notes..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="pm-btn pm-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button type="submit" class="pm-btn pm-btn-gold" id="paySubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="paySubmitText">Save Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL   = "{{ url('admin/payments') }}";
let searchTimeout = null;

function showToast(message, type = 'success') {
    if (typeof showFlashMessage === 'function') showFlashMessage(message, type);
    else alert(message);
}

// ─────────────────────────────────────────
// FILTERS
// ─────────────────────────────────────────
function getFilters() {
    return {
        month:     document.getElementById('pmMonthFilter').value,
        year:      document.getElementById('pmYearFilter').value,
        hostel_id: document.getElementById('pmHostelFilter').value,
        room_no:   document.getElementById('pmRoomFilter').value.trim(),
        bed_no:    document.getElementById('pmBedFilter').value.trim(),
        status:    document.getElementById('pmStatusFilter').value,
        search:    document.getElementById('pmSearch').value.trim(),
    };
}

function exportData(type) {
    const params = new URLSearchParams(getFilters());
    const url = type === 'csv'
        ? `${BASE_URL}/export/csv?${params.toString()}`
        : `${BASE_URL}/export/pdf?${params.toString()}`;
    window.open(url, '_blank');
}

// ─────────────────────────────────────────
// LOAD PAYMENTS
// ─────────────────────────────────────────
async function loadPayments() {
    const container = document.getElementById('pmTableContent');
    container.innerHTML = `<div class="pm-loading"><div class="pm-spinner"></div>Loading...</div>`;

    try {
        const response = await fetch(`${BASE_URL}/filter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(getFilters())
        });

        const data = await response.json();

        if (!data.success) {
            showToast('Failed to load payments', 'error');
            return;
        }

        // Summary counts
        document.getElementById('summaryPaidCount').textContent    = data.stats.paid || 0;
        document.getElementById('summaryPartialCount').textContent = data.stats.partial || 0;
        document.getElementById('summaryUnpaidCount').textContent  = data.stats.unpaid || 0;
        document.getElementById('summaryPendingCount').textContent = data.stats.pending || 0;
        document.getElementById('summaryTotal').textContent        = data.stats.total || 0;

        // Summary amounts
        document.getElementById('summaryPaid').textContent    = '₹' + formatNumber(data.stats.paid_amount || 0);
        document.getElementById('summaryPartial').textContent = '₹' + formatNumber(data.stats.partial_amount || 0);
        document.getElementById('summaryUnpaid').textContent  = '₹' + formatNumber(data.stats.unpaid_amount || 0);
        document.getElementById('summaryPending').textContent = '₹' + formatNumber(data.stats.pending_amount || 0);
        document.getElementById('summaryBalance').textContent = '₹' + formatNumber(data.stats.total_balance || 0);

        document.getElementById('pmMonthLabel').textContent = data.month_label;
        document.getElementById('pmCountLabel').textContent = data.rows.length + ' records';

        renderTable(data.rows);

    } catch (err) {
        console.error(err);
        container.innerHTML = `
            <div class="pm-empty">
                <div class="pm-empty-icon"><i class="bi bi-x-circle"></i></div>
                <h5>Failed to Load</h5>
                <p>Please try again.</p>
            </div>`;
    }
}

// ─────────────────────────────────────────
// RENDER TABLE
// ─────────────────────────────────────────
function renderTable(rows) {
    const container = document.getElementById('pmTableContent');

    if (!rows || rows.length === 0) {
        container.innerHTML = `
            <div class="pm-empty">
                <div class="pm-empty-icon"><i class="bi bi-check-circle-fill"></i></div>
                <h5>No Records</h5>
                <p>No residents found matching your filters.</p>
            </div>`;
        return;
    }

    let html = `
        <table class="pm-table">
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Location</th>
                    <th>Rent</th>
                    <th>Paid</th>
                    <th>Prev. Pending</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>`;

    rows.forEach(r => {
        const initials = r.resident_name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        const avatarHtml = r.profile_image
            ? `<img src="{{ asset('uploads/residents/profile') }}/${r.profile_image}" alt="" onerror="this.style.display='none';this.parentNode.textContent='${initials}';">`
            : initials;

        const rowClass = r.status === 'PENDING' ? 'row-pending'
                       : r.status === 'UNPAID' ? 'row-unpaid'
                       : r.status === 'PARTIAL' ? 'row-partial' : '';

        const prevPendingDisplay = r.previous_pending > 0
            ? `<span class="pm-amount prev">₹${formatNumber(r.previous_pending)}</span>`
            : '<span style="color:#d1d5db;">—</span>';

        const balanceClass = r.total_due > 0 ? 'pending' : 'bal-ok';

        const rowData = {
            resident_id: r.resident_id,
            resident_name: r.resident_name,
            hostel_name: r.hostel_name,
            room_no: r.room_no,
            bed_no: r.bed_no,
            month: r.month,
            year: r.year,
            month_label: r.month_label,
            rent_amount: r.rent_amount,
            previous_pending: r.previous_pending,
            total_due: r.total_due,
            payment_id: r.payment_id,
        };

        html += `
            <tr class="${rowClass}">
                <td>
                    <div class="pm-resident-cell">
                        <div class="pm-avatar">${avatarHtml}</div>
                        <div class="pm-resident-info">
                            <div class="pm-resident-name">${escapeHtml(r.resident_name)}</div>
                            <div class="pm-resident-sub">${escapeHtml(r.resident_code)} • ${escapeHtml(r.phone)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="pm-location">
                        <span class="row1">${escapeHtml(r.hostel_name)}</span>
                        <span class="row2">Room ${escapeHtml(r.room_no)} • Bed ${escapeHtml(r.bed_no)}</span>
                    </div>
                </td>
                <td><span class="pm-amount">₹${formatNumber(r.rent_amount)}</span></td>
                <td><span class="pm-amount paid">₹${formatNumber(r.current_paid)}</span></td>
                <td>${prevPendingDisplay}</td>
                <td><span class="pm-amount ${balanceClass}">₹${formatNumber(r.total_due)}</span></td>
                <td>
                    <span class="pm-pill ${r.status.toLowerCase()}">
                        <span class="pm-pill-dot"></span>
                        ${r.status === 'PENDING' ? 'Pending' : r.status === 'UNPAID' ? 'Unpaid' : r.status === 'PARTIAL' ? 'Partial' : 'Paid'}
                    </span>
                </td>
                <td style="text-align:right;">
                    <div style="display:inline-flex; gap:0.3rem;">
                        ${r.payment_id
                            ? `<button type="button" class="pm-action-btn edit" title="Edit" onclick='openEditPayment(${JSON.stringify(rowData)})'>
                                   <i class="bi bi-pencil"></i>
                               </button>`
                            : `<button type="button" class="pm-action-btn pay" title="Pay" onclick='openPayModal(${JSON.stringify(rowData)})'>
                                   <i class="bi bi-cash"></i>
                               </button>`
                        }
                    </div>
                </td>
            </tr>`;
    });

    html += `</tbody></table>`;
    container.innerHTML = html;
}

// ─────────────────────────────────────────
// OPEN PAY MODAL (from row — no manual selector)
// ─────────────────────────────────────────
function openPayModal(row) {
    document.getElementById('manualResidentSelector').style.display = 'none';
    document.getElementById('manualSummaryBox').style.display = 'block';

    document.getElementById('paymentModalTitle').innerHTML = '<i class="bi bi-cash-coin"></i> Record Payment';
    document.getElementById('paySubmitText').textContent = 'Save Payment';
    document.getElementById('payEditMode').value = '0';
    document.getElementById('payPaymentId').value = '';

    document.getElementById('payResidentId').value = row.resident_id;
    document.getElementById('payResidentName').textContent     = row.resident_name;
    document.getElementById('payResidentLocation').textContent = `${row.hostel_name} — Room ${row.room_no} • Bed ${row.bed_no}`;
    document.getElementById('payMonthLabel').textContent = row.month_label;
    document.getElementById('payRequiredRent').textContent   = '₹' + formatNumber(row.rent_amount);
    document.getElementById('payPreviousPending').textContent = '₹' + formatNumber(row.previous_pending);
    document.getElementById('payTotalDue').textContent  = '₹' + formatNumber(row.total_due);

    document.getElementById('payMonth').value = row.month;
    document.getElementById('payYear').value  = row.year;
    document.getElementById('payRent').value  = row.rent_amount;
    document.getElementById('payDiscount').value = 0;
    document.getElementById('payFine').value     = 0;
    document.getElementById('payCash').value     = 0;
    document.getElementById('payUpi').value      = row.rent_amount;
    document.getElementById('payDate').value     = new Date().toISOString().slice(0, 10);
    document.getElementById('payTransactionId').value = '';
    document.getElementById('payRemark').value = '';

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

// ─────────────────────────────────────────
// OPEN EDIT PAYMENT
// ─────────────────────────────────────────
async function openEditPayment(row) {
    if (!row.payment_id) return;

    document.getElementById('manualResidentSelector').style.display = 'none';
    document.getElementById('manualSummaryBox').style.display = 'block';

    try {
        const response = await fetch(`${BASE_URL}/${row.payment_id}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
        });
        const data = await response.json();

        if (!data.success) {
            showToast('Failed to load payment', 'error');
            return;
        }

        const p = data.payment;

        document.getElementById('paymentModalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Edit Payment';
        document.getElementById('paySubmitText').textContent = 'Update Payment';
        document.getElementById('payEditMode').value = '1';
        document.getElementById('payPaymentId').value = p.id;

        document.getElementById('payResidentId').value = p.resident_id;
        document.getElementById('payResidentName').textContent     = row.resident_name;
        document.getElementById('payResidentLocation').textContent = `${row.hostel_name} — Room ${row.room_no} • Bed ${row.bed_no}`;
        document.getElementById('payMonthLabel').textContent = row.month_label;
        document.getElementById('payRequiredRent').textContent   = '₹' + formatNumber(p.rent_amount);
        document.getElementById('payPreviousPending').textContent = '₹' + formatNumber(row.previous_pending);
        document.getElementById('payTotalDue').textContent  = '₹' + formatNumber(row.total_due);

        document.getElementById('payMonth').value = p.month;
        document.getElementById('payYear').value  = p.year;
        document.getElementById('payRent').value  = p.rent_amount;
        document.getElementById('payDiscount').value = p.discount_amount || 0;
        document.getElementById('payFine').value     = p.fine_amount || 0;
        document.getElementById('payCash').value     = p.cash_paid_amount || 0;
        document.getElementById('payUpi').value      = p.upi_paid_amount || 0;
        document.getElementById('payDate').value     = p.payment_date ? p.payment_date.substring(0, 10) : new Date().toISOString().slice(0, 10);
        document.getElementById('payTransactionId').value = p.transaction_id || '';
        document.getElementById('payRemark').value = p.remark || '';

        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    } catch (err) {
        console.error(err);
        showToast('Failed to load payment', 'error');
    }
}

// ─────────────────────────────────────────
// SUBMIT PAYMENT
// ─────────────────────────────────────────
document.getElementById('paymentForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const submitBtn = document.getElementById('paySubmitBtn');
    const submitText = document.getElementById('paySubmitText');
    const originalText = submitText.textContent;
    const paymentId = document.getElementById('payPaymentId').value;
    const editMode = document.getElementById('payEditMode').value === '1';
    const residentId = document.getElementById('payResidentId').value;

    if (!residentId) {
        showToast('Please select a resident', 'error');
        return;
    }

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="pm-spinner" style="width:14px;height:14px;border-width:1.5px;border-top-color:white;"></span> Saving...';

    const formData = new FormData(this);
    const url = editMode ? `${BASE_URL}/${paymentId}` : BASE_URL;
    if (editMode) formData.append('_method', 'PUT');

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            setTimeout(() => loadPayments(), 500);
        } else if (data.errors) {
            showToast(Object.values(data.errors)[0][0] || 'Please check the form', 'error');
        } else {
            showToast(data.message || 'Failed to save', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Network error', 'error');
    } finally {
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
});

// ─────────────────────────────────────────
// MANUAL PAYMENT MODAL
// ─────────────────────────────────────────
function openManualPaymentModal() {
    document.getElementById('paymentForm').reset();
    document.getElementById('paymentModalTitle').innerHTML = '<i class="bi bi-plus-lg"></i> Manual Payment';
    document.getElementById('paySubmitText').textContent = 'Save Payment';
    document.getElementById('payEditMode').value = '0';
    document.getElementById('payPaymentId').value = '';
    document.getElementById('payResidentId').value = '';
    document.getElementById('payDate').value = new Date().toISOString().slice(0, 10);
    document.getElementById('payMonth').value = document.getElementById('pmMonthFilter').value;
    document.getElementById('payYear').value = document.getElementById('pmYearFilter').value;

    // Show the manual selector
    document.getElementById('manualResidentSelector').style.display = 'block';
    document.getElementById('manualSummaryBox').style.display = 'none';

    // Reset dropdowns
    document.getElementById('manualHostelId').value = '';
    document.getElementById('manualRoomId').innerHTML = '<option value="">Select Hostel First</option>';
    document.getElementById('manualRoomId').disabled = true;
    document.getElementById('manualResidentDropdown').innerHTML = '<option value="">Select Room First</option>';
    document.getElementById('manualResidentDropdown').disabled = true;

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

// ─────────────────────────────────────────
// MANUAL PAYMENT — CASCADE SELECTS
// ─────────────────────────────────────────

// Hostel → Rooms
document.getElementById('manualHostelId')?.addEventListener('change', async function () {
    const hostelId = this.value;
    const roomSelect = document.getElementById('manualRoomId');
    const residentSelect = document.getElementById('manualResidentDropdown');
    const summaryBox = document.getElementById('manualSummaryBox');

    roomSelect.innerHTML = '<option value="">Loading...</option>';
    roomSelect.disabled = true;
    residentSelect.innerHTML = '<option value="">Select Room First</option>';
    residentSelect.disabled = true;
    summaryBox.style.display = 'none';

    if (!hostelId) {
        roomSelect.innerHTML = '<option value="">Select Hostel First</option>';
        return;
    }

    try {
        const response = await fetch(`{{ url('admin/payments/rooms') }}/${hostelId}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
        });
        const data = await response.json();

        if (data.success && data.rooms.length > 0) {
            let html = '<option value="">Select Room</option>';
            data.rooms.forEach(r => {
                html += `<option value="${r.id}">Room ${escapeHtml(r.room_no)}</option>`;
            });
            roomSelect.innerHTML = html;
            roomSelect.disabled = false;
        } else {
            roomSelect.innerHTML = '<option value="">No rooms found</option>';
        }
    } catch (err) {
        console.error(err);
        roomSelect.innerHTML = '<option value="">Failed to load</option>';
    }
});

// Room → Residents
document.getElementById('manualRoomId')?.addEventListener('change', async function () {
    const roomId = this.value;
    const residentSelect = document.getElementById('manualResidentDropdown');
    const summaryBox = document.getElementById('manualSummaryBox');

    residentSelect.innerHTML = '<option value="">Loading...</option>';
    residentSelect.disabled = true;
    summaryBox.style.display = 'none';

    if (!roomId) {
        residentSelect.innerHTML = '<option value="">Select Room First</option>';
        return;
    }

    try {
        const response = await fetch(`{{ url('admin/payments/residents') }}/${roomId}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
        });
        const data = await response.json();

        if (data.success && data.residents.length > 0) {
            let html = '<option value="">Select Resident</option>';
            data.residents.forEach(r => {
                html += `<option value="${r.id}" data-rent="${r.rent_amount}" data-code="${escapeHtml(r.resident_code)}">${escapeHtml(r.name)} (${escapeHtml(r.resident_code)})</option>`;
            });
            residentSelect.innerHTML = html;
            residentSelect.disabled = false;
        } else {
            residentSelect.innerHTML = '<option value="">No residents in this room</option>';
        }
    } catch (err) {
        console.error(err);
        residentSelect.innerHTML = '<option value="">Failed to load</option>';
    }
});

// Resident → Fill summary
document.getElementById('manualResidentDropdown')?.addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    const residentId = this.value;
    const summaryBox = document.getElementById('manualSummaryBox');

    if (!residentId) {
        summaryBox.style.display = 'none';
        document.getElementById('payResidentId').value = '';
        return;
    }

    document.getElementById('payResidentId').value = residentId;

    const rent = parseFloat(option.dataset.rent) || 0;

    document.getElementById('payResidentName').textContent = option.text;
    document.getElementById('payResidentLocation').textContent =
        document.getElementById('manualHostelId').options[document.getElementById('manualHostelId').selectedIndex].text
        + ' — ' +
        document.getElementById('manualRoomId').options[document.getElementById('manualRoomId').selectedIndex].text;
    document.getElementById('payMonthLabel').textContent =
        document.getElementById('payMonth').options[document.getElementById('payMonth').selectedIndex].text
        + ' ' +
        document.getElementById('payYear').value;

    document.getElementById('payRequiredRent').textContent = '₹' + formatNumber(rent);
    document.getElementById('payRent').value = rent;
    document.getElementById('payUpi').value = rent;
    document.getElementById('payPreviousPending').textContent = '₹0';
    document.getElementById('payTotalDue').textContent = '₹' + formatNumber(rent);

    summaryBox.style.display = 'block';
});

// ─────────────────────────────────────────
// FILTER LISTENERS
// ─────────────────────────────────────────
function debounceLoad() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadPayments, 400);
}

document.getElementById('pmSearch').addEventListener('input', debounceLoad);
document.getElementById('pmRoomFilter').addEventListener('input', debounceLoad);
document.getElementById('pmBedFilter').addEventListener('input', debounceLoad);
document.getElementById('pmHostelFilter').addEventListener('change', loadPayments);
document.getElementById('pmMonthFilter').addEventListener('change', loadPayments);
document.getElementById('pmYearFilter').addEventListener('change', loadPayments);
document.getElementById('pmStatusFilter').addEventListener('change', loadPayments);

// ─────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────
function formatNumber(n) {
    const num = parseFloat(n) || 0;
    return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

document.addEventListener('DOMContentLoaded', function () {
    loadPayments();
});
</script>
@endpush
