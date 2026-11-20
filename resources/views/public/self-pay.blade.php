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
        :root { --primary:#0A1E3F; --gold:#C5A028; }
        * { box-sizing: border-box; }

        body {
            font-family:'Plus Jakarta Sans',sans-serif;
            background:linear-gradient(135deg,#0A1E3F 0%,#1a3a6b 100%);
            min-height:100vh; margin:0; display:flex; align-items:center;
            justify-content:center; padding:1rem;
        }

        .pay-card {
            background:#fff; border-radius:20px; overflow:hidden;
            box-shadow:0 24px 64px rgba(0,0,0,.3);
            width:100%; max-width:480px;
        }

        .pay-header {
            background:linear-gradient(135deg,var(--primary),#1a3a6b);
            color:#fff; padding:1.75rem 1.5rem 1.5rem;
            text-align:center; position:relative; overflow:hidden;
        }
        .pay-header::after {
            content:''; position:absolute; top:-50px; right:-50px;
            width:180px; height:180px;
            background:rgba(197,160,40,.15); border-radius:50%;
        }
        .pay-logo {
            width:60px; height:60px; border-radius:50%;
            background:var(--gold); display:flex; align-items:center;
            justify-content:center; margin:0 auto .75rem;
            font-size:1.6rem; color:#fff; position:relative; z-index:1;
        }
        .pay-header h1 {
            font-size:1.1rem; font-weight:700; margin:0 0 .25rem;
            position:relative; z-index:1;
        }
        .pay-header p {
            font-size:.75rem; opacity:.8; margin:0;
            position:relative; z-index:1;
        }

        .pay-body { padding:1.5rem; }

        .pay-form-group { margin-bottom:1rem; }
        .pay-form-label {
            display:block; font-size:.75rem; font-weight:700;
            color:#374151; margin-bottom:.4rem;
            text-transform:uppercase; letter-spacing:.5px;
        }
        .pay-input-wrap { position:relative; }
        .pay-input-wrap i {
            position:absolute; left:.9rem; top:50%;
            transform:translateY(-50%); color:#9ca3af; font-size:1rem;
        }
        .pay-input {
            width:100%; padding:.75rem 1rem .75rem 2.5rem;
            border:2px solid #e5e7eb; border-radius:12px;
            font-size:.95rem; font-family:inherit; transition:all .2s;
        }
        .pay-input:focus {
            outline:none; border-color:var(--gold);
            box-shadow:0 0 0 4px rgba(197,160,40,.1);
        }
        .pay-input[readonly] {
            background:#f3f4f6; cursor:not-allowed; color:#374151;
        }

        .pay-btn {
            width:100%; padding:.85rem; border-radius:12px; border:none;
            font-size:.9rem; font-weight:700; cursor:pointer;
            transition:all .2s; display:flex; align-items:center;
            justify-content:center; gap:.5rem; font-family:inherit;
        }
        .pay-btn-primary {
            background:linear-gradient(135deg,var(--gold),#d4af37);
            color:var(--primary);
        }
        .pay-btn-primary:hover {
            transform:translateY(-2px);
            box-shadow:0 8px 20px rgba(197,160,40,.4);
        }
        .pay-btn-primary:disabled {
            opacity:.6; cursor:not-allowed; transform:none;
        }

        .pay-result { display:none; animation:slideUp .4s ease; }
        @keyframes slideUp {
            from{opacity:0;transform:translateY(20px)}
            to{opacity:1;transform:translateY(0)}
        }

        .resident-card {
            background:linear-gradient(135deg,#f8fafc,#f1f5f9);
            border-radius:12px; padding:1rem; margin-bottom:1rem;
            border:1px solid #e5e7eb;
        }
        .resident-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:.3rem 0; font-size:.82rem;
        }
        .resident-row .label { color:#6b7280; }
        .resident-row .value { font-weight:700; color:var(--primary); }

        .month-card {
            background:#fff; border-radius:12px; border:2px solid #e5e7eb;
            padding:1rem; margin-bottom:1rem;
        }
        .month-card.current {
            border-color:var(--gold);
            background:linear-gradient(135deg,#fffbeb,#fef9e7);
        }
        .month-card.previous {
            border-color:#fca5a5;
            background:linear-gradient(135deg,#fef2f2,#fee2e2);
        }
        .month-card-header {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:.75rem; padding-bottom:.5rem;
            border-bottom:1px dashed rgba(0,0,0,.1);
        }
        .month-card-header .month-name {
            font-weight:700; font-size:.85rem; color:var(--primary);
        }

        .badge {
            display:inline-flex; align-items:center; gap:4px;
            padding:3px 10px; border-radius:20px;
            font-size:.65rem; font-weight:700; text-transform:uppercase;
        }
        .badge.paid    { background:#dcfce7; color:#166534; }
        .badge.partial { background:#fef3c7; color:#92400e; }
        .badge.pending { background:#fee2e2; color:#991b1b; }
        .badge.unpaid  { background:#e5e7eb; color:#4b5563; }

        .month-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:.25rem 0; font-size:.78rem;
        }
        .month-row .label { color:#6b7280; }
        .month-row .value {
            font-family:'SF Mono',monospace; font-weight:600; color:#374151;
        }
        .month-row.total {
            border-top:1px dashed #d1d5db; margin-top:.5rem;
            padding-top:.5rem; font-weight:700;
        }
        .month-row.total .value { font-size:1rem; color:var(--primary); }
        .month-row.due .value { color:#dc2626; }
        .month-row.paid-amount .value { color:#059669; }

        .discount-note {
            font-size:.7rem; color:#0891b2; margin-top:.4rem;
            background:#ecfeff; padding:.45rem .6rem; border-radius:6px;
            display:flex; align-items:flex-start; gap:.35rem;
            line-height:1.4;
        }
        .discount-note.none {
            background:#fef3c7; color:#92400e;
        }
        .discount-note i { margin-top:1px; flex-shrink:0; }

        .total-box {
            background:linear-gradient(135deg,var(--primary),#1a3a6b);
            color:#fff; border-radius:14px; padding:1.25rem 1.5rem;
            text-align:center; margin-bottom:1rem;
        }
        .total-box .label {
            font-size:.7rem; text-transform:uppercase;
            letter-spacing:1px; opacity:.85; margin-bottom:.25rem;
        }
        .total-box .amount {
            font-size:2rem; font-weight:800;
            font-family:'SF Mono',monospace;
        }

        .upi-pay-btn {
            display:flex; align-items:center; justify-content:center;
            gap:.5rem; width:100%; padding:1rem; border-radius:12px;
            border:none; background:linear-gradient(135deg,#10b981,#059669);
            color:#fff; font-weight:700; font-size:.95rem;
            cursor:pointer; font-family:inherit; transition:all .2s;
        }
        .upi-pay-btn:hover {
            transform:translateY(-2px);
            box-shadow:0 8px 20px rgba(16,185,129,.4);
        }
        .upi-pay-btn:disabled {
            opacity:.6; cursor:not-allowed; transform:none;
        }

        .no-due-box {
            text-align:center; padding:1.5rem;
            background:linear-gradient(135deg,#f0fdf4,#dcfce7);
            border:2px solid #86efac; border-radius:14px;
        }
        .no-due-box i {
            font-size:3rem; color:#10b981; margin-bottom:.5rem; display:block;
        }
        .no-due-box h3 {
            color:#166534; font-weight:700; margin:0 0 .25rem; font-size:1.1rem;
        }
        .no-due-box p { color:#4b5563; font-size:.8rem; margin:0; }

        .success-box {
            text-align:center; padding:1.5rem;
            background:linear-gradient(135deg,#eff6ff,#dbeafe);
            border:2px solid #93c5fd; border-radius:14px;
        }
        .success-box i {
            font-size:3rem; color:#2563eb; margin-bottom:.5rem; display:block;
        }
        .success-box h3 {
            color:#1e40af; font-weight:700; margin:0 0 .25rem; font-size:1.1rem;
        }
        .success-box p { color:#4b5563; font-size:.8rem; margin:0; }

        .error-box {
            background:#fef2f2; border:2px solid #fca5a5;
            border-radius:12px; padding:1rem;
            display:flex; align-items:flex-start; gap:.75rem;
            margin-bottom:1rem;
        }
        .error-box i { color:#dc2626; font-size:1.25rem; margin-top:1px; }
        .error-box p {
            color:#991b1b; font-size:.85rem; margin:0; font-weight:500;
        }

        .pay-footer {
            text-align:center; padding:1rem; background:#f9fafb;
            border-top:1px solid #f3f4f6; font-size:.7rem; color:#9ca3af;
        }

        .spinner {
            width:16px; height:16px;
            border:2px solid rgba(255,255,255,.3);
            border-top-color:#fff; border-radius:50%;
            animation:spin .6s linear infinite;
            display:inline-block;
        }
        @keyframes spin { to { transform:rotate(360deg); } }

        .prev-months {
            display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.5rem;
        }
        .prev-month-tag {
            background:#fee2e2; color:#991b1b;
            padding:2px 8px; border-radius:12px;
            font-size:.65rem; font-weight:600;
        }

        .manual-section {
            border-top:2px dashed #e5e7eb;
            padding-top:1rem; margin-top:.5rem;
        }
        .manual-title {
            font-size:.95rem; font-weight:700;
            color:var(--primary); margin:0 0 .4rem;
        }
        .manual-hint {
            font-size:.72rem; color:#6b7280;
            margin:0 0 .85rem; line-height:1.4;
        }

        .date-hint {
            font-size:.68rem; color:#6b7280; margin-top:.3rem;
            line-height:1.3;
        }
        .date-hint strong { color:#0891b2; }

        .amount-loading {
            opacity:.5; pointer-events:none;
        }

        .pay-toast {
            position:fixed; bottom:24px; left:50%;
            transform:translateX(-50%);
            padding:12px 20px; border-radius:10px;
            color:#fff; font-weight:600; font-size:.85rem;
            z-index:10001; max-width:90%; text-align:center;
        }

        @media (max-width:480px) {
            .pay-body { padding:1.15rem; }
            .total-box .amount { font-size:1.6rem; }
        }
    </style>
</head>
<body>

<div class="pay-card">
    <div class="pay-header">
        <div class="pay-logo"><i class="bi bi-house-heart-fill"></i></div>
        <h1>{{ $hostel->hostel_name }}</h1>
        <p><i class="bi bi-shield-check"></i> Secure Rent Payment</p>
    </div>

    <div class="pay-body">

        {{-- STEP 1: Phone lookup --}}
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

        {{-- STEP 2: Dues + manual form --}}
        <div class="pay-result" id="resultSection">
            <div id="resultContent"></div>
            <button type="button" class="pay-btn" onclick="resetForm()"
                    style="background:#f3f4f6;color:#374151;margin-top:1rem;">
                <i class="bi bi-arrow-left"></i> Check Another Number
            </button>
        </div>

    </div>

    <div class="pay-footer">
        <i class="bi bi-lock-fill"></i> Powered by Sanjay PG Hostel Management
    </div>
</div>

<script>
/* ═══════════════════════════════════════════════════════════
   CONFIG
   ═══════════════════════════════════════════════════════════ */
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const LOOKUP_URL = "{{ route('public.self-pay.lookup', $encodedHostelId) }}";
const SUBMIT_URL = "{{ route('public.self-pay.submit', $encodedHostelId) }}";

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
let currentPhone    = '';
let currentDate     = todayStr();

function todayStr() {
    const d = new Date();
    return d.getFullYear() + '-'
        + String(d.getMonth() + 1).padStart(2, '0') + '-'
        + String(d.getDate()).padStart(2, '0');
}

/* ═══════════════════════════════════════════════════════════
   PHONE LOOKUP
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
        const res  = await fetch(LOOKUP_URL, {
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':CSRF_TOKEN,
                'Accept':'application/json'
            },
            body: JSON.stringify({ phone, payment_date: todayStr() })
        });
        const data = await res.json();
        if (!data.success) {
            showError(data.message || 'Not found');
            return;
        }
        renderResult(data, phone, todayStr());
    } catch (err) {
        console.error(err);
        showError('Network error. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitText.textContent = 'Check My Dues';
    }
});

function showError(msg) {
    errorBox.innerHTML = `
        <div class="error-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <p>${escapeHtml(msg)}</p>
        </div>`;
}

/* ═══════════════════════════════════════════════════════════
   RECALCULATE ON DATE CHANGE
   ═══════════════════════════════════════════════════════════ */
async function recalculateDues() {
    const dateEl = document.getElementById('manualDate');
    if (!dateEl || !currentPhone) return;

    const dateVal = dateEl.value;
    if (!dateVal) return;

    const amtEl = document.getElementById('manualAmount');
    const oldAmt = amtEl ? amtEl.value : '';
    if (amtEl) amtEl.value = '...';

    try {
        const res = await fetch(LOOKUP_URL, {
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':CSRF_TOKEN,
                'Accept':'application/json'
            },
            body: JSON.stringify({ phone: currentPhone, payment_date: dateVal })
        });
        const data = await res.json();

        if (!data.success) {
            showToast('❌ ' + (data.message || 'Recalc failed'), 'error');
            if (amtEl) amtEl.value = oldAmt;
            return;
        }

        currentTotalDue = parseFloat(data.total_due) || 0;
        currentDate     = dateVal;

        renderResult(data, currentPhone, dateVal);
        showToast('✅ Dues updated for ' + data.as_of, 'info');

    } catch (e) {
        console.error(e);
        showToast('❌ Network error', 'error');
        if (amtEl) amtEl.value = oldAmt;
    }
}

/* ═══════════════════════════════════════════════════════════
   RENDER RESULT
   ═══════════════════════════════════════════════════════════ */
function renderResult(data, phone, keepDate) {
    const r    = data.resident;
    const curr = data.current_month;
    const prev = data.previous_pending;

    currentTotalDue = parseFloat(data.total_due) || 0;
    currentPhone    = phone;

    const statusClass = (curr.status || 'unpaid').toLowerCase();
    const dateVal     = keepDate || todayStr();
    currentDate       = dateVal;

    let html = `
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
        </div>`;

    html += `
        <div class="month-card current">
            <div class="month-card-header">
                <span class="month-name">📅 ${escapeHtml(curr.month)}</span>
                <span class="badge ${statusClass}">
                    ${curr.status === 'PAID' ? '✅ Paid'
                        : curr.status === 'PARTIAL' ? '🟡 Partial'
                        : '⬜ Unpaid'}
                </span>
            </div>
            <div class="month-row">
                <span class="label">Rent</span>
                <span class="value">₹${formatNumber(curr.rent)}</span>
            </div>
            ${curr.total_discount > 0 ? `
                <div class="month-row">
                    <span class="label">Discount (auto)</span>
                    <span class="value" style="color:#0891b2;">- ₹${formatNumber(curr.total_discount)}</span>
                </div>
                <div class="discount-note">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>
                        Based on <strong>${escapeHtml(data.as_of)}</strong>
                        (day ${data.as_of_day}): ₹${formatNumber(curr.applied_date_discount)}
                        discount applied. <strong>Not editable.</strong>
                    </span>
                </div>
            ` : `
                <div class="discount-note none">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>
                        No date-based discount for
                        <strong>${escapeHtml(data.as_of)}</strong>
                        (day ${data.as_of_day}).
                    </span>
                </div>
            `}
            ${curr.fine > 0 ? `
                <div class="month-row">
                    <span class="label">Fine</span>
                    <span class="value" style="color:#dc2626;">+ ₹${formatNumber(curr.fine)}</span>
                </div>
            ` : ''}
            <div class="month-row paid-amount">
                <span class="label">Paid</span>
                <span class="value">₹${formatNumber(curr.paid)}</span>
            </div>
            <div class="month-row total ${curr.balance > 0 ? 'due' : ''}">
                <span class="label">Balance</span>
                <span class="value">₹${formatNumber(curr.balance)}</span>
            </div>
        </div>`;

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
                            <span class="prev-month-tag">
                                ${escapeHtml(m.label)}: ₹${formatNumber(m.amount)}
                            </span>
                        `).join('')}
                    </div>
                ` : ''}
            </div>`;
    }

    if (currentTotalDue > 0) {
        html += `
            <div class="total-box">
                <div class="label">Total Amount Due</div>
                <div class="amount">₹${formatNumber(currentTotalDue)}</div>
            </div>

            <div class="manual-section" id="manualForm">
                <h4 class="manual-title">💸 Enter Your Payment Details</h4>
                <p class="manual-hint">
                    Pay ₹${formatNumber(currentTotalDue)} via GPay / PhonePe / Paytm
                    to this hostel's UPI, then fill this form with the UPI
                    transaction ID you received.
                </p>

                <div class="pay-form-group">
                    <label class="pay-form-label">📅 Payment Date</label>
                    <input type="date" class="pay-input" id="manualDate"
                           value="${dateVal}"
                           max="${todayStr()}"
                           onchange="recalculateDues()"
                           style="padding-left:1rem;">
                    <div class="date-hint">
                        Discount auto-changes with date:
                        <strong>Day 1–5 → ₹250</strong> ·
                        <strong>Day 6–10 → ₹125</strong> ·
                        <strong>Day 11+ → ₹0</strong>
                    </div>
                </div>

                <div class="pay-form-group">
                    <label class="pay-form-label">Amount (₹)</label>
                    <input type="number" class="pay-input" id="manualAmount"
                           value="${currentTotalDue}" readonly
                           style="padding-left:1rem;">
                </div>

                <div class="pay-form-group">
                    <label class="pay-form-label">UPI Transaction ID / UTR</label>
                    <input type="text" class="pay-input" id="manualTxn"
                           placeholder="e.g. 4321789023456" maxlength="100"
                           style="padding-left:1rem;">
                </div>

                <div class="pay-form-group">
                    <label class="pay-form-label">Note (optional)</label>
                    <input type="text" class="pay-input" id="manualNote"
                           placeholder="e.g. Paid via GPay" maxlength="255"
                           style="padding-left:1rem;">
                </div>

                <div id="manualError" style="color:#dc2626;font-size:.8rem;margin-bottom:.5rem;"></div>

                <button type="button" class="upi-pay-btn" id="manualSubmitBtn"
                        onclick="submitManual()">
                    <i class="bi bi-check-circle-fill"></i> Submit Payment
                </button>
            </div>`;
    } else {
        html += `
            <div class="no-due-box">
                <i class="bi bi-check-circle-fill"></i>
                <h3>All Dues Cleared! 🎉</h3>
                <p>You have no pending payments for ${escapeHtml(data.as_of)}.</p>
            </div>`;
    }

    resultContent.innerHTML = html;
    lookupForm.style.display = 'none';
    resultSection.style.display = 'block';
}

/* ═══════════════════════════════════════════════════════════
   SUBMIT MANUAL PAYMENT
   ═══════════════════════════════════════════════════════════ */
async function submitManual() {
    const btn     = document.getElementById('manualSubmitBtn');
    const errEl   = document.getElementById('manualError');
    const amount  = currentTotalDue;
    const txn     = document.getElementById('manualTxn').value.trim();
    const note    = document.getElementById('manualNote').value.trim();
    const payDate = document.getElementById('manualDate').value;

    errEl.textContent = '';

    if (!payDate) {
        errEl.textContent = 'Please select a payment date.';
        return;
    }
    if (txn.length < 6) {
        errEl.textContent = 'Please enter a valid transaction ID (min 6 chars).';
        return;
    }

    btn.disabled = true;
    const old = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> Submitting...';

    try {
        const res = await fetch(SUBMIT_URL, {
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':CSRF_TOKEN,
                'Accept':'application/json'
            },
            body: JSON.stringify({
                phone: currentPhone,
                amount,
                transaction_id: txn,
                note: note || null,
                payment_date: payDate
            })
        });
        const data = await res.json();

        if (!data.success) {
            errEl.textContent = data.message || 'Submit failed';
            btn.disabled = false;
            btn.innerHTML = old;
            return;
        }

        resultContent.innerHTML = `
            <div class="success-box">
                <i class="bi bi-hourglass-split"></i>
                <h3>Payment Submitted! 🎉</h3>
                <p>
                    Admin will verify your transaction shortly.<br>
                    Date: <strong>${escapeHtml(payDate)}</strong><br>
                    Ref: <strong>${escapeHtml(txn)}</strong>
                </p>
            </div>`;

        showToast('✅ Submitted. Admin verification pending.', 'success');

    } catch (e) {
        console.error(e);
        errEl.textContent = 'Network error. Try again.';
        btn.disabled = false;
        btn.innerHTML = old;
    }
}

/* ═══════════════════════════════════════════════════════════
   RESET
   ═══════════════════════════════════════════════════════════ */
function resetForm() {
    phoneInput.value = '';
    errorBox.innerHTML = '';
    resultSection.style.display = 'none';
    lookupForm.style.display = 'block';
    currentTotalDue = 0;
    currentPhone = '';
    currentDate  = todayStr();
}

/* ═══════════════════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════════════════ */
function showToast(message, type) {
    const existing = document.querySelector('.pay-toast');
    if (existing) existing.remove();

    const colors = {
        success: { bg:'#10b981', shadow:'rgba(16,185,129,.4)' },
        error:   { bg:'#dc2626', shadow:'rgba(220,38,38,.4)' },
        info:    { bg:'#3b82f6', shadow:'rgba(59,130,246,.4)' }
    };
    const c = colors[type] || colors.success;

    const toast = document.createElement('div');
    toast.className = 'pay-toast';
    toast.style.background = c.bg;
    toast.style.boxShadow = `0 8px 24px ${c.shadow}`;
    toast.innerHTML = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'all .3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* ═══════════════════════════════════════════════════════════
   HELPERS
   ═══════════════════════════════════════════════════════════ */
function formatNumber(n) {
    const num = parseFloat(n) || 0;
    return num.toLocaleString('en-IN', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/[&<>"']/g, c => ({
        '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
    }[c]));
}
</script>

</body>
</html>
