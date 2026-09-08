<!DOCTYPE html>
<html>
<head>
    <title>Payment Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
        }
        .status-card {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
        }
        .status-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        .status-details p {
            margin: 5px 0;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-home:hover {
            transform: scale(1.05);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-card">
            <div class="success-icon">✅</div>
            <h3 class="text-success mt-3">Payment Successful!</h3>
            <p class="text-muted">Your payment has been completed</p>

            <div class="status-details">
                <p><strong>Status:</strong> <span class="text-success">{{ $status }}</span></p>
                <p><strong>Transaction ID:</strong> {{ $txn_id }}</p>
                <p><strong>Amount:</strong> ₹{{ number_format($amount, 2) }}</p>
                <p><strong>UPI ID:</strong> {{ $upi_id ?? 'N/A' }}</p>
            </div>

            <a href="{{ route('pay.form') }}" class="btn-home">
                🔄 Pay Again
            </a>
            
            <div class="mt-3">
                <a href="/" class="text-muted">🏠 Home</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect after 5 seconds
        setTimeout(function() {
            // window.location.href = "{{ route('pay.form') }}";
        }, 5000);
    </script>
</body>
</html>