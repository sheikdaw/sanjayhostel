@extends('layouts.office')

@section('title', 'Resident Management')
@section('page_title', 'Resident Management')

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
        .resident-container {
            max-width: 100%;
            padding: 0 15px;
        }
        .modal-content form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }
        /* ============================================
           HEADER
        ============================================ */
        .resident-header {
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

        .resident-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .resident-header p {
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
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
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

        .stat-card.male .number { color: #3b82f6; }
        .stat-card.female .number { color: #ec4899; }
        .stat-card.active .number { color: var(--success); }
        .stat-card.vacated .number { color: var(--danger); }
        .stat-card.total .number { color: var(--primary); }
        .stat-card.rent .number { color: #92400e; }
        .stat-card.rent { background: linear-gradient(135deg, #fef3c7, #fde68a); }
        .stat-card.biometric .number { color: #7c3aed; }
        .stat-card.biometric { background: linear-gradient(135deg, #ede9fe, #ddd6fe); }
        .stat-card.food .number { color: #166534; }
        .stat-card.food { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }

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
           BULK ACTIONS
        ============================================ */
        .bulk-actions {
            display: none;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .bulk-actions.show {
            display: flex;
        }

        .bulk-actions .count {
            font-weight: 600;
            color: var(--primary);
        }

        .bulk-actions select {
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            font-size: 0.75rem;
        }

        /* ============================================
           RESIDENT CARD
        ============================================ */
        .resident-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            position: relative;
            height: 100%;
        }

        .resident-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        .resident-card .card-checkbox {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 2;
        }

        .resident-card .card-checkbox input {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .resident-header-card {
            padding: 1rem 1.25rem;
            padding-left: 3rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .resident-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gold);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            flex-shrink: 0;
            overflow: hidden;
        }

        .resident-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .resident-code {
            font-size: 0.65rem;
            font-family: monospace;
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 4px;
            color: rgba(255, 255, 255, 0.9);
        }

        .resident-body {
            padding: 1rem 1.25rem;
        }

        .resident-detail {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .resident-detail i {
            width: 16px;
            color: var(--gold);
        }

        .resident-detail .label {
            color: #6b7280;
        }

        .resident-detail .value {
            color: #1f2937;
            font-weight: 500;
        }

        .resident-room-info {
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 6px;
            margin: 0.5rem 0;
            font-size: 0.75rem;
        }

        .resident-room-info .label {
            color: #6b7280;
        }

        .resident-room-info .value {
            font-weight: 600;
            color: var(--primary);
        }

        .resident-rent {
            font-size: 0.8rem;
            font-weight: 700;
            color: #92400e;
            background: #fef3c7;
            padding: 2px 10px;
            border-radius: 12px;
            display: inline-block;
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

        .status-badge.vacated {
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

        .status-badge.vacated .dot {
            background: var(--danger);
        }

        .food-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .food-badge.with-food {
            background: #dcfce7;
            color: #166534;
        }

        .food-badge.without-food {
            background: #f3f4f6;
            color: #4b5563;
        }

        .biometric-badge-small {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 1px 8px;
            border-radius: 12px;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .biometric-badge-small.enabled {
            background: #dcfce7;
            color: #166534;
        }

        .biometric-badge-small.disabled {
            background: #fee2e2;
            color: #991b1b;
        }

        .biometric-badge-small.not-synced {
            background: #f3f4f6;
            color: #6b7280;
        }

        .document-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 0.6rem;
            background: #e3f2fd;
            color: #1565c0;
            cursor: pointer;
            transition: all 0.2s;
        }

        .document-badge:hover {
            background: #bbdefb;
        }

        .document-badge.has-doc {
            background: #dcfce7;
            color: #166534;
        }

        /* ============================================
           BUTTONS
        ============================================ */
        .btn-action {
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: white;
            font-size: 0.75rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-action:hover {
            background: #f3f4f6;
        }

        .btn-action.text-danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .btn-action.text-primary:hover {
            background: #e3f2fd;
            border-color: #90caf9;
        }

        .btn-action.text-success:hover {
            background: #dcfce7;
            border-color: #86efac;
        }

        .btn-action.text-info:hover {
            background: #cff4fc;
            border-color: #81d4fa;
        }

        .btn-action.text-warning:hover {
            background: #fef3c7;
            border-color: #fcd34d;
        }

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
           MODAL - SCROLLABLE FIX
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

        /* Modal Scrollbar Styling */
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

        .file-input-box {
            padding: 0.5rem;
        }

        .file-input-box input[type="file"] {
            padding: 0.3rem;
            border: none;
            background: transparent;
            width: 100%;
            font-size: 0.8rem;
        }

        .file-input-box input[type="file"]::-webkit-file-upload-button {
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            background: white;
            cursor: pointer;
            font-size: 0.75rem;
        }

        .file-input-box input[type="file"]::-webkit-file-upload-button:hover {
            background: #f3f4f6;
        }

        .file-preview-container {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .file-preview-container img {
            max-width: 60px;
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }

        .file-preview-container .file-info {
            flex: 1;
            font-size: 0.75rem;
        }

        .file-preview-container .file-info .filename {
            font-weight: 600;
            color: #1f2937;
        }

        .file-preview-container .file-info .filesize {
            color: #6b7280;
        }

        .existing-doc-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.6rem;
            background: #dcfce7;
            color: #166534;
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
            .resident-header {
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

            .bulk-actions {
                flex-wrap: wrap;
                justify-content: center;
            }

            .resident-header-card {
                padding: 0.75rem 1rem;
                padding-left: 2.5rem;
                flex-wrap: wrap;
            }

            .resident-avatar {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .resident-body {
                padding: 0.75rem 1rem;
            }

            .resident-detail {
                font-size: 0.7rem;
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

            .resident-header h1 {
                font-size: 1.2rem;
            }

            .header-actions .btn-primary-custom,
            .header-actions .btn-secondary-custom,
            .header-actions .btn-purple-custom {
                padding: 0.35rem 0.8rem;
                font-size: 0.75rem;
            }
        }

        /* ============================================
           PRINT STYLES
        ============================================ */
        @media print {
            .no-print {
                display: none !important;
            }
            .resident-card {
                break-inside: avoid;
                border: 1px solid #ddd !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="resident-container">

        {{-- ============================================
        HEADER
        ============================================ --}}
        <div class="resident-header no-print">
            <div>
                <h1><i class="bi bi-people-fill"></i> Resident Management</h1>
                <p>Manage all residents, their accommodations, biometric access, and documents</p>
                @if ($user->role != 'admin')
                    <p style="color: var(--gold); font-size:0.8rem; margin-top:4px;">
                        <i class="bi bi-info-circle"></i> You have access to {{ $hostels->count() }} hostel(s)
                    </p>
                @endif
            </div>
            <div class="header-actions">
                <button type="button" class="btn-secondary-custom" onclick="exportData()">
                    <i class="bi bi-download"></i> Export
                </button>
                <button type="button" class="btn-purple-custom" onclick="syncAllBiometric()">
                    <i class="bi bi-cloud-upload"></i> Sync Biometric
                </button>
                <button type="button" class="btn-primary-custom" id="addResidentBtn">
                    <i class="bi bi-plus-circle"></i> Add Resident
                </button>
            </div>
        </div>

        {{-- ============================================
        STATISTICS
        ============================================ --}}
        <div class="stats-grid">
            <div class="stat-card total">
                <span class="icon">🏠</span>
                <div class="number">{{ $stats['total'] }}</div>
                <div class="label">Total</div>
            </div>
            <div class="stat-card active">
                <span class="icon">✅</span>
                <div class="number">{{ $stats['active'] }}</div>
                <div class="label">Active</div>
            </div>
            <div class="stat-card vacated">
                <span class="icon">❌</span>
                <div class="number">{{ $stats['vacated'] }}</div>
                <div class="label">Vacated</div>
            </div>
            <div class="stat-card male">
                <span class="icon">👨</span>
                <div class="number">{{ $stats['male'] }}</div>
                <div class="label">Men</div>
            </div>
            <div class="stat-card female">
                <span class="icon">👩</span>
                <div class="number">{{ $stats['female'] }}</div>
                <div class="label">Women</div>
            </div>
            <div class="stat-card food">
                <span class="icon">🍽️</span>
                <div class="number">{{ $stats['with_food'] ?? 0 }}</div>
                <div class="label">With Food</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb);">
                <span class="icon">🍞</span>
                <div class="number" style="color: #4b5563;">{{ $stats['without_food'] ?? 0 }}</div>
                <div class="label">Without Food</div>
            </div>
            <div class="stat-card rent">
                <span class="icon">💰</span>
                <div class="number">₹{{ number_format($stats['total_rent'] ?? 0, 0) }}</div>
                <div class="label">Monthly Rent</div>
            </div>
            <div class="stat-card biometric">
                <span class="icon">🔒</span>
                <div class="number">{{ $biometricStats['access_enabled'] ?? 0 }}</div>
                <div class="label">Biometric Active</div>
            </div>
        </div>

        {{-- ============================================
        BULK ACTIONS
        ============================================ --}}
        <div class="bulk-actions no-print" id="bulkActions">
            <span><i class="bi bi-check-square"></i> <span class="count" id="selectedCount">0</span> selected</span>
            <span style="color:#6b7280;">|</span>
            <select id="bulkStatusSelect">
                <option value="">Change Status</option>
                <option value="ACTIVE">Active</option>
                <option value="VACATED">Vacated</option>
            </select>
            <button class="btn-action text-primary" onclick="bulkStatusUpdate()">
                <i class="bi bi-check-circle"></i> Apply
            </button>
            <button class="btn-action text-danger" onclick="bulkDelete()">
                <i class="bi bi-trash"></i> Delete
            </button>
            <button class="btn-action" onclick="clearSelection()">
                <i class="bi bi-x"></i> Clear
            </button>
        </div>

        {{-- ============================================
        FILTERS - FIXED WITH PROPER DATA ATTRIBUTES
        ============================================ --}}
        <div class="filter-section no-print">
            <div class="filter-group">
                <label style="font-size:0.8rem; font-weight:600;">Filter:</label>
            </div>
            <div class="filter-group">
                <select id="filterStatus">
                    <option value="">All Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="VACATED">Vacated</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="filterHostel">
                    <option value="">All Hostels</option>
                    @foreach ($hostels as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <select id="filterGender">
                    <option value="">All Gender</option>
                    <option value="MEN">👨 Men</option>
                    <option value="WOMEN">👩 Women</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="filterFood">
                    <option value="">All Food</option>
                    <option value="WITH_FOOD">🍽️ With Food</option>
                    <option value="WITHOUT_FOOD">🍞 Without Food</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="filterBiometric">
                    <option value="">All Biometric</option>
                    <option value="enabled">✅ Enabled</option>
                    <option value="disabled">❌ Disabled</option>
                    <option value="not_synced">⏳ Not Synced</option>
                </select>
            </div>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchResident" placeholder="Search by name, code, phone...">
            </div>
            <button class="btn-clear-filters" onclick="clearFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Clear
            </button>
            <span class="result-count" id="resultCount"></span>
        </div>

        {{-- ============================================
        RESIDENTS GRID - WITH PROPER DATA ATTRIBUTES
        ============================================ --}}
        <div id="residentsContainer">
            @if ($residents->count() > 0)
                <div class="row g-4" id="residentsGrid">
                    @foreach ($residents as $resident)
                        {{--
                            IMPORTANT: All data-* attributes must be properly set
                            These are used by the filter JavaScript
                        --}}
                        <div class="col-xl-3 col-lg-4 col-md-6 resident-item"
                             data-id="{{ $resident->id }}"
                             data-status="{{ $resident->status }}"
                             data-hostel="{{ $resident->hostel_id }}"
                             data-gender="{{ $resident->hostel->hostel_type ?? '' }}"
                             data-food="{{ $resident->food_status }}"
                             data-biometric="{{ $resident->employee_code ? ($resident->biometric_access ? 'enabled' : 'disabled') : 'not_synced' }}"
                             data-name="{{ strtolower($resident->name) }}"
                             data-code="{{ strtolower($resident->resident_code) }}"
                             data-phone="{{ $resident->phone }}"
                             data-email="{{ strtolower($resident->email ?? '') }}">

                            <div class="resident-card">
                                {{-- Checkbox --}}
                                <div class="card-checkbox no-print">
                                    <input type="checkbox" class="resident-checkbox" value="{{ $resident->id }}"
                                           onclick="updateBulkActions()">
                                </div>

                                {{-- Header --}}
                                <div class="resident-header-card">
                                    <div class="resident-avatar">
                                        @if ($resident->profile_image)
                                            <img src="{{ asset($resident->profile_image) }}" alt="{{ $resident->name }}">
                                        @else
                                            {{ strtoupper(substr($resident->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:600; font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            {{ $resident->name }}
                                        </div>
                                        <div style="font-size:0.7rem; opacity:0.8;">
                                            <span class="resident-code">{{ $resident->resident_code }}</span>
                                            @if($resident->employee_code)
                                                <span style="margin-left:8px; background:rgba(255,255,255,0.2); padding:0 6px; border-radius:3px; font-size:0.6rem;">
                                                    Emp: {{ $resident->employee_code }}
                                                </span>
                                            @else
                                                <span style="margin-left:8px; background:rgba(255,255,255,0.2); padding:0 6px; border-radius:3px; font-size:0.6rem;">
                                                    ⚠️ Not Synced
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Body --}}
                                <div class="resident-body">
                                    {{-- Contact --}}
                                    <div class="resident-detail">
                                        <i class="bi bi-phone"></i>
                                        <span class="value">{{ $resident->phone }}</span>
                                    </div>
                                    @if($resident->parentsphone)
                                        <div class="resident-detail">
                                            <i class="bi bi-person-lines-fill"></i>
                                            <span class="label">Parents:</span>
                                            <span class="value">{{ $resident->parentsphone }}</span>
                                        </div>
                                    @endif
                                    @if ($resident->email)
                                        <div class="resident-detail">
                                            <i class="bi bi-envelope"></i>
                                            <span class="value">{{ Str::limit($resident->email, 25) }}</span>
                                        </div>
                                    @endif

                                    {{-- Biometric Status --}}
                                    <div class="resident-detail" style="margin-top:4px;">
                                        <i class="bi bi-fingerprint"></i>
                                        <span class="label">Biometric:</span>
                                        @if($resident->employee_code)
                                            <span class="biometric-badge-small {{ $resident->biometric_access ? 'enabled' : 'disabled' }}">
                                                <i class="bi {{ $resident->biometric_access ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                                {{ $resident->biometric_access ? 'Enabled' : 'Disabled' }}
                                            </span>
                                            @if($resident->last_sync_at)
                                                <span style="font-size:0.6rem; color:#6b7280; margin-left:4px;">
                                                    ({{ $resident->last_sync_at->format('d M Y') }})
                                                </span>
                                            @endif
                                        @else
                                            <span class="biometric-badge-small not-synced">
                                                <i class="bi bi-clock"></i> Not Synced
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Documents --}}
                                    <div class="resident-detail" style="margin-top:4px;">
                                        <i class="bi bi-files"></i>
                                        <span class="label">Docs:</span>
                                        @if ($resident->profile_image)
                                            <span class="document-badge has-doc" onclick="viewDocument('{{ $resident->profile_image_url }}', 'Profile Image')">
                                                <i class="bi bi-image"></i> Profile
                                            </span>
                                        @endif
                                        @if ($resident->aadhar_document)
                                            <span class="document-badge has-doc" onclick="viewDocument('{{ $resident->aadhar_document_url }}', 'Aadhar Document')">
                                                <i class="bi bi-file-earmark-pdf"></i> Aadhar
                                            </span>
                                        @endif
                                        @if ($resident->application_document)
                                            <span class="document-badge has-doc" onclick="viewDocument('{{ $resident->application_document_url }}', 'Application')">
                                                <i class="bi bi-file-earmark-text"></i> App
                                            </span>
                                        @endif
                                        @if (!$resident->profile_image && !$resident->aadhar_document && !$resident->application_document)
                                            <span style="font-size:0.65rem; color:#9ca3af;">No docs</span>
                                        @endif
                                    </div>

                                    {{-- Room Info --}}
                                    <div class="resident-room-info">
                                        <div class="d-flex justify-content-between">
                                            <span>
                                                <span class="label">Room:</span>
                                                <span class="value">#{{ $resident->room->room_no ?? 'N/A' }}</span>
                                            </span>
                                            <span>
                                                <span class="label">Bed:</span>
                                                <span class="value">#{{ $resident->bed->bed_no ?? 'N/A' }}</span>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <div style="font-size:0.7rem; color:#6b7280;">
                                                {{ $resident->hostel->hostel_name ?? 'N/A' }}
                                                @if($resident->hostel)
                                                    <span>{{ $resident->hostel->hostel_type == 'MEN' ? '👨' : '👩' }}</span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="food-badge {{ $resident->food_status == 'WITH_FOOD' ? 'with-food' : 'without-food' }}">
                                                    {{ $resident->food_status == 'WITH_FOOD' ? '🍽️' : '🍞' }}
                                                </span>
                                                <span class="resident-rent">
                                                    ₹{{ number_format($resident->rent_amount ?? 0, 0) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <button class="status-badge {{ strtolower($resident->status) }}" onclick="toggleStatus({{ $resident->id }})">
                                            <span class="dot"></span>
                                            {{ $resident->status }}
                                        </button>
                                        <div class="d-flex gap-1 no-print">
                                            <button class="btn-action text-primary" onclick="viewResidentDetails({{ $resident->id }})" title="View Full Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($resident->employee_code)
                                                <button class="btn-action text-warning" onclick="toggleBiometricAccess({{ $resident->id }})" title="Toggle Biometric Access">
                                                    <i class="bi bi-fingerprint"></i>
                                                </button>
                                            @else
                                                <button class="btn-action text-success" onclick="syncSingleBiometric({{ $resident->id }})" title="Sync to Biometric">
                                                    <i class="bi bi-cloud-upload"></i>
                                                </button>
                                            @endif
                                            <button class="btn-action text-primary" onclick="editResident({{ $resident->id }})" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn-action text-danger" onclick="deleteResident({{ $resident->id }})" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- No Results --}}
                <div id="noSearchResults" class="no-results-state" style="display:none;">
                    <i class="bi bi-search"></i>
                    <h5>No residents found</h5>
                    <p>No residents match your search criteria. Try adjusting your filters.</p>
                    <button class="btn-clear-filters" onclick="clearFilters()">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear All Filters
                    </button>
                </div>

            @else
                {{-- Empty State --}}
                <div class="ds-card">
                    <div class="empty-state">
                        <i class="bi bi-people"></i>
                        <h5>No residents found</h5>
                        <p class="text-muted">Register residents and allocate rooms.</p>
                        <button type="button" class="btn-primary-custom" onclick="openAddModal()">
                            <i class="bi bi-plus-circle"></i> Add Resident
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================
    ADD/EDIT MODAL - SCROLLABLE
    ============================================ --}}
    <div class="modal fade" id="residentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"><i class="bi bi-person-plus"></i> Add Resident</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="residentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="editId" name="edit_id">
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- Personal Information --}}
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-person rv-input-icon"></i>
                                    <input type="text" name="name" id="name" class="rv-input" placeholder="Full name" required>
                                </div>
                                <div class="invalid-feedback" id="name_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-phone rv-input-icon"></i>
                                    <input type="text" name="phone" id="phone" class="rv-input" placeholder="+91 98765 43210" required>
                                </div>
                                <div class="invalid-feedback" id="phone_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Parents Phone</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-person-lines-fill rv-input-icon"></i>
                                    <input type="text" name="parentsphone" id="parentsphone" class="rv-input" placeholder="+91 98765 43210">
                                </div>
                                <div class="invalid-feedback" id="parentsphone_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-envelope rv-input-icon"></i>
                                    <input type="email" name="email" id="email" class="rv-input" placeholder="resident@email.com">
                                </div>
                                <div class="invalid-feedback" id="email_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aadhaar No</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-credit-card rv-input-icon"></i>
                                    <input type="text" name="aadhaar_no" id="aadhaar_no" class="rv-input" placeholder="XXXX XXXX XXXX">
                                </div>
                                <div class="invalid-feedback" id="aadhaar_no_error"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-geo-alt rv-input-icon textarea-icon"></i>
                                    <textarea name="address" id="address" class="rv-input textarea-input" placeholder="Complete address"></textarea>
                                </div>
                                <div class="invalid-feedback" id="address_error"></div>
                            </div>

                            {{-- Documents Upload --}}
                            <div class="col-md-4">
                                <label class="form-label">Profile Image</label>
                                <div class="rv-input-box file-input-box">
                                    <input type="file" name="profile_image" id="profile_image" accept="image/*">
                                    <small class="text-muted" style="display:block; font-size:0.65rem;">JPG, PNG (Max 2MB)</small>
                                </div>
                                <div id="profile_image_preview" style="display:none; margin-top:6px;">
                                    <div class="file-preview-container">
                                        <img id="profile_preview_img" src="" alt="Preview">
                                        <div class="file-info">
                                            <div class="filename" id="profile_filename">File</div>
                                            <div class="filesize" id="profile_filesize">0 KB</div>
                                        </div>
                                        <button type="button" class="btn-action text-danger" onclick="removeFile('profile_image')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="profile_image_existing" style="display:none; margin-top:6px;">
                                    <div class="file-preview-container">
                                        <img id="profile_existing_img" src="" alt="Existing">
                                        <div class="file-info">
                                            <div class="filename">Current Profile</div>
                                        </div>
                                        <span class="existing-doc-badge"><i class="bi bi-check-circle"></i> Uploaded</span>
                                    </div>
                                </div>
                                <div class="invalid-feedback" id="profile_image_error"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Aadhar Document</label>
                                <div class="rv-input-box file-input-box">
                                    <input type="file" name="aadhar_document" id="aadhar_document" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted" style="display:block; font-size:0.65rem;">PDF, JPG, PNG (Max 5MB)</small>
                                </div>
                                <div id="aadhar_document_preview" style="display:none; margin-top:6px;">
                                    <div class="file-preview-container">
                                        <i class="bi bi-file-earmark-pdf" style="font-size:2rem; color:#dc2626;"></i>
                                        <div class="file-info">
                                            <div class="filename" id="aadhar_filename">File</div>
                                            <div class="filesize" id="aadhar_filesize">0 KB</div>
                                        </div>
                                        <button type="button" class="btn-action text-danger" onclick="removeFile('aadhar_document')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="aadhar_document_existing" style="display:none; margin-top:6px;">
                                    <div class="file-preview-container">
                                        <i class="bi bi-file-earmark-pdf" style="font-size:2rem; color:#dc2626;"></i>
                                        <div class="file-info">
                                            <div class="filename">Current Aadhar</div>
                                        </div>
                                        <a id="aadhar_existing_link" href="#" target="_blank" class="btn-action text-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <span class="existing-doc-badge"><i class="bi bi-check-circle"></i> Uploaded</span>
                                    </div>
                                </div>
                                <div class="invalid-feedback" id="aadhar_document_error"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Application Document</label>
                                <div class="rv-input-box file-input-box">
                                    <input type="file" name="application_document" id="application_document" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted" style="display:block; font-size:0.65rem;">PDF, JPG, PNG (Max 5MB)</small>
                                </div>
                                <div id="application_document_preview" style="display:none; margin-top:6px;">
                                    <div class="file-preview-container">
                                        <i class="bi bi-file-earmark-text" style="font-size:2rem; color:#2563eb;"></i>
                                        <div class="file-info">
                                            <div class="filename" id="application_filename">File</div>
                                            <div class="filesize" id="application_filesize">0 KB</div>
                                        </div>
                                        <button type="button" class="btn-action text-danger" onclick="removeFile('application_document')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="application_document_existing" style="display:none; margin-top:6px;">
                                    <div class="file-preview-container">
                                        <i class="bi bi-file-earmark-text" style="font-size:2rem; color:#2563eb;"></i>
                                        <div class="file-info">
                                            <div class="filename">Current Application</div>
                                        </div>
                                        <a id="application_existing_link" href="#" target="_blank" class="btn-action text-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <span class="existing-doc-badge"><i class="bi bi-check-circle"></i> Uploaded</span>
                                    </div>
                                </div>
                                <div class="invalid-feedback" id="application_document_error"></div>
                            </div>

                            {{-- Accommodation Details --}}
                            <div class="col-md-4">
                                <label class="form-label">Hostel <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-building rv-input-icon"></i>
                                    <select name="hostel_id" id="hostel_id" class="rv-input" required>
                                        <option value="">Select Hostel</option>
                                        @foreach ($hostels as $hostel)
                                            <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }} ({{ $hostel->hostel_code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="invalid-feedback" id="hostel_id_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Room <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-door-open rv-input-icon"></i>
                                    <select name="room_id" id="room_id" class="rv-input" required>
                                        <option value="">Select Room</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback" id="room_id_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bed <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-bed rv-input-icon"></i>
                                    <select name="bed_id" id="bed_id" class="rv-input" required>
                                        <option value="">Select Bed</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback" id="bed_id_error"></div>
                            </div>

                            {{-- Food & Rent --}}
                            <div class="col-md-6">
                                <label class="form-label">Food Status <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-egg-fried rv-input-icon"></i>
                                    <select name="food_status" id="food_status" class="rv-input" required>
                                        <option value="">Select</option>
                                        <option value="WITH_FOOD">🍽️ With Food</option>
                                        <option value="WITHOUT_FOOD">🍞 Without Food</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback" id="food_status_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rent (₹) <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-currency-rupee rv-input-icon"></i>
                                    <input type="number" name="rent_amount" id="rent_amount" class="rv-input"
                                           placeholder="0.00" step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback" id="rent_amount_error"></div>
                            </div>

                            {{-- Financial & Dates --}}
                            <div class="col-md-6">
                                <label class="form-label">Deposit (₹)</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-currency-rupee rv-input-icon"></i>
                                    <input type="number" name="deposit_amount" id="deposit_amount" class="rv-input"
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                                <div class="invalid-feedback" id="deposit_amount_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Joining Date <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-calendar3 rv-input-icon"></i>
                                    <input type="date" name="joining_date" id="joining_date" class="rv-input" required>
                                </div>
                                <div class="invalid-feedback" id="joining_date_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-toggle-on rv-input-icon"></i>
                                    <select name="status" id="status" class="rv-input" required>
                                        <option value="ACTIVE">✅ Active</option>
                                        <option value="VACATED">❌ Vacated</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback" id="status_error"></div>
                            </div>
                            <div class="col-md-6" id="vacateDateDiv" style="display:none;">
                                <label class="form-label">Vacate Date</label>
                                <div class="rv-input-box">
                                    <i class="bi bi-calendar-x rv-input-icon"></i>
                                    <input type="date" name="vacate_date" id="vacate_date" class="rv-input">
                                </div>
                                <div class="invalid-feedback" id="vacate_date_error"></div>
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
    DETAILS VIEW MODAL
    ============================================ --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-badge"></i> Resident Details</h5>
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

    {{-- ============================================
    DOCUMENT VIEWER MODAL
    ============================================ --}}
    <div class="modal fade" id="documentViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentViewerTitle">Document Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" id="documentViewerBody">
                    <div id="documentViewerContent">
                        <p class="text-muted">No document selected</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal" style="background:#6b7280;">Close</button>
                    <a href="#" id="documentDownloadLink" target="_blank" class="btn-primary-custom" style="text-decoration:none;">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container" id="flashMessageContainer"></div>
@endsection

{{-- ============================================
JAVASCRIPT - FIXED FILTERS WITH DEBUG
============================================ --}}
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================================
// VARIABLES
// ============================================
let residentModal, detailsModal, documentViewerModal;

$(document).ready(function() {
    console.log('✅ Document ready!');

    // Initialize Modals
    residentModal = new bootstrap.Modal(document.getElementById('residentModal'), {
        backdrop: 'static',
        keyboard: true
    });
    detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'), {
        backdrop: 'static',
        keyboard: true
    });
    documentViewerModal = new bootstrap.Modal(document.getElementById('documentViewerModal'), {
        backdrop: 'static',
        keyboard: true
    });

    // ============================================
    // FILTER BINDING - USING INPUT/CHANGE EVENTS
    // ============================================

    // ✅ Search with keyup (debounced)
    let searchTimeout;
    $('#searchResident').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            console.log('🔍 Searching for:', $('#searchResident').val());
            applyFilters();
        }, 300);
    });

    // ✅ All filter dropdowns - use change event
    $('#filterStatus').on('change', function() {
        console.log('📌 Status filter:', $(this).val());
        applyFilters();
    });

    $('#filterHostel').on('change', function() {
        console.log('🏠 Hostel filter:', $(this).val());
        applyFilters();
    });

    $('#filterGender').on('change', function() {
        console.log('👤 Gender filter:', $(this).val());
        applyFilters();
    });

    $('#filterFood').on('change', function() {
        console.log('🍽️ Food filter:', $(this).val());
        applyFilters();
    });

    $('#filterBiometric').on('change', function() {
        console.log('🔒 Biometric filter:', $(this).val());
        applyFilters();
    });

    // ✅ Add Resident Button
    $('#addResidentBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    // ✅ Modal hidden event
    $('#residentModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    // ✅ Form submit
    $('#residentForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // ✅ Hostel -> Room
    $('#hostel_id').on('change', function() {
        let hostelId = $(this).val();
        if (hostelId) {
            $.ajax({
                url: "{{ route('admin.residents.get-rooms') }}",
                type: 'POST',
                data: { hostel_id: hostelId, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    let select = $('#room_id');
                    select.empty().append('<option value="">Select Room</option>');
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(key, room) {
                            let bedInfo = room.available_beds > 0 ? ' (Beds: ' + room
                                .available_beds + ')' : ' (Full)';
                            select.append('<option value="' + room.id + '" data-beds="' +
                                room.available_beds + '">Room #' + room.room_no +
                                ' - ' + room.room_type.room_type_name + bedInfo +
                                '</option>');
                        });
                    } else {
                        select.append('<option value="">No rooms available</option>');
                    }
                    $('#bed_id').empty().append('<option value="">Select Bed</option>');
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!',
                        'error');
                    }
                }
            });
        } else {
            $('#room_id').empty().append('<option value="">Select Room</option>');
            $('#bed_id').empty().append('<option value="">Select Bed</option>');
        }
    });

    // ✅ Room -> Bed
    $('#room_id').on('change', function() {
        let roomId = $(this).val();
        if (roomId) {
            $.ajax({
                url: '/admin/residents/room/' + roomId + '/beds',
                type: 'GET',
                success: function(response) {
                    let select = $('#bed_id');
                    select.empty().append('<option value="">Select Bed</option>');
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(key, bed) {
                            let statusLabel = bed.status === 'OCCUPIED' ?
                                ' (Occupied)' : ' (Vacant)';
                            let disabled = bed.status === 'OCCUPIED' ? 'disabled' :
                                '';
                            select.append('<option value="' + bed.id + '" ' +
                                disabled + '>Bed #' + bed.bed_no + ' (' + bed
                                .bed_type + ')' + statusLabel + '</option>');
                        });
                    } else {
                        select.append('<option value="">No vacant beds</option>');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!',
                        'error');
                    }
                }
            });
        } else {
            $('#bed_id').empty().append('<option value="">Select Bed</option>');
        }
    });

    // ✅ Status -> Vacate Date
    $('#status').on('change', function() {
        if ($(this).val() === 'VACATED') {
            $('#vacateDateDiv').show();
            $('#vacate_date').prop('required', true);
        } else {
            $('#vacateDateDiv').hide();
            $('#vacate_date').prop('required', false);
        }
    });

    // ✅ Set joining date
    $('#joining_date').val(new Date().toISOString().split('T')[0]);

    // ✅ File input handlers
    setupFileInput('profile_image', 'image');
    setupFileInput('aadhar_document', 'document');
    setupFileInput('application_document', 'document');

    // ✅ Run initial filter after page load
    console.log('🔄 Running initial filter...');
    applyFilters();

    console.log('✅ All filters initialized!');
});

// ============================================
// ✅ FIXED: applyFilters with PROPER DATA ATTRIBUTES
// ============================================
function applyFilters() {
    console.log('🚀 applyFilters() called!');

    var status = $('#filterStatus').val() || '';
    var hostel = $('#filterHostel').val() || '';
    var gender = $('#filterGender').val() || '';
    var food = $('#filterFood').val() || '';
    var biometric = $('#filterBiometric').val() || '';
    var search = $('#searchResident').val().toLowerCase().trim() || '';

    console.log('📊 Filters:', { status, hostel, gender, food, biometric, search });

    var visibleCount = 0;
    var totalCount = $('.resident-item').length;

    console.log('📦 Total resident items:', totalCount);

    if (totalCount === 0) {
        console.warn('⚠️ No resident items found in DOM!');
        return;
    }

    $('.resident-item').each(function(index) {
        var show = true;
        var $item = $(this);

        // ✅ Get data attributes from the element
        var resStatus = $item.attr('data-status') || '';
        var resHostel = $item.attr('data-hostel') || '';
        var resGender = $item.attr('data-gender') || '';
        var resFood = $item.attr('data-food') || '';
        var resBiometric = $item.attr('data-biometric') || '';

        // Search fields - get from data attributes
        var resName = ($item.attr('data-name') || '').toLowerCase();
        var resCode = ($item.attr('data-code') || '').toLowerCase();
        var resPhone = ($item.attr('data-phone') || '').toLowerCase();
        var resEmail = ($item.attr('data-email') || '').toLowerCase();
        var resId = String($item.attr('data-id') || '');

        // ✅ Log first 3 items for debugging
        if (index < 3) {
            console.log(`🔍 Item ${index + 1} data:`, {
                status: resStatus,
                hostel: resHostel,
                gender: resGender,
                food: resFood,
                biometric: resBiometric,
                name: resName,
                code: resCode
            });
        }

        // ✅ Apply status filter
        if (status && resStatus !== status) {
            show = false;
        }

        // ✅ Apply hostel filter
        if (hostel && show && resHostel !== String(hostel)) {
            show = false;
        }

        // ✅ Apply gender filter
        if (gender && show && resGender !== gender) {
            show = false;
        }

        // ✅ Apply food filter
        if (food && show && resFood !== food) {
            show = false;
        }

        // ✅ Apply biometric filter
        if (biometric && show && resBiometric !== biometric) {
            show = false;
        }

        // ✅ Apply search filter
        if (search && show) {
            var searchMatch = false;

            // Check all searchable fields
            if (resName.includes(search)) searchMatch = true;
            if (resCode.includes(search)) searchMatch = true;
            if (resPhone.includes(search)) searchMatch = true;
            if (resEmail.includes(search)) searchMatch = true;
            if (resId.includes(search)) searchMatch = true;

            // Also check the full text content as fallback
            var textContent = $item.text().toLowerCase();
            if (textContent.includes(search)) searchMatch = true;

            if (!searchMatch) {
                show = false;
            }
        }

        // ✅ Show or hide
        if (show) {
            $item.show();
            visibleCount++;
        } else {
            $item.hide();
        }
    });

    console.log('✅ Visible count:', visibleCount, '/', totalCount);

    // ✅ Update result count
    var resultCountEl = $('#resultCount');
    if (visibleCount === totalCount) {
        resultCountEl.text('');
    } else {
        resultCountEl.text('Showing ' + visibleCount + ' of ' + totalCount + ' residents');
    }

    // ✅ Show/hide no results message
    var noResultsDiv = $('#noSearchResults');
    if (visibleCount === 0 && totalCount > 0) {
        noResultsDiv.show();
        console.warn('⚠️ No results found!');
    } else {
        noResultsDiv.hide();
    }
}

// ============================================
// CLEAR FILTERS
// ============================================
function clearFilters() {
    console.log('🧹 Clearing all filters...');
    $('#filterStatus, #filterHostel, #filterGender, #filterFood, #filterBiometric').val('');
    $('#searchResident').val('');
    $('#resultCount').text('');
    $('#noSearchResults').hide();
    applyFilters();
    console.log('✅ Filters cleared!');
}

// ============================================
// FILE INPUT HANDLERS
// ============================================
function setupFileInput(inputId, type) {
    $('#' + inputId).on('change', function() {
        const file = this.files[0];
        const previewId = inputId + '_preview';
        const existingId = inputId + '_existing';

        $('#' + existingId).hide();

        if (file) {
            const fileSize = (file.size / 1024).toFixed(1);
            const fileName = file.name;

            if (type === 'image') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profile_preview_img').attr('src', e.target.result);
                    $('#profile_filename').text(fileName);
                    $('#profile_filesize').text(fileSize + ' KB');
                    $('#' + previewId).show();
                };
                reader.readAsDataURL(file);
            } else {
                const iconClass = file.type === 'application/pdf' ? 'bi-file-earmark-pdf' :
                    'bi-file-earmark-text';
                const iconColor = file.type === 'application/pdf' ? '#dc2626' : '#2563eb';
                const previewContainer = $('#' + previewId + ' .file-preview-container');
                previewContainer.find('i').attr('class', 'bi ' + iconClass).css('color', iconColor);
                $('#' + inputId + '_filename').text(fileName);
                $('#' + inputId + '_filesize').text(fileSize + ' KB');
                $('#' + previewId).show();
            }
        } else {
            $('#' + previewId).hide();
        }
    });
}

function removeFile(inputId) {
    $('#' + inputId).val('');
    $('#' + inputId + '_preview').hide();
    const existingId = inputId + '_existing';
    if ($('#' + existingId).data('has-file') === true) {
        $('#' + existingId).show();
    }
}

// ============================================
// BIOMETRIC FUNCTIONS
// ============================================
function syncAllBiometric() {
    Swal.fire({
        title: 'Sync All Residents?',
        text: "This will sync all residents to the biometric system.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, sync them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.residents.sync-all-biometric') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast('Synced ' + response.success_count + ' residents successfully!', 'success');
                        if (response.failure_count > 0) {
                            showToast(response.failure_count + ' residents failed to sync.', 'error');
                        }
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast(response.message || 'Failed to sync residents', 'error');
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.error || 'Failed to sync!', 'error');
                }
            });
        }
    });
}

w

function toggleBiometricAccess(id) {
    $.ajax({
        url: '/admin/residents/' + id + '/toggle-biometric',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(response.message || 'Failed to toggle biometric access!', 'error');
            }
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.error || 'Failed to toggle biometric access!', 'error');
        }
    });
}

// ============================================
// VIEW RESIDENT DETAILS
// ============================================
function viewResidentDetails(id) {
    detailsModal.show();
    $('#detailsBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading resident details...</p>
        </div>
    `);

    $.ajax({
        url: '/admin/residents/' + id + '/details',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderDetails(response.data);
            } else {
                $('#detailsBody').html(`
                    <div class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle" style="font-size:3rem;"></i>
                        <p class="mt-2">${response.error || 'Failed to load details'}</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#detailsBody').html(`
                <div class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle" style="font-size:3rem;"></i>
                    <p class="mt-2">Failed to load resident details</p>
                </div>
            `);
        }
    });
}

function renderDetails(data) {
    let html = `
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="text-center p-3" style="background: #f8fafc; border-radius:12px;">
                    <div style="width:150px; height:150px; border-radius:50%; margin:0 auto; overflow:hidden; border:4px solid var(--gold); background:var(--primary);">
                        <img src="${data.profile_image || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.name) + '&background=c5a028&color=fff&size=150'}"
                             alt="${data.name}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <h3 class="mt-3 mb-1">${data.name}</h3>
                    <p class="text-muted small">${data.resident_code}</p>
                    <div class="mt-2">
                        <span class="biometric-badge-small ${data.biometric.access_enabled ? 'enabled' : 'disabled'}">
                            <i class="bi ${data.biometric.access_enabled ? 'bi-check-circle' : 'bi-x-circle'}"></i>
                            ${data.biometric.access_status}
                        </span>
                        ${data.biometric.employee_code ? `<span class="ms-2 badge bg-secondary">${data.biometric.employee_code}</span>` : ''}
                    </div>
                    <div class="mt-2 d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge-custom ${data.status.badge}">${data.status.label}</span>
                        <span class="food-badge ${data.financial.food_status_badge}">
                            ${data.financial.food_status_icon} ${data.financial.food_status_label}
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="resident-rent">${data.financial.rent_formatted} / month</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="card-title"><i class="bi bi-person"></i> Personal Info</div>
                            <div class="detail-item"><span class="label">Phone</span><span class="value">${data.phone}</span></div>
                            ${data.parents_phone ? `<div class="detail-item"><span class="label">Parents Phone</span><span class="value">${data.parents_phone}</span></div>` : ''}
                            ${data.email ? `<div class="detail-item"><span class="label">Email</span><span class="value">${data.email}</span></div>` : ''}
                            ${data.aadhaar_no ? `<div class="detail-item"><span class="label">Aadhaar</span><span class="value">${data.aadhaar_no}</span></div>` : ''}
                            ${data.address ? `<div class="detail-item"><span class="label">Address</span><span class="value" style="text-align:left;">${data.address}</span></div>` : ''}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="card-title"><i class="bi bi-building"></i> Accommodation</div>
                            <div class="detail-item"><span class="label">Hostel</span><span class="value">${data.hostel.name} ${data.hostel.type_icon}</span></div>
                            <div class="detail-item"><span class="label">Room</span><span class="value">#${data.room.number} (${data.room.type})</span></div>
                            <div class="detail-item"><span class="label">Bed</span><span class="value">#${data.bed.number} (${data.bed.type})</span></div>
                            <div class="detail-item"><span class="label">Joining Date</span><span class="value">${data.status.joining_date_formatted}</span></div>
                            ${data.status.vacate_date ? `<div class="detail-item"><span class="label">Vacate Date</span><span class="value">${data.status.vacate_date_formatted}</span></div>` : ''}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="card-title"><i class="bi bi-wallet"></i> Financial</div>
                            <div class="detail-item"><span class="label">Rent</span><span class="value">${data.financial.rent_formatted}</span></div>
                            <div class="detail-item"><span class="label">Deposit</span><span class="value">${data.financial.deposit_formatted || '₹0.00'}</span></div>
                            <div class="detail-item"><span class="label">Food Status</span><span class="value">${data.financial.food_status_icon} ${data.financial.food_status_label}</span></div>
                            <div class="detail-item"><span class="label">Status</span><span class="value"><span class="badge-custom ${data.status.badge}">${data.status.label}</span></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="card-title"><i class="bi bi-fingerprint"></i> Biometric</div>
                            <div class="detail-item"><span class="label">Employee Code</span><span class="value"><code>${data.biometric.employee_code}</code></span></div>
                            <div class="detail-item"><span class="label">Access Status</span><span class="value"><span class="biometric-badge-small ${data.biometric.access_enabled ? 'enabled' : 'disabled'}"><i class="bi ${data.biometric.access_enabled ? 'bi-check-circle' : 'bi-x-circle'}"></i> ${data.biometric.access_status}</span></span></div>
                            <div class="detail-item"><span class="label">Last Synced</span><span class="value">${data.biometric.last_sync_at}</span></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-card">
                            <div class="card-title"><i class="bi bi-credit-card"></i> Payment Status</div>
                            ${data.current_payment ? `
                            <div class="row g-3">
                                <div class="col-md-3"><div style="text-align:center; padding:0.5rem; background:#f8fafc; border-radius:8px;"><div style="font-size:0.7rem; color:#6b7280;">Month</div><div style="font-weight:600;">${data.current_payment.month_name} ${data.current_payment.year}</div></div></div>
                                <div class="col-md-3"><div style="text-align:center; padding:0.5rem; background:#f8fafc; border-radius:8px;"><div style="font-size:0.7rem; color:#6b7280;">Rent</div><div style="font-weight:600;">₹${data.current_payment.rent_amount}</div></div></div>
                                <div class="col-md-3"><div style="text-align:center; padding:0.5rem; background:#f8fafc; border-radius:8px;"><div style="font-size:0.7rem; color:#6b7280;">Paid</div><div style="font-weight:600; color:var(--success);">₹${data.current_payment.total_paid}</div></div></div>
                                <div class="col-md-3"><div style="text-align:center; padding:0.5rem; background:#f8fafc; border-radius:8px;"><div style="font-size:0.7rem; color:#6b7280;">Status</div><div style="font-weight:600;">${data.current_payment.status_label}</div></div></div>
                            </div>` : '<p class="text-muted text-center">No payment record for current month</p>'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('#detailsBody').html(html);
}

// ============================================
// DOCUMENT VIEWER
// ============================================
function viewDocument(url, title) {
    if (!url) {
        showToast('Document not found!', 'error');
        return;
    }

    document.getElementById('documentViewerTitle').textContent = title;
    document.getElementById('documentDownloadLink').href = url;

    const content = document.getElementById('documentViewerContent');

    if (url.match(/\.(jpeg|jpg|png|gif)$/i)) {
        content.innerHTML =
            `<img src="${url}" alt="${title}" style="max-width:100%; max-height:70vh; border-radius:8px;">`;
    } else {
        content.innerHTML = `
            <iframe src="${url}" style="width:100%; height:70vh; border:none; border-radius:8px;"></iframe>
            <p class="text-muted mt-2" style="font-size:0.8rem;">
                <i class="bi bi-info-circle"></i> If the document doesn't load,
                <a href="${url}" target="_blank">click here to open it directly</a>
            </p>
        `;
    }

    documentViewerModal.show();
}

// ============================================
// BULK ACTIONS
// ============================================
function updateBulkActions() {
    var checked = $('.resident-checkbox:checked');
    var count = checked.length;

    if (count > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(count);
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    $('.resident-checkbox').prop('checked', false);
    updateBulkActions();
}

function getSelectedIds() {
    var ids = [];
    $('.resident-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function bulkStatusUpdate() {
    var ids = getSelectedIds();
    var status = $('#bulkStatusSelect').val();

    if (ids.length === 0 || !status) {
        showToast('Please select residents and a status', 'error');
        return;
    }

    Swal.fire({
        title: 'Update Status?',
        text: "Are you sure you want to update " + ids.length + " residents to " + status + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c5a028',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, update them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.residents.bulk-status') }}",
                type: 'POST',
                data: { ids: ids, status: status, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!',
                        'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to update!',
                        'error');
                    }
                }
            });
        }
    });
}

function bulkDelete() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;

    Swal.fire({
        title: 'Delete Residents?',
        text: "Are you sure you want to delete " + ids.length + " residents? This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.residents.bulk-delete') }}",
                type: 'POST',
                data: { ids: ids, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!',
                        'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to delete!',
                        'error');
                    }
                }
            });
        }
    });
}

// ============================================
// EXPORT
// ============================================
function exportData() {
    window.location.href = "{{ route('admin.residents.export') }}";
}

// ============================================
// MODAL FUNCTIONS
// ============================================
function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Resident';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('joining_date').value = new Date().toISOString().split('T')[0];
    residentModal.show();
}

function resetForm() {
    const form = document.getElementById('residentForm');
    form.reset();
    $('#room_id').empty().append('<option value="">Select Room</option>');
    $('#bed_id').empty().append('<option value="">Select Bed</option>');
    $('#vacateDateDiv').hide();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Resident';
    document.getElementById('joining_date').value = new Date().toISOString().split('T')[0];

    $('[id$="_preview"]').hide();
    $('[id$="_existing"]').hide();
    $('[id$="_existing"]').data('has-file', false);
}

// ============================================
// FORM SUBMISSION
// ============================================
function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.residents.store') }}";
    let formData = new FormData(document.getElementById('residentForm'));

    if (id) {
        url = "{{ url('admin/residents') }}/" + id;
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#saveBtn').prop('disabled', true).html(
                '<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                residentModal.hide();
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
            $('#saveBtn').prop('disabled', false).html(
                '<i class="bi bi-check-circle"></i> <span id="saveBtnText">' + text +
                '</span>');
        }
    });
}

// ============================================
// CRUD OPERATIONS
// ============================================
function editResident(id) {
    $.ajax({
        url: "{{ url('admin/residents') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Resident';
                document.getElementById('editId').value = data.id;
                document.getElementById('name').value = data.name;
                document.getElementById('phone').value = data.phone;
                document.getElementById('parentsphone').value = data.parentsphone || '';
                document.getElementById('email').value = data.email || '';
                document.getElementById('aadhaar_no').value = data.aadhaar_no || '';
                document.getElementById('address').value = data.address || '';
                document.getElementById('hostel_id').value = data.hostel_id;
                document.getElementById('food_status').value = data.food_status || '';
                document.getElementById('rent_amount').value = data.rent_amount || 0;
                document.getElementById('deposit_amount').value = data.deposit_amount || 0;
                document.getElementById('status').value = data.status;

                if (data.joining_date) {
                    const joiningDate = new Date(data.joining_date);
                    document.getElementById('joining_date').value = joiningDate.toISOString()
                        .split('T')[0];
                }

                if (data.vacate_date) {
                    const vacateDate = new Date(data.vacate_date);
                    document.getElementById('vacate_date').value = vacateDate.toISOString()
                        .split('T')[0];
                    $('#vacateDateDiv').show();
                } else {
                    $('#vacateDateDiv').hide();
                    document.getElementById('vacate_date').value = '';
                }

                document.getElementById('saveBtnText').textContent = 'Update';

                // Show existing documents
                if (data.profile_image) {
                    $('#profile_image_existing').data('has-file', true);
                    $('#profile_image_existing').show();
                    $('#profile_existing_img').attr('src', '{{ asset('') }}' + data
                    .profile_image);
                } else {
                    $('#profile_image_existing').hide();
                }

                if (data.aadhar_document) {
                    $('#aadhar_document_existing').data('has-file', true);
                    $('#aadhar_document_existing').show();
                    $('#aadhar_existing_link').attr('href', '{{ asset('') }}' + data
                        .aadhar_document);
                } else {
                    $('#aadhar_document_existing').hide();
                }

                if (data.application_document) {
                    $('#application_document_existing').data('has-file', true);
                    $('#application_document_existing').show();
                    $('#application_existing_link').attr('href', '{{ asset('') }}' + data
                        .application_document);
                } else {
                    $('#application_document_existing').hide();
                }

                // Load rooms
                $.ajax({
                    url: "{{ route('admin.residents.get-rooms') }}",
                    type: 'POST',
                    data: { hostel_id: data.hostel_id, _token: '{{ csrf_token() }}' },
                    success: function(roomResponse) {
                        let select = $('#room_id');
                        select.empty().append('<option value="">Select Room</option>');

                        if (roomResponse.success && roomResponse.data.length > 0) {
                            let currentRoomExists = false;

                            $.each(roomResponse.data, function(key, room) {
                                let bedInfo = room.available_beds > 0 ? ' (Beds: ' +
                                    room.available_beds + ')' : ' (Full)';
                                let selected = (room.id == data.room_id) ?
                                    'selected' : '';
                                if (room.id == data.room_id) {
                                    currentRoomExists = true;
                                }

                                select.append('<option value="' + room.id + '" ' +
                                    selected + ' data-beds="' + room
                                    .available_beds + '">Room #' + room
                                    .room_no + ' - ' + room.room_type
                                    .room_type_name + bedInfo + '</option>');
                            });

                            if (!currentRoomExists && data.room_id) {
                                $.ajax({
                                    url: '/admin/residents/room/' + data
                                        .room_id + '/details',
                                    type: 'GET',
                                    success: function(
                                    currentRoomResponse) {
                                        if (currentRoomResponse
                                            .success) {
                                            let room = currentRoomResponse
                                                .data;
                                            select.append(
                                                '<option value="' +
                                                room.id +
                                                '" selected>Room #' +
                                                room.room_no +
                                                ' - ' + room
                                                .room_type
                                                .room_type_name +
                                                ' (Current Room)</option>'
                                                );
                                        }
                                    }
                                });
                            }

                            if (data.room_id) {
                                select.val(data.room_id);
                            }
                        } else {
                            select.append('<option value="">No rooms available</option>');
                        }

                        // Load beds
                        $.ajax({
                            url: '/admin/residents/room/' + data.room_id +
                                '/beds',
                            type: 'GET',
                            success: function(bedResponse) {
                                let bedSelect = $('#bed_id');
                                bedSelect.empty().append(
                                    '<option value="">Select Bed</option>');

                                if (bedResponse.success && bedResponse
                                    .data.length > 0) {
                                    let currentBedExists = false;

                                    bedResponse.data.sort(function(
                                    a, b) {
                                        if (a.id == data.bed_id)
                                            return -1;
                                        if (b.id == data.bed_id)
                                            return 1;
                                        if (a.status === 'OCCUPIED' &&
                                            b.status !== 'OCCUPIED')
                                            return -1;
                                        if (a.status !== 'OCCUPIED' &&
                                            b.status === 'OCCUPIED')
                                            return 1;
                                        return a.bed_no.localeCompare(
                                            b.bed_no);
                                    });

                                    $.each(bedResponse.data, function(
                                        key, bed) {
                                        let selected = (bed.id ==
                                            data.bed_id) ?
                                            'selected' : '';
                                        let statusLabel = '';

                                        if (bed.id == data.bed_id) {
                                            statusLabel =
                                            ' (Current)';
                                            currentBedExists = true;
                                        } else if (bed.status ===
                                            'OCCUPIED') {
                                            statusLabel =
                                            ' (Occupied)';
                                        } else {
                                            statusLabel =
                                            ' (Vacant)';
                                        }

                                        let disabled = (bed.status ===
                                            'OCCUPIED' && bed.id !=
                                            data.bed_id) ?
                                            'disabled' : '';

                                        bedSelect.append(
                                            '<option value="' + bed
                                            .id + '" ' + selected +
                                            ' ' + disabled + '>' +
                                            'Bed #' + bed.bed_no +
                                            ' (' + bed.bed_type +
                                            ')' + statusLabel +
                                            '</option>'
                                        );
                                    });

                                    if (!currentBedExists && data
                                        .bed_id) {
                                        bedSelect.append(
                                            '<option value="' + data
                                            .bed_id +
                                            '" selected>Bed #' +
                                            (data.bed ? data.bed
                                                .bed_no : 'N/A') +
                                            ' (Current Bed)</option>'
                                            );
                                    }

                                    if (data.bed_id) {
                                        bedSelect.val(data.bed_id);
                                    }
                                } else {
                                    if (data.bed) {
                                        bedSelect.append(
                                            '<option value="' + data
                                            .bed.id +
                                            '" selected>Bed #' + data
                                            .bed.bed_no + ' (' + data
                                            .bed.bed_type +
                                            ') - Current</option>'
                                            );
                                    }
                                    bedSelect.append(
                                        '<option value="">No beds available</option>'
                                        );
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status === 403) {
                                    showToast(xhr.responseJSON
                                        ?.message ||
                                        'Permission denied!',
                                        'error');
                                }
                            }
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            showToast(xhr.responseJSON?.message ||
                                'Permission denied!', 'error');
                        }
                    }
                });

                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                residentModal.show();
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load resident data', 'error');
            }
        }
    });
}

function deleteResident(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone! All associated documents will also be deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/residents') }}/" + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!',
                            'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to delete!',
                            'error');
                    }
                }
            });
        }
    });
}

function toggleStatus(id) {
    Swal.fire({
        title: 'Toggle Status?',
        text: "Change resident status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c5a028',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/residents') }}/" + id + "/toggle-status",
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
                        showToast(xhr.responseJSON?.message || 'Permission denied!',
                            'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to update status!',
                            'error');
                    }
                }
            });
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
