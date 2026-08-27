<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $hostel->hostel_name }} - Residents</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1a3a6b;
            --primary-light: #2a5a9b;
            --gold: #c5a028;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }

        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hostel-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 30px 30px;
        }

        .hostel-header .hostel-icon { font-size: 3rem; color: var(--gold); }
        .hostel-header h1 { font-size: 2rem; font-weight: 700; }
        .hostel-header .hostel-code {
            background: rgba(255,255,255,0.15);
            padding: 0.3rem 1.5rem;
            border-radius: 20px;
            font-family: monospace;
            display: inline-block;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .stat-card .number { font-size: 1.3rem; font-weight: 700; color: var(--primary); }
        .stat-card .label { font-size: 0.6rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .icon { font-size: 1.2rem; display: block; margin-bottom: 0.25rem; }

        /* Search Bar */
        .search-section {
            background: white;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-section .search-box {
            flex: 1;
            position: relative;
            min-width: 200px;
        }

        .search-section .search-box input {
            width: 100%;
            padding: 0.5rem 0.75rem 0.5rem 2.2rem;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 0.9rem;
            transition: all 0.2s;
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
            left: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-section .filter-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-section .filter-group select {
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 0.8rem;
            background: #f8fafc;
        }

        .search-section .filter-group select:focus {
            border-color: var(--gold);
            outline: none;
        }

        .search-section .result-count {
            font-size: 0.75rem;
            color: #6b7280;
            padding: 0.2rem 0.75rem;
            background: #f3f4f6;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* Room Cards */
        .room-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s;
            margin-bottom: 1.5rem;
        }

        .room-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.08); }

        .room-header {
            background: linear-gradient(135deg, #f8fafc, #e5e7eb);
            padding: 0.75rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            border-bottom: 2px solid var(--gold);
        }

        .room-header .room-no { font-weight: 700; font-size: 1.1rem; color: var(--primary); }
        .room-header .room-info { font-size: 0.8rem; color: #6b7280; }
        .room-header .toggle-icon { transition: transform 0.3s; }
        .room-header .toggle-icon.collapsed { transform: rotate(-90deg); }

        .resident-list { padding: 0.5rem 1rem; }

        /* Resident Item - Clickable */
        .resident-item {
            display: flex;
            align-items: center;
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s;
            border-radius: 8px;
            cursor: pointer;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .resident-item:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }

        .resident-item:last-child { border-bottom: none; }

        .resident-item .click-area {
            display: flex;
            align-items: center;
            flex: 1;
            gap: 0.75rem;
            min-width: 0;
        }

        .resident-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--gold);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }

        .resident-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .resident-info { flex: 1; min-width: 0; }
        .resident-info .name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .resident-info .details {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .resident-info .details i { margin-right: 3px; }

        .resident-info .details .food-badge {
            font-size: 0.6rem;
            padding: 0.1rem 0.5rem;
            border-radius: 10px;
            font-weight: 600;
        }

        .resident-info .details .food-badge.with-food { background: #dcfce7; color: #166534; }
        .resident-info .details .food-badge.without-food { background: #f3f4f6; color: #4b5563; }

        /* Status Badge */
        .status-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-weight: 600;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-badge.paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.partial {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.pending {
            background: #fee2e2;
            color: #991b1b;
            animation: pulse 2s infinite;
        }

        .status-badge.not-paid {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-badge.paid .dot { background: var(--success); }
        .status-badge.partial .dot { background: var(--warning); }
        .status-badge.pending .dot { background: var(--danger); }
        .status-badge.not-paid .dot { background: #9ca3af; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .rent-badge {
            font-size: 0.7rem;
            font-weight: 700;
            color: #92400e;
            background: #fef3c7;
            padding: 0.15rem 0.6rem;
            border-radius: 12px;
            white-space: nowrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .empty-state i { font-size: 3rem; color: #d1d5db; margin-bottom: 0.5rem; }

        .no-results {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .no-results i { font-size: 2.5rem; color: #d1d5db; margin-bottom: 0.5rem; }

        /* Modal Styles */
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
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .payment-detail:last-child { border-bottom: none; }
        .payment-detail .label { color: #6b7280; font-size: 0.85rem; }
        .payment-detail .value { font-weight: 600; color: #1f2937; font-size: 0.95rem; }
        .payment-detail .value.due { color: var(--danger); }
        .payment-detail .value.clear { color: var(--success); }

        .payment-method-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin: 0.5rem 0;
        }

        .payment-method-option {
            padding: 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.8rem;
        }

        .payment-method-option:hover { border-color: var(--gold); background: #f8fafc; }
        .payment-method-option.selected { border-color: var(--gold); background: #fef3c7; }
        .payment-method-option i { font-size: 1.2rem; display: block; margin-bottom: 0.25rem; }

        .btn-submit-payment {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--gold), #b8941a);
            color: white;
            transition: all 0.3s;
        }

        .btn-submit-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(197, 160, 40, 0.3);
        }

        .btn-submit-payment:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .badge-status {
            font-size: 0.6rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
        }
        .badge-status.paid { background: #dcfce7; color: #166534; }
        .badge-status.pending { background: #fee2e2; color: #991b1b; }
        .badge-status.partial { background: #fef3c7; color: #92400e; }

        .payment-history-table {
            font-size: 0.8rem;
        }
        .payment-history-table th {
            background: #f8fafc;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }

        .toast-container {
            position: fixed;
            top: 20px;
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

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        @media (max-width: 768px) {
            .search-section { flex-direction: column; align-items: stretch; }
            .search-section .filter-group { justify-content: center; }
            .resident-item { flex-wrap: wrap; }
            .resident-item .click-area { flex-wrap: wrap; }
            .payment-method-group { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Header -->
    <div class="hostel-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="hostel-icon"><i class="bi bi-building"></i></div>
                    <h1>{{ $hostel->hostel_name }}</h1>
                    <span class="hostel-code">{{ $hostel->hostel_code ?? 'HOSTEL' }}</span>
                    <p class="mt-2" style="opacity:0.8;">
                        <i class="bi bi-geo-alt"></i> Room-wise resident list • Click on any resident to pay
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-shield-lock"></i> Secure
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <!-- Statistics -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <span class="icon">👨‍👩‍👧‍👦</span>
                <div class="number" id="totalCount">{{ $stats['total_residents'] }}</div>
                <div class="label">Total Residents</div>
            </div>
            <div class="stat-card">
                <span class="icon">✅</span>
                <div class="number" style="color:var(--success);" id="paidCount">0</div>
                <div class="label">Paid</div>
            </div>
            <div class="stat-card">
                <span class="icon">⚠️</span>
                <div class="number" style="color:var(--warning);" id="partialCount">0</div>
                <div class="label">Partial</div>
            </div>
            <div class="stat-card">
                <span class="icon">❌</span>
                <div class="number" style="color:var(--danger);" id="pendingCount">0</div>
                <div class="label">Pending</div>
            </div>
            <div class="stat-card">
                <span class="icon">💰</span>
                <div class="number" style="color:#92400e;">₹{{ number_format($stats['total_rent'], 0) }}</div>
                <div class="label">Monthly Rent</div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="search-section">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Search by name, room, or phone..." onkeyup="filterResidents()">
            </div>
            <div class="filter-group">
                <select id="statusFilter" onchange="filterResidents()">
                    <option value="">All Status</option>
                    <option value="PAID">✅ Paid</option>
                    <option value="PARTIAL">⚠️ Partial</option>
                    <option value="PENDING">❌ Pending</option>
                    <option value="NOT_PAID">🔴 Not Paid</option>
                </select>
                <select id="roomFilter" onchange="filterResidents()">
                    <option value="">All Rooms</option>
                    @foreach($occupiedRooms as $room)
                        <option value="{{ $room->id }}">Room #{{ $room->room_no }}</option>
                    @endforeach
                </select>
                <span class="result-count" id="resultCount">Showing all</span>
            </div>
        </div>

        <!-- Room-wise Residents -->
        <div id="residentsContainer">
            @if($occupiedRooms->count() > 0)
                @foreach($occupiedRooms as $room)
                    <div class="room-card" data-room-id="{{ $room->id }}">
                        <div class="room-header" onclick="toggleRoom(this)">
                            <div>
                                <span class="room-no"><i class="bi bi-door-open"></i> Room #{{ $room->room_no }}</span>
                                <span class="room-info ms-2">
                                    {{ $room->beds->count() }} resident(s) • 
                                    {{ $room->roomType->room_type_name ?? 'Standard' }}
                                </span>
                            </div>
                            <div>
                                <span class="badge bg-primary me-2" id="roomTotal-{{ $room->id }}">
                                    ₹{{ number_format($room->beds->first()?->resident?->rent_amount ?? 0, 0) }}
                                </span>
                                <i class="bi bi-chevron-down toggle-icon collapsed"></i>
                            </div>
                        </div>
                        <div class="resident-list" style="display: none;">
                            @foreach($room->beds as $bed)
                                @if($bed->resident)
                                    @php
                                        $resident = $bed->resident;
                                        // Get current month payment status
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
                                    @endphp
                                    <div class="resident-item" 
                                         data-resident-id="{{ $resident->id }}"
                                         data-room-id="{{ $room->id }}"
                                         data-name="{{ strtolower($resident->name) }}"
                                         data-status="{{ $status }}"
                                         onclick="openPaymentModal(event, {{ $resident->id }})">
                                        
                                        <div class="click-area">
                                            <!-- Avatar -->
                                            <div class="resident-avatar">
                                                @if($resident->profile_image)
                                                    <img src="{{ asset($resident->profile_image) }}" alt="{{ $resident->name }}">
                                                @else
                                                    {{ strtoupper(substr($resident->name, 0, 2)) }}
                                                @endif
                                            </div>

                                            <!-- Info -->
                                            <div class="resident-info">
                                                <div class="name">{{ $resident->name }}</div>
                                                <div class="details">
                                                    <i class="bi bi-bed"></i> Bed #{{ $bed->bed_no }}
                                                    <span class="food-badge {{ $resident->food_status == 'WITH_FOOD' ? 'with-food' : 'without-food' }} ms-1">
                                                        {{ $resident->food_status == 'WITH_FOOD' ? '🍽️' : '🍞' }}
                                                    </span>
                                                    <span class="ms-1"><i class="bi bi-phone"></i> {{ $resident->phone }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="rent-badge">₹{{ number_format($resident->rent_amount, 0) }}</span>
                                            <span class="status-badge {{ $statusClass }}">
                                                <span class="dot"></span>
                                                {{ $statusLabel }}
                                                @if($status == 'PARTIAL')
                                                    (₹{{ number_format($balance, 0) }})
                                                @endif
                                            </span>
                                            <i class="bi bi-chevron-right" style="color:#9ca3af;"></i>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
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
            <h5>No matching residents found</h5>
            <p class="text-muted">Try adjusting your search or filters</p>
            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Clear Filters
            </button>
        </div>

        <div class="text-center mt-4 mb-5">
            <small class="text-muted">
                <i class="bi bi-shield-check"></i> Click on any resident to record payment
            </small>
        </div>
    </div>

    <!-- Payment Modal -->
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

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

   <script>
    var csrfToken = '{{ csrf_token() }}';
    var hostelId = '{{ $hostel->id }}';

    // Define routes properly
    var routes = {
        details: '{{ route("guest.payment.details") }}',
        manual: '{{ route("guest.payment.manual") }}',
        history: '{{ url("/guest/payment/history") }}'  // FIXED: Use url() instead of route()
    };

    let paymentModal;
    let currentResident = null;
    let selectedMethod = 'cash';

    $(document).ready(function() {
        paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));

        // Auto expand first room
        $('.room-card:first .resident-list').slideDown(200);
        $('.room-card:first .toggle-icon').removeClass('collapsed');

        // Update stats
        updateStats();

        // Enter key for search
        $('#searchInput').on('keyup', function(e) {
            if (e.key === 'Enter') {
                filterResidents();
            }
        });
    });

    function toggleRoom(header) {
        const list = header.nextElementSibling;
        const icon = header.querySelector('.toggle-icon');

        if (list.style.display === 'none') {
            $(list).slideDown(200);
            icon.classList.remove('collapsed');
        } else {
            $(list).slideUp(200);
            icon.classList.add('collapsed');
        }
    }

    function filterResidents() {
        const search = $('#searchInput').val().toLowerCase().trim();
        const statusFilter = $('#statusFilter').val();
        const roomFilter = $('#roomFilter').val();

        let visibleCount = 0;
        let totalCount = 0;
        let paidCount = 0;
        let partialCount = 0;
        let pendingCount = 0;

        $('.resident-item').each(function() {
            const $item = $(this);
            const name = $item.data('name') || '';
            const status = $item.data('status') || '';
            const roomId = String($item.data('room-id') || '');

            let show = true;

            // Search filter
            if (search) {
                const text = $item.text().toLowerCase();
                if (!text.includes(search) && !name.includes(search)) {
                    show = false;
                }
            }

            // Status filter
            if (show && statusFilter) {
                if (statusFilter === 'NOT_PAID' && status !== 'NOT_PAID') show = false;
                if (statusFilter !== 'NOT_PAID' && status !== statusFilter) show = false;
            }

            // Room filter
            if (show && roomFilter) {
                if (roomId !== roomFilter) show = false;
            }

            totalCount++;

            if (show) {
                $item.show();
                visibleCount++;
                // Count statuses for stats
                if (status === 'PAID') paidCount++;
                else if (status === 'PARTIAL') partialCount++;
                else if (status === 'PENDING' || status === 'NOT_PAID') pendingCount++;
            } else {
                $item.hide();
            }
        });

        // Update result count
        $('#resultCount').text(visibleCount === totalCount ? 'Showing all' : `Showing ${visibleCount} of ${totalCount}`);

        // Show/hide no results
        if (visibleCount === 0 && totalCount > 0) {
            $('#noResults').show();
        } else {
            $('#noResults').hide();
        }

        // Update stats
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
        $('.resident-item').each(function() {
            const status = $(this).data('status') || '';
            if (status === 'PAID') paid++;
            else if (status === 'PARTIAL') partial++;
            else if (status === 'PENDING' || status === 'NOT_PAID') pending++;
        });
        $('#paidCount').text(paid);
        $('#partialCount').text(partial);
        $('#pendingCount').text(pending);
    }

    function openPaymentModal(event, residentId) {
        event.preventDefault();
        event.stopPropagation();

        currentResident = { id: residentId };

        // Show loading
        document.getElementById('paymentModalBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading payment details...</p>
            </div>
        `;

        paymentModal.show();

        // Fetch resident details
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

        let html = `
            <!-- Resident Info -->
            <div class="mb-3">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="resident-avatar" style="width:56px; height:56px; font-size:1.2rem; border-color:var(--gold);">
                        ${data.profile_image ? `<img src="${data.profile_image}" alt="${data.name}">` : data.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h5 class="mb-0">${data.name}</h5>
                        <div class="text-muted" style="font-size:0.8rem;">
                            <i class="bi bi-door-open"></i> Room #${data.room_no} • 
                            <i class="bi bi-bed"></i> Bed #${data.bed_no}
                        </div>
                    </div>
                </div>

                <div class="payment-detail">
                    <span class="label"><i class="bi bi-phone"></i> Phone</span>
                    <span class="value">${data.phone}</span>
                </div>
                <div class="payment-detail">
                    <span class="label"><i class="bi bi-envelope"></i> Email</span>
                    <span class="value">${data.email || 'Not provided'}</span>
                </div>
                <div class="payment-detail" style="border-top:2px solid #e5e7eb; padding-top:0.5rem; margin-top:0.5rem;">
                    <span class="label"><i class="bi bi-currency-rupee"></i> Monthly Rent</span>
                    <span class="value">₹${rent.toFixed(2)}</span>
                </div>
                ${data.discount > 0 ? `
                <div class="payment-detail" style="color:#065f46;">
                    <span class="label"><i class="bi bi-tag"></i> Discount</span>
                    <span class="value" style="color:#065f46;">- ₹${data.discount.toFixed(2)}</span>
                </div>` : ''}
                ${data.fine_amount > 0 ? `
                <div class="payment-detail" style="color:#991b1b;">
                    <span class="label"><i class="bi bi-clock"></i> Late Fee</span>
                    <span class="value" style="color:#991b1b;">+ ₹${data.fine_amount.toFixed(2)}</span>
                </div>` : ''}
                <div class="payment-detail" style="border-top:2px solid var(--gold); padding-top:0.5rem; margin-top:0.5rem;">
                    <span class="label"><strong>Total to Pay</strong></span>
                    <span class="value ${amount > 0 ? 'due' : 'clear'}" style="font-size:1.2rem;">
                        ${amount > 0 ? '₹' + amount.toFixed(2) : '✅ All Paid'}
                    </span>
                </div>
                ${data.discount_message ? `<div class="alert alert-info mt-2" style="font-size:0.75rem; padding:0.4rem 0.75rem;"><i class="bi bi-info-circle"></i> ${data.discount_message}</div>` : ''}
                ${data.fine_message ? `<div class="alert alert-danger mt-2" style="font-size:0.75rem; padding:0.4rem 0.75rem;"><i class="bi bi-exclamation-circle"></i> ${data.fine_message}</div>` : ''}
                ${data.has_pending ? `<div class="alert alert-warning mt-2" style="font-size:0.8rem; padding:0.5rem 0.75rem;"><i class="bi bi-exclamation-triangle-fill"></i> ${data.pending_count} pending payment(s) from previous months</div>` : ''}
            </div>
        `;

        // Only show payment form if not fully paid
        if (!isPaid && amount > 0) {
            html += `
            <hr>
            <h6 class="mb-2"><i class="bi bi-pencil-square"></i> Enter Payment Details</h6>

            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Amount (₹)</label>
                    <input type="number" id="paymentAmount" class="form-control" 
                           value="${amount.toFixed(2)}" 
                           step="0.01" min="0.01" max="${amount}"
                           style="border-radius:8px; border:1px solid #d1d5db; padding:0.5rem 0.75rem;">
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Payment Method</label>
                    <div class="payment-method-group">
                        <div class="payment-method-option selected" data-method="cash" onclick="selectMethod(this)">
                            <i class="bi bi-cash"></i> Cash
                        </div>
                        <div class="payment-method-option" data-method="upi" onclick="selectMethod(this)">
                            <i class="bi bi-phone"></i> UPI
                        </div>
                        <div class="payment-method-option" data-method="card" onclick="selectMethod(this)">
                            <i class="bi bi-credit-card"></i> Card
                        </div>
                        <div class="payment-method-option" data-method="bank_transfer" onclick="selectMethod(this)">
                            <i class="bi bi-bank"></i> Bank
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2 mt-1">
                <div class="col-md-6">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Receipt/Reference No</label>
                    <input type="text" id="paymentReference" class="form-control" 
                           value="${data.reference || 'PAY-' + Date.now()}" 
                           style="border-radius:8px; border:1px solid #d1d5db; padding:0.5rem 0.75rem; font-size:0.85rem;">
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Remarks (Optional)</label>
                    <input type="text" id="paymentRemarks" class="form-control" 
                           placeholder="Any notes..." 
                           style="border-radius:8px; border:1px solid #d1d5db; padding:0.5rem 0.75rem; font-size:0.85rem;">
                </div>
            </div>

            <button class="btn-submit-payment mt-3" id="submitPaymentBtn" onclick="submitPayment()">
                <i class="bi bi-check-circle"></i> Record Payment
            </button>
            `;
        } else {
            html += `
            <div class="text-center py-2">
                <div style="font-size:2rem; margin-bottom:0.5rem;">✅</div>
                <h5>This month's rent is fully paid!</h5>
                <p class="text-muted">No payment due for this month.</p>
                <button class="btn btn-secondary btn-sm mt-2" data-bs-dismiss="modal">Close</button>
            </div>
            `;
        }

        // Payment History - FIXED: Use routes.history with resident ID
        if (data.payment_history && data.payment_history.length > 0) {
            html += `
            <hr>
            <h6 class="mb-2"><i class="bi bi-clock-history"></i> Payment History</h6>
            <div class="table-responsive">
                <table class="table table-sm payment-history-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Rent</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
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

    function selectMethod(el) {
        document.querySelectorAll('.payment-method-option').forEach(opt => opt.classList.remove('selected'));
        el.classList.add('selected');
        selectedMethod = el.dataset.method;
    }

    function submitPayment() {
        const amount = parseFloat(document.getElementById('paymentAmount').value);
        const reference = document.getElementById('paymentReference').value.trim();
        const remarks = document.getElementById('paymentRemarks').value.trim();

        if (!amount || amount <= 0) {
            showToast('Please enter a valid amount', 'error');
            return;
        }

        if (!reference) {
            showToast('Please enter a reference number', 'error');
            return;
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
                remarks: remarks,
                hostel_id: hostelId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    const data = response.data;
                    const statusIcon = data.is_full_paid ? '✅' : (data.is_partial ? '⚠️' : '❌');

                    document.getElementById('paymentModalBody').innerHTML = `
                        <div class="text-center py-4">
                            <div style="font-size:3rem; margin-bottom:0.5rem;">${statusIcon}</div>
                            <h5>${data.status_message}</h5>
                            <div class="payment-detail" style="justify-content:center; gap:2rem; flex-wrap:wrap; border:none;">
                                <div><span class="label">Receipt</span><br><span class="value">${data.receipt_no}</span></div>
                                <div><span class="label">Amount Paid</span><br><span class="value" style="color:var(--success);">₹${data.amount_paid.toFixed(2)}</span></div>
                                <div><span class="label">Balance</span><br><span class="value ${data.balance > 0 ? 'due' : 'clear'}">₹${data.balance.toFixed(2)}</span></div>
                            </div>
                            <div class="mt-3">
                                <span class="badge-status ${data.status.toLowerCase()}">${data.status}</span>
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
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join(', ');
                }
                showToast('❌ ' + message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Record Payment';
            }
        });
    }

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
        if (typeof type === 'undefined') type = 'success';
        var container = document.getElementById('toastContainer');
        var icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
        var color = type === 'success' ? '#10b981' : '#dc2626';

        var toast = document.createElement('div');
        toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '');
        toast.innerHTML = `
            <i class="bi ${icon}" style="color: ${color}; font-size: 1.25rem;"></i>
            <div class="message">${message}</div>
            <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
        `;
        container.appendChild(toast);

        setTimeout(function() {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(function() { toast.remove(); }, 300);
            }
        }, 8000);
    }
</script>

</body>
</html>