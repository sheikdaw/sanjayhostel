<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $hostel->hostel_name }} - Residents</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1a3a6b;
            --primary-light: #2a5a9b;
            --gold: #c5a028;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            
            /* Status Colors */
            --status-paid: #22c55e;
            --status-paid-bg: #dcfce7;
            --status-partial: #f59e0b;
            --status-partial-bg: #fef3c7;
            --status-pending: #ef4444;
            --status-pending-bg: #fee2e2;
        }

        * { box-sizing: border-box; }
        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
        }

        /* ===== HEADER ===== */
        .hostel-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 1.25rem 0;
            margin-bottom: 1rem;
            border-radius: 0 0 20px 20px;
        }

        .hostel-header .hostel-icon { font-size: 2rem; color: var(--gold); }
        .hostel-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
        .hostel-header .hostel-code {
            background: rgba(255,255,255,0.15);
            padding: 0.2rem 1rem;
            border-radius: 20px;
            font-family: monospace;
            font-size: 0.75rem;
            display: inline-block;
        }
        .hostel-header .sub-text {
            opacity: 0.8;
            font-size: 0.8rem;
            margin: 0.25rem 0 0;
        }

        /* ===== STATS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-card {
            background: white;
            padding: 0.4rem 0.5rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .stat-card .number { font-size: 1rem; font-weight: 700; color: var(--primary); }
        .stat-card .label { font-size: 0.5rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; }
        .stat-card .icon { font-size: 0.9rem; display: block; }

        /* ===== SEARCH ===== */
        .search-section {
            background: white;
            padding: 0.5rem 0.75rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-bottom: 0.75rem;
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-section .search-box {
            flex: 1;
            position: relative;
            min-width: 150px;
        }

        .search-section .search-box input {
            width: 100%;
            padding: 0.3rem 0.5rem 0.3rem 1.8rem;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 0.8rem;
            background: #f8fafc;
        }

        .search-section .search-box input:focus {
            border-color: var(--gold);
            outline: none;
            box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
            background: white;
        }

        .search-section .search-box i {
            position: absolute;
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.8rem;
        }

        .search-section .filter-group {
            display: flex;
            gap: 0.3rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-section .filter-group select {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 0.7rem;
            background: #f8fafc;
        }

        .search-section .filter-group select:focus {
            border-color: var(--gold);
            outline: none;
        }

        .search-section .result-count {
            font-size: 0.65rem;
            color: #6b7280;
            padding: 0.1rem 0.5rem;
            background: #f3f4f6;
            border-radius: 12px;
            white-space: nowrap;
        }

        /* ===== RESIDENT GRID ===== */
        .resident-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.5rem;
        }

        /* ===== RESIDENT CARD WITH STATUS COLOR ===== */
        .resident-card {
            background: white;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            border-left: 4px solid #e5e7eb;
        }

        .resident-card.status-paid {
            border-left-color: var(--status-paid);
            background: linear-gradient(to right, rgba(34, 197, 94, 0.05), white);
        }

        .resident-card.status-partial {
            border-left-color: var(--status-partial);
            background: linear-gradient(to right, rgba(245, 158, 11, 0.05), white);
        }

        .resident-card.status-pending {
            border-left-color: var(--status-pending);
            background: linear-gradient(to right, rgba(239, 68, 68, 0.05), white);
        }

        .resident-card.status-not-paid {
            border-left-color: var(--status-pending);
            background: linear-gradient(to right, rgba(239, 68, 68, 0.08), white);
        }

        .resident-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: var(--gold);
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
            display: inline-block;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }

        .status-indicator.paid { background: var(--status-paid); }
        .status-indicator.partial { background: var(--status-partial); }
        .status-indicator.pending { background: var(--status-pending); }
        .status-indicator.not-paid { background: var(--status-pending); }

        .resident-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gold);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }

        .resident-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .resident-avatar.color-0 { background: #fbbf24; color: #78350f; }
        .resident-avatar.color-1 { background: #60a5fa; color: #1e3a5f; }
        .resident-avatar.color-2 { background: #34d399; color: #064e3b; }
        .resident-avatar.color-3 { background: #f472b6; color: #831843; }
        .resident-avatar.color-4 { background: #a78bfa; color: #2e1065; }
        .resident-avatar.color-5 { background: #fb923c; color: #7c2d12; }
        .resident-avatar.color-6 { background: #f87171; color: #7f1d1d; }
        .resident-avatar.color-7 { background: #6ee7b7; color: #064e3b; }
        .resident-avatar.color-8 { background: #93c5fd; color: #1e3a5f; }
        .resident-avatar.color-9 { background: #fcd34d; color: #78350f; }

        .resident-info {
            flex: 1;
            min-width: 0;
        }

        .resident-info .name {
            font-weight: 600;
            font-size: 0.8rem;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .resident-info .details {
            font-size: 0.6rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .resident-info .details .food-badge {
            font-size: 0.5rem;
            padding: 0.05rem 0.35rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .resident-info .details .food-badge.with-food { background: #dcfce7; color: #166534; }
        .resident-info .details .food-badge.without-food { background: #f3f4f6; color: #4b5563; }

        .status-badge {
            font-size: 0.55rem;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            font-weight: 700;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-badge.paid {
            background: var(--status-paid-bg);
            color: #166534;
            border: 1px solid var(--status-paid);
        }

        .status-badge.partial {
            background: var(--status-partial-bg);
            color: #92400e;
            border: 1px solid var(--status-partial);
        }

        .status-badge.pending {
            background: var(--status-pending-bg);
            color: #991b1b;
            border: 1px solid var(--status-pending);
            animation: pulse 2s infinite;
        }

        .status-badge.not-paid {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .status-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-badge.paid .dot { background: var(--status-paid); }
        .status-badge.partial .dot { background: var(--status-partial); }
        .status-badge.pending .dot { background: var(--status-pending); }
        .status-badge.not-paid .dot { background: #9ca3af; }

        .rent-badge {
            font-size: 0.6rem;
            font-weight: 700;
            color: #92400e;
            background: #fef3c7;
            padding: 0.05rem 0.4rem;
            border-radius: 10px;
            white-space: nowrap;
        }

        .room-badge {
            font-size: 0.55rem;
            color: #4b5563;
            background: #f3f4f6;
            padding: 0.05rem 0.4rem;
            border-radius: 10px;
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #6b7280;
        }
        .empty-state i { font-size: 2.5rem; color: #d1d5db; margin-bottom: 0.5rem; }

        .no-results {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        .no-results i { font-size: 2rem; color: #d1d5db; margin-bottom: 0.5rem; }

        /* ===== MODAL ===== */
        .modal-content { border-radius: 16px; border: none; }
        .modal-header {
            background: var(--primary);
            color: white;
            border-radius: 16px 16px 0 0;
        }
        .modal-header .btn-close { filter: brightness(0) invert(1); }

        .payment-detail {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .payment-detail:last-child { border-bottom: none; }
        .payment-detail .label { color: #6b7280; font-size: 0.8rem; }
        .payment-detail .value { font-weight: 600; color: #1f2937; font-size: 0.85rem; }
        .payment-detail .value.due { color: var(--danger); }
        .payment-detail .value.clear { color: var(--success); }

        .payment-method-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.4rem;
            margin: 0.4rem 0;
        }

        .payment-method-option {
            padding: 0.4rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.75rem;
        }

        .payment-method-option:hover { border-color: var(--gold); background: #f8fafc; }
        .payment-method-option.selected { border-color: var(--gold); background: #fef3c7; }
        .payment-method-option i { font-size: 1rem; display: block; margin-bottom: 0.15rem; }

        .btn-submit-payment {
            width: 100%;
            padding: 0.6rem;
            font-size: 0.9rem;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, var(--gold), #b8941a);
            color: white;
            transition: all 0.3s;
        }

        .btn-submit-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(197, 160, 40, 0.3);
        }
        .btn-submit-payment:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .badge-status {
            font-size: 0.6rem;
            padding: 0.15rem 0.4rem;
            border-radius: 10px;
            font-weight: 600;
        }
        .badge-status.paid { background: #dcfce7; color: #166534; }
        .badge-status.pending { background: #fee2e2; color: #991b1b; }
        .badge-status.partial { background: #fef3c7; color: #92400e; }

        .payment-history-table { font-size: 0.75rem; }
        .payment-history-table th {
            background: #f8fafc;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #6b7280;
        }

        /* ===== PROFILE IMAGE UPLOAD STYLES ===== */
        .profile-image-section .resident-avatar {
            transition: all 0.3s ease;
            width: 80px;
            height: 80px;
            font-size: 1.5rem;
            border: 3px solid var(--gold);
            margin: 0 auto;
            border-radius: 50%;
            background: var(--gold);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .profile-image-section .resident-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-image-section .resident-avatar:hover {
            box-shadow: 0 0 20px rgba(197, 160, 40, 0.3);
        }

        .profile-image-section .btn-primary {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--primary);
        }

        .profile-image-section .btn-primary:hover {
            background: #b8941a;
            border-color: #b8941a;
            transform: scale(1.1);
        }

        .profile-image-section .btn-danger {
            width: 24px;
            height: 24px;
            padding: 0;
            font-size: 10px;
            border: 2px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dob-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 600;
            background: #fce7f3;
            color: #831843;
        }

        .dob-badge i {
            color: #ec4899;
        }

        /* ===== TRANSACTION ID STYLES ===== */
        #transactionIdRow {
            animation: slideDown 0.3s ease;
        }

        #transactionIdRow input {
            background: #f8fafc;
        }

        #transactionIdRow input:focus {
            background: white;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-method-option[data-method="upi"] i {
            color: #7c3aed;
        }

        .payment-method-option[data-method="card"] i {
            color: #2563eb;
        }

        .payment-method-option[data-method="bank_transfer"] i {
            color: #059669;
        }

        .payment-method-option[data-method="cash"] i {
            color: #16a34a;
        }

        .status-badge-large {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge-large.paid {
            background: #dcfce7;
            color: #166534;
            border: 2px solid var(--status-paid);
        }

        .status-badge-large.partial {
            background: #fef3c7;
            color: #92400e;
            border: 2px solid var(--status-partial);
        }

        .status-badge-large.pending {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid var(--status-pending);
            animation: pulse 2s infinite;
        }

        .status-badge-large .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-badge-large.paid .dot { background: var(--status-paid); }
        .status-badge-large.partial .dot { background: var(--status-partial); }
        .status-badge-large.pending .dot { background: var(--status-pending); }

        .transaction-id-display {
            font-size: 0.8rem;
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 2px 10px;
            border-radius: 4px;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            word-break: break-all;
        }

        /* ===== TOAST ===== */
        .toast-container {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 9999;
            max-width: 350px;
        }

        .toast-custom {
            background: white;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            border-left: 4px solid #10b981;
            margin-bottom: 0.5rem;
            animation: slideInRight 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .toast-custom.error { border-left-color: #dc2626; }
        .toast-custom .message { flex: 1; color: #1f2937; }
        .toast-custom .close-btn { background: none; border: none; color: #9ca3af; cursor: pointer; }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @media (max-width: 768px) {
            .search-section { flex-direction: column; align-items: stretch; }
            .search-section .filter-group { justify-content: center; }
            .resident-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
            .resident-card { padding: 0.4rem; }
            .resident-avatar { width: 32px; height: 32px; font-size: 0.7rem; }
            .resident-info .name { font-size: 0.7rem; }
            .profile-image-section .resident-avatar { width: 60px; height: 60px; font-size: 1.2rem; }
            .payment-method-group { grid-template-columns: repeat(2, 1fr); }
            .status-badge { font-size: 0.5rem; padding: 0.1rem 0.3rem; }
            .status-indicator { width: 8px; height: 8px; }
        }

        @media (max-width: 480px) {
            .resident-grid { grid-template-columns: 1fr 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .hostel-header h1 { font-size: 1.2rem; }
            .profile-image-section .resident-avatar { width: 50px; height: 50px; font-size: 1rem; }
            .payment-method-group { grid-template-columns: 1fr 1fr; }
            .status-badge { font-size: 0.45rem; padding: 0.05rem 0.25rem; }
        }
    </style>
</head>
<body>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ===== HEADER ===== -->
    <div class="hostel-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-2">
                        <span class="hostel-icon"><i class="bi bi-building"></i></span>
                        <div>
                            <h1>{{ $hostel->hostel_name }}</h1>
                            <span class="hostel-code">{{ $hostel->hostel_code ?? 'HOSTEL' }}</span>
                            <p class="sub-text">
                                <i class="bi bi-geo-alt"></i> Click on any resident to pay
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-shield-lock"></i> Secure
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">

        <!-- ===== STATISTICS ===== -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <span class="icon">👨‍👩‍👧‍👦</span>
                <div class="number" id="totalCount">{{ $stats['total_residents'] }}</div>
                <div class="label">Total</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid var(--status-paid);">
                <span class="icon">✅</span>
                <div class="number" style="color:var(--success);" id="paidCount">0</div>
                <div class="label">Paid</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid var(--status-partial);">
                <span class="icon">⚠️</span>
                <div class="number" style="color:var(--warning);" id="partialCount">0</div>
                <div class="label">Partial</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid var(--status-pending);">
                <span class="icon">❌</span>
                <div class="number" style="color:var(--danger);" id="pendingCount">0</div>
                <div class="label">Pending</div>
            </div>
        </div>

        <!-- ===== SEARCH & FILTER ===== -->
        <div class="search-section">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Search by name, room, phone..." onkeyup="filterResidents()">
            </div>
            <div class="filter-group">
                <select id="statusFilter" onchange="filterResidents()">
                    <option value="">All Status</option>
                    <option value="PAID" style="color: var(--status-paid);">✅ Paid</option>
                    <option value="PARTIAL" style="color: var(--status-partial);">⚠️ Partial</option>
                    <option value="PENDING" style="color: var(--status-pending);">❌ Pending</option>
                    <option value="NOT_PAID" style="color: var(--status-pending);">🔴 Not Paid</option>
                </select>
                <select id="roomFilter" onchange="filterResidents()">
                    <option value="">All Rooms</option>
                    @foreach($occupiedRooms as $room)
                        <option value="{{ $room->id }}">#{{ $room->room_no }}</option>
                    @endforeach
                </select>
                <span class="result-count" id="resultCount">All</span>
            </div>
        </div>

        <!-- ===== RESIDENT GRID ===== -->
        <div id="residentsContainer">
            @if($occupiedRooms->count() > 0)
                <div class="resident-grid" id="residentGrid">
                    @foreach($occupiedRooms as $room)
                        @foreach($room->beds as $bed)
                            @if($bed->resident)
                                @php
                                    $resident = $bed->resident;
                                    $currentMonth = now()->month;
                                    $currentYear = now()->year;
                                    $payment = \App\Models\Payment::where('resident_id', $resident->id)
                                        ->where('month', $currentMonth)
                                        ->where('year', $currentYear)
                                        ->first();

                                    if ($payment) {
                                        $status = $payment->status;
                                        $balance = $payment->balance_amount;
                                        $paidAmount = ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0);
                                    } else {
                                        $status = 'NOT_PAID';
                                        $balance = $resident->rent_amount ?? 0;
                                        $paidAmount = 0;
                                    }

                                    $statusClass = strtolower($status);
                                    $statusLabel = $status == 'NOT_PAID' ? 'Not Paid' : $status;
                                    $colorIndex = crc32($resident->name) % 10;
                                    $initials = strtoupper(substr($resident->name, 0, 2));
                                    $hasProfileImage = $resident->profile_image ? true : false;
                                    $profileImageUrl = $resident->profile_image_url;
                                    $hasDob = $resident->dob ? true : false;
                                    $dobFormatted = $hasDob ? $resident->dob->format('d M Y') : '';
                                    $age = $hasDob ? $resident->dob->age : null;
                                    
                                    $statusCardClass = $statusClass;
                                    if ($status == 'NOT_PAID') $statusCardClass = 'not-paid';
                                @endphp
                                <div class="resident-card status-{{ $statusCardClass }}" 
                                     data-resident-id="{{ $resident->id }}"
                                     data-room-id="{{ $room->id }}"
                                     data-name="{{ strtolower($resident->name) }}"
                                     data-status="{{ $status }}"
                                     onclick="openPaymentModal(event, {{ $resident->id }})">
                                    
                                    <div class="resident-avatar color-{{ $colorIndex }}" id="avatar-{{ $resident->id }}">
                                        @if($hasProfileImage && $profileImageUrl)
                                            <img src="{{ $profileImageUrl }}" alt="{{ $resident->name }}">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>

                                    <div class="resident-info">
                                        <div class="name">{{ $resident->name }}</div>
                                        <div class="details">
                                            <span class="room-badge">#{{ $room->room_no }}-{{ $bed->bed_no }}</span>
                                            <span class="food-badge {{ $resident->food_status == 'WITH_FOOD' ? 'with-food' : 'without-food' }}">
                                                {{ $resident->food_status == 'WITH_FOOD' ? '🍽️' : '🍞' }}
                                            </span>
                                            <span class="rent-badge">₹{{ number_format($resident->rent_amount, 0) }}</span>
                                            @if($hasDob)
                                                <span class="dob-badge" title="DOB: {{ $dobFormatted }}{{ $age ? ' (Age: '.$age.')' : '' }}">
                                                    <i class="bi bi-calendar-heart"></i>
                                                    {{ $dobFormatted }}
                                                    @if($age)
                                                        ({{ $age }})
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <span class="status-badge {{ $statusClass }}">
                                        <span class="dot"></span>
                                        {{ $statusLabel }}
                                        @if($status == 'PARTIAL')
                                            (₹{{ number_format($balance, 0) }})
                                        @endif
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h5>No residents found</h5>
                    <p class="text-muted">No active residents in this hostel.</p>
                </div>
            @endif
        </div>

        <!-- No Results -->
        <div class="no-results" id="noResults" style="display:none;">
            <i class="bi bi-search"></i>
            <h5>No matching residents</h5>
            <p class="text-muted">Try adjusting your search or filters</p>
            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Clear
            </button>
        </div>

        <div class="text-center mt-3 mb-4">
            <small class="text-muted">
                <i class="bi bi-shield-check"></i> Click on any resident to record payment
            </small>
        </div>
    </div>

    <!-- ===== PAYMENT MODAL ===== -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-credit-card"></i> Pay Rent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading payment details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var csrfToken = '{{ csrf_token() }}';
        var hostelId = '{{ $hostel->id }}';

        var routes = {
            details: '{{ route("guest.payment.details") }}',
            manual: '{{ route("guest.payment.manual") }}',
            history: '{{ url("/guest/payment/history") }}',
            updateProfileImage: '{{ route("guest.resident.update-profile-image") }}',
            removeProfileImage: '{{ route("guest.resident.remove-profile-image") }}',
            updateDob: '{{ route("guest.resident.update-dob") }}'
        };

        let paymentModal;
        let currentResident = null;
        let selectedMethod = 'cash';

        $(document).ready(function() {
            paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            updateStats();

            $('#searchInput').on('keyup', function(e) {
                if (e.key === 'Enter') filterResidents();
            });
        });

        // ============================================
        // FILTER FUNCTIONS
        // ============================================

        function filterResidents() {
            const search = $('#searchInput').val().toLowerCase().trim();
            const statusFilter = $('#statusFilter').val();
            const roomFilter = $('#roomFilter').val();

            let visibleCount = 0;
            let totalCount = 0;
            let paidCount = 0, partialCount = 0, pendingCount = 0;

            $('.resident-card').each(function() {
                const $item = $(this);
                const name = $item.data('name') || '';
                const status = $item.data('status') || '';
                const roomId = String($item.data('room-id') || '');

                let show = true;

                if (search) {
                    const text = $item.text().toLowerCase();
                    if (!text.includes(search) && !name.includes(search)) show = false;
                }

                if (show && statusFilter) {
                    if (statusFilter === 'NOT_PAID' && status !== 'NOT_PAID') show = false;
                    if (statusFilter !== 'NOT_PAID' && status !== statusFilter) show = false;
                }

                if (show && roomFilter) {
                    if (roomId !== roomFilter) show = false;
                }

                totalCount++;

                if (show) {
                    $item.show();
                    visibleCount++;
                    if (status === 'PAID') paidCount++;
                    else if (status === 'PARTIAL') partialCount++;
                    else if (status === 'PENDING' || status === 'NOT_PAID') pendingCount++;
                } else {
                    $item.hide();
                }
            });

            $('#resultCount').text(visibleCount === totalCount ? 'All' : visibleCount + '/' + totalCount);
            $('#noResults').toggle(visibleCount === 0 && totalCount > 0);
            $('#paidCount').text(paidCount);
            $('#partialCount').text(partialCount);
            $('#pendingCount').text(pendingCount);
        }

        function clearFilters() {
            $('#searchInput').val('');
            $('#statusFilter').val('');
            $('#roomFilter').val('');
            filterResidents();
        }

        function updateStats() {
            let paid = 0, partial = 0, pending = 0;
            $('.resident-card').each(function() {
                const status = $(this).data('status') || '';
                if (status === 'PAID') paid++;
                else if (status === 'PARTIAL') partial++;
                else if (status === 'PENDING' || status === 'NOT_PAID') pending++;
            });
            $('#paidCount').text(paid);
            $('#partialCount').text(partial);
            $('#pendingCount').text(pending);
        }

        // ============================================
        // PAYMENT MODAL FUNCTIONS
        // ============================================

        function openPaymentModal(event, residentId) {
            event.preventDefault();
            event.stopPropagation();
            currentResident = { id: residentId };

            document.getElementById('paymentModalBody').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading payment details...</p>
                </div>
            `;

            paymentModal.show();

            $.ajax({
                url: routes.details,
                type: 'POST',
                data: {
                    resident_id: residentId,
                    hostel_id: hostelId,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        currentResident = response.data;
                        renderPaymentModal(currentResident);
                    } else {
                        showError('Failed to load payment details');
                    }
                },
                error: function() {
                    showError('Failed to load payment details');
                }
            });
        }

        function renderPaymentModal(data) {
            const amount = parseFloat(data.amount_to_pay || 0);
            const rent = parseFloat(data.rent_amount || 0);
            const isPaid = data.is_paid || false;

            const hasProfileImage = data.profile_image || data.profile_image_thumb;
            const profileImageUrl = data.profile_image || data.profile_image_thumb || '';
            const initials = data.name ? data.name.charAt(0).toUpperCase() : '?';

            const hasDob = data.dob_formatted && data.dob_formatted !== 'N/A';
            const dobDisplay = hasDob ? data.dob_formatted : '';
            const ageDisplay = data.age ? `(${data.age} years)` : '';

            const status = data.current_month_status || 'PENDING';
            const statusLower = status.toLowerCase();
            const statusLabel = status === 'NOT_PAID' ? 'Not Paid' : status;
            const isFullPaid = status === 'PAID' && amount <= 0;

            let statusBadgeClass = statusLower;
            if (status === 'NOT_PAID') statusBadgeClass = 'pending';

            let html = `
                <div class="mb-3">
                    <!-- ===== PROFILE IMAGE SECTION ===== -->
                    <div class="profile-image-section mb-3 text-center">
                        <div class="position-relative d-inline-block">
                            <div class="resident-avatar" id="modalProfileAvatar">
                                ${hasProfileImage ? 
                                    `<img id="modalProfileImage" src="${profileImageUrl}" alt="${data.name}">` :
                                    `<span id="modalProfileInitials" style="font-weight:700; font-size:1.8rem;">${initials}</span>`
                                }
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                                    style="width:30px; height:30px; padding:0; font-size:14px; border:2px solid white;" 
                                    onclick="document.getElementById('profileImageInput').click()" 
                                    title="Change Photo">
                                <i class="bi bi-camera"></i>
                            </button>
                            
                            ${hasProfileImage ? 
                                `<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" 
                                        style="width:24px; height:24px; padding:0; font-size:10px; border:2px solid white;" 
                                        onclick="removeProfileImage(${data.resident_id})" 
                                        title="Remove Photo">
                                    <i class="bi bi-x"></i>
                                </button>` : ''
                            }
                            
                            <input type="file" id="profileImageInput" accept="image/*" style="display:none" 
                                   onchange="uploadProfileImage(event, ${data.resident_id})">
                        </div>
                        <div style="font-size:0.65rem; color:#6b7280; margin-top:0.25rem;">
                            <i class="bi bi-info-circle"></i> Click camera icon to change photo
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                        <div>
                            <h5 class="mb-0">${data.name}</h5>
                            <div class="text-muted" style="font-size:0.8rem;">
                                <i class="bi bi-door-open"></i> Room #${data.room_no} • 
                                <i class="bi bi-bed"></i> Bed #${data.bed_no}
                            </div>
                            ${hasDob ? `
                                <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">
                                    <i class="bi bi-calendar-heart" style="color:#ec4899;"></i> 
                                    ${dobDisplay} ${ageDisplay}
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1" style="font-size:0.65rem; text-decoration:none;" 
                                            onclick="editDob(${data.resident_id})" title="Edit DOB">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            ` : `
                                <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">
                                    <span style="color:#9ca3af;">No DOB set</span>
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1" style="font-size:0.65rem; text-decoration:none;" 
                                            onclick="editDob(${data.resident_id})" title="Add DOB">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </div>
                            `}
                        </div>
                        <div class="status-badge-large ${statusBadgeClass}">
                            <span class="dot"></span>
                            ${isFullPaid ? '✅ PAID' : statusLabel}
                            ${status === 'PARTIAL' ? ` (₹${data.balance || 0})` : ''}
                        </div>
                    </div>

                    <div class="payment-detail"><span class="label"><i class="bi bi-phone"></i> Phone</span><span class="value">${data.phone}</span></div>
                    <div class="payment-detail"><span class="label"><i class="bi bi-envelope"></i> Email</span><span class="value">${data.email || 'Not provided'}</span></div>
                    <div class="payment-detail" style="border-top:2px solid #e5e7eb; padding-top:0.4rem; margin-top:0.4rem;">
                        <span class="label"><i class="bi bi-currency-rupee"></i> Monthly Rent</span>
                        <span class="value">₹${rent.toFixed(2)}</span>
                    </div>
                    ${data.discount > 0 ? `<div class="payment-detail" style="color:#065f46;"><span class="label"><i class="bi bi-tag"></i> Discount</span><span class="value" style="color:#065f46;">- ₹${data.discount.toFixed(2)}</span></div>` : ''}
                    ${data.fine_amount > 0 ? `<div class="payment-detail" style="color:#991b1b;"><span class="label"><i class="bi bi-clock"></i> Late Fee</span><span class="value" style="color:#991b1b;">+ ₹${data.fine_amount.toFixed(2)}</span></div>` : ''}
                    <div class="payment-detail" style="border-top:2px solid var(--gold); padding-top:0.4rem; margin-top:0.4rem;">
                        <span class="label"><strong>Total to Pay</strong></span>
                        <span class="value ${amount > 0 ? 'due' : 'clear'}" style="font-size:1.1rem;">
                            ${amount > 0 ? '₹' + amount.toFixed(2) : '✅ All Paid'}
                        </span>
                    </div>
                    ${data.discount_message ? `<div class="alert alert-info mt-2" style="font-size:0.7rem; padding:0.3rem 0.6rem;"><i class="bi bi-info-circle"></i> ${data.discount_message}</div>` : ''}
                    ${data.fine_message ? `<div class="alert alert-danger mt-2" style="font-size:0.7rem; padding:0.3rem 0.6rem;"><i class="bi bi-exclamation-circle"></i> ${data.fine_message}</div>` : ''}
                    ${data.has_pending ? `<div class="alert alert-warning mt-2" style="font-size:0.75rem; padding:0.3rem 0.6rem;"><i class="bi bi-exclamation-triangle-fill"></i> ${data.pending_count} pending payment(s) from previous months</div>` : ''}
                </div>
            `;

            if (!isPaid && amount > 0) {
                html += `
                <hr>
                <h6 class="mb-2"><i class="bi bi-pencil-square"></i> Enter Payment Details</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">Amount (₹)</label>
                        <input type="number" id="paymentAmount" class="form-control" 
                               value="${amount.toFixed(2)}" step="0.01" min="0.01" max="${amount}"
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.85rem;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">Payment Method</label>
                        <div class="payment-method-group">
                            <div class="payment-method-option selected" data-method="cash" onclick="selectMethod(this)"><i class="bi bi-cash"></i> Cash</div>
                            <div class="payment-method-option" data-method="upi" onclick="selectMethod(this)"><i class="bi bi-phone"></i> UPI</div>
                            <div class="payment-method-option" data-method="card" onclick="selectMethod(this)"><i class="bi bi-credit-card"></i> Card</div>
                            <div class="payment-method-option" data-method="bank_transfer" onclick="selectMethod(this)"><i class="bi bi-bank"></i> Bank</div>
                        </div>
                    </div>
                </div>
                
                <!-- ===== TRANSACTION ID FIELD ===== -->
                <div class="row g-2 mt-1" id="transactionIdRow" style="display:none;">
                    <div class="col-12">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">
                            <i class="bi bi-receipt"></i> Transaction ID <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="transactionId" class="form-control" 
                               placeholder="Enter UPI/Card/Bank transaction ID" 
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.8rem;">
                        <small class="text-muted" style="font-size:0.6rem;">
                            <i class="bi bi-info-circle"></i> Required for UPI, Card, and Bank Transfer payments
                        </small>
                    </div>
                </div>
                
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">Receipt No</label>
                        <input type="text" id="paymentReference" class="form-control" 
                               value="${data.reference || 'PAY-' + Date.now()}" 
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.8rem;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">Remarks</label>
                        <input type="text" id="paymentRemarks" class="form-control" placeholder="Notes..." 
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.8rem;">
                    </div>
                </div>
                <button class="btn-submit-payment mt-3" id="submitPaymentBtn" onclick="submitPayment()">
                    <i class="bi bi-check-circle"></i> Record Payment
                </button>
                `;
            } else {
                html += `
                <div class="text-center py-2">
                    <div style="font-size:2rem;">✅</div>
                    <h5>This month's rent is fully paid!</h5>
                    <button class="btn btn-secondary btn-sm mt-2" data-bs-dismiss="modal">Close</button>
                </div>
                `;
            }

            if (data.payment_history && data.payment_history.length > 0) {
                html += `
                <hr>
                <h6 class="mb-2"><i class="bi bi-clock-history"></i> Payment History</h6>
                <div class="table-responsive">
                    <table class="table table-sm payment-history-table">
                        <thead><tr><th>Month</th><th>Rent</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                        <tbody>
                            ${data.payment_history.map(p => `
                            <tr>
                                <td>${p.month}</td>
                                <td>₹${p.rent}</td>
                                <td>₹${p.paid}</td>
                                <td>₹${p.balance}</td>
                                <td><span class="badge-status ${p.status.toLowerCase()}">${p.status}</span></td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>`;
            }

            document.getElementById('paymentModalBody').innerHTML = html;
        }

        // ============================================
        // PAYMENT METHOD SELECTION WITH TRANSACTION ID
        // ============================================

        function selectMethod(el) {
            document.querySelectorAll('.payment-method-option').forEach(opt => opt.classList.remove('selected'));
            el.classList.add('selected');
            selectedMethod = el.dataset.method;
            
            const transactionRow = document.getElementById('transactionIdRow');
            const transactionInput = document.getElementById('transactionId');
            
            if (selectedMethod === 'upi' || selectedMethod === 'card' || selectedMethod === 'bank_transfer') {
                transactionRow.style.display = 'block';
                transactionInput.required = true;
                transactionInput.placeholder = selectedMethod === 'upi' ? 'Enter UPI transaction ID' : 
                                                selectedMethod === 'card' ? 'Enter card transaction ID' : 
                                                'Enter bank transaction ID';
            } else {
                transactionRow.style.display = 'none';
                transactionInput.required = false;
                transactionInput.value = '';
            }
        }

        // ============================================
        // SUBMIT PAYMENT WITH TRANSACTION ID
        // ============================================

        function submitPayment() {
            const amount = parseFloat(document.getElementById('paymentAmount').value);
            const reference = document.getElementById('paymentReference').value.trim();
            const remarks = document.getElementById('paymentRemarks').value.trim();
            const transactionId = document.getElementById('transactionId').value.trim();

            if (!amount || amount <= 0) {
                showToast('Please enter a valid amount', 'error');
                return;
            }
            if (!reference) {
                showToast('Please enter a reference number', 'error');
                return;
            }

            if (selectedMethod === 'upi' || selectedMethod === 'card' || selectedMethod === 'bank_transfer') {
                if (!transactionId) {
                    showToast('Please enter transaction ID for ' + selectedMethod.toUpperCase() + ' payment', 'error');
                    return;
                }
            }

            const btn = document.getElementById('submitPaymentBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Recording...';

            $.ajax({
                url: routes.manual,
                type: 'POST',
                data: {
                    resident_id: currentResident.id,
                    amount: amount,
                    payment_method: selectedMethod,
                    reference: reference,
                    transaction_id: transactionId,
                    remarks: remarks,
                    hostel_id: hostelId,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        const data = response.data;
                        const statusIcon = data.is_full_paid ? '✅' : (data.is_partial ? '⚠️' : '❌');

                        let transactionHtml = '';
                        if (data.transaction_id) {
                            transactionHtml = `
                                <div><span class="label">Transaction ID</span><br><span class="transaction-id-display">${data.transaction_id}</span></div>
                            `;
                        }

                        const statusClass = data.status.toLowerCase();

                        document.getElementById('paymentModalBody').innerHTML = `
                            <div class="text-center py-4">
                                <div style="font-size:2.5rem;">${statusIcon}</div>
                                <h5>${data.status_message}</h5>
                                <div class="payment-detail" style="justify-content:center; gap:1.5rem; flex-wrap:wrap; border:none;">
                                    <div><span class="label">Receipt</span><br><span class="value">${data.receipt_no}</span></div>
                                    <div><span class="label">Amount Paid</span><br><span class="value" style="color:var(--success);">₹${data.amount_paid.toFixed(2)}</span></div>
                                    <div><span class="label">Balance</span><br><span class="value ${data.balance > 0 ? 'due' : 'clear'}">₹${data.balance.toFixed(2)}</span></div>
                                    ${transactionHtml}
                                </div>
                                <div class="mt-2">
                                    <span class="status-badge-large ${statusClass}">
                                        <span class="dot"></span>
                                        ${data.status}
                                    </span>
                                </div>
                                <button class="btn btn-success mt-3" data-bs-dismiss="modal" onclick="location.reload()">
                                    <i class="bi bi-check-circle"></i> Done
                                </button>
                            </div>
                        `;
                    } else {
                        showToast(response.message || 'Failed to record payment', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-circle"></i> Record Payment';
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to record payment';
                    if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().join(', ');
                    }
                    showToast('❌ ' + message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Record Payment';
                }
            });
        }

        // ============================================
        // DOB EDIT FUNCTION
        // ============================================

        function editDob(residentId) {
            const currentDob = currentResident.dob || '';
            
            Swal.fire({
                title: 'Update Date of Birth',
                html: `
                    <div class="text-start">
                        <label class="form-label" style="font-size:0.85rem; font-weight:600;">Date of Birth</label>
                        <input type="date" id="dobInput" class="form-control" value="${currentDob}" max="${new Date().toISOString().split('T')[0]}">
                        <small class="text-muted" style="font-size:0.7rem;">Select the resident's date of birth</small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#c5a028',
                cancelButtonColor: '#6b7280',
                preConfirm: () => {
                    const dob = document.getElementById('dobInput').value;
                    return dob;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const dob = result.value;
                    
                    $.ajax({
                        url: routes.updateDob,
                        type: 'POST',
                        data: {
                            resident_id: residentId,
                            dob: dob,
                            _token: csrfToken
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('Date of Birth updated successfully!', 'success');
                                refreshModalContent(residentId);
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast(response.message || 'Failed to update DOB', 'error');
                            }
                        },
                        error: function(xhr) {
                            let message = 'Failed to update DOB';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                message = errors.join(', ');
                            }
                            showToast(message, 'error');
                        }
                    });
                }
            });
        }

        // ============================================
        // PROFILE IMAGE FUNCTIONS
        // ============================================

        function uploadProfileImage(event, residentId) {
            const file = event.target.files[0];
            if (!file) {
                showToast('Please select an image file', 'error');
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Please upload a valid image (JPEG, PNG, JPG, GIF)', 'error');
                event.target.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showToast('Image size should be less than 2MB', 'error');
                event.target.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('resident_id', residentId);
            formData.append('profile_image', file);
            formData.append('_token', csrfToken);

            const avatar = document.getElementById('modalProfileAvatar');
            if (avatar) {
                avatar.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>';
            }

            $.ajax({
                url: routes.updateProfileImage,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast('Profile image updated successfully!', 'success');
                        updateModalAvatar(response.data.profile_image);
                        updateResidentCardAvatar(residentId, response.data.profile_image);
                        refreshModalContent(residentId);
                    } else {
                        showToast(response.message || 'Failed to update profile image', 'error');
                        resetModalAvatar(initials);
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to update profile image';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join(', ');
                    }
                    showToast(message, 'error');
                    resetModalAvatar(initials);
                }
            });

            event.target.value = '';
        }

        function removeProfileImage(residentId) {
            if (!confirm('Are you sure you want to remove this profile image?')) {
                return;
            }

            $.ajax({
                url: routes.removeProfileImage,
                type: 'POST',
                data: {
                    resident_id: residentId,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Profile image removed successfully!', 'success');
                        resetModalAvatar(initials);
                        updateResidentCardAvatar(residentId, null);
                        refreshModalContent(residentId);
                    } else {
                        showToast(response.message || 'Failed to remove image', 'error');
                    }
                },
                error: function() {
                    showToast('Failed to remove profile image', 'error');
                }
            });
        }

        function updateModalAvatar(imageUrl) {
            const avatar = document.getElementById('modalProfileAvatar');
            if (avatar) {
                avatar.innerHTML = `<img src="${imageUrl}" alt="Profile" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
            }
        }

        function resetModalAvatar(initials) {
            const avatar = document.getElementById('modalProfileAvatar');
            if (avatar) {
                avatar.innerHTML = `<span style="font-weight:700; font-size:1.8rem;">${initials || '?'}</span>`;
            }
        }

        function updateResidentCardAvatar(residentId, imageUrl) {
            const avatarDiv = document.getElementById(`avatar-${residentId}`);
            if (avatarDiv) {
                if (imageUrl) {
                    avatarDiv.innerHTML = `<img src="${imageUrl}" alt="Profile">`;
                } else {
                    const card = $(`.resident-card[data-resident-id="${residentId}"]`);
                    const name = card.find('.name').text() || 'Unknown';
                    const initials = name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
                    avatarDiv.innerHTML = initials;
                }
            }
        }

        function refreshModalContent(residentId) {
            $.ajax({
                url: routes.details,
                type: 'POST',
                data: {
                    resident_id: residentId,
                    hostel_id: hostelId,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        currentResident = response.data;
                        renderPaymentModal(currentResident);
                    }
                }
            });
        }

        let initials = '?';

        const originalRender = renderPaymentModal;
        renderPaymentModal = function(data) {
            initials = data.name ? data.name.charAt(0).toUpperCase() : '?';
            originalRender(data);
        };

        // ============================================
        // TOAST / ERROR FUNCTIONS
        // ============================================

        function showError(message) {
            document.getElementById('paymentModalBody').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle" style="font-size:2rem;"></i>
                    <p class="mt-2">${message}</p>
                    <button class="btn btn-secondary btn-sm mt-2" data-bs-dismiss="modal">Close</button>
                </div>
            `;
        }

        function showToast(message, type) {
            const container = document.getElementById('toastContainer');
            const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
            const color = type === 'success' ? '#10b981' : '#dc2626';

            const toast = document.createElement('div');
            toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '');
            toast.innerHTML = `
                <i class="bi ${icon}" style="color:${color}; font-size:1.1rem;"></i>
                <div class="message">${message}</div>
                <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'slideOutRight 0.3s ease forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 6000);
        }
    </script>

</body>
</html>