<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
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
        .success-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: #22c55e;
            background: #dcfce7;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }
        .receipt-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
        }
        .receipt-box .row { padding: 0.4rem 0; border-bottom: 1px solid #e5e7eb; }
        .receipt-box .row:last-child { border-bottom: none; }
        .receipt-box .label { color: #6b7280; font-size: 0.85rem; }
        .receipt-box .value { font-weight: 600; color: #1a3a5c; }
        .btn-home {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            border: none;
            background: var(--axis-gradient, linear-gradient(135deg, #0066b3 0%, #004080 100%));
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,102,179,0.3); color: white; }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2>Payment Successful! ✅</h2>
        <p class="text-muted">Your rent payment has been completed successfully.</p>

        <div class="receipt-box">
            <div class="row">
                <span class="label">Receipt No</span>
                <span class="value">{{ $receipt_no ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Resident</span>
                <span class="value">{{ $resident->name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Room</span>
                <span class="value">#{{ $resident->room->room_no ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Amount Paid</span>
                <span class="value">{{ number_format($amount ?? 0, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">Date</span>
                <span class="value">{{ now()->format('d M Y h:i A') }}</span>
            </div>
        </div>

        <a href="{{ route('guest.payment.index') }}" class="btn-home">
            <i class="bi bi-house"></i> Back to Payment
        </a>
    </div>
</body>
</html>