{{-- resources/views/admin/complaint/index.blade.php --}}
@extends('layouts.office')

@section('title', 'Complaints')

@section('content')
<style>
    /* ============ FONTS & BASE ============ */
    .cmp-root {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-feature-settings: 'cv02','cv03','cv04','cv11';
        -webkit-font-smoothing: antialiased;
    }
    .cmp-root * { box-sizing: border-box; }

    /* ============ SCROLLBARS ============ */
    .cmp-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .cmp-scroll::-webkit-scrollbar-track { background: transparent; }
    .cmp-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .cmp-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* ============ ANIMATIONS ============ */
    @keyframes cmp-fade-up {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes cmp-slide-in {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
    @keyframes cmp-pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
    .cmp-fade-up { animation: cmp-fade-up 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
    .cmp-slide-in { animation: cmp-slide-in 0.32s cubic-bezier(0.16, 1, 0.3, 1); }

    /* ============ STATUS PILLS ============ */
    .pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 10px; border-radius: 999px;
        font-size: 11px; font-weight: 600; letter-spacing: 0.01em;
        line-height: 1.4; white-space: nowrap;
    }
    .pill-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

    .pill-pending     { background: #fef3c7; color: #92400e; }
    .pill-pending .pill-dot     { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.18); }

    .pill-in_progress { background: #dbeafe; color: #1e40af; }
    .pill-in_progress .pill-dot { background: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.18); animation: cmp-pulse-ring 2s infinite; }

    .pill-resolved    { background: #d1fae5; color: #065f46; }
    .pill-resolved .pill-dot    { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.18); }

    .pill-rejected    { background: #fee2e2; color: #991b1b; }
    .pill-rejected .pill-dot    { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.18); }

    /* ============ PRIORITY CHIPS ============ */
    .prio { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; }
    .prio-low     { color: #64748b; }
    .prio-medium  { color: #0284c7; }
    .prio-high    { color: #ea580c; }
    .prio-urgent  { color: #dc2626; }
    .prio-bar { width: 3px; height: 12px; border-radius: 2px; }
    .prio-low    .prio-bar { background: #94a3b8; }
    .prio-medium .prio-bar { background: #0284c7; }
    .prio-high   .prio-bar { background: #ea580c; }
    .prio-urgent .prio-bar { background: #dc2626; }

    /* ============ TABLE ============ */
    .cmp-row { transition: background-color 0.12s ease; }
    .cmp-row:hover { background-color: #f8fafc; }
    .cmp-row:hover .cmp-actions { opacity: 1; }
    .cmp-actions { opacity: 0.55; transition: opacity 0.15s ease; }

    /* ============ DRAWER ============ */
    .drawer-panel { animation: cmp-slide-in 0.32s cubic-bezier(0.16, 1, 0.3, 1); }
    .drawer-panel.closing { transform: translateX(100%); transition: transform 0.25s ease-in; }

    /* ============ INPUTS ============ */
    .inp {
        width: 100%; padding: 9px 12px; font-size: 13px;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
        color: #0f172a; transition: all 0.15s ease; outline: none;
    }
    .inp:hover { border-color: #cbd5e1; }
    .inp:focus { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
    .inp:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

    select.inp { appearance: none; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='3'><polyline points='6 9 12 15 18 9'/></svg>"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }

    /* ============ BUTTONS ============ */
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 8px; transition: all 0.15s ease; cursor: pointer; border: none; white-space: nowrap; }
    .btn-primary { background: #0f172a; color: #fff; }
    .btn-primary:hover { background: #1e293b; }
    .btn-danger { background: #dc2626; color: #fff; }
    .btn-danger:hover { background: #b91c1c; }
    .btn-ghost { background: transparent; color: #475569; border: 1px solid #e2e8f0; }
    .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; color: #0f172a; }
    .btn-icon { width: 32px; height: 32px; padding: 0; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; color: #64748b; }
    .btn-icon:hover { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; }

    /* ============ ACTION ICONS ============ */
    .ico-btn { width: 30px; height: 30px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.12s ease; border: 1px solid transparent; }
    .ico-edit { color: #2563eb; background: #eff6ff; }
    .ico-edit:hover { background: #dbeafe; }
    .ico-resolve { color: #059669; background: #ecfdf5; }
    .ico-resolve:hover { background: #d1fae5; }
    .ico-start { color: #d97706; background: #fffbeb; }
    .ico-start:hover { background: #fef3c7; }
    .ico-del { color: #dc2626; background: #fef2f2; }
    .ico-del:hover { background: #fee2e2; }

    /* ============ SIDEBAR ACCENT ============ */
    .sidebar-item { transition: all 0.15s ease; }
    .sidebar-item:hover { background: #1e293b; color: #fff; }
    .sidebar-item.active { background: #1e293b; color: #fff; }
</style>

<div class="cmp-root min-h-screen bg-slate-50 flex">

    {{-- ============================================================ --}}
    {{-- ==================== SIDEBAR =============================== --}}
    {{-- ============================================================ --}}
    <aside class="hidden lg:flex w-[240px] flex-col bg-slate-900 text-slate-400 flex-shrink-0">
        <div class="px-5 py-6 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center">
                    <i class="fas fa-building text-white text-xs"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm leading-tight">Sanjay & Harini</div>
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider">Admin Panel</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-5 space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg">
                <i class="fas fa-chart-pie w-4 text-center"></i> Dashboard
            </a>
            <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg">
                <i class="fas fa-hotel w-4 text-center"></i> Hostels
            </a>
            <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg">
                <i class="fas fa-users w-4 text-center"></i> Residents
            </a>
            <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg">
                <i class="fas fa-credit-card w-4 text-center"></i> Payments
            </a>
            <a href="{{ route('admin.complaints.index') }}" class="sidebar-item active flex items-center gap-3 px-3 py-2.5 rounded-lg">
                <i class="fas fa-clipboard-list w-4 text-center"></i> Complaints
                <span class="ml-auto text-[10px] bg-rose-500 text-white px-1.5 py-0.5 rounded font-bold" id="sidebarBadge">0</span>
            </a>
            <div class="pt-4 mt-4 border-t border-slate-800">
                <div class="px-3 mb-2 text-[10px] text-slate-600 uppercase font-bold tracking-wider">Others</div>
                <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg">
                    <i class="fas fa-user-tie w-4 text-center"></i> Employees
                </a>
                <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg">
                    <i class="fas fa-cog w-4 text-center"></i> Settings
                </a>
            </div>
        </nav>

        <div class="px-4 py-4 border-t border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white text-xs font-semibold truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="text-[10px] text-slate-500">Administrator</div>
                </div>
                <a href="#" class="text-slate-500 hover:text-white transition"><i class="fas fa-sign-out-alt text-xs"></i></a>
            </div>
        </div>
    </aside>

    {{-- ============================================================ --}}
    {{-- ==================== MAIN ================================= --}}
    {{-- ============================================================ --}}
    <main class="flex-1 min-w-0 flex flex-col">

        {{-- ============ TOP BAR ============ --}}
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button class="lg:hidden btn-icon"><i class="fas fa-bars text-sm"></i></button>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 text-xs text-slate-400 mb-0.5">
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
                            <i class="fas fa-chevron-right text-[8px]"></i>
                            <span class="text-slate-600 font-medium">Complaints</span>
                        </div>
                        <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Complaint Management</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="loadData()" class="btn btn-ghost" title="Refresh data">
                        <i class="fas fa-sync-alt text-xs"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                    <button onclick="exportCsv()" class="btn btn-primary">
                        <i class="fas fa-download text-xs"></i>
                        <span class="hidden sm:inline">Export</span>
                    </button>
                </div>
            </div>
        </header>

        <div class="flex-1 px-6 lg:px-8 py-6">

            {{-- ============ KPI CARDS ============ --}}
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 mb-6">
                <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total</span>
                        <i class="fas fa-layer-group text-slate-300 text-sm"></i>
                    </div>
                    <div class="text-3xl font-bold text-slate-900 tabular-nums leading-none" id="statTotal">0</div>
                    <div class="text-[11px] text-slate-400 mt-2">All-time complaints</div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:0.03s">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending</span>
                        <span class="pill pill-pending !px-2 !py-0.5"><span class="pill-dot"></span></span>
                    </div>
                    <div class="text-3xl font-bold text-amber-600 tabular-nums leading-none" id="statPending">0</div>
                    <div class="text-[11px] text-slate-400 mt-2">Awaiting action</div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:0.06s">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">In Progress</span>
                        <span class="pill pill-in_progress !px-2 !py-0.5"><span class="pill-dot"></span></span>
                    </div>
                    <div class="text-3xl font-bold text-blue-600 tabular-nums leading-none" id="statProgress">0</div>
                    <div class="text-[11px] text-slate-400 mt-2">Being worked on</div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:0.09s">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Resolved</span>
                        <span class="pill pill-resolved !px-2 !py-0.5"><span class="pill-dot"></span></span>
                    </div>
                    <div class="text-3xl font-bold text-emerald-600 tabular-nums leading-none" id="statResolved">0</div>
                    <div class="text-[11px] text-slate-400 mt-2">Successfully closed</div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-4 cmp-fade-up" style="animation-delay:0.12s">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rejected</span>
                        <span class="pill pill-rejected !px-2 !py-0.5"><span class="pill-dot"></span></span>
                    </div>
                    <div class="text-3xl font-bold text-rose-600 tabular-nums leading-none" id="statRejected">0</div>
                    <div class="text-[11px] text-slate-400 mt-2">Closed as invalid</div>
                </div>
            </div>

            {{-- ============ FILTER BAR ============ --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3 mb-5">
                <div class="flex flex-col lg:flex-row gap-2">
                    <div class="relative flex-1 min-w-0">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="fSearch" oninput="debouncedLoad()"
                            class="inp !pl-9"
                            placeholder="Search by complaint #, resident name, phone or room…">
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 lg:w-auto">
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
                    <button onclick="resetFilters()" title="Reset filters"
                        class="btn btn-ghost !px-3">
                        <i class="fas fa-xmark text-xs"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </button>
                </div>
            </div>

            {{-- ============ DATA TABLE ============ --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto cmp-scroll">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/50">
                                <th class="text-left px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-[240px]">Complaint</th>
                                <th class="text-left px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Resident</th>
                                <th class="text-left px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider hidden xl:table-cell">Category</th>
                                <th class="text-left px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-[100px]">Priority</th>
                                <th class="text-left px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-[130px]">Status</th>
                                <th class="text-right px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-[160px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="listBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>

            {{-- ============ PAGINATION ============ --}}
            <div id="pagination" class="mt-5"></div>
        </div>
    </main>
</div>

{{-- ============================================================ --}}
{{-- ==================== EDIT DRAWER =========================== --}}
{{-- ============================================================ --}}
<div id="editDrawer" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" onclick="closeEdit()"></div>

    <div id="editPanel" class="drawer-panel absolute right-0 top-0 h-full w-full max-w-[560px] bg-white shadow-2xl flex flex-col">

        {{-- Drawer header --}}
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
            <button onclick="closeEdit()"
                class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-700 transition flex-shrink-0">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        {{-- Drawer body --}}
        <div class="flex-1 overflow-y-auto cmp-scroll p-6 space-y-5">

            {{-- Resident card --}}
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
                <textarea id="eDescription" rows="5" minlength="10" maxlength="2000"
                    class="inp !resize-none"></textarea>
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
                <textarea id="eAdminRemark" rows="3"
                    class="inp !resize-none"
                    placeholder="e.g. Plumber assigned — will visit by 5 PM"></textarea>
            </div>
        </div>

        {{-- Drawer footer --}}
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
            <div class="text-[11px] text-slate-400">
                <i class="fas fa-info-circle mr-1"></i> Changes are logged
            </div>
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

document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadData();
});

/* ================= HELPERS ================= */
function showToast(type, msg) {
    const t = document.getElementById('toast');
    const styles = {
        success: 'bg-slate-900',
        error:   'bg-rose-600',
        info:    'bg-slate-900',
    };
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
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

function statusPill(s) {
    const labels = {
        pending: 'Pending',
        in_progress: 'In Progress',
        resolved: 'Resolved',
        rejected: 'Rejected',
    };
    return `<span class="pill pill-${s}"><span class="pill-dot"></span>${labels[s] || s}</span>`;
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
            document.getElementById('sidebarBadge').textContent = j.data.pending;
        }
    } catch (e) { console.error(e); }
}

/* ================= LOAD DATA ================= */
async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('listBody');
    tbody.innerHTML = Array(5).fill(0).map(() => `
        <tr class="animate-pulse">
            <td class="px-5 py-4"><div class="h-3 bg-slate-100 rounded w-32"></div></td>
            <td class="px-5 py-4"><div class="h-3 bg-slate-100 rounded w-24"></div></td>
            <td class="px-5 py-4 hidden xl:table-cell"><div class="h-3 bg-slate-100 rounded w-20"></div></td>
            <td class="px-5 py-4"><div class="h-3 bg-slate-100 rounded w-12"></div></td>
            <td class="px-5 py-4"><div class="h-5 bg-slate-100 rounded-full w-20"></div></td>
            <td class="px-5 py-4"><div class="h-3 bg-slate-100 rounded w-16 ml-auto"></div></td>
        </tr>`).join('');

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
            tbody.innerHTML = `<tr><td colspan="6" class="py-20 text-center">
                <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-inbox text-xl text-slate-300"></i>
                </div>
                <div class="text-sm font-semibold text-slate-600">No complaints found</div>
                <div class="text-xs text-slate-400 mt-1">Try adjusting your filters or search query</div>
            </td></tr>`;
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = items.map(c => renderRow(c)).join('');
        renderPagination(j.data);
        loadStats();
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="6" class="py-16 text-center">
            <i class="fas fa-exclamation-triangle text-2xl text-rose-400"></i>
            <div class="text-sm text-rose-500 mt-2 font-medium">${e.message}</div>
        </td></tr>`;
    }
}

/* ================= ROW ================= */
function renderRow(c) {
    const initial = esc(c.name).charAt(0).toUpperCase();
    const avatar = c.resident_photo
        ? `<img src="${c.resident_photo}" class="w-9 h-9 rounded-full object-cover ring-2 ring-white shadow-sm">`
        : `<div class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center font-bold text-xs ring-2 ring-white shadow-sm">${initial}</div>`;

    return `
        <tr class="cmp-row">
            <td class="px-5 py-3.5 align-middle">
                <div class="flex items-center gap-3">
                    ${avatar}
                    <div class="min-w-0">
                        <div class="font-mono font-semibold text-slate-900 text-[12.5px] truncate">${esc(c.complaint_number)}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                            <i class="far fa-clock text-[9px]"></i>${esc(c.created_at)}
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-5 py-3.5 align-middle">
                <div class="text-[13px] font-semibold text-slate-800 truncate">${esc(c.name)}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 truncate">
                    <i class="fas fa-phone text-[9px] mr-1 text-slate-300"></i>${esc(c.phone)}
                    <span class="mx-1 text-slate-300">·</span>
                    <i class="fas fa-door-closed text-[9px] mr-1 text-slate-300"></i>${esc(c.room_number || 'N/A')}
                </div>
                <div class="text-[10.5px] text-slate-400 mt-0.5 truncate">
                    <i class="fas fa-hotel text-[9px] mr-1"></i>${esc(c.hostel_name)}
                </div>
            </td>
            <td class="px-5 py-3.5 align-middle hidden xl:table-cell">
                <div class="text-[12.5px] text-slate-700 font-medium capitalize">${esc(c.category)}</div>
                <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[220px]">${esc(c.description).slice(0, 55)}…</div>
            </td>
            <td class="px-5 py-3.5 align-middle">${priorityChip(c.priority)}</td>
            <td class="px-5 py-3.5 align-middle">${statusPill(c.status)}</td>
            <td class="px-5 py-3.5 align-middle text-right">
                <div class="cmp-actions inline-flex items-center gap-1">
                    <button onclick="openEdit(${c.id})" title="Edit details" class="ico-btn ico-edit">
                        <i class="fas fa-pen"></i>
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
            </td>
        </tr>`;
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
                    active
                        ? 'bg-slate-900 text-white shadow-sm'
                        : 'text-slate-600 hover:bg-slate-100'
                }">${i}</button>`;
        } else if (Math.abs(i - p.current_page) === 2) {
            nums += `<span class="px-1 text-slate-300 text-xs">…</span>`;
        }
    }

    el.innerHTML = `
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="text-[12px] text-slate-500">
                Showing <span class="font-semibold text-slate-700">${p.from || 0}</span>
                – <span class="font-semibold text-slate-700">${p.to || 0}</span>
                of <span class="font-semibold text-slate-700">${p.total}</span>
            </div>
            <div class="flex items-center gap-1">
                ${prev}
                <div class="flex items-center gap-0.5 mx-1">${nums}</div>
                ${next}
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

        document.getElementById('eComplaintNo').textContent  = c.complaint_number;
        document.getElementById('eName').textContent         = c.name;
        document.getElementById('ePhone').textContent        = c.phone;
        document.getElementById('eRoomHostel').textContent   = `${c.room_number || 'N/A'} · ${c.hostel_name}`;
        document.getElementById('eCategory').value           = c.category;
        document.getElementById('ePriority').value           = c.priority;
        document.getElementById('eDescription').value        = c.description;
        document.getElementById('eStatus').value             = c.status;
        document.getElementById('eAdminRemark').value        = c.admin_remark || '';
        document.getElementById('eCreatedAt').value          = c.created_at;

        const statusLabels = {
            pending: 'Pending',
            in_progress: 'In Progress',
            resolved: 'Resolved',
            rejected: 'Rejected',
        };
        const hs = document.getElementById('eHeaderStatus');
        hs.className = `pill pill-${c.status} !text-[10px]`;
        hs.innerHTML = `<span class="pill-dot"></span>${statusLabels[c.status] || c.status}`;

        document.getElementById('editDrawer').classList.remove('hidden');
        // trigger re-animation
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
    panel.classList.add('closing');
    setTimeout(() => {
        drawer.classList.add('hidden');
        panel.classList.remove('closing');
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

    if (payload.description.length < 10) {
        showToast('error', 'Description must be at least 10 characters');
        return;
    }

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Saving…';

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
        btn.innerHTML = '<i class="fas fa-check text-xs"></i> Save changes';
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Deleting…';

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
        btn.innerHTML = '<i class="fas fa-trash text-xs"></i> Delete';
    }
}

/* ================= RESET ================= */
function resetFilters() {
    ['fSearch','fHostel','fStatus','fPriority','fCategory'].forEach(id => {
        document.getElementById(id).value = '';
    });
    loadData(1);
}

/* ================= EXPORT (placeholder) ================= */
function exportCsv() {
    showToast('info', 'Export feature ready — wire to /admin/complaints/export');
}

/* ================= ESC ================= */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeEdit(); closeDelete(); }
});
</script>
@endsection