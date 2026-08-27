<!-- resources/views/guest/payment.blade.php -->

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
            --upi-blue: #1a73e8;
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
            background: linear-gradient(135deg, #1a3a5c 0%, #2a5a8c 100%);
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

        .payment-header .upi-badge {
            position: absolute;
            bottom: 8px;
            right: 16px;
            font-size: 0.55rem;
            opacity: 0.6;
            letter-spacing: 1px;
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

        #pendingInfo {
            display: none;
            background: #fef3c7;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            color: #92400e;
            margin-top: 0.5rem;
        }

        /* Discount & Fine Badges */
        .badge-discount {
            background: #d1fae5;
            color: #065f46;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            display: inline-block;
        }

        .badge-fine {
            background: #fee2e2;
            color: #991b1b;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            display: inline-block;
        }

        /* UPI Payment Button */
        .btn-pay-upi {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            transition: all 0.3s;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-pay-upi:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 115, 232, 0.3);
        }

        .btn-pay-upi:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-pay-upi .upi-icon {
            font-size: 1.1rem;
        }

        /* UPI App Buttons */
        .upi-apps {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .upi-app-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 0.4rem 1rem;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            background: white;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #1f2937;
            text-decoration: none;
        }

        .upi-app-btn:hover {
            border-color: var(--upi-blue);
            background: #f0f7ff;
            transform: translateY(-2px);
        }

        .upi-app-btn.google-pay { border-color: #1a73e8; }
        .upi-app-btn.phonepe { border-color: #5f259f; }
        .upi-app-btn.paytm { border-color: #00baf2; }

        /* UPI ID Display */
        .upi-id-display {
            background: #f3f4f6;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--primary-color);
            text-align: center;
            margin: 0.5rem 0;
        }

        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .payment-methods img {
            height: 24px;
            opacity: 0.6;
            filter: grayscale(100%);
        }

        .payment-methods img.active {
            opacity: 1;
            filter: grayscale(0%);
        }

        /* Success screen */
        #paymentSuccess {
            text-align: center;
        }

        #paymentSuccess .info-row {
            text-align: left;
        }

        /* Confirm-payment modal */
        #confirmModalBackdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            align-items: center;
            justify-content: center;
        }

        #confirmModalBackdrop .modal-box {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            max-width: 340px;
            width: 90%;
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

        .toast-custom.warning {
            border-left-color: #f59e0b;
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
            .payment-methods { gap: 0.5rem; }
            .upi-apps { gap: 0.5rem; }
        }
    </style>
</head>

<body>

    <div class="payment-container" id="paymentApp">
        <!-- Header -->
        <div class="payment-header">
            <span class="secure-badge"><i class="bi bi-shield-lock"></i> Secure</span>
            <span class="upi-badge">Powered by UPI</span>
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
                @if (isset($reference))
                    <input type="hidden" id="paymentReference" value="{{ $reference }}">
                @endif
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

                <!-- Discount & Fine Display -->
                <div id="discountDisplay" style="display: none; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                    <span class="badge-discount" id="discountBadge"><i class="bi bi-tag"></i> <span id="discountText">0% off</span></span>
                    <span class="badge-fine" id="fineBadge" style="display: none;"><i class="bi bi-clock"></i> <span id="fineText">Late fee</span></span>
                </div>

                <div class="info-row" style="border-bottom: 2px solid var(--gold-color); padding-bottom: 0.75rem; margin-bottom: 0.5rem;">
                    <span class="label"><i class="bi bi-currency-rupee"></i> Amount to Pay</span>
                    <span class="value due-amount" id="totalDue">₹0.00</span>
                </div>

                <!-- UPI ID Display -->
                <div id="upiIdDisplay" class="upi-id-display" style="display: none;">
                    <i class="bi bi-phone"></i> Pay to: <span id="upiIdText">-</span>
                </div>

                <div id="pendingInfo">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span id="pendingCount">0</span> pending payments from previous months
                </div>

                <!-- UPI Payment Button -->
                <button class="btn-pay-upi" id="payNowBtn" onclick="initiateUPIPayment()" style="display: none;">
                    <i class="bi bi-phone"></i> Pay with UPI
                </button>

                <!-- UPI App Quick Links -->
                <div class="upi-apps" id="upiApps" style="display: none;">
                    <a href="#" class="upi-app-btn google-pay" onclick="openUPIApp('googlepay'); return false;">
                        <span>📱</span> Google Pay
                    </a>
                    <a href="#" class="upi-app-btn phonepe" onclick="openUPIApp('phonepe'); return false;">
                        <span>📱</span> PhonePe
                    </a>
                    <a href="#" class="upi-app-btn paytm" onclick="openUPIApp('paytm'); return false;">
                        <span>📱</span> Paytm
                    </a>
                    <a href="#" class="upi-app-btn" onclick="openUPIApp('other'); return false;">
                        <span>📱</span> Other UPI
                    </a>
                </div>

                <div class="payment-methods">
                    <span style="font-size: 0.7rem; color: #9ca3af; width: 100%; text-align: center; margin-bottom: 0.25rem;">
                        Secure payments via UPI
                    </span>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Google_Pay_logo.svg/1200px-Google_Pay_logo.svg.png" alt="Google Pay" style="height: 20px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/PhonePe_Logo.png/1200px-PhonePe_Logo.png" alt="PhonePe" style="height: 20px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Paytm_Logo_%282017%29.svg/1200px-Paytm_Logo_%282017%29.svg.png" alt="Paytm" style="height: 20px;">
                </div>

                <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.5rem; text-align:center;">
                    <i class="bi bi-shield-check"></i>
                    Pay directly via any UPI app, then confirm here so we can record it.
                </div>
            </div>

            <!-- Step 3: Payment Success -->
            <div class="resident-info" id="paymentSuccess">
                <i class="bi bi-check-circle-fill" style="font-size:3rem;color:#22c55e;"></i>
                <h4 style="margin-top:0.75rem;color:var(--primary-color);">Payment Recorded</h4>
                <div class="info-row">
                    <span class="label">Amount Paid</span>
                    <span class="value" id="successAmount">-</span>
                </div>
                <div class="info-row">
                    <span class="label">Receipt No.</span>
                    <span class="value" id="successReceipt">-</span>
                </div>
                <div class="info-row">
                    <span class="label">Transaction ID</span>
                    <span class="value" id="successTxnId">-</span>
                </div>
                <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.75rem;">
                    <i class="bi bi-hourglass-split"></i>
                    Your payment is pending verification by the hostel admin against the bank statement.
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm-payment modal shown when the user returns from their UPI app -->
    <div id="confirmModalBackdrop">
        <div class="modal-box">
            <h5 style="color:var(--primary-color);">Did you complete the payment?</h5>
            <p style="font-size:0.85rem;color:#6b7280;">Enter the UPI transaction/reference ID from your payment app (optional but recommended).</p>
            <input type="text" id="utrInput" class="form-control" placeholder="e.g. 302481234567" style="margin-bottom:1rem;">
            <div style="display:flex;gap:0.5rem;">
                <button class="btn-find" style="flex:1;background:#22c55e;" onclick="confirmPayment(true)">Yes, Paid</button>
                <button class="btn-find" style="flex:1;background:#9ca3af;" onclick="confirmPayment(false)">Not Yet</button>
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
          
        };

        let currentResident = null;
        let currentReference = null;
        let upiData = null;
        let statusCheckInterval = null;
        let awaitingReturn = false;

        $(document).ready(function() {
            $('#mobileInput').on('keypress', function(e) {
                if (e.key === 'Enter') {
                    findResident();
                }
            });

            // Check for payment callback results (older deep-link based flow)
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const reference = urlParams.get('reference');

            if (status === 'success' && reference) {
                showToast('✅ Payment reference received: ' + reference, 'success');
                checkPaymentStatus(reference);
            } else if (status === 'cancelled' && reference) {
                showToast('Payment was cancelled. You can try again.', 'error');
            }
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

                        // Show amount
                        const amountToPay = parseFloat(response.data.amount_to_pay || response.data.total_due);
                        $('#totalDue').text('₹' + amountToPay.toFixed(2));

                        // Show discount and fine
                        const discount = parseFloat(response.data.discount_amount || 0);
                        const fine = parseFloat(response.data.fine_amount || 0);

                        if (discount > 0 || fine > 0) {
                            $('#discountDisplay').show();
                            if (discount > 0) {
                                $('#discountText').text('₹' + discount.toFixed(2) + ' off');
                                $('#discountBadge').show();
                            } else {
                                $('#discountBadge').hide();
                            }
                            if (fine > 0) {
                                $('#fineText').text('₹' + fine.toFixed(2) + ' late fee');
                                $('#fineBadge').show();
                            } else {
                                $('#fineBadge').hide();
                            }
                        } else {
                            $('#discountDisplay').hide();
                        }

                        if (amountToPay > 0) {
                            $('#totalDue').removeClass('clear').addClass('due-amount');
                        } else {
                            $('#totalDue').removeClass('due-amount').addClass('clear');
                        }

                        if (response.data.has_pending) {
                            $('#pendingInfo').show();
                            $('#pendingCount').text(response.data.pending_count);
                        } else {
                            $('#pendingInfo').hide();
                        }

                        // Show UPI ID
                        if (response.data.has_upi && response.data.upi_id) {
                            $('#upiIdText').text(response.data.upi_id);
                            $('#upiIdDisplay').show();
                            $('#payNowBtn').show();
                            $('#upiApps').show();
                            showToast('Resident found! Click "Pay with UPI" to continue.', 'success');
                        } else {
                            $('#upiIdDisplay').hide();
                            $('#payNowBtn').hide();
                            $('#upiApps').hide();
                            showToast('⚠️ UPI payment not configured for this hostel. Please contact admin.', 'error');
                        }

                        $('#paymentSuccess').removeClass('show').hide();
                        $('#residentInfo').addClass('show').show();
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

        function initiateUPIPayment() {
            if (!currentResident) {
                showToast('Please find your resident details first', 'error');
                return;
            }

            const amount = parseFloat(currentResident.amount_to_pay || currentResident.total_due);
            if (amount <= 0) {
                showToast('No payment due at this time', 'error');
                return;
            }

            const btn = $('#payNowBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Preparing UPI...');

            $.ajax({
                url: paymentRoutes.generateUPI,
                type: 'POST',
                data: {
                    amount: amount,
                    reference: currentReference,
                    resident_id: currentResident.resident_id,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        upiData = response;
                        launchUPI(response.upi_uri, response.reference);
                    } else {
                        showToast('Failed to generate UPI link: ' + (response.message || 'Unknown error'), 'error');
                    }
                    btn.prop('disabled', false).html('<i class="bi bi-phone"></i> Pay with UPI');
                },
                error: function(xhr) {
                    var message = 'Failed to generate UPI payment link';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('❌ ' + message, 'error');
                    btn.prop('disabled', false).html('<i class="bi bi-phone"></i> Pay with UPI');
                }
            });
        }

        function openUPIApp(app) {
            if (!upiData) {
                showToast('Please generate UPI payment first', 'error');
                return;
            }
            launchUPI(upiData.upi_uri, upiData.reference);
        }

        function launchUPI(uri, reference) {
            awaitingReturn = true;
            currentReference = reference;
            showToast('✅ Opening your UPI app...', 'success');
            window.location.href = uri;

            // Background polling (only resolves if an admin/webhook confirms server-side)
            startStatusCheck(reference);
        }

        // Detect when the user comes back to this tab after the UPI app closes
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible' && awaitingReturn) {
                awaitingReturn = false;
                setTimeout(function () {
                    document.getElementById('confirmModalBackdrop').style.display = 'flex';
                }, 400);
            }
        });

        function confirmPayment(didPay) {
            document.getElementById('confirmModalBackdrop').style.display = 'none';

            if (!didPay) {
                showToast('No problem — click "Pay with UPI" again whenever you\'re ready.', 'warning');
                return;
            }

            const utr = $('#utrInput').val().trim();

            $.ajax({
                url: paymentRoutes.verifyUPI,
                type: 'POST',
                data: {
                    reference: currentReference,
                    transaction_id: utr || null,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        if (statusCheckInterval) clearInterval(statusCheckInterval);
                        showSuccessScreen(response.data);
                    } else {
                        showToast(response.message || 'Could not confirm payment yet.', 'warning');
                    }
                },
                error: function(xhr) {
                    var message = 'Could not confirm payment. It may still be processing.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('⚠️ ' + message, 'warning');
                }
            });
        }

        function showSuccessScreen(payment) {
            $('#mobileSection').hide();
            $('#residentInfo').removeClass('show').hide();

            const amount = payment.upi_paid_amount ?? payment.amount ?? 0;
            $('#successAmount').text('₹' + parseFloat(amount).toFixed(2));
            $('#successReceipt').text(payment.receipt_no ?? currentReference);
            $('#successTxnId').text(payment.transaction_id ?? '-');

            $('#paymentSuccess').addClass('show').show();
            showToast('✅ Payment recorded successfully!', 'success');
        }

        function startStatusCheck(reference) {
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }

            let attempts = 0;
            const maxAttempts = 30; // 5 minutes (10s * 30)

            statusCheckInterval = setInterval(function() {
                attempts++;

                $.ajax({
                    url: paymentRoutes.checkUPIStatus + '?reference=' + reference,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'COMPLETED') {
                            clearInterval(statusCheckInterval);
                            document.getElementById('confirmModalBackdrop').style.display = 'none';
                            showSuccessScreen(response.data);
                        } else if (response.status === 'EXPIRED') {
                            clearInterval(statusCheckInterval);
                            showToast('⏰ Payment session expired. Please try again.', 'error');
                        }
                    },
                    error: function() {
                        // Silent fail
                    }
                });

                if (attempts >= maxAttempts) {
                    clearInterval(statusCheckInterval);
                }
            }, 10000); // Check every 10 seconds
        }

        function checkPaymentStatus(reference) {
            $.ajax({
                url: paymentRoutes.checkUPIStatus + '?reference=' + reference,
                type: 'GET',
                success: function(response) {
                    if (response.status === 'COMPLETED') {
                        showSuccessScreen(response.data);
                    }
                },
                error: function() {
                    // Silent fail
                }
            });
        }

        function showToast(message, type = 'success') {
            var container = document.getElementById('toastContainer');
            var icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-exclamation-circle-fill');
            var color = type === 'success' ? '#10b981' : (type === 'warning' ? '#f59e0b' : '#dc2626');

            var toast = document.createElement('div');
            toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '') + (type === 'warning' ? 'warning' : '');
            toast.innerHTML = `
                <i class="bi ${icon}" style="color: ${color}; font-size: 1.25rem;"></i>
                <div class="message">${message}</div>
                <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
            `;
            container.appendChild(toast);

            setTimeout(function() {
                if (toast.parentElement) {
                    toast.style.animation = 'slideOutRight 0.3s ease forwards';
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                }
            }, 8000);
        }
    </script>

</body>

</html>
