<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment {{ $success ? 'Success' : 'Processing' }} — Sanjay PG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @unless($success)
        <meta http-equiv="refresh" content="4">
    @endunless

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #0A1E3F, #1a3a6b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            margin: 0;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
        }
        .success-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1.5rem;
            animation: popIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .success-icon.pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        @keyframes popIn {
            0%   { transform: scale(0); }
            100% { transform: scale(1); }
        }
        h2 {
            color: #0A1E3F;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }
        .divider {
            height: 1px;
            background: #f3f4f6;
            margin: 1.5rem 0;
        }
        .footer-note {
            font-size: 0.72rem;
            color: #9ca3af;
        }
        .order-ref {
            font-family: 'SF Mono', monospace;
            font-size: 0.7rem;
            color: #9ca3af;
            margin-top: 0.5rem;
            word-break: break-all;
        }
        .spinner-inline {
            display: inline-block;
            width: 14px; height: 14px;
            border: 2px solid rgba(0,0,0,0.15);
            border-top-color: #d97706;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: -2px;
            margin-right: 6px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="success-card">
        @if($success)
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2>Payment Successful!</h2>
            <p>Your payment has been received. Thank you!</p>
        @else
            <div class="success-icon pending">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <h2>Verifying Payment…</h2>
            <p><span class="spinner-inline"></span>Confirming with the bank. This usually takes a few seconds.</p>
        @endif

        @if(!empty($orderId))
            <div class="order-ref">Ref: {{ $orderId }}</div>
        @endif

        <div class="divider"></div>

        <p class="footer-note">
            <i class="bi bi-lock-fill"></i>
            Sanjay PG Hostel Management
        </p>
    </div>
</body>
</html>