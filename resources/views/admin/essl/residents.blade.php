<!DOCTYPE html>
<html>

<head>
    <title>Active Residents (ESSL)</title>

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .filter {
            margin-bottom: 20px;
        }

        select {
            padding: 8px 12px;
            min-width: 250px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }
    </style>
</head>

<body>

    <h2>Active Residents with Employee Code</h2>

    @if ($hostels->isEmpty())

        <p>No active hostels found.</p>
    @else
        <div class="filter">
            <form method="GET">

                <label for="hostel">
                    <strong>Select Hostel:</strong>
                </label>

                <select name="hostel" id="hostel" onchange="this.form.submit()">

                    <option value="all" {{ request('hostel', 'all') == 'all' ? 'selected' : '' }}>
                        All Hostels
                    </option>

                    @foreach ($hostels as $hostel)
                        <option value="{{ $hostel->id }}" {{ request('hostel') == $hostel->id ? 'selected' : '' }}>
                            {{ $hostel->hostel_name }}
                        </option>
                    @endforeach

                </select>

            </form>
        </div>

    @endif


    @if ($residents->isEmpty())

        <p>No active residents found.</p>
    @else
        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Employee Code</th>
                    <th>Hostel</th>
                    <th>Room</th>
                    <th>Bed</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($residents as $i => $resident)
                    <tr>

                        <td>{{ $i + 1 }}</td>

                        <td>
                            {{ $resident->name }}
                        </td>

                        <td>
                            {{ $resident->employee_code }}
                        </td>

                        <td>
                            {{ $resident->hostel->hostel_name ?? 'N/A' }}
                        </td>

                        <td>
                            {{ $resident->room->room_no ?? 'N/A' }}
                        </td>

                        <td>
                            {{ $resident->bed->bed_no ?? 'N/A' }}
                        </td>

                        <td>
                            @if ($resident->status === 'VACATED')
                                <span style="color: red; font-weight: bold;">
                                    BLOCKED
                                </span>
                            @elseif($resident->status === 'ACTIVE' && $resident->biometric_access)
                                <span style="color: green; font-weight: bold;">
                                    UNBLOCKED
                                </span>
                            @else
                                <span style="color: red; font-weight: bold;">
                                    BLOCKED
                                </span>
                            @endif
                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>

        <p>
            <strong>Total:</strong> {{ $residents->count() }}
        </p>

    @endif

</body>

</html>
