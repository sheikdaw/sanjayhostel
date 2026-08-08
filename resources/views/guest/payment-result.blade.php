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

        h3 { font-weight: 700; margin-bottom: 0.5rem; }
        h3.success-text { color: #166534; }
        h3.failed-text { color: #991b1b; }

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
    </style>
</head>

<body>

    <div class="result-container" id="resultApp"
        data-state="{{ $success === true ? 'success' : ($success === false ? 'failed' : 'pending') }}"
        data-reference="{{ $reference ?? '' }}">

        <!-- Pending / checking -->
        <div id="pendingBlock" @if($success !== null) style="display:none;" @endif>
            <div class="spinner-border text-primary" role="status"></div>
            <h3 class="mt-3">Checking your payment…</h3>
            <p class="muted">{{ $message ?? 'Please wait while we confirm your payment status.' }}</p>
        </div>

        <!-- Success -->
        <div id="successBlock" @if($success !== true) style="display:none;" @endif>
            <div class="icon-circle success"><i class="bi bi-check-lg"></i></div>
            <h3 class="success-text">Payment Successful! 🎉</h3>
            <p class="muted">Your payment has been recorded.</p>

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
                    <span class="value" id="receiptText">{{ $receipt_no ?? '-' }}</span>
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
        // No "did you pay?" question anywhere here. If PhonePe already
        // told the server COMPLETED/FAILED on the way in, we show that
        // straight away. If it's still PENDING, we quietly poll the real
        // order status every few seconds until PhonePe gives a final
        // answer, then flip the UI automatically.
        var app = document.getElementById('resultApp');
        var state = app.dataset.state;
        var reference = app.dataset.reference;
        var statusUrl = '{{ route('guest.payment.status') }}';
        var pollTimer = null;
        var attempts = 0;
        var maxAttempts = 30; // ~2 minutes at 4s intervals

        if (state === 'pending' && reference) {
            checkStatus();
            pollTimer = setInterval(checkStatus, 4000);
        }

        function checkStatus() {
            attempts++;

            $.get(statusUrl, { reference: reference })
                .done(function(response) {
                    if (!response.success) return;

                    if (response.state === 'COMPLETED') {
                        clearInterval(pollTimer);
                        showSuccess(response.data);
                    } else if (response.state === 'FAILED') {
                        clearInterval(pollTimer);
                        showFailed('Payment failed. Please try again.');
                    } else if (attempts >= maxAttempts) {
                        clearInterval(pollTimer);
                        showFailed('We could not confirm your payment yet. If money was deducted, it will reflect shortly — please check back later.');
                    }
                    // otherwise still PENDING — keep polling silently
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
            document.getElementById('amtText').textContent = '₹' + parseFloat(data.amount).toFixed(2);
            document.getElementById('receiptText').textContent = data.receipt_no;
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