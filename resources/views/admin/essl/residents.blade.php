@extends('layouts.office')

@section('title', 'eSSL Device Sync')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-fingerprint text-primary"></i> eSSL Device Sync
            </h1>
            <p class="text-muted small mb-0">
                Only <strong>ACTIVE</strong> residents are synced. Block / Unblock only disables access on the device — nothing is ever deleted.
            </p>
        </div>

        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="hostel_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Hostels</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}" @selected($selectedHostel == $hostel->id)>
                            {{ $hostel->hostel_name }}
                            @if($hostel->biometric_device_id)
                                — {{ $hostel->biometric_device_id }}
                            @else
                                (No device)
                            @endif
                        </option>
                    @endforeach
                </select>
            </form>

            @if($selectedHostel)
                <button type="button" id="btn-sync-hostel" data-hostel="{{ $selectedHostel }}" class="btn btn-warning">
                    <i class="fas fa-cloud-upload-alt"></i> Sync Entire Hostel
                </button>
            @endif
        </div>
    </div>

    {{-- Hostel devices --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="text-uppercase text-muted small mb-2">Hostel Devices</h6>
            <div class="row g-2">
                @foreach($hostels as $hostel)
                    <div class="col-md-4">
                        <div class="border rounded p-2 small">
                            <div class="fw-semibold">{{ $hostel->hostel_name }}</div>
                            @if($hostel->biometric_device_id)
                                <span class="badge bg-success"><i class="fas fa-check"></i> {{ $hostel->biometric_device_id }}</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-times"></i> No device</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    <div id="bulk-bar" class="card border-0 shadow-sm mb-3 d-none"
         style="background: linear-gradient(90deg,#eef5ff,#f6fbff); border-left: 4px solid #0d6efd !important;">
        <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="fas fa-check-square text-primary"></i>
                <strong id="bulk-count">0</strong> resident(s) selected
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-primary" id="btn-bulk-sync">
                    <i class="fas fa-fingerprint"></i> Sync Selected
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="btn-bulk-block">
                    <i class="fas fa-ban"></i> Block Selected
                </button>
                <button type="button" class="btn btn-sm btn-success" id="btn-bulk-unblock">
                    <i class="fas fa-unlock"></i> Unblock Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-bulk-clear">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Residents table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="check-all" class="form-check-input"></th>
                            <th style="width:60px;">#</th>
                            <th>Resident</th>
                            <th>Employee Code</th>
                            <th>Status</th>
                            <th>Hostel / Room / Bed</th>
                            <th>Device</th>
                            <th>Access</th>
                            <th>Last Sync</th>
                            <th class="text-end" style="width:220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($residents as $i => $resident)
                        @php
                            $hasDevice = $resident->employee_code && $resident->hostel && $resident->hostel->biometric_device_id;
                        @endphp
                        <tr data-row="{{ $resident->id }}">
                            <td><input type="checkbox" class="form-check-input row-check" value="{{ $resident->id }}"></td>
                            <td>{{ $i + 1 }}</td>

                            <td>
                                <div class="fw-semibold">{{ $resident->name }}</div>
                                <div class="text-muted small">{{ $resident->resident_code }}</div>
                            </td>

                            <td>
                                @if($resident->employee_code)
                                    <span class="badge bg-dark">{{ $resident->employee_code }}</span>
                                @else
                                    <span class="badge bg-secondary">No Code</span>
                                @endif
                            </td>

                            <td>
                                @if($resident->status === 'ACTIVE')
                                    <span class="badge bg-success">ACTIVE</span>
                                @else
                                    <span class="badge bg-secondary">{{ $resident->status }}</span>
                                @endif
                            </td>

                            <td class="small">
                                {{ $resident->hostel->hostel_name ?? '—' }}
                                @if($resident->room) / {{ $resident->room->room_no }} @endif
                                @if($resident->bed) / {{ $resident->bed->bed_no }} @endif
                            </td>

                            <td class="small">
                                @if($resident->hostel && $resident->hostel->biometric_device_id)
                                    <span class="badge bg-info">{{ $resident->hostel->biometric_device_id }}</span>
                                @else
                                    <span class="badge bg-danger">No device</span>
                                @endif
                            </td>

                            <td class="access-cell">
                                @if($resident->biometric_access)
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-ban"></i> Blocked</span>
                                @endif
                            </td>

                            <td class="last-sync-cell small text-muted">
                                {{ $resident->last_sync_at ? $resident->last_sync_at->format('d M Y, h:i A') : 'Never' }}
                            </td>

                            <td class="text-end">
                                @if($hasDevice)
                                    {{-- Add: ACTIVE residents only --}}
                                    @if($resident->status === 'ACTIVE')
                                        <button type="button" class="btn btn-sm btn-primary btn-sync" data-id="{{ $resident->id }}">
                                            <i class="fas fa-fingerprint"></i> Add
                                        </button>
                                    @endif

                                    {{-- Block: any status. Unblock: ACTIVE only. Never deletes. --}}
                                    @if($resident->biometric_access)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-block-toggle"
                                                data-id="{{ $resident->id }}" data-block="1" data-name="{{ $resident->name }}">
                                            <i class="fas fa-ban"></i> Block
                                        </button>
                                    @elseif($resident->status === 'ACTIVE')
                                        <button type="button" class="btn btn-sm btn-outline-success btn-block-toggle"
                                                data-id="{{ $resident->id }}" data-block="0" data-name="{{ $resident->name }}">
                                            <i class="fas fa-unlock"></i> Unblock
                                        </button>
                                    @else
                                        <span class="badge bg-secondary">Blocked / Not ACTIVE</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">No code / device</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No residents found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <div id="appToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="appToastMsg">Done</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const toastEl  = document.getElementById('appToast');
    const toastMsg = document.getElementById('appToastMsg');
    const toast    = new bootstrap.Toast(toastEl, { delay: 4500 });

    function showToast(message, success = true) {
        toastEl.classList.remove('bg-success', 'bg-danger');
        toastEl.classList.add(success ? 'bg-success' : 'bg-danger');
        toastMsg.textContent = message;
        toast.show();
    }

    async function postJSON(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok && !data.message) {
            data.message = data.errors ? Object.values(data.errors).flat().join(' ') : ('HTTP ' + res.status);
            data.success = false;
        }
        return { ok: res.ok, data };
    }

    /* ── Select All / Row checkboxes ── */
    const checkAll  = document.getElementById('check-all');
    const bulkBar   = document.getElementById('bulk-bar');
    const bulkCount = document.getElementById('bulk-count');
    const rowChecks = () => document.querySelectorAll('.row-check');

    function selectedIds() {
        return Array.from(rowChecks()).filter(c => c.checked).map(c => parseInt(c.value, 10));
    }

    function refreshBulkBar() {
        const ids = selectedIds();
        bulkCount.textContent = ids.length;
        bulkBar.classList.toggle('d-none', ids.length === 0);
        if (checkAll) {
            checkAll.indeterminate = ids.length > 0 && ids.length < rowChecks().length;
            checkAll.checked       = ids.length > 0 && ids.length === rowChecks().length;
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks().forEach(c => c.checked = this.checked);
            refreshBulkBar();
        });
    }
    document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', refreshBulkBar));

    document.getElementById('btn-bulk-clear').addEventListener('click', () => {
        rowChecks().forEach(c => c.checked = false);
        if (checkAll) checkAll.checked = false;
        refreshBulkBar();
    });

    /* ── Single: Sync ── */
    document.querySelectorAll('.btn-sync').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            const original = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const { data } = await postJSON('{{ route("admin.essl.resident.sync") }}', { resident_id: id });
                if (data.success) {
                    showToast('✅ ' + data.message, true);
                    setTimeout(() => location.reload(), 900);
                } else {
                    this.disabled = false;
                    this.innerHTML = original;
                    showToast('❌ ' + (data.message || 'Failed'), false);
                }
            } catch (err) {
                this.disabled = false;
                this.innerHTML = original;
                showToast('❌ Network error: ' + err.message, false);
            }
        });
    });

    /* ── Single: Block / Unblock ── */
    document.querySelectorAll('.btn-block-toggle').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id     = this.dataset.id;
            const block  = this.dataset.block === '1';
            const name   = this.dataset.name;
            const action = block ? 'Block' : 'Unblock';

            if (!confirm(`${action} "${name}"?`)) return;

            const original = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const { data } = await postJSON('{{ route("admin.essl.resident.block") }}', { resident_id: id, block: block });
                if (data.success) {
                    showToast('✅ ' + data.message, true);
                    setTimeout(() => location.reload(), 900);
                } else {
                    this.disabled = false;
                    this.innerHTML = original;
                    showToast('❌ ' + (data.message || 'Failed'), false);
                }
            } catch (err) {
                this.disabled = false;
                this.innerHTML = original;
                showToast('❌ Network error: ' + err.message, false);
            }
        });
    });

    /* ── Bulk helper ── */
    function bulkAction(btnId, label, busyText, url, extra) {
        document.getElementById(btnId).addEventListener('click', async function () {
            const ids = selectedIds();
            if (ids.length === 0) return;
            if (!confirm(`${label} ${ids.length} resident(s)?`)) return;

            const original = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + busyText;

            try {
                const { data } = await postJSON(url, Object.assign({ resident_ids: ids }, extra));
                showToast((data.success ? '✅ ' : '⚠️ ') + data.message, data.success !== false);
                setTimeout(() => location.reload(), 1800);
            } catch (err) {
                this.disabled = false;
                this.innerHTML = original;
                showToast('❌ ' + err.message, false);
            }
        });
    }

    bulkAction('btn-bulk-sync',    'Sync',    'Syncing...',    '{{ route("admin.essl.resident.bulk-sync") }}',  {});
    bulkAction('btn-bulk-block',   'Block',   'Blocking...',   '{{ route("admin.essl.resident.bulk-block") }}', { block: true });
    bulkAction('btn-bulk-unblock', 'Unblock', 'Unblocking...', '{{ route("admin.essl.resident.bulk-block") }}', { block: false });

    /* ── Sync entire hostel ── */
    const syncHostelBtn = document.getElementById('btn-sync-hostel');
    if (syncHostelBtn) {
        syncHostelBtn.addEventListener('click', async function () {
            const hostelId = this.dataset.hostel;
            const original = this.innerHTML;
            if (!confirm('Sync all ACTIVE residents of this hostel?')) return;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';

            try {
                const { data } = await postJSON('{{ route("admin.essl.hostel.sync") }}', { hostel_id: hostelId });
                showToast((data.success ? '✅ ' : '⚠️ ') + data.message, data.success !== false);
                if ((data.synced || 0) > 0) setTimeout(() => location.reload(), 1800);
            } catch (err) {
                showToast('❌ ' + err.message, false);
            } finally {
                this.disabled = false;
                this.innerHTML = original;
            }
        });
    }
})();
</script>
@endpush
