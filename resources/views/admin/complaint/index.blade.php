{{-- resources/views/admin/complaint/index.blade.php --}}
@extends('layouts.office') {{-- change to your admin layout --}}

@section('title', 'Complaint Management')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- ============ HEADER ============ --}}
    <header class="bg-gradient-to-r from-red-800 to-red-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-5 py-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <i class="fas fa-user-shield text-3xl"></i>
                <div>
                    <h1 class="text-2xl font-extrabold">Auth · Complaint Management</h1>
                    <p class="text-red-100 text-xs">All hostel complaints · Full control</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm bg-white/15 px-4 py-2 rounded-full border border-white/20">
                    <i class="fas fa-user-circle mr-1"></i> {{ auth()->user()->name ?? 'Admin' }}
                </span>
                <a href="{{ route('admin.dashboard') }}"
                   class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg font-semibold text-sm transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-5 py-7">

        {{-- ============ STATS ============ --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-7">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-l-gray-400">
                <div class="text-xs text-gray-500 font-semibold uppercase">Total</div>
                <div class="text-2xl font-bold text-gray-800" id="statTotal">0</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-l-amber-400">
                <div class="text-xs text-gray-500 font-semibold uppercase">Pending</div>
                <div class="text-2xl font-bold text-amber-600" id="statPending">0</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-l-blue-500">
                <div class="text-xs text-gray-500 font-semibold uppercase">In Progress</div>
                <div class="text-2xl font-bold text-blue-600" id="statProgress">0</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-l-emerald-500">
                <div class="text-xs text-gray-500 font-semibold uppercase">Resolved</div>
                <div class="text-2xl font-bold text-emerald-600" id="statResolved">0</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-l-rose-500">
                <div class="text-xs text-gray-500 font-semibold uppercase">Rejected</div>
                <div class="text-2xl font-bold text-rose-600" id="statRejected">0</div>
            </div>
        </div>

        {{-- ============ FILTERS ============ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="grid md:grid-cols-6 gap-3">
                <div class="md:col-span-2 relative">
                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                    <input type="text" id="fSearch" oninput="debouncedLoad()"
                        class="w-full border border-gray-300 rounded-lg pl-11 pr-4 py-3 focus:ring-2 focus:ring-red-500 outline-none"
                        placeholder="Search no, name, phone, room...">
                </div>

                <select id="fHostel" onchange="loadData()"
                    class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none bg-white">
                    <option value="">All hostels</option>
                    @foreach($hostels as $h)
    <option value="{{ $h->id }}">{{ $h->hostel_name }}</option>
@endforeach
                </select>

                <select id="fStatus" onchange="loadData()"
                    class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none bg-white">
                    <option value="">All status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="rejected">Rejected</option>
                </select>

                <select id="fPriority" onchange="loadData()"
                    class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none bg-white">
                    <option value="">All priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>

                <select id="fCategory" onchange="loadData()"
                    class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none bg-white">
                    <option value="">All category</option>
                    <option value="electrical">Electrical</option>
                    <option value="plumbing">Plumbing</option>
                    <option value="furniture">Furniture</option>
                    <option value="cleaning">Cleaning</option>
                    <option value="wifi">WiFi</option>
                    <option value="food">Food</option>
                    <option value="security">Security</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="flex gap-2 mt-3">
                <button onclick="loadData()"
                    class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold transition flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button onclick="resetFilters()"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg font-semibold transition">
                    Reset
                </button>
            </div>
        </div>

        {{-- ============ LIST ============ --}}
        <div id="listWrap">
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
                <i class="fas fa-spinner fa-spin text-3xl"></i>
                <div class="mt-2">Loading complaints…</div>
            </div>
        </div>

        {{-- Pagination --}}
        <div id="pagination" class="mt-5 flex justify-center"></div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- ==================== EDIT MODAL ============================ --}}
{{-- ============================================================ --}}
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl my-8 animate-[fadeIn_.2s_ease]">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div>
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-blue-600"></i> Update Complaint
                </h3>
                <p class="text-xs text-gray-400 mt-0.5 font-mono" id="eComplaintNo">—</p>
            </div>
            <button onclick="closeEdit()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6 max-h-[70vh] overflow-y-auto">
            {{-- Resident info --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5">
                <div class="text-xs text-blue-700 font-bold uppercase mb-2 flex items-center gap-1">
                    <i class="fas fa-user"></i> Resident
                </div>
                <div class="grid sm:grid-cols-3 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Name</div>
                        <div class="font-semibold" id="eName">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Phone</div>
                        <div class="font-semibold" id="ePhone">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Room / Hostel</div>
                        <div class="font-semibold" id="eRoomHostel">—</div>
                    </div>
                </div>
            </div>

            {{-- Editable --}}
            <div class="grid sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
                    <select id="eCategory" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="electrical">Electrical</option>
                        <option value="plumbing">Plumbing / Water</option>
                        <option value="furniture">Furniture</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="wifi">WiFi / Internet</option>
                        <option value="food">Food / Mess</option>
                        <option value="security">Security</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Priority</label>
                    <select id="ePriority" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                <textarea id="eDescription" rows="4" minlength="10" maxlength="2000"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none resize-y"></textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select id="eStatus" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Submitted</label>
                    <input type="text" id="eCreatedAt" disabled
                        class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-500">
                </div>
            </div>

            <div class="mb-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Admin remark</label>
                <textarea id="eAdminRemark" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                    placeholder="e.g., Plumber assigned, will visit tomorrow"></textarea>
            </div>
        </div>

        <div class="p-6 border-t border-gray-100 flex flex-wrap gap-3 justify-end">
            <button onclick="closeEdit()"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition">
                Cancel
            </button>
            <button onclick="saveEdit()" id="saveBtn"
                class="bg-blue-600 hover:bg-blue-700 text-white px-7 py-3 rounded-lg font-semibold transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save changes
            </button>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- ==================== DELETE MODAL ========================= --}}
{{-- ============================================================ --}}
<div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 text-center">
        <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash-alt text-3xl text-rose-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Delete Complaint?</h3>
        <p class="text-sm text-gray-500 mb-1">You're about to delete</p>
        <p class="font-mono font-bold text-rose-600 mb-5" id="delNo">—</p>
        <p class="text-xs text-gray-400 mb-5">This action cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDelete()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold">
                Cancel
            </button>
            <button onclick="confirmDelete()" id="delBtn"
                class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-lg font-semibold flex items-center justify-center gap-2">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- ==================== TOAST ================================= --}}
{{-- ============================================================ --}}
<div id="toast" class="hidden fixed bottom-6 right-6 z-[60] rounded-xl shadow-2xl px-5 py-4 text-white font-medium max-w-sm"></div>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-8px);} to { opacity: 1; transform: translateY(0);} }
</style>

<script>
/* ================= CONFIG ================= */
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
    || '{{ csrf_token() }}';

const ROUTES = {
    data:        "{{ route('admin.complaints.data') }}",
    stats:       "{{ route('admin.complaints.stats') }}",
    show:        "{{ url('admin/complaints') }}",
    update:      "{{ url('admin/complaints') }}",
    changeStatus:"{{ url('admin/complaints') }}",
    destroy:     "{{ url('admin/complaints') }}",
};

let currentPage = 1;
let editingId   = null;
let deletingId  = null;
let debounceT   = null;

/* ================= INIT ================= */
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadData();
});

/* ================= HELPERS ================= */
function showToast(type, msg) {
    const t = document.getElementById('toast');
    const bg = { success:'bg-emerald-600', error:'bg-rose-600', info:'bg-blue-600' }[type];
    t.className = `fixed bottom-6 right-6 z-[60] rounded-xl shadow-2xl px-5 py-4 text-white font-medium max-w-sm ${bg}`;
    t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':'info-circle'} mr-2"></i>${msg}`;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 3000);
}

function debouncedLoad() {
    clearTimeout(debounceT);
    debounceT = setTimeout(() => { currentPage = 1; loadData(); }, 400);
}

function statusBadge(s) {
    const map = {
        pending:     'bg-amber-100 text-amber-800 border-amber-200',
        in_progress: 'bg-blue-100 text-blue-800 border-blue-200',
        resolved:    'bg-emerald-100 text-emerald-800 border-emerald-200',
        rejected:    'bg-rose-100 text-rose-800 border-rose-200',
    };
    return `<span class="text-xs px-3 py-1 rounded-full font-semibold border ${map[s] || 'bg-gray-100'}">${s.replace('_',' ').toUpperCase()}</span>`;
}

function priorityBadge(p) {
    const map = {
        low:    'bg-gray-100 text-gray-700 border-gray-200',
        medium: 'bg-blue-50 text-blue-700 border-blue-200',
        high:   'bg-orange-100 text-orange-800 border-orange-200',
        urgent: 'bg-rose-100 text-rose-800 border-rose-200',
    };
    return `<span class="text-xs px-2 py-0.5 rounded-full font-semibold border ${map[p] || ''}">${(p||'').toUpperCase()}</span>`;
}

function esc(str) {
    return (str ?? '').toString()
        .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
}

/* ================= STATS ================= */
async function loadStats() {
    const params = new URLSearchParams();
    const h = document.getElementById('fHostel').value;
    if (h) params.set('hostel_id', h);

    try {
        const res = await fetch(`${ROUTES.stats}?${params}`, {
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const j = await res.json();
        if (j.success) {
            document.getElementById('statTotal').textContent    = j.data.total;
            document.getElementById('statPending').textContent  = j.data.pending;
            document.getElementById('statProgress').textContent = j.data.inProgress;
            document.getElementById('statResolved').textContent = j.data.resolved;
            document.getElementById('statRejected').textContent = j.data.rejected;
        }
    } catch (e) { console.error(e); }
}

/* ================= LOAD DATA ================= */
async function loadData(page = 1) {
    currentPage = page;
    const wrap = document.getElementById('listWrap');
    wrap.innerHTML = `<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
        <i class="fas fa-spinner fa-spin text-3xl"></i><div class="mt-2">Loading complaints…</div></div>`;

    const params = new URLSearchParams({ page });
    const map = {
        search:   'fSearch',
        hostel_id:'fHostel',
        status:   'fStatus',
        priority: 'fPriority',
        category: 'fCategory',
    };
    for (const [k, id] of Object.entries(map)) {
        const v = document.getElementById(id).value.trim();
        if (v) params.set(k, v);
    }

    try {
        const res = await fetch(`${ROUTES.data}?${params}`, {
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const j = await res.json();

        if (!j.success) throw new Error('Failed to load');

        const items = j.data.data || [];
        if (!items.length) {
            wrap.innerHTML = `<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
                <i class="fas fa-inbox text-5xl mb-3 opacity-40"></i><br>No complaints match your filters</div>`;
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        wrap.innerHTML = items.map(c => renderCard(c)).join('');
        renderPagination(j.data);

        loadStats();
    } catch (e) {
        wrap.innerHTML = `<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-rose-500">
            <i class="fas fa-exclamation-circle text-3xl mb-2"></i><br>${e.message}</div>`;
    }
}

/* ================= CARD ================= */
function renderCard(c) {
    return `
        <div class="bg-white border border-gray-200 rounded-2xl p-5 md:p-6 mb-4 hover:shadow-md transition">
            <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
                <div class="flex items-start gap-3">
                    ${c.resident_photo
                        ? `<img src="${c.resident_photo}" class="w-11 h-11 rounded-full object-cover border border-gray-200">`
                        : `<div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg">
                            ${esc(c.name).charAt(0).toUpperCase()}</div>`}
                    <div>
                        <div class="font-mono font-bold text-blue-700 text-lg">${esc(c.complaint_number)}</div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            <i class="far fa-clock mr-1"></i>${esc(c.created_at)}
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    ${statusBadge(c.status)}
                    ${priorityBadge(c.priority)}
                </div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm mb-4">
                <div><span class="text-gray-500">Resident:</span> <span class="font-medium">${esc(c.name)}</span></div>
                <div><span class="text-gray-500">Phone:</span> <span class="font-medium">${esc(c.phone)}</span></div>
                <div><span class="text-gray-500">Room:</span> <span class="font-medium">${esc(c.room_number || 'N/A')}</span></div>
                <div><span class="text-gray-500">Hostel:</span> <span class="font-medium">${esc(c.hostel_name)}</span></div>
            </div>

            <div class="flex items-center gap-2 text-sm mb-3">
                <span class="text-gray-500">Category:</span>
                <span class="font-medium capitalize">${esc(c.category)}</span>
            </div>

            <div class="text-sm bg-gray-50 p-4 rounded-lg border border-gray-100 mb-4">
                <div class="font-semibold text-gray-700 mb-1"><i class="fas fa-align-left mr-1"></i>Description</div>
                <div class="text-gray-600 whitespace-pre-wrap">${esc(c.description)}</div>
            </div>

            ${c.admin_remark ? `<div class="text-sm bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg mb-4">
                <i class="fas fa-comment-dots text-amber-600 mr-1"></i>
                <strong>Remark:</strong> ${esc(c.admin_remark)}</div>` : ''}

            <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                <button onclick="openEdit(${c.id})"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition">
                    <i class="fas fa-pen"></i> Update details
                </button>
                <button onclick="openDelete(${c.id}, '${esc(c.complaint_number)}')"
                    class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
                ${c.status !== 'resolved' ? `
                    <button onclick="quickStatus(${c.id}, 'resolved')"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition">
                        <i class="fas fa-check"></i> Mark resolved
                    </button>` : ''}
                ${c.status === 'pending' ? `
                    <button onclick="quickStatus(${c.id}, 'in_progress')"
                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition">
                        <i class="fas fa-play"></i> Start
                    </button>` : ''}
            </div>
        </div>`;
}

/* ================= PAGINATION ================= */
function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (!p || p.last_page <= 1) { el.innerHTML = ''; return; }

    let btns = '';
    for (let i = 1; i <= p.last_page; i++) {
        if (i === 1 || i === p.last_page || Math.abs(i - p.current_page) <= 1) {
            btns += `<button onclick="loadData(${i})"
                class="px-3 py-2 rounded-lg font-semibold text-sm ${i === p.current_page
                    ? 'bg-red-600 text-white' : 'bg-white border border-gray-200 hover:bg-gray-50'}">${i}</button>`;
        } else if (Math.abs(i - p.current_page) === 2) {
            btns += `<span class="px-2 text-gray-400">…</span>`;
        }
    }
    el.innerHTML = `<div class="flex gap-1 flex-wrap">${btns}</div>`;
}

/* ================= EDIT ================= */
async function openEdit(id) {
    try {
        const res = await fetch(`${ROUTES.show}/${id}`, {
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const j = await res.json();
        if (!j.success) throw new Error('Not found');

        const c = j.data;
        editingId = id;

        document.getElementById('eComplaintNo').textContent = c.complaint_number;
        document.getElementById('eName').textContent        = c.name;
        document.getElementById('ePhone').textContent       = c.phone;
        document.getElementById('eRoomHostel').textContent  = `${c.room_number || 'N/A'} · ${c.hostel_name}`;
        document.getElementById('eCategory').value          = c.category;
        document.getElementById('ePriority').value          = c.priority;
        document.getElementById('eDescription').value       = c.description;
        document.getElementById('eStatus').value            = c.status;
        document.getElementById('eAdminRemark').value       = c.admin_remark || '';
        document.getElementById('eCreatedAt').value         = c.created_at;

        document.getElementById('editModal').classList.remove('hidden');
    } catch (e) {
        showToast('error', 'Failed to load complaint');
    }
}

function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    editingId = null;
}

async function saveEdit() {
    if (!editingId) return;

    const payload = {
        category:     document.getElementById('eCategory').value,
        priority:     document.getElementById('ePriority').value,
        description:  document.getElementById('eDescription').value.trim(),
        status:       document.getElementById('eStatus').value,
        admin_remark: document.getElementById('eAdminRemark').value.trim(),
    };

    if (payload.description.length < 10) {
        showToast('error', 'Description must be at least 10 characters');
        return;
    }

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
        const res = await fetch(`${ROUTES.update}/${editingId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify(payload),
        });
        const j = await res.json();

        if (j.success) {
            showToast('success', j.message);
            closeEdit();
            loadData(currentPage);
        } else {
            showToast('error', j.message || 'Update failed');
        }
    } catch (e) {
        showToast('error', 'Network error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save changes';
    }
}

/* ================= QUICK STATUS ================= */
async function quickStatus(id, status) {
    try {
        const res = await fetch(`${ROUTES.changeStatus}/${id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ status }),
        });
        const j = await res.json();
        if (j.success) {
            showToast('success', j.message);
            loadData(currentPage);
        } else {
            showToast('error', j.message || 'Failed');
        }
    } catch (e) {
        showToast('error', 'Network error');
    }
}

/* ================= DELETE ================= */
function openDelete(id, number) {
    deletingId = id;
    document.getElementById('delNo').textContent = number;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDelete() {
    document.getElementById('deleteModal').classList.add('hidden');
    deletingId = null;
}

async function confirmDelete() {
    if (!deletingId) return;
    const btn = document.getElementById('delBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

    try {
        const res = await fetch(`${ROUTES.destroy}/${deletingId}`, {
            method: 'DELETE',
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const j = await res.json();
        if (j.success) {
            showToast('success', j.message);
            closeDelete();
            loadData(currentPage);
        } else {
            showToast('error', j.message || 'Delete failed');
        }
    } catch (e) {
        showToast('error', 'Network error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
}

/* ================= RESET ================= */
function resetFilters() {
    ['fSearch','fHostel','fStatus','fPriority','fCategory'].forEach(id => {
        document.getElementById(id).value = '';
    });
    loadData(1);
}

/* ================= ESC KEY ================= */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeEdit();
        closeDelete();
    }
});
</script>
@endsection