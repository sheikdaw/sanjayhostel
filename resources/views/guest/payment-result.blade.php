<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
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
            max-width: 500px;
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
        .spinner-border { width: 3rem; height: 3rem; }
        .details-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin: 1.25rem 0;
            text-align: left;
        }
        .details-card .row-line {
            display: flex;
            justify-content: space-between;
            padding: 0.35rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-card .row-line:last-child { border-bottom: none; }
        .btn-action {
            width: 100%;
            padding: 0.75rem;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }
        .btn-action:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="result-container">
        @if($success === true)
            <div class="icon-circle success"><i class="bi bi-check-lg"></i></div>
            <h3 class="text-success">Payment Successful! 🎉</h3>
            <p class="text-muted">{{ $message ?? 'Your payment has been recorded.' }}</p>
            
            <div class="details-card">
                <div class="row-line">
                    <span class="text-muted">Reference</span>
                    <span class="fw-bold">{{ $reference ?? '-' }}</span>
                </div>
                <div class="row-line">
                    <span class="text-muted">Amount</span>
                    <span class="fw-bold">₹{{ number_format($amount ?? 0, 2) }}</span>
                </div>
                <div class="row-line">
                    <span class="text-muted">Receipt No</span>
                    <span class="fw-bold">{{ $receipt_no ?? '-' }}</span>
                </div>
                @if($biometric_enabled ?? false)
                <div class="row-line">
                    <span class="text-muted">Biometric Access</span>
                    <span class="fw-bold text-success"><i class="bi bi-check-circle"></i> Enabled</span>
                </div>
                @endif
            </div>
            
            <a href="{{ url('/') }}" class="btn-action btn-primary">
                <i class="bi bi-house"></i> Back to Home
            </a>

        @elseif($success === false)
            <div class="icon-circle failed"><i class="bi bi-x-lg"></i></div>
            <h3 class="text-danger">Payment Failed</h3>
            <p class="text-muted">{{ $message ?? 'Please try again.' }}</p>
            
            <a href="{{ url()->previous() }}" class="btn-action btn-warning text-white">
                <i class="bi bi-arrow-repeat"></i> Try Again
            </a>

        @else
            <div class="icon-circle pending"><i class="bi bi-clock"></i></div>
            <h3 class="text-warning">Processing...</h3>
            <p class="text-muted">{{ $message ?? 'Please wait while we confirm your payment.' }}</p>
            <div class="spinner-border text-warning mt-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            
            <div class="details-card mt-3">
                <div class="row-line">
                    <span class="text-muted">Reference</span>
                    <span class="fw-bold">{{ $reference ?? '-' }}</span>
                </div>
            </div>
            
            <p class="text-muted small mt-2">
                <i class="bi bi-info-circle"></i> This page will update automatically.
            </p>
        @endif
    </div>

    @if($success === null)
    <script>
        // Auto-refresh to check status
        const reference = '{{ $reference ?? '' }}';
        let attempts = 0;
        const maxAttempts = 30;
        
        function checkStatus() {
            if (!reference) return;
            attempts++;
            
            fetch('/guest/payment/status?reference=' + reference)
                .then(res => res.json())
                .then(data => {
                    if (data.state === 'COMPLETED') {
                        location.reload();
                    } else if (data.state === 'FAILED' || attempts >= maxAttempts) {
                        location.reload();
                    }
                })
                .catch(() => {
                    if (attempts >= maxAttempts) {
                        location.reload();
                    }
                });
        }
        
        setInterval(checkStatus, 3000);
    </script>
    @endif
</body>
</html>