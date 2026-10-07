<!DOCTYPE html>
<html>
<head>
    <title>Active Residents (ESSL)</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Active Residents with Employee Code</h2>

    @if($residents->isEmpty())
        <p>No active residents found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Employee Code</th>
                    <th>Hostel</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($residents as $i => $resident)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $resident->name }}</td>
                        <td>{{ $resident->employee_code }}</td>
                        <td>{{ $resident->hostel->hostel_name ?? 'N/A' }}</td>
                        <td>{{ $resident->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total:</strong> {{ $residents->count() }}</p>
    @endif
</body>
</html>
