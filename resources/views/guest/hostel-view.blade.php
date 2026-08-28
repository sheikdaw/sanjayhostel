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
            
            --status-paid: #22c55e;
            --status-paid-bg: #dcfce7;
            --status-partial: #f59e0b;
            --status-partial-bg: #fef3c7;
            --status-pending: #ef4444;
            --status-pending-bg: #fee2e2;
        }

        * { box-sizing: border-box; }
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .hostel-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 0.6rem 0;
            margin-bottom: 0.5rem;
            border-radius: 0 0 16px 16px;
            flex-shrink: 0;
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

        .container-fluid {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
            padding-bottom: 0.4rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            flex-shrink: 0;
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

        .search-section {
            background: white;
            padding: 0.4rem 0.75rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-bottom: 0.5rem;
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
            flex-shrink: 0;
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

        /* Language Toggle Button */
        .lang-toggle {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            white-space: nowrap;
        }
        .lang-toggle:hover {
            background: rgba(255,255,255,0.35);
            border-color: rgba(255,255,255,0.5);
        }
        .lang-toggle i {
            margin-right: 4px;
        }

        #residentsContainer {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .resident-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.5rem;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .resident-card {
            border-radius: 14px;
            border: none;
            padding: 0.6rem 0.4rem;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.35rem;
            position: relative;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        .resident-card.card-color-0 { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .resident-card.card-color-1 { background: linear-gradient(135deg, #60a5fa, #2563eb); }
        .resident-card.card-color-2 { background: linear-gradient(135deg, #34d399, #059669); }
        .resident-card.card-color-3 { background: linear-gradient(135deg, #f472b6, #db2777); }
        .resident-card.card-color-4 { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
        .resident-card.card-color-5 { background: linear-gradient(135deg, #fb923c, #ea580c); }
        .resident-card.card-color-6 { background: linear-gradient(135deg, #22d3ee, #0891b2); }
        .resident-card.card-color-7 { background: linear-gradient(135deg, #4ade80, #16a34a); }
        .resident-card.card-color-8 { background: linear-gradient(135deg, #38bdf8, #0284c7); }
        .resident-card.card-color-9 { background: linear-gradient(135deg, #facc15, #ca8a04); }

        .resident-card:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0,0,0,0.22);
            z-index: 2;
        }

        .status-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.35);
        }
        .status-dot.paid { background: var(--status-paid); }
        .status-dot.partial { background: var(--status-partial); }
        .status-dot.pending, .status-dot.not-paid {
            background: var(--status-pending);
            animation: pulse 2s infinite;
        }

        .resident-avatar {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.95);
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
            overflow: hidden;
            border: 3px solid rgba(255,255,255,0.6);
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
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
            width: 100%;
            min-width: 0;
        }

        .resident-info .name {
            font-weight: 700;
            font-size: 0.8rem;
            color: white;
            text-shadow: 0 1px 3px rgba(0,0,0,0.35);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
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

        .profile-image-section .resident-avatar {
            transition: all 0.3s ease;
            width: 300px;
            height: 300px;
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

        /* Tamil font support */
        .lang-ta {
            font-family: 'Noto Sans Tamil', 'Segoe UI', Tahoma, sans-serif;
        }

        .lang-ta .name,
        .lang-ta .resident-info .name {
            font-family: 'Noto Sans Tamil', 'Segoe UI', Tahoma, sans-serif;
        }

        @media (max-width: 768px) {
            .search-section { flex-direction: column; align-items: stretch; }
            .search-section .filter-group { justify-content: center; }
            .resident-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
            .resident-card { padding: 0.5rem; }
            .resident-avatar { width: 52px; height: 52px; font-size: 0.9rem; }
            .resident-info .name { font-size: 0.7rem; }
            .profile-image-section .resident-avatar { width: 60px; height: 60px; font-size: 1.2rem; }
            .payment-method-group { grid-template-columns: repeat(2, 1fr); }
            .status-badge { font-size: 0.5rem; padding: 0.1rem 0.3rem; }
            .lang-toggle { font-size: 0.6rem; padding: 0.15rem 0.6rem; }
        }

        @media (max-width: 480px) {
            .resident-grid { grid-template-columns: 1fr 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .hostel-header h1 { font-size: 1.2rem; }
            .profile-image-section .resident-avatar { width: 50px; height: 50px; font-size: 1rem; }
            .payment-method-group { grid-template-columns: 1fr 1fr; }
            .status-badge { font-size: 0.45rem; padding: 0.05rem 0.25rem; }
            .lang-toggle { font-size: 0.55rem; padding: 0.1rem 0.5rem; }
        }
        .transaction-id-display {
            font-size: 0.75rem;
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 2px 10px;
            border-radius: 4px;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            word-break: break-all;
            display: inline-block;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <div class="toast-container" id="toastContainer"></div>

    <div class="hostel-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-2">
                        <span class="hostel-icon"><i class="bi bi-building"></i></span>
                        <div>
                            <h1>{{ $hostel->hostel_name }}</h1>
                            <span class="hostel-code">{{ $hostel->hostel_code ?? 'HOSTEL' }}</span>
                            <p class="sub-text" id="clickHintText">
                                <i class="bi bi-geo-alt"></i> Click on any resident to pay
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-warning text-dark me-2">
                        <i class="bi bi-shield-lock"></i> <span id="secureText">Secure</span>
                    </span>
                    <button class="lang-toggle" onclick="toggleLanguage()" id="langToggleBtn">
                        <i class="bi bi-translate"></i> <span id="langToggleText">தமிழ்</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">

        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <span class="icon">👨‍👩‍👧‍👦</span>
                <div class="number" id="totalCount">{{ $stats['total_residents'] }}</div>
                <div class="label" id="totalLabel">Total</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid var(--status-paid);">
                <span class="icon">✅</span>
                <div class="number" style="color:var(--success);" id="paidCount">0</div>
                <div class="label" id="paidLabel">Paid</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid var(--status-partial);">
                <span class="icon">⚠️</span>
                <div class="number" style="color:var(--warning);" id="partialCount">0</div>
                <div class="label" id="partialLabel">Partial</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid var(--status-pending);">
                <span class="icon">❌</span>
                <div class="number" style="color:var(--danger);" id="pendingCount">0</div>
                <div class="label" id="pendingLabel">Pending</div>
            </div>
        </div>

        <div class="search-section">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Search by name, room, phone..." onkeyup="filterResidents()">
            </div>
            <div class="filter-group">
                <select id="statusFilter" onchange="filterResidents()">
                    <option value="" id="statusAll">All Status</option>
                    <option value="PAID" style="color: var(--status-paid);">✅ Paid</option>
                    <option value="PARTIAL" style="color: var(--status-partial);">⚠️ Partial</option>
                    <option value="PENDING" style="color: var(--status-pending);">❌ Pending</option>
                    <option value="NOT_PAID" style="color: var(--status-pending);">🔴 Not Paid</option>
                </select>
                <select id="roomFilter" onchange="filterResidents()">
                    <option value="" id="roomAll">All Rooms</option>
                    @foreach($occupiedRooms as $room)
                        <option value="{{ $room->id }}">#{{ $room->room_no }}</option>
                    @endforeach
                </select>
                <span class="result-count" id="resultCount">All</span>
            </div>
        </div>

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
                                <div class="resident-card card-color-{{ $colorIndex }}" 
                                     data-resident-id="{{ $resident->id }}"
                                     data-room-id="{{ $room->id }}"
                                     data-name="{{ strtolower($resident->name) }}"
                                     data-status="{{ $status }}"
                                     onclick="openPaymentModal(event, {{ $resident->id }})">

                                    <span class="status-dot {{ $statusCardClass }}" title="{{ $statusLabel }}"></span>

                                    <div class="resident-avatar color-{{ $colorIndex }}" id="avatar-{{ $resident->id }}">
                                        @if($hasProfileImage && $profileImageUrl)
                                            <img src="{{ $profileImageUrl }}" alt="{{ $resident->name }}">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>

                                    <div class="resident-info">
                                        <div class="name">{{ $resident->name }}</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h5 id="noResidentsTitle">No residents found</h5>
                    <p class="text-muted" id="noResidentsDesc">No active residents in this hostel.</p>
                </div>
            @endif
        </div>

        <div class="no-results" id="noResults" style="display:none;">
            <i class="bi bi-search"></i>
            <h5 id="noMatchTitle">No matching residents</h5>
            <p class="text-muted" id="noMatchDesc">Try adjusting your search or filters</p>
            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> <span id="clearBtnText">Clear</span>
            </button>
        </div>

        <div class="text-center mt-3 mb-4">
            <small class="text-muted" id="footerHint">
                <i class="bi bi-shield-check"></i> Click on any resident to record payment
            </small>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPayTitle"><i class="bi bi-credit-card"></i> Pay Rent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted" id="loadingText">Loading payment details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        // ============================================
        // TRANSLATION SYSTEM
        // ============================================
        let currentLang = 'en'; // 'en' or 'ta'

        const translations = {
            en: {
                // Header
                clickHint: 'Click on any resident to pay',
                secure: 'Secure',
                langToggle: 'தமிழ்',
                
                // Stats
                total: 'Total',
                paid: 'Paid',
                partial: 'Partial',
                pending: 'Pending',
                
                // Search
                searchPlaceholder: 'Search by name, room, phone...',
                allStatus: 'All Status',
                allRooms: 'All Rooms',
                
                // No results
                noResidentsTitle: 'No residents found',
                noResidentsDesc: 'No active residents in this hostel.',
                noMatchTitle: 'No matching residents',
                noMatchDesc: 'Try adjusting your search or filters',
                clearBtn: 'Clear',
                footerHint: 'Click on any resident to record payment',
                
                // Modal
                payRent: 'Pay Rent',
                loading: 'Loading payment details...',
                phone: 'Phone',
                email: 'Email',
                monthlyRent: 'Monthly Rent',
                discount: 'Discount',
                lateFee: 'Late Fee',
                totalToPay: 'Total to Pay',
                allPaid: '✅ All Paid',
                enterPaymentDetails: 'Enter Payment Details',
                amount: 'Amount (₹)',
                paymentMethod: 'Payment Method',
                cash: 'Cash',
                upi: 'UPI',
                card: 'Card',
                bank: 'Bank',
                transactionId: 'Transaction ID',
                transactionIdRequired: 'Required for UPI, Card, and Bank Transfer payments',
                receiptNo: 'Receipt No',
                remarks: 'Remarks',
                recordPayment: 'Record Payment',
                close: 'Close',
                paymentHistory: 'Payment History',
                month: 'Month',
                rent: 'Rent',
                paidAmt: 'Paid',
                balance: 'Balance',
                status: 'Status',
                done: 'Done',
                transactionIds: 'Transaction IDs',
                
                // Toast messages
                validAmount: 'Please enter a valid amount',
                validReference: 'Please enter a reference number',
                transactionRequired: 'Please enter transaction ID for {method} payment',
                recording: 'Recording...',
                failedPayment: 'Failed to record payment',
                
                // DOB
                updateDob: 'Update Date of Birth',
                dateOfBirth: 'Date of Birth',
                selectDob: 'Select the resident\'s date of birth',
                update: 'Update',
                cancel: 'Cancel',
                dobUpdated: 'Date of Birth updated successfully!',
                noDob: 'No DOB set',
                addDob: 'Add DOB',
                editDob: 'Edit DOB',
                
                // Profile image
                changePhoto: 'Change Photo',
                removePhoto: 'Remove Photo',
                clickCamera: 'Click camera icon to change photo',
                imageUpdated: 'Profile image updated successfully!',
                imageRemoved: 'Profile image removed successfully!',
                selectImage: 'Please select an image file',
                validImage: 'Please upload a valid image (JPEG, PNG, JPG, GIF)',
                imageSize: 'Image size should be less than 2MB',
                failedImage: 'Failed to update profile image',
                removeConfirm: 'Are you sure you want to remove this profile image?',
                
                // Payment status
                paidStatus: 'PAID',
                notPaid: 'Not Paid',
                partialStatus: 'PARTIAL',
                pendingStatus: 'PENDING',
                remainingBalance: 'Remaining Balance',
                pendingCount: 'pending payment(s) from previous months',
                
                // Filter options
                filterPaid: '✅ Paid',
                filterPartial: '⚠️ Partial',
                filterPending: '❌ Pending',
                filterNotPaid: '🔴 Not Paid',
            },
            ta: {
                // Header
                clickHint: 'குடியிருப்பாளரைக் கிளிக் செய்து கட்டணம் செலுத்தவும்',
                secure: 'பாதுகாப்பானது',
                langToggle: 'English',
                
                // Stats
                total: 'மொத்தம்',
                paid: 'செலுத்தப்பட்டது',
                partial: 'பகுதி',
                pending: 'நிலுவையில்',
                
                // Search
                searchPlaceholder: 'பெயர், அறை, தொலைபேசி மூலம் தேடுக...',
                allStatus: 'அனைத்து நிலைகள்',
                allRooms: 'அனைத்து அறைகள்',
                
                // No results
                noResidentsTitle: 'குடியிருப்பாளர்கள் எதுவும் இல்லை',
                noResidentsDesc: 'இந்த விடுதியில் செயலில் உள்ள குடியிருப்பாளர்கள் இல்லை.',
                noMatchTitle: 'பொருந்தும் குடியிருப்பாளர்கள் இல்லை',
                noMatchDesc: 'உங்கள் தேடல் அல்லது வடிகட்டிகளை மாற்றி முயற்சிக்கவும்',
                clearBtn: 'அழி',
                footerHint: 'கட்டணம் பதிவு செய்ய எந்த குடியிருப்பாளரையும் கிளிக் செய்க',
                
                // Modal
                payRent: 'வாடகை செலுத்துக',
                loading: 'கட்டண விவரங்களை ஏற்றுகிறது...',
                phone: 'தொலைபேசி',
                email: 'மின்னஞ்சல்',
                monthlyRent: 'மாத வாடகை',
                discount: 'தள்ளுபடி',
                lateFee: 'தாமதக் கட்டணம்',
                totalToPay: 'செலுத்த வேண்டிய மொத்தம்',
                allPaid: '✅ அனைத்தும் செலுத்தப்பட்டது',
                enterPaymentDetails: 'கட்டண விவரங்களை உள்ளிடுக',
                amount: 'தொகை (₹)',
                paymentMethod: 'கட்டண முறை',
                cash: 'பணம்',
                upi: 'யுபிஐ',
                card: 'அட்டை',
                bank: 'வங்கி',
                transactionId: 'பரிவர்த்தனை ஐடி',
                transactionIdRequired: 'UPI, அட்டை மற்றும் வங்கி பரிவர்த்தனைகளுக்கு தேவை',
                receiptNo: 'ரசீது எண்',
                remarks: 'குறிப்புகள்',
                recordPayment: 'கட்டணம் பதிவு செய்க',
                close: 'மூடு',
                paymentHistory: 'கட்டண வரலாறு',
                month: 'மாதம்',
                rent: 'வாடகை',
                paidAmt: 'செலுத்தப்பட்டது',
                balance: 'இருப்பு',
                status: 'நிலை',
                done: 'முடிந்தது',
                transactionIds: 'பரிவர்த்தனை ஐடிகள்',
                
                // Toast messages
                validAmount: 'சரியான தொகையை உள்ளிடவும்',
                validReference: 'ரசீது எண்ணை உள்ளிடவும்',
                transactionRequired: '{method} கட்டணத்திற்கு பரிவர்த்தனை ஐடி உள்ளிடவும்',
                recording: 'பதிவு செய்கிறது...',
                failedPayment: 'கட்டணம் பதிவு செய்யத் தவறியது',
                
                // DOB
                updateDob: 'பிறந்த தேதியை புதுப்பிக்கவும்',
                dateOfBirth: 'பிறந்த தேதி',
                selectDob: 'குடியிருப்பாளரின் பிறந்த தேதியைத் தேர்ந்தெடுக்கவும்',
                update: 'புதுப்பி',
                cancel: 'ரத்து செய்',
                dobUpdated: 'பிறந்த தேதி வெற்றிகரமாக புதுப்பிக்கப்பட்டது!',
                noDob: 'பிறந்த தேதி இல்லை',
                addDob: 'பிறந்த தேதி சேர்',
                editDob: 'பிறந்த தேதி திருத்து',
                
                // Profile image
                changePhoto: 'புகைப்படம் மாற்று',
                removePhoto: 'புகைப்படம் அகற்று',
                clickCamera: 'புகைப்படம் மாற்ற கேமரா ஐகானை கிளிக் செய்க',
                imageUpdated: 'சுயவிவர புகைப்படம் வெற்றிகரமாக புதுப்பிக்கப்பட்டது!',
                imageRemoved: 'சுயவிவர புகைப்படம் வெற்றிகரமாக அகற்றப்பட்டது!',
                selectImage: 'படக் கோப்பைத் தேர்ந்தெடுக்கவும்',
                validImage: 'சரியான படத்தைப் பதிவேற்றவும் (JPEG, PNG, JPG, GIF)',
                imageSize: 'படத்தின் அளவு 2MBக்கும் குறைவாக இருக்க வேண்டும்',
                failedImage: 'சுயவிவர புகைப்படத்தை புதுப்பிக்க தவறியது',
                removeConfirm: 'இந்த சுயவிவர புகைப்படத்தை அகற்ற விரும்புகிறீர்களா?',
                
                // Payment status
                paidStatus: 'செலுத்தப்பட்டது',
                notPaid: 'செலுத்தப்படவில்லை',
                partialStatus: 'பகுதி செலுத்தப்பட்டது',
                pendingStatus: 'நிலுவையில்',
                remainingBalance: 'மீதமுள்ள இருப்பு',
                pendingCount: 'முந்தைய மாதங்களில் நிலுவையில் உள்ள கட்டணம்(கள்)',
                
                // Filter options
                filterPaid: '✅ செலுத்தப்பட்டது',
                filterPartial: '⚠️ பகுதி செலுத்தப்பட்டது',
                filterPending: '❌ நிலுவையில்',
                filterNotPaid: '🔴 செலுத்தப்படவில்லை',
            }
        };

        function t(key, params = {}) {
            let text = translations[currentLang]?.[key] || translations.en[key] || key;
            // Replace placeholders like {method}
            Object.keys(params).forEach(k => {
                text = text.replace(new RegExp(`\\{${k}\\}`, 'g'), params[k]);
            });
            return text;
        }

        function toggleLanguage() {
            currentLang = currentLang === 'en' ? 'ta' : 'en';
            updateAllTexts();
            document.getElementById('langToggleText').textContent = t('langToggle');
            // Also update any dynamic content that might have been rendered
            if (currentResident) {
                refreshModalContent(currentResident.id);
            }
            // Refresh filter dropdown text
            updateFilterOptions();
        }

        function updateFilterOptions() {
            const statusFilter = document.getElementById('statusFilter');
            const options = statusFilter.options;
            if (options.length >= 5) {
                options[0].text = t('allStatus');
                options[1].text = t('filterPaid');
                options[2].text = t('filterPartial');
                options[3].text = t('filterPending');
                options[4].text = t('filterNotPaid');
            }
            document.getElementById('roomAll').textContent = t('allRooms');
        }

        function updateAllTexts() {
            // Header
            document.getElementById('clickHintText').innerHTML = `<i class="bi bi-geo-alt"></i> ${t('clickHint')}`;
            document.getElementById('secureText').textContent = t('secure');
            
            // Stats
            document.getElementById('totalLabel').textContent = t('total');
            document.getElementById('paidLabel').textContent = t('paid');
            document.getElementById('partialLabel').textContent = t('partial');
            document.getElementById('pendingLabel').textContent = t('pending');
            
            // Search
            document.getElementById('searchInput').placeholder = t('searchPlaceholder');
            updateFilterOptions();
            
            // No results
            document.getElementById('noResidentsTitle')?.textContent && (document.getElementById('noResidentsTitle').textContent = t('noResidentsTitle'));
            document.getElementById('noResidentsDesc')?.textContent && (document.getElementById('noResidentsDesc').textContent = t('noResidentsDesc'));
            document.getElementById('noMatchTitle')?.textContent && (document.getElementById('noMatchTitle').textContent = t('noMatchTitle'));
            document.getElementById('noMatchDesc')?.textContent && (document.getElementById('noMatchDesc').textContent = t('noMatchDesc'));
            document.getElementById('clearBtnText')?.textContent && (document.getElementById('clearBtnText').textContent = t('clearBtn'));
            
            // Footer
            document.getElementById('footerHint').innerHTML = `<i class="bi bi-shield-check"></i> ${t('footerHint')}`;
            
            // Modal
            document.getElementById('modalPayTitle').innerHTML = `<i class="bi bi-credit-card"></i> ${t('payRent')}`;
            document.getElementById('loadingText').textContent = t('loading');
            
            // Update document direction
            document.documentElement.lang = currentLang === 'ta' ? 'ta' : 'en';
            
            // Add Tamil font class if needed
            if (currentLang === 'ta') {
                document.body.classList.add('lang-ta');
            } else {
                document.body.classList.remove('lang-ta');
            }
        }

        // Override the renderPaymentModal function to use translations
        const originalRenderPaymentModal = window.renderPaymentModal || function() {};

        // We'll override the renderPaymentModal after the original is defined
        // But we need to make sure translations are applied to dynamic content

        let paymentModal;
        let currentResident = null;
        let selectedMethod = 'cash';

        $(document).ready(function() {
            paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            updateStats();
            fitResidentGrid();
            updateAllTexts();

            $('#searchInput').on('keyup', function(e) {
                if (e.key === 'Enter') filterResidents();
            });
        });

        let fitResizeTimer = null;
        $(window).on('resize', function() {
            clearTimeout(fitResizeTimer);
            fitResizeTimer = setTimeout(fitResidentGrid, 150);
        });

        // ============================================
        // FIT-TO-SCREEN GRID
        // ============================================
        function fitResidentGrid() {
            const container = document.getElementById('residentsContainer');
            const grid = document.getElementById('residentGrid');
            if (!grid || !container) return;

            const cards = Array.from(grid.querySelectorAll('.resident-card'))
                .filter(c => c.style.display !== 'none');
            const count = cards.length;
            if (count === 0) return;

            const availWidth = container.clientWidth;
            const availHeight = container.clientHeight;
            if (availWidth <= 0 || availHeight <= 0) return;

            const cardAspect = 0.82;
            let bestCols = 1;
            let bestScale = -Infinity;
            for (let cols = 1; cols <= count; cols++) {
                const rows = Math.ceil(count / cols);
                const cellW = availWidth / cols;
                const cellH = availHeight / rows;
                const scale = Math.min(cellW / cardAspect, cellH);
                if (scale > bestScale) {
                    bestScale = scale;
                    bestCols = cols;
                }
            }
            const rows = Math.ceil(count / bestCols);

            grid.style.gridTemplateColumns = `repeat(${bestCols}, 1fr)`;
            grid.style.gridTemplateRows = `repeat(${rows}, 1fr)`;
            grid.style.gridAutoFlow = 'row';

            const cellW = availWidth / bestCols;
            const cellH = availHeight / rows;
            const minCell = Math.min(cellW, cellH);

            const avatarSize = Math.max(24, Math.min(120, minCell * 0.42));
            const fontSize = Math.max(8, Math.min(24, minCell * 0.10));
            const dotSize = Math.max(8, Math.min(16, minCell * 0.09));

            cards.forEach(card => {
                const avatar = card.querySelector('.resident-avatar');
                const name = card.querySelector('.name');
                const dot = card.querySelector('.status-dot');
                if (avatar) {
                    avatar.style.width = avatarSize + 'px';
                    avatar.style.height = avatarSize + 'px';
                    avatar.style.fontSize = (avatarSize * 0.32) + 'px';
                }
                if (name) {
                    name.style.fontSize = fontSize + 'px';
                }
                if (dot) {
                    dot.style.width = dotSize + 'px';
                    dot.style.height = dotSize + 'px';
                }
            });
        }

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
            fitResidentGrid();
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
            
            currentResident = { 
                id: residentId,
                resident_id: residentId
            };

            document.getElementById('paymentModalBody').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted" id="loadingText">${t('loading')}</p>
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
                        currentResident = {
                            ...response.data,
                            id: residentId,
                            resident_id: residentId
                        };
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
            const balance = parseFloat(data.balance || 0);

            const hasProfileImage = data.profile_image || data.profile_image_thumb;
            const profileImageUrl = data.profile_image || data.profile_image_thumb || '';
            const initials = data.name ? data.name.charAt(0).toUpperCase() : '?';

            const hasDob = data.dob_formatted && data.dob_formatted !== 'N/A';
            const dobDisplay = hasDob ? data.dob_formatted : '';
            const ageDisplay = data.age ? `(${data.age} years)` : '';

            const status = data.current_month_status || 'PENDING';
            const statusLower = status.toLowerCase();
            const statusLabel = status === 'NOT_PAID' ? t('notPaid') : 
                               status === 'PAID' ? t('paidStatus') : 
                               status === 'PARTIAL' ? t('partialStatus') : t('pendingStatus');
            const isFullPaid = status === 'PAID' && amount <= 0;

            let statusBadgeClass = statusLower;
            if (status === 'NOT_PAID') statusBadgeClass = 'pending';

            let balanceDisplay = '';
            if (status === 'PARTIAL' && balance > 0) {
                balanceDisplay = `<div class="payment-detail" style="color:#92400e; border-bottom: 2px solid var(--status-partial);">
                    <span class="label"><i class="bi bi-currency-rupee"></i> ${t('remainingBalance')}</span>
                    <span class="value" style="color:var(--warning); font-weight:700;">₹${balance.toFixed(2)}</span>
                </div>`;
            }

            let html = `
                <div class="mb-3">
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
                                    title="${t('changePhoto')}">
                                <i class="bi bi-camera"></i>
                            </button>
                            
                            ${hasProfileImage ? 
                                `<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" 
                                        style="width:24px; height:24px; padding:0; font-size:10px; border:2px solid white;" 
                                        onclick="removeProfileImage(${data.resident_id})" 
                                        title="${t('removePhoto')}">
                                    <i class="bi bi-x"></i>
                                </button>` : ''
                            }
                            
                            <input type="file" id="profileImageInput" accept="image/*" style="display:none" 
                                   onchange="uploadProfileImage(event, ${data.resident_id})">
                        </div>
                        <div style="font-size:0.65rem; color:#6b7280; margin-top:0.25rem;">
                            <i class="bi bi-info-circle"></i> ${t('clickCamera')}
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
                                            onclick="editDob(${data.resident_id})" title="${t('editDob')}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            ` : `
                                <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">
                                    <span style="color:#9ca3af;">${t('noDob')}</span>
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1" style="font-size:0.65rem; text-decoration:none;" 
                                            onclick="editDob(${data.resident_id})" title="${t('addDob')}">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </div>
                            `}
                        </div>
                        <div class="status-badge-large ${statusBadgeClass}">
                            <span class="dot"></span>
                            ${isFullPaid ? '✅ ' + t('paidStatus') : statusLabel}
                            ${status === 'PARTIAL' ? ` (₹${balance.toFixed(0)})` : ''}
                        </div>
                    </div>

                    <div class="payment-detail"><span class="label"><i class="bi bi-phone"></i> ${t('phone')}</span><span class="value">${data.phone}</span></div>
                    <div class="payment-detail"><span class="label"><i class="bi bi-envelope"></i> ${t('email')}</span><span class="value">${data.email || 'Not provided'}</span></div>
                    <div class="payment-detail" style="border-top:2px solid #e5e7eb; padding-top:0.4rem; margin-top:0.4rem;">
                        <span class="label"><i class="bi bi-currency-rupee"></i> ${t('monthlyRent')}</span>
                        <span class="value">₹${rent.toFixed(2)}</span>
                    </div>
                    ${data.discount > 0 ? `<div class="payment-detail" style="color:#065f46;"><span class="label"><i class="bi bi-tag"></i> ${t('discount')}</span><span class="value" style="color:#065f46;">- ₹${data.discount.toFixed(2)}</span></div>` : ''}
                    ${data.fine_amount > 0 ? `<div class="payment-detail" style="color:#991b1b;"><span class="label"><i class="bi bi-clock"></i> ${t('lateFee')}</span><span class="value" style="color:#991b1b;">+ ₹${data.fine_amount.toFixed(2)}</span></div>` : ''}
                    ${balanceDisplay}
                    <div class="payment-detail" style="border-top:2px solid var(--gold); padding-top:0.4rem; margin-top:0.4rem;">
                        <span class="label"><strong>${t('totalToPay')}</strong></span>
                        <span class="value ${amount > 0 ? 'due' : 'clear'}" style="font-size:1.1rem;">
                            ${amount > 0 ? '₹' + amount.toFixed(2) : '✅ ' + t('allPaid')}
                        </span>
                    </div>
                    ${data.discount_message ? `<div class="alert alert-info mt-2" style="font-size:0.7rem; padding:0.3rem 0.6rem;"><i class="bi bi-info-circle"></i> ${data.discount_message}</div>` : ''}
                    ${data.fine_message ? `<div class="alert alert-danger mt-2" style="font-size:0.7rem; padding:0.3rem 0.6rem;"><i class="bi bi-exclamation-circle"></i> ${data.fine_message}</div>` : ''}
                    ${data.has_pending ? `<div class="alert alert-warning mt-2" style="font-size:0.75rem; padding:0.3rem 0.6rem;"><i class="bi bi-exclamation-triangle-fill"></i> ${data.pending_count} ${t('pendingCount')}</div>` : ''}
                </div>
            `;

            if (!isPaid && amount > 0) {
                // Get translated payment method names
                const methodCash = t('cash');
                const methodUpi = t('upi');
                const methodCard = t('card');
                const methodBank = t('bank');
                
                html += `
                <hr>
                <h6 class="mb-2"><i class="bi bi-pencil-square"></i> ${t('enterPaymentDetails')}</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">${t('amount')}</label>
                        <input type="number" id="paymentAmount" class="form-control" 
                               value="${amount.toFixed(2)}" step="0.01" min="0.01" max="${amount}"
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.85rem;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">${t('paymentMethod')}</label>
                        <div class="payment-method-group">
                            <div class="payment-method-option selected" data-method="cash" onclick="selectMethod(this)"><i class="bi bi-cash"></i> ${methodCash}</div>
                            <div class="payment-method-option" data-method="upi" onclick="selectMethod(this)"><i class="bi bi-phone"></i> ${methodUpi}</div>
                            <div class="payment-method-option" data-method="card" onclick="selectMethod(this)"><i class="bi bi-credit-card"></i> ${methodCard}</div>
                            <div class="payment-method-option" data-method="bank_transfer" onclick="selectMethod(this)"><i class="bi bi-bank"></i> ${methodBank}</div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-2 mt-1" id="transactionIdRow" style="display:none;">
                    <div class="col-12">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">
                            <i class="bi bi-receipt"></i> ${t('transactionId')} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="transactionId" class="form-control" 
                               placeholder="${t('transactionIdRequired')}" 
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.8rem;">
                        <small class="text-muted" style="font-size:0.6rem;">
                            <i class="bi bi-info-circle"></i> ${t('transactionIdRequired')}
                        </small>
                    </div>
                </div>
                
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">${t('receiptNo')}</label>
                        <input type="text" id="paymentReference" class="form-control" 
                               value="${data.reference || 'PAY-' + Date.now()}" 
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.8rem;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600;">${t('remarks')}</label>
                        <input type="text" id="paymentRemarks" class="form-control" placeholder="${t('remarks')}..." 
                               style="border-radius:8px; border:1px solid #d1d5db; padding:0.4rem 0.6rem; font-size:0.8rem;">
                    </div>
                </div>
                <button class="btn-submit-payment mt-3" id="submitPaymentBtn" onclick="submitPayment()">
                    <i class="bi bi-check-circle"></i> ${t('recordPayment')}
                </button>
                `;
            } else {
                html += `
                <div class="text-center py-2">
                    <div style="font-size:2rem;">✅</div>
                    <h5>${t('allPaid')}</h5>
                    <button class="btn btn-secondary btn-sm mt-2" data-bs-dismiss="modal">${t('close')}</button>
                </div>
                `;
            }

            if (data.payment_history && data.payment_history.length > 0) {
                html += `
                <hr>
                <h6 class="mb-2"><i class="bi bi-clock-history"></i> ${t('paymentHistory')}</h6>
                <div class="table-responsive">
                    <table class="table table-sm payment-history-table">
                        <thead><tr><th>${t('month')}</th><th>${t('rent')}</th><th>${t('paidAmt')}</th><th>${t('balance')}</th><th>${t('status')}</th></tr></thead>
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
            
            // Update any translated text in the modal that was just rendered
            updateModalTranslations();
        }

        function updateModalTranslations() {
            // This will update any dynamic text that might have been rendered
            // We need to re-translate any text nodes that were created
            const modalBody = document.getElementById('paymentModalBody');
            if (!modalBody) return;
            
            // Update transaction placeholder if it exists
            const txnInput = document.getElementById('transactionId');
            if (txnInput) {
                const methodMap = {
                    'cash': t('cash'),
                    'upi': t('upi'),
                    'card': t('card'),
                    'bank_transfer': t('bank')
                };
                const method = selectedMethod || 'cash';
                const methodName = methodMap[method] || method;
                txnInput.placeholder = t('transactionIdRequired');
            }
        }

        function selectMethod(el) {
            document.querySelectorAll('.payment-method-option').forEach(opt => opt.classList.remove('selected'));
            el.classList.add('selected');
            selectedMethod = el.dataset.method;
            
            const transactionRow = document.getElementById('transactionIdRow');
            const transactionInput = document.getElementById('transactionId');
            
            if (selectedMethod === 'upi' || selectedMethod === 'card' || selectedMethod === 'bank_transfer') {
                transactionRow.style.display = 'block';
                transactionInput.required = true;
                const methodMap = {
                    'upi': t('upi'),
                    'card': t('card'),
                    'bank_transfer': t('bank')
                };
                const methodName = methodMap[selectedMethod] || selectedMethod;
                transactionInput.placeholder = t('transactionIdRequired');
            } else {
                transactionRow.style.display = 'none';
                transactionInput.required = false;
                transactionInput.value = '';
            }
        }

        // ============================================
        // SUBMIT PAYMENT
        // ============================================

        function submitPayment() {
            const amount = parseFloat(document.getElementById('paymentAmount').value);
            const reference = document.getElementById('paymentReference').value.trim();
            const remarks = document.getElementById('paymentRemarks').value.trim();
            const transactionId = document.getElementById('transactionId').value.trim();

            if (!amount || amount <= 0) {
                showToast(t('validAmount'), 'error');
                return;
            }
            if (!reference) {
                showToast(t('validReference'), 'error');
                return;
            }

            if (selectedMethod === 'upi' || selectedMethod === 'card' || selectedMethod === 'bank_transfer') {
                if (!transactionId) {
                    const methodMap = {
                        'upi': t('upi'),
                        'card': t('card'),
                        'bank_transfer': t('bank')
                    };
                    const methodName = methodMap[selectedMethod] || selectedMethod;
                    showToast(t('transactionRequired', { method: methodName.toUpperCase() }), 'error');
                    return;
                }
            }

            const btn = document.getElementById('submitPaymentBtn');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> ${t('recording')}`;

            const residentId = currentResident.id || currentResident.resident_id;

            $.ajax({
                url: routes.manual,
                type: 'POST',
                data: {
                    resident_id: residentId,
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
                            const txnIds = data.transaction_id.split(' / ');
                            if (txnIds.length > 1) {
                                transactionHtml = `
                                    <div><span class="label">${t('transactionIds')}</span><br>
                                        ${txnIds.map(id => `<span class="transaction-id-display" style="display:block; margin-top:2px;">${id}</span>`).join('')}
                                    </div>
                                `;
                            } else {
                                transactionHtml = `
                                    <div><span class="label">${t('transactionId')}</span><br><span class="transaction-id-display">${data.transaction_id}</span></div>
                                `;
                            }
                        }

                        const statusClass = data.status.toLowerCase();

                        document.getElementById('paymentModalBody').innerHTML = `
                            <div class="text-center py-4">
                                <div style="font-size:2.5rem;">${statusIcon}</div>
                                <h5>${data.status_message}</h5>
                                <div class="payment-detail" style="justify-content:center; gap:1.5rem; flex-wrap:wrap; border:none;">
                                    <div><span class="label">${t('receiptNo')}</span><br><span class="value">${data.receipt_no}</span></div>
                                    <div><span class="label">${t('paidAmt')}</span><br><span class="value" style="color:var(--success);">₹${data.amount_paid.toFixed(2)}</span></div>
                                    <div><span class="label">${t('balance')}</span><br><span class="value ${data.balance > 0 ? 'due' : 'clear'}">₹${data.balance.toFixed(2)}</span></div>
                                    ${transactionHtml}
                                </div>
                                <div class="mt-2">
                                    <span class="status-badge-large ${statusClass}">
                                        <span class="dot"></span>
                                        ${data.status}
                                    </span>
                                </div>
                                <button class="btn btn-success mt-3" data-bs-dismiss="modal" onclick="location.reload()">
                                    <i class="bi bi-check-circle"></i> ${t('done')}
                                </button>
                            </div>
                        `;
                    } else {
                        showToast(response.message || t('failedPayment'), 'error');
                        btn.disabled = false;
                        btn.innerHTML = `<i class="bi bi-check-circle"></i> ${t('recordPayment')}`;
                    }
                },
                error: function(xhr) {
                    let message = t('failedPayment');
                    if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join(', ');
                    }
                    showToast('❌ ' + message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-check-circle"></i> ${t('recordPayment')}`;
                }
            });
        }

        // ============================================
        // DOB EDIT FUNCTION
        // ============================================

        function editDob(residentId) {
            const currentDob = currentResident.dob || '';
            
            Swal.fire({
                title: t('updateDob'),
                html: `
                    <div class="text-start">
                        <label class="form-label" style="font-size:0.85rem; font-weight:600;">${t('dateOfBirth')}</label>
                        <input type="date" id="dobInput" class="form-control" value="${currentDob}" max="${new Date().toISOString().split('T')[0]}">
                        <small class="text-muted" style="font-size:0.7rem;">${t('selectDob')}</small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: t('update'),
                cancelButtonText: t('cancel'),
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
                                showToast(t('dobUpdated'), 'success');
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
                showToast(t('selectImage'), 'error');
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showToast(t('validImage'), 'error');
                event.target.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showToast(t('imageSize'), 'error');
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
                        showToast(t('imageUpdated'), 'success');
                        updateModalAvatar(response.data.profile_image);
                        updateResidentCardAvatar(residentId, response.data.profile_image);
                        refreshModalContent(residentId);
                    } else {
                        showToast(response.message || t('failedImage'), 'error');
                        resetModalAvatar(initials);
                    }
                },
                error: function(xhr) {
                    let message = t('failedImage');
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
            if (!confirm(t('removeConfirm'))) {
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
                        showToast(t('imageRemoved'), 'success');
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
                        currentResident = {
                            ...response.data,
                            id: residentId,
                            resident_id: residentId
                        };
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
                    <button class="btn btn-secondary btn-sm mt-2" data-bs-dismiss="modal">${t('close')}</button>
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

        // Initialize translations after page load
        $(document).ready(function() {
            updateAllTexts();
        });
    </script>

    <!-- Add Tamil font for better rendering -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;600;700&display=swap" rel="stylesheet">

</body>
</html>