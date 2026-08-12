<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1a3a5c;
            --primary-light: #2a5a8c;
            --gold-color: #c5a028;
        }

        body {
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .result-container {
            max-width: 480px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2.75rem;
        }

        .icon-circle.success { background: #dcfce7; color: #22c55e; }
        .icon-circle.failed { background: #fee2e2; color: #dc2626; }
        .icon-circle.pending { background: #fef3c7; color: #d97706; }

        h3 { font-weight: 700; margin-bottom: 0.5rem; }
        h3.success-text { color: #166534; }
        h3.failed-text { color: #991b1b; }
        h3.pending-text { color: #92400e; }

        .muted { color: #6b7280; font-size: 0.9rem; }

        .receipt-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin: 1.25rem 0;
            text-align: left;
            font-size: 0.9rem;
        }

        .receipt-details .row-line {
            display: flex;
            justify-content: space-between;
            padding: 0.35rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .receipt-details .row-line:last-child { border-bottom: none; }
        .receipt-details .label { color: #6b7280; font-weight: 500; }
        .receipt-details .value { font-weight: 600; color: var(--primary-color); }

        .btn-action {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }

        .btn-action:hover { background: var(--primary-light); color: white; transform: translateY(-2px); }

        .spinner-border { width: 3rem; height: 3rem; }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--gold-color);
            color: var(--gold-color);
        }
        
        .btn-outline:hover {
            background: var(--gold-color);
            color: white;
        }
    </style>
</head>

<body>

    <div class="result-container" id="resultApp"
        data-state="{{ $success === true ? 'success' : ($success === false ? 'failed' : 'pending') }}"
        data-reference="{{ $reference ?? '' }}">

        <!-- Pending / checking -->
        <div id="pendingBlock" @if($success !== null && $success !== 'pending') style="display:none;" @endif>
            <div class="icon-circle pending"><i class="bi bi-clock-history"></i></div>
            <h3 class="pending-text">Processing your payment…</h3>
            <p class="muted">{{ $message ?? 'Please wait while we confirm your payment status.' }}</p>
            <div class="spinner-border text-primary mt-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 1rem;">
                <i class="bi bi-info-circle"></i> This may take a few seconds
            </p>
        </div>

        <!-- Success -->
        <div id="successBlock" @if($success !== true) style="display:none;" @endif>
            <div class="icon-circle success"><i class="bi bi-check-lg"></i></div>
            <h3 class="success-text">Payment Successful! 🎉</h3>
            <p class="muted">Your payment has been recorded successfully.</p>

            <div class="receipt-details">
                <div class="row-line">
                    <span class="label">Reference</span>
                    <span class="value" id="refText">{{ $reference ?? '-' }}</span>
                </div>
                <div class="row-line">
                    <span class="label">Amount Paid</span>
                    <span class="value" id="amtText">₹{{ number_format($amount ?? 0, 2) }}</span>
                </div>
                <div class="row-line">
                    <span class="label">Receipt No</span>
                    <span class="value" id="receiptText">{{ $receipt_no ?? $reference ?? '-' }}</span>
                </div>
                <div class="row-line" style="border-bottom: none; padding-bottom: 0;">
                    <span class="label">Payment Date</span>
                    <span class="value" id="dateText">{{ $payment_date ?? now()->format('d M Y h:i A') }}</span>
                </div>
            </div>

            <a href="{{ url('/guest/payment' . (isset($encodedHostelId) && $encodedHostelId ? '/' . $encodedHostelId : '')) }}" class="btn-action">
                <i class="bi bi-house"></i> Back to Payment Page
            </a>
        </div>

        <!-- Failed -->
        <div id="failedBlock" @if($success !== false) style="display:none;" @endif>
            <div class="icon-circle failed"><i class="bi bi-x-lg"></i></div>
            <h3 class="failed-text">Payment Not Completed</h3>
            <p class="muted" id="failedMessage">{{ $message ?? 'The payment could not be completed. Please try again.' }}</p>

            <a href="{{ url('/guest/payment' . (isset($encodedHostelId) && $encodedHostelId ? '/' . $encodedHostelId : '')) }}" class="btn-action">
                <i class="bi bi-arrow-repeat"></i> Try Again
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var app = document.getElementById('resultApp');
        var state = app.dataset.state;
        var reference = app.dataset.reference;
        var statusUrl = '{{ route('guest.payment.status') }}';
        var pollTimer = null;
        var attempts = 0;
        var maxAttempts = 30; // ~2 minutes at 4s intervals

        // Only poll if state is pending and we have a reference
        if (state === 'pending' && reference) {
            checkStatus();
            pollTimer = setInterval(checkStatus, 4000);
        }

        function checkStatus() {
            attempts++;

            $.get(statusUrl, { reference: reference })
                .done(function(response) {
                    if (!response.success) {
                        // If we've tried too many times, show a timeout message
                        if (attempts >= maxAttempts) {
                            clearInterval(pollTimer);
                            showFailed('We could not confirm your payment yet. If money was deducted, it will reflect shortly — please check back later.');
                        }
                        return;
                    }

                    // Check if payment is completed
                    if (response.state === 'COMPLETED' || response.data?.status === 'PAID') {
                        clearInterval(pollTimer);
                        showSuccess(response.data);
                    } 
                    // Check if payment failed
                    else if (response.state === 'FAILED' || response.data?.status === 'FAILED') {
                        clearInterval(pollTimer);
                        showFailed('Payment failed. Please try again.');
                    } 
                    // If we've reached max attempts, show timeout
                    else if (attempts >= maxAttempts) {
                        clearInterval(pollTimer);
                        showFailed('We could not confirm your payment yet. If money was deducted, it will reflect shortly — please check back later.');
                    }
                    // Otherwise still PENDING — keep polling silently
                })
                .fail(function() {
                    if (attempts >= maxAttempts) {
                        clearInterval(pollTimer);
                        showFailed('We could not confirm your payment status. Please check back later.');
                    }
                });
        }

        function showSuccess(data) {
            document.getElementById('pendingBlock').style.display = 'none';
            document.getElementById('failedBlock').style.display = 'none';
            document.getElementById('successBlock').style.display = '';

            document.getElementById('refText').textContent = reference;
            document.getElementById('amtText').textContent = '₹' + (parseFloat(data?.amount) || 0).toFixed(2);
            document.getElementById('receiptText').textContent = data?.receipt_no || reference;
            document.getElementById('dateText').textContent = data?.payment_date || new Date().toLocaleString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        function showFailed(msg) {
            document.getElementById('pendingBlock').style.display = 'none';
            document.getElementById('successBlock').style.display = 'none';
            document.getElementById('failedBlock').style.display = '';
            document.getElementById('failedMessage').textContent = msg;
        }
    </script>

</body>

</html>