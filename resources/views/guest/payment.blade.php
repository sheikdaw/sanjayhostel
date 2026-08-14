<!DOCTYPE html>
<html>
<head>
    <title>Pay Rent - {{ $hostel->hostel_name ?? 'Hostel' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        /* Add your styles here */
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Pay Rent</h2>
        <div id="resident-form">
            <!-- Resident lookup form -->
            <input type="text" id="mobile" placeholder="Enter Mobile Number">
            <button onclick="fetchResident()">Get Details</button>
        </div>
        
        <div id="resident-details" style="display:none;">
            <!-- Resident details and payment info -->
        </div>
        
        <div id="payment-button" style="display:none;">
            <button onclick="initiatePayment()">Pay Now</button>
        </div>
    </div>

    <script>
        let residentData = null;
        let orderData = null;

        function fetchResident() {
            const mobile = document.getElementById('mobile').value;
            const hostelId = '{{ $hostelId }}';
            
            fetch('{{ route("guest.payment.resident") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ mobile, hostel_id: hostelId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    residentData = data.data;
                    displayResidentDetails(residentData);
                    document.getElementById('payment-button').style.display = 'block';
                } else {
                    alert(data.message || 'Resident not found');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function displayResidentDetails(data) {
            const container = document.getElementById('resident-details');
            container.style.display = 'block';
            container.innerHTML = `
                <h3>${data.name}</h3>
                <p>Room: ${data.room_no}</p>
                <p>Phone: ${data.phone}</p>
                <p>Amount to Pay: ₹${data.amount_to_pay}</p>
                <p>${data.discount_message}</p>
                <p>${data.fine_message}</p>
            `;
        }

        function initiatePayment() {
            if (!residentData) {
                alert('Please fetch resident details first');
                return;
            }

            const amount = residentData.amount_to_pay;
            const reference = residentData.reference;
            const residentId = residentData.resident_id;

            // Create order
            fetch('{{ route("guest.payment.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    amount: amount,
                    reference: reference,
                    resident_id: residentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    orderData = data;
                    openRazorpayCheckout(data);
                } else {
                    alert('Failed to create order: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function openRazorpayCheckout(data) {
            const options = {
                key: data.key_id,
                amount: data.amount * 100, // in paise
                currency: data.currency,
                name: 'Hostel Payment',
                description: 'Rent Payment',
                order_id: data.order_id,
                prefill: {
                    name: residentData.name,
                    email: residentData.email,
                    contact: residentData.phone
                },
                notes: {
                    resident_id: residentData.resident_id,
                    reference: data.reference
                },
                theme: {
                    color: '#F37254'
                },
                handler: function(response) {
                    verifyPayment(response, data.reference);
                },
                modal: {
                    ondismiss: function() {
                        window.location.href = '{{ route("guest.payment.callback") }}?status=cancelled&reference=' + data.reference;
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        }

        function verifyPayment(response, reference) {
            fetch('{{ route("guest.payment.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_signature: response.razorpay_signature,
                    reference: reference
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("guest.payment.callback") }}?status=success&reference=' + reference;
                } else {
                    alert('Payment verification failed: ' + data.message);
                    window.location.href = '{{ route("guest.payment.callback") }}?status=failed&reference=' + reference;
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>