<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="sample-token">
  <title>Sanjay & Harini Hostel · Complaint Portal</title>
  <!-- Tailwind + Font Awesome 6 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Font Inter for a subtle upgrade -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
  <style>
    * { font-family: 'Inter', system-ui, sans-serif; }
    body { background: #f8fafc; }
    .tab-btn { transition: all 0.2s ease; }
    .complaint-card { transition: box-shadow 0.2s ease; }
    .complaint-card:hover { box-shadow: 0 12px 24px -8px rgba(0,0,0,0.12); }
    .badge-status { font-size: 0.7rem; letter-spacing: 0.02em; }
    input, select, textarea { background: #ffffff; }
    .glass-header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
  </style>
</head>
<body class="min-h-screen antialiased text-gray-800">

<!-- header with brand -->
<header class="glass-header text-white shadow-lg">
  <div class="max-w-5xl mx-auto px-5 py-7 flex flex-wrap items-center justify-between">
    <div>
      <div class="flex items-center gap-3">
        <i class="fas fa-building text-3xl opacity-90"></i>
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight">Sanjay <span class="text-blue-200">&</span> Harini</h1>
          <p class="text-blue-100 text-sm font-medium mt-0.5">
            <i class="fas fa-tools mr-1"></i> Complaint & Management Portal
          </p>
        </div>
      </div>
    </div>
    <div class="flex items-center gap-2 text-sm bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/20 mt-3 sm:mt-0">
      <i class="fas fa-shield-alt text-blue-200"></i>
      <span class="font-medium">Resident-only access</span>
    </div>
  </div>
</header>

<!-- subtle wave / divider -->
<div class="h-4 bg-gradient-to-b from-blue-700/10 to-transparent"></div>

<!-- Tabs -->
<div class="max-w-5xl mx-auto px-5 -mt-5">
  <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/40 flex overflow-hidden">
    <button onclick="switchTab('new')" id="tab-new"
      class="tab-btn flex-1 py-4 font-semibold text-blue-700 border-b-4 border-blue-600 bg-blue-50/80 flex items-center justify-center gap-2">
      <i class="fas fa-plus-circle"></i> New complaint
    </button>
    <button onclick="switchTab('track')" id="tab-track"
      class="tab-btn flex-1 py-4 font-semibold text-gray-500 hover:bg-gray-50 flex items-center justify-center gap-2">
      <i class="fas fa-search"></i> Track
    </button>
    <button onclick="switchTab('my')" id="tab-my"
      class="tab-btn flex-1 py-4 font-semibold text-gray-500 hover:bg-gray-50 flex items-center justify-center gap-2">
      <i class="fas fa-list-ul"></i> My complaints
    </button>
  </div>
</div>

<!-- main content area -->
<div class="max-w-5xl mx-auto px-5 py-7">
  <!-- alert box -->
  <div id="alertBox" class="hidden mb-6 rounded-xl p-4 shadow-sm"></div>

  <!-- ========== NEW COMPLAINT ========== -->
  <div id="content-new" class="tab-content">
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-5 flex items-center gap-2">
        <i class="fas fa-pen-to-square text-blue-600"></i> Register a new complaint
      </h2>

      <!-- STEP 1: phone verification -->
      <div id="step-verify" class="border-2 border-dashed border-blue-300 rounded-xl p-5 bg-blue-50/60 mb-6">
        <div class="flex items-start gap-3">
          <div class="bg-blue-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold shrink-0">1</div>
          <div class="flex-1">
            <p class="text-sm text-blue-900 font-medium mb-3">
              Enter your <strong>registered mobile number</strong> to verify as an active resident of Sanjay & Harini Hostel.
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
              <input type="text" id="verifyPhone" inputmode="numeric" maxlength="15"
                class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                placeholder="e.g., 9876543210">
              <button type="button" onclick="verifyResident()" id="verifyBtn"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold whitespace-nowrap flex items-center justify-center gap-2 transition shadow-sm">
                <i class="fas fa-user-check"></i> Verify
              </button>
            </div>
            <div id="verifyMsg" class="mt-3 text-sm"></div>
          </div>
        </div>
      </div>

      <!-- STEP 2: complaint form (hidden until verified) -->
      <div id="step-form" class="hidden">
        <!-- resident card -->
        <div id="residentCard" class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-4">
          <div id="residentPhoto" class="w-14 h-14 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-700 text-2xl shadow-inner">
            <i class="fas fa-user"></i>
          </div>
          <div class="flex-1">
            <div class="font-bold text-emerald-900 text-lg" id="rName">—</div>
            <div class="text-sm text-emerald-700 flex flex-wrap gap-x-3">
              <span><i class="fas fa-phone-alt mr-1"></i><span id="rPhone">—</span></span>
              <span><i class="fas fa-door-open mr-1"></i>Room: <span id="rRoom">—</span></span>
            </div>
          </div>
          <div class="text-emerald-600 text-3xl">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>

        <!-- complaint form -->
        <form id="complaintForm" enctype="multipart/form-data">
          <input type="hidden" name="encoded_id" value="sample-encoded-id">
          <input type="hidden" name="phone" id="verifiedPhone">
          <input type="hidden" name="resident_id" id="verifiedResidentId">

          <div class="grid md:grid-cols-2 gap-5 mb-5">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
              <select name="category" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                <option value="">— Select category —</option>
                <option value="plumbing">Plumbing / Water</option>
                <option value="electrical">Electrical</option>
                <option value="wifi">Wi-Fi / Internet</option>
                <option value="cleaning">Cleaning / Housekeeping</option>
                <option value="food">Food / Mess</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
              <select name="priority" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>

          <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description <span class="text-red-500">*</span></label>
            <textarea name="description" rows="4" required minlength="10" maxlength="2000"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-y"
              placeholder="Describe the issue in detail (min 10 characters)..."></textarea>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              <i class="fas fa-camera text-gray-500 mr-1"></i> Attach photo (optional)
            </label>
            <div class="flex items-center gap-4">
              <label class="cursor-pointer bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium transition flex items-center gap-2">
                <i class="fas fa-cloud-upload-alt"></i> Choose image
                <input type="file" name="image" accept="image/*" id="imageInput" class="hidden">
              </label>
              <span class="text-xs text-gray-400">Max 5MB (JPG, PNG)</span>
            </div>
            <div id="imagePreview" class="mt-3 hidden">
              <img id="previewImg" class="max-h-44 rounded-lg border shadow-sm">
            </div>
          </div>

          <button type="submit" id="submitBtn"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white py-3.5 rounded-xl font-semibold text-lg shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
            <i class="fas fa-paper-plane"></i> Submit complaint
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ========== TRACK ========== -->
  <div id="content-track" class="tab-content hidden">
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-5 flex items-center gap-2">
        <i class="fas fa-magnifying-glass text-blue-600"></i> Track complaint
      </h2>
      <div class="flex flex-col sm:flex-row gap-3">
        <input type="text" id="trackNumber"
          class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
          placeholder="Enter complaint number (e.g., CMP-2025-001)">
        <button onclick="trackComplaint()"
          class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
          <i class="fas fa-search"></i> Track
        </button>
      </div>
      <div id="trackResult" class="mt-7"></div>
    </div>
  </div>

  <!-- ========== MY COMPLAINTS ========== -->
  <div id="content-my" class="tab-content hidden">
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-5 flex items-center gap-2">
        <i class="fas fa-list-check text-blue-600"></i> My complaints
      </h2>
      <div class="flex flex-col sm:flex-row gap-3 mb-7">
        <input type="text" id="myPhone" inputmode="numeric"
          class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
          placeholder="Enter your registered phone number">
        <button onclick="loadMyComplaints()"
          class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
          <i class="fas fa-arrow-right"></i> Search
        </button>
      </div>
      <div id="myComplaintsList" class="space-y-4"></div>
    </div>
  </div>
</div>

<!-- footer -->
<footer class="text-center text-gray-400 text-sm py-8 border-t border-gray-200/70 max-w-5xl mx-auto">
  <i class="fas fa-helmet-safety mr-1"></i> Sanjay & Harini Hostel · Complaint Management System
</footer>

<script>
// ---------- config (replaced with static/demo values for preview) ----------
const encodedId = "sample-encoded-id";
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let verifiedResident = null;

// ---------- tab switching ----------
function switchTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(el => {
    el.classList.remove('text-blue-700','border-b-4','border-blue-600','bg-blue-50/80');
    el.classList.add('text-gray-500','hover:bg-gray-50');
  });
  document.getElementById('content-'+tab).classList.remove('hidden');
  const b = document.getElementById('tab-'+tab);
  b.classList.add('text-blue-700','border-b-4','border-blue-600','bg-blue-50/80');
  b.classList.remove('text-gray-500','hover:bg-gray-50');
}

// ---------- alert ----------
function showAlert(type, msg) {
  const box = document.getElementById('alertBox');
  const styles = {
    success: 'bg-emerald-50 text-emerald-800 border border-emerald-200',
    error: 'bg-rose-50 text-rose-800 border border-rose-200',
    info: 'bg-blue-50 text-blue-800 border border-blue-200'
  };
  box.className = 'mb-6 rounded-xl p-4 shadow-sm ' + styles[type];
  box.innerHTML = msg;
  box.classList.remove('hidden');
  window.scrollTo({top:0, behavior:'smooth'});
  if (type === 'success') setTimeout(() => box.classList.add('hidden'), 8000);
}

// ---------- verify resident (demo) ----------
async function verifyResident() {
  const phone = document.getElementById('verifyPhone').value.trim();
  const msg = document.getElementById('verifyMsg');
  const btn = document.getElementById('verifyBtn');

  if (!phone || phone.length < 10) {
    msg.innerHTML = '<span class="text-rose-600 flex items-center gap-1"><i class="fas fa-circle-exclamation"></i> Enter a valid 10-digit mobile number</span>';
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
  msg.innerHTML = '<span class="text-blue-600 flex items-center gap-1"><i class="fas fa-spinner fa-spin"></i> Verifying resident...</span>';

  // Demo: simulate verification based on phone number length or any number >= 10 digits
  // In real app this would be an API call.
  try {
    // fake network delay
    await new Promise(r => setTimeout(r, 600));

    // Demo logic: accept any 10+ digit number as verified (for preview)
    // In production, this would check against the backend.
    if (phone.length >= 10) {
      // simulate resident data
      verifiedResident = {
        name: 'Ananya Sharma',
        phone: phone,
        room_number: 'B-204',
        resident_id: 'RES-1001',
        photo: null
      };

      document.getElementById('rName').textContent = verifiedResident.name;
      document.getElementById('rPhone').textContent = verifiedResident.phone;
      document.getElementById('rRoom').textContent = verifiedResident.room_number;
      document.getElementById('verifiedPhone').value = verifiedResident.phone;
      document.getElementById('verifiedResidentId').value = verifiedResident.resident_id;

      // (no photo for demo)

      document.getElementById('step-verify').classList.add('hidden');
      document.getElementById('step-form').classList.remove('hidden');

      showAlert('success', '✓ <strong>Resident verified!</strong> You can now register your complaint.');
      msg.innerHTML = '<span class="text-emerald-600 font-medium"><i class="fas fa-check-circle"></i> Verified</span>';
    } else {
      verifiedResident = null;
      document.getElementById('step-form').classList.add('hidden');
      document.getElementById('step-verify').classList.remove('hidden');
      msg.innerHTML = '<span class="text-rose-600 font-semibold"><i class="fas fa-circle-xmark"></i> Not a resident</span>';
      showAlert('error', '<strong>❌ Access Denied!</strong><br>This phone number is not registered as an active resident in Sanjay & Harini Hostel.<br><small>Only residents can register complaints.</small>');
    }
  } catch (e) {
    msg.innerHTML = '<span class="text-rose-600">Network error</span>';
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-user-check"></i> Verify';
  }
}

// Enter key on phone verify
document.getElementById('verifyPhone').addEventListener('keypress', e => {
  if (e.key === 'Enter') { e.preventDefault(); verifyResident(); }
});

// ---------- image preview ----------
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

// ---------- submit complaint (demo) ----------
document.getElementById('complaintForm').addEventListener('submit', async function(e) {
  e.preventDefault();

  if (!verifiedResident) {
    showAlert('error', 'Please verify your phone number first.');
    return;
  }

  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

  // DEMO: simulate successful complaint submission
  setTimeout(() => {
    // show success with fake complaint number
    const fakeNumber = 'CMP-' + Math.floor(Math.random() * 9000 + 1000);
    showAlert('success',
      `<strong>✓ Complaint registered!</strong><br>
       Complaint No: <span class="font-mono font-bold text-lg bg-white/60 px-2 py-0.5 rounded">${fakeNumber}</span><br>
       <small>Please save this number to track your complaint.</small>`);

    // reset form
    this.reset();
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('step-verify').classList.remove('hidden');
    document.getElementById('step-form').classList.add('hidden');
    document.getElementById('verifyPhone').value = '';
    document.getElementById('verifyMsg').innerHTML = '';
    verifiedResident = null;

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit complaint';
  }, 700);
});

// ---------- track complaint (demo) ----------
async function trackComplaint() {
  const num = document.getElementById('trackNumber').value.trim();
  if (!num) return alert('Please enter a complaint number');

  const result = document.getElementById('trackResult');
  result.innerHTML = '<div class="text-center py-6"><i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i></div>';

  // Demo: return a sample complaint card
  setTimeout(() => {
    const demoComplaint = {
      complaint_number: num || 'CMP-2025-042',
      created_at: '2025-03-15 10:30',
      status: 'in_progress',
      name: 'Ananya Sharma',
      room_number: 'B-204',
      category: 'Plumbing',
      priority: 'High',
      description: 'Leaking tap in bathroom, water wastage. Needs urgent fix.',
      image: null,
      admin_remark: 'Plumber assigned, will visit by 5 PM today.',
      resolved_at: null
    };
    result.innerHTML = renderComplaintCard(demoComplaint);
  }, 500);
}

// ---------- my complaints (demo) ----------
async function loadMyComplaints() {
  const phone = document.getElementById('myPhone').value.trim();
  if (!phone) return alert('Please enter your phone number');

  const list = document.getElementById('myComplaintsList');
  list.innerHTML = '<div class="text-center py-6"><i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i></div>';

  // Demo: return two sample complaints
  setTimeout(() => {
    const demoList = [
      {
        complaint_number: 'CMP-2025-042',
        created_at: '2025-03-15 10:30',
        status: 'in_progress',
        category: 'Plumbing',
        priority: 'High',
        description: 'Leaking tap in bathroom, water wastage...',
        admin_remark: 'Plumber assigned'
      },
      {
        complaint_number: 'CMP-2025-038',
        created_at: '2025-03-12 08:15',
        status: 'resolved',
        category: 'Wi-Fi / Internet',
        priority: 'Medium',
        description: 'Wi-Fi not working in room B-204...',
        admin_remark: 'Router replaced, issue fixed'
      }
    ];

    if (demoList.length) {
      list.innerHTML = demoList.map(c => `
        <div class="complaint-card border border-gray-200 rounded-xl p-5 bg-white hover:shadow-md transition">
          <div class="flex justify-between items-start mb-3">
            <div>
              <div class="font-mono font-bold text-blue-700 text-lg">${c.complaint_number}</div>
              <div class="text-xs text-gray-400 mt-0.5"><i class="far fa-clock mr-1"></i>${c.created_at}</div>
            </div>
            ${statusBadge(c.status)}
          </div>
          <div class="flex flex-wrap gap-4 text-sm mb-2">
            <span><i class="fas fa-tag text-gray-400 mr-1"></i>${c.category}</span>
            <span><i class="fas fa-flag text-gray-400 mr-1"></i>${c.priority}</span>
          </div>
          <div class="text-sm text-gray-600 line-clamp-2">${c.description}</div>
          ${c.admin_remark ? `<div class="mt-3 text-xs bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg">
            <i class="fas fa-comment-dots text-amber-600 mr-1"></i><strong>Admin:</strong> ${c.admin_remark}</div>` : ''}
        </div>
      `).join('');
    } else {
      list.innerHTML = '<div class="text-center text-gray-400 py-8"><i class="fas fa-inbox text-4xl mb-2 opacity-40"></i><br>No complaints found</div>';
    }
  }, 500);
}

// ---------- helpers ----------
function statusBadge(status) {
  const map = {
    pending:     'bg-amber-100 text-amber-800 border border-amber-200',
    in_progress: 'bg-blue-100 text-blue-800 border border-blue-200',
    resolved:    'bg-emerald-100 text-emerald-800 border border-emerald-200',
    rejected:    'bg-rose-100 text-rose-800 border border-rose-200'
  };
  const label = status.replace('_',' ').toUpperCase();
  return `<span class="badge-status px-3 py-1 rounded-full font-semibold ${map[status] || 'bg-gray-100'}">${label}</span>`;
}

function renderComplaintCard(c) {
  return `
    <div class="border border-gray-200 rounded-xl p-6 bg-gray-50/70 shadow-sm">
      <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
        <div>
          <div class="font-mono font-bold text-blue-700 text-xl">${c.complaint_number}</div>
          <div class="text-xs text-gray-400 mt-1"><i class="far fa-calendar-alt mr-1"></i>Submitted: ${c.created_at}</div>
        </div>
        ${statusBadge(c.status)}
      </div>
      <div class="grid sm:grid-cols-2 gap-3 text-sm mb-4">
        <div><span class="text-gray-500">Name:</span> <span class="font-medium">${c.name}</span></div>
        <div><span class="text-gray-500">Room:</span> <span class="font-medium">${c.room_number || 'N/A'}</span></div>
        <div><span class="text-gray-500">Category:</span> <span class="font-medium">${c.category}</span></div>
        <div><span class="text-gray-500">Priority:</span> <span class="font-medium">${c.priority}</span></div>
        ${c.resolved_at ? `<div class="sm:col-span-2"><span class="text-gray-500">Resolved:</span> ${c.resolved_at}</div>` : ''}
      </div>
      <div class="text-sm bg-white p-4 rounded-lg border border-gray-200 mb-4">
        <div class="font-semibold text-gray-700 mb-1"><i class="fas fa-align-left mr-1"></i>Description</div>
        <div class="text-gray-600">${c.description}</div>
      </div>
      ${c.image ? `<img src="${c.image}" class="max-h-64 rounded-lg border mb-4 shadow-sm">` : ''}
      ${c.admin_remark ? `<div class="text-sm bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
        <i class="fas fa-comment-dots text-amber-600 mr-1"></i><strong>Admin remark:</strong><br>${c.admin_remark}</div>` : ''}
    </div>`;
}
</script>
</body>
</html>
