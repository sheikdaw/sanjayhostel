<!-- payment-result.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Payment Result</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: green; }
        .failed { color: red; }
        .pending { color: orange; }
        .container { max-width: 500px; margin: 0 auto; }
        .card { padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 10px 20px; background: #0c3b6f; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .btn:hover { background: #1a5a8c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            @if(isset($success) && $success === true)
                <h1 class="success">✅ Payment Successful!</h1>
                <p><strong>Reference:</strong> {{ $reference ?? 'N/A' }}</p>
                <p><strong>Amount:</strong> ₹{{ isset($amount) ? number_format($amount, 2) : '0.00' }}</p>
                <p><strong>Receipt No:</strong> {{ $receipt_no ?? $reference ?? 'N/A' }}</p>
                <a href="{{ url('/guest/payment') }}" class="btn">Make Another Payment</a>
            @elseif(isset($success) && $success === null)
                <h1 class="pending">⏳ Payment Pending</h1>
                <p>{{ $message ?? 'Your payment is being processed.' }}</p>
                <p><strong>Reference:</strong> {{ $reference ?? 'N/A' }}</p>
                <a href="{{ url('/guest/payment') }}" class="btn">Go Back</a>
            @else
                <h1 class="failed">❌ Payment Failed</h1>
                <p>{{ $message ?? 'Payment could not be completed.' }}</p>
                <p><strong>Reference:</strong> {{ $reference ?? 'N/A' }}</p>
                <a href="{{ url('/guest/payment') }}" class="btn">Try Again</a>
            @endif
        </div>
    </div>
</body>
</html>
