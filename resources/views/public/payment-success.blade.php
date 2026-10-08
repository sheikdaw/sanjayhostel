<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Success — Sanjay PG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
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
            max-width: 420px;
            width: 100%;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
        }
        .success-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1.5rem;
            animation: popIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        @keyframes popIn {
            0%   { transform: scale(0); }
            100% { transform: scale(1); }
        }
        h2 {
            color: #0A1E3F;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }
        .divider {
            height: 1px;
            background: #f3f4f6;
            margin: 1.5rem 0;
        }
        .footer-note {
            font-size: 0.72rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2>Payment Successful!</h2>
        <p>Your payment has been received. Thank you!</p>

        <div class="divider"></div>

        <p class="footer-note">
            <i class="bi bi-lock-fill"></i>
            Sanjay PG Hostel Management
        </p>
    </div>

    <script>
        // Auto-close if opened via UPI app
        setTimeout(() => {
            if (window.history.length > 1) {
                // Just leave the page open — user can close it
            }
        }, 100);
    </script>
</body>
</html>