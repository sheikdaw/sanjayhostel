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
                Each resident is pushed to <strong>their hostel's</strong> biometric device.
            </p>
        </div>

        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="hostel_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Hostels</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}"
                            @selected($selectedHostel == $hostel->id)>
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
                <button type="button"
                        id="btn-sync-hostel"
                        data-hostel="{{ $selectedHostel }}"
                        class="btn btn-warning">
                    <i class="fas fa-cloud-upload-alt"></i> Sync Entire Hostel
                </button>
            @endif
        </div>
    </div>

    {{-- Hostel device status cards --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="text-uppercase text-muted small mb-2">Hostel Devices</h6>
            <div class="row g-2">
                @foreach($hostels as $hostel)
                    <div class="col-md-4">
                        <div class="border rounded p-2 small">
                            <div class="fw-semibold">{{ $hostel->hostel_name }}</div>
                            @if($hostel->biometric_device_id)
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> {{ $hostel->biometric_device_id }}
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times"></i> No device configured
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
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
                            <th style="width:60px;">#</th>
                            <th>Resident</th>
                            <th>Employee Code</th>
                            <th>Hostel / Room / Bed</th>
                            <th>Device</th>
                            <th>Access</th>
                            <th>Last Sync</th>
                            <th class="text-end" style="width:280px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($residents as $i => $resident)
                        <tr data-row="{{ $resident->id }}">
                            <td>{{ $i + 1 }}</td>

                            {{-- Resident --}}
                            <td>
                                <div class="fw-semibold">{{ $resident->name }}</div>
                                <div class="text-muted small">{{ $resident->resident_code }}</div>
                            </td>

                            {{-- Employee Code --}}
                            <td>
                                @if($resident->employee_code)
                                    <span class="badge bg-dark">{{ $resident->employee_code }}</span>
                                @else
                                    <span class="badge bg-secondary">No Code</span>
                                @endif
                            </td>

                            {{-- Hostel / Room / Bed --}}
                            <td class="small">
                                {{ $resident->hostel->hostel_name ?? '—' }}
                                @if($resident->room) / {{ $resident->room->room_no }} @endif
                                @if($resident->bed) / {{ $resident->bed->bed_no }} @endif
                            </td>

                            {{-- Device serial --}}
                            <td class="small">
                                @if($resident->hostel && $resident->hostel->biometric_device_id)
                                    <span class="badge bg-info">
                                        {{ $resident->hostel->biometric_device_id }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">No device</span>
                                @endif
                            </td>

                            {{-- Access status badge --}}
                            <td class="access-cell">
                                @if($resident->biometric_access)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-ban"></i> Blocked
                                    </span>
                                @endif
                            </td>

                            {{-- Last sync --}}
                            <td class="last-sync-cell small text-muted">
                                {{ $resident->last_sync_at
                                    ? $resident->last_sync_at->format('d M Y, h:i A')
                                    : 'Never' }}
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">
                                {{-- Add to Device --}}
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-sync"
                                        data-id="{{ $resident->id }}"
                                        @if(
                                            !$resident->employee_code ||
                                            !$resident->hostel ||
                                            !$resident->hostel->biometric_device_id
                                        ) disabled @endif>
                                    <i class="fas fa-fingerprint"></i> Add
                                </button>

                                {{-- Block / Unblock --}}
                                @if($resident->employee_code && $resident->hostel && $resident->hostel->biometric_device_id)
                                    @if($resident->biometric_access)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-block-toggle"
                                                data-id="{{ $resident->id }}"
                                                data-block="1"
                                                data-name="{{ $resident->name }}">
                                            <i class="fas fa-ban"></i> Block
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success btn-block-toggle"
                                                data-id="{{ $resident->id }}"
                                                data-block="0"
                                                data-name="{{ $resident->name }}">
                                            <i class="fas fa-unlock"></i> Unblock
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No residents found.
                            </td>
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
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
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
    const toast    = new bootstrap.Toast(toastEl, { delay: 3500 });

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
        return { ok: res.ok, data: await res.json().catch(() => ({})) };
    }

    /* ── Sync one resident ─────────────────────────── */
    document.querySelectorAll('.btn-sync').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            const original = this.innerHTML;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const { data } = await postJSON(
                    '{{ route("admin.essl.resident.sync") }}',
                    { resident_id: id }
                );

                if (data.success) {
                    this.innerHTML = '<i class="fas fa-check"></i> Synced';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');

                    const row = document.querySelector(`[data-row="${id}"]`);
                    if (row) {
                        row.querySelector('.last-sync-cell').textContent = data.synced_at;
                    }

                    showToast('✅ ' + data.message, true);
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

    /* ── Block / Unblock ──────────────────────────── */
    document.querySelectorAll('.btn-block-toggle').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id     = this.dataset.id;
            const block  = this.dataset.block === '1';
            const name   = this.dataset.name;
            const action = block ? 'Block' : 'Unblock';

            if (!confirm(`${action} "${name}" on the device?`)) return;

            const original = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const { data } = await postJSON(
                    '{{ route("admin.essl.resident.block") }}',
                    { resident_id: id, block: block }
                );

                if (data.success) {
                    showToast('✅ ' + data.message, true);

                    // Update badge
                    const row = document.querySelector(`[data-row="${id}"]`);
                    if (row) {
                        const cell = row.querySelector('.access-cell');
                        if (cell) {
                            cell.innerHTML = block
                                ? '<span class="badge bg-danger"><i class="fas fa-ban"></i> Blocked</span>'
                                : '<span class="badge bg-success"><i class="fas fa-check"></i> Active</span>';
                        }
                    }

                    // Reload to update button state cleanly
                    setTimeout(() => location.reload(), 1000);
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

    /* ── Sync entire hostel ───────────────────────── */
    const syncHostelBtn = document.getElementById('btn-sync-hostel');
    if (syncHostelBtn) {
        syncHostelBtn.addEventListener('click', async function () {
            const hostelId = this.dataset.hostel;
            const original = this.innerHTML;

            if (!confirm('Push ALL active residents of this hostel to its device?')) return;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';

            try {
                const { data } = await postJSON(
                    '{{ route("admin.essl.hostel.sync") }}',
                    { hostel_id: hostelId }
                );

                showToast((data.success ? '✅ ' : '⚠️ ') + data.message, data.success !== false);

                if ((data.synced || 0) > 0) {
                    setTimeout(() => location.reload(), 1500);
                }
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
