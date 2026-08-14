<!DOCTYPE html>
<html>
<head>
    <title>New Enquiry - Sanjay & Harini Hostels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #c9a84c;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .field {
            margin-bottom: 15px;
            padding: 10px;
            background: #fff;
            border-radius: 4px;
            border-left: 3px solid #c9a84c;
        }
        .field-label {
            font-weight: bold;
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .field-value {
            font-size: 1rem;
            margin-top: 3px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 0.85rem;
            color: #888;
        }
        .badge {
            display: inline-block;
            background: #c9a84c;
            color: #fff;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;color:#fff;">📋 New Enquiry Received</h2>
        <p style="margin:5px 0 0;color:#f5f0e0;">Sanjay & Harini Hostels</p>
    </div>

    <div class="content">
        <p style="font-size:1.1rem;margin-top:0;">
            <strong>You have a new enquiry from your website.</strong>
        </p>

        <div class="field">
            <div class="field-label">👤 Full Name</div>
            <div class="field-value">{{ $data['name'] }}</div>
        </div>

        <div class="field">
            <div class="field-label">📞 Phone</div>
            <div class="field-value">{{ $data['phone'] }}</div>
        </div>

        <div class="field">
            <div class="field-label">🎯 Interest</div>
            <div class="field-value">
                @php
                    $interests = [
                        'sanjay_room' => 'Sanjay Boys – Room',
                        'harini_room' => 'Harini Girls – Room',
                        'lunch_box' => 'Lunch Box Delivery',
                        'general' => 'General Enquiry',
                    ];
                @endphp
                {{ $interests[$data['interest']] ?? $data['interest'] }}
            </div>
        </div>

        <div class="field">
            <div class="field-label">📍 Branch</div>
            <div class="field-value">
                @php
                    $branches = [
                        'alandur' => 'Alandur',
                        'st_thomas_mount' => 'St. Thomas Mount',
                        'perungalathur' => 'Perungalathur (Boys only)',
                    ];
                @endphp
                {{ $branches[$data['branch']] ?? $data['branch'] }}
            </div>
        </div>

        @if(!empty($data['message']))
        <div class="field">
            <div class="field-label">💬 Message</div>
            <div class="field-value">{{ $data['message'] }}</div>
        </div>
        @endif

        <div style="margin-top:25px;padding:15px;background:#f0ece0;border-radius:5px;text-align:center;">
            <p style="margin:0;font-size:0.9rem;">
                📅 Received: {{ now()->format('d M Y, h:i A') }}
            </p>
        </div>
    </div>

    <div class="footer">
        <p>This email was sent from the contact form on Sanjay & Harini Hostels website.</p>
        <p style="margin-top:5px;">
            <a href="https://www.sanjayandharinihostels.com" style="color:#c9a84c;text-decoration:none;">sanjayandharinihostels.com</a>
        </p>
    </div>
</body>
</html>
