{{-- resources/views/admin/attendances/index.blade.php --}}
@extends('layouts.office')

@section('title', 'Attendance Management')
@section('page_title', 'Attendance Management')

@push('styles')
<style>
    .filter-section {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: flex-end;
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    .filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
    .filter-section label { font-size: 0.75rem; font-weight: 600; color: #374151; }
    .filter-section select,
    .filter-section input {
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
    .btn-action.text-danger:hover { background: #fee2e2; border-color: #fca5a5; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-badge.present { background: #dcfce7; color: #166534; }
    .status-badge.present .dot { background: #22c55e; }
    .status-badge.absent { background: #fee2e2; color: #991b1b; }
    .status-badge.absent .dot { background: #ef4444; }
    .status-badge.leave { background: #fef3c7; color: #92400e; }
    .status-badge.leave .dot { background: #f59e0b; }
    .status-badge.half_day { background: #fed7aa; color: #9a3412; }
    .status-badge.half_day .dot { background: #fb923c; }
    .status-badge.holiday { background: #ede9fe; color: #5b21b6; }
    .status-badge.holiday .dot { background: #8b5cf6; }
    .status-badge.weekly_off { background: #e5e7eb; color: #374151; }
    .status-badge.weekly_off .dot { background: #6b7280; }
    .pagination-wrapper { padding: 1rem; display: flex; justify-content: center; }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header {
        background: var(--sanjay-primary);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 1rem 1.5rem;
    }
    .modal-header .btn-close { filter: brightness(0) invert(1); }
    .modal-body { padding: 1.5rem; max-height: 60vh; overflow-y: auto; }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; }
    .employee-check-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 0.4rem;
    }
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
    .toast-custom .close-btn { background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0 0.25rem; }
    @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
</style>
@endpush

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Attendance Management</h1>
        <p class="ol-page-sub">Track and manage daily employee attendance</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attendances.report') }}" class="rv-submit" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-bar-chart"></i> Report
        </a>
        <button type="button" class="rv-submit" data-bs-toggle="modal" data-bs-target="#bulkMarkModal" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-people"></i> Bulk Mark
        </button>
        <a href="{{ route('admin.attendances.create') }}" class="rv-submit" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none;">
            <i class="bi bi-plus-circle"></i> Mark Attendance
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Section --}}
<form method="GET" action="{{ route('admin.attendances.index') }}" class="filter-section">
    <div class="filter-group">
        <label>Date</label>
        <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}">
    </div>
    <div class="filter-group">
        <label>Hostel</label>
        <select name="hostel_id">
            <option value="">All Hostels</option>
            @foreach($hostels as $hostel)
                <option value="{{ $hostel->id }}" {{ request('hostel_id') == $hostel->id ? 'selected' : '' }}>
                    {{ $hostel->hostel_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label>Status</label>
        <select name="status">
            <option value="">All Status</option>
            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
            <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
            <option value="holiday" {{ request('status') == 'holiday' ? 'selected' : '' }}>Holiday</option>
            <option value="weekly_off" {{ request('status') == 'weekly_off' ? 'selected' : '' }}>Weekly Off</option>
        </select>
    </div>
    <div class="filter-group">
        <label>Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or code...">
    </div>
    <div class="filter-group">
        <button type="submit" class="btn-action" style="padding:0.45rem 1rem;">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </div>
    <div class="filter-group">
        <a href="{{ route('admin.attendances.index') }}" class="btn-action" style="padding:0.45rem 1rem; text-decoration:none; display:inline-block;">
            <i class="bi bi-arrow-counterclockwise"></i> Clear
        </a>
    </div>
</form>

{{-- Attendance Table --}}
<div class="ds-card">
    <div class="d-flex justify-content-between align-items-center px-3 pt-3">
        <button class="btn-action text-danger" id="bulkDeleteBtn" style="display:none;" onclick="bulkDeleteAttendances()">
            <i class="bi bi-trash"></i> Delete Selected
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                    <th>Employee</th>
                    <th>Code</th>
                    <th>Hostel</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Hours</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td><input type="checkbox" class="row-check" value="{{ $attendance->id }}" onchange="toggleBulkDeleteBtn()"></td>
                        <td><strong>{{ $attendance->employee->name ?? 'N/A' }}</strong></td>
                        <td>{{ $attendance->employee->employee_code ?? '-' }}</td>
                        <td>{{ $attendance->employee->hostel->hostel_name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</td>
                        <td>
                            <span class="status-badge {{ $attendance->status }}">
                                <span class="dot"></span>
                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            </span>
                        </td>
                        <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : '-' }}</td>
                        <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : '-' }}</td>
                        <td>{{ $attendance->working_hours ?? '-' }}</td>
                        <td>{{ $attendance->remarks ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.attendances.edit', $attendance->id) }}" class="btn-action text-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.attendances.destroy', $attendance->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this attendance record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action text-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">No attendance records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper">
        {{ $attendances->links() }}
    </div>
</div>

{{-- Bulk Mark Attendance Modal --}}
<div class="modal fade" id="bulkMarkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.attendances.bulk-mark') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="leave">Leave</option>
                                <option value="half_day">Half Day</option>
                                <option value="holiday">Holiday</option>
                                <option value="weekly_off">Weekly Off</option>
                            </select>
                        </div>
                    </div>
                    <label class="form-label">Select Employees <span class="required">*</span></label>
                    <div class="mb-2">
                        <input type="checkbox" id="checkAllEmployees" onclick="toggleAllEmployees(this)">
                        <label for="checkAllEmployees" style="font-size:0.8rem;">Select All</label>
                    </div>
                    @foreach($employees as $employee)
                        <div class="employee-check-row">
                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="employee-checkbox">
                            <span>{{ $employee->name }} ({{ $employee->employee_code }})</span>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action" data-bs-dismiss="modal" style="padding:0.5rem 1.2rem;">Cancel</button>
                    <button type="submit" class="rv-submit" style="width:auto; padding:0 1.5rem; height:38px; border-radius:9px; animation:none;">
                        <i class="bi bi-check-circle"></i> Mark Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

@endsection

@push('scripts')
<script>
function toggleSelectAll(source) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = source.checked);
    toggleBulkDeleteBtn();
}

function toggleAllEmployees(source) {
    document.querySelectorAll('.employee-checkbox').forEach(cb => cb.checked = source.checked);
}

function toggleBulkDeleteBtn() {
    var checked = document.querySelectorAll('.row-check:checked').length;
    document.getElementById('bulkDeleteBtn').style.display = checked > 0 ? 'inline-flex' : 'none';
}

function bulkDeleteAttendances() {
    var ids = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
    if (ids.length === 0) return;
    if (!confirm('Delete ' + ids.length + ' selected attendance record(s)?')) return;

    fetch("{{ route('admin.attendances.bulk-delete') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    })
    .catch(() => showToast('Something went wrong!', 'error'));
}

function showToast(message, type = 'success') {
    var container = document.getElementById('toastContainer');
    var toast = document.createElement('div');
    toast.className = 'toast-custom' + (type === 'error' ? ' error' : '');
    toast.innerHTML = '<i class="bi bi-' + (type === 'error' ? 'exclamation-circle text-danger' : 'check-circle text-success') + '"></i>' +
        '<div class="message">' + message + '</div>' +
        '<button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>';
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
</script>
@endpush