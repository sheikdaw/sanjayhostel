@extends('layouts.office')

@section('title', 'Dashboard — Sanjay PG Hostel')
@section('page_title', 'Dashboard')

@push('styles')
<style>
    /* Dashboard specific styles */
    .ds-stat {
        background: white;
        border-radius: 12px;
        padding: 1.25rem 1.25rem 1rem 1.25rem;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .ds-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.06);
    }
    .ds-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }
    .ds-stat.green::before { background: #10b981; }
    .ds-stat.gold::before { background: #f59e0b; }
    .ds-stat.blue::before { background: #3b82f6; }
    .ds-stat.red::before { background: #ef4444; }
    .ds-stat.purple::before { background: #8b5cf6; }
    .ds-stat.pink::before { background: #ec4899; }

    .ds-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 0.75rem;
    }
    .ds-stat-icon.green { background: rgba(16,185,129,0.1); color: #10b981; }
    .ds-stat-icon.gold { background: rgba(245,158,11,0.1); color: #f59e0b; }
    .ds-stat-icon.blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
    .ds-stat-icon.red { background: rgba(239,68,68,0.1); color: #ef4444; }
    .ds-stat-icon.purple { background: rgba(139,92,246,0.1); color: #8b5cf6; }
    .ds-stat-icon.pink { background: rgba(236,72,153,0.1); color: #ec4899; }

    .ds-stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .ds-stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0a2e1a;
        font-family: var(--font-mono, monospace);
        line-height: 1.2;
    }
    .ds-stat-change {
        font-size: 0.7rem;
        display: inline-flex;
        align-items: center;
        margin-top: 0.5rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    .ds-stat-change.up {
        color: #10b981;
        background: rgba(16,185,129,0.1);
    }
    .ds-stat-change.down {
        color: #ef4444;
        background: rgba(239,68,68,0.1);
    }
    .ds-stat-change.neutral {
        color: #6b7280;
        background: rgba(107,114,128,0.1);
    }

    /* Quick Action Buttons */
    .ds-quick-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 0.5rem;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        text-decoration: none;
        color: #374151;
        font-size: 0.7rem;
        font-weight: 500;
        transition: all 0.2s;
        text-align: center;
        min-height: 70px;
    }
    .ds-quick-btn:hover {
        background: white;
        border-color: var(--sanjay-gold);
        color: var(--sanjay-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        text-decoration: none;
    }
    .ds-quick-btn i {
        font-size: 1.5rem;
        margin-bottom: 0.3rem;
        color: var(--sanjay-gold);
    }
    .col-sm-2-custom {
        flex: 0 0 auto;
        width: 16.666%;
    }
    @media (max-width: 576px) {
        .col-sm-2-custom {
            width: 33.333%;
        }
    }

    /* Bar Chart */
    .ds-bar-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.4rem;
        padding: 0.1rem 0;
    }
    .ds-bar-label {
        font-size: 0.7rem;
        color: #6b7280;
        width: 32px;
        text-align: right;
        font-weight: 500;
    }
    .ds-bar-track {
        flex: 1;
        height: 18px;
        background: #f3f4f6;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
    }
    .ds-bar-fill {
        height: 100%;
        background: #10b981;
        border-radius: 20px;
        transition: width 1s cubic-bezier(0.22, 1, 0.36, 1);
        width: 0%;
    }
    .ds-bar-fill.gold { background: #f59e0b; }
    .ds-bar-fill.blue { background: #3b82f6; }
    .ds-bar-fill.red { background: #ef4444; }
    .ds-bar-fill.purple { background: #8b5cf6; }
    .ds-bar-val {
        font-size: 0.7rem;
        font-weight: 600;
        color: #374151;
        width: 48px;
        font-family: var(--font-mono, monospace);
        text-align: right;
    }

    /* Progress Bars */
    .ds-progress-wrap {
        margin-bottom: 0.75rem;
    }
    .ds-progress-wrap:last-child {
        margin-bottom: 0;
    }
    .ds-progress-head {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #374151;
        margin-bottom: 0.2rem;
    }
    .ds-progress-head span:first-child {
        font-weight: 500;
    }
    .ds-progress-head span:last-child {
        font-weight: 600;
        color: #6b7280;
    }
    .ds-progress-bar {
        height: 6px;
        background: #f3f4f6;
        border-radius: 20px;
        overflow: hidden;
    }
    .ds-progress-fill {
        height: 100%;
        border-radius: 20px;
        transition: width 1s ease;
    }

    /* Table Styles */
    .ds-table {
        width: 100%;
        font-size: 0.8rem;
        border-collapse: collapse;
    }
    .ds-table thead th {
        text-align: left;
        padding: 0.6rem 0.75rem;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #f3f4f6;
    }
    .ds-table tbody td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .ds-table tbody tr:hover {
        background: #fafbfc;
    }
    .ds-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Pills */
    .ds-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .ds-pill.paid { background: #dcfce7; color: #166534; }
    .ds-pill.pending { background: #fef3c7; color: #92400e; }
    .ds-pill.overdue { background: #fee2e2; color: #991b1b; }
    .ds-pill.partial { background: #fef3c7; color: #92400e; }
    .ds-pill.vacant { background: #dcfce7; color: #166534; }
    .ds-pill.occupied { background: #fef3c7; color: #92400e; }
    .ds-pill.blocked { background: #fee2e2; color: #991b1b; }
    .ds-pill.active { background: #dcfce7; color: #166534; }
    .ds-pill.vacated { background: #fee2e2; color: #991b1b; }
    .ds-pill.on-track { background: #dcfce7; color: #166534; }
    .ds-pill.behind { background: #fef3c7; color: #92400e; }
    .ds-pill.at-risk { background: #fee2e2; color: #991b1b; }

    /* Activity Feed */
    .ds-activity-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .ds-activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .ds-activity-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.8rem;
    }
    .ds-activity-text {
        font-size: 0.8rem;
        color: #374151;
        line-height: 1.4;
    }
    .ds-activity-text strong {
        color: #0a2e1a;
    }
    .ds-activity-time {
        font-size: 0.65rem;
        color: #9ca3af;
        margin-top: 0.1rem;
    }

    /* Cards */
    .ds-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .ds-card-head {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .ds-card-title {
        font-weight: 600;
        color: #0a2e1a;
        font-size: 0.9rem;
    }
    .ds-card-body {
        padding: 1.25rem;
        flex: 1;
    }

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        border-radius: 12px;
        padding: 1.5rem 2rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .welcome-banner::after {
        content: '';
        position: absolute;
        right: -50px;
        top: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .welcome-banner h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .welcome-banner p {
        opacity: 0.8;
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    .welcome-banner .badge-live {
        background: rgba(255,255,255,0.15);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .welcome-banner .badge-live .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #22c55e;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.3; }
        100% { opacity: 1; }
    }

    /* Hostel Stat Card */
    .hostel-stat-card {
        background: #f8fafc;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    .hostel-stat-card:hover {
        border-color: var(--sanjay-gold);
        background: white;
    }
    .hostel-stat-card .hostel-name {
        font-weight: 600;
        color: var(--sanjay-primary);
        font-size: 0.85rem;
    }
    .hostel-stat-card .hostel-code {
        font-size: 0.6rem;
        color: #6b7280;
        font-family: monospace;
    }
    .hostel-stat-card .stat-item {
        font-size: 0.7rem;
        color: #6b7280;
        display: flex;
        justify-content: space-between;
        padding: 2px 0;
    }
    .hostel-stat-card .stat-item .value {
        font-weight: 600;
        color: #374151;
    }
    .hostel-stat-card .occupancy-bar {
        height: 4px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.25rem;
    }
    .hostel-stat-card .occupancy-bar .fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    /* Scrollable container */
    .scrollable-container {
        max-height: 400px;
        overflow-y: auto;
    }
    .scrollable-container::-webkit-scrollbar {
        width: 4px;
    }
    .scrollable-container::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 4px;
    }
    .scrollable-container::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }
    .scrollable-container::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ds-stat-value {
            font-size: 1.25rem;
        }
        .welcome-banner {
            padding: 1rem 1.25rem;
        }
        .welcome-banner h1 {
            font-size: 1.1rem;
        }
        .ds-card-head {
            padding: 0.75rem 1rem;
        }
        .ds-card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')

{{-- ── Welcome Banner ── --}}
<div class="welcome-banner">
    <div style="position:relative; z-index:1;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h1>Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name ?? 'Officer')[0] }} 👋</h1>
                <p>Welcome to Sanjay PG Hostel Management System — {{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge-live">
                    <span class="dot"></span>
                    Live
                </span>
                <a href="#" class="btn btn-sm" style="background:rgba(255,255,255,0.15); color:white; border:1px solid rgba(255,255,255,0.2); border-radius:9px; padding:4px 14px; font-size:0.8rem; text-decoration:none;">
                    <i class="bi bi-download me-1"></i> Export Report
                </a>
            </div>
        </div>
        @if(auth()->user()->role != 'admin')
            <div style="margin-top:0.5rem; font-size:0.8rem; opacity:0.8;">
                <i class="bi bi-info-circle me-1"></i> You have access to {{ $hostels->count() }} hostel(s)
            </div>
        @endif
    </div>
</div>

{{-- ── Stat Grid ── --}}
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="ds-stat green">
            <div class="ds-stat-icon green"><i class="bi bi-building"></i></div>
            <div class="ds-stat-label">Total Hostels</div>
            <div class="ds-stat-value">{{ $totalHostels }}</div>
            <span class="ds-stat-change up"><i class="bi bi-circle-fill me-1" style="font-size:6px;"></i>Active</span>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="ds-stat blue">
            <div class="ds-stat-icon blue"><i class="bi bi-door-open"></i></div>
            <div class="ds-stat-label">Total Rooms</div>
            <div class="ds-stat-value">{{ $totalRooms }}</div>
            <span class="ds-stat-change neutral">{{ $totalHostels > 0 ? round($totalRooms / $totalHostels, 1) : 0 }} avg per hostel</span>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="ds-stat purple">
            <div class="ds-stat-icon purple"><i class="bi bi-bed"></i></div>
            <div class="ds-stat-label">Total Beds</div>
            <div class="ds-stat-value">{{ $totalBeds }}</div>
            <span class="ds-stat-change up"><i class="bi bi-arrow-up-short"></i>{{ $occupiedBeds }} occupied</span>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="ds-stat gold">
            <div class="ds-stat-icon gold"><i class="bi bi-people"></i></div>
            <div class="ds-stat-label">Active Residents</div>
            <div class="ds-stat-value">{{ $totalResidents }}</div>
            <span class="ds-stat-change down"><i class="bi bi-arrow-down-short"></i>{{ $totalVacated }} vacated</span>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="ds-stat pink">
            <div class="ds-stat-icon pink"><i class="bi bi-currency-rupee"></i></div>
            <div class="ds-stat-label">Total Collected</div>
            <div class="ds-stat-value">₹{{ number_format($totalCollected / 100000, 1) }}L</div>
            <span class="ds-stat-change up"><i class="bi bi-arrow-up-short"></i>{{ $totalPayments }} transactions</span>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="ds-stat red">
            <div class="ds-stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="ds-stat-label">Pending Amount</div>
            <div class="ds-stat-value">₹{{ number_format($totalPending / 100000, 1) }}L</div>
            <span class="ds-stat-change down"><i class="bi bi-arrow-up-short"></i>{{ $pendingCount }} pending</span>
        </div>
    </div>
</div>

{{-- ── Row 2: Chart + Quick Actions ── --}}
<div class="row g-3 mb-4">

    {{-- Monthly collections chart --}}
    <div class="col-xl-5 col-lg-6">
        <div class="ds-card h-100">
            <div class="ds-card-head">
                <div class="ds-card-title">Monthly Collections (Last 6 Months)</div>
                <span class="ds-pill paid">Lakhs ₹</span>
            </div>
            <div class="ds-card-body">
                @if(count($months) > 0)
                    @foreach($months as $index => $month)
                    <div class="ds-bar-row">
                        <span class="ds-bar-label">{{ $month }}</span>
                        <div class="ds-bar-track">
                            <div class="ds-bar-fill {{ $index % 3 === 1 ? 'gold' : ($index % 3 === 2 ? 'blue' : '') }}"
                                 style="width: 0%;"
                                 data-width="{{ $collections[$index] > 0 ? round(($collections[$index] / max($collections)) * 100) : 0 }}"></div>
                        </div>
                        <span class="ds-bar-val">₹{{ $collections[$index] }}L</span>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-bar-chart" style="font-size:2rem; color:#d1d5db;"></i>
                        <p class="text-muted mt-2" style="font-size:0.85rem;">No payment data available yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick actions + Hostel stats --}}
    <div class="col-xl-7 col-lg-6 d-flex flex-column gap-3">

        {{-- Quick actions --}}
        <div class="ds-card">
            <div class="ds-card-head">
                <div class="ds-card-title">Quick Actions</div>
            </div>
            <div class="ds-card-body">
                <div class="row g-2">
                    <div class="col-4 col-sm-2-custom">
                        <a href="{{ route('admin.residents.index') }}" class="ds-quick-btn">
                            <i class="bi bi-person-plus"></i>
                            Add Resident
                        </a>
                    </div>
                    <div class="col-4 col-sm-2-custom">
                        <a href="{{ route('admin.payments.index') }}" class="ds-quick-btn">
                            <i class="bi bi-credit-card"></i>
                            Payments
                        </a>
                    </div>
                    <div class="col-4 col-sm-2-custom">
                        <a href="{{ route('admin.rooms.index') }}" class="ds-quick-btn">
                            <i class="bi bi-door-open"></i>
                            Rooms
                        </a>
                    </div>
                    <div class="col-4 col-sm-2-custom">
                        <a href="{{ route('admin.beds.index') }}" class="ds-quick-btn">
                            <i class="bi bi-bed"></i>
                            Beds
                        </a>
                    </div>
                    <div class="col-4 col-sm-2-custom">
                        <a href="{{ route('admin.hostels.index') }}" class="ds-quick-btn">
                            <i class="bi bi-building"></i>
                            Hostels
                        </a>
                    </div>
                    <div class="col-4 col-sm-2-custom">
                        <a href="#" class="ds-quick-btn">
                            <i class="bi bi-file-bar-graph"></i>
                            Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hostel wise quick stats --}}
        <div class="ds-card flex-grow-1">
            <div class="ds-card-head">
                <div class="ds-card-title">Hostel Wise Overview</div>
                <span style="font-size:0.72rem; color:#9ca3af; font-family:var(--font-mono);">{{ $totalResidents }} residents</span>
            </div>
            <div class="ds-card-body">
                <div class="row g-2">
                    @foreach($hostelStats as $stat)
                    <div class="col-md-6 col-xl-4">
                        <div class="hostel-stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="hostel-name">{{ $stat['name'] }}</div>
                                    <div class="hostel-code">{{ $stat['code'] }}</div>
                                </div>
                                <span class="ds-pill {{ $stat['occupancy_rate'] >= 70 ? 'paid' : ($stat['occupancy_rate'] >= 40 ? 'pending' : 'vacated') }}">
                                    {{ $stat['occupancy_rate'] }}%
                                </span>
                            </div>
                            <div class="mt-1">
                                <div class="stat-item">
                                    <span>Residents</span>
                                    <span class="value">{{ $stat['residents'] }}</span>
                                </div>
                                <div class="stat-item">
                                    <span>Beds</span>
                                    <span class="value">{{ $stat['occupied'] }}/{{ $stat['beds'] }}</span>
                                </div>
                                <div class="occupancy-bar">
                                    <div class="fill" style="width: {{ $stat['occupancy_rate'] }}%; background: {{ $stat['occupancy_rate'] >= 70 ? '#10b981' : ($stat['occupancy_rate'] >= 40 ? '#f59e0b' : '#ef4444') }};"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Row 3: Recent Transactions + Activity ── --}}
<div class="row g-3 mb-4">

    {{-- Recent transactions table --}}
    <div class="col-xl-8">
        <div class="ds-card">
            <div class="ds-card-head">
                <div class="ds-card-title">Recent Transactions</div>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm"
                   style="font-size:0.72rem; color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:7px; padding:4px 12px; background:rgba(16,185,129,0.05); text-decoration:none;">
                   View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div style="overflow-x:auto;">
                @if($recentPayments->count() > 0)
                    <table class="ds-table">
                        <thead>
                            <tr>
                                <th>Receipt No.</th>
                                <th>Resident</th>
                                <th>Hostel</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                            <tr>
                                <td><span style="font-family:var(--font-mono); font-size:0.72rem; color:#0a2e1a;">{{ $payment->receipt_no }}</span></td>
                                <td><span style="font-weight:600; color:#111827;">{{ $payment->resident->name ?? 'N/A' }}</span></td>
                                <td><span style="color:#6b7280;">{{ $payment->resident->hostel->hostel_name ?? 'N/A' }}</span></td>
                                <td><span style="font-family:var(--font-mono); font-weight:500; color:#0a2e1a;">₹{{ number_format($payment->rent_amount, 0) }}</span></td>
                                <td><span style="color:#9ca3af; font-size:0.72rem;">{{ $payment->payment_date->format('d M Y') }}</span></td>
                                <td><span class="ds-pill {{ $payment->status_badge }}">{{ $payment->status_label }}</span></td>
                                <td>
                                    <a href="#" style="color:#10b981; font-size:13px;" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-credit-card" style="font-size:2rem; color:#d1d5db;"></i>
                        <p class="text-muted mt-2" style="font-size:0.85rem;">No recent transactions</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Activity feed --}}
    <div class="col-xl-4">
        <div class="ds-card h-100">
            <div class="ds-card-head">
                <div class="ds-card-title">Recent Activity</div>
                <span style="font-size:0.68rem; color:#9ca3af; font-family:var(--font-mono);">Live</span>
            </div>
            <div class="ds-card-body scrollable-container">
                @if($recentResidents->count() > 0)
                    @foreach($recentResidents as $resident)
                    <div class="ds-activity-item">
                        <div class="ds-activity-dot" style="background:rgba(16,185,129,0.1); color:#10b981;">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div>
                            <div class="ds-activity-text">
                                <strong>New resident registered</strong> — {{ $resident->name }}
                                <span style="font-size:0.7rem; color:#6b7280;">({{ $resident->resident_code }})</span>
                            </div>
                            <div class="ds-activity-time">
                                {{ $resident->created_at->diffForHumans() }}
                                <span style="color:#6b7280;">•</span>
                                {{ $resident->hostel->hostel_name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history" style="font-size:2rem; color:#d1d5db;"></i>
                        <p class="text-muted mt-2" style="font-size:0.85rem;">No recent activity</p>
                    </div>
                @endif

                @if($recentPayments->count() > 0)
                    @foreach($recentPayments->take(3) as $payment)
                    <div class="ds-activity-item">
                        <div class="ds-activity-dot" style="background:rgba(16,185,129,0.1); color:#10b981;">
                            <i class="bi bi-check2"></i>
                        </div>
                        <div>
                            <div class="ds-activity-text">
                                <strong>Payment received</strong> — ₹{{ number_format($payment->rent_amount, 0) }}
                                from {{ $payment->resident->name ?? 'N/A' }}
                            </div>
                            <div class="ds-activity-time">
                                {{ $payment->created_at->diffForHumans() }}
                                <span style="color:#6b7280;">•</span>
                                {{ $payment->status_label }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Row 4: Additional Statistics ── --}}
<div class="row g-3 mb-4">

    {{-- Bed Status Distribution --}}
    <div class="col-md-4">
        <div class="ds-card">
            <div class="ds-card-head">
                <div class="ds-card-title">Bed Status Distribution</div>
            </div>
            <div class="ds-card-body">
                @php
                    $bedStatuses = [
                        ['Vacant', $vacantBeds, '#22c55e'],
                        ['Occupied', $occupiedBeds, '#f59e0b'],
                        ['Blocked', $blockedBeds, '#ef4444']
                    ];
                    $bedTotal = $vacantBeds + $occupiedBeds + $blockedBeds;
                @endphp
                @foreach($bedStatuses as $status)
                <div class="ds-progress-wrap">
                    <div class="ds-progress-head">
                        <span>{{ $status[0] }}</span>
                        <span>{{ $status[1] }} ({{ $bedTotal > 0 ? round(($status[1] / $bedTotal) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="ds-progress-bar">
                        <div class="ds-progress-fill" style="width: {{ $bedTotal > 0 ? round(($status[1] / $bedTotal) * 100) : 0 }}%; background: {{ $status[2] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Resident Status Distribution --}}
    <div class="col-md-4">
        <div class="ds-card">
            <div class="ds-card-head">
                <div class="ds-card-title">Resident Status</div>
            </div>
            <div class="ds-card-body">
                @php
                    $residentStatuses = [
                        ['Active', $totalResidents, '#22c55e'],
                        ['Vacated', $totalVacated, '#ef4444']
                    ];
                    $residentTotal = $totalResidents + $totalVacated;
                @endphp
                @foreach($residentStatuses as $status)
                <div class="ds-progress-wrap">
                    <div class="ds-progress-head">
                        <span>{{ $status[0] }}</span>
                        <span>{{ $status[1] }} ({{ $residentTotal > 0 ? round(($status[1] / $residentTotal) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="ds-progress-bar">
                        <div class="ds-progress-fill" style="width: {{ $residentTotal > 0 ? round(($status[1] / $residentTotal) * 100) : 0 }}%; background: {{ $status[2] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Room Type Distribution --}}
    <div class="col-md-4">
        <div class="ds-card">
            <div class="ds-card-head">
                <div class="ds-card-title">Room Type Distribution</div>
            </div>
            <div class="ds-card-body">
                @if($roomTypeDistribution->count() > 0)
                    @foreach($roomTypeDistribution as $type)
                    <div class="ds-progress-wrap">
                        <div class="ds-progress-head">
                            <span>{{ $type->room_type_name }}</span>
                            <span>{{ $type->total }}</span>
                        </div>
                        <div class="ds-progress-bar">
                            <div class="ds-progress-fill" style="width: {{ $roomTypeDistribution->sum('total') > 0 ? round(($type->total / $roomTypeDistribution->sum('total')) * 100) : 0 }}%; background: var(--sanjay-gold);"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <p class="text-muted" style="font-size:0.85rem;">No room types available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Row 5: Bed Type Distribution ── --}}
<div class="row g-3">
    <div class="col-12">
        <div class="ds-card">
            <div class="ds-card-head">
                <div class="ds-card-title">Bed Type Distribution</div>
                <span style="font-size:0.72rem; color:#9ca3af;">Total: {{ $totalBeds }} beds</span>
            </div>
            <div class="ds-card-body">
                <div class="row g-3">
                    @if($bedTypeDistribution->count() > 0)
                        @foreach($bedTypeDistribution as $type)
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center p-2" style="background:#f8fafc; border-radius:8px;">
                                <div>
                                    <span style="font-weight:600; color:var(--sanjay-primary);">
                                        <i class="bi {{ $type->bed_type == 'NORMAL' ? 'bi-bed' : 'bi-layers' }} me-2"></i>
                                        {{ $type->bed_type }}
                                    </span>
                                </div>
                                <div>
                                    <span style="font-weight:700; font-size:1.1rem; color:#0a2e1a;">{{ $type->total }}</span>
                                    <span style="color:#6b7280; font-size:0.7rem; margin-left:4px;">
                                        ({{ $totalBeds > 0 ? round(($type->total / $totalBeds) * 100, 1) : 0 }}%)
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center py-3">
                            <p class="text-muted" style="font-size:0.85rem;">No bed data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Animate bar fills on load
    document.addEventListener('DOMContentLoaded', function () {
        // Animate chart bars
        const bars = document.querySelectorAll('.ds-bar-fill[data-width]');
        bars.forEach(bar => {
            const w = bar.getAttribute('data-width');
            bar.style.width = '0%';
            setTimeout(() => { bar.style.width = w + '%'; }, 100);
        });

        // Animate progress bars
        const progressBars = document.querySelectorAll('.ds-progress-fill');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => { bar.style.width = width; }, 200);
        });

        // Animate hostel occupancy bars
        const occupancyBars = document.querySelectorAll('.hostel-stat-card .occupancy-bar .fill');
        occupancyBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => { bar.style.width = width; }, 300);
        });
    });

    // Refresh data every 30 seconds (optional)
    setInterval(function() {
        // You can add AJAX call here to refresh stats
        console.log('Dashboard auto-refresh...');
    }, 30000);
</script>
@endpush
