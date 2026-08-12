<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay Your Rent - {{ $hostel->hostel_name ?? 'Hostel' }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1a3a5c;
            --primary-light: #2a5a8c;
            --gold-color: #c5a028;
            --bg-gradient-start: #f0f4f8;
            --bg-gradient-end: #e2e8f0;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .payment-container {
            max-width: 560px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .payment-header {
            background: var(--primary-color);
            padding: 1.75rem 2rem;
            text-align: center;
            color: white;
            position: relative;
        }

        .payment-header .hostel-icon {
            font-size: 2.5rem;
            color: var(--gold-color);
            margin-bottom: 0.5rem;
        }

        .payment-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .payment-header .hostel-code {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.2rem 1rem;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            font-family: monospace;
        }

        .payment-header .secure-badge {
            position: absolute;
            top: 12px;
            right: 16px;
            font-size: 0.65rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
        }

        .payment-body {
            padding: 2rem;
        }

        /* Mobile Input Section */
        .mobile-section {
            text-align: center;
            padding: 0.5rem 0 1.5rem 0;
        }

        .mobile-section .subtitle {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .mobile-input-group {
            display: flex;
            gap: 0.5rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .mobile-input-group .form-control {
            flex: 1;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 0.7rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .mobile-input-group .form-control:focus {
            border-color: var(--gold-color);
            box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
        }

        .mobile-input-group .btn-find {
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            border: none;
            background: var(--gold-color);
            color: white;
            font-weight: 600;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .mobile-input-group .btn-find:hover {
            background: #b08c1e;
            transform: translateY(-2px);
        }

        .mobile-input-group .btn-find:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Resident Info Card */
        .resident-info {
            display: none;
            background: #f8fafc;
            border-radius: 16px;
            padding: 1.25rem;
            margin: 1rem 0;
            border: 2px solid #e5e7eb;
            animation: fadeIn 0.4s ease;
        }

        .resident-info.show {
            display: block;
        }

        .resident-info .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .resident-info .info-row:last-child {
            border-bottom: none;
        }

        .resident-info .label {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .resident-info .value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        .resident-info .due-amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: #dc2626;
        }

        .resident-info .due-amount.clear {
            color: #22c55e;
        }

        /* Discount Section */
        .discount-section {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0 0.75rem 0;
            display: none;
        }

        .discount-section.show {
            display: block;
        }

        .discount-section .discount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }

        .discount-section .discount-row .label {
            color: #065f46;
        }

        .discount-section .discount-row .amount {
            color: #047857;
            font-weight: 700;
        }

        .discount-section .discount-badge {
            background: #047857;
            color: white;
            padding: 0.1rem 0.6rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .final-amount-row {
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-top: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .final-amount-row .label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .final-amount-row .amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gold-color);
        }

        #pendingInfo {
            display: none;
            background: #fef3c7;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            color: #92400e;
            margin-top: 0.5rem;
        }

        .btn-pay-again {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            background: var(--primary-color);
            color: white;
            transition: all 0.3s;
            margin-top: 0.5rem;
        }

        .btn-pay-again:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .btn-pay-again:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Toast */
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            border-left: 4px solid #10b981;
            margin-bottom: 0.75rem;
            animation: slideInRight 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .toast-custom.error {
            border-left-color: #dc2626;
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
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        @media (max-width: 480px) {
            .payment-body { padding: 1.25rem; }
            .mobile-input-group { flex-direction: column; }
            .mobile-input-group .btn-find { width: 100%; }
            .resident-info .info-row { flex-direction: column; gap: 0.2rem; }
            .resident-info .info-row .value { text-align: left; }
            .discount-section .discount-row { flex-direction: column; gap: 0.25rem; align-items: flex-start; }
            .final-amount-row { flex-direction: column; gap: 0.25rem; text-align: center; }
        }
    </style>
</head>

<body>

    <div class="payment-container" id="paymentApp">
        <!-- Header -->
        <div class="payment-header">
            <span class="secure-badge"><i class="bi bi-shield-lock"></i> Secure</span>
            <div class="hostel-icon"><i class="bi bi-building"></i></div>
            <h1>{{ isset($hostel->hostel_name) ? $hostel->hostel_name : 'Hostel Rent Payment' }}</h1>
            @if (isset($hostel) && !empty($hostel->hostel_code))
                <span class="hostel-code">{{ $hostel->hostel_code }}</span>
            @endif
        </div>

        <!-- Body -->
        <div class="payment-body">

            <!-- Step 1: Mobile Number Input -->
            <div class="mobile-section" id="mobileSection">
                <div class="subtitle">
                    <i class="bi bi-phone"></i> Enter your registered mobile number to view your rent
                </div>
                <div class="mobile-input-group">
                    <input type="tel" class="form-control" id="mobileInput" placeholder="Enter mobile number"
                        maxlength="15">
                    <button class="btn-find" id="findBtn" onclick="findResident()">
                        <i class="bi bi-search"></i> Find
                    </button>
                </div>
                <div id="mobileHelp" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                    <i class="bi bi-info-circle"></i> Enter the mobile number registered with the hostel
                </div>
            </div>

            <!-- Step 2: Resident Info -->
            <div class="resident-info" id="residentInfo">
                <div class="info-row">
                    <span class="label"><i class="bi bi-person"></i> Resident</span>
                    <span class="value" id="residentName">-</span>
                </div>
                <div class="info-row">
                    <span class="label"><i class="bi bi-door-open"></i> Room</span>
                    <span class="value" id="residentRoom">-</span>
                </div>
                <div class="info-row">
                    <span class="label"><i class="bi bi-phone"></i> Mobile</span>
                    <span class="value" id="residentPhone">-</span>
                </div>
                <div class="info-row">
                    <span class="label"><i class="bi bi-envelope"></i> Email</span>
                    <span class="value" id="residentEmail">-</span>
                </div>
                <div class="info-row" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                    <span class="label"><i class="bi bi-currency-rupee"></i> Total Due</span>
                    <span class="value due-amount" id="totalDue">₹0.00</span>
                </div>

                <!-- Discount Section -->
                <div class="discount-section" id="discountSection">
                    <div class="discount-row">
                        <span class="label">
                            <i class="bi bi-tag"></i> <span id="discountLabel">Early Payment Discount</span>
                        </span>
                        <span class="amount">-₹<span id="discountAmount">0</span></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                        <span style="font-size: 0.7rem; color: #065f46;" id="discountNote">Applied for payment between 1st-5th</span>
                        <span class="discount-badge" id="discountBadge">SAVE</span>
                    </div>
                </div>

                <!-- Final Amount -->
                <div class="final-amount-row">
                    <span class="label"><i class="bi bi-credit-card"></i> Final Amount to Pay</span>
                    <span class="amount">₹<span id="finalAmount">0.00</span></span>
                </div>

                <div id="pendingInfo">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span id="pendingCount">0</span> pending payments from previous months
                </div>
                <button class="btn-pay-again" id="payNowBtn" onclick="generatePayment()"
                    style="background: var(--gold-color); margin-top: 0.75rem;">
                    <i class="bi bi-shield-check"></i> Proceed to Pay ₹<span id="payButtonAmount">0</span>
                </button>
                <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.5rem; text-align:center;">
                    You'll be redirected to PhonePe's secure checkout. Once you finish paying there,
                    you'll be brought straight back here and we'll confirm it automatically.
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var hostelId = '{{ isset($hostelId) ? $hostelId : '' }}';
        var csrfToken = '{{ csrf_token() }}';
        var paymentRoutes = {
            resident: '{{ route('guest.payment.resident') }}',
            qr: '{{ route('guest.payment.qr') }}'
        };

        let currentResident = null;
        let currentReference = null;

        $(document).ready(function() {
            $('#mobileInput').on('keypress', function(e) {
                if (e.key === 'Enter') {
                    findResident();
                }
            });
        });

        function findResident() {
            const mobile = $('#mobileInput').val().trim();

            if (!mobile || mobile.length < 10) {
                showToast('Please enter a valid 10-digit mobile number', 'error');
                return;
            }

            const btn = $('#findBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

            $.ajax({
                url: paymentRoutes.resident,
                type: 'POST',
                data: {
                    mobile: mobile,
                    hostel_id: hostelId,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        currentResident = response.data;
                        currentReference = response.data.reference;

                        $('#residentName').text(response.data.name);
                        $('#residentRoom').text('Room #' + response.data.room_no);
                        $('#residentPhone').text(response.data.phone);
                        $('#residentEmail').text(response.data.email || 'Not provided');
                        $('#totalDue').text('₹' + parseFloat(response.data.total_due).toFixed(2));

                        // Handle total due styling
                        if (response.data.total_due > 0) {
                            $('#totalDue').removeClass('clear').addClass('due-amount');
                        } else {
                            $('#totalDue').removeClass('due-amount').addClass('clear');
                        }

                        // Handle discount display
                        const discount = parseFloat(response.data.discount || 0);
                        const finalAmount = parseFloat(response.data.final_amount || response.data.total_due);

                        if (discount > 0) {
                            $('#discountSection').addClass('show');
                            $('#discountAmount').text(discount.toFixed(2));
                            $('#finalAmount').text(finalAmount.toFixed(2));
                            $('#payButtonAmount').text(finalAmount.toFixed(2));

                            // Set discount label based on type
                            if (response.data.discount_type === 'early_discount_250') {
                                $('#discountLabel').text('Early Payment Discount (1st-5th)');
                                $('#discountNote').text('Applied for payment between 1st-5th of the month');
                            } else if (response.data.discount_type === 'early_discount_125') {
                                $('#discountLabel').text('Early Payment Discount (6th-10th)');
                                $('#discountNote').text('Applied for payment between 6th-10th of the month');
                            }
                        } else {
                            $('#discountSection').removeClass('show');
                            $('#finalAmount').text(finalAmount.toFixed(2));
                            $('#payButtonAmount').text(finalAmount.toFixed(2));
                        }

                        // Show pending info
                        if (response.data.has_pending) {
                            $('#pendingInfo').show();
                            $('#pendingCount').text(response.data.pending_count);
                        } else {
                            $('#pendingInfo').hide();
                        }

                        $('#residentInfo').addClass('show');
                        showToast('Resident found! Click "Proceed to Pay" to continue.', 'success');
                    }
                },
                error: function(xhr) {
                    var message = 'Resident not found. Please check your mobile number.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('❌ ' + message, 'error');
                    $('#residentInfo').removeClass('show');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-search"></i> Find');
                }
            });
        }

        function generatePayment() {
            if (!currentResident) {
                showToast('Please find your resident details first', 'error');
                return;
            }

            // Use final_amount instead of total_due
            var amount = currentResident.final_amount || currentResident.total_due;
            if (amount <= 0) {
                showToast('No payment due at this time', 'error');
                return;
            }

            var btn = $('#payNowBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Redirecting to PhonePe...');

            $.ajax({
                url: paymentRoutes.qr,
                type: 'GET',
                data: {
                    amount: amount,
                    reference: currentReference,
                    resident_id: currentResident.resident_id
                },
                success: function(response) {
                    console.log(response);
                    if (response.success && response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        showToast('Could not start payment. Please try again.', 'error');
                        btn.prop('disabled', false).html(
                            '<i class="bi bi-shield-check"></i> Proceed to Pay ₹' +
                            (currentResident.final_amount || currentResident.total_due).toFixed(2)
                        );
                    }
                },
                error: function(xhr) {
                    var message = 'Failed to start payment';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('❌ ' + message, 'error');
                    btn.prop('disabled', false).html(
                        '<i class="bi bi-shield-check"></i> Proceed to Pay ₹' +
                        (currentResident.final_amount || currentResident.total_due).toFixed(2)
                    );
                }
            });
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
