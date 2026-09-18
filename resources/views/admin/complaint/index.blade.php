{{-- resources/views/admin/complaint/index.blade.php --}}
@extends('layouts.office')

@section('title', 'Complaints')

@section('content')
<style>
    .cmp-root {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-feature-settings: 'cv02','cv03','cv04','cv11';
        -webkit-font-smoothing: antialiased;
    }
    .cmp-root * { box-sizing: border-box; }

    /* Status pill */
    .pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 999px;
        font-size: 10.5px; font-weight: 700; letter-spacing: 0.02em;
        line-height: 1.4; white-space: nowrap;
    }
    .pill-dot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
    .pill-pending     { background: #fef3c7; color: #92400e; }
    .pill-pending .pill-dot     { background: #f59e0b; }
    .pill-in_progress { background: #dbeafe; color: #1e40af; }
    .pill-in_progress .pill-dot { background: #3b82f6; }
    .pill-resolved    { background: #d1fae5; color: #065f46; }
    .pill-resolved .pill-dot    { background: #10b981; }
    .pill-rejected    { background: #fee2e2; color: #991b1b; }
    .pill-rejected .pill-dot    { background: #ef4444; }

    /* Priority chip */
    .prio { display: inline-flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
    .prio-low     { color: #64748b; }
    .prio-medium  { color: #0284c7; }
    .prio-high    { color: #ea580c; }
    .prio-urgent  { color: #dc2626; }
    .prio-bar { width: 3px; height: 11px; border-radius: 2px; }
    .prio-low     .prio-bar { background: #94a3b8; }
    .prio-medium  .prio-bar { background: #0284c7; }
    .prio-high    .prio-bar { background: #ea580c; }
    .prio-urgent  .prio-bar { background: #dc2626; }

    /* Complaint card */
    .c-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column;
    }
    .c-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 24px -8px rgba(15,23,42,0.10);
        transform: translateY(-2px);
    }
    .c-card-img {
        position: relative;
        height: 160px;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        overflow: hidden;
        display: flex; align-items: center; justify-content: center;
    }
    .c-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .c-card-img .no-img {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        color: #94a3b8;
    }
    .c-card-img .no-img i { font-size: 26px; }
    .c-card-img .no-img span { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }

    /* Status strip on top of image */
    .c-status-strip {
        position: absolute; top: 10px; left: 10px;
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 999px;
        font-size: 10.5px; font-weight: 700;
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.9);
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .c-priority-strip {
        position: absolute; top: 10px; right: 10px;
        padding: 4px 9px; border-radius: 999px;
        font-size: 10px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.92);
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    /* Icon buttons */
    .ico-btn {
        width: 30px; height: 30px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 11px; transition: all 0.12s ease;
        border: none; cursor: pointer;
    }
    .ico-edit { color: #2563eb; background: #eff6ff; } .ico-edit:hover { background: #dbeafe; }
    .ico-resolve { color: #059669; background: #ecfdf5; } .ico-resolve:hover { background: #d1fae5; }
    .ico-start { color: #d97706; background: #fffbeb; } .ico-start:hover { background: #fef3c7; }
    .ico-del { color: #dc2626; background: #fef2f2; } .ico-del:hover { background: #fee2e2; }

    /* Inputs */
    .inp {
        width: 100%; padding: 9px 12px; font-size: 13px;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
        color: #0f172a; transition: all 0.15s ease; outline: none;
    }
    .inp:hover { border-color: #cbd5e1; }
    .inp:focus { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
    .inp:disabled { background: #f8fafc; color: #94a3b8; }
    select.inp { appearance: none; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='3'><polyline points='6 9 12 15 18 9'/></svg>"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 12.5px; font-weight: 600; padding: 8px 14px; border-radius: 8px; transition: all 0.15s ease; cursor: pointer; border: none; white-space: nowrap; }
    .btn-primary { background: #0f172a; color: #fff; } .btn-primary:hover { background: #1e293b; }
    .btn-danger { background: #dc2626; color: #fff; } .btn-danger:hover { background: #b91c1c; }
    .btn-ghost { background: #fff; color: #475569; border: 1px solid #e2e8f0; } .btn-ghost:hover { background: #f8fafc; color: #0f172a; }

    @keyframes cmp-fade-up { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes cmp-slide-in { from { transform: translateX(100%); } to { transform: translateX(0); } }
    .cmp-fade-up { animation: cmp-fade-up 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
    .drawer-panel { animation: cmp-slide-in 0.32s cubic-bezier(0.16, 1, 0.3, 1); }

    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .cmp-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .cmp-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>

<div class="cmp-root">

    {{-- ============ PAGE HEADER ============ --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-slate-600 font-medium">Complaints</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Complaint Management</h1>
            <p class="text-sm text-slate-500 mt-1">Manage all hostel complaints across every property</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="loadData()" class="btn btn-ghost">
                <i class="fas fa-sync-alt text-xs"></i> Refresh
            </button>
            <button onclick="exportCsv()" class="btn btn-primary">
                <i class="fas fa-download text-xs"></i> Export
            </button>
        </div>
    </div>

    {{-- ============ KPI STRIP ============ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</span>
                <i class="fas fa-layer-group text-slate-300 text-sm"></i>
            </div>
            <div class="text-3xl font-bold text-slate-900 tabular-nums leading-none" id="statTotal">0</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:.03s">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pending</span>
                <span class="pill pill-pending !px-2 !py-0.5"><span class="pill-dot"></span></span>
            </div>
            <div class="text-3xl font-bold text-amber-600 tabular-nums leading-none" id="statPending">0</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:.06s">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">In Progress</span>
                <span class="pill pill-in_progress !px-2 !py-0.5"><span class="pill-dot"></span></span>
            </div>
            <div class="text-3xl font-bold text-blue-600 tabular-nums leading-none" id="statProgress">0</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:.09s">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Resolved</span>
                <span class="pill pill-resolved !px-2 !py-0.5"><span class="pill-dot"></span></span>
            </div>
            <div class="text-3xl font-bold text-emerald-600 tabular-nums leading-none" id="statResolved">0</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:.12s">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rejected</span>
                <span class="pill pill-rejected !px-2 !py-0.5"><span class="pill-dot"></span></span>
            </div>
            <div class="text-3xl font-bold text-rose-600 tabular-nums leading-none" id="statRejected">0</div>
        </div>
    </div>

    {{-- ============ FILTER BAR ============ --}}
    <div class="bg-white rounded-xl border border-slate-200 p-3 mb-6">
        <div class="flex flex-col lg:flex-row gap-2">
            <div class="relative flex-1 min-w-0">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="fSearch" oninput="debouncedLoad()"
                    class="inp !pl-9" placeholder="Search complaint #, name, phone, room…">
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <select id="fHostel" onchange="loadData()" class="inp !w-auto min-w-[130px] cursor-pointer">
                    <option value="">All hostels</option>
                    @foreach($hostels as $h)
                        <option value="{{ $h->id }}">{{ $h->hostel_name }}</option>
                    @endforeach
                </select>
                <select id="fStatus" onchange="loadData()" class="inp !w-auto min-w-[120px] cursor-pointer">
                    <option value="">All status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="fPriority" onchange="loadData()" class="inp !w-auto min-w-[110px] cursor-pointer">
                    <option value="">All priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                <select id="fCategory" onchange="loadData()" class="inp !w-auto min-w-[120px] cursor-pointer">
                    <option value="">All categories</option>
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
            <button onclick="resetFilters()" class="btn btn-ghost !px-3">
                <i class="fas fa-xmark text-xs"></i>
                <span class="hidden sm:inline">Reset</span>
            </button>
        </div>
    </div>

    {{-- ============ CARD GRID ============ --}}
    <div id="cardGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {{-- Skeleton --}}
        <div class="bg-white rounded-[14px] border border-slate-200 overflow-hidden animate-pulse">
            <div class="h-40 bg-slate-100"></div>
            <div class="p-4 space-y-2"><div class="h-3 bg-slate-100 rounded w-3/4"></div><div class="h-3 bg-slate-100 rounded w-1/2"></div></div>
        </div>
    </div>

    {{-- Pagination --}}
    <div id="pagination" class="mt-6"></div>
</div>

{{-- ============================================================ --}}
{{-- ==================== EDIT DRAWER =========================== --}}
{{-- ============================================================ --}}
<div id="editDrawer" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" onclick="closeEdit()"></div>

    <div id="editPanel" class="drawer-panel absolute right-0 top-0 h-full w-full max-w-[560px] bg-white shadow-2xl flex flex-col">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-200 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="pill pill-pending !text-[10px]" id="eHeaderStatus">
                        <span class="pill-dot"></span> Loading
                    </span>
                </div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Update Complaint</h3>
                <p class="text-xs text-slate-400 font-mono mt-0.5" id="eComplaintNo">—</p>
            </div>
            <button onclick="closeEdit()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-700 transition">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto cmp-scroll p-6 space-y-5">

            {{-- Complaint image preview --}}
            <div id="eImageWrap" class="hidden rounded-xl overflow-hidden border border-slate-200">
                <img id="eImage" class="w-full max-h-64 object-cover">
            </div>

            {{-- Resident info --}}
            <div class="rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex-1">Resident</div>
                    <i class="fas fa-user-circle text-slate-300"></i>
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <div class="text-[10px] text-slate-400 font-medium mb-0.5">NAME</div>
                        <div class="font-semibold text-slate-900 text-[13px] truncate" id="eName">—</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-medium mb-0.5">PHONE</div>
                        <div class="font-semibold text-slate-900 text-[13px] tabular-nums" id="ePhone">—</div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-[10px] text-slate-400 font-medium mb-0.5">ROOM · HOSTEL</div>
                        <div class="font-semibold text-slate-900 text-[13px]" id="eRoomHostel">—</div>
                    </div>
                </div>
            </div>

            {{-- Category + Priority --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Category</label>
                    <select id="eCategory" class="inp">
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
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Priority</label>
                    <select id="ePriority" class="inp">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Description</label>
                <textarea id="eDescription" rows="5" minlength="10" maxlength="2000" class="inp !resize-none"></textarea>
            </div>

            {{-- Status + Submitted --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select id="eStatus" class="inp">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Submitted</label>
                    <input type="text" id="eCreatedAt" disabled class="inp tabular-nums">
                </div>
            </div>

            {{-- Admin remark --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                    Admin Remark <span class="text-slate-300 font-normal normal-case tracking-normal">(visible to resident)</span>
                </label>
                <textarea id="eAdminRemark" rows="3" class="inp !resize-none"
                    placeholder="e.g. Plumber assigned — will visit by 5 PM"></textarea>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
            <div class="text-[11px] text-slate-400"><i class="fas fa-info-circle mr-1"></i> Changes are logged</div>
            <div class="flex gap-2">
                <button onclick="closeEdit()" class="btn btn-ghost">Cancel</button>
                <button onclick="saveEdit()" id="saveBtn" class="btn btn-primary !px-5">
                    <i class="fas fa-check text-xs"></i> Save changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- ==================== DELETE MODAL ========================== --}}
{{-- ============================================================ --}}
<div id="deleteModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]" onclick="closeDelete()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-[400px] p-6 cmp-fade-up">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-triangle-exclamation text-rose-600"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <h3 class="text-base font-bold text-slate-900">Delete complaint</h3>
                <p class="text-[13px] text-slate-500 mt-1">This action cannot be undone and will permanently remove the complaint from the system.</p>
                <div class="mt-3 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Complaint</div>
                    <div class="font-mono font-semibold text-slate-700 text-sm" id="delNo">—</div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 mt-6 justify-end">
            <button onclick="closeDelete()" class="btn btn-ghost">Cancel</button>
            <button onclick="confirmDelete()" id="delBtn" class="btn btn-danger">
                <i class="fas fa-trash text-xs"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- ================= TOAST ================= --}}
<div id="toast" class="hidden fixed bottom-6 right-6 z-[70] rounded-xl shadow-2xl px-4 py-3 text-white font-medium text-[13px] max-w-sm flex items-center gap-2.5"></div>

<script>
/* ================= CONFIG ================= */
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

const ROUTES = {
    data:         "{{ route('admin.complaints.data') }}",
    stats:        "{{ route('admin.complaints.stats') }}",
    show:         "{{ url('admin/complaints') }}",
    update:       "{{ url('admin/complaints') }}",
    changeStatus: "{{ url('admin/complaints') }}",
    destroy:      "{{ url('admin/complaints') }}",
};

let currentPage = 1;
let editingId   = null;
let deletingId  = null;
let debounceT   = null;

document.addEventListener('DOMContentLoaded', () => { loadStats(); loadData(); });

/* ================= HELPERS ================= */
function showToast(type, msg) {
    const t = document.getElementById('toast');
    const styles = { success:'bg-slate-900', error:'bg-rose-600', info:'bg-slate-900' };
    const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle' };
    t.className = `fixed bottom-6 right-6 z-[70] rounded-xl shadow-2xl px-4 py-3 text-white font-medium text-[13px] max-w-sm flex items-center gap-2.5 ${styles[type]}`;
    t.innerHTML = `<i class="fas fa-${icons[type]}"></i><span>${msg}</span>`;
    t.classList.remove('hidden');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.add('hidden'), 3200);
}

function debouncedLoad() {
    clearTimeout(debounceT);
    debounceT = setTimeout(() => { currentPage = 1; loadData(); }, 350);
}

function statusLabel(s) {
    return { pending:'Pending', in_progress:'In Progress', resolved:'Resolved', rejected:'Rejected' }[s] || s;
}

function statusPill(s, small) {
    return `<span class="pill pill-${s} ${small ? '!text-[10px]' : ''}"><span class="pill-dot"></span>${statusLabel(s)}</span>`;
}

function priorityChip(p) {
    return `<span class="prio prio-${p}"><span class="prio-bar"></span>${p || '—'}</span>`;
}

function esc(str) {
    return (str ?? '').toString()
        .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
        .replaceAll('"','&quot;').replaceAll("'",'&#39;');
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
    const grid = document.getElementById('cardGrid');

    // Skeleton
    grid.innerHTML = Array(8).fill(0).map(() => `
        <div class="bg-white rounded-[14px] border border-slate-200 overflow-hidden animate-pulse">
            <div class="h-40 bg-slate-100"></div>
            <div class="p-4 space-y-2">
                <div class="h-3 bg-slate-100 rounded w-3/4"></div>
                <div class="h-3 bg-slate-100 rounded w-1/2"></div>
                <div class="h-8 bg-slate-100 rounded w-full mt-3"></div>
            </div>
        </div>`).join('');

    const params = new URLSearchParams({ page });
    const map = { search:'fSearch', hostel_id:'fHostel', status:'fStatus', priority:'fPriority', category:'fCategory' };
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
            grid.innerHTML = `<div class="col-span-full bg-white rounded-2xl border border-slate-200 py-20 text-center">
                <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-inbox text-xl text-slate-300"></i>
                </div>
                <div class="text-sm font-semibold text-slate-600">No complaints found</div>
                <div class="text-xs text-slate-400 mt-1">Try adjusting your filters or search query</div>
            </div>`;
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        grid.innerHTML = items.map((c, i) => renderCard(c, i)).join('');
        renderPagination(j.data);
        loadStats();
    } catch (e) {
        grid.innerHTML = `<div class="col-span-full bg-white rounded-2xl border border-slate-200 py-16 text-center">
            <i class="fas fa-exclamation-triangle text-2xl text-rose-400"></i>
            <div class="text-sm text-rose-500 mt-2 font-medium">${e.message}</div>
        </div>`;
    }
}

/* ================= CARD ================= */
function renderCard(c, idx) {
    const initial = esc(c.name).charAt(0).toUpperCase();
    const imageBlock = c.image
        ? `<div class="c-card-img">
                <img src="${c.image}" alt="${esc(c.complaint_number)}" loading="lazy"
                    onerror="this.parentNode.innerHTML='<div class=\\'no-img\\'><i class=\\'fas fa-image\\'></i><span>Image unavailable</span></div>'">
                <div class="c-status-strip">${statusPill(c.status, true)}</div>
                <div class="c-priority-strip">${priorityChip(c.priority)}</div>
           </div>`
        : `<div class="c-card-img">
                <div class="no-img">
                    <i class="fas fa-${catIcon(c.category)}"></i>
                    <span>${esc(c.category)}</span>
                </div>
                <div class="c-status-strip">${statusPill(c.status, true)}</div>
                <div class="c-priority-strip">${priorityChip(c.priority)}</div>
           </div>`;

    return `
        <div class="c-card cmp-fade-up" style="animation-delay:${Math.min(idx * 0.03, 0.3)}s">
            ${imageBlock}

            <div class="p-4 flex-1 flex flex-col">
                {{-- Complaint number --}}
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="font-mono font-bold text-slate-900 text-[12.5px] truncate">${esc(c.complaint_number)}</div>
                    <div class="text-[10.5px] text-slate-400 whitespace-nowrap flex items-center gap-1">
                        <i class="far fa-clock text-[9px]"></i>${esc(c.created_at)}
                    </div>
                </div>

                {{-- Resident --}}
                <div class="flex items-center gap-2.5 mb-3 pb-3 border-b border-slate-100">
                    ${c.resident_photo
                        ? `<img src="${c.resident_photo}" class="w-8 h-8 rounded-full object-cover ring-2 ring-white shadow-sm">`
                        : `<div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center font-bold text-[11px] ring-2 ring-white shadow-sm">${initial}</div>`}
                    <div class="min-w-0 flex-1">
                        <div class="text-[12.5px] font-semibold text-slate-800 truncate">${esc(c.name)}</div>
                        <div class="text-[10.5px] text-slate-500 truncate">
                            ${esc(c.phone)} <span class="text-slate-300">·</span> Room ${esc(c.room_number || 'N/A')}
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="text-[12px] text-slate-600 line-clamp-2 mb-3 flex-1" title="${esc(c.description)}">
                    ${esc(c.description)}
                </div>

                {{-- Meta row --}}
                <div class="flex items-center gap-2 text-[10.5px] text-slate-500 mb-3">
                    <span class="inline-flex items-center gap-1 capitalize">
                        <i class="fas fa-tag text-[9px] text-slate-400"></i>${esc(c.category)}
                    </span>
                    <span class="text-slate-300">·</span>
                    <span class="inline-flex items-center gap-1 truncate">
                        <i class="fas fa-hotel text-[9px] text-slate-400"></i>${esc(c.hostel_name)}
                    </span>
                </div>

                {{-- Admin remark --}}
                ${c.admin_remark ? `
                    <div class="text-[11px] bg-amber-50 border-l-2 border-amber-400 px-2.5 py-1.5 rounded-r-md mb-3 text-amber-900">
                        <i class="fas fa-comment-dots text-[9px] mr-1"></i>${esc(c.admin_remark)}
                    </div>` : ''}

                {{-- Actions --}}
                <div class="flex items-center gap-1.5 pt-3 border-t border-slate-100">
                    <button onclick="openEdit(${c.id})" class="btn btn-ghost !flex-1 !text-[11.5px] !py-1.5">
                        <i class="fas fa-pen text-[10px]"></i> Update
                    </button>
                    ${c.status !== 'resolved' ? `
                        <button onclick="quickStatus(${c.id}, 'resolved')" title="Mark resolved" class="ico-btn ico-resolve">
                            <i class="fas fa-check"></i>
                        </button>` : ''}
                    ${c.status === 'pending' ? `
                        <button onclick="quickStatus(${c.id}, 'in_progress')" title="Start progress" class="ico-btn ico-start">
                            <i class="fas fa-play"></i>
                        </button>` : ''}
                    <button onclick="openDelete(${c.id}, '${esc(c.complaint_number)}')" title="Delete" class="ico-btn ico-del">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
}

function catIcon(cat) {
    const map = {
        electrical: 'bolt', plumbing: 'faucet', furniture: 'couch',
        cleaning: 'broom', wifi: 'wifi', food: 'utensils',
        security: 'shield-halved', other: 'circle-info',
    };
    return map[cat] || 'circle-info';
}

/* ================= PAGINATION ================= */
function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (!p || p.last_page <= 1) { el.innerHTML = ''; return; }

    const prev = p.current_page > 1
        ? `<button onclick="loadData(${p.current_page - 1})" class="btn btn-ghost !px-3"><i class="fas fa-chevron-left text-xs"></i></button>`
        : `<button class="btn btn-ghost !px-3 opacity-40 cursor-not-allowed" disabled><i class="fas fa-chevron-left text-xs"></i></button>`;
    const next = p.current_page < p.last_page
        ? `<button onclick="loadData(${p.current_page + 1})" class="btn btn-ghost !px-3"><i class="fas fa-chevron-right text-xs"></i></button>`
        : `<button class="btn btn-ghost !px-3 opacity-40 cursor-not-allowed" disabled><i class="fas fa-chevron-right text-xs"></i></button>`;

    let nums = '';
    for (let i = 1; i <= p.last_page; i++) {
        if (i === 1 || i === p.last_page || Math.abs(i - p.current_page) <= 1) {
            const active = i === p.current_page;
            nums += `<button onclick="loadData(${i})"
                class="min-w-[34px] h-8 px-3 rounded-lg font-semibold text-[12.5px] transition ${
                    active ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'
                }">${i}</button>`;
        } else if (Math.abs(i - p.current_page) === 2) {
            nums += `<span class="px-1 text-slate-300 text-xs">…</span>`;
        }
    }

    el.innerHTML = `
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="text-[12px] text-slate-500">
                Showing <span class="font-semibold text-slate-700">${p.from || 0}</span>–<span class="font-semibold text-slate-700">${p.to || 0}</span>
                of <span class="font-semibold text-slate-700">${p.total}</span>
            </div>
            <div class="flex items-center gap-1">
                ${prev}<div class="flex items-center gap-0.5 mx-1">${nums}</div>${next}
            </div>
        </div>`;
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

        // Image
        const imgWrap = document.getElementById('eImageWrap');
        const img     = document.getElementById('eImage');
        if (c.image) { img.src = c.image; imgWrap.classList.remove('hidden'); }
        else { img.removeAttribute('src'); imgWrap.classList.add('hidden'); }

        const hs = document.getElementById('eHeaderStatus');
        hs.className = `pill pill-${c.status} !text-[10px]`;
        hs.innerHTML = `<span class="pill-dot"></span>${statusLabel(c.status)}`;

        document.getElementById('editDrawer').classList.remove('hidden');
        const panel = document.getElementById('editPanel');
        panel.classList.remove('drawer-panel');
        void panel.offsetWidth;
        panel.classList.add('drawer-panel');
    } catch (e) {
        showToast('error', 'Failed to load complaint');
    }
}

function closeEdit() {
    const drawer = document.getElementById('editDrawer');
    const panel  = document.getElementById('editPanel');
    panel.style.transform = 'translateX(100%)';
    panel.style.transition = 'transform 0.22s ease-in';
    setTimeout(() => {
        drawer.classList.add('hidden');
        panel.style.transform = '';
        panel.style.transition = '';
    }, 220);
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
    if (payload.description.length < 10) { showToast('error', 'Description must be at least 10 characters'); return; }

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Saving…';

    try {
        const res = await fetch(`${ROUTES.update}/${editingId}`, {
            method: 'PUT',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        });
        const j = await res.json();
        if (j.success) { showToast('success', j.message); closeEdit(); loadData(currentPage); }
        else showToast('error', j.message || 'Update failed');
    } catch (e) { showToast('error', 'Network error'); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check text-xs"></i> Save changes';
    }
}

/* ================= QUICK STATUS ================= */
async function quickStatus(id, status) {
    try {
        const res = await fetch(`${ROUTES.changeStatus}/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ status }),
        });
        const j = await res.json();
        if (j.success) { showToast('success', j.message); loadData(currentPage); }
        else showToast('error', j.message || 'Failed');
    } catch (e) { showToast('error', 'Network error'); }
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Deleting…';
    try {
        const res = await fetch(`${ROUTES.destroy}/${deletingId}`, {
            method: 'DELETE', headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const j = await res.json();
        if (j.success) { showToast('success', j.message); closeDelete(); loadData(currentPage); }
        else showToast('error', j.message || 'Delete failed');
    } catch (e) { showToast('error', 'Network error'); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash text-xs"></i> Delete';
    }
}

/* ================= RESET / EXPORT ================= */
function resetFilters() {
    ['fSearch','fHostel','fStatus','fPriority','fCategory'].forEach(id => document.getElementById(id).value = '');
    loadData(1);
}
function exportCsv() { showToast('info', 'Export feature ready — wire to /admin/complaints/export'); }

/* ================= ESC ================= */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeEdit(); closeDelete(); }
});
</script>
@endsection