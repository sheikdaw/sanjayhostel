<!DOCTYPE html>
<html>
<head>
    <title>ESSL - Residents</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; color: #222; }
        h2 { margin-bottom: 20px; }

        .filter, .bulk-bar {
            background: #fff; padding: 15px; border: 1px solid #ddd;
            border-radius: 6px; margin-bottom: 15px;
        }
        .filter { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        select { padding: 9px 12px; min-width: 280px; border: 1px solid #bbb; border-radius: 4px; font-size: 14px; }
        .loading { display: none; color: #666; }

        .bulk-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn { border: 0; padding: 8px 16px; border-radius: 5px; font-weight: bold; cursor: pointer; color: #fff; font-size: 13px; }
        .btn.green { background: #16a34a; }
        .btn.red { background: #dc2626; }
        .btn.blue { background: #2563eb; }
        .btn.dark { background: #374151; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }

        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background: #f4f4f4; }
        tr:hover { background: #fafafa; }
        tr.selected { background: #eff6ff; }

        .status-btn { border: 0; padding: 7px 14px; border-radius: 5px; font-size: 12px; font-weight: bold; cursor: pointer; }
        .status-btn.unblocked { background: #dcfce7; color: #15803d; }
        .status-btn.blocked { background: #fee2e2; color: #dc2626; }
        .status-btn.syncing { background: #e5e7eb; color: #555; cursor: wait; }
        .sync-btn { border: 1px solid #2563eb; background: #fff; color: #2563eb; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: bold; cursor: pointer; }
        .sync-btn:disabled, .status-btn:disabled { opacity: .6; cursor: wait; }

        .empty { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 6px; }
        .total { margin-top: 15px; }
        .message { margin-left: 8px; font-size: 12px; }
        .success { color: #16a34a; }
        .error { color: #dc2626; }
        #bulkMessage { font-size: 13px; }
        .muted { color: #888; font-size: 12px; }
    </style>
</head>

<body>

<h2>Residents - ESSL Biometric Access</h2>

@if ($hostels->isEmpty())
    <div class="empty">No active hostels found.</div>
@else
    <div class="filter">
        <label for="hostel_id"><strong>Select Hostel:</strong></label>
        <select id="hostel_id">
            <option value="all">All Hostels</option>
            @foreach ($hostels as $hostel)
                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn dark" id="syncHostelBtn" disabled>Sync Whole Hostel</button>
        <span id="loading" class="loading">Loading...</span>
    </div>
@endif

<div class="bulk-bar">
    <strong>Selected: <span id="selectedCount">0</span></strong>
    <button type="button" class="btn green" id="bulkUnblock" disabled>Unblock Selected</button>
    <button type="button" class="btn red" id="bulkBlock" disabled>Block Selected</button>
    <button type="button" class="btn blue" id="bulkSync" disabled>Sync Selected</button>
    <span id="bulkMessage"></span>
</div>

<div id="residentTable"></div>

<script>
    const URL_GET         = "{{ route('admin.essl.get-residents') }}";
    const URL_SYNC        = "{{ route('admin.essl.resident.sync') }}";
    const URL_BULK_SYNC   = "{{ route('admin.essl.resident.bulk-sync') }}";
    const URL_HOSTEL_SYNC = "{{ route('admin.essl.hostel.sync') }}";
    const URL_BLOCK       = "{{ route('admin.essl.resident.block') }}";
    const URL_BULK_BLOCK  = "{{ route('admin.essl.resident.bulk-block') }}";

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    /* ───────── helpers ───────── */

    function esc(v) {
        if (v === null || v === undefined || v === '') return 'N/A';
        return $('<div>').text(v).html();
    }

    function isUnblocked(r) {
        return r.status === 'ACTIVE' && (r.biometric_access == true || r.biometric_access == 1);
    }

    function fmtDate(d) {
        return d ? esc(String(d).replace('T', ' ').substring(0, 16)) : '<span class="muted">Never</span>';
    }

    function setStatusBtn(id, unblocked) {
        $(`.status-btn[data-id="${id}"]`)
            .removeClass('blocked unblocked syncing')
            .addClass(unblocked ? 'unblocked' : 'blocked')
            .text(unblocked ? 'UNBLOCKED' : 'BLOCKED')
            .prop('disabled', false);
    }

    function setSyncTime(id, time) {
        if (time) {
            $(`tr[data-id="${id}"] .last-sync`).html(fmtDate(time));
        }
    }

    function flash(el, msg, ok) {
        el.parent().find('.message').remove();
        const span = $('<span class="message"></span>').addClass(ok ? 'success' : 'error').text(msg);
        el.parent().append(span);
        setTimeout(() => span.remove(), 5000);
    }

    function bulkMsg(msg, ok) {
        $('#bulkMessage').removeClass('success error').addClass(ok ? 'success' : 'error').text(msg);
        setTimeout(() => $('#bulkMessage').text(''), 8000);
    }

    function errMsg(xhr) {
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) return xhr.responseJSON.message;
        }
        return 'Something went wrong.';
    }

    /* ───────── load + render ───────── */

    function loadResidents(hostelId) {
        $('#loading').show();
        $('#syncHostelBtn').prop('disabled', hostelId === 'all');

        $.get(URL_GET, { hostel_id: hostelId })
            .done(function (res) {
                if (!res.success || !res.residents || res.residents.length === 0) {
                    $('#residentTable').html('<div class="empty">No residents found.</div>');
                    return;
                }
                renderTable(res.residents, res.total);
            })
            .fail(function (xhr) {
                console.error(xhr);
                $('#residentTable').html('<div class="empty error">Failed to load residents.</div>');
            })
            .always(function () {
                $('#loading').hide();
                updateSelectedCount();
            });
    }

    function renderTable(residents, total) {
        let html = `
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Employee Code</th>
                        <th>Hostel</th>
                        <th>Room</th>
                        <th>Bed</th>
                        <th>Last Sync</th>
                        <th>Sync</th>
                        <th>Biometric Access</th>
                    </tr>
                </thead>
                <tbody>`;

        residents.forEach(function (r, i) {
            const un = isUnblocked(r);
            html += `
                <tr data-id="${r.id}">
                    <td><input type="checkbox" class="row-check" value="${r.id}"></td>
                    <td>${i + 1}</td>
                    <td>${esc(r.name)}</td>
                    <td>${esc(r.employee_code)}</td>
                    <td>${esc(r.hostel ? r.hostel.hostel_name : null)}</td>
                    <td>${esc(r.room ? r.room.room_no : null)}</td>
                    <td>${esc(r.bed ? r.bed.bed_no : null)}</td>
                    <td class="last-sync">${fmtDate(r.last_sync_at)}</td>
                    <td><button type="button" class="sync-btn" data-id="${r.id}">Sync</button></td>
                    <td>
                        <button type="button" class="status-btn ${un ? 'unblocked' : 'blocked'}" data-id="${r.id}">
                            ${un ? 'UNBLOCKED' : 'BLOCKED'}
                        </button>
                    </td>
                </tr>`;
        });

        html += `
                </tbody>
            </table>
            <div class="total"><strong>Total:</strong> ${total}</div>`;

        $('#residentTable').html(html);
    }

    $('#hostel_id').on('change', function () {
        loadResidents(this.value);
    });

    /* ───────── selection ───────── */

    function selectedIds() {
        return $('.row-check:checked').map(function () { return this.value; }).get();
    }

    function updateSelectedCount() {
        const ids   = selectedIds();
        const total = $('.row-check').length;

        $('#selectedCount').text(ids.length);
        $('#bulkBlock, #bulkUnblock, #bulkSync').prop('disabled', ids.length === 0);
        $('#selectAll').prop('checked', total > 0 && ids.length === total);
    }

    $(document).on('change', '#selectAll', function () {
        $('.row-check').prop('checked', this.checked);
        $('#residentTable tbody tr').toggleClass('selected', this.checked);
        updateSelectedCount();
    });

    $(document).on('change', '.row-check', function () {
        $(this).closest('tr').toggleClass('selected', this.checked);
        updateSelectedCount();
    });

    function clearSelection() {
        $('.row-check, #selectAll').prop('checked', false);
        $('#residentTable tbody tr').removeClass('selected');
        updateSelectedCount();
    }

    /* ───────── apply server results to rows ───────── */

    function applyResults(results) {
        (results || []).forEach(function (x) {
            setStatusBtn(x.id, x.unblocked);
            setSyncTime(x.id, x.last_sync_at);
            $(`.sync-btn[data-id="${x.id}"]`).prop('disabled', false).text('Sync');
        });
    }

    function lockRows(ids) {
        ids.forEach(function (id) {
            $(`.status-btn[data-id="${id}"]`).prop('disabled', true).addClass('syncing').text('SYNCING...');
            $(`.sync-btn[data-id="${id}"]`).prop('disabled', true).text('...');
        });
    }

    function unlockRows(ids) {
        ids.forEach(function (id) {
            $(`.status-btn[data-id="${id}"]`).prop('disabled', false).removeClass('syncing');
            $(`.sync-btn[data-id="${id}"]`).prop('disabled', false).text('Sync');
        });
    }

    /* ───────── single block / unblock ───────── */

    $(document).on('click', '.status-btn', function () {
        const btn = $(this);
        if (btn.prop('disabled')) return;

        const id           = btn.data('id');
        const wasUnblocked = btn.hasClass('unblocked');
        const block        = wasUnblocked ? 1 : 0;   // unblocked ah irundha → block pannanum

        lockRows([id]);

        $.post(URL_BLOCK, { resident_id: id, block: block })
            .done(function (res) {
                if (res.success) {
                    setStatusBtn(id, res.unblocked);
                    setSyncTime(id, res.last_sync_at);
                } else {
                    setStatusBtn(id, wasUnblocked);
                }
                flash(btn, res.message, res.success);
            })
            .fail(function (xhr) {
                setStatusBtn(id, wasUnblocked);
                flash(btn, errMsg(xhr), false);
            })
            .always(function () {
                unlockRows([id]);
            });
    });

    /* ───────── single sync ───────── */

    $(document).on('click', '.sync-btn', function () {
        const btn = $(this);
        if (btn.prop('disabled')) return;

        const id = btn.data('id');
        btn.prop('disabled', true).text('...');

        $.post(URL_SYNC, { resident_id: id })
            .done(function (res) {
                setSyncTime(id, res.last_sync_at);
                flash(btn, res.message, res.success);
            })
            .fail(function (xhr) {
                flash(btn, errMsg(xhr), false);
            })
            .always(function () {
                btn.prop('disabled', false).text('Sync');
            });
    });

    /* ───────── bulk block / unblock ───────── */

    function bulkBlock(block) {
        const ids = selectedIds();
        if (!ids.length) return;

        if (!confirm(`${block ? 'Block' : 'Unblock'} ${ids.length} resident(s)?`)) return;

        $('#bulkBlock, #bulkUnblock, #bulkSync').prop('disabled', true);
        $('#bulkMessage').removeClass('success error').text('Processing...');
        lockRows(ids);

        $.post(URL_BULK_BLOCK, { resident_ids: ids, block: block ? 1 : 0 })
            .done(function (res) {
                applyResults(res.results);
                bulkMsg(res.message, res.success);
                clearSelection();
            })
            .fail(function (xhr) {
                unlockRows(ids);
                bulkMsg(errMsg(xhr), false);
                // Original state theriyaadhu → fresh load
                loadResidents($('#hostel_id').val() || 'all');
            })
            .always(function () {
                updateSelectedCount();
            });
    }

    $('#bulkBlock').on('click', () => bulkBlock(true));
    $('#bulkUnblock').on('click', () => bulkBlock(false));

    /* ───────── bulk sync ───────── */

    $('#bulkSync').on('click', function () {
        const ids = selectedIds();
        if (!ids.length) return;

        if (!confirm(`Sync ${ids.length} resident(s) to device?`)) return;

        $('#bulkBlock, #bulkUnblock, #bulkSync').prop('disabled', true);
        $('#bulkMessage').removeClass('success error').text('Syncing...');
        lockRows(ids);

        $.post(URL_BULK_SYNC, { resident_ids: ids })
            .done(function (res) {
                applyResults(res.results);
                bulkMsg(res.message, res.success);
                clearSelection();
            })
            .fail(function (xhr) {
                unlockRows(ids);
                bulkMsg(errMsg(xhr), false);
                loadResidents($('#hostel_id').val() || 'all');
            })
            .always(function () {
                updateSelectedCount();
            });
    });

    /* ───────── sync whole hostel ───────── */

    $('#syncHostelBtn').on('click', function () {
        const hostelId = $('#hostel_id').val();
        if (hostelId === 'all') return;

        if (!confirm('Sync all ACTIVE residents of this hostel?')) return;

        const btn = $(this).prop('disabled', true).text('Syncing...');

        $.post(URL_HOSTEL_SYNC, { hostel_id: hostelId })
            .done(function (res) {
                bulkMsg(res.message, res.success);
                loadResidents(hostelId);
            })
            .fail(function (xhr) {
                bulkMsg(errMsg(xhr), false);
            })
            .always(function () {
                btn.prop('disabled', $('#hostel_id').val() === 'all').text('Sync Whole Hostel');
            });
    });

    /* ───────── first load ───────── */

    loadResidents('all');
</script>

</body>
</html>
