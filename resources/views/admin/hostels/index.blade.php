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
    .btn-action { padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #e5e7eb; background: white; font-size: 0.75rem; }
    .btn-action:hover { background: #f3f4f6; }
    .btn-action.text-danger:hover { background: #fee2e2; border-color: #fca5a5; }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header {
        background: var(--sanjay-primary);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 1rem 1.5rem;
    }
    .modal-header .btn-close { filter: brightness(0) invert(1); }
    .modal-body { padding: 1.5rem; }
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
    .toast-custom .icon { font-size: 1.25rem; }
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
    .page-title-link {
        color: var(--sanjay-gold);
        text-decoration: none;
    }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Hostel Management</h1>
        <p class="ol-page-sub">Manage all hostels and their details</p>
    </div>
    <div>
        <button type="button" class="rv-submit" id="addHostelBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Add Hostel
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
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="status-badge {{ strtolower($hostel->status) }}" onclick="toggleStatus({{ $hostel->id }}, '{{ $hostel->status }}')">
                                    <span class="dot"></span>
                                    {{ $hostel->status }}
                                </button>
                                <div class="d-flex gap-1">
                                    <button class="btn-action" onclick="editHostel({{ $hostel->id }})" title="Edit">
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
                    <i class="bi bi-plus-circle"></i>
                    Add Hostel
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="hostelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="saveBtn" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; display:inline-flex; align-items:center; gap:6px; animation:none;">
                        <i class="bi bi-check-circle"></i>
                        <span id="saveBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="flashMessageContainer"></div>

<script>
$(document).ready(function() {
    var hostelModal = new bootstrap.Modal(document.getElementById('hostelModal'), {
        backdrop: 'static',
        keyboard: true
    });

    $('#addHostelBtn').on('click', function(e) {
        e.preventDefault();
        openAddModal();
    });

    $('#hostelModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    $('#hostelForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });
});

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Hostel';
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('hostelModal'));
    modal.show();
}

function resetForm() {
    const form = document.getElementById('hostelForm');
    form.reset();
    $('.invalid-feedback').text('');
    $('.rv-input-box').removeClass('is-invalid');
    document.getElementById('saveBtnText').textContent = 'Save';
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Hostel';
}

function submitForm() {
    let id = document.getElementById('editId').value;
    let url = "{{ route('admin.hostels.store') }}";
    let formData = new FormData(document.getElementById('hostelForm'));

    if (id) {
        url = "{{ url('admin/hostels') }}/" + id;
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#saveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Saving...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('hostelModal'));
                if (modal) modal.hide();
                showToast(response.message, 'success');
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

function editHostel(id) {
    $.ajax({
        url: "{{ url('admin/hostels') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                document.getElementById('modalTitle').textContent = 'Edit Hostel';
                document.getElementById('editId').value = data.id;
                document.getElementById('hostel_code').value = data.hostel_code;
                document.getElementById('hostel_name').value = data.hostel_name;
                document.getElementById('hostel_type').value = data.hostel_type;
                document.getElementById('status').value = data.status;
                document.getElementById('address').value = data.address || '';
                document.getElementById('phone').value = data.phone || '';
                document.getElementById('email').value = data.email || '';
                document.getElementById('saveBtnText').textContent = 'Update';
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('hostelModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            showToast('Failed to load hostel data', 'error');
        }
    });
}

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
                    showToast(xhr.responseJSON?.message || 'Failed to delete!', 'error');
                }
            });
        }
    });
}

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
                    showToast(xhr.responseJSON?.message || 'Failed to update status!', 'error');
                }
            });
        }
    });
}

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
