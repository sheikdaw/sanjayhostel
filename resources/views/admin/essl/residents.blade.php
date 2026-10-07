<!DOCTYPE html>
<html>

<head>
    <title>ESSL - Residents</title>
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
        select { padding: 9px 12px; min-width: 280px; border: 1px solid #bbb;
                 border-radius: 4px; font-size: 14px; }
        .loading { display: none; color: #666; }

        .bulk-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn { border: 0; padding: 8px 16px; border-radius: 5px; font-weight: bold;
               cursor: pointer; color: #fff; font-size: 13px; }
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
        tr.inactive { background: #fafafa; color: #888; }

        .status-btn { border: 0; padding: 7px 14px; border-radius: 5px;
                      font-size: 12px; font-weight: bold; cursor: pointer; }
        .status-btn.unblocked { background: #dcfce7; color: #15803d; }
        .status-btn.blocked { background: #fee2e2; color: #dc2626; }
        .status-btn.syncing { background: #e5e7eb; color: #555; cursor: wait; }
        .status-btn.permanent { background: #e5e7eb; color: #6b7280; cursor: not-allowed; }

        .sync-btn { border: 1px solid #2563eb; background: #fff; color: #2563eb;
                    padding: 6px 12px; border-radius: 5px; font-size: 12px;
                    font-weight: bold; cursor: pointer; }
        .sync-btn:disabled, .status-btn:disabled { opacity: .6; cursor: wait; }
        .status-btn.permanent:disabled { opacity: 1; cursor: not-allowed; }

        .empty { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 6px; }
        .total { margin-top: 15px; }
        .success { color: #16a34a; }
        .error { color: #dc2626; }
        #bulkMessage { font-size: 13px; }
        .muted { color: #888; font-size: 12px; }

        .toast {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            padding: 12px 20px; border-radius: 6px; color: #fff;
            font-size: 13px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px; word-wrap: break-word;
        }
        .toast.success { background: #16a34a; }
        .toast.error { background: #dc2626; }
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
    /* ───────── URL constants ───────── */

    const URL_GET         = "{{ route('admin.essl.get-residents') }}";
    const URL_SYNC        = "{{ route('admin.essl.resident.sync') }}";
    const URL_BULK_SYNC   = "{{ route('admin.essl.resident.bulk-sync') }}";
    const URL_HOSTEL_SYNC = "{{ route('admin.essl.hostel.sync') }}";
    const URL_BLOCK       = "{{ route('admin.essl.resident.block') }}";
    const URL_BULK_BLOCK  = "{{ route('admin.essl.resident.bulk-block') }}";
    const URL_CMD_STATUS  = "{{ route('admin.essl.command-status') }}";
    const CSRF            = "{{ csrf_token() }}";

    const BULK_CHUNK      = 50;   // server max:100

    /* ───────── helpers ───────── */

    function esc(v) {
        if (v === null || v === undefined || v === '') return 'N/A';
        return $('<div>').text(v).html();
    }

    function isUnblocked(r) {
        return r.status === 'ACTIVE' && (r.biometric_access == true || r.biometric_access == 1);
    }

    function fmtDate(d) {
        return d ? esc(String(d).replace('T', ' ').substring(0, 16))
                 : '<span class="muted">Never</span>';
    }

    function setStatusBtn(id, unblocked) {
        $('.status-btn[data-id="' + id + '"]:not(.permanent)')
            .removeClass('blocked unblocked syncing')
            .addClass(unblocked ? 'unblocked' : 'blocked')
            .text(unblocked ? 'UNBLOCKED' : 'BLOCKED')
            .prop('disabled', false);
    }

    function setSyncTime(id, time) {
        if (time) {
            $('tr[data-id="' + id + '"] .last-sync').html(fmtDate(time));
        }
    }

    function toast(msg, ok) {
        var $t = $('<div class="toast"></div>')
            .addClass(ok ? 'success' : 'error')
            .text(msg)
            .appendTo('body');
        setTimeout(function () {
            $t.fadeOut(300, function () { $(this).remove(); });
        }, 4500);
    }

    function bulkMsg(msg, ok) {
        $('#bulkMessage').removeClass('success error')
            .addClass(ok ? 'success' : 'error').text(msg);
        setTimeout(function () { $('#bulkMessage').text(''); }, 8000);
    }

    function errMsg(xhr) {
        if (xhr.responseJSON && xhr.responseJSON.message) return xhr.responseJSON.message;
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            return Object.values(xhr.responseJSON.errors).flat().join(' | ');
        }
        return 'Something went wrong.';
    }

    /* ───────── chunk helper (FIX #4) ───────── */

    function chunkArray(arr, size) {
        var out = [];
        for (var i = 0; i < arr.length; i += size) {
            out.push(arr.slice(i, i + size));
        }
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
                .prop('disabled', true).addClass('syncing').text('SYNCING...');
            $('.sync-btn[data-id="' + id + '"]').prop('disabled', true).text('...');
        });
    }

    function unlockRows(ids) {
        ids.forEach(function (id) {
            $('.status-btn[data-id="' + id + '"]:not(.permanent)')
                .prop('disabled', false).removeClass('syncing');
            $('.sync-btn[data-id="' + id + '"]').prop('disabled', false).text('Sync');
        });
    }

    function applyResults(results) {
        (results || []).forEach(function (x) {
            setStatusBtn(x.id, x.unblocked);
            setSyncTime(x.id, x.last_sync_at);
            $('.sync-btn[data-id="' + x.id + '"]').prop('disabled', false).text('Sync');
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
                    $('#residentTable').html('<div class="empty">No residents found.</div>');
                    updateSelectedCount();
                    return;
                }

                renderTable(res.residents, res.total);
                updateSelectedCount();
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                $('#loading').hide();
                $('#residentTable').html('<div class="empty error">Failed to load residents.</div>');
                updateSelectedCount();
            }
        });
    }

    function renderTable(residents, total) {
        var html = '' +
            '<table>' +
                '<thead>' +
                    '<tr>' +
                        '<th><input type="checkbox" id="selectAll"></th>' +
                        '<th>#</th>' +
                        '<th>Name</th>' +
                        '<th>Employee Code</th>' +
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
                    '<button type="button" class="status-btn ' +
                        (isAllowed ? 'unblocked' : 'blocked') + '" data-id="' + r.id + '">' +
                        (isAllowed ? 'UNBLOCKED' : 'BLOCKED') +
                    '</button>';

                syncCell = '<button type="button" class="sync-btn" data-id="' + r.id + '">Sync</button>';
            } else {
                accessCell =
                    '<button type="button" class="status-btn permanent" disabled ' +
                        'title="Resident is ' + esc(r.status) + '">' +
                        'BLOCKED (' + esc(r.status) + ')' +
                    '</button>';
                syncCell = '<span class="muted">-</span>';
            }

            html +=
                '<tr data-id="' + r.id + '" class="' + (isActive ? '' : 'inactive') + '">' +
                    '<td><input type="checkbox" class="row-check" value="' + r.id + '" ' +
                        (isActive ? '' : 'disabled') + '></td>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + esc(r.name) + '</td>' +
                    '<td>' + esc(r.employee_code) + '</td>' +
                    '<td>' + esc(r.hostel ? r.hostel.hostel_name : null) + '</td>' +
                    '<td>' + esc(r.room ? r.room.room_no : null) + '</td>' +
                    '<td>' + esc(r.bed ? r.bed.bed_no : null) + '</td>' +
                    '<td class="last-sync">' + fmtDate(r.last_sync_at) + '</td>' +
                    '<td>' + syncCell + '</td>' +
                    '<td>' + accessCell + '</td>' +
                '</tr>';
        });

        html +=
                '</tbody>' +
            '</table>' +
            '<div class="total"><strong>Total:</strong> ' + total + '</div>';

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

    /* ───────── 3. SINGLE SYNC ───────── */

    $(document).on('click', '.sync-btn', function () {
        var btn = $(this);
        if (btn.prop('disabled')) return;

        var id = btn.data('id');
        btn.prop('disabled', true).text('...');

        $.ajax({
            url: URL_SYNC,
            type: "POST",
            data: { _token: CSRF, resident_id: id },
            success: function (res) {
                btn.prop('disabled', false).text('Sync');
                setSyncTime(id, res.last_sync_at);
                toast(res.message, res.success);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                btn.prop('disabled', false).text('Sync');
                toast(errMsg(xhr), false);
            }
        });
    });

    /* ───────── 4. BULK BLOCK / UNBLOCK (chunked) ───────── */

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
                loadResidents($('#hostel_id').val() || 'all');
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

    /* ───────── 5. BULK SYNC (chunked) ───────── */

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
                loadResidents($('#hostel_id').val() || 'all');
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
        btn.prop('disabled', true).text('Syncing...');

        $.ajax({
            url: URL_HOSTEL_SYNC,
            type: "POST",
            data: { _token: CSRF, hostel_id: hostelId },
            success: function (res) {
                btn.prop('disabled', false).text('Sync Whole Hostel');
                bulkMsg(res.message, res.success);
                loadResidents(hostelId);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                btn.prop('disabled', false).text('Sync Whole Hostel');
                bulkMsg(errMsg(xhr), false);
            }
        });
    });

    /* ───────── FIRST LOAD ───────── */

    loadResidents('all');
</script>

</body>

</html>
