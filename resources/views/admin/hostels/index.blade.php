@extends('layouts.office')

@section('title', 'Hostel Management')
@section('page_title', 'Hostel Management')

@push('styles')
    <style>
        /* ============================================
           GLOBAL STYLES
        ============================================ */
        :root {
            --primary: #1a3a6b;
            --primary-light: #2a5a9b;
            --gold: #c5a028;
            --gold-light: #f5e6b8;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }

        /* ============================================
           LAYOUT
        ============================================ */
        .hostel-container {
            max-width: 100%;
            padding: 0 15px;
        }

        /* ============================================
           HEADER
        ============================================ */
        .hostel-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .hostel-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .hostel-header p {
            opacity: 0.8;
            margin: 0;
            font-size: 0.9rem;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* ============================================
           STATS GRID
        ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: var(--gold);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .stat-card .number {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card .label {
            font-size: 0.6rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .icon {
            font-size: 1.2rem;
            display: block;
            margin-bottom: 0.25rem;
        }

        .stat-card.total .number { color: var(--primary); }
        .stat-card.active .number { color: var(--success); }
        .stat-card.inactive .number { color: var(--danger); }
        .stat-card.men .number { color: #3b82f6; }
        .stat-card.women .number { color: #ec4899; }
        .stat-card.rooms .number { color: #7c3aed; }
        .stat-card.beds .number { color: #92400e; }

        /* ============================================
           FILTER SECTION
        ============================================ */
        .filter-section {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-bottom: 1rem;
        }

        .filter-section .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-section select,
        .filter-section input {
            padding: 0.35rem 0.8rem;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 0.8rem;
            background: white;
            min-width: 120px;
        }

        .filter-section select:focus,
        .filter-section input:focus {
            border-color: var(--gold);
            outline: none;
            box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .search-box input {
            width: 100%;
            padding: 0.35rem 0.8rem 0.35rem 2rem;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 0.8rem;
            background: white;
        }

        .search-box i {
            position: absolute;
            left: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .result-count {
            font-size: 0.75rem;
            color: #6b7280;
            padding: 0.25rem 0.5rem;
            background: #f3f4f6;
            border-radius: 4px;
            margin-left: auto;
        }

        .btn-clear-filters {
            padding: 0.35rem 1rem;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: white;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-clear-filters:hover {
            background: #f3f4f6;
        }

        /* ============================================
           HOSTEL CARD
        ============================================ */
        .hostel-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            position: relative;
            height: 100%;
        }

        .hostel-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        .hostel-card .card-header {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hostel-card .card-header .hostel-type-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .hostel-card .card-body {
            padding: 1.25rem;
        }

        .hostel-card .card-body .hostel-code {
            font-size: 0.7rem;
            font-family: monospace;
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 4px;
            color: #6b7280;
        }

        .hostel-detail {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hostel-detail i {
            width: 18px;
            color: var(--gold);
        }

        .hostel-detail .label {
            color: #6b7280;
        }

        .hostel-detail .value {
            color: #1f2937;
            font-weight: 500;
        }

        .hostel-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin: 0.75rem 0;
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 8px;
        }

        .hostel-stats-row .stat-item {
            text-align: center;
        }

        .hostel-stats-row .stat-item .stat-number {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .hostel-stats-row .stat-item .stat-label {
            font-size: 0.6rem;
            color: #6b7280;
            text-transform: uppercase;
        }

        .hostel-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .hostel-actions .btn-sm {
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: white;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .hostel-actions .btn-sm:hover {
            background: #f3f4f6;
        }

        .hostel-actions .btn-sm.primary:hover {
            background: #e3f2fd;
            border-color: #90caf9;
        }

        .hostel-actions .btn-sm.success:hover {
            background: #dcfce7;
            border-color: #86efac;
        }

        .hostel-actions .btn-sm.warning:hover {
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .hostel-actions .btn-sm.danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .hostel-actions .btn-sm.purple:hover {
            background: #ede9fe;
            border-color: #c4b5fd;
        }

        /* ============================================
           BADGES
        ============================================ */
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

        .status-badge:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-badge.active .dot {
            background: var(--success);
        }

        .status-badge.inactive .dot {
            background: var(--danger);
        }

        .biometric-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .biometric-status-badge.configured {
            background: #dcfce7;
            color: #166534;
        }

        .biometric-status-badge.not-configured {
            background: #f3f4f6;
            color: #6b7280;
        }

        .biometric-status-badge.online {
            background: #dcfce7;
            color: #166534;
        }

        .biometric-status-badge.offline {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ============================================
           BUTTONS
        ============================================ */
        .btn-primary-custom {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-custom:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 58, 107, 0.3);
        }

        .btn-secondary-custom {
            background: #6b7280;
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary-custom:hover {
            background: #4b5563;
        }

        .btn-purple-custom {
            background: #7c3aed;
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-purple-custom:hover {
            background: #6d28d9;
        }

        /* ============================================
           MODAL - SCROLLABLE
        ============================================ */
        .modal-content {
            border-radius: 16px;
            border: none;
            max-height: 95vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background: var(--primary);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 1rem 1.5rem;
            flex-shrink: 0;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
            max-height: calc(95vh - 130px);
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            flex-shrink: 0;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
        }

        /* Modal Scrollbar */
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: var(--gold);
            border-radius: 3px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #b8941a;
        }

        /* ============================================
           FORM STYLES
        ============================================ */
        .rv-input-box {
            position: relative;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fafafa;
            transition: all 0.2s;
        }

        .rv-input-box:focus-within {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
            background: white;
        }

        .rv-input-box.is-invalid {
            border-color: var(--danger);
        }

        .rv-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
            pointer-events: none;
        }

        .rv-input-icon.textarea-icon {
            top: 16px;
            transform: none;
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

        .rv-input.textarea-input {
            min-height: 60px;
            resize: vertical;
        }

        select.rv-input {
            appearance: none;
            padding-right: 2rem;
            cursor: pointer;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.3rem;
        }

        .form-label .required {
            color: var(--danger);
            margin-left: 2px;
        }

        .invalid-feedback {
            font-size: 0.75rem;
            color: var(--danger);
            margin-top: 0.25rem;
        }

        /* ============================================
           BIOMETRIC CONFIG SECTION
        ============================================ */
        .biometric-config-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-top: 0.5rem;
        }

        .biometric-config-card .config-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.8rem;
        }

        .biometric-config-card .config-row:last-child {
            border-bottom: none;
        }

        .biometric-config-card .config-row .label {
            color: #6b7280;
        }

        .biometric-config-card .config-row .value {
            font-weight: 600;
            color: #1f2937;
        }

        /* ============================================
           TOAST
        ============================================ */
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            border-left: 4px solid var(--success);
            margin-bottom: 0.75rem;
            animation: slideInRight 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .toast-custom.error {
            border-left-color: var(--danger);
        }

        .toast-custom .message {
            flex: 1;
            font-size: 0.85rem;
            color: #1f2937;
        }

        .toast-custom .close-btn {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0 0.25rem;
            font-size: 1.2rem;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        /* ============================================
           EMPTY STATE
        ============================================ */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .no-results-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-top: 1rem;
        }

        .no-results-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 0.75rem;
        }

        .no-results-state h5 {
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .no-results-state p {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 768px) {
            .hostel-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                padding: 1rem;
            }

            .header-actions {
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 0.5rem;
            }

            .stat-card .number {
                font-size: 1rem;
            }

            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-section .filter-group {
                flex-wrap: wrap;
            }

            .filter-section select,
            .filter-section input {
                min-width: 100%;
            }

            .search-box {
                min-width: 100%;
            }

            .result-count {
                margin-left: 0;
                text-align: center;
            }

            .hostel-stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .modal-body {
                max-height: calc(90vh - 130px);
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hostel-header h1 {
                font-size: 1.2rem;
            }

            .header-actions .btn-primary-custom,
            .header-actions .btn-secondary-custom,
            .header-actions .btn-purple-custom {
                padding: 0.35rem 0.8rem;
                font-size: 0.75rem;
            }

            .hostel-stats-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* ============================================
           PRINT STYLES
        ============================================ */
        @media print {
            .no-print {
                display: none !important;
            }
            .hostel-card {
                break-inside: avoid;
                border: 1px solid #ddd !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="hostel-container">

        {{-- ============================================
        HEADER
        ============================================ --}}
        <div class="hostel-header no-print">
            <div>
                <h1><i class="bi bi-building"></i> Hostel Management</h1>
                <p>Manage all hostels, their configurations, biometric devices, and UPI payments</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn-purple-custom" onclick="syncAllHostelsBiometric()">
                    <i class="bi bi-cloud-upload"></i> Sync All Biometric
                </button>
                <button type="button" class="btn-secondary-custom" onclick="window.location.href='{{ route('admin.hostels.biometric-config') }}'">
                    <i class="bi bi-fingerprint"></i> Biometric Config
                </button>
                <button type="button" class="btn-primary-custom" id="addHostelBtn">
                    <i class="bi bi-plus-circle"></i> Add Hostel
                </button>
            </div>
        </div>

        {{-- ============================================
        STATISTICS
        ============================================ --}}
        <div class="stats-grid">
            <div class="stat-card total">
                <span class="icon">🏠</span>
                <div class="number">{{ $stats['total'] ?? 0 }}</div>
                <div class="label">Total Hostels</div>
            </div>
            <div class="stat-card active">
                <span class="icon">✅</span>
                <div class="number">{{ $stats['active'] ?? 0 }}</div>
                <div class="label">Active</div>
            </div>
            <div class="stat-card inactive">
                <span class="icon">⛔</span>
                <div class="number">{{ $stats['inactive'] ?? 0 }}</div>
                <div class="label">Inactive</div>
            </div>
            <div class="stat-card men">
                <span class="icon">👨</span>
                <div class="number">{{ $stats['men'] ?? 0 }}</div>
                <div class="label">Men Hostels</div>
            </div>
            <div class="stat-card women">
                <span class="icon">👩</span>
                <div class="number">{{ $stats['women'] ?? 0 }}</div>
                <div class="label">Women Hostels</div>
            </div>
            <div class="stat-card rooms">
                <span class="icon">🚪</span>
                <div class="number">{{ $stats['total_rooms'] ?? 0 }}</div>
                <div class="label">Total Rooms</div>
            </div>
            <div class="stat-card beds">
                <span class="icon">🛏️</span>
                <div class="number">{{ $stats['total_beds'] ?? 0 }}</div>
                <div class="label">Total Beds</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                <span class="icon">🔒</span>
                <div class="number" style="color: #7c3aed;">{{ $stats['biometric_enabled'] ?? 0 }}</div>
                <div class="label">Biometric Hostels</div>
            </div>
        </div>

        {{-- ============================================
        FILTERS
        ============================================ --}}
        <div class="filter-section no-print">
            <div class="filter-group">
                <label style="font-size:0.8rem; font-weight:600;">Filter:</label>
            </div>
            <div class="filter-group">
                <select id="filterStatus">
                    <option value="">All Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="filterType">
                    <option value="">All Types</option>
                    <option value="MEN">👨 Men</option>
                    <option value="WOMEN">👩 Women</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="filterBiometric">
                    <option value="">All Biometric</option>
                    <option value="configured">✅ Configured</option>
                    <option value="not_configured">❌ Not Configured</option>
                </select>
            </div>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchHostel" placeholder="Search by name, code, phone, email...">
            </div>
            <button class="btn-clear-filters" onclick="clearFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Clear
            </button>
            <span class="result-count" id="resultCount"></span>
        </div>

        {{-- ============================================
        HOSTELS GRID
        ============================================ --}}
        <div id="hostelsContainer">
            @if ($hostels->count() > 0)
                <div class="row g-4" id="hostelsGrid">
                    @foreach ($hostels as $hostel)
                        <div class="col-xl-4 col-lg-6 col-md-6 hostel-item"
                             data-id="{{ $hostel->id }}"
                             data-status="{{ $hostel->status }}"
                             data-type="{{ $hostel->hostel_type }}"
                             data-biometric="{{ $hostel->biometric_device_id ? 'configured' : 'not_configured' }}"
                             data-name="{{ strtolower($hostel->hostel_name) }}"
                             data-code="{{ strtolower($hostel->hostel_code) }}"
                             data-phone="{{ $hostel->phone ?? '' }}"
                             data-email="{{ strtolower($hostel->email ?? '') }}">

                            <div class="hostel-card">
                                {{-- Card Header --}}
                                <div class="card-header">
                                    <div>
                                        <div style="font-weight:600; font-size:1rem;">
                                            {{ $hostel->hostel_name }}
                                        </div>
                                        <div style="font-size:0.7rem; opacity:0.8;">
                                            <span class="hostel-code">{{ $hostel->hostel_code }}</span>
                                            @if($hostel->biometric_device_id)
                                                <span style="margin-left:8px; background:rgba(255,255,255,0.2); padding:0 6px; border-radius:3px; font-size:0.6rem;">
                                                    <i class="bi bi-fingerprint"></i> Biometric
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="hostel-type-badge">
                                            {{ $hostel->hostel_type == 'MEN' ? '👨 Men' : '👩 Women' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div class="card-body">
                                    {{-- Contact Details --}}
                                    @if($hostel->phone)
                                        <div class="hostel-detail">
                                            <i class="bi bi-phone"></i>
                                            <span class="value">{{ $hostel->phone }}</span>
                                        </div>
                                    @endif
                                    @if($hostel->email)
                                        <div class="hostel-detail">
                                            <i class="bi bi-envelope"></i>
                                            <span class="value">{{ Str::limit($hostel->email, 30) }}</span>
                                        </div>
                                    @endif
                                    @if($hostel->address)
                                        <div class="hostel-detail">
                                            <i class="bi bi-geo-alt"></i>
                                            <span class="value">{{ Str::limit($hostel->address, 40) }}</span>
                                        </div>
                                    @endif

                                    {{-- UPI Info --}}
                                    @if($hostel->upi_id)
                                        <div class="hostel-detail">
                                            <i class="bi bi-upc-scan"></i>
                                            <span class="label">UPI:</span>
                                            <span class="value" style="font-size:0.7rem; word-break:break-all;">{{ $hostel->upi_id }}</span>
                                            @if($hostel->upi_payee_name)
                                                <span style="font-size:0.65rem; color:#6b7280;">({{ $hostel->upi_payee_name }})</span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Stats Row --}}
                                    <div class="hostel-stats-row">
                                        <div class="stat-item">
                                            <div class="stat-number">{{ $hostel->residents_count ?? 0 }}</div>
                                            <div class="stat-label">Residents</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number">{{ $hostel->rooms_count ?? 0 }}</div>
                                            <div class="stat-label">Rooms</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number">{{ $hostel->beds_count ?? 0 }}</div>
                                            <div class="stat-label">Beds</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number" style="color: #7c3aed;">
                                                {{ $hostel->biometric_residents_count ?? 0 }}
                                            </div>
                                            <div class="stat-label">Bio. Synced</div>
                                        </div>
                                    </div>

                                    {{-- Biometric Status --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="status-badge {{ strtolower($hostel->status) }}" onclick="toggleHostelStatus({{ $hostel->id }})">
                                                <span class="dot"></span>
                                                {{ $hostel->status }}
                                            </span>
                                        </div>
                                        <div>
                                            @if($hostel->biometric_device_id)
                                                <span class="biometric-status-badge configured" id="bio-status-{{ $hostel->id }}">
                                                    <i class="bi bi-check-circle"></i> Configured
                                                </span>
                                            @else
                                                <span class="biometric-status-badge not-configured">
                                                    <i class="bi bi-slash-circle"></i> Not Configured
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Biometric Config Preview --}}
                                    @if($hostel->biometric_device_id)
                                        <div class="biometric-config-card">
                                            <div class="config-row">
                                                <span class="label">Device:</span>
                                                <span class="value">{{ $hostel->biometric_device_name ?? $hostel->biometric_device_id }}</span>
                                            </div>
                                            <div class="config-row">
                                                <span class="label">IP:</span>
                                                <span class="value">{{ $hostel->biometric_ip_address }}</span>
                                            </div>
                                            <div class="config-row">
                                                <span class="label">Port:</span>
                                                <span class="value">{{ $hostel->biometric_port ?? '4370' }}</span>
                                            </div>
                                            @if($hostel->employee_code_prefix)
                                                <div class="config-row">
                                                    <span class="label">Code Prefix:</span>
                                                    <span class="value">{{ $hostel->employee_code_prefix }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="hostel-actions">
                                        <button class="btn-sm primary" onclick="editHostel({{ $hostel->id }})" title="Edit Hostel">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn-sm purple" onclick="openBiometricModal({{ $hostel->id }})" title="Configure Biometric">
                                            <i class="bi bi-fingerprint"></i> Bio Config
                                        </button>
                                        @if($hostel->biometric_device_id)
                                            <button class="btn-sm success" onclick="testBiometricConnection({{ $hostel->id }})" title="Test Connection">
                                                <i class="bi bi-plug"></i> Test
                                            </button>
                                            <button class="btn-sm warning" onclick="syncHostelBiometric({{ $hostel->id }})" title="Sync Residents">
                                                <i class="bi bi-cloud-upload"></i> Sync
                                            </button>
                                        @endif
                                        <button class="btn-sm danger" onclick="deleteHostel({{ $hostel->id }})" title="Delete Hostel">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- No Results --}}
                <div id="noSearchResults" class="no-results-state" style="display:none;">
                    <i class="bi bi-search"></i>
                    <h5>No hostels found</h5>
                    <p>No hostels match your search criteria. Try adjusting your filters.</p>
                    <button class="btn-clear-filters" onclick="clearFilters()">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear All Filters
                    </button>
                </div>

            @else
                {{-- Empty State --}}
                <div class="empty-state">
                    <i class="bi bi-building"></i>
                    <h5>No hostels found</h5>
                    <p class="text-muted">Create your first hostel to get started.</p>
                    <button type="button" class="btn-primary-custom" onclick="openAddModal()">
                        <i class="bi bi-plus-circle"></i> Add Hostel
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================
    ADD/EDIT MODAL
    ============================================ --}}
    <div class="modal fade" id="hostelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"><i class="bi bi-building-add"></i> Add Hostel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="hostelForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="editId" name="edit_id">
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- Basic Information --}}
                            <div class="col-md-6">
                                <label class="form-label">Hostel Code <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-hash rv-input-icon"></i>
                                    <input type="text" name="hostel_code" id="hostel_code" class="rv-input" placeholder="e.g. HOST-001" required>
                                </div>
                                <div class="invalid-feedback" id="hostel_code_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hostel Name <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-building rv-input-icon"></i>
                                    <input type="text" name="hostel_name" id="hostel_name" class="rv-input" placeholder="Hostel name" required>
                                </div>
                                <div class="invalid-feedback" id="hostel_name_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hostel Type <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-person rv-input-icon"></i>
                                    <select name="hostel_type" id="hostel_type" class="rv-input" required>
                                        <option value="">Select Type</option>
                                        <option value="MEN">👨 Men</option>
                                        <option value="WOMEN">👩 Women</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback" id="hostel_type_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-toggle-on rv-input-icon"></i>
                                    <select name="status" id="status" class="rv-input" required>
                                        <option value="ACTIVE">✅ Active</option>
                                        <option value="INACTIVE">⛔ Inactive</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback" id="status_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-phone rv-input-icon"></i>
                                    <input type="text" name="phone" id="phone" class="rv-input" placeholder="+91 98765 43210">
                                </div>
                                <div class="invalid-feedback" id="phone_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-envelope rv-input-icon"></i>
                                    <input type="email" name="email" id="email" class="rv-input" placeholder="hostel@email.com">
                                </div>
                                <div class="invalid-feedback" id="email_error"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-geo-alt rv-input-icon textarea-icon"></i>
                                    <textarea name="address" id="address" class="rv-input textarea-input" placeholder="Complete address"></textarea>
                                </div>
                                <div class="invalid-feedback" id="address_error"></div>
                            </div>

                            {{-- UPI Configuration --}}
                            <div class="col-12">
                                <hr>
                                <h6 class="mb-3"><i class="bi bi-upc-scan text-gold"></i> UPI Payment Configuration</h6>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">UPI ID / URL</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-upc-scan rv-input-icon"></i>
                                    <input type="text" name="upi_id" id="upi_id" class="rv-input" placeholder="upi://pay?pa=hostel@bank&pn=HostelName">
                                </div>
                                <small class="text-muted" style="font-size:0.7rem;">
                                    <i class="bi bi-info-circle"></i> Enter full UPI URL or UPI ID (e.g., upi://pay?pa=hostel@bank)
                                </small>
                                <div class="invalid-feedback" id="upi_id_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payee Name</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-person rv-input-icon"></i>
                                    <input type="text" name="upi_payee_name" id="upi_payee_name" class="rv-input" placeholder="Payee name">
                                </div>
                                <small class="text-muted" style="font-size:0.7rem;">
                                    <i class="bi bi-info-circle"></i> Will default to hostel name
                                </small>
                                <div class="invalid-feedback" id="upi_payee_name_error"></div>
                            </div>

                            {{-- Biometric Configuration (Minimal) --}}
                            <div class="col-12">
                                <hr>
                                <h6 class="mb-3"><i class="bi bi-fingerprint text-purple"></i> Biometric Configuration</h6>
                                <p class="text-muted small">Configure biometric device details for this hostel. Use the "Biometric Config" button on the hostel card for full configuration.</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Device ID</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-qr-code rv-input-icon"></i>
                                    <input type="text" name="biometric_device_id" id="biometric_device_id" class="rv-input" placeholder="Device serial number">
                                </div>
                                <div class="invalid-feedback" id="biometric_device_id_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Device Name</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-tag rv-input-icon"></i>
                                    <input type="text" name="biometric_device_name" id="biometric_device_name" class="rv-input" placeholder="e.g. Main Gate Device">
                                </div>
                                <div class="invalid-feedback" id="biometric_device_name_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">IP Address</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-wifi rv-input-icon"></i>
                                    <input type="text" name="biometric_ip_address" id="biometric_ip_address" class="rv-input" placeholder="192.168.1.100">
                                </div>
                                <div class="invalid-feedback" id="biometric_ip_address_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-plug rv-input-icon"></i>
                                    <input type="text" name="biometric_port" id="biometric_port" class="rv-input" placeholder="4370" value="4370">
                                </div>
                                <div class="invalid-feedback" id="biometric_port_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Location Code</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-geo rv-input-icon"></i>
                                    <input type="text" name="biometric_location_code" id="biometric_location_code" class="rv-input" placeholder="LOC_001">
                                </div>
                                <div class="invalid-feedback" id="biometric_location_code_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Code Prefix</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-hash rv-input-icon"></i>
                                    <input type="text" name="employee_code_prefix" id="employee_code_prefix" class="rv-input" placeholder="e.g. HOST-">
                                </div>
                                <small class="text-muted" style="font-size:0.7rem;">
                                    <i class="bi bi-info-circle"></i> Will be prepended to resident employee codes
                                </small>
                                <div class="invalid-feedback" id="employee_code_prefix_error"></div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal" style="background:#6b7280;">Cancel</button>
                        <button type="submit" class="btn-primary-custom" id="saveBtn">
                            <i class="bi bi-check-circle"></i> <span id="saveBtnText">Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================
    BIOMETRIC CONFIG MODAL
    ============================================ --}}
    <div class="modal fade" id="biometricModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-fingerprint"></i> Biometric Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="biometricForm">
                    @csrf
                    <input type="hidden" id="bioHostelId" name="hostel_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Configure the biometric device for this hostel. All residents will be synced to this device.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Device ID <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-qr-code rv-input-icon"></i>
                                    <input type="text" name="biometric_device_id" id="bio_device_id" class="rv-input" placeholder="Device serial number" required>
                                </div>
                                <div class="invalid-feedback" id="bio_device_id_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Device Name</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-tag rv-input-icon"></i>
                                    <input type="text" name="biometric_device_name" id="bio_device_name" class="rv-input" placeholder="e.g. Main Gate Device">
                                </div>
                                <div class="invalid-feedback" id="bio_device_name_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">IP Address <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-wifi rv-input-icon"></i>
                                    <input type="text" name="biometric_ip_address" id="bio_ip_address" class="rv-input" placeholder="192.168.1.100" required>
                                </div>
                                <div class="invalid-feedback" id="bio_ip_address_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-plug rv-input-icon"></i>
                                    <input type="text" name="biometric_port" id="bio_port" class="rv-input" placeholder="4370" value="4370">
                                </div>
                                <div class="invalid-feedback" id="bio_port_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Location Code</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-geo rv-input-icon"></i>
                                    <input type="text" name="biometric_location_code" id="bio_location_code" class="rv-input" placeholder="LOC_001">
                                </div>
                                <div class="invalid-feedback" id="bio_location_code_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Code Prefix</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-hash rv-input-icon"></i>
                                    <input type="text" name="employee_code_prefix" id="bio_code_prefix" class="rv-input" placeholder="e.g. HOST-">
                                </div>
                                <small class="text-muted" style="font-size:0.7rem;">
                                    <i class="bi bi-info-circle"></i> Prepended to resident employee codes
                                </small>
                                <div class="invalid-feedback" id="bio_code_prefix_error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal" style="background:#6b7280;">Cancel</button>
                        <button type="button" class="btn-purple-custom" onclick="testBiometricConfig()" style="background:#7c3aed;">
                            <i class="bi bi-plug"></i> Test Connection
                        </button>
                        <button type="submit" class="btn-primary-custom" id="bioSaveBtn">
                            <i class="bi bi-check-circle"></i> Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================
    DETAILS VIEW MODAL
    ============================================ --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-building"></i> Hostel Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal" style="background:#6b7280;">Close</button>
                    <button type="button" class="btn-primary-custom" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container" id="flashMessageContainer"></div>
@endsection

{{-- ============================================
JAVASCRIPT
============================================ --}}
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================================
// VARIABLES
// ============================================
let hostelModal, biometricModal, detailsModal;

$(document).ready(function() {
    console.log('✅ Document ready!');

    // Initialize Modals
    hostelModal = new bootstrap.Modal(document.getElementById('hostelModal'), {
        backdrop: 'static',
        keyboard: true
    });
    biometricModal = new bootstrap.Modal(document.getElementById('biometricModal'), {
        backdrop: 'static',
        keyboard: true
    });
    detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'), {
        backdrop: 'static',
        keyboard: true
    });

    // ============================================
    // FILTER BINDING
    // ============================================

    let searchTimeout;
    $('#searchHostel').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });

    $('#filterStatus, #filterType, #filterBiometric').on('change', function() {
        applyFilters();
    });

    // Add Hostel Button
    $('#addHostelBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    // Modal hidden event
    $('#hostelModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Form submit
    $('#hostelForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Biometric Form submit
    $('#biometricForm').on('submit', function(e) {
        e.preventDefault();
        submitBiometricConfig();
    });

    // Run initial filter
    applyFilters();
});

// ============================================
// APPLY FILTERS
// ============================================
function applyFilters() {
    var status = $('#filterStatus').val() || '';
    var type = $('#filterType').val() || '';
    var biometric = $('#filterBiometric').val() || '';
    var search = $('#searchHostel').val().toLowerCase().trim() || '';

    var visibleCount = 0;
    var totalCount = $('.hostel-item').length;

    $('.hostel-item').each(function(index) {
        var show = true;
        var $item = $(this);

        var resStatus = $item.attr('data-status') || '';
        var resType = $item.attr('data-type') || '';
        var resBiometric = $item.attr('data-biometric') || '';
        var resName = ($item.attr('data-name') || '').toLowerCase();
        var resCode = ($item.attr('data-code') || '').toLowerCase();
        var resPhone = ($item.attr('data-phone') || '').toLowerCase();
        var resEmail = ($item.attr('data-email') || '').toLowerCase();
        var resId = String($item.attr('data-id') || '');

        if (status && resStatus !== status) show = false;
        if (type && show && resType !== type) show = false;
        if (biometric && show && resBiometric !== biometric) show = false;

        if (search && show) {
            var searchMatch = false;
            if (resName.includes(search)) searchMatch = true;
            if (resCode.includes(search)) searchMatch = true;
            if (resPhone.includes(search)) searchMatch = true;
            if (resEmail.includes(search)) searchMatch = true;
            if (resId.includes(search)) searchMatch = true;
            var textContent = $item.text().toLowerCase();
            if (textContent.includes(search)) searchMatch = true;
            if (!searchMatch) show = false;
        }

        if (show) {
            $item.show();
            visibleCount++;
        } else {
            $item.hide();
        }
    });

    var resultCountEl = $('#resultCount');
    if (visibleCount === totalCount) {
        resultCountEl.text('');
    } else {
        resultCountEl.text('Showing ' + visibleCount + ' of ' + totalCount + ' hostels');
    }

    if (visibleCount === 0 && totalCount > 0) {
        $('#noSearchResults').show();
    } else {
        $('#noSearchResults').hide();
    }
}

// ============================================
// CLEAR FILTERS
// ============================================
function clearFilters() {
    $('#filterStatus, #filterType, #filterBiometric').val('');
    $('#searchHostel').val('');
    $('#resultCount').text('');
    $('#noSearchResults').hide();
    applyFilters();
}

// ============================================
// MODAL FUNCTIONS
// ============================================
function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Hostel';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    hostelModal.show();
}

function resetForm() {
    const form = document.getElementById('hostelForm');
    form.reset();
    document.getElementById('biometric_port').value = '4370';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Hostel';
}

// ============================================
// FORM SUBMISSION
// ============================================
function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.hostels.store') }}";
    let formData = new FormData(document.getElementById('hostelForm'));

    if (id) {
        url = "{{ url('admin/hostels') }}/" + id;
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#saveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                hostelModal.hide();
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
            let id = document.getElementById('editId').value;
            let text = id ? 'Update' : 'Save';
            $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">' + text + '</span>');
        }
    });
}

// ============================================
// CRUD OPERATIONS
// ============================================
function editHostel(id) {
    $.ajax({
        url: "{{ url('admin/hostels') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Hostel';
                document.getElementById('editId').value = data.id;
                document.getElementById('hostel_code').value = data.hostel_code;
                document.getElementById('hostel_name').value = data.hostel_name;
                document.getElementById('hostel_type').value = data.hostel_type;
                document.getElementById('status').value = data.status;
                document.getElementById('address').value = data.address || '';
                document.getElementById('phone').value = data.phone || '';
                document.getElementById('email').value = data.email || '';
                document.getElementById('upi_id').value = data.upi_id || '';
                document.getElementById('upi_payee_name').value = data.upi_payee_name || '';
                document.getElementById('biometric_device_id').value = data.biometric_device_id || '';
                document.getElementById('biometric_device_name').value = data.biometric_device_name || '';
                document.getElementById('biometric_ip_address').value = data.biometric_ip_address || '';
                document.getElementById('biometric_port').value = data.biometric_port || '4370';
                document.getElementById('biometric_location_code').value = data.biometric_location_code || '';
                document.getElementById('employee_code_prefix').value = data.employee_code_prefix || '';

                document.getElementById('saveBtnText').textContent = 'Update';
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                hostelModal.show();
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load hostel data', 'error');
            }
        }
    });
}

function deleteHostel(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone! All associated rooms, beds, and residents will also be affected.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/hostels') }}/" + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(response.message || 'Failed to delete!', 'error');
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

function toggleHostelStatus(id) {
    Swal.fire({
        title: 'Toggle Status?',
        text: "Change hostel status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c5a028',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/hostels') }}/" + id + "/toggle-status",
                type: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                        showToast(xhr.responseJSON?.message || 'Failed to update status!', 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// BIOMETRIC CONFIGURATION
// ============================================
function openBiometricModal(id) {
    $('#bioHostelId').val(id);
    $('#biometricForm')[0].reset();
    $('#bio_port').val('4370');
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');

    // Load existing config
    $.ajax({
        url: "{{ url('admin/hostels') }}/" + id + "/biometric-config",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                $('#bio_device_id').val(data.biometric_device_id || '');
                $('#bio_device_name').val(data.biometric_device_name || '');
                $('#bio_ip_address').val(data.biometric_ip_address || '');
                $('#bio_port').val(data.biometric_port || '4370');
                $('#bio_location_code').val(data.biometric_location_code || '');
                $('#bio_code_prefix').val(data.employee_code_prefix || '');
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            }
        }
    });

    biometricModal.show();
}

function submitBiometricConfig() {
    let id = $('#bioHostelId').val();
    let formData = new FormData(document.getElementById('biometricForm'));

    $.ajax({
        url: "{{ url('admin/hostels') }}/" + id + "/biometric-config",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        beforeSend: function() {
            $('#bioSaveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                biometricModal.hide();
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
                        $('#bio_' + field).closest('.rv-input-box').addClass('is-invalid');
                        $('#bio_' + field + '_error').text(messages[0]);
                    });
                    showToast('Please fix validation errors', 'error');
                }
            } else {
                showToast(xhr.responseJSON?.message || 'Failed to save configuration!', 'error');
            }
        },
        complete: function() {
            $('#bioSaveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Save Configuration');
        }
    });
}

function testBiometricConfig() {
    let id = $('#bioHostelId').val();

    Swal.fire({
        title: 'Testing Connection',
        text: 'Please wait while we test the biometric device connection...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "{{ url('admin/hostels') }}/" + id + "/test-biometric",
        type: 'GET',
        success: function(response) {
            Swal.close();
            if (response.success) {
                showToast('✅ ' + response.message, 'success');
            } else {
                showToast('❌ ' + response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('❌ Failed to test connection!', 'error');
            }
        }
    });
}

// ============================================
// BIOMETRIC SYNC OPERATIONS
// ============================================
function syncHostelBiometric(id) {
    Swal.fire({
        title: 'Sync Residents?',
        text: "This will sync all active residents of this hostel to the biometric device.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, sync them!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Syncing...',
                text: 'Please wait while residents are synced to the device.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ url('admin/hostels') }}/" + id + "/sync-biometric",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        let msg = '✅ ' + response.message;
                        if (response.failed > 0) {
                            msg += ' (Failed: ' + response.failed + ')';
                        }
                        showToast(msg, response.failed > 0 ? 'error' : 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast('❌ ' + response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast('❌ Failed to sync residents!', 'error');
                    }
                }
            });
        }
    });
}

function syncAllHostelsBiometric() {
    Swal.fire({
        title: 'Sync All Hostels?',
        text: "This will sync all active residents from all configured hostels to their respective biometric devices.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, sync all!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Syncing All...',
                text: 'Please wait while all hostels are synced.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.hostels.sync-all-biometric') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        let msg = '✅ ' + response.message;
                        showToast(msg, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast('❌ ' + response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast('❌ Failed to sync hostels!', 'error');
                    }
                }
            });
        }
    });
}

function testBiometricConnection(id) {
    Swal.fire({
        title: 'Testing Connection',
        text: 'Checking biometric device connection...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "{{ url('admin/hostels') }}/" + id + "/test-biometric",
        type: 'GET',
        success: function(response) {
            Swal.close();
            if (response.success) {
                showToast('✅ ' + response.message, 'success');
            } else {
                showToast('❌ ' + response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('❌ Failed to test connection!', 'error');
            }
        }
    });
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================
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
    const color = type === 'success' ? '#22c55e' : '#ef4444';

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
</script>
@endpush