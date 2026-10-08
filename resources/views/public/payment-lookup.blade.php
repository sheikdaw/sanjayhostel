<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay Rent — {{ $hostel->hostel_name }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0A1E3F;
            --gold: #C5A028;
            --gold-light: #E8D5A3;
        }
        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0A1E3F 0%, #1a3a6b 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .pay-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
        }

        .pay-header {
            background: linear-gradient(135deg, var(--primary), #1a3a6b);
            color: white;
            padding: 1.75rem 1.5rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .pay-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 180px;
            height: 180px;
            background: rgba(197, 160, 40, 0.15);
            border-radius: 50%;
        }

        .pay-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.6rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .pay-header h1 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
            position: relative;
            z-index: 1;
        }

        .pay-header p {
            font-size: 0.75rem;
            opacity: 0.8;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .pay-body {
            padding: 1.5rem;
        }

        .pay-form-group {
            margin-bottom: 1rem;
        }

        .pay-form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pay-input-wrap {
            position: relative;
        }

        .pay-input-wrap i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
        }

        .pay-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .pay-input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(197, 160, 40, 0.1);
        }

        .pay-btn {
            width: 100%;
            padding: 0.85rem;
            border-radius: 12px;
            border: none;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: inherit;
        }

        .pay-btn-primary {
            background: linear-gradient(135deg, var(--gold), #d4af37);
            color: var(--primary);
        }

        .pay-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(197, 160, 40, 0.4);
        }

        .pay-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .pay-result {
            display: none;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .resident-card {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
        }

        .resident-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.3rem 0;
            font-size: 0.82rem;
        }

        .resident-row .label { color: #6b7280; }
        .resident-row .value { font-weight: 700; color: var(--primary); }

        .month-card {
            background: white;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .month-card.current {
            border-color: var(--gold);
            background: linear-gradient(135deg, #fffbeb, #fef9e7);
        }

        .month-card.previous {
            border-color: #fca5a5;
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
        }

        .month-card.paid {
            border-color: #86efac;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        }

        .month-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(0,0,0,0.1);
        }

        .month-card-header .month-name {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--primary);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge.paid    { background: #dcfce7; color: #166534; }
        .badge.partial { background: #fef3c7; color: #92400e; }
        .badge.pending { background: #fee2e2; color: #991b1b; }
        .badge.unpaid  { background: #e5e7eb; color: #4b5563; }

        .month-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            font-size: 0.78rem;
        }

        .month-row .label { color: #6b7280; }
        .month-row .value {
            font-family: 'SF Mono', 'Monaco', monospace;
            font-weight: 600;
            color: #374151;
        }

        .month-row.total {
            border-top: 1px dashed #d1d5db;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            font-weight: 700;
        }

        .month-row.total .value {
            font-size: 1rem;
            color: var(--primary);
        }

        .month-row.due .value { color: #dc2626; }
        .month-row.paid-amount .value { color: #059669; }

        .total-box {
            background: linear-gradient(135deg, var(--primary), #1a3a6b);
            color: white;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .total-box .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.85;
            margin-bottom: 0.25rem;
        }

        .total-box .amount {
            font-size: 2rem;
            font-weight: 800;
            font-family: 'SF Mono', monospace;
        }

        .total-box.paid-all {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .upi-pay-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-weight: 700;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .upi-pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .upi-pay-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .upi-pay-btn i { font-size: 1.1rem; }

        .no-due-box {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 2px solid #86efac;
            border-radius: 14px;
        }

        .no-due-box i {
            font-size: 3rem;
            color: #10b981;
            margin-bottom: 0.5rem;
            display: block;
        }

        .no-due-box h3 {
            color: #166534;
            font-weight: 700;
            margin: 0 0 0.25rem;
            font-size: 1.1rem;
        }

        .no-due-box p {
            color: #4b5563;
            font-size: 0.8rem;
            margin: 0;
        }

        .error-box {
            background: #fef2f2;
            border: 2px solid #fca5a5;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .error-box i {
            color: #dc2626;
            font-size: 1.25rem;
            margin-top: 1px;
        }

        .error-box p {
            color: #991b1b;
            font-size: 0.85rem;
            margin: 0;
            font-weight: 500;
        }

        .pay-footer {
            text-align: center;
            padding: 1rem;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            font-size: 0.7rem;
            color: #9ca3af;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: inline-block;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .prev-months {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.5rem;
        }

        .prev-month-tag {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .qr-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 1rem;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .qr-modal-content {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            max-width: 340px;
            width: 100%;
            box-shadow: 0 24px 64px rgba(0,0,0,0.4);
        }

        .qr-modal-content h3 {
            color: var(--primary);
            font-weight: 700;
            margin: 0 0 0.25rem;
            font-size: 1.05rem;
        }

        .qr-modal-content .qr-amount {
            color: #059669;
            font-weight: 800;
            font-size: 1.5rem;
            font-family: 'SF Mono', monospace;
            margin-bottom: 1rem;
        }

        .qr-modal-content img {
            width: 250px;
            height: 250px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 8px;
            background: white;
        }

        .qr-modal-content .qr-note {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.75rem;
        }

        .qr-modal-close {
            margin-top: 1rem;
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .qr-modal-close:hover { background: #1a3a6b; }

        .redirect-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 30, 63, 0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .redirect-overlay .big-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.2);
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 1.5rem;
        }

        .redirect-overlay h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
        }

        .redirect-overlay p {
            font-size: 0.85rem;
            opacity: 0.8;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="pay-card">
    {{-- Header --}}
    <div class="pay-header">
        <div class="pay-logo">
            <i class="bi bi-house-heart-fill"></i>
        </div>
        <h1>{{ $hostel->hostel_name }}</h1>
        <p><i class="bi bi-shield-check"></i> Secure Rent Payment</p>
    </div>

    {{-- Body --}}
    <div class="pay-body">

        {{-- Lookup Form --}}
        <div id="lookupForm">
            <form id="phoneForm" autocomplete="off">
                @csrf
                <div class="pay-form-group">
                    <label class="pay-form-label">📱 Enter Your Mobile Number</label>
                    <div class="pay-input-wrap">
                        <i class="bi bi-phone"></i>
                        <input type="tel" class="pay-input" id="phoneInput"
                               placeholder="9876543210" maxlength="15" required>
                    </div>
                </div>

                <div id="errorContainer"></div>

                <button type="submit" class="pay-btn pay-btn-primary" id="submitBtn">
                    <i class="bi bi-search"></i>
                    <span id="submitText">Check My Dues</span>
                </button>
            </form>
        </div>

        {{-- Result Section --}}
        <div class="pay-result" id="resultSection">
            <div id="resultContent"></div>

            <button type="button" class="pay-btn" onclick="resetForm()"
                    style="background:#f3f4f6; color:#374151; margin-top:1rem;">
                <i class="bi bi-arrow-left"></i>
                Check Another Number
            </button>
        </div>

    </div>

    {{-- Footer --}}
    <div class="pay-footer">
        <i class="bi bi-lock-fill"></i> Powered by Sanjay PG Hostel Management
    </div>
</div>

<script>
    /* ═══════════════════════════════════════════════════════════
       CONFIG
       ═══════════════════════════════════════════════════════════ */
    const CSRF_TOKEN   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const LOOKUP_URL   = "{{ route('public.payment.lookup',   $encodedHostelId) }}";
    const INITIATE_URL = "{{ route('public.payment.initiate', $encodedHostelId) }}";

    /* ═══════════════════════════════════════════════════════════
       DOM REFS
       ═══════════════════════════════════════════════════════════ */
    const form          = document.getElementById('phoneForm');
    const phoneInput    = document.getElementById('phoneInput');
    const submitBtn     = document.getElementById('submitBtn');
    const submitText    = document.getElementById('submitText');
    const errorBox      = document.getElementById('errorContainer');
    const lookupForm    = document.getElementById('lookupForm');
    const resultSection = document.getElementById('resultSection');
    const resultContent = document.getElementById('resultContent');

    /* ═══════════════════════════════════════════════════════════
       STATE
       ═══════════════════════════════════════════════════════════ */
    let currentTotalDue = 0;
    let currentPhone    = '';   // ★ FIX: captured at lookup time, never read from hidden input

    /* ═══════════════════════════════════════════════════════════
       LOOKUP FORM SUBMIT
       ═══════════════════════════════════════════════════════════ */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const phone = phoneInput.value.trim();
        if (!phone || phone.length < 10) {
            showError('Please enter a valid 10-digit mobile number');
            return;
        }

        submitBtn.disabled = true;
        submitText.innerHTML = '<span class="spinner"></span> Searching...';
        errorBox.innerHTML = '';

        try {
            const response = await fetch(LOOKUP_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone })
            });

            const data = await response.json();

            if (!data.success) {
                showError(data.message || 'Not found');
                return;
            }

            renderResult(data, phone);   // ★ FIX: pass phone through

        } catch (err) {
            console.error(err);
            showError('Network error. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Check My Dues';
        }
    });

    /* ═══════════════════════════════════════════════════════════
       SHOW ERROR
       ═══════════════════════════════════════════════════════════ */
    function showError(msg) {
        errorBox.innerHTML = `
            <div class="error-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <p>${escapeHtml(msg)}</p>
            </div>`;
    }

    /* ═══════════════════════════════════════════════════════════
       RENDER RESULT
       ═══════════════════════════════════════════════════════════ */
    function renderResult(data, phone) {
        const r    = data.resident;
        const curr = data.current_month;
        const prev = data.previous_pending;

        currentTotalDue = parseFloat(data.total_due) || 0;
        currentPhone    = phone;   // ★ FIX: stash for payNow()

        const statusClass = (curr.status || 'unpaid').toLowerCase();

        let html = `
            <!-- Resident Info -->
            <div class="resident-card">
                <div class="resident-row">
                    <span class="label">Name</span>
                    <span class="value">${escapeHtml(r.name)}</span>
                </div>
                <div class="resident-row">
                    <span class="label">Room / Bed</span>
                    <span class="value">${escapeHtml(r.room_no)} / ${escapeHtml(r.bed_no)}</span>
                </div>
                <div class="resident-row">
                    <span class="label">Monthly Rent</span>
                    <span class="value">₹${formatNumber(r.rent_amount)}</span>
                </div>
            </div>
        `;

        html += `
            <div class="month-card current">
                <div class="month-card-header">
                    <span class="month-name">📅 ${escapeHtml(curr.month)}</span>
                    <span class="badge ${statusClass}">
                        ${curr.status === 'PAID' ? '✅ Paid' : curr.status === 'PARTIAL' ? '🟡 Partial' : '⬜ Unpaid'}
                    </span>
                </div>
                <div class="month-row">
                    <span class="label">Rent</span>
                    <span class="value">₹${formatNumber(curr.rent)}</span>
                </div>
                ${curr.total_discount > 0 ? `
                <div class="month-row">
                    <span class="label">Discount</span>
                    <span class="value" style="color:#0891b2;">- ₹${formatNumber(curr.total_discount)}</span>
                </div>` : ''}
                ${curr.fine > 0 ? `
                <div class="month-row">
                    <span class="label">Fine</span>
                    <span class="value" style="color:#dc2626;">+ ₹${formatNumber(curr.fine)}</span>
                </div>` : ''}
                <div class="month-row paid-amount">
                    <span class="label">Paid</span>
                    <span class="value">₹${formatNumber(curr.paid)}</span>
                </div>
                <div class="month-row total ${curr.balance > 0 ? 'due' : ''}">
                    <span class="label">Balance</span>
                    <span class="value">₹${formatNumber(curr.balance)}</span>
                </div>
            </div>
        `;

        if (prev.total > 0) {
            html += `
                <div class="month-card previous">
                    <div class="month-card-header">
                        <span class="month-name">⚠️ Previous Pending</span>
                        <span class="badge pending">Due</span>
                    </div>
                    <div class="month-row total due">
                        <span class="label">Total Previous Due</span>
                        <span class="value">₹${formatNumber(prev.total)}</span>
                    </div>
                    ${prev.months.length > 0 ? `
                        <div class="prev-months">
                            ${prev.months.map(m => `
                                <span class="prev-month-tag">${escapeHtml(m.label)}: ₹${formatNumber(m.amount)}</span>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            `;
        }

        if (currentTotalDue > 0) {
            html += `
                <div class="total-box">
                    <div class="label">Total Amount Due</div>
                    <div class="amount">₹${formatNumber(currentTotalDue)}</div>
                </div>

                <button type="button" class="upi-pay-btn" id="payBtn" onclick="payNow()">
                    <i class="bi bi-credit-card-2-front-fill"></i>
                    Pay ₹${formatNumber(currentTotalDue)}
                </button>
            `;
        } else {
            html += `
                <div class="no-due-box">
                    <i class="bi bi-check-circle-fill"></i>
                    <h3>All Dues Cleared! 🎉</h3>
                    <p>You have no pending payments. Thank you!</p>
                </div>
            `;
        }

        resultContent.innerHTML = html;

        lookupForm.style.display = 'none';
        resultSection.style.display = 'block';
    }

    /* ═══════════════════════════════════════════════════════════
       PAY NOW — uses currentPhone, NOT the hidden input
       ═══════════════════════════════════════════════════════════ */
    async function payNow() {
        // ★ FIX: read from state, not from the hidden input
        const phone = (currentPhone || '').trim();

        if (!phone || phone.length < 10) {
            alert('Please enter a valid mobile number');
            return;
        }

        const btn = document.getElementById('payBtn');
        if (!btn) return;

        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Opening payment...';

        try {
            const res = await fetch(INITIATE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone })   // ★ FIX: valid phone guaranteed
            });

            const data = await res.json();
            console.log('Initiate response:', data);

            if (!data.success) {
                alert(data.message || 'Payment initiation failed');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                return;
            }

            /* 1) Hosted-page redirect */
            if (data.mode === 'redirect' && data.redirect_url) {
                showRedirectOverlay('Redirecting to secure payment page...');
                setTimeout(() => { window.location.href = data.redirect_url; }, 600);
                return;
            }

            /* 2) Gateway form POST */
            if (data.gateway && data.gateway.url && data.gateway.fields) {
                showRedirectOverlay('Redirecting to secure payment page...');

                const f = document.createElement('form');
                f.method = data.gateway.method || 'POST';
                f.action = data.gateway.url;

                Object.entries(data.gateway.fields).forEach(([k, v]) => {
                    const i = document.createElement('input');
                    i.type  = 'hidden';
                    i.name  = k;
                    i.value = v;
                    f.appendChild(i);
                });

                document.body.appendChild(f);
                setTimeout(() => f.submit(), 300);
                return;
            }

            /* 3) UPI deep link */
            if (data.mode === 'upi_link' && data.upi_link) {
                window.location.href = data.upi_link;
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }, 3000);
                showToast('UPI app open aagum... pay pannunga!', 'info');
                return;
            }

            /* 4) Direct QR */
            if (data.qr_code) {
                showQrModal(data.qr_code, currentTotalDue);
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                return;
            }

            /* 5) Unknown */
            alert(data.message || 'Payment method unavailable');
            btn.disabled = false;
            btn.innerHTML = originalHTML;

        } catch (err) {
            console.error(err);
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       QR MODAL
       ═══════════════════════════════════════════════════════════ */
    function showQrModal(qrData, amount) {
        const existing = document.querySelector('.qr-modal-overlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.className = 'qr-modal-overlay';
        overlay.innerHTML = `
            <div class="qr-modal-content">
                <h3>Scan & Pay</h3>
                <div class="qr-amount">₹${formatNumber(amount)}</div>
                <img src="${qrData}" alt="QR Code">
                <p class="qr-note">
                    <i class="bi bi-info-circle"></i>
                    GPay / PhonePe / Paytm la scan pannunga
                </p>
                <button class="qr-modal-close" onclick="this.closest('.qr-modal-overlay').remove()">
                    Close
                </button>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    /* ═══════════════════════════════════════════════════════════
       REDIRECT OVERLAY
       ═══════════════════════════════════════════════════════════ */
    function showRedirectOverlay(message) {
        const existing = document.querySelector('.redirect-overlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.className = 'redirect-overlay';
        overlay.innerHTML = `
            <div class="big-spinner"></div>
            <h3>${escapeHtml(message)}</h3>
            <p>Please do not close this window</p>
        `;
        document.body.appendChild(overlay);
    }

    /* ═══════════════════════════════════════════════════════════
       TOAST
       ═══════════════════════════════════════════════════════════ */
    function showToast(message, type = 'success') {
        const existing = document.querySelector('.pay-toast');
        if (existing) existing.remove();

        const colors = {
            success: { bg: '#10b981', shadow: 'rgba(16, 185, 129, 0.4)' },
            error:   { bg: '#dc2626', shadow: 'rgba(220, 38, 38, 0.4)' },
            info:    { bg: '#3b82f6', shadow: 'rgba(59, 130, 246, 0.4)' }
        };
        const c = colors[type] || colors.success;

        const toast = document.createElement('div');
        toast.className = 'pay-toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: ${c.bg};
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 8px 24px ${c.shadow};
            z-index: 10001;
            max-width: 90%;
            text-align: center;
        `;
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'all 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /* ═══════════════════════════════════════════════════════════
       RESET FORM
       ═══════════════════════════════════════════════════════════ */
    function resetForm() {
        phoneInput.value = '';
        errorBox.innerHTML = '';
        resultSection.style.display = 'none';
        lookupForm.style.display = 'block';
        currentTotalDue = 0;
        currentPhone = '';   // ★ FIX: clear state too
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */
    function formatNumber(n) {
        const num = parseFloat(n) || 0;
        return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
</script>

</body>
</html>