{{-- resources/views/admin/advances/index.blade.php --}}
@extends('layouts.office')

@section('title', 'Advance Management')
@section('page_title', 'Advance Management')

@push('styles')
<style>
    .advance-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }
    .advance-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
    .advance-header {
        padding: 1rem 1.25rem;
        background: var(--sanjay-primary);
        color: white;
    }
    .advance-body { padding: 1rem 1.25rem; }
    .advance-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .advance-stat {
        text-align: center;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .advance-stat .number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .advance-stat .label {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: uppercase;
    }
    .advance-stat.total .number { color: var(--sanjay-primary); }
    .advance-stat.taken .number { color: #f59e0b; }
    .advance-stat.deducted .number { color: #22c55e; }
    .advance-stat.balance .number { color: #dc2626; }
    .filter-section {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    .filter-section select {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        font-size: 0.8rem;
        background: white;
    }
    .btn-action {
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: white;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
    .btn-action:hover { background: #f3f4f6; }
    .btn-action.text-primary:hover { background: #e3f2fd; border-color: #90caf9; }
    .btn-action.text-success:hover { background: #dcfce7; border-color: #86efac; }
    .btn-action.text-danger:hover { background: #fee2e2; border-color: #fca5a5; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .status-badge.active { background: #dcfce7; color: #166534; }
    .status-badge.inactive { background: #fee2e2; color: #991b1b; }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.active .dot { background: #22c55e; }
    .status-badge.inactive .dot { background: #ef4444; }
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
    .form-label { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; }
    .form-label .required { color: #dc2626; margin-left: 2px; }
    .invalid-feedback { font-size: 0.75rem; color: #dc2626; margin-top: 0.25rem; }
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
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Advance Management</h1>
        <p class="ol-page-sub">Manage employee advances and deductions</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.advances.monthly') }}" class="rv-submit" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-calendar"></i>
            Monthly Report
        </a>
        <button type="button" class="rv-submit" id="takeAdvanceBtn" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i>
            Take Advance
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="advance-stats">
    <div class="advance-stat total">
        <div class="number">₹{{ number_format($summary['total_advance'], 2) }}</div>
        <div class="label">Total Advance This Month</div>
    </div>
    <div class="advance-stat deducted">
        <div class="number">₹{{ number_format($summary['total_deduction'], 2) }}</div>
        <div class="label">Total Deduction This Month</div>
    </div>
    <div class="advance-stat balance">
        <div class="number">₹{{ number_format($summary['total_outstanding'], 2) }}</div>
        <div class="label">Total Outstanding Balance</div>
    </div>
</div>

{{-- Filter Section --}}
<div class="filter-section">
    <div class="filter-group">
        <label style="font-size:0.8rem; font-weight:600;">Month:</label>
        <input type="month" id="filterMonth" value="{{ $month }}" onchange="applyFilters()">
    </div>
    <div class="filter-group">
        <select id="filterEmployee" onchange="applyFilters()">
            <option value="">All Employees</option>
            @foreach($allEmployees as $emp)
                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->employee_code }} - {{ $emp->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <select id="filterStatus" onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <button class="btn-action" onclick="clearFilters()" style="padding:0.4rem 1rem;">
        <i class="bi bi-arrow-counterclockwise"></i> Clear
    </button>
</div>

{{-- Employees List --}}
<div class="ds-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Code</th>
                    <th>Hostel</th>
                    <th>Advance Taken</th>
                    <th>Deducted</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php
                        $monthlyAdvance = $employee->getMonthlyAdvanceTaken($month);
                        $monthlyDeduction = $employee->getMonthlyDeduction($month);
                    @endphp
                    <tr>
                        <td><strong>{{ $employee->name }}</strong></td>
                        <td>{{ $employee->employee_code }}</td>
                        <td>{{ $employee->hostel->hostel_name ?? 'N/A' }}</td>
                        <td class="text-warning">₹{{ number_format($monthlyAdvance, 2) }}</td>
                        <td class="text-success">₹{{ number_format($monthlyDeduction, 2) }}</td>
                        <td class="text-{{ $employee->advance_balance > 0 ? 'danger' : 'success' }}">
                            ₹{{ number_format($employee->advance_balance, 2) }}
                        </td>
                        <td>
                            <span class="status-badge {{ $employee->status }}">
                                <span class="dot"></span>
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.advances.history', $employee->id) }}" class="btn-action text-primary" title="View History">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            <button class="btn-action text-success" onclick="takeAdvance({{ $employee->id }})" title="Take Advance">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                            @if($employee->advance_balance > 0)
                                <button class="btn-action text-primary" onclick="deductAdvance({{ $employee->id }})" title="Deduct Advance">
                                    <i class="bi bi-dash-circle"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper">
        {{ $employees->links() }}
    </div>
</div>

{{-- Take Advance Modal --}}
<div class="modal fade" id="advanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="advanceModalTitle">Take Advance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="advanceForm">
                @csrf
                <input type="hidden" id="advance_employee_id" name="employee_id">
                <input type="hidden" id="advance_type" name="type" value="take">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Employee</label>
                            <div class="rv-input-box">
                                <i class="bi bi-person rv-input-icon"></i>
                                <input type="text" id="advance_employee_name" class="rv-input" readonly disabled>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amount <span class="required">*</span></label>
                            <div class="rv-input-box">
                                <i class="bi bi-currency-rupee rv-input-icon"></i>
                                <input type="number" step="0.01" name="amount" id="advance_amount" class="rv-input" placeholder="0.00" min="0.01" required>
                            </div>
                            <div class="invalid-feedback" id="advance_amount_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Month</label>
                            <div class="rv-input-box">
                                <i class="bi bi-calendar rv-input-icon"></i>
                                <input type="month" name="month" id="advance_month" class="rv-input" value="{{ $month }}">
                            </div>
                            <div class="invalid-feedback" id="advance_month_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <div class="rv-input-box">
                                <i class="bi bi-chat rv-input-icon"></i>
                                <textarea name="remarks" id="advance_remarks" class="rv-input" rows="2" placeholder="Reason for advance..."></textarea>
                            </div>
                            <div class="invalid-feedback" id="advance_remarks_error"></div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info" id="advance_balance_info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Current Outstanding Balance:</strong> 
                                <span id="current_balance">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rv-submit" id="advanceSaveBtn" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; display:inline-flex; align-items:center; gap:6px; animation:none;">
                        <i class="bi bi-check-circle"></i> Submit
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
    var advanceModal = new bootstrap.Modal(document.getElementById('advanceModal'), {
        backdrop: 'static',
        keyboard: true
    });

    $('#takeAdvanceBtn').on('click', function(e) {
        e.preventDefault();
        $('#advanceModalTitle').text('Take Advance');
        $('#advance_type').val('take');
        $('#advance_employee_id').val('');
        $('#advance_employee_name').val('');
        $('#advance_amount').val('');
        $('#advance_remarks').val('');
        $('#current_balance').text('₹0.00');
        $('#advanceForm')[0].reset();
        $('.invalid-feedback').text('');
        $('.rv-input-box').removeClass('is-invalid');
        advanceModal.show();
    });

    $('#advanceModal').on('hidden.bs.modal', function() {
        $('#advanceForm')[0].reset();
        $('.invalid-feedback').text('');
        $('.rv-input-box').removeClass('is-invalid');
    });

    $('#advanceForm').on('submit', function(e) {
        e.preventDefault();
        submitAdvance();
    });
});

function applyFilters() {
    var month = $('#filterMonth').val();
    var employee = $('#filterEmployee').val();
    var status = $('#filterStatus').val();
    
    var url = "{{ route('admin.advances.index') }}";
    var params = [];
    
    if (month) params.push('month=' + month);
    if (employee) params.push('employee_id=' + employee);
    if (status) params.push('status=' + status);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    window.location.href = url;
}

function clearFilters() {
    $('#filterMonth').val('');
    $('#filterEmployee').val('');
    $('#filterStatus').val('');
    applyFilters();
}

function takeAdvance(id) {
    $.ajax({
        url: "{{ url('admin/employees') }}/" + id,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                $('#advanceModalTitle').text('Take Advance - ' + data.name);
                $('#advance_type').val('take');
                $('#advance_employee_id').val(data.id);
                $('#advance_employee_name').val(data.name + ' (' + data.employee_code + ')');
                $('#advance_amount').val('');
                $('#advance_remarks').val('');
                $('#current_balance').text('₹' + Number(data.advance_balance).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('advanceModal'));
                modal.show();
            }
        },
        error: function() {
            showToast('Failed to load employee data', 'error');
        }
    });
}

function deductAdvance(id) {
    $.ajax({
        url: "{{ url('admin/employees') }}/" + id,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                $('#advanceModalTitle').text('Deduct Advance - ' + data.name);
                $('#advance_type').val('deduct');
                $('#advance_employee_id').val(data.id);
                $('#advance_employee_name').val(data.name + ' (' + data.employee_code + ')');
                $('#advance_amount').val('');
                $('#advance_remarks').val('');
                $('#current_balance').text('₹' + Number(data.advance_balance).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('.invalid-feedback').text('');
                $('.rv-input-box').removeClass('is-invalid');
                var modal = new bootstrap.Modal(document.getElementById('advanceModal'));
                modal.show();
            }
        },
        error: function() {
            showToast('Failed to load employee data', 'error');
        }
    });
}

function submitAdvance() {
    let type = $('#advance_type').val();
    let url = type === 'take' ? "{{ route('admin.advances.take') }}" : "{{ route('admin.advances.deduct') }}";
    let formData = new FormData(document.getElementById('advanceForm'));

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#advanceSaveBtn').prop('disabled', true).html('<i class="bi bi-spinner bi-spin"></i> Processing...');
            $('.invalid-feedback').text('');
            $('.rv-input-box').removeClass('is-invalid');
        },
        success: function(response) {
            if (response.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('advanceModal'));
                if (modal) modal.hide();
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                if (xhr.responseJSON.message) {
                    showToast(xhr.responseJSON.message, 'error');
                } else {
                    $.each(errors, function(field, messages) {
                        let fieldId = 'advance_' + field;
                        $('#' + fieldId).closest('.rv-input-box').addClass('is-invalid');
                        $('#' + fieldId + '_error').text(messages[0]);
                    });
                    showToast('Please fix validation errors', 'error');
                }
            } else {
                showToast(xhr.responseJSON?.message || 'Something went wrong!', 'error');
            }
        },
        complete: function() {
            $('#advanceSaveBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Submit');
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