<!DOCTYPE html>
<html>
<head>
    <title>eSSL Device Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre { background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 6px; font-size: 12px; max-height: 500px; overflow: auto; }
        .card-fix { position: sticky; top: 20px; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid py-4">

    <h2>🧪 eSSL Device Test Panel</h2>
    <p class="text-muted">Add residents and see the real SOAP response with server-returned CommandId.</p>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><strong>{{ $residents->count() }}</strong> ACTIVE residents</span>
                    <button class="btn btn-warning btn-sm" onclick="addAll()">⚡ Add ALL</button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Serial</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($residents as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->name }}</td>
                                <td><span class="badge bg-dark">{{ $r->employee_code }}</span></td>
                                <td>
                                    @if($r->hostel?->biometric_device_id)
                                        <span class="badge bg-info">{{ $r->hostel->biometric_device_id }}</span>
                                    @else
                                        <span class="badge bg-danger">No device</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="addOne({{ $r->id }})">
                                        Add &amp; Test
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm card-fix">
                <div class="card-header bg-dark text-white d-flex justify-content-between">
                    <strong>📋 Response</strong>
                    <button class="btn btn-sm btn-outline-light" onclick="document.getElementById('output').textContent=''">Clear</button>
                </div>
                <div class="card-body">
                    <pre id="output">Click "Add &amp; Test" to see the full SOAP request/response.</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const csrf = '{{ csrf_token() }}';

function print(obj) {
    document.getElementById('output').textContent = JSON.stringify(obj, null, 2);
}

async function addOne(id) {
    print({ status: '⏳ Calling device...' });
    try {
        const res = await fetch('/essl-test/add-one/' + id, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        print(data);
    } catch (e) {
        print({ error: e.message });
    }
}

async function addAll() {
    print({ status: '⏳ Adding ALL residents...' });
    try {
        const res = await fetch('/essl-test/add-all', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        print(data);
    } catch (e) {
        print({ error: e.message });
    }
}
</script>
</body>
</html>
