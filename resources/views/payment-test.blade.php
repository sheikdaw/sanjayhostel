<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI Payment Demo</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .payment-box {
            background: #fff;
            padding: 30px;
            width: 350px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .amount {
            font-size: 30px;
            font-weight: bold;
            margin: 20px 0;
        }

        .pay-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #1976d2;
            color: white;
            font-size: 17px;
            cursor: pointer;
        }

        .pay-btn:hover {
            background: #125ca8;
        }
    </style>
</head>

<body>

<div class="payment-box">

    <h2>Payment</h2>

    <p>Amount to Pay</p>

    <div class="amount">
        ₹100
    </div>

    <button class="pay-btn" onclick="payNow()">
        Pay ₹100 via UPI
    </button>

</div>


<script>

function payNow() {

    // Receiver UPI ID
    const upiId = "sheikjob888@okicici";

    // Receiver name
    const name = "SGT Solutions";

    // Amount
    const amount = "1";

    // Transaction note
    const note = "Test Payment";

    // Create UPI URL
    const upiUrl =
        "upi://pay" +
        "?pa=" + encodeURIComponent(upiId) +
        "&pn=" + encodeURIComponent(name) +
        "&am=" + encodeURIComponent(amount) +
        "&cu=INR" +
        "&tn=" + encodeURIComponent(note);

    // Open UPI application
    window.location.href = upiUrl;
}

</script>

</body>
</html>