@extends('layouts.office')

@section('title', 'Payment Links')
@section('page_title', 'Generate Payment Links')

@push('styles')
<style>
    .link-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }
    .link-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .link-card .hostel-name {
        font-weight: 700;
        color: var(--sanjay-primary);
    }
    .link-card .link-url {
        background: #f8fafc;
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.8rem;
        word-break: break-all;
        font-family: monospace;
        margin: 0.5rem 0;
    }
    .copy-btn {
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        border: none;
        background: var(--sanjay-gold);
        color: white;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
    .copy-btn:hover {
        background: #b08c1e;
    }
    .qr-preview {
        display: inline-block;
        background: white;
        padding: 0.5rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .qr-preview img {
        width: 80px;
        height: 80px;
    }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Payment Links</h1>
        <p class="ol-page-sub">Generate secure encoded payment links for each hostel</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="ds-card">
            <div class="card-body">
                <h5 class="mb-3"><i class="bi bi-link-45deg"></i> Hostel Payment Links</h5>

                @foreach($hostels as $hostel)
                    <div class="link-card">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="hostel-name">
                                    <i class="bi bi-building"></i> {{ $hostel->hostel_name }}
                                </div>
                                <small class="text-muted">ID: {{ $hostel->id }}</small>
                            </div>
                            <div class="col-md-6">
                                <div class="link-url" id="link-{{ $hostel->id }}">
                                    {{ $encodedLinks[$hostel->id] ?? '' }}
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="copy-btn" onclick="copyLink('{{ $hostel->id }}')">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                                <button class="btn btn-sm btn-outline-secondary ms-1" onclick="generateQR('{{ $hostel->id }}')">
                                    <i class="bi bi-qr-code"></i>
                                </button>
                                <div class="qr-preview mt-2" id="qr-{{ $hostel->id }}" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function copyLink(hostelId) {
    const link = document.getElementById('link-' + hostelId).textContent;
    navigator.clipboard.writeText(link).then(() => {
        showToast('Link copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback
        const input = document.createElement('input');
        input.value = link;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showToast('Link copied to clipboard!', 'success');
    });
}

function generateQR(hostelId) {
    const link = document.getElementById('link-' + hostelId).textContent;
    const qrContainer = document.getElementById('qr-' + hostelId);

    if (qrContainer.style.display === 'block') {
        qrContainer.style.display = 'none';
        return;
    }

    // Generate QR code using an API
    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(link);
    qrContainer.innerHTML = '<img src="' + qrUrl + '" alt="QR Code">';
    qrContainer.style.display = 'block';
}

function showToast(message, type = 'success') {
    // Your existing toast function
    const toast = document.createElement('div');
    toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '');
    toast.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}"
           style="color: ${type === 'success' ? '#10b981' : '#dc2626'}; font-size: 1.25rem;"></i>
        <div class="message">${message}</div>
        <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}
</script>

@endsection
