@extends('layouts.office')

@section('title', 'ESSL — Biometric Access')
@section('page_title', 'Biometric Access')

@push('styles')
<style>
    .es-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .es-page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .es-page-title i { color: var(--sanjay-gold); }
    .es-page-subtitle { font-size: 0.8rem; color: #6b7280; margin: 0.25rem 0 0 0; }

    .es-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .es-filter-select {
        padding: 0.55rem 2rem 0.55rem 0.85rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        color: #374151;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        min-width: 240px;
    }
    .es-filter-select:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }

    .es-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.6rem 1.15rem;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .es-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .es-btn-dark {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
    }
    .es-btn-dark:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(10, 30, 63, 0.25);
        color: white;
    }
    .es-btn-gold {
        background: linear-gradient(135deg, var(--sanjay-gold), #d4af37);
        color: var(--sanjay-primary);
    }
    .es-btn-gold:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(197, 160, 40, 0.35);
    }
    .es-btn-outline { background: white; color: #6b7280; border: 1px solid #e5e7eb; }
    .es-btn-outline:hover:not(:disabled) { background: #f9fafb; color: #374151; }
    .es-btn-green { background: #10b981; color: white; }
    .es-btn-green:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
    .es-btn-red { background: #ef4444; color: white; }
    .es-btn-red:hover:not(:disabled) { background: #dc2626; transform: translateY(-1px); }
    .es-btn-blue { background: #3b82f6; color: white; }
    .es-btn-blue:hover:not(:disabled) { background: #2563eb; transform: translateY(-1px); }

    /* Bulk bar */
    .es-bulk-bar {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .es-bulk-count {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        background: #fffbeb;
        border: 1px solid rgba(197, 160, 40, 0.3);
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        font-family: 'DM Mono', monospace;
    }
    .es-bulk-msg {
        margin-left: auto;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .es-bulk-msg.success { color: #059669; }
    .es-bulk-msg.error   { color: #dc2626; }

    /* Table card */
    .es-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .es-table-wrap { overflow-x: auto; }
    .es-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }
    .es-table thead th {
        background: #0a1e3f;
        color: white;
        padding: 10px 12px;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
    }
    .es-table thead th.es-check-col {
        width: 42px;
        text-align: center;
    }
    .es-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        color: #374151;
    }
    .es-table tbody tr { transition: background 0.15s; }
    .es-table tbody tr:hover { background: #fffbeb; }
    .es-table tbody tr.selected { background: #eff6ff; }
    .es-table tbody tr.inactive {
        background: #fafafa;
        color: #9ca3af;
    }
    .es-table tbody tr.inactive td { color: #9ca3af; }
    .es-table .es-name {
        font-weight: 600;
        color: var(--sanjay-primary);
    }
    .es-table .es-code {
        font-family: 'DM Mono', monospace;
        font-size: 0.75rem;
        color: #6b7280;
        background: #f9fafb;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .es-table .es-room {
        font-weight: 600;
        color: var(--sanjay-gold);
    }
    .es-table .es-muted { color: #9ca3af; font-size: 0.72rem; }

    /* Checkbox */
    .es-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: var(--sanjay-gold);
    }
    .es-checkbox:disabled { cursor: not-allowed; opacity: 0.4; }

    /* Status pills */
    .es-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        text-transform: uppercase;
        font-family: inherit;
    }
    .es-status:disabled { cursor: wait; opacity: 0.65; }
    .es-status.unblocked { background: #dcfce7; color: #166534; border-color: #86efac; }
    .es-status.unblocked:hover:not(:disabled) { background: #bbf7d0; }
    .es-status.blocked { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .es-status.blocked:hover:not(:disabled) { background: #fecaca; }
    .es-status.syncing { background: #f3f4f6; color: #4b5563; border-color: #e5e7eb; }
    .es-status.permanent {
        background: #f3f4f6;
        color: #6b7280;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }
    .es-status-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    /* Sync button */
    .es-sync-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #3b82f6;
        background: white;
        color: #2563eb;
        font-size: 0.7rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .es-sync-btn:hover:not(:disabled) { background: #eff6ff; }
    .es-sync-btn:disabled { opacity: 0.55; cursor: wait; }

    /* Empty / loading */
    .es-empty {
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border-radius: 14px;
        border: 2px dashed #e5e7eb;
    }
    .es-empty-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(197, 160, 40, 0.1), rgba(10, 30, 63, 0.05));
        color: var(--sanjay-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1rem;
    }
    .es-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .es-empty p { color: #6b7280; font-size: 0.85rem; margin: 0; }
    .es-empty.error { border-color: #fecaca; }
    .es-empty.error .es-empty-icon { background: #fef2f2; color: #dc2626; }

    /* Total row */
    .es-total {
        padding: 0.75rem 1rem;
        background: #fafbfc;
        border-top: 1px solid #f3f4f6;
        font-size: 0.78rem;
        color: #6b7280;
    }
    .es-total strong { color: var(--sanjay-primary); }

    /* Spinner */
    .es-loading {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.78rem;
        color: #9ca3af;
    }
    .es-spinner {
        width: 14px;
        height: 14px;
        border: 2px solid #e5e7eb;
        border-top-color: var(--sanjay-gold);
        border-radius: 50%;
        animation: es-spin 0.6s linear infinite;
        display: inline-block;
    }
    @keyframes es-spin { to { transform: rotate(360deg); } }

    /* Toast */
    .es-toast {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 2147483647;
        padding: 14px 20px;
        border-radius: 12px;
        color: white;
        font-size: 0.82rem;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        max-width: 480px;
        word-wrap: break-word;
        line-height: 1.4;
        animation: es-slide-in 0.25s ease-out;
    }
    .es-toast.success { background: linear-gradient(135deg, #10b981, #059669); }
    .es-toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
    @keyframes es-slide-in {
        from { transform: translateX(30px); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }

    @media (max-width: 768px) {
        .es-toolbar { flex-direction: column; align-items: stretch; }
        .es-filter-select { width: 100%; }
        .es-bulk-bar { flex-direction: column; align-items: stretch; }
        .es-bulk-msg { margin-left: 0; }
    }
</style>
@endpush

@section('content')

<div class="es-page-header">
    <div>
        <h2 class="es-page-title">
            <i class="bi bi-fingerprint"></i>
            Biometric Access Management
        </h2>
        <p class="es-page-subtitle">Sync residents to eSSL devices • Block / unblock biometric entry</p>
    </div>
</div>

@if ($hostels->isEmpty())
    <div class="es-empty">
        <div class="es-empty-icon"><i class="bi bi-building"></i></div>
        <h5>No Active Hostels</h5>
        <p>Add a hostel to manage biometric access.</p>
    </div>
@else

    {{-- Toolbar --}}
    <div class="es-toolbar">
        <select class="es-filter-select" id="hostel_id">
            <option value="all">All Hostels</option>
            @foreach ($hostels as $hostel)
                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
            @endforeach
        </select>

        <button type="button" class="es-btn es-btn-dark" id="syncHostelBtn" disabled>
            <i class="bi bi-arrow-repeat"></i> Sync Whole Hostel
        </button>

        <span id="loading" class="es-loading" style="display:none;">
            <span class="es-spinner"></span> Loading residents...
        </span>
    </div>

    {{-- Bulk actions --}}
    <div class="es-bulk-bar">
        <span class="es-bulk-count">
            <i class="bi bi-check2-square"></i>
            <span id="selectedCount">0</span> selected
        </span>

        <button type="button" class="es-btn es-btn-green" id="bulkUnblock" disabled>
            <i class="bi bi-unlock"></i> Unblock Selected
        </button>
        <button type="button" class="es-btn es-btn-red" id="bulkBlock" disabled>
            <i class="bi bi-lock"></i> Block Selected
        </button>
        <button type="button" class="es-btn es-btn-blue" id="bulkSync" disabled>
            <i class="bi bi-arrow-repeat"></i> Sync Selected
        </button>

        <span id="bulkMessage" class="es-bulk-msg"></span>
    </div>

    <div id="residentTable"></div>

@endif

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    /* ───────── URL constants ───────── */

    const URL_GET         = "{{ route('admin.essl.get-residents') }}";
    const URL_SYNC        = "{{ route('admin.essl.resident.sync') }}";
    const URL_BULK_SYNC   = "{{ route('admin.essl.resident.bulk-sync') }}";
    const URL_HOSTEL_SYNC = "{{ route('admin.essl.hostel.sync') }}";
    const URL_BLOCK       = "{{ route('admin.essl.resident.block') }}";
    const URL_BULK_BLOCK  = "{{ route('admin.essl.resident.bulk-block') }}";
    const URL_CMD_STATUS  = "{{ route('admin.essl.command-status') }}";
    const CSRF            = "{{ csrf_token() }}";

    const BULK_CHUNK      = 50;

    /* ───────── helpers ───────── */

    function esc(v) {
        if (v === null || v === undefined || v === '') return 'N/A';
        return $('<div>').text(v).html();
    }

    function isUnblocked(r) {
        return r.status === 'ACTIVE' && (r.biometric_access == true || r.biometric_access == 1);
    }

    function fmtDate(d) {
        return d
            ? esc(String(d).replace('T', ' ').substring(0, 16))
            : '<span class="es-muted">Never</span>';
    }

    function setStatusBtn(id, unblocked) {
        $('.status-btn[data-id="' + id + '"]:not(.permanent)')
            .removeClass('blocked unblocked syncing')
            .addClass(unblocked ? 'unblocked' : 'blocked')
            .html('<span class="es-status-dot"></span>' + (unblocked ? 'UNBLOCKED' : 'BLOCKED'))
            .prop('disabled', false);
    }

    function setSyncTime(id, time) {
        if (time) {
            $('tr[data-id="' + id + '"] .last-sync').html(fmtDate(time));
        }
    }

    // ✅ FIXED: Toast always visible with max z-index, longer duration
    function toast(msg, ok) {
        $('.es-toast').remove();

        var $t = $('<div class="es-toast"></div>')
            .addClass(ok ? 'success' : 'error')
            .text(msg || 'No message returned')
            .css({
                'position': 'fixed',
                'top': '24px',
                'right': '24px',
                'z-index': 2147483647
            })
            .appendTo('body');

        setTimeout(function () {
            $t.fadeOut(400, function () { $(this).remove(); });
        }, 6000);
    }

    function bulkMsg(msg, ok) {
        $('#bulkMessage')
            .removeClass('success error')
            .addClass(ok ? 'success' : 'error')
            .text(msg);
        setTimeout(function () { $('#bulkMessage').text(''); }, 8000);
    }

    function errMsg(xhr) {
        if (xhr.responseJSON && xhr.responseJSON.message) return xhr.responseJSON.message;
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            return Object.values(xhr.responseJSON.errors).flat().join(' | ');
        }
        return 'Something went wrong.';
    }

    function chunkArray(arr, size) {
        var out = [];
        for (var i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
        return out;
    }

    /* ───────── selection ───────── */

    function selectedIds() {
        return $('.row-check:checked:not(:disabled)').map(function () {
            return this.value;
        }).get();
    }

    function updateSelectedCount() {
        var ids   = selectedIds();
        var total = $('.row-check:not(:disabled)').length;

        $('#selectedCount').text(ids.length);
        $('#bulkBlock, #bulkUnblock, #bulkSync').prop('disabled', ids.length === 0);
        $('#selectAll').prop('checked', total > 0 && ids.length === total);
    }

    function clearSelection() {
        $('.row-check, #selectAll').prop('checked', false);
        $('#residentTable tbody tr').removeClass('selected');
        updateSelectedCount();
    }

    $(document).on('change', '#selectAll', function () {
        $('.row-check:not(:disabled)').prop('checked', this.checked);
        $('#residentTable tbody tr:not(.inactive)').toggleClass('selected', this.checked);
        updateSelectedCount();
    });

    $(document).on('change', '.row-check', function () {
        $(this).closest('tr').toggleClass('selected', this.checked);
        updateSelectedCount();
    });

    /* ───────── lock / unlock rows ───────── */

    function lockRows(ids) {
        ids.forEach(function (id) {
            $('.status-btn[data-id="' + id + '"]:not(.permanent)')
                .prop('disabled', true).addClass('syncing')
                .html('<span class="es-spinner" style="border-top-color:#6b7280;"></span> SYNCING');
            $('.sync-btn[data-id="' + id + '"]').prop('disabled', true).text('...');
        });
    }

    function unlockRows(ids) {
        ids.forEach(function (id) {
            $('.status-btn[data-id="' + id + '"]:not(.permanent)')
                .prop('disabled', false).removeClass('syncing');
            $('.sync-btn[data-id="' + id + '"]').prop('disabled', false)
                .html('<i class="bi bi-arrow-repeat"></i> Sync');
        });
    }

    // ✅ FIXED: null-safe apply
    function applyResults(results) {
        (results || []).forEach(function (x) {
            if (typeof x.unblocked !== 'undefined') {
                setStatusBtn(x.id, x.unblocked);
            }
            setSyncTime(x.id, x.last_sync_at);
            $('.sync-btn[data-id="' + x.id + '"]')
                .prop('disabled', false)
                .html('<i class="bi bi-arrow-repeat"></i> Sync');
        });
    }

    /* ───────── 1. LOAD RESIDENTS ───────── */

    function loadResidents(hostelId) {
        $('#loading').show();
        $('#syncHostelBtn').prop('disabled', hostelId === 'all');

        $.ajax({
            url: URL_GET,
            type: "GET",
            data: { hostel_id: hostelId },
            success: function (res) {
                $('#loading').hide();

                if (!res.success || !res.residents || res.residents.length === 0) {
                    $('#residentTable').html(
                        '<div class="es-empty">' +
                            '<div class="es-empty-icon"><i class="bi bi-people"></i></div>' +
                            '<h5>No Residents Found</h5>' +
                            '<p>Try a different hostel or add residents.</p>' +
                        '</div>'
                    );
                    updateSelectedCount();
                    return;
                }

                renderTable(res.residents, res.total);
                updateSelectedCount();
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                $('#loading').hide();
                $('#residentTable').html(
                    '<div class="es-empty error">' +
                        '<div class="es-empty-icon"><i class="bi bi-exclamation-triangle"></i></div>' +
                        '<h5>Failed to Load</h5>' +
                        '<p>Please try again.</p>' +
                    '</div>'
                );
                updateSelectedCount();
            }
        });
    }

    function renderTable(residents, total) {
        var html = '' +
            '<div class="es-card">' +
            '<div class="es-table-wrap">' +
            '<table class="es-table">' +
                '<thead>' +
                    '<tr>' +
                        '<th class="es-check-col"><input type="checkbox" id="selectAll" class="es-checkbox"></th>' +
                        '<th>#</th>' +
                        '<th>Name</th>' +
                        '<th>Emp. Code</th>' +
                        '<th>Hostel</th>' +
                        '<th>Room</th>' +
                        '<th>Bed</th>' +
                        '<th>Last Sync</th>' +
                        '<th>Sync</th>' +
                        '<th>Biometric Access</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>';

        residents.forEach(function (r, i) {
            var isActive  = r.status === 'ACTIVE';
            var isAllowed = isUnblocked(r);
            var accessCell, syncCell;

            if (isActive) {
                accessCell =
                    '<button type="button" class="es-status status-btn ' +
                        (isAllowed ? 'unblocked' : 'blocked') + '" data-id="' + r.id + '">' +
                        '<span class="es-status-dot"></span>' +
                        (isAllowed ? 'UNBLOCKED' : 'BLOCKED') +
                    '</button>';

                syncCell = '<button type="button" class="es-sync-btn sync-btn" data-id="' + r.id + '">' +
                                '<i class="bi bi-arrow-repeat"></i> Sync' +
                           '</button>';
            } else {
                accessCell =
                    '<button type="button" class="es-status permanent" disabled ' +
                        'title="Resident is ' + esc(r.status) + '">' +
                        '<i class="bi bi-lock-fill"></i> BLOCKED (' + esc(r.status) + ')' +
                    '</button>';
                syncCell = '<span class="es-muted">—</span>';
            }

            html +=
                '<tr data-id="' + r.id + '" class="' + (isActive ? '' : 'inactive') + '">' +
                    '<td class="es-check-col">' +
                        '<input type="checkbox" class="row-check es-checkbox" value="' + r.id + '" ' +
                            (isActive ? '' : 'disabled') + '>' +
                    '</td>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td class="es-name">' + esc(r.name) + '</td>' +
                    '<td><span class="es-code">' + esc(r.employee_code) + '</span></td>' +
                    '<td>' + esc(r.hostel ? r.hostel.hostel_name : null) + '</td>' +
                    '<td class="es-room">' + esc(r.room ? r.room.room_no : null) + '</td>' +
                    '<td class="es-room">' + esc(r.bed ? r.bed.bed_no : null) + '</td>' +
                    '<td class="last-sync">' + fmtDate(r.last_sync_at) + '</td>' +
                    '<td>' + syncCell + '</td>' +
                    '<td>' + accessCell + '</td>' +
                '</tr>';
        });

        html +=
                '</tbody>' +
            '</table>' +
            '</div>' +
            '<div class="es-total"><strong>Total:</strong> ' + total + ' resident(s)</div>' +
            '</div>';

        $('#residentTable').html(html);
    }

    $('#hostel_id').on('change', function () {
        loadResidents(this.value);
    });

    /* ───────── 2. SINGLE BLOCK / UNBLOCK ───────── */

    $(document).on('click', '.status-btn:not(.permanent)', function () {
        var btn = $(this);
        if (btn.prop('disabled')) return;

        var id           = btn.data('id');
        var wasUnblocked = btn.hasClass('unblocked');
        var block        = wasUnblocked ? 1 : 0;

        lockRows([id]);

        $.ajax({
            url: URL_BLOCK,
            type: "POST",
            data: { _token: CSRF, resident_id: id, block: block },
            success: function (res) {
                unlockRows([id]);
                if (res.success) {
                    setStatusBtn(id, res.unblocked);
                    setSyncTime(id, res.last_sync_at);
                } else {
                    setStatusBtn(id, wasUnblocked);
                }
                toast(res.message, res.success);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                unlockRows([id]);
                setStatusBtn(id, wasUnblocked);
                toast(errMsg(xhr), false);
            }
        });
    });

    /* ───────── 3. SINGLE SYNC (FIXED: updates status pill too) ───────── */

    $(document).on('click', '.sync-btn', function () {
        var btn = $(this);
        if (btn.prop('disabled')) return;

        var id = btn.data('id');
        btn.prop('disabled', true).html(
            '<span class="es-spinner" style="border-color:#2563eb;border-top-color:transparent;width:10px;height:10px;"></span>'
        );

        $.ajax({
            url: URL_SYNC,
            type: "POST",
            data: { _token: CSRF, resident_id: id },
            success: function (res) {
                btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Sync');

                // ✅ Update the status pill (BLOCKED / UNBLOCKED)
                if (typeof res.unblocked !== 'undefined') {
                    setStatusBtn(id, res.unblocked);
                }

                setSyncTime(id, res.last_sync_at);
                toast(res.message, res.success);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Sync');
                toast(errMsg(xhr), false);
            }
        });
    });

    /* ───────── 4. BULK BLOCK / UNBLOCK ───────── */

    function bulkBlock(block) {
        var ids = selectedIds();
        if (!ids.length) return;

        if (!confirm((block ? 'Block ' : 'Unblock ') + ids.length + ' resident(s)?')) return;

        var chunks = chunkArray(ids, BULK_CHUNK);
        var done   = 0;
        var failed = 0;
        var total  = ids.length;

        $('#bulkBlock, #bulkUnblock, #bulkSync').prop('disabled', true);
        $('#bulkMessage').removeClass('success error').text('Processing 0/' + total + '...');
        lockRows(ids);

        function runNext() {
            if (!chunks.length) {
                bulkMsg(
                    (failed === 0 ? 'All ' : (total - failed) + '/') + total +
                    (block ? ' blocked.' : ' unblocked.') +
                    (failed ? ' Failed: ' + failed : ''),
                    failed === 0
                );
                clearSelection();
                // ✅ Delayed refresh — keeps toasts visible
                setTimeout(function () {
                    loadResidents($('#hostel_id').val() || 'all');
                }, 1200);
                return;
            }

            var chunk = chunks.shift();

            $.ajax({
                url: URL_BULK_BLOCK,
                type: "POST",
                data: { _token: CSRF, resident_ids: chunk, block: block ? 1 : 0 },
                success: function (res) {
                    applyResults(res.results);
                    done += chunk.length;
                    $('#bulkMessage').text('Processing ' + done + '/' + total + '...');
                    runNext();
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    unlockRows(chunk);
                    failed += chunk.length;
                    done   += chunk.length;
                    runNext();
                }
            });
        }

        runNext();
    }

    $('#bulkBlock').on('click', function () { bulkBlock(true); });
    $('#bulkUnblock').on('click', function () { bulkBlock(false); });

    /* ───────── 5. BULK SYNC ───────── */

    $('#bulkSync').on('click', function () {
        var ids = selectedIds();
        if (!ids.length) return;

        if (!confirm('Sync ' + ids.length + ' resident(s) to device?')) return;

        var chunks = chunkArray(ids, BULK_CHUNK);
        var done   = 0;
        var failed = 0;
        var total  = ids.length;

        $('#bulkBlock, #bulkUnblock, #bulkSync').prop('disabled', true);
        $('#bulkMessage').removeClass('success error').text('Syncing 0/' + total + '...');
        lockRows(ids);

        function runNext() {
            if (!chunks.length) {
                bulkMsg(
                    (failed === 0 ? 'All ' : (total - failed) + '/') + total +
                    ' synced.' + (failed ? ' Failed: ' + failed : ''),
                    failed === 0
                );
                clearSelection();
                // ✅ Delayed refresh
                setTimeout(function () {
                    loadResidents($('#hostel_id').val() || 'all');
                }, 1200);
                return;
            }

            var chunk = chunks.shift();

            $.ajax({
                url: URL_BULK_SYNC,
                type: "POST",
                data: { _token: CSRF, resident_ids: chunk },
                success: function (res) {
                    applyResults(res.results);
                    done += chunk.length;
                    $('#bulkMessage').text('Syncing ' + done + '/' + total + '...');
                    runNext();
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    unlockRows(chunk);
                    failed += chunk.length;
                    done   += chunk.length;
                    runNext();
                }
            });
        }

        runNext();
    });

    /* ───────── 6. SYNC WHOLE HOSTEL ───────── */

    $('#syncHostelBtn').on('click', function () {
        var hostelId = $('#hostel_id').val();
        if (hostelId === 'all') return;

        if (!confirm('Sync all ACTIVE residents of this hostel?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html(
            '<span class="es-spinner" style="border-top-color:#fff;"></span> Syncing...'
        );

        $.ajax({
            url: URL_HOSTEL_SYNC,
            type: "POST",
            data: { _token: CSRF, hostel_id: hostelId },
            success: function (res) {
                btn.prop('disabled', false).html(
                    '<i class="bi bi-arrow-repeat"></i> Sync Whole Hostel'
                );
                bulkMsg(res.message, res.success);

                // ✅ Delayed refresh
                setTimeout(function () {
                    loadResidents(hostelId);
                }, 1200);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                btn.prop('disabled', false).html(
                    '<i class="bi bi-arrow-repeat"></i> Sync Whole Hostel'
                );
                bulkMsg(errMsg(xhr), false);
            }
        });
    });

    /* ───────── FIRST LOAD ───────── */

    loadResidents('all');
</script>
@endpush
