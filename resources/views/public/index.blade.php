@extends('layouts.office')

@section('title', 'Payment Links — Sanjay PG Hostel')
@section('page_title', 'Payment Links')

@push('styles')
<style>
    .pl-page-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;
    }
    .pl-page-title {
        font-size: 1.25rem; font-weight: 700; color: var(--sanjay-primary);
        margin: 0; display: flex; align-items: center; gap: 0.5rem;
    }
    .pl-page-title i { color: var(--sanjay-gold); }
    .pl-page-subtitle { font-size: 0.8rem; color: #6b7280; margin: 0.25rem 0 0 0; }

    .pl-info-box {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 0.85rem 1.1rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.82rem;
        color: #1e40af;
    }
    .pl-info-box i { font-size: 1.1rem; margin-top: 1px; flex-shrink: 0; }
    .pl-info-box strong { color: #1e3a8a; }

    .pl-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1rem;
    }

    .pl-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }
    .pl-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.08);
        border-color: rgba(197, 160, 40, 0.3);
    }
    .pl-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--sanjay-gold), var(--sanjay-primary));
    }

    .pl-card-head {
        padding: 1.1rem 1.25rem 0.85rem;
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .pl-card-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(197, 160, 40, 0.15), rgba(10, 30, 63, 0.08));
        color: var(--sanjay-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .pl-card-info { flex: 1; min-width: 0; }
    .pl-card-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0 0 0.2rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pl-card-code {
        font-size: 0.68rem;
        color: #9ca3af;
        font-family: 'DM Mono', monospace;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .pl-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .pl-badge.active   { background: #dcfce7; color: #166534; }
    .pl-badge.inactive { background: #fee2e2; color: #991b1b; }
    .pl-badge.male     { background: #dbeafe; color: #1e40af; }
    .pl-badge.female   { background: #fce7f3; color: #9d174d; }
    .pl-badge.co-ed    { background: #f3e8ff; color: #6b21a8; }

    .pl-card-body {
        padding: 1rem 1.25rem;
    }

    .pl-label {
        font-size: 0.65rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }

    .pl-url-box {
        display: flex;
        gap: 0.4rem;
        margin-bottom: 0.85rem;
    }
    .pl-url-input {
        flex: 1;
        padding: 0.5rem 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.72rem;
        font-family: 'DM Mono', monospace;
        background: #f8fafc;
        color: #374151;
        min-width: 0;
        cursor: pointer;
        text-overflow: ellipsis;
    }
    .pl-url-input:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        background: white;
    }

    .pl-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        white-space: nowrap;
    }
    .pl-btn-copy {
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
    }
    .pl-btn-copy:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(197, 160, 40, 0.35);
    }
    .pl-btn-copy.copied {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .pl-actions-row {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .pl-btn-secondary {
        background: white;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }
    .pl-btn-secondary:hover {
        background: #f9fafb;
        color: var(--sanjay-primary);
        border-color: var(--sanjay-gold);
    }
    .pl-btn-whatsapp {
        background: linear-gradient(135deg, #25d366, #128c7e);
        color: white;
    }
    .pl-btn-whatsapp:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        color: white;
    }
    .pl-btn-qr {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }
    .pl-btn-qr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .pl-qr-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
    }
    .pl-qr-modal .modal-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        padding: 1rem 1.5rem;
    }
    .pl-qr-modal .modal-title { font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
    .pl-qr-modal .modal-title i { color: var(--sanjay-gold); }
    .pl-qr-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .pl-qr-modal .modal-body { padding: 1.5rem; text-align: center; }
    .pl-qr-image {
        width: 250px;
        height: 250px;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        padding: 8px;
        background: white;
    }

    .pl-empty {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        border: 2px dashed #e5e7eb;
        grid-column: 1 / -1;
    }
    .pl-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 0.75rem; }

    .pl-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #10b981;
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: plToastIn 0.3s ease;
    }
    @keyframes plToastIn {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 768px) {
        .pl-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="pl-page-header">
    <div>
        <h2 class="pl-page-title">
            <i class="bi bi-link-45deg"></i>
            Payment Links
        </h2>
        <p class="pl-page-subtitle">Share these links with residents to collect payments</p>
    </div>
</div>

<div class="pl-info-box">
    <i class="bi bi-info-circle-fill"></i>
    <div>
        <strong>How it works:</strong>
        Share the link with residents. They open it, enter their mobile number, see pending dues, and pay via UPI.
        Each hostel has its own unique secure link.
    </div>
</div>

<div class="pl-grid">
    @forelse($links as $link)
        <div class="pl-card">
            <div class="pl-card-head">
                <div class="pl-card-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="pl-card-info">
                    <h3 class="pl-card-name" title="{{ $link['name'] }}">{{ $link['name'] }}</h3>
                    <div class="pl-card-code">
                        <span>{{ $link['code'] }}</span>
                        <span class="pl-badge {{ $link['status'] }}">{{ ucfirst($link['status']) }}</span>
                        <span class="pl-badge {{ $link['type'] }}">{{ ucfirst($link['type']) }}</span>
                    </div>
                </div>
            </div>

            <div class="pl-card-body">
                <div class="pl-label">📎 Payment Link</div>
                <div class="pl-url-box">
                    <input type="text"
                           class="pl-url-input"
                           value="{{ $link['url'] }}"
                           readonly
                           id="url-{{ $link['id'] }}"
                           onclick="this.select()">
                    <button type="button"
                            class="pl-btn pl-btn-copy"
                            onclick="copyLink({{ $link['id'] }}, this)">
                        <i class="bi bi-clipboard"></i>
                        <span>Copy</span>
                    </button>
                </div>

                <div class="pl-label">🚀 Quick Actions</div>
                <div class="pl-actions-row">
                    <a href="{{ $link['url'] }}"
                       target="_blank"
                       class="pl-btn pl-btn-secondary">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>

                    <button type="button"
                            class="pl-btn pl-btn-qr"
                            data-id="{{ $link['id'] }}"
                            data-name="{{ $link['name'] }}"
                            data-qr="{{ $link['qr'] }}"
                            onclick="showQR(this.dataset.id, this.dataset.name, this.dataset.qr)">
                        <i class="bi bi-qr-code"></i> QR Code
                    </button>

                    <a href="https://wa.me/?text={{ urlencode('🏠 ' . $link['name'] . ' — Pay your rent online: ' . $link['url']) }}"
                       target="_blank"
                       class="pl-btn pl-btn-whatsapp">
                        <i class="bi bi-whatsapp"></i> Share
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="pl-empty">
            <i class="bi bi-inbox"></i>
            <h5 style="color:var(--sanjay-primary); font-weight:700;">No Hostels Found</h5>
            <p style="color:#6b7280; font-size:0.85rem;">Add a hostel first to generate payment links.</p>
        </div>
    @endforelse
</div>

<div class="modal fade pl-qr-modal" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:340px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-qr-code"></i>
                    Scan to Pay
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="qrHostelName" style="font-weight:700; color:var(--sanjay-primary); margin-bottom:0.75rem;"></h6>
                <img id="qrImage" src="" alt="QR Code" class="pl-qr-image">
                <p style="font-size:0.72rem; color:#9ca3af; margin-top:0.75rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    Residents can scan this to open the payment page
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyLink(hostelId, btn) {
        const input = document.getElementById('url-' + hostelId);
        input.select();
        input.setSelectionRange(0, 99999);

        const doFallbackCopy = () => {
            try {
                document.execCommand('copy');
                return true;
            } catch (e) {
                return false;
            }
        };

        const onSuccess = () => {
            const originalHTML = btn.innerHTML;
            btn.classList.add('copied');
            btn.innerHTML = '<i class="bi bi-check-lg"></i><span>Copied!</span>';

            showToast('✅ Link copied to clipboard');

            setTimeout(() => {
                btn.classList.remove('copied');
                btn.innerHTML = originalHTML;
            }, 2000);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(onSuccess).catch(() => {
                if (doFallbackCopy()) onSuccess();
                else showToast('❌ Copy failed — please copy manually', 'error');
            });
        } else {
            if (doFallbackCopy()) onSuccess();
            else showToast('❌ Copy failed — please copy manually', 'error');
        }
    }

    function showQR(hostelId, hostelName, qrUrl) {
        document.getElementById('qrHostelName').textContent = hostelName;
        document.getElementById('qrImage').src = qrUrl;
        new bootstrap.Modal(document.getElementById('qrModal')).show();
    }

    function showToast(message, type = 'success') {
        const existing = document.querySelector('.pl-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'pl-toast';
        if (type === 'error') {
            toast.style.background = '#dc2626';
            toast.style.boxShadow = '0 8px 24px rgba(220, 38, 38, 0.4)';
        }
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'all 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
</script>
@endpush

@endsection