<!DOCTYPE html>
<html>

<head>

    <title>ESSL - Residents</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f8f9fa;
            color: #222;
        }

        h2 {
            margin-bottom: 20px;
        }

        .filter {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        select {
            padding: 9px 12px;
            min-width: 280px;
            border: 1px solid #bbb;
            border-radius: 4px;
            background: #fff;
            font-size: 14px;
        }

        .loading {
            display: none;
            margin-left: 10px;
            color: #666;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f4f4f4;
            font-weight: bold;
        }

        tr:hover {
            background: #fafafa;
        }

        .unblocked {
            color: #16a34a;
            font-weight: bold;
        }

        .blocked {
            color: #dc2626;
            font-weight: bold;
        }

        .empty {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .total {
            margin-top: 15px;
        }
    </style>

</head>


<body>

    <h2>Residents - ESSL Biometric Access</h2>


    {{-- HOSTEL FILTER --}}

    @if ($hostels->isEmpty())

        <div class="empty">
            No active hostels found.
        </div>
    @else
        <div class="filter">

            <label for="hostel_id">
                <strong>Select Hostel:</strong>
            </label>

            <select id="hostel_id">

                <option value="all">
                    All Hostels
                </option>

                @foreach ($hostels as $hostel)
                    <option value="{{ $hostel->id }}">
                        {{ $hostel->hostel_name }}
                    </option>
                @endforeach

            </select>

            <span id="loading" class="loading">
                Loading...
            </span>

        </div>

    @endif


    {{-- RESIDENT TABLE --}}

    <div id="residentTable">

        @if ($residents->isEmpty())

            <div class="empty">
                No residents found.
            </div>
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
                        <th>Biometric Access</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($residents as $i => $resident)
                        <tr>

                            <td>
                                {{ $i + 1 }}
                            </td>

                            <td>
                                {{ $resident->name }}
                            </td>

                            <td>
                                {{ $resident->employee_code ?? 'N/A' }}
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

                                @if ($resident->status === 'ACTIVE' && $resident->biometric_access)
                                    <span class="unblocked">
                                        UNBLOCKED
                                    </span>
                                @else
                                    <span class="blocked">
                                        BLOCKED
                                    </span>
                                @endif

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

            <div class="total">

                <strong>Total:</strong>
                {{ $residents->count() }}

            </div>

        @endif

    </div>


    {{-- AJAX --}}

    <script>
        document
            .getElementById('hostel_id')
            .addEventListener('change', function() {

                const hostelId = this.value;

                const table =
                    document.getElementById('residentTable');

                const loading =
                    document.getElementById('loading');


                loading.style.display = 'inline';


                /*
                 * Separate AJAX route
                 *
                 * Example:
                 *
                 * /admin/essl/get-residents?hostel_id=1
                 */

                const url =
                    "{{ route('admin.essl.get-residents') }}" +
                    "?hostel_id=" +
                    encodeURIComponent(hostelId);


                fetch(url, {

                        method: 'GET',

                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }

                    })

                    .then(response => {

                        if (!response.ok) {
                            throw new Error(
                                'Failed to load residents'
                            );
                        }

                        return response.json();

                    })

                    .then(data => {

                        if (!data.success) {
                            throw new Error(
                                'Unable to load residents'
                            );
                        }


                        const residents = data.residents;


                        /*
                         * No residents
                         */

                        if (residents.length === 0) {

                            table.innerHTML = `

                            <div class="empty">

                                No residents found
                                for this hostel.

                            </div>

                        `;

                            return;
                        }


                        /*
                         * Create table
                         */

                        let html = `

                        <table>

                            <thead>

                                <tr>

                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Employee Code</th>
                                    <th>Hostel</th>
                                    <th>Room</th>
                                    <th>Bed</th>
                                    <th>Biometric Access</th>

                                </tr>

                            </thead>

                            <tbody>

                    `;


                        residents.forEach(
                            function(resident, index) {

                                /*
                                 * ACTIVE + biometric_access true
                                 * = UNBLOCKED
                                 *
                                 * Everything else
                                 * = BLOCKED
                                 */

                                let biometricStatus;


                                if (
                                    resident.status === 'ACTIVE' &&
                                    resident.biometric_access === true
                                ) {

                                    biometricStatus = `

                                    <span class="unblocked">
                                        UNBLOCKED
                                    </span>

                                `;

                                } else {

                                    biometricStatus = `

                                    <span class="blocked">
                                        BLOCKED
                                    </span>

                                `;

                                }


                                html += `

                                <tr>

                                    <td>
                                        ${index + 1}
                                    </td>

                                    <td>
                                        ${resident.name ?? 'N/A'}
                                    </td>

                                    <td>
                                        ${resident.employee_code ?? 'N/A'}
                                    </td>

                                    <td>
                                        ${
                                            resident.hostel?.hostel_name
                                            ?? 'N/A'
                                        }
                                    </td>

                                    <td>
                                        ${
                                            resident.room?.room_no
                                            ?? 'N/A'
                                        }
                                    </td>

                                    <td>
                                        ${
                                            resident.bed?.bed_no
                                            ?? 'N/A'
                                        }
                                    </td>

                                    <td>
                                        ${biometricStatus}
                                    </td>

                                </tr>

                            `;

                            }
                        );


                        html += `

                            </tbody>

                        </table>

                        <div class="total">

                            <strong>Total:</strong>
                            ${data.total}

                        </div>

                    `;


                        table.innerHTML = html;

                    })

                    .catch(error => {

                        console.error(error);

                        table.innerHTML = `

                        <div class="empty"
                            style="color:red;">

                            Failed to load residents.

                        </div>

                    `;

                    })

                    .finally(() => {

                        loading.style.display = 'none';

                    });

            });
    </script>


</body>

</html>
