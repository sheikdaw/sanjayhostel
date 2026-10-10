<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vacancy & Allocation Report</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; padding: 12px; }
        h1 { font-size: 15px; color: #0a1e3f; margin-bottom: 3px; }
        .meta { font-size: 8px; color: #6b7280; margin-bottom: 8px; }
        .summary { display: table; width: 100%; margin-bottom: 10px; border-collapse: separate; border-spacing: 4px; }
        .summary-row { display: table-row; }
        .sum-card {
            display: table-cell; padding: 6px 8px; border: 1px solid #e5e7eb;
            border-radius: 5px; text-align: center; background: #f9fafb;
        }
        .sum-label { font-size: 7px; color: #6b7280; text-transform: uppercase; font-weight: bold; }
        .sum-value { font-size: 13px; font-weight: bold; color: #0a1e3f; }
        table.main { width: 100%; border-collapse: collapse; }
        table.main th {
            background: #0a1e3f; color: white; padding: 5px 4px;
            font-size: 8px; text-align: left; font-weight: bold;
        }
        table.main td {
            padding: 4px; border-bottom: 1px solid #e5e7eb; font-size: 8px;
            vertical-align: middle;
        }
        table.main tr:nth-child(even) td { background: #f9fafb; }
        .badge {
            display: inline-block; padding: 1px 6px; border-radius: 8px;
            font-size: 7px; font-weight: bold;
        }
        .badge-full    { background: #dcfce7; color: #166534; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-vacant  { background: #fee2e2; color: #991b1b; }
        .badge-occ     { background: #dcfce7; color: #166534; }
        .badge-vac     { background: #f3f4f6; color: #4b5563; }
        .room-cell { font-weight: bold; color: #0a1e3f; }
        .muted { color: #9ca3af; }
    </style>
</head>
<body>

<h1>Room Vacancy & Allocation Report</h1>
<div class="meta">Generated: {{ $generated }}</div>

<div class="summary">
    <div class="summary-row">
        <div class="sum-card"><div class="sum-label">Rooms</div><div class="sum-value">{{ $summary['total_rooms'] }}</div></div>
        <div class="sum-card"><div class="sum-label">Beds</div><div class="sum-value">{{ $summary['total_beds'] }}</div></div>
        <div class="sum-card"><div class="sum-label">Occupied</div><div class="sum-value" style="color:#059669;">{{ $summary['occupied_beds'] }}</div></div>
        <div class="sum-card"><div class="sum-label">Vacant</div><div class="sum-value" style="color:#dc2626;">{{ $summary['vacant_beds'] }}</div></div>
        <div class="sum-card"><div class="sum-label">Full</div><div class="sum-value" style="color:#059669;">{{ $summary['fully_occupied'] }}</div></div>
        <div class="sum-card"><div class="sum-label">Partial</div><div class="sum-value" style="color:#f59e0b;">{{ $summary['partial_occupied'] }}</div></div>
        <div class="sum-card"><div class="sum-label">Empty</div><div class="sum-value" style="color:#dc2626;">{{ $summary['fully_vacant'] }}</div></div>
        <div class="sum-card"><div class="sum-label">Occupancy</div><div class="sum-value" style="color:#c5a028;">{{ $summary['occupancy_rate'] }}%</div></div>
    </div>
</div>

<table class="main">
    <thead>
        <tr>
            <th>Hostel</th>
            <th>Room</th>
            <th>Status</th>
            <th style="text-align:center;">Total</th>
            <th style="text-align:center;">Occ.</th>
            <th style="text-align:center;">Vac.</th>
            <th>Bed</th>
            <th>Bed Status</th>
            <th>Resident</th>
            <th>Phone</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $room)
            @foreach($room['beds'] as $idx => $bed)
            <tr>
                @if($idx === 0)
                    <td rowspan="{{ count($room['beds']) }}" style="font-weight:bold;">{{ $room['hostel_name'] }}</td>
                    <td rowspan="{{ count($room['beds']) }}" class="room-cell">{{ $room['room_no'] }}</td>
                    <td rowspan="{{ count($room['beds']) }}">
                        <span class="badge badge-{{ strtolower($room['room_status']) }}">{{ $room['room_status'] }}</span>
                    </td>
                    <td rowspan="{{ count($room['beds']) }}" style="text-align:center;">{{ $room['total_beds'] }}</td>
                    <td rowspan="{{ count($room['beds']) }}" style="text-align:center; color:#059669; font-weight:bold;">{{ $room['occupied_count'] }}</td>
                    <td rowspan="{{ count($room['beds']) }}" style="text-align:center; color:#dc2626; font-weight:bold;">{{ $room['vacant_count'] }}</td>
                @endif
                <td>{{ $bed['bed_no'] }} <span class="muted">({{ $bed['bed_type'] }})</span></td>
                <td>
                    <span class="badge {{ $bed['status'] === 'OCCUPIED' ? 'badge-occ' : 'badge-vac' }}">
                        {{ $bed['status'] }}
                    </span>
                </td>
                <td>
                    @if($bed['resident_name'])
                        <strong>{{ $bed['resident_name'] }}</strong><br>
                        <span class="muted">{{ $bed['resident_code'] }}</span>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td>{{ $bed['phone'] ?? '—' }}</td>
            </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="10" style="text-align:center; padding:20px; color:#9ca3af;">
                    No rooms found for this filter
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
