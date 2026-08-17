<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>SGT Solutions - Google Pay</title>


    <!-- Google Pay JavaScript SDK -->

    <script
        async
        src="https://pay.google.com/gp/p/js/pay.js"
        onload="onGooglePayLoaded()">
    </script>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f3f5f7;

        }


        .payment-card {

            width: 380px;

            max-width: 92%;

            background: #ffffff;

            padding: 30px;

            border-radius: 16px;

            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, 0.12);

            text-align: center;

        }


        .payment-card h2 {

            margin: 0 0 8px;

            font-size: 25px;

        }


        .subtitle {

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;

        }


        .amount {

            font-size: 38px;

            font-weight: bold;

            margin-bottom: 25px;

        }


        #google-pay-button {

            display: flex;

            justify-content: center;

        }


        .status {

            margin-top: 20px;

            padding: 12px;

            border-radius: 8px;

            background: #f5f5f5;

            font-size: 14px;

            color: #555;

            word-break: break-word;

        }


        .details {

            margin-top: 20px;

            padding: 15px;

            border-radius: 8px;

            background: #fafafa;

            text-align: left;

            font-size: 13px;

            line-height: 1.8;

        }


        .details strong {

            color: #222;

        }

    </style>

</head>


<body>


<div class="payment-card">


    <h2>
        SGT Solutions
    </h2>


    <div class="subtitle">
        Google Pay UPI Test
    </div>


    <div class="amount">
        ₹1.00
    </div>


    <!-- Google Pay button will appear here -->

    <div id="google-pay-button"></div>


    <div class="details">

        <div>
            <strong>UPI ID:</strong>
            sheikjob88@okicici
        </div>

        <div>
            <strong>Amount:</strong>
            ₹1.00
        </div>

        <div>
            <strong>Currency:</strong>
            INR
        </div>

    </div>


    <div
        id="status"
        class="status">

        Loading Google Pay...

    </div>


</div>



<script>


/*
|--------------------------------------------------------------------------
| GOOGLE PAY SETTINGS
|--------------------------------------------------------------------------
*/


const GOOGLE_PAY_ENVIRONMENT = "TEST";


/*
|--------------------------------------------------------------------------
| YOUR UPI VPA
|--------------------------------------------------------------------------
|
| Your UPI ID
|
*/

const MERCHANT_VPA =
    "sheikjob88@okicici";


/*
|--------------------------------------------------------------------------
| MERCHANT NAME
|--------------------------------------------------------------------------
*/

const MERCHANT_NAME =
    "SGT Solutions";


/*
|--------------------------------------------------------------------------
| MERCHANT CATEGORY CODE
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| For production use the MCC assigned
| during merchant onboarding.
|
*/

const MERCHANT_MCC =
    "0000";


/*
|--------------------------------------------------------------------------
| PAYMENT AMOUNT
|--------------------------------------------------------------------------
*/

const PAYMENT_AMOUNT =
    "1.00";


/*
|--------------------------------------------------------------------------
| CURRENCY
|--------------------------------------------------------------------------
*/

const CURRENCY =
    "INR";


/*
|--------------------------------------------------------------------------
| GOOGLE PAY CLIENT
|--------------------------------------------------------------------------
*/

let paymentsClient = null;



/*
|--------------------------------------------------------------------------
| PAGE LOADED
|--------------------------------------------------------------------------
*/

function onGooglePayLoaded() {

    try {

        paymentsClient =
            new google.payments.api.PaymentsClient({

                environment:
                    GOOGLE_PAY_ENVIRONMENT

            });


        setStatus(
            "Checking Google Pay..."
        );


        checkGooglePay();

    }
    catch (error) {

        console.error(error);

        setStatus(
            "Google Pay initialization failed."
        );

    }

}



/*
|--------------------------------------------------------------------------
| CHECK GOOGLE PAY
|--------------------------------------------------------------------------
*/

function checkGooglePay() {


    const request = {

        apiVersion: 2,

        apiVersionMinor: 0,

        allowedPaymentMethods: [

            {

                type: "UPI"

            }

        ]

    };


    paymentsClient
        .isReadyToPay(request)

        .then(function(response) {


            console.log(
                "Google Pay Ready:",
                response
            );


            if (response.result) {

                createGooglePayButton();

                setStatus(
                    "Google Pay is ready."
                );

            }
            else {

                setStatus(
                    "Google Pay is not available."
                );

            }

        })

        .catch(function(error) {

            console.error(
                "isReadyToPay error:",
                error
            );


            setStatus(
                "Unable to check Google Pay."
            );

        });

}



/*
|--------------------------------------------------------------------------
| CREATE GOOGLE PAY BUTTON
|--------------------------------------------------------------------------
*/

function createGooglePayButton() {


    const button =
        paymentsClient.createButton({

            onClick:
                startPayment

        });


    document
        .getElementById(
            "google-pay-button"
        )
        .appendChild(button);

}



/*
|--------------------------------------------------------------------------
| GENERATE UNIQUE TRANSACTION ID
|--------------------------------------------------------------------------
*/

function generateTransactionId() {


    const timestamp =
        Date.now();


    const random =
        Math.random()
            .toString(36)
            .substring(2, 10)
            .toUpperCase();


    return (
        "SGT-" +
        timestamp +
        "-" +
        random
    );

}



/*
|--------------------------------------------------------------------------
| START PAYMENT
|--------------------------------------------------------------------------
*/

function startPayment() {


    setStatus(
        "Opening Google Pay..."
    );


    /*
    |--------------------------------------------------------------------------
    | UNIQUE TRANSACTION ID
    |--------------------------------------------------------------------------
    */

    const transactionId =
        generateTransactionId();


    console.log(
        "Transaction ID:",
        transactionId
    );


    /*
    |--------------------------------------------------------------------------
    | PAYMENT REQUEST
    |--------------------------------------------------------------------------
    */

    const paymentDataRequest = {


        apiVersion: 2,


        apiVersionMinor: 0,


        allowedPaymentMethods: [


            {


                type: "UPI",


                parameters: {


                    payeeVpa:
                        MERCHANT_VPA,


                    payeeName:
                        MERCHANT_NAME,


                    mcc:
                        MERCHANT_MCC,


                    transactionReferenceId:
                        transactionId,


                    referenceUrl:
                        window.location.href

                }

            }

        ],


        transactionInfo: {


            totalPriceStatus:
                "FINAL",


            totalPrice:
                PAYMENT_AMOUNT,


            currencyCode:
                CURRENCY,


            transactionNote:
                "SGT Solutions Test Payment"

        }

    };


    console.log(
        "Payment Request:",
        paymentDataRequest
    );


    /*
    |--------------------------------------------------------------------------
    | OPEN GOOGLE PAY
    |--------------------------------------------------------------------------
    */

    paymentsClient
        .loadPaymentData(
            paymentDataRequest
        )


        .then(function(paymentData) {


            console.log(
                "Payment Data:",
                paymentData
            );


            setStatus(
                "Payment response received."
            );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | DO NOT mark payment as SUCCESS
            | only from browser response.
            |
            | Send transaction details to
            | your Laravel backend and verify
            | with bank/PSP.
            |
            */


            sendToLaravel(
                transactionId,
                paymentData
            );

        })


        .catch(function(error) {


            console.error(
                "Google Pay Error:",
                error
            );


            if (
                error &&
                error.statusCode
            ) {

                setStatus(
                    "Google Pay Error: " +
                    error.statusCode
                );

            }
            else {

                setStatus(
                    "Payment cancelled or failed."
                );

            }

        });

}



/*
|--------------------------------------------------------------------------
| SEND PAYMENT DATA TO LARAVEL
|--------------------------------------------------------------------------
*/

function sendToLaravel(
    transactionId,
    paymentData
) {


    setStatus(
        "Verifying payment..."
    );


    fetch(
        "/api/google-pay/verify",
        {

            method: "POST",


            headers: {

                "Content-Type":
                    "application/json",

                "Accept":
                    "application/json",

                /*
                |--------------------------------------------------------------------------
                | Laravel CSRF
                |--------------------------------------------------------------------------
                |
                | If using web.php routes,
                | uncomment and configure
                | CSRF token.
                |
                */

                /*
                "X-CSRF-TOKEN":
                    document
                    .querySelector(
                        'meta[name="csrf-token"]'
                    )
                    .getAttribute("content")
                */

            },


            body: JSON.stringify({

                transaction_id:
                    transactionId,

                amount:
                    PAYMENT_AMOUNT,

                upi_id:
                    MERCHANT_VPA,

                payment_data:
                    paymentData

            })

        }
    )


    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                "Server error: " +
                response.status
            );

        }

        return response.json();

    })


    .then(function(result) {


        console.log(
            "Laravel Response:",
            result
        );


        if (
            result.success === true &&
            result.status === "SUCCESS"
        ) {

            setStatus(
                "Payment Successful ✓"
            );

        }
        else {

            setStatus(
                "Payment could not be verified."
            );

        }

    })


    .catch(function(error) {


        console.error(
            "Verification error:",
            error
        );


        setStatus(
            "Payment verification failed."
        );

    });

}



/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

function setStatus(message) {

    document
        .getElementById("status")
        .innerText =
            message;

}


</script>


</body>

</html>