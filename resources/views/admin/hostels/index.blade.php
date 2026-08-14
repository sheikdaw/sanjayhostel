<!-- resources/views/admin/hostels/index.blade.php -->

@extends('layouts.office')

@section('title', 'Hostel Management')
@section('page_title', 'Hostel Management')

@push('styles')
<style>
    .hostel-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }
    .hostel-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
    .hostel-header {
        background: var(--sanjay-primary);
        padding: 1.25rem;
        color: white;
        position: relative;
        min-height: 80px;
    }
    .hostel-type-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .hostel-type-badge.men { background: #3b82f6; color: white; }
    .hostel-type-badge.women { background: #ec4899; color: white; }
    .hostel-body { padding: 1rem 1.25rem; }
    .hostel-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin: 0.75rem 0;
    }
    .hostel-stat-item {
        text-align: center;
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: 8px;
    }
    .hostel-stat-item .number { font-size: 1rem; font-weight: 700; color: var(--sanjay-primary); }
    .hostel-stat-item .label { font-size: 0.6rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
    }
    .status-badge:hover { opacity: 0.8; transform: scale(1.05); }
    .status-badge.active { background: #dcfce7; color: #166534; }
    .status-badge.inactive { background: #fee2e2; color: #991b1b; }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.active .dot { background: #22c55e; }
    .status-badge.inactive .dot { background: #ef4444; }

    .btn-action {
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: white;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-action:hover { background: #f3f4f6; }
    .btn-action.text-danger:hover { background: #fee2e2; border-color: #fca5a5; }
    .btn-action.text-primary:hover { background: #e3f2fd; border-color: #90caf9; }
    .btn-action.text-success:hover { background: #dcfce7; border-color: #86efac; }
    .btn-action.text-info:hover { background: #cff4fc; border-color: #81d4fa; }
    .btn-action.text-warning:hover { background: #fef3c7; border-color: #fcd34d; }

    .biometric-section {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.75rem;
        margin: 0.75rem 0;
        border: 1px solid #e5e7eb;
    }
    .biometric-section .biometric-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .biometric-section .biometric-header .title {
        font-size: 0.7rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .biometric-status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 4px;
    }
    .biometric-status-dot.online { background: #22c55e; }
    .biometric-status-dot.offline { background: #ef4444; }
    .biometric-status-dot.configuring { background: #f59e0b; }
    .biometric-status-dot.not-configured { background: #9ca3af; }

    .biometric-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    .biometric-stat-item {
        text-align: center;
        padding: 0.3rem;
        background: white;
        border-radius: 6px;
    }
    .biometric-stat-item .number { font-size: 0.9rem; font-weight: 700; color: var(--sanjay-primary); }
    .biometric-stat-item .label { font-size: 0.55rem; color: #6b7280; text-transform: uppercase; }

    .upi-section {
        background: #f0f7ff;
        border-radius: 8px;
        padding: 0.75rem;
        margin: 0.75rem 0;
        border: 1px solid #dbeafe;
    }
    .upi-section .upi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .upi-section .upi-header .title {
        font-size: 0.7rem;
        font-weight: 600;
        color: #1a56db;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .upi-status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 4px;
    }
    .upi-status-dot.configured { background: #22c55e; }
    .upi-status-dot.not-configured { background: #ef4444; }
    .upi-id-display {
        font-family: monospace;
        font-size: 0.8rem;
        color: #1a56db;
        background: white;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        display: inline-block;
    }

    .modal-content { border-radius: 16px; border: none; }
    .modal-header {
        background: var(--sanjay-primary);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 1rem 1.5rem;
    }
    .modal-header .btn-close { filter: brightness(0) invert(1); }
    .modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; }

    .rv-input-box {
        position: relative;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fafafa;
        transition: all 0.2s;
    }
    .rv-input-box:focus-within {
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
        background: white;
    }
    .rv-input-box.is-invalid { border-color: #dc2626; }
    .rv-input-box.is-valid { border-color: #16a34a; }
    .rv-input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.9rem;
        pointer-events: none;
    }
    .rv-input {
        width: 100%;
        padding: 0.6rem 0.8rem 0.6rem 2.4rem;
        border: none;
        background: transparent;
        outline: none;
        font-size: 0.85rem;
        color: #1f2937;
    }
    select.rv-input { appearance: none; padding-right: 2rem; cursor: pointer; }
    .form-label { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; }
    .form-label .required { color: #dc2626; margin-left: 2px; }
    .form-label .optional-tag {
        font-size: 0.65rem;
        font-weight: 500;
        color: #9ca3af;
        text-transform: none;
        margin-left: 4px;
    }
    .invalid-feedback { font-size: 0.75rem; color: #dc2626; margin-top: 0.25rem; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-state i { font-size: 4rem; color: #d1d5db; margin-bottom: 1rem; }

    .toast-container {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    }
    .toast-custom {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        border-left: 4px solid #10b981;
        margin-bottom: 0.75rem;
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .toast-custom.error { border-left-color: #dc2626; }
    .toast-custom .message { flex: 1; font-size: 0.85rem; color: #1f2937; }
    .toast-custom .close-btn {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0 0.25rem;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    @media (max-width: 576px) {
        .hostel-stats { grid-template-columns: repeat(3, 1fr); gap: 0.25rem; }
        .hostel-stat-item { padding: 0.25rem; }
        .hostel-stat-item .number { font-size: 0.85rem; }
        .biometric-stats { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Hostel Management</h1>
        <p class="ol-page-sub">Manage all hostels, biometric devices, and UPI payments</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="rv-submit" onclick="syncAllHostels()"
                style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#7c3aed;">
            <i class="bi bi-cloud-upload"></i> Sync All
        </button>
        <button type="button" class="rv-submit" id="addHostelBtn"
                style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i> Add Hostel
        </button>
    </div>
</div>

{{-- Hostels Grid --}}
<div id="hostelsContainer">
    @if($hostels->count() > 0)
        <div class="row g-4" id="hostelsGrid">
            @foreach($hostels as $hostel)
                <div class="col-xl-4 col-lg-6" data-id="{{ $hostel->id }}">
                    <div class="hostel-card">
                        <div class="hostel-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size:0.65rem; opacity:0.7; font-family: monospace;">{{ $hostel->hostel_code }}</div>
                                    <h5 style="margin: 4px 0 0 0; color: white; font-weight: 700;">{{ $hostel->hostel_name }}</h5>
                                </div>
                                <span class="hostel-type-badge {{ strtolower($hostel->hostel_type) }}">
                                    {{ $hostel->hostel_type == 'MEN' ? '👤 Men' : '👩 Women' }}
                                </span>
                            </div>
                        </div>
                        <div class="hostel-body">
                            @if($hostel->address)
                                <div style="font-size:0.75rem; color:#6b7280; margin-bottom:0.5rem;">
                                    <i class="bi bi-geo-alt me-1"></i> {{ Str::limit($hostel->address, 60) }}
                                </div>
                            @endif
                            <div class="hostel-stats">
                                <div class="hostel-stat-item">
                                    <div class="number">{{ $hostel->residents_count ?? 0 }}</div>
                                    <div class="label">Residents</div>
                                </div>
                                <div class="hostel-stat-item">
                                    <div class="number">{{ $hostel->rooms_count ?? 0 }}</div>
                                    <div class="label">Rooms</div>
                                </div>
                                <div class="hostel-stat-item">
                                    <div class="number">{{ $hostel->beds_count ?? 0 }}</div>
                                    <div class="label">Beds</div>
                                </div>
                            </div>

                            {{-- Biometric Section --}}
                            <div class="biometric-section">
                                <div class="biometric-header">
                                    <span class="title"><i class="bi bi-fingerprint"></i> Biometric Device</span>
                                    <span>
                                        <span class="biometric-status-dot {{
                                            $hostel->biometric_ip_address ? 'online' :
                                            ($hostel->biometric_device_id ? 'configuring' : 'not-configured')
                                        }}"></span>
                                        <span style="font-size:0.7rem; font-weight:500;">
                                            {{ $hostel->biometric_device_name ?? 'Not Configured' }}
                                        </span>
                                    </span>
                                </div>
                                @if($hostel->biometric_ip_address)
                                    <div style="font-size:0.65rem; color:#6b7280;">
                                        <i class="bi bi-wifi"></i> {{ $hostel->biometric_ip_address }}:{{ $hostel->biometric_port ?? '4370' }}
                                        @if($hostel->biometric_location_code)
                                            <span class="ms-2">📍 {{ $hostel->biometric_location_code }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="biometric-stats">
                                    <div class="biometric-stat-item">
                                        <div class="number" style="color: #3b82f6;">{{ $hostel->biometric_residents_count ?? 0 }}</div>
                                        <div class="label">Synced</div>
                                    </div>
                                    <div class="biometric-stat-item">
                                        <div class="number" style="color: #22c55e;">{{ $hostel->biometric_access_count ?? 0 }}</div>
                                        <div class="label">Access Enabled</div>
                                    </div>
                                </div>
                            </div>

                            {{-- UPI Section --}}
                            <div class="upi-section">
                                <div class="upi-header">
                                    <span class="title"><i class="bi bi-phone"></i> UPI Payment</span>
                                    <span>
                                        <span class="upi-status-dot {{ $hostel->upi_id ? 'configured' : 'not-configured' }}"></span>
                                        <span style="font-size:0.7rem; font-weight:500;">
                                            {{ $hostel->upi_id ? 'Configured' : 'Not Configured' }}
                                        </span>
                                    </span>
                                </div>
                                @if($hostel->upi_id)
                                    <div style="font-size:0.65rem; color:#6b7280;">
                                        <i class="bi bi-upc-scan"></i>
                                        <span class="upi-id-display">{{ $hostel->upi_id }}</span>
                                        <span class="ms-2">👤 {{ $hostel->upi_payee_name }}</span>
                                    </div>
                                @else
                                    <div style="font-size:0.65rem; color:#6b7280;">
                                        <i class="bi bi-exclamation-circle"></i> No UPI ID configured
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <button class="status-badge {{ strtolower($hostel->status) }}" onclick="toggleStatus({{ $hostel->id }}, '{{ $hostel->status }}')">
                                    <span class="dot"></span>
                                    {{ $hostel->status }}
                                </button>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button class="btn-action text-info" onclick="openBiometricConfig({{ $hostel->id }})" title="Configure Biometric">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <button class="btn-action text-success" onclick="syncHostel({{ $hostel->id }})" title="Sync Biometric">
                                        <i class="bi bi-cloud-upload"></i>
                                    </button>
                                    <button class="btn-action text-primary" onclick="editHostel({{ $hostel->id }})" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action text-danger" onclick="deleteHostel({{ $hostel->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="ds-card">
            <div class="empty-state">
                <i class="bi bi-building"></i>
                <h5>No hostels found</h5>
                <p class="text-muted">Get started by creating your first hostel.</p>
                <button type="button" class="rv-submit" onclick="openAddModal()" style="width:auto; display:inline-flex; padding:0 1.5rem; height:38px; border-radius:9px; align-items:center; gap:6px; animation:none;">
                    <i class="bi bi-plus-circle"></i> Add Hostel
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Hostel Modal --}}
<div class="modal fade" id="hostelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Hostel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="hostelForm">
                @csrf
                <input type="hidden" id="editId" name="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Basic Details -->
                        <div class="col-md-6">
                            <label class="form-label">Hostel Code <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <input type="text" name="hostel_code" id="hostel_code" class="rv-input" placeholder="e.g., SPG-M-001" required>
                            </div>
                            <div class="invalid-feedback" id="hostel_code_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hostel Name <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-building rv-input-icon"></i>
                                <input type="text" name="hostel_name" id="hostel_name" class="rv-input" placeholder="e.g., Sanjay PG - Men's" required>
                            </div>
                            <div class="invalid-feedback" id="hostel_name_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-gender-ambiguous rv-input-icon"></i>
                                <select name="hostel_type" id="hostel_type" class="rv-input" required>
                                    <option value="">Select</option>
                                    <option value="MEN">👤 Men's Hostel</option>
                                    <option value="WOMEN">👩 Women's Hostel</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="hostel_type_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-toggle-on rv-input-icon"></i>
                                <select name="status" id="status" class="rv-input" required>
                                    <option value="ACTIVE">✅ Active</option>
                                    <option value="INACTIVE">❌ Inactive</option>
                                </select>
                            </div>
                            <div class="invalid-feedback" id="status_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <div class="rv-input-box">
                                <i class="bi bi-geo-alt rv-input-icon" style="top: 16px; transform: none;"></i>
                                <textarea name="address" id="address" class="rv-input" placeholder="Enter complete address" style="min-height: 60px;"></textarea>
                            </div>
                            <div class="invalid-feedback" id="address_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <div class="rv-input-box">
                                <i class="bi bi-phone rv-input-icon"></i>
                                <input type="text" name="phone" id="phone" class="rv-input" placeholder="+91 98765 43210">
                            </div>
                            <div class="invalid-feedback" id="phone_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <div class="rv-input-box">
                                <i class="bi bi-envelope rv-input-icon"></i>
                                <input type="email" name="email" id="email" class="rv-input" placeholder="hostel@domain.com">
                            </div>
                            <div class="invalid-feedback" id="email_error"></div>
                        </div>

                        {{-- UPI Configuration --}}
                        <div class="col-12">
                            <hr>
                            <h6><i class="bi bi-phone"></i> UPI Payment Configuration</h6>
                            <p class="text-muted small mb-0">Configure UPI for receiving rent payments directly. Leave blank if not accepting UPI payments yet.</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">UPI ID <span class="optional-tag">(optional)</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-upc-scan rv-input-icon"></i>
                                <input type="text" name="upi_id" id="upi_id" class="rv-input"
                                       placeholder="merchant@upi" value="{{ old('upi_id') }}"
                                       pattern="^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$"
                                       title="Enter a valid UPI ID, e.g. merchant@ybl" maxlength="100">
                            </div>
                            <div class="invalid-feedback" id="upi_id_error"></div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Examples: merchant@ybl (Google Pay), merchant@paytm (PhonePe/Paytm)
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">UPI Payee Name <span class="optional-tag">(optional)</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-person rv-input-icon"></i>
                                <input type="text" name="upi_payee_name" id="upi_payee_name" class="rv-input"
                                       placeholder="Defaults to Hostel Name" value="{{ old('upi_payee_name') }}" maxlength="255">
                            </div>
                            <div class="invalid-feedback" id="upi_payee_name_error"></div>
                            <small class="text-muted">Name shown in the payer's UPI app. Auto-fills from Hostel Name unless you type your own.</small>
                        </div>

                        {{-- Biometric Configuration --}}
                        <div class="col-12">
                            <hr>
                            <h6><i class="bi bi-fingerprint"></i> Biometric Configuration</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Device ID</label>
                            <div class="rv-input-box">
                                <i class="bi bi-hdd-stack rv-input-icon"></i>
                                <input type="text" name="biometric_device_id" id="biometric_device_id" class="rv-input" placeholder="e.g., DEV_001">
                            </div>
                            <div class="invalid-feedback" id="biometric_device_id_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Device Name</label>
                            <div class="rv-input-box">
                                <i class="bi bi-device-ssd rv-input-icon"></i>
                                <input type="text" name="biometric_device_name" id="biometric_device_name" class="rv-input" placeholder="e.g., Main Door Device">
                            </div>
                            <div class="invalid-feedback" id="biometric_device_name_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP Address</label>
                            <div class="rv-input-box">
                                <i class="bi bi-wifi rv-input-icon"></i>
                                <input type="text" name="biometric_ip_address" id="biometric_ip_address" class="rv-input" placeholder="192.168.1.100">
                            </div>
                            <div class="invalid-feedback" id="biometric_ip_address_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Port</label>
                            <div class="rv-input-box">
                                <i class="bi bi-plug rv-input-icon"></i>
                                <input type="text" name="biometric_port" id="biometric_port" class="rv-input" placeholder="4370" value="4370">
                            </div>
                            <div class="invalid-feedback" id="biometric_port_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location Code</label>
                            <div class="rv-input-box">
                                <i class="bi bi-geo-alt rv-input-icon"></i>
                                <input type="text" name="biometric_location_code" id="biometric_location_code" class="rv-input" placeholder="e.g., LOC_001">
                            </div>
                            <div class="invalid-feedback" id="biometric_location_code_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee Code Prefix</label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <input type="text" name="employee_code_prefix" id="employee_code_prefix" class="rv-input" placeholder="e.g., H1">
                            </div>
                            <div class="invalid-feedback" id="employee_code_prefix_error"></div>
                            <small class="text-muted">Example: H1-24-000001</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="saveBtn" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; display:inline-flex; align-items:center; gap:6px; animation:none;">
                        <i class="bi bi-check-circle"></i> <span id="saveBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Biometric Configuration Modal --}}
<div class="modal fade" id="biometricModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #7c3aed;">
                <h5 class="modal-title"><i class="bi bi-fingerprint"></i> Biometric Configuration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="biometricForm">
                @csrf
                <input type="hidden" id="biometricHostelId" name="hostel_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Device ID <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-hdd-stack rv-input-icon"></i>
                                <input type="text" name="biometric_device_id" id="biometric_device_id_edit" class="rv-input" placeholder="e.g., DEV_001" required>
                            </div>
                            <div class="invalid-feedback" id="biometric_device_id_edit_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Device Name</label>
                            <div class="rv-input-box">
                                <i class="bi bi-device-ssd rv-input-icon"></i>
                                <input type="text" name="biometric_device_name" id="biometric_device_name_edit" class="rv-input" placeholder="e.g., Main Door Device">
                            </div>
                            <div class="invalid-feedback" id="biometric_device_name_edit_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP Address <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-wifi rv-input-icon"></i>
                                <input type="text" name="biometric_ip_address" id="biometric_ip_address_edit" class="rv-input" placeholder="192.168.1.100" required>
                            </div>
                            <div class="invalid-feedback" id="biometric_ip_address_edit_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Port</label>
                            <div class="rv-input-box">
                                <i class="bi bi-plug rv-input-icon"></i>
                                <input type="text" name="biometric_port" id="biometric_port_edit" class="rv-input" placeholder="4370" value="4370">
                            </div>
                            <div class="invalid-feedback" id="biometric_port_edit_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location Code</label>
                            <div class="rv-input-box">
                                <i class="bi bi-geo-alt rv-input-icon"></i>
                                <input type="text" name="biometric_location_code" id="biometric_location_code_edit" class="rv-input" placeholder="e.g., LOC_001">
                            </div>
                            <div class="invalid-feedback" id="biometric_location_code_edit_error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee Code Prefix</label>
                            <div class="rv-input-box">
                                <i class="bi bi-tag rv-input-icon"></i>
                                <input type="text" name="employee_code_prefix" id="employee_code_prefix_edit" class="rv-input" placeholder="e.g., H1">
                            </div>
                            <div class="invalid-feedback" id="employee_code_prefix_edit_error"></div>
                            <small class="text-muted">Example: H1-24-000001</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="biometricSaveBtn" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; display:inline-flex; align-items:center; gap:6px; animation:none; background:#7c3aed;">
                        <i class="bi bi-check-circle"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="flashMessageContainer"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // ✅ Initialize Modals
    var hostelModalEl = document.getElementById('hostelModal');
    var biometricModalEl = document.getElementById('biometricModal');

    var hostelModal = new bootstrap.Modal(hostelModalEl, {
        backdrop: 'static',
        keyboard: true
    });

    var biometricModal = new bootstrap.Modal(biometricModalEl, {
        backdrop: 'static',
        keyboard: true
    });

    // ✅ Store modal instances in global scope for access from functions
    window.hostelModal = hostelModal;
    window.biometricModal = biometricModal;

    // ✅ Add Hostel Button Click
    $('#addHostelBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    // ✅ Reset form when modal is hidden
    $('#hostelModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    // ✅ Form submission
    $('#hostelForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // ✅ Biometric form submission
    $('#biometricForm').on('submit', function(e) {
        e.preventDefault();
        submitBiometricForm();
    });

    // ✅ Auto-fill UPI payee name from hostel name
    $('#hostel_name').on('input', function() {
        var payeeField = $('#upi_payee_name');
        if (!payeeField.data('user-edited')) {
            payeeField.val($(this).val());
        }
    });

    $('#upi_payee_name').on('input', function() {
        $(this).data('user-edited', $(this).val().length > 0);
    });

    // ✅ Live UPI ID format feedback
    $('#upi_id').on('blur', function() {
        var val = $(this).val().trim();
        var box = $(this).closest('.rv-input-box');
        box.removeClass('is-invalid is-valid');
        $('#upi_id_error').text('');
        if (val.length === 0) return;
        var upiPattern = /^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/;
        if (!upiPattern.test(val)) {
            box.addClass('is-invalid');
            $('#upi_id_error').text('Enter a valid UPI ID, e.g. merchant@ybl');
        } else {
            box.addClass('is-valid');
        }
    });
});

// ============================================
// ✅ MODAL FUNCTIONS
// ============================================

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Hostel';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid is-valid');
    $('#upi_payee_name').data('user-edited', false);

    // ✅ Show modal using stored instance
    if (window.hostelModal) {
        window.hostelModal.show();
    } else {
        // Fallback: create new instance
        var modal = new bootstrap.Modal(document.getElementById('hostelModal'));
        modal.show();
        window.hostelModal = modal;
    }
}

function resetForm() {
    document.getElementById('hostelForm').reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid is-valid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Hostel';
    $('#upi_payee_name').data('user-edited', false);
}

// ============================================
// ✅ FORM SUBMISSION
// ============================================

function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.hostels.store') }}";
    let formData = new FormData(document.getElementById('hostelForm'));

    if (id) {
        url = "{{ url('admin/hostels') }}/" + id;
        formData.append('_method', 'PUT');
    }

    // ✅ Add CSRF token
    formData.append('_token', '{{ csrf_token() }}');

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#saveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid is-valid');
        },
        success: function(response) {
            if (response.success) {
                if (window.hostelModal) {
                    window.hostelModal.hide();
                }
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    var fieldElement = $('#' + field);
                    if (fieldElement.length) {
                        fieldElement.closest('.rv-input-box').addClass('is-invalid');
                        $('#' + field + '_error').text(messages[0]);
                    }
                });
                showToast('Please fix validation errors', 'error');
            } else {
                showToast(xhr.responseJSON?.message || 'Something went wrong!', 'error');
            }
        },
        complete: function() {
            let id = document.getElementById('editId').value;
            let text = id ? 'Update' : 'Save';
            $('#saveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> <span id="saveBtnText">' + text + '</span>');
        }
    });
}

// ============================================
// ✅ EDIT HOSTEL
// ============================================

function editHostel(id) {
    $.ajax({
        url: "{{ url('admin/hostels') }}/" + id + "/edit",
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Hostel';
                document.getElementById('editId').value = data.id;
                document.getElementById('hostel_code').value = data.hostel_code || '';
                document.getElementById('hostel_name').value = data.hostel_name || '';
                document.getElementById('hostel_type').value = data.hostel_type || '';
                document.getElementById('status').value = data.status || 'ACTIVE';
                document.getElementById('address').value = data.address || '';
                document.getElementById('phone').value = data.phone || '';
                document.getElementById('email').value = data.email || '';

                // UPI Fields
                document.getElementById('upi_id').value = data.upi_id || '';
                document.getElementById('upi_payee_name').value = data.upi_payee_name || '';
                $('#upi_payee_name').data('user-edited', !!(data.upi_payee_name && data.upi_payee_name !== data.hostel_name));

                // Biometric Fields
                document.getElementById('biometric_device_id').value = data.biometric_device_id || '';
                document.getElementById('biometric_device_name').value = data.biometric_device_name || '';
                document.getElementById('biometric_ip_address').value = data.biometric_ip_address || '';
                document.getElementById('biometric_port').value = data.biometric_port || '4370';
                document.getElementById('biometric_location_code').value = data.biometric_location_code || '';
                document.getElementById('employee_code_prefix').value = data.employee_code_prefix || '';

                document.getElementById('saveBtnText').textContent = 'Update';
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid is-valid');

                // ✅ Show modal
                if (window.hostelModal) {
                    window.hostelModal.show();
                } else {
                    var modal = new bootstrap.Modal(document.getElementById('hostelModal'));
                    modal.show();
                    window.hostelModal = modal;
                }
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            } else {
                showToast('Failed to load hostel data', 'error');
            }
        }
    });
}

// ============================================
// ✅ DELETE HOSTEL
// ============================================

function deleteHostel(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/hostels') }}/" + id,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to delete!', 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// ✅ TOGGLE STATUS
// ============================================

function toggleStatus(id, currentStatus) {
    let newStatus = currentStatus === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    let statusText = newStatus === 'ACTIVE' ? 'Active' : 'Inactive';

    Swal.fire({
        title: 'Toggle Status?',
        text: "Change hostel status to " + statusText + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--sanjay-gold)',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/hostels') }}/" + id + "/toggle-status",
                type: 'POST',
                data: {
                    _method: 'PATCH',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to update status!', 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// ✅ BIOMETRIC CONFIGURATION
// ============================================

function openBiometricConfig(id) {
    $('#biometricHostelId').val(id);
    $('#biometric_device_id_edit').val('');
    $('#biometric_device_name_edit').val('');
    $('#biometric_ip_address_edit').val('');
    $('#biometric_port_edit').val('4370');
    $('#biometric_location_code_edit').val('');
    $('#employee_code_prefix_edit').val('');
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid is-valid');

    $.ajax({
        url: '/admin/hostels/' + id + '/biometric-config',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#biometric_device_id_edit').val(data.biometric_device_id || '');
                $('#biometric_device_name_edit').val(data.biometric_device_name || '');
                $('#biometric_ip_address_edit').val(data.biometric_ip_address || '');
                $('#biometric_port_edit').val(data.biometric_port || '4370');
                $('#biometric_location_code_edit').val(data.biometric_location_code || '');
                $('#employee_code_prefix_edit').val(data.employee_code_prefix || 'H' + data.id);
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
            }
        }
    });

    // ✅ Show biometric modal
    if (window.biometricModal) {
        window.biometricModal.show();
    } else {
        var modal = new bootstrap.Modal(document.getElementById('biometricModal'));
        modal.show();
        window.biometricModal = modal;
    }
}

function submitBiometricForm() {
    const id = $('#biometricHostelId').val();
    const formData = new FormData(document.getElementById('biometricForm'));
    formData.append('_token', '{{ csrf_token() }}');

    $.ajax({
        url: '/admin/hostels/' + id + '/biometric-config',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#biometricSaveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid is-valid');
        },
        success: function(response) {
            if (response.success) {
                if (window.biometricModal) {
                    window.biometricModal.hide();
                }
                showToast('Biometric configuration saved successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $('#' + field).closest('.rv-input-box').addClass('is-invalid');
                    $('#' + field + '_error').text(messages[0]);
                });
                showToast('Please fix validation errors', 'error');
            } else {
                showToast(xhr.responseJSON?.message || 'Failed to save configuration!', 'error');
            }
        },
        complete: function() {
            $('#biometricSaveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Save Configuration');
        }
    });
}

// ============================================
// ✅ SYNC FUNCTIONS
// ============================================

function syncHostel(id) {
    Swal.fire({
        title: 'Sync Hostel Residents?',
        text: "This will sync all residents of this hostel to the biometric device.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, sync them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/hostels/' + id + '/sync-biometric',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast('Synced ' + response.synced + ' residents successfully!', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast(response.message || 'Failed to sync!', 'error');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to sync!', 'error');
                    }
                }
            });
        }
    });
}

function syncAllHostels() {
    Swal.fire({
        title: 'Sync All Hostels?',
        text: "This will sync all residents from all hostels to their respective biometric devices.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, sync all!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/hostels/sync-all-biometric',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        showToast('Synced ' + response.total_synced + ' residents from ' + response.hostels_synced + ' hostels!', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast(response.message || 'Failed to sync!', 'error');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        showToast(xhr.responseJSON?.message || 'Permission denied!', 'error');
                    } else {
                        showToast(xhr.responseJSON?.message || 'Failed to sync!', 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// ✅ TOAST NOTIFICATIONS
// ============================================

function showToast(message, type = 'success') {
    let container = document.getElementById('flashMessageContainer');
    if (!container) {
        const newContainer = document.createElement('div');
        newContainer.id = 'flashMessageContainer';
        newContainer.className = 'toast-container';
        document.body.appendChild(newContainer);
        container = newContainer;
    }

    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    const color = type === 'success' ? '#10b981' : '#dc2626';

    const toast = document.createElement('div');
    toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '');
    toast.innerHTML = `
        <i class="bi ${icon}" style="color: ${color}; font-size: 1.25rem;"></i>
        <div class="message">${message}</div>
        <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}
</script>

@endsection
