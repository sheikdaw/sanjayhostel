{{-- resources/views/guest/complaint/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complaint Portal - {{ $hostel->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Header -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-lg">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $hostel->name }}</h1>
                <p class="text-blue-100 text-sm mt-1">
                    <i class="fas fa-tools mr-1"></i> Complaint Portal
                </p>
            </div>
            <i class="fas fa-headset text-4xl opacity-50"></i>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="max-w-4xl mx-auto px-4 -mt-4">
    <div class="bg-white rounded-lg shadow-md overflow-hidden flex">
        <button onclick="switchTab('new')" id="tab-new"
            class="tab-btn flex-1 py-3 font-semibold text-blue-600 border-b-2 border-blue-600 bg-blue-50">
            <i class="fas fa-plus-circle mr-2"></i>New Complaint
        </button>
        <button onclick="switchTab('track')" id="tab-track"
            class="tab-btn flex-1 py-3 font-semibold text-gray-600 hover:bg-gray-50">
            <i class="fas fa-search mr-2"></i>Track
        </button>
        <button onclick="switchTab('my')" id="tab-my"
            class="tab-btn flex-1 py-3 font-semibold text-gray-600 hover:bg-gray-50">
            <i class="fas fa-list mr-2"></i>My Complaints
        </button>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-6">
    <div id="alertBox" class="hidden mb-4 rounded-lg p-4"></div>

    <!-- ============ NEW COMPLAINT ============ -->
    <div id="content-new" class="tab-content">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-edit text-blue-600 mr-2"></i>Register New Complaint
            </h2>

            <!-- STEP 1: Phone Verification -->
            <div id="step-verify" class="border-2 border-dashed border-blue-300 rounded-lg p-4 mb-4 bg-blue-50">
                <p class="text-sm text-blue-800 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Step 1:</strong> Enter your registered phone number to verify.
                    Only active residents of <strong>{{ $hostel->name }}</strong> can register complaints.
                </p>
                <div class="flex gap-2">
                    <input type="text" id="verifyPhone" inputmode="numeric" maxlength="15"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2"
                        placeholder="Enter registered mobile number">
                    <button type="button" onclick="verifyResident()" id="verifyBtn"
                        class="bg-blue-600 text-white px-5 rounded-lg hover:bg-blue-700 font-semibold whitespace-nowrap">
                        <i class="fas fa-user-check mr-1"></i> Verify
                    </button>
                </div>
                <div id="verifyMsg" class="mt-3 text-sm"></div>
            </div>

            <!-- STEP 2: Complaint Form (hidden until verified) -->
            <div id="step-form" class="hidden">
                <div id="residentCard" class="bg-green-50 border border-green-300 rounded-lg p-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div id="residentPhoto"
                            class="w-12 h-12 rounded-full bg-green-200 flex items-center justify-center text-green-700 text-xl">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-green-900" id="rName"></div>
                            <div class="text-xs text-green-700">
                                <span id="rPhone"></span> • Room: <span id="rRoom"></span>
                            </div>
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                    </div>
                </div>

                <form id="complaintForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="encoded_id" value="{{ $encodedId }}">
                    <input type="hidden" name="phone" id="verifiedPhone">
                    <input type="hidden" name="resident_id" id="verifiedResidentId">

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="category" required
                                class="w-full border rounded-lg px-3 py-2">
                                <option value="">-- Select --</option>
                                @foreach($categories as $k => $l)
                                    <option value="{{ $k }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <select name="priority" required class="w-full border rounded-lg px-3 py-2">
                                @foreach($priorities as $k => $l)
                                    <option value="{{ $k }}" {{ $k=='medium'?'selected':'' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="4" required minlength="10" maxlength="2000"
                            class="w-full border rounded-lg px-3 py-2"
                            placeholder="Describe your issue in detail (min 10 characters)..."></textarea>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="fas fa-camera mr-1"></i> Attach Photo (Optional)
                        </label>
                        <input type="file" name="image" accept="image/*" id="imageInput"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Max 5MB (JPG, PNG)</p>
                        <div id="imagePreview" class="mt-2 hidden">
                            <img id="previewImg" class="max-h-40 rounded border">
                        </div>
                    </div>

                    <button type="submit" id="submitBtn"
                        class="w-full mt-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-3 rounded-lg font-semibold hover:shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Complaint
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============ TRACK ============ -->
    <div id="content-track" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-search text-blue-600 mr-2"></i>Track Complaint
            </h2>
            <div class="flex gap-2">
                <input type="text" id="trackNumber"
                    class="flex-1 border rounded-lg px-3 py-2"
                    placeholder="Enter complaint number">
                <button onclick="trackComplaint()"
                    class="bg-blue-600 text-white px-6 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div id="trackResult" class="mt-6"></div>
        </div>
    </div>

    <!-- ============ MY COMPLAINTS ============ -->
    <div id="content-my" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-list text-blue-600 mr-2"></i>My Complaints
            </h2>
            <div class="flex gap-2 mb-4">
                <input type="text" id="myPhone" inputmode="numeric"
                    class="flex-1 border rounded-lg px-3 py-2"
                    placeholder="Enter your registered phone">
                <button onclick="loadMyComplaints()"
                    class="bg-blue-600 text-white px-6 rounded-lg hover:bg-blue-700">
                    Search
                </button>
            </div>
            <div id="myComplaintsList"></div>
        </div>
    </div>
</div>

<div class="text-center text-gray-500 text-sm py-6">
    &copy; {{ date('Y') }} {{ $hostel->name }}
</div>

<script>
const encodedId = "{{ $encodedId }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let verifiedResident = null;

// ---------- TAB SWITCH ----------
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('text-blue-600','border-b-2','border-blue-600','bg-blue-50');
        el.classList.add('text-gray-600');
    });
    document.getElementById('content-'+tab).classList.remove('hidden');
    const b = document.getElementById('tab-'+tab);
    b.classList.add('text-blue-600','border-b-2','border-blue-600','bg-blue-50');
    b.classList.remove('text-gray-600');
}

// ---------- ALERT ----------
function showAlert(type, msg) {
    const box = document.getElementById('alertBox');
    const colors = {
        success: 'bg-green-100 text-green-800 border border-green-300',
        error:   'bg-red-100 text-red-800 border border-red-300',
        info:    'bg-blue-100 text-blue-800 border border-blue-300'
    };
    box.className = 'mb-4 rounded-lg p-4 ' + colors[type];
    box.innerHTML = msg;
    box.classList.remove('hidden');
    window.scrollTo({top:0, behavior:'smooth'});
    if (type === 'success') setTimeout(() => box.classList.add('hidden'), 8000);
}

// ============ VERIFY RESIDENT ============
async function verifyResident() {
    const phone = document.getElementById('verifyPhone').value.trim();
    const msg = document.getElementById('verifyMsg');
    const btn = document.getElementById('verifyBtn');

    if (!phone || phone.length < 10) {
        msg.innerHTML = '<span class="text-red-600"><i class="fas fa-exclamation-circle"></i> Enter valid 10-digit number</span>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    msg.innerHTML = '<span class="text-blue-600"><i class="fas fa-spinner fa-spin"></i> Verifying...</span>';

    try {
        const res = await fetch("{{ route('guest.complaint.verify-resident') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ encoded_id: encodedId, phone })
        });
        const data = await res.json();

        if (data.success && data.verified) {
            // ✔ Verified — fill resident details
            verifiedResident = data.data;

            document.getElementById('rName').textContent  = data.data.name;
            document.getElementById('rPhone').textContent = data.data.phone;
            document.getElementById('rRoom').textContent  = data.data.room_number || 'N/A';
            document.getElementById('verifiedPhone').value = data.data.phone;
            document.getElementById('verifiedResidentId').value = data.data.resident_id;

            if (data.data.photo) {
                document.getElementById('residentPhoto').innerHTML =
                    `<img src="${data.data.photo}" class="w-12 h-12 rounded-full object-cover">`;
            }

            // Show complaint form
            document.getElementById('step-verify').classList.add('hidden');
            document.getElementById('step-form').classList.remove('hidden');

            showAlert('success', '✓ <strong>Resident verified!</strong> You can now register your complaint.');
        } else {
            // ✘ Not a resident — BLOCK
            verifiedResident = null;
            document.getElementById('step-form').classList.add('hidden');
            document.getElementById('step-verify').classList.remove('hidden');

            msg.innerHTML = `<span class="text-red-600 font-semibold">
                <i class="fas fa-times-circle"></i> ${data.message}
            </span>`;
            showAlert('error', 
                `<strong>❌ Access Denied!</strong><br>
                 This phone number is not registered as an active resident in <strong>{{ $hostel->name }}</strong>.<br>
                 <small>Only residents can register complaints.</small>`);
        }
    } catch (e) {
        msg.innerHTML = '<span class="text-red-600">Network error</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-check mr-1"></i> Verify';
    }
}

// Allow Enter key on phone verify
document.getElementById('verifyPhone').addEventListener('keypress', e => {
    if (e.key === 'Enter') { e.preventDefault(); verifyResident(); }
});

// ---------- IMAGE PREVIEW ----------
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) { document.getElementById('imagePreview').classList.add('hidden'); return; }
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('previewImg').src = ev.target.result;
        document.getElementById('imagePreview').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});

// ============ SUBMIT COMPLAINT ============
document.getElementById('complaintForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!verifiedResident) {
        showAlert('error', 'Please verify your phone number first.');
        return;
    }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';

    const formData = new FormData(this);

    try {
        const res = await fetch("{{ route('guest.complaint.submit') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            showAlert('success',
                `<strong>✓ Complaint Registered!</strong><br>
                 Complaint No: <span class="font-mono font-bold text-lg">${data.data.complaint_number}</span><br>
                 <small>Please save this number to track your complaint.</small>`);

            // Reset
            this.reset();
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('step-verify').classList.remove('hidden');
            document.getElementById('step-form').classList.add('hidden');
            document.getElementById('verifyPhone').value = '';
            document.getElementById('verifyMsg').innerHTML = '';
            verifiedResident = null;
        } else {
            let msg = data.message || 'Error';
            if (data.errors) msg = Object.values(data.errors).flat().join('<br>');
            showAlert('error', msg);

            // If resident not found (server-side block)
            if (data.code === 'RESIDENT_NOT_FOUND') {
                document.getElementById('step-verify').classList.remove('hidden');
                document.getElementById('step-form').classList.add('hidden');
                verifiedResident = null;
            }
        }
    } catch (e) {
        showAlert('error', 'Network error. Try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Submit Complaint';
    }
});

// ============ TRACK ============
async function trackComplaint() {
    const num = document.getElementById('trackNumber').value.trim();
    if (!num) return alert('Enter complaint number');

    const result = document.getElementById('trackResult');
    result.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i></div>';

    try {
        const res = await fetch("{{ route('guest.complaint.track') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ encoded_id: encodedId, complaint_number: num })
        });
        const data = await res.json();

        if (data.success) {
            result.innerHTML = renderComplaintCard(data.data);
        } else {
            result.innerHTML = `<div class="text-center text-red-600 py-4">
                <i class="fas fa-exclamation-circle text-2xl mb-2"></i><br>${data.message}</div>`;
        }
    } catch (e) {
        result.innerHTML = '<div class="text-red-600 text-center py-4">Network error</div>';
    }
}

// ============ MY COMPLAINTS ============
async function loadMyComplaints() {
    const phone = document.getElementById('myPhone').value.trim();
    if (!phone) return alert('Enter phone number');

    const list = document.getElementById('myComplaintsList');
    list.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i></div>';

    try {
        const res = await fetch("{{ route('guest.complaint.my-complaints') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ encoded_id: encodedId, phone })
        });
        const data = await res.json();

        if (data.success && data.data.length) {
            list.innerHTML = data.data.map(c => `
                <div class="border rounded-lg p-4 mb-3 hover:shadow-md">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="font-mono font-bold text-blue-700">${c.complaint_number}</div>
                            <div class="text-xs text-gray-500">${c.created_at}</div>
                        </div>
                        ${statusBadge(c.status)}
                    </div>
                    <div class="text-sm text-gray-700 mb-1">
                        <strong>Category:</strong> ${c.category} | <strong>Priority:</strong> ${c.priority}
                    </div>
                    <div class="text-sm text-gray-600">${c.description}...</div>
                    ${c.admin_remark ? `<div class="mt-2 text-xs bg-yellow-50 border-l-4 border-yellow-400 p-2">
                        <strong>Admin:</strong> ${c.admin_remark}</div>` : ''}
                </div>
            `).join('');
        } else {
            list.innerHTML = '<div class="text-center text-gray-500 py-4">No complaints found</div>';
        }
    } catch (e) {
        list.innerHTML = '<div class="text-red-600 text-center py-4">Network error</div>';
    }
}

// ============ HELPERS ============
function statusBadge(status) {
    const map = {
        pending:     'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        resolved:    'bg-green-100 text-green-800',
        rejected:    'bg-red-100 text-red-800'
    };
    return `<span class="text-xs px-2 py-1 rounded-full font-semibold ${map[status]}">
        ${status.replace('_',' ').toUpperCase()}</span>`;
}

function renderComplaintCard(c) {
    return `
        <div class="border rounded-lg p-5 bg-gray-50">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="font-mono font-bold text-blue-700 text-lg">${c.complaint_number}</div>
                    <div class="text-xs text-gray-500">Submitted: ${c.created_at}</div>
                </div>
                ${statusBadge(c.status)}
            </div>
            <div class="grid md:grid-cols-2 gap-2 text-sm mb-3">
                <div><strong>Name:</strong> ${c.name}</div>
                <div><strong>Room:</strong> ${c.room_number || 'N/A'}</div>
                <div><strong>Category:</strong> ${c.category}</div>
                <div><strong>Priority:</strong> ${c.priority}</div>
                ${c.resolved_at ? `<div><strong>Resolved:</strong> ${c.resolved_at}</div>` : ''}
            </div>
            <div class="text-sm bg-white p-3 rounded border mb-3">
                <strong>Description:</strong><br>${c.description}
            </div>
            ${c.image ? `<img src="${c.image}" class="max-h-64 rounded border mb-3">` : ''}
            ${c.admin_remark ? `<div class="text-sm bg-yellow-50 border-l-4 border-yellow-400 p-3">
                <strong>Admin Remark:</strong><br>${c.admin_remark}</div>` : ''}
        </div>`;
}
</script>
</body>
</html>