<!DOCTYPE html>
<html>
<head>
    <title>Payment Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-body text-center">
                @if($status == 'success')
                    <div class="text-success">✅ Payment Successful!</div>
                    <p>Transaction ID: {{ $txn_id }}</p>
                    <p>Amount: ₹{{ $amount }}</p>
                @else
                    <div class="text-danger">❌ Payment Failed</div>
                @endif
                <a href="{{ route('pay.form') }}" class="btn btn-primary mt-3">Pay Again</a>
            </div>
        </div>
    </div>
</body>
</html>