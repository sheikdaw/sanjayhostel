<!DOCTYPE html>
<html>
<head>
    <title>UPI Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-body text-center">
                <h4>Pay via UPI</h4>
                <form action="{{ route('pay.initiate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" value="1.00" step="0.01" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Pay Now with UPI
                    </button>
                </form>
                <small class="text-muted mt-2 d-block">Opens GPay/PhonePe/Paytm</small>
            </div>
        </div>
    </div>
</body>
</html>