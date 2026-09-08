<!DOCTYPE html>
<html>
<head>
    <title>Payment Result - Axis Bank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            width: 100%;
        }
        .card {
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            background: white;
            text-align: center;
            border: none;
        }
        .success { color: #22c55e; }
        .failed { color: #dc2626; }
        .pending { color: #f59e0b; }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: #0066b3;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #004080;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 102, 179, 0.3);
        }
        .details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .details .row {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details .row:last-child {
            border-bottom: none;
        }
        .details .label {
            color: #6b7280;
            font-size: 0.85rem;
        }
        .details .value {
            font-weight: 600;
            color: #1f2937;
        }
        .axis-badge {
            display: inline-block;
            background: #0066b3;
            color: white;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="axis-badge"><i class="bi bi-shield-check"></i> Axis Bank</div>

            @if(isset($success) && $success === true)
                <div class="icon success"><i class="bi bi-check-circle-fill"></i></div>
                <h1 class="success">Payment Successful!</h1>
                <p style="color: #6b7280; margin-bottom: 0.5rem;">Your payment has been processed successfully.</p>

            @elseif(isset($success) && $success === null)
                <div class="icon pending"><i class="bi bi-clock-history"></i></div>
                <h1 class="pending">Payment Processing</h1>
                <p style="color: #6b7280; margin-bottom: 0.5rem;">{{ $message ?? 'Your payment is being processed.' }}</p>

            @else
                <div class="icon failed"><i class="bi bi-x-circle-fill"></i></div>
                <h1 class="failed">Payment Failed</h1>
                <p style="color: #6b7280; margin-bottom: 0.5rem;">{{ $message ?? 'Payment could not be completed.' }}</p>
            @endif

            <div class="details">
                <div class="row d-flex justify-content-between">
                    <span class="label">Reference Number</span>
                    <span class="value">{{ $reference ?? 'N/A' }}</span>
                </div>
                @if(isset($amount) && $amount !== null)
                <div class="row d-flex justify-content-between">
                    <span class="label">Amount</span>
                    <span class="value">₹{{ number_format($amount, 2) }}</span>
                </div>
                @endif
                @if(isset($receipt_no) && $receipt_no !== null)
                <div class="row d-flex justify-content-between">
                    <span class="label">Receipt Number</span>
                    <span class="value">{{ $receipt_no }}</span>
                </div>
                @endif
                <div class="row d-flex justify-content-between">
                    <span class="label">Payment Method</span>
                    <span class="value">{{ $payment_method ?? 'Axis Bank' }}</span>
                </div>
                <div class="row d-flex justify-content-between">
                    <span class="label">Date & Time</span>
                    <span class="value">{{ now()->format('d M Y h:i A') }}</span>
                </div>
            </div>

            @if(isset($success) && $success === true)
                <a href="{{ url('/guest/payment') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Make Another Payment
                </a>
            @elseif(isset($success) && $success === null)
                <a href="{{ url('/guest/payment') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Go Back
                </a>
                <button onclick="window.location.reload()" class="btn btn-outline-secondary mt-2" style="width:100%;">
                    <i class="bi bi-arrow-repeat"></i> Check Status Again
                </button>
            @else
                <a href="{{ url('/guest/payment') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat"></i> Try Again
                </a>
            @endif

            <p style="margin-top: 1.5rem; font-size: 0.75rem; color: #9ca3af;">
                <i class="bi bi-lock"></i> Secured by Axis Bank
            </p>
        </div>
    </div>
</body>
</html>