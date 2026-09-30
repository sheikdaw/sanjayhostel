<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1E3F, #1a3a6b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
        }
        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
        h2 { color: #0A1E3F; font-weight: 700; margin-bottom: 0.5rem; }
        p { color: #6b7280; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2>Payment Successful!</h2>
        <p>Your payment has been received. Thank you!</p>
        <p style="font-size:0.75rem; margin-top:2rem; color:#9ca3af;">
            Sanjay PG Hostel Management
        </p>
    </div>
</body>
</html>
