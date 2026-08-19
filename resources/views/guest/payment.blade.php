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
            --axis-color: #0066b3;
            --axis-gradient: linear-gradient(135deg, #0066b3 0%, #004080 100%);
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
            background: var(--axis-gradient);
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

        .payment-header .axis-badge {
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

        .btn-pay-now {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            background: var(--axis-gradient);
            color: white;
            transition: all 0.3s;
            margin-top: 0.5rem;
        }

        .btn-pay-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 102, 179, 0.3);
        }

        .btn-pay-now:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-pay-now .axis-icon {
            font-size: 1.1rem;
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
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @media (max-width: 480px) {
            .payment-body {
                padding: 1.25rem;
            }
            .mobile-input-group {
                flex-direction: column;
            }
            .mobile-input-group .btn-find {
                width: 100%;
            }
            .resident-info .info-row {
                flex-direction: column;
                gap: 0.2rem;
            }
            .resident-info .info-row .value {
                text-align: left;
            }
            .payment-methods {
                gap: 0.5rem;
            }
        }

        /* Axis Bank Loading Overlay */
        .payment-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99999;
            align-items: center;
            justify-content: center;
        }

        .payment-overlay.show {
            display: flex;
        }

        .payment-overlay .spinner {
            background: white;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            max-width: 400px;
        }

        .payment-overlay .spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .payment-overlay .spinner p {
            margin-top: 15px;
            color: #333;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <!-- Payment Overlay -->
    <div class="payment-overlay" id="paymentOverlay">
        <div class="spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p id="overlayMessage">Redirecting to Axis Bank secure payment gateway...</p>
        </div>
    </div>

    <div class="payment-container" id="paymentApp">
        <!-- Header -->
        <div class="payment-header">
            <span class="secure-badge"><i class="bi bi-shield-lock"></i> Secure</span>
            <span class="axis-badge">Powered by Axis Bank</span>
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
                    <span class="badge-discount" id="discountBadge"><i class="bi bi-tag"></i> <span id="discountText">0%
                            off</span></span>
                    <span class="badge-fine" id="fineBadge" style="display: none;"><i class="bi bi-clock"></i> <span
                            id="fineText">Late fee</span></span>
                </div>

                <div class="info-row"
                    style="border-bottom: 2px solid var(--gold-color); padding-bottom: 0.75rem; margin-bottom: 0.5rem;">
                    <span class="label"><i class="bi bi-currency-rupee"></i> Amount to Pay</span>
                    <span class="value due-amount" id="totalDue">₹0.00</span>
                </div>

                <div id="pendingInfo">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span id="pendingCount">0</span> pending payments from previous months
                </div>

                <button class="btn-pay-now" id="payNowBtn" onclick="initiateAxisPayment()">
                    <i class="bi bi-shield-check axis-icon"></i> Pay with Axis Bank
                </button>

                <div class="payment-methods">
                    <span
                        style="font-size: 0.7rem; color: #9ca3af; width: 100%; text-align: center; margin-bottom: 0.25rem;">
                        Secure payments via
                    </span>
                    <img src="https://www.axisbank.com/images/axis-bank-logo.svg" alt="Axis Bank" class="active"
                        style="height: 20px;">
                    <img src="https://www.visa.com/logo.svg" alt="Visa" style="height: 20px;">
                    <img src="https://www.mastercard.com/logo.svg" alt="Mastercard" style="height: 20px;">
                    <img src="https://www.upi.com/logo.svg" alt="UPI" style="height: 20px;">
                </div>

                <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.5rem; text-align:center;">
                    You'll be redirected to Axis Bank's secure checkout. Once you finish paying there,
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
            createOrder: '{{ route('guest.payment.create-order') }}',
            verify: '{{ route('guest.payment.verify') }}',
            callback: '{{ route('guest.payment.callback') }}'
        };

        let currentResident = null;
        let currentReference = null;
        let orderData = null;

        $(document).ready(function() {
            $('#mobileInput').on('keypress', function(e) {
                if (e.key === 'Enter') {
                    findResident();
                }
            });

            // Check for payment callback results
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const reference = urlParams.get('reference');
            const transactionId = urlParams.get('transaction_id');

            if (status === 'success' && reference) {
                showToast('✅ Payment successful! Reference: ' + reference, 'success');
                checkPaymentStatus(reference);
            } else if (status === 'cancelled' && reference) {
                showToast('Payment was cancelled. You can try again.', 'error');
                $('#paymentOverlay').removeClass('show');
            } else if (status === 'failed' && reference) {
                showToast('Payment failed. Please try again.', 'error');
                $('#paymentOverlay').removeClass('show');
            } else if (transactionId) {
                // Check if we have a transaction ID but no status, might be a successful redirect
                if (reference) {
                    checkPaymentStatus(reference);
                }
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

                        const amountToPay = parseFloat(response.data.amount_to_pay || response.data.total_due);
                        $('#totalDue').text('₹' + amountToPay.toFixed(2));

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

                        $('#residentInfo').addClass('show');
                        showToast('Resident found! Click "Pay with Axis Bank" to continue.', 'success');
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

        function initiateAxisPayment() {
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
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Creating order...');

            // Show overlay
            $('#paymentOverlay').addClass('show');
            $('#overlayMessage').text('Creating payment order...');

            // Step 1: Create Axis Bank Order
            $.ajax({
                url: paymentRoutes.createOrder,
                type: 'POST',
                data: {
                    amount: amount,
                    reference: currentReference,
                    resident_id: currentResident.resident_id,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        orderData = response;
                        $('#overlayMessage').text('Redirecting to Axis Bank secure payment gateway...');
                        redirectToAxisBank(response);
                    } else {
                        $('#paymentOverlay').removeClass('show');
                        showToast('Failed to create payment order: ' + (response.message || 'Unknown error'),
                            'error');
                        btn.prop('disabled', false).html(
                            '<i class="bi bi-shield-check axis-icon"></i> Pay with Axis Bank');
                    }
                },
                error: function(xhr) {
                    $('#paymentOverlay').removeClass('show');
                    var message = 'Failed to create payment order';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('❌ ' + message, 'error');
                    btn.prop('disabled', false).html(
                        '<i class="bi bi-shield-check axis-icon"></i> Pay with Axis Bank');
                }
            });
        }

        function redirectToAxisBank(data) {
            // Store the reference in session for callback
            sessionStorage.setItem('axis_payment_reference', data.reference);
            sessionStorage.setItem('axis_order_id', data.order_id);

            // Build the Axis Bank payment URL with all parameters
            const paymentUrl = data.payment_url || 'https://secure.axisbank.com/payment';

            // Create a form to submit to Axis Bank
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = paymentUrl;

            // Add all required parameters
            const params = {
                merchant_id: data.merchant_id,
                order_id: data.order_id,
                transaction_id: data.transaction_id,
                amount: data.amount,
                currency: data.currency || 'INR',
                reference_id: data.reference,
                return_url: '{{ url('/guest/payment/callback') }}',
                cancel_url: '{{ url('/guest/payment/cancel') }}',
                signature: data.signature,
                customer_name: currentResident.name,
                customer_email: currentResident.email || '',
                customer_phone: currentResident.phone,
            };

            // Add resident_id and reference in notes
            params.notes_resident_id = currentResident.resident_id;
            params.notes_reference = data.reference;

            // Create hidden inputs
            for (let key in params) {
                if (params[key] !== null && params[key] !== undefined) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = params[key];
                    form.appendChild(input);
                }
            }

            document.body.appendChild(form);

            // Submit the form to redirect to Axis Bank
            form.submit();

            // Fallback: if form submission doesn't work, redirect directly
            setTimeout(function() {
                if (document.body.contains(form)) {
                    document.body.removeChild(form);
                    window.location.href = paymentUrl + '?' + new URLSearchParams(params).toString();
                }
            }, 3000);
        }

        function verifyAxisPayment(orderId, transactionId, reference, signature) {
            const btn = $('#payNowBtn');
            btn.html('<span class="spinner-border spinner-border-sm"></span> Verifying payment...');
            $('#overlayMessage').text('Verifying your payment...');

            $.ajax({
                url: paymentRoutes.verify,
                type: 'POST',
                data: {
                    order_id: orderId,
                    transaction_id: transactionId,
                    reference: reference,
                    signature: signature,
                    status: 'SUCCESS',
                    _token: csrfToken
                },
                success: function(data) {
                    if (data.success) {
                        showToast('✅ Payment successful! Reference: ' + reference, 'success');
                        window.location.href = paymentRoutes.callback +
                            '?reference=' + encodeURIComponent(reference) +
                            '&transaction_id=' + encodeURIComponent(transactionId) +
                            '&status=success';
                    } else {
                        $('#paymentOverlay').removeClass('show');
                        showToast('Payment verification failed: ' + (data.message || 'Unknown error'),
                            'error');
                        btn.prop('disabled', false).html(
                            '<i class="bi bi-shield-check axis-icon"></i> Pay with Axis Bank');
                    }
                },
                error: function(xhr) {
                    $('#paymentOverlay').removeClass('show');
                    var message = 'Payment verification failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('❌ ' + message, 'error');
                    btn.prop('disabled', false).html(
                        '<i class="bi bi-shield-check axis-icon"></i> Pay with Axis Bank');
                }
            });
        }

        function checkPaymentStatus(reference) {
            $.ajax({
                url: paymentRoutes.callback + '?reference=' + reference + '&ajax=1',
                type: 'GET',
                success: function(data) {
                    if (data.success) {
                        showToast('✅ Payment confirmed!', 'success');
                    }
                },
                error: function() {
                    // Silent fail
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
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                }
            }, 8000);
        }

        // Handle browser back/forward for payment flow
        window.addEventListener('popstate', function(e) {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status === 'success' || status === 'cancelled') {
                location.reload();
            }
        });
    </script>

</body>

</html>