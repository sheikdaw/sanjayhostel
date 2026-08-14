<!DOCTYPE html>
<html>
<head>
    <title>Payment Result</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: green; }
        .failed { color: red; }
        .pending { color: orange; }
    </style>
</head>
<body>
    @if($success === true)
        <h1 class="success">✅ Payment Successful!</h1>
        <p>Reference: {{ $reference }}</p>
        <p>Amount: ₹{{ number_format($amount ?? 0, 2) }}</p>
        <p>Receipt No: {{ $receipt_no ?? $reference }}</p>
        <a href="{{ url('/guest/payment') }}">Make Another Payment</a>
    @elseif($success === null)
        <h1 class="pending">⏳ Payment Pending</h1>
        <p>{{ $message }}</p>
        <p>Reference: {{ $reference }}</p>
        <a href="{{ url('/guest/payment') }}">Go Back</a>
    @else
        <h1 class="failed">❌ Payment Failed</h1>
        <p>{{ $message }}</p>
        <p>Reference: {{ $reference }}</p>
        <a href="{{ url('/guest/payment') }}">Try Again</a>
    @endif
</body>
</html>