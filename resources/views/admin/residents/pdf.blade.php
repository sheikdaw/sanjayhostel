<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Residents Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1f2937;
            padding: 12px;
        }
        .header {
            border-bottom: 2px solid #0a1e3f;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            color: #0a1e3f;
            margin-bottom: 2px;
        }
        .header .sub {
            font-size: 9px;
            color: #6b7280;
        }
        .filters {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 8.5px;
        }
        .filters span {
            display: inline-block;
            margin-right: 14px;
        }
        .filters strong { color: #0a1e3f; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead th {
            background: #0a1e3f;
            color: #fff;
            padding: 5px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        tbody td {
            padding: 4px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .status-active  { color: #059669; font-weight: 700; }
        .status-vacated { color: #dc2626; font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .footer {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Residents Report</h1>
        <div class="sub">Generated on {{ $generated }} • Total: {{ $residents->count() }} resident(s)</div>
    </div>

    <div class="filters">
        <span><strong>Status:</strong> {{ ucfirst($filters['status'] ?? 'all') }}</span>
        @if(!empty($filters['hostel']))
            <span><strong>Hostel:</strong> {{ $filters['hostel'] }}</span>
        @endif
        @if(!empty($filters['food_status']))
            <span><strong>Food:</strong> {{ $filters['food_status'] === 'WITH_FOOD' ? 'With Food' : 'Without Food' }}</span>
        @endif
        @if(!empty($filters['search']))
            <span><strong>Search:</strong> "{{ $filters['search'] }}"</span>
        @endif
        <span><strong>Order:</strong> Room No ↑</span>
    </div>

    @if($residents->isEmpty())
        <div class="empty">No residents match the selected filters.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:28px;">#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Hostel</th>
                    <th class="text-center">Room</th>
                    <th class="text-center">Bed</th>
                    <th>Food</th>
                    <th class="text-right">Rent</th>
                    <th class="text-right">Deposit</th>
                    <th>Joining</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($residents as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->resident_code }}</td>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->phone }}</td>
                    <td>{{ $r->hostel->hostel_name ?? '—' }}</td>
                    <td class="text-center">{{ $r->room->room_no ?? '—' }}</td>
                    <td class="text-center">{{ $r->bed->bed_no ?? '—' }}</td>
                    <td>{{ $r->food_status === 'WITH_FOOD' ? 'With' : 'Without' }}</td>
                    <td class="text-right">Rs.{{ number_format($r->rent_amount, 0) }}</td>
                    <td class="text-right">Rs.{{ number_format($r->deposit_amount, 0) }}</td>
                    <td>{{ $r->joining_date ? $r->joining_date->format('d M Y') : '—' }}</td>
                    <td class="text-center status-{{ strtolower($r->status) }}">
                        {{ ucfirst(strtolower($r->status)) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Sanjay PG Hostel — Confidential Report
    </div>

</body>
</html>
