<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UPI Payment</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: Arial, sans-serif;

            background: #f3f6f9;
        }

        .payment-card {
            width: 380px;
            max-width: 92%;

            background: #ffffff;

            padding: 30px;

            border-radius: 16px;

            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);

            text-align: center;
        }

        .payment-card h2 {
            margin: 0 0 8px;
            font-size: 26px;
        }

        .payment-card p {
            color: #666;
            margin: 0 0 25px;
        }

        .amount {
            font-size: 38px;
            font-weight: bold;

            margin-bottom: 25px;
        }

        .pay-button {
            width: 100%;

            padding: 15px 20px;

            border: none;

            border-radius: 8px;

            background: #1976d2;

            color: #ffffff;

            font-size: 17px;
            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }

        .pay-button:hover {
            background: #125ca8;
        }

        .pay-button:active {
            transform: scale(0.98);
        }

        .upi-details {
            margin-top: 22px;

            padding: 15px;

            border-radius: 10px;

            background: #f7f7f7;

            text-align: left;

            font-size: 14px;

            line-height: 1.7;

            word-break: break-word;
        }

        .label {
            color: #777;
        }

        .value {
            font-weight: bold;
        }

        .status {
            margin-top: 18px;

            font-size: 14px;

            color: #555;

            min-height: 20px;
        }
    </style>
</head>

<body>

<div class="payment-card">

    <h2>UPI Payment</h2>

    <p>Make a secure test payment</p>

    <div class="amount">
        ₹1.00
    </div>

    <button
        type="button"
        class="pay-button"
        onclick="payNow()"
    >
        Pay ₹1 via UPI
    </button>

    <div class="upi-details">

        <div>
            <span class="label">Receiver:</span>
            <span class="value">Sheik Dawood</span>
        </div>

        <div>
            <span class="label">UPI ID:</span>
            <span class="value">sheikjob888@okicici</span>
        </div>

        <div>
            <span class="label">Amount:</span>
            <span class="value">₹1.00</span>
        </div>

    </div>

    <div
        id="status"
        class="status"
    ></div>

</div>


<script>

function payNow() {

    const status = document.getElementById("status");

    status.innerText = "Opening UPI app...";


    /*
    |--------------------------------------------------------------------------
    | Your UPI Payment Details
    |--------------------------------------------------------------------------
    */

    const upiId = "sheikjob888@okicici";

    const receiverName = "Sheik Dawood";

    const amount = "1.00";

    const currency = "INR";

    const note = "Test Payment";

    const aid = "uGICAgMDy_sjXBQ";


    /*
    |--------------------------------------------------------------------------
    | Create UPI URL
    |--------------------------------------------------------------------------
    */

    const upiUrl =
        "upi://pay" +
        "?pa=" + encodeURIComponent(upiId) +
        "&pn=" + encodeURIComponent(receiverName) +
        "&am=" + encodeURIComponent(amount) +
        "&cu=" + encodeURIComponent(currency) +
        "&tn=" + encodeURIComponent(note) +
        "&aid=" + encodeURIComponent(aid);


    /*
    |--------------------------------------------------------------------------
    | Show URL in browser console
    |--------------------------------------------------------------------------
    */

    console.log("UPI URL:");
    console.log(upiUrl);


    /*
    |--------------------------------------------------------------------------
    | Open UPI Application
    |--------------------------------------------------------------------------
    */

    window.location.href = upiUrl;


    /*
    |--------------------------------------------------------------------------
    | Fallback message
    |--------------------------------------------------------------------------
    */

    setTimeout(function () {

        status.innerText =
            "If the UPI app did not open, please open this page on your mobile.";

    }, 2500);

}

</script>

</body>
</html>