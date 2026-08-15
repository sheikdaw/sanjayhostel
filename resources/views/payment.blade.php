<!DOCTYPE html>
<html>
<head>
    <title>UPI Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .payment-card {
            max-width: 450px;
            margin: 30px auto;
            padding: 25px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            border: 2px dashed #4CAF50;
            margin: 15px 0;
        }
        .qr-container img, .qr-container svg {
            width: 100%;
            max-width: 250px;
            height: auto;
        }
        .upi-id {
            background: #f0f8f0;
            padding: 10px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 16px;
            word-break: break-all;
        }
        .btn-pay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-pay:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .auto-pay-timer {
            background: #fff3cd;
            padding: 10px;
            border-radius: 10px;
            margin-top: 15px;
            display: none;
        }
        .upi-apps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        .upi-app-btn {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        .upi-app-btn:hover {
            border-color: #4CAF50;
            background: #f0f8f0;
        }
        .upi-app-btn .icon {
            font-size: 30px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-card">
            <h4 class="text-center mb-3">💰 Pay via UPI</h4>
            
            <!-- UPI ID Display -->
            <div class="text-center mb-3">
                <small class="text-muted">Pay to:</small>
                <div class="upi-id">{{ $upiId }}</div>
            </div>

            <!-- QR Code -->
            <div class="qr-container text-center">
                <h6 class="mb-2">📱 Scan to Pay</h6>
                {!! $qrCode !!}
                <small class="text-muted d-block mt-2">Or click below to auto-pay</small>
            </div>

            <!-- Amount -->
            <div class="text-center mb-3">
                <h2 class="text-success">₹{{ number_format($amount, 2) }}</h2>
            </div>

            <!-- UPI Apps Quick Pay -->
            <div class="upi-apps">
                <button class="upi-app-btn" onclick="payWithUPI('gpay')">
                    <span class="icon">📱</span>
                    Google Pay
                </button>
                <button class="upi-app-btn" onclick="payWithUPI('phonepe')">
                    <span class="icon">🟣</span>
                    PhonePe
                </button>
                <button class="upi-app-btn" onclick="payWithUPI('paytm')">
                    <span class="icon">🔵</span>
                    Paytm
                </button>
            </div>

            <!-- Pay Button -->
            <form action="{{ route('pay.initiate') }}" method="POST" id="paymentForm">
                @csrf
                <input type="hidden" name="amount" value="{{ $amount }}">
                <button type="submit" class="btn btn-pay w-100 text-white">
                    🚀 Pay Now (Auto Open UPI)
                </button>
            </form>

            <!-- Auto-Pay Timer -->
            <div class="auto-pay-timer" id="autoPayTimer">
                <span>⏰ Auto-opening UPI app in <span id="timer">5</span> seconds...</span>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    🔒 Secure UPI Payment • Instant Transfer
                </small>
            </div>
        </div>
    </div>

    <script>
        // Auto open UPI after 3 seconds
        setTimeout(function() {
            document.getElementById('autoPayTimer').style.display = 'block';
            let timer = 3;
            const timerElement = document.getElementById('timer');
            
            const countdown = setInterval(function() {
                timer--;
                timerElement.textContent = timer;
                
                if (timer <= 0) {
                    clearInterval(countdown);
                    document.getElementById('paymentForm').submit();
                }
            }, 1000);
        }, 2000);

        // Manual UPI App selection
        function payWithUPI(app) {
            let upiUrl = "{{ $upiUrl }}";
            
            // App-specific URLs
            const appUrls = {
                'gpay': 'googlepay://upi/pay?',
                'phonepe': 'phonepe://upi/pay?',
                'paytm': 'paytm://upi/pay?'
            };
            
            if (app === 'gpay') {
                window.location.href = upiUrl;
            } else if (app === 'phonepe') {
                window.location.href = upiUrl;
            } else if (app === 'paytm') {
                window.location.href = upiUrl;
            }
            
            // Fallback: use default UPI
            setTimeout(function() {
                window.location.href = upiUrl;
            }, 500);
        }

        // Auto-detect mobile and open UPI
        if (/Mobi|Android|iPhone|iPad/i.test(navigator.userAgent)) {
            console.log('📱 Mobile detected - Auto UPI mode');
        }
    </script>
</body>
</html>