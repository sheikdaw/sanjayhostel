{{-- resources/views/admin/complaint/index.blade.php --}}
@extends('layouts.office')

@section('title', 'Complaint Management')
@section('page_title', 'Complaint Management')

@push('styles')
    <style>
        /* ============================================
           GLOBAL STYLES (matches Hostel page)
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
            --purple: #7c3aed;
        }

        .complaint-container {
            max-width: 100%;
            padding: 0 15px;
        }

        /* ============================================
           HEADER
        ============================================ */
        .complaint-header {
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

        .complaint-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .complaint-header p {
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
        .stat-card.pending .number { color: var(--warning); }
        .stat-card.progress .number { color: var(--info); }
        .stat-card.resolved .number { color: var(--success); }
        .stat-card.rejected .number { color: var(--danger); }

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
           COMPLAINT CARD
        ============================================ */
        .complaint-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .complaint-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        /* Card Header (complaint number + status) */
        .complaint-card .card-header {
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .complaint-card .card-header .complaint-no {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.3px;
        }

        .complaint-card .card-header .complaint-date {
            font-size: 0.65rem;
            opacity: 0.85;
            display: block;
            margin-top: 2px;
        }

        /* Image block on the card */
        .complaint-image {
            position: relative;
            height: 160px;
            background: #f3f4f6;
            overflow: hidden;
        }

        .complaint-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .complaint-image .no-image {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
        }

        .complaint-image .no-image i {
            font-size: 2rem;
            margin-bottom: 4px;
        }

        .complaint-image .no-image span {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Status overlay pill on image */
        .complaint-image .overlay-status {
            position: absolute;
            top: 8px;
            left: 8px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .complaint-image .overlay-priority {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        /* Priority colors */
        .prio-text.low    { color: #64748b; }
        .prio-text.medium { color: #0284c7; }
        .prio-text.high   { color: #ea580c; }
        .prio-text.urgent { color: #dc2626; }

        /* Card body */
        .complaint-card .card-body {
            padding: 1rem 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Resident row */
        .resident-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 0.75rem;
            margin-bottom: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .resident-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(26, 58, 107, 0.2);
            overflow: hidden;
        }

        .resident-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .resident-info { flex: 1; min-width: 0; }
        .resident-info .name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .resident-info .meta {
            font-size: 0.7rem;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Description */
        .complaint-desc {
            font-size: 0.8rem;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        /* Meta chips */
        .meta-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            background: #f8fafc;
            border-radius: 4px;
            font-size: 0.68rem;
            color: #475569;
            font-weight: 500;
        }

        .meta-chip i {
            color: var(--gold);
            font-size: 0.65rem;
        }

        /* Admin remark callout */
        .admin-remark {
            font-size: 0.72rem;
            color: #92400e;
            background: #fef3c7;
            border-left: 3px solid var(--warning);
            padding: 0.4rem 0.6rem;
            border-radius: 0 6px 6px 0;
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .admin-remark i {
            margin-right: 4px;
        }

        /* Actions */
        .complaint-actions {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
            margin-top: auto;
        }

        .complaint-actions .btn-sm {
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: white;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex: 1;
            justify-content: center;
            white-space: nowrap;
        }

        .complaint-actions .btn-sm:hover { background: #f3f4f6; }

        .complaint-actions .btn-sm.primary:hover { background: #e3f2fd; border-color: #90caf9; color: #1565c0; }
        .complaint-actions .btn-sm.success:hover { background: #dcfce7; border-color: #86efac; color: #166534; }
        .complaint-actions .btn-sm.warning:hover { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
        .complaint-actions .btn-sm.danger:hover  { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

        .complaint-actions .btn-icon-only {
            flex: 0 0 auto;
            width: 32px;
            padding: 0.3rem;
        }

        /* ============================================
           STATUS BADGES
        ============================================ */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .status-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-badge.status-pending      { background: #fef3c7; color: #92400e; }
        .status-badge.status-pending .dot { background: #f59e0b; }

        .status-badge.status-in_progress      { background: #dbeafe; color: #1e40af; }
        .status-badge.status-in_progress .dot { background: #3b82f6; }

        .status-badge.status-resolved      { background: #dcfce7; color: #166534; }
        .status-badge.status-resolved .dot { background: #22c55e; }

        .status-badge.status-rejected      { background: #fee2e2; color: #991b1b; }
        .status-badge.status-rejected .dot { background: #ef4444; }

        /* ============================================
           MODAL (matches hostel design)
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

        .modal-body::-webkit-scrollbar { width: 6px; }
        .modal-body::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        .modal-body::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 3px; }

        /* ============================================
           FORM (matches hostel modal design)
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

        .rv-input-box.is-invalid { border-color: var(--danger); }

        .rv-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
            pointer-events: none;
        }

        .rv-input-icon.textarea-icon { top: 16px; transform: none; }

        .rv-input {
            width: 100%;
            padding: 0.6rem 0.8rem 0.6rem 2.4rem;
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.85rem;
            color: #1f2937;
        }

        .rv-input.textarea-input { min-height: 60px; resize: vertical; }

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

        .form-label .required { color: var(--danger); margin-left: 2px; }

        .invalid-feedback {
            font-size: 0.75rem;
            color: var(--danger);
            margin-top: 0.25rem;
        }

        /* ============================================
           BUTTONS (matches hostel design)
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

        .btn-secondary-custom:hover { background: #4b5563; }

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

        .toast-custom.error { border-left-color: var(--danger); }

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
           EMPTY / LOADING STATE
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

        .no-results-state h5 { color: #374151; margin-bottom: 0.5rem; }
        .no-results-state p { color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem; }

        /* Skeleton loading card */
        .skeleton-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            height: 100%;
        }
        .skeleton-card .sk-img { height: 160px; background: #e5e7eb; }
        .skeleton-card .sk-body { padding: 1rem; }
        .skeleton-card .sk-line {
            height: 10px;
            background: #e5e7eb;
            border-radius: 4px;
            margin-bottom: 8px;
            animation: pulse 1.4s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 768px) {
            .complaint-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                padding: 1rem;
            }
            .header-actions { justify-content: center; }
            .stats-grid { grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 0.5rem; }
            .stat-card .number { font-size: 1rem; }
            .filter-section { flex-direction: column; align-items: stretch; }
            .filter-section .filter-group { flex-wrap: wrap; }
            .filter-section select, .filter-section input { min-width: 100%; }
            .search-box { min-width: 100%; }
            .result-count { margin-left: 0; text-align: center; }
            .modal-body { max-height: calc(90vh - 130px); padding: 1rem; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .complaint-header h1 { font-size: 1.2rem; }
            .header-actions .btn-primary-custom,
            .header-actions .btn-secondary-custom { padding: 0.35rem 0.8rem; font-size: 0.75rem; }
        }
    </style>
@endpush

@section('content')
    <div class="complaint-container">

        {{-- ============================================
        HEADER
        ============================================ --}}
        <div class="complaint-header no-print">
            <div>
                <h1><i class="bi bi-clipboard-check"></i> Complaint Management</h1>
                <p>Review, update, and resolve complaints from all hostels</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn-secondary-custom" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button type="button" class="btn-primary-custom" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>

        {{-- ============================================
        STATISTICS
        ============================================ --}}
        <div class="stats-grid">
            <div class="stat-card total">
                <span class="icon">📋</span>
                <div class="number" id="statTotal">0</div>
                <div class="label">Total</div>
            </div>
            <div class="stat-card pending">
                <span class="icon">⏳</span>
                <div class="number" id="statPending">0</div>
                <div class="label">Pending</div>
            </div>
            <div class="stat-card progress">
                <span class="icon">🔄</span>
                <div class="number" id="statProgress">0</div>
                <div class="label">In Progress</div>
            </div>
            <div class="stat-card resolved">
                <span class="icon">✅</span>
                <div class="number" id="statResolved">0</div>
                <div class="label">Resolved</div>
            </div>
            <div class="stat-card rejected">
                <span class="icon">⛔</span>
                <div class="number" id="statRejected">0</div>
                <div class="label">Rejected</div>
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
                <select id="filterHostel">
                    <option value="">All Hostels</option>
                    @foreach($hostels as $h)
                        <option value="{{ $h->id }}">{{ $h->hostel_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <select id="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">⏳ Pending</option>
                    <option value="in_progress">🔄 In Progress</option>
                    <option value="resolved">✅ Resolved</option>
                    <option value="rejected">⛔ Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="filterPriority">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">🔴 Urgent</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="filterCategory">
                    <option value="">All Categories</option>
                    <option value="electrical">Electrical</option>
                    <option value="plumbing">Plumbing</option>
                    <option value="furniture">Furniture</option>
                    <option value="cleaning">Cleaning</option>
                    <option value="wifi">WiFi</option>
                    <option value="food">Food</option>
                    <option value="security">Security</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchComplaint" placeholder="Search by complaint #, name, phone, room...">
            </div>
            <button class="btn-clear-filters" onclick="clearFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Clear
            </button>
            <span class="result-count" id="resultCount"></span>
        </div>

        {{-- ============================================
        COMPLAINTS GRID
        ============================================ --}}
        <div id="complaintsContainer">
            <div class="row g-4" id="complaintsGrid"></div>

            {{-- No Results --}}
            <div id="noResults" class="no-results-state" style="display:none;">
                <i class="bi bi-inbox"></i>
                <h5>No complaints found</h5>
                <p>No complaints match your search criteria. Try adjusting your filters.</p>
                <button class="btn-clear-filters" onclick="clearFilters()">
                    <i class="bi bi-arrow-counterclockwise"></i> Clear All Filters
                </button>
            </div>
        </div>

        {{-- ============================================
        PAGINATION
        ============================================ --}}
        <div id="pagination" class="d-flex justify-content-center mt-4"></div>
    </div>

    {{-- ============================================
    UPDATE COMPLAINT MODAL
    ============================================ --}}
    <div class="modal fade" id="complaintModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i> Update Complaint
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="complaintForm">
                    @csrf
                    <input type="hidden" id="editId" name="id">
                    <div class="modal-body">

                        {{-- Complaint no banner --}}
                        <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px; padding:0.6rem 1rem; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <div>
                                <div style="font-size:0.7rem; color:#6b7280; text-transform:uppercase; font-weight:600;">Complaint No</div>
                                <div id="eComplaintNo" style="font-family:monospace; font-weight:700; color:var(--primary); font-size:0.95rem;">—</div>
                            </div>
                            <div id="eStatusBannerPill"></div>
                        </div>

                        {{-- Complaint image --}}
                        <div id="eImageWrap" style="display:none; margin-bottom:1rem;">
                            <div style="font-size:0.75rem; font-weight:600; color:#374151; margin-bottom:0.4rem;">
                                <i class="bi bi-image text-gold"></i> Attached Photo
                            </div>
                            <img id="eImage" style="width:100%; max-height:260px; object-fit:cover; border-radius:10px; border:1px solid #e5e7eb;">
                        </div>

                        <div class="row g-3">

                            {{-- Resident info (read only) --}}
                            <div class="col-12">
                                <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1px solid #bfdbfe; border-radius:10px; padding:0.9rem 1rem;">
                                    <div style="font-size:0.7rem; text-transform:uppercase; font-weight:700; color:#1e40af; margin-bottom:0.6rem; letter-spacing:0.3px;">
                                        <i class="bi bi-person-circle"></i> Resident Information
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div style="font-size:0.65rem; color:#6b7280; text-transform:uppercase; font-weight:600;">Name</div>
                                            <div id="eName" style="font-weight:600; color:#1f2937; font-size:0.85rem;">—</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="font-size:0.65rem; color:#6b7280; text-transform:uppercase; font-weight:600;">Phone</div>
                                            <div id="ePhone" style="font-weight:600; color:#1f2937; font-size:0.85rem;">—</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div style="font-size:0.65rem; color:#6b7280; text-transform:uppercase; font-weight:600;">Room · Hostel</div>
                                            <div id="eRoomHostel" style="font-weight:600; color:#1f2937; font-size:0.85rem;">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Category + Priority --}}
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-tag rv-input-icon"></i>
                                    <select name="category" id="eCategory" class="rv-input" required>
                                        <option value="electrical">Electrical</option>
                                        <option value="plumbing">Plumbing / Water</option>
                                        <option value="furniture">Furniture</option>
                                        <option value="cleaning">Cleaning / Housekeeping</option>
                                        <option value="wifi">WiFi / Internet</option>
                                        <option value="food">Food / Mess</option>
                                        <option value="security">Security</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-flag rv-input-icon"></i>
                                    <select name="priority" id="ePriority" class="rv-input" required>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">🔴 Urgent</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label">Description <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-card-text rv-input-icon textarea-icon"></i>
                                    <textarea name="description" id="eDescription" class="rv-input textarea-input" minlength="10" maxlength="2000" required></textarea>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="required">*</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-toggle-on rv-input-icon"></i>
                                    <select name="status" id="eStatus" class="rv-input" required>
                                        <option value="pending">⏳ Pending</option>
                                        <option value="in_progress">🔄 In Progress</option>
                                        <option value="resolved">✅ Resolved</option>
                                        <option value="rejected">⛔ Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Submitted At</label>
                                <div class="rv-input-box" style="opacity:0.75;">
                                    <i class="bi bi-clock-history rv-input-icon"></i>
                                    <input type="text" id="eCreatedAt" class="rv-input" disabled>
                                </div>
                            </div>

                            {{-- Admin remark --}}
                            <div class="col-12">
                                <label class="form-label">Admin Remark <span style="color:#9ca3af; font-weight:400; text-transform:none; letter-spacing:0; font-size:0.75rem;">(visible to resident)</span></label>
                                <div class="rv-input-box">
                                    <i class="bi bi-chat-left-text rv-input-icon textarea-icon"></i>
                                    <textarea name="admin_remark" id="eAdminRemark" class="rv-input textarea-input" placeholder="e.g. Plumber assigned — will visit by 5 PM"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-primary-custom" id="saveBtn">
                            <i class="bi bi-check-circle"></i> <span id="saveBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container" id="flashMessageContainer"></div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================================
// CONFIG
// ============================================
const CSRF = '{{ csrf_token() }}';
const ROUTES = {
    data:         "{{ route('admin.complaints.data') }}",
    stats:        "{{ route('admin.complaints.stats') }}",
    base:         "{{ url('admin/complaints') }}",
};
const PER_PAGE = 20;

let complaintModal;
let currentPage = 1;
let debounceT = null;

// ============================================
// INIT
// ============================================
$(document).ready(function () {
    complaintModal = new bootstrap.Modal(document.getElementById('complaintModal'), {
        backdrop: 'static',
        keyboard: true
    });

    // Bind filters
    $('#filterHostel, #filterStatus, #filterPriority, #filterCategory').on('change', function () {
        currentPage = 1;
        loadComplaints();
        loadStats();
    });

    $('#searchComplaint').on('keyup', function () {
        clearTimeout(debounceT);
        debounceT = setTimeout(() => {
            currentPage = 1;
            loadComplaints();
        }, 350);
    });

    // Form submit
    $('#complaintForm').on('submit', function (e) {
        e.preventDefault();
        submitForm();
    });

    // Initial load
    loadStats();
    loadComplaints();
});

// ============================================
// STATS
// ============================================
function loadStats() {
    const params = new URLSearchParams();
    const h = $('#filterHostel').val();
    if (h) params.set('hostel_id', h);

    $.ajax({
        url: ROUTES.stats + '?' + params.toString(),
        type: 'GET',
        success: function (res) {
            if (res.success) {
                $('#statTotal').text(res.data.total);
                $('#statPending').text(res.data.pending);
                $('#statProgress').text(res.data.inProgress);
                $('#statResolved').text(res.data.resolved);
                $('#statRejected').text(res.data.rejected);
            }
        }
    });
}

// ============================================
// LOAD COMPLAINTS
// ============================================
function loadComplaints(page) {
    if (page) currentPage = page;

    const $grid = $('#complaintsGrid');
    $('#noResults').hide();
    $('#pagination').empty();

    // Skeleton
    $grid.html(Array(8).fill(0).map(() => `
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="skeleton-card">
                <div class="sk-img"></div>
                <div class="sk-body">
                    <div class="sk-line" style="width:70%"></div>
                    <div class="sk-line" style="width:50%"></div>
                    <div class="sk-line" style="width:90%"></div>
                    <div class="sk-line" style="width:60%"></div>
                </div>
            </div>
        </div>
    `).join(''));

    const params = new URLSearchParams({ page: currentPage });
    const map = {
        search: 'searchComplaint',
        hostel_id: 'filterHostel',
        status: 'filterStatus',
        priority: 'filterPriority',
        category: 'filterCategory',
    };
    for (const [k, id] of Object.entries(map)) {
        const v = $('#' + id).val();
        if (v) params.set(k, v);
    }

    $.ajax({
        url: ROUTES.data + '?' + params.toString(),
        type: 'GET',
        success: function (res) {
            if (!res.success) {
                $grid.html('');
                showToast('Failed to load complaints', 'error');
                return;
            }
            const items = res.data.data || [];
            if (!items.length) {
                $grid.html('');
                $('#noResults').show();
                $('#resultCount').text('');
                return;
            }

            $grid.html(items.map(renderCard).join(''));

            // Result count
            const total = res.data.total;
            const from = res.data.from || 0;
            const to = res.data.to || 0;
            $('#resultCount').text(`Showing ${from}–${to} of ${total}`);

            renderPagination(res.data);
        },
        error: function (xhr) {
            $grid.html('');
            showToast(xhr.responseJSON?.message || 'Failed to load complaints', 'error');
        }
    });
}

// ============================================
// RENDER CARD
// ============================================
function renderCard(c) {
    const initial = (c.name || '?').charAt(0).toUpperCase();
    const avatar = c.resident_photo
        ? `<img src="${c.resident_photo}" alt="">`
        : initial;

    const statusLabel = {
        pending: 'Pending',
        in_progress: 'In Progress',
        resolved: 'Resolved',
        rejected: 'Rejected',
    }[c.status] || c.status;

    const statusDot = {
        pending: '#f59e0b',
        in_progress: '#3b82f6',
        resolved: '#22c55e',
        rejected: '#ef4444',
    }[c.status] || '#9ca3af';

    const priorityLabel = (c.priority || '—').toUpperCase();
    const categoryIcon = catIcon(c.category);

    const imageBlock = c.image
        ? `<div class="complaint-image">
                <img src="${c.image}" alt="${escHtml(c.complaint_number)}" loading="lazy"
                     onerror="this.parentNode.innerHTML='<div class=\\'no-image\\'><i class=\\'bi bi-image\\'></i><span>Image unavailable</span></div>'">
                <div class="overlay-status" style="color:${statusDot};">
                    <span class="dot" style="background:${statusDot};"></span>${statusLabel}
                </div>
                <div class="overlay-priority prio-text ${c.priority}">${priorityLabel}</div>
           </div>`
        : `<div class="complaint-image">
                <div class="no-image">
                    <i class="bi ${categoryIcon}"></i>
                    <span>${escHtml(c.category)}</span>
                </div>
                <div class="overlay-status" style="color:${statusDot};">
                    <span class="dot" style="background:${statusDot};"></span>${statusLabel}
                </div>
                <div class="overlay-priority prio-text ${c.priority}">${priorityLabel}</div>
           </div>`;

    return `
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="complaint-card">
                <div class="card-header">
                    <div>
                        <div class="complaint-no">${escHtml(c.complaint_number)}</div>
                        <span class="complaint-date"><i class="bi bi-clock"></i> ${escHtml(c.created_at)}</span>
                    </div>
                    <i class="bi bi-clipboard-check" style="font-size:1.4rem; opacity:0.6;"></i>
                </div>

                ${imageBlock}

                <div class="card-body">
                    <div class="resident-row">
                        <div class="resident-avatar">${avatar}</div>
                        <div class="resident-info">
                            <div class="name">${escHtml(c.name)}</div>
                            <div class="meta">
                                <i class="bi bi-phone"></i> ${escHtml(c.phone)}
                                <span style="color:#d1d5db;">·</span>
                                <i class="bi bi-door-closed"></i> ${escHtml(c.room_number || 'N/A')}
                            </div>
                        </div>
                    </div>

                    <div class="complaint-desc" title="${escHtml(c.description)}">
                        ${escHtml(c.description)}
                    </div>

                    <div class="meta-row">
                        <span class="meta-chip">
                            <i class="bi bi-tag"></i> ${escHtml(c.category)}
                        </span>
                        <span class="meta-chip">
                            <i class="bi bi-building"></i> ${escHtml(c.hostel_name || '—')}
                        </span>
                    </div>

                    ${c.admin_remark ? `
                        <div class="admin-remark">
                            <i class="bi bi-chat-left-text"></i>${escHtml(c.admin_remark)}
                        </div>` : ''}

                    <div class="complaint-actions">
                        <button class="btn-sm primary" onclick="openEdit(${c.id})" title="Update details">
                            <i class="bi bi-pencil"></i> Update
                        </button>
                        ${c.status !== 'resolved' ? `
                            <button class="btn-sm success btn-icon-only" onclick="quickStatus(${c.id}, 'resolved')" title="Mark Resolved">
                                <i class="bi bi-check2-circle"></i>
                            </button>` : ''}
                        ${c.status === 'pending' ? `
                            <button class="btn-sm warning btn-icon-only" onclick="quickStatus(${c.id}, 'in_progress')" title="Start Progress">
                                <i class="bi bi-play-circle"></i>
                            </button>` : ''}
                        <button class="btn-sm danger btn-icon-only" onclick="deleteComplaint(${c.id}, '${escHtml(c.complaint_number)}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
}

function catIcon(cat) {
    const map = {
        electrical: 'bi-lightning-charge',
        plumbing: 'bi-droplet',
        furniture: 'bi-lamp',
        cleaning: 'bi-brush',
        wifi: 'bi-wifi',
        food: 'bi-cup-hot',
        security: 'bi-shield-check',
        other: 'bi-info-circle',
    };
    return map[cat] || 'bi-info-circle';
}

function escHtml(s) {
    return (s ?? '').toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// ============================================
// PAGINATION
// ============================================
function renderPagination(p) {
    if (!p || p.last_page <= 1) {
        $('#pagination').empty();
        return;
    }

    let html = '<nav><ul class="pagination pagination-sm mb-0">';

    // Prev
    html += `<li class="page-item ${p.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadComplaints(${p.current_page - 1})">
            <i class="bi bi-chevron-left"></i>
        </a>
    </li>`;

    // Numbers
    for (let i = 1; i <= p.last_page; i++) {
        if (i === 1 || i === p.last_page || Math.abs(i - p.current_page) <= 1) {
            html += `<li class="page-item ${i === p.current_page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadComplaints(${i})">${i}</a>
            </li>`;
        } else if (Math.abs(i - p.current_page) === 2) {
            html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
        }
    }

    // Next
    html += `<li class="page-item ${p.current_page === p.last_page ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadComplaints(${p.current_page + 1})">
            <i class="bi bi-chevron-right"></i>
        </a>
    </li>`;

    html += '</ul></nav>';
    $('#pagination').html(html);
}

// ============================================
// OPEN EDIT MODAL
// ============================================
function openEdit(id) {
    $.ajax({
        url: ROUTES.base + '/' + id,
        type: 'GET',
        success: function (res) {
            if (!res.success) {
                showToast('Complaint not found', 'error');
                return;
            }
            const c = res.data;

            $('#editId').val(c.id);
            $('#eComplaintNo').text(c.complaint_number);
            $('#eName').text(c.name);
            $('#ePhone').text(c.phone);
            $('#eRoomHostel').text(`${c.room_number || 'N/A'} · ${c.hostel_name}`);
            $('#eCategory').val(c.category);
            $('#ePriority').val(c.priority);
            $('#eDescription').val(c.description);
            $('#eStatus').val(c.status);
            $('#eAdminRemark').val(c.admin_remark || '');
            $('#eCreatedAt').val(c.created_at);

            // Status banner pill
            const statusColor = {
                pending: '#f59e0b',
                in_progress: '#3b82f6',
                resolved: '#22c55e',
                rejected: '#ef4444',
            }[c.status] || '#9ca3af';
            const statusLabel = {
                pending: 'Pending', in_progress: 'In Progress',
                resolved: 'Resolved', rejected: 'Rejected',
            }[c.status] || c.status;
            $('#eStatusBannerPill').html(
                `<span class="status-badge status-${c.status}">
                    <span class="dot" style="background:${statusColor};"></span>${statusLabel}
                 </span>`
            );

            // Image
            if (c.image) {
                $('#eImage').attr('src', c.image);
                $('#eImageWrap').show();
            } else {
                $('#eImageWrap').hide();
            }

            // Clear validation
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');

            complaintModal.show();
        },
        error: function (xhr) {
            showToast(xhr.responseJSON?.message || 'Failed to load complaint', 'error');
        }
    });
}

// ============================================
// SUBMIT UPDATE
// ============================================
function submitForm() {
    const id = $('#editId').val();
    if (!id) return;

    const payload = {
        _token: CSRF,
        _method: 'PUT',
        category: $('#eCategory').val(),
        priority: $('#ePriority').val(),
        description: $('#eDescription').val().trim(),
        status: $('#eStatus').val(),
        admin_remark: $('#eAdminRemark').val().trim(),
    };

    if (payload.description.length < 10) {
        showToast('Description must be at least 10 characters', 'error');
        return;
    }

    $.ajax({
        url: ROUTES.base + '/' + id,
        type: 'POST',
        data: payload,
        beforeSend: function () {
            $('#saveBtn').prop('disabled', true).html('<i class="bi bi-arrow-repeat" style="animation:spin 1s linear infinite;"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function (res) {
            if (res.success) {
                complaintModal.hide();
                showToast(res.message || 'Complaint updated successfully', 'success');
                loadComplaints();
                loadStats();
            } else {
                showToast(res.message || 'Update failed', 'error');
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors || {};
                $.each(errors, function (field, msgs) {
                    $('#e' + field.charAt(0).toUpperCase() + field.slice(1)).closest('.rv-input-box').addClass('is-invalid');
                });
                showToast('Please fix validation errors', 'error');
            } else {
                showToast(xhr.responseJSON?.message || 'Update failed', 'error');
            }
        },
        complete: function () {
            $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">Save Changes</span>');
        }
    });
}

// ============================================
// QUICK STATUS CHANGE
// ============================================
function quickStatus(id, status) {
    $.ajax({
        url: ROUTES.base + '/' + id + '/status',
        type: 'POST',
        data: { _token: CSRF, _method: 'PATCH', status: status },
        success: function (res) {
            if (res.success) {
                showToast(res.message || 'Status updated', 'success');
                loadComplaints();
                loadStats();
            } else {
                showToast(res.message || 'Failed to update status', 'error');
            }
        },
        error: function (xhr) {
            showToast(xhr.responseJSON?.message || 'Failed to update status', 'error');
        }
    });
}

// ============================================
// DELETE
// ============================================
function deleteComplaint(id, number) {
    Swal.fire({
        title: 'Delete Complaint?',
        html: `Are you sure you want to delete <strong style="color:#dc2626;">${number}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: ROUTES.base + '/' + id,
                type: 'POST',
                data: { _token: CSRF, _method: 'DELETE' },
                success: function (res) {
                    if (res.success) {
                        showToast(res.message || 'Complaint deleted', 'success');
                        loadComplaints();
                        loadStats();
                    } else {
                        showToast(res.message || 'Failed to delete', 'error');
                    }
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to delete', 'error');
                }
            });
        }
    });
}

// ============================================
// REFRESH / CLEAR
// ============================================
function refreshData() {
    loadComplaints();
    loadStats();
    showToast('Data refreshed', 'success');
}

function clearFilters() {
    $('#filterHostel, #filterStatus, #filterPriority, #filterCategory').val('');
    $('#searchComplaint').val('');
    $('#resultCount').text('');
    currentPage = 1;
    loadComplaints();
    loadStats();
}

// ============================================
// TOAST
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
    }, 4500);
}
</script>
@endpush