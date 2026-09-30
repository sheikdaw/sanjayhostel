@extends('layouts.office')

@section('title', 'Beds — Sanjay PG Hostel')
@section('page_title', 'Beds')

@push('styles')
<style>
    .bd-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .bd-page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .bd-page-title i { color: var(--sanjay-gold); }
    .bd-page-subtitle { font-size: 0.8rem; color: #6b7280; margin: 0.25rem 0 0 0; }

    .bd-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .bd-search-box { position: relative; flex: 1; min-width: 200px; max-width: 320px; }
    .bd-search-box input {
        width: 100%;
        padding: 0.55rem 1rem 0.55rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        color: #374151;
    }
    .bd-search-box input:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .bd-search-box i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
    .bd-filter-select {
        padding: 0.55rem 2rem 0.55rem 0.85rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.82rem;
        background: white;
        color: #374151;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
    }
    .bd-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
    }
    .bd-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(10, 30, 63, 0.25);
        color: white;
    }

    .bd-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 0.85rem;
    }
    .bd-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 1rem;
        transition: all 0.25s;
        position: relative;
    }
    .bd-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
        border-color: rgba(197, 160, 40, 0.3);
    }
    .bd-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.6rem;
    }
    .bd-bed-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 700;
        font-family: 'DM Mono', monospace;
    }
    .bd-bed-badge.vacant   { background: #dcfce7; color: #166534; }
    .bd-bed-badge.occupied { background: #fee2e2; color: #991b1b; }
    .bd-bed-badge.blocked  { background: #f3f4f6; color: #6b7280; }

    .bd-type-tag {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        border-radius: 20px;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .bd-type-tag.normal { background: #dbeafe; color: #1e40af; }
    .bd-type-tag.bunker { background: #ede9fe; color: #6b21a8; }

    .bd-card-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--sanjay-primary);
        margin-bottom: 0.2rem;
    }
    .bd-card-sub {
        font-size: 0.7rem;
        color: #9ca3af;
        font-family: 'DM Mono', monospace;
        margin-bottom: 0.6rem;
    }
    .bd-card-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.62rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .bd-card-status.vacant   { background: #dcfce7; color: #166534; }
    .bd-card-status.occupied { background: #fee2e2; color: #991b1b; }
    .bd-card-status.blocked  { background: #f3f4f6; color: #4b5563; }
    .bd-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    .bd-card-footer {
        display: flex;
        gap: 0.3rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
        justify-content: flex-end;
    }
    .bd-icon-btn {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.75rem;
    }
    .bd-icon-btn:hover { transform: translateY(-1px); }
    .bd-icon-btn.edit:hover   { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
    .bd-icon-btn.delete:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
    .bd-icon-btn.toggle:hover { background: #fefce8; border-color: #eab308; color: #ca8a04; }

    .bd-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border-radius: 12px;
        border: 2px dashed #e5e7eb;
    }
    .bd-empty-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(197, 160, 40, 0.1), rgba(10, 30, 63, 0.05));
        color: var(--sanjay-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1rem;
    }
    .bd-empty h5 { color: var(--sanjay-primary); font-weight: 700; margin-bottom: 0.4rem; font-size: 1rem; }
    .bd-empty p { color: #6b7280; font-size: 0.8rem; margin-bottom: 1rem; }

    /* Modal */
    .bd-modal .modal-content {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.15);
    }
    .bd-modal .modal-header {
        background: linear-gradient(135deg, var(--sanjay-primary), #1a3a6b);
        color: white;
        border: none;
        padding: 1rem 1.4rem;
    }
    .bd-modal .modal-title { font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
    .bd-modal .modal-title i { color: var(--sanjay-gold); }
    .bd-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
    .bd-modal .modal-body { padding: 1.4rem; }
    .bd-modal .modal-footer { padding: 1rem 1.4rem; border-top: 1px solid #f3f4f6; background: #fafbfc; }

    .bd-form-group { margin-bottom: 0.85rem; }
    .bd-form-label { display: block; font-size: 0.72rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem; }
    .bd-form-label .required { color: #ef4444; margin-left: 2px; }
    .bd-form-control {
        width: 100%;
        padding: 0.55rem 0.8rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.8rem;
        color: #374151;
        background: white;
    }
    .bd-form-control:focus {
        outline: none;
        border-color: var(--sanjay-gold);
        box-shadow: 0 0 0 3px rgba(197, 160, 40, 0.1);
    }
    .bd-form-control.is-invalid { border-color: #ef4444; }
    .bd-form-error { font-size: 0.68rem; color: #ef4444; margin-top: 0.2rem; display: none; }
    .bd-form-error.show { display: block; }
    .bd-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

    .bd-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }
    .bd-btn-gold { background: linear-gradient(135deg, var(--sanjay-gold), #d4af37); color: var(--sanjay-primary); }
    .bd-btn-gold:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(197, 160, 40, 0.35); }
    .bd-btn-gold:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
    .bd-btn-outline { background: white; color: #6b7280; border: 1px solid #e5e7eb; }
    .bd-btn-outline:hover { background: #f9fafb; color: #374151; }
    .bd-btn-danger { background: #ef4444; color: white; }
    .bd-btn-danger:hover { background: #dc2626; }

    .bd-spinner {
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: bd-spin 0.6s linear infinite;
        display: inline-block;
    }
    @keyframes bd-spin { to { transform: rotate(360deg); } }

    .bd-delete-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #fef2f2;
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        margin: 0 auto 1rem;
    }
</style>
@endpush

@section('content')

<div class="bd-page-header">
    <div>
        <h2 class="bd-page-title">
            <i class="bi bi-bed"></i>
            Bed Management
        </h2>
        <p class="bd-page-subtitle">Manage individual beds across all rooms</p>
    </div>
    <button type="button" class="bd-btn-primary" onclick="openCreateModal()">
        <i class="bi bi-plus-lg"></i>
        Add Bed
    </button>
</div>

<div class="bd-toolbar">
    <div class="bd-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="bdSearchInput" placeholder="Search by bed no or room...">
    </div>

    <select class="bd-filter-select" id="bdRoomFilter">
        <option value="">All Rooms</option>
        @foreach($rooms as $room)
            <option value="{{ $room->id }}">
                Room {{ $room->room_no }} — {{ $room->hostel->hostel_name ?? '' }}
            </option>
        @endforeach
    </select>

    <select class="bd-filter-select" id="bdTypeFilter">
        <option value="">All Types</option>
        <option value="NORMAL">Normal</option>
        <option value="BUNKER">Bunker</option>
    </select>

    <select class="bd-filter-select" id="bdStatusFilter">
        <option value="">All Status</option>
        <option value="VACANT">Vacant</option>
        <option value="OCCUPIED">Occupied</option>
        <option value="BLOCKED">Blocked</option>
    </select>

    <span style="margin-left:auto; font-size:0.75rem; color:#9ca3af;" id="bdCountLabel">
        {{ $beds->count() }} beds
    </span>
</div>

<div class="bd-grid" id="bdGrid">
    @forelse($beds as $bed)
    <div class="bd-card"
         data-id="{{ $bed->id }}"
         data-bed-no="{{ strtolower($bed->bed_no) }}"
         data-room-no="{{ strtolower($bed->room->room_no ?? '') }}"
         data-room-id="{{ $bed->room_id }}"
         data-type="{{ $bed->bed_type }}"
         data-status="{{ $bed->status }}">

        <div class="bd-card-head">
            <div class="bd-bed-badge {{ strtolower($bed->status) }}">
                {{ $bed->bed_no }}
            </div>
            <span class="bd-type-tag {{ strtolower($bed->bed_type) }}">
                <i class="bi bi-{{ $bed->bed_type === 'NORMAL' ? 'bed' : 'layers' }}"></i>
                {{ $bed->bed_type }}
            </span>
        </div>

        <div class="bd-card-title">Bed {{ $bed->bed_no }}</div>
        <div class="bd-card-sub">
            Room {{ $bed->room->room_no ?? 'N/A' }} •
            {{ $bed->room->hostel->hostel_name ?? 'N/A' }}
        </div>

        <span class="bd-card-status {{ strtolower($bed->status) }}">
            <span class="bd-dot"></span>
            {{ $bed->status }}
        </span>

        <div class="bd-card-footer">
            @if($bed->status !== 'OCCUPIED')
                <button type="button" class="bd-icon-btn toggle"
                        onclick="toggleStatus({{ $bed->id }})"
                        title="{{ $bed->status === 'VACANT' ? 'Block' : 'Unblock' }}">
                    <i class="bi bi-{{ $bed->status === 'VACANT' ? 'lock' : 'unlock' }}"></i>
                </button>
            @endif
            <button type="button" class="bd-icon-btn edit"
                    onclick="openEditModal({{ $bed->id }})"
                    title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="bd-icon-btn delete"
                    onclick="openDeleteModal({{ $bed->id }}, '{{ addslashes($bed->bed_no) }}')"
                    title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    @empty
    <div class="bd-empty">
        <div class="bd-empty-icon">
            <i class="bi bi-bed"></i>
        </div>
        <h5>No Beds Yet</h5>
        <p>Beds are auto-created when you add rooms. You can also add beds manually here.</p>
    </div>
    @endforelse
</div>

{{-- CREATE / EDIT MODAL --}}
<div class="modal fade bd-modal" id="bedModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bedModalTitle">
                    <i class="bi bi-bed"></i>
                    Add New Bed
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="bedForm" autocomplete="off">
                @csrf
                <input type="hidden" id="bedId" name="id" value="">

                <div class="modal-body">
                    <div class="bd-form-group">
                        <label class="bd-form-label">Room <span class="required">*</span></label>
                        <select class="bd-form-control" id="room_id" name="room_id" required>
                            <option value="">Select Room</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">
                                    Room {{ $room->room_no }} — {{ $room->hostel->hostel_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="bd-form-error" id="error_room_id"></div>
                    </div>

                    <div class="bd-form-row">
                        <div class="bd-form-group">
                            <label class="bd-form-label">Bed No <span class="required">*</span></label>
                            <input type="text" class="bd-form-control"
                                   id="bed_no" name="bed_no"
                                   placeholder="e.g., 1, A, B1" required>
                            <div class="bd-form-error" id="error_bed_no"></div>
                        </div>

                        <div class="bd-form-group">
                            <label class="bd-form-label">Type <span class="required">*</span></label>
                            <select class="bd-form-control" id="bed_type" name="bed_type" required>
                                <option value="NORMAL">Normal Cot</option>
                                <option value="BUNKER">Bunker Cot</option>
                            </select>
                            <div class="bd-form-error" id="error_bed_type"></div>
                        </div>
                    </div>

                    <div class="bd-form-group">
                        <label class="bd-form-label">Status <span class="required">*</span></label>
                        <select class="bd-form-control" id="status" name="status" required>
                            <option value="VACANT">Vacant</option>
                            <option value="OCCUPIED">Occupied</option>
                            <option value="BLOCKED">Blocked</option>
                        </select>
                        <div class="bd-form-error" id="error_status"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="bd-btn bd-btn-outline" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button type="submit" class="bd-btn bd-btn-gold" id="bedSubmitBtn">
                        <i class="bi bi-check-lg"></i>
                        <span id="bedSubmitText">Save Bed</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade bd-modal" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:1.8rem 1.4rem;">
                <div class="bd-delete-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 style="color:var(--sanjay-primary); font-weight:700; margin-bottom:0.4rem; font-size:0.95rem;">
                    Delete Bed?
                </h5>
                <p style="color:#6b7280; font-size:0.82rem; margin-bottom:1rem;">
                    You are about to delete <strong id="deleteBedName"></strong>
                </p>
                <p style="color:#ef4444; font-size:0.72rem; margin-bottom:0;">
                    <i class="bi bi-info-circle"></i>
                    Cannot delete if a resident is assigned.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:0.5rem;">
                <button type="button" class="bd-btn bd-btn-outline" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Cancel
                </button>
                <button type="button" class="bd-btn bd-btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    <span id="deleteBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL = "{{ url('admin/beds') }}";
let currentDeleteId = null;

function showToast(message, type = 'success') {
    if (typeof showFlashMessage === 'function') {
        showFlashMessage(message, type);
    } else {
        alert(message);
    }
}

function openCreateModal() {
    resetForm();
    document.getElementById('bedModalTitle').innerHTML = '<i class="bi bi-bed"></i> Add New Bed';
    document.getElementById('bedSubmitText').textContent = 'Save Bed';
    document.getElementById('bedId').value = '';
    new bootstrap.Modal(document.getElementById('bedModal')).show();
}

async function openEditModal(id) {
    resetForm();

    try {
        const response = await fetch(`${BASE_URL}/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const data = await response.json();

        if (data.success) {
            const bed = data.bed;

            document.getElementById('bedModalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Edit Bed';
            document.getElementById('bedSubmitText').textContent = 'Update Bed';
            document.getElementById('bedId').value = bed.id;

            document.getElementById('room_id').value = bed.room_id;
            document.getElementById('bed_no').value = bed.bed_no;
            document.getElementById('bed_type').value = bed.bed_type;
            document.getElementById('status').value = bed.status;

            new bootstrap.Modal(document.getElementById('bedModal')).show();
        } else {
            showToast('Failed to load bed', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to load bed', 'error');
    }
}

function resetForm() {
    document.getElementById('bedForm').reset();
    document.getElementById('bedId').value = '';

    document.querySelectorAll('.bd-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.bd-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

document.getElementById('bedForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('bedSubmitBtn');
    const submitText = document.getElementById('bedSubmitText');
    const originalText = submitText.textContent;
    const bedId = document.getElementById('bedId').value;
    const isEdit = bedId !== '';

    document.querySelectorAll('.bd-form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.bd-form-control').forEach(el => {
        el.classList.remove('is-invalid');
    });

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="bd-spinner"></span> Saving...';

    const formData = new FormData(this);
    const url = isEdit ? `${BASE_URL}/${bedId}` : BASE_URL;

    if (isEdit) formData.append('_method', 'PUT');

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('bedModal')).hide();
            setTimeout(() => window.location.reload(), 700);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errEl = document.getElementById(`error_${field}`);
                    const inputEl = document.getElementById(field);
                    if (errEl) { errEl.textContent = data.errors[field][0]; errEl.classList.add('show'); }
                    if (inputEl) inputEl.classList.add('is-invalid');
                });
                showToast('Please fix the errors', 'error');
            } else {
                showToast(data.message || 'Something went wrong', 'error');
            }
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    } finally {
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
});

function openDeleteModal(id, bedNo) {
    currentDeleteId = id;
    document.getElementById('deleteBedName').textContent = `Bed ${bedNo}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!currentDeleteId) return;

    const btn = this;
    const btnText = document.getElementById('deleteBtnText');
    const originalText = btnText.textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="bd-spinner"></span> Deleting...';

    try {
        const response = await fetch(`${BASE_URL}/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.message || 'Failed to delete', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false;
        btnText.textContent = originalText;
        currentDeleteId = null;
    }
});

async function toggleStatus(id) {
    try {
        const response = await fetch(`${BASE_URL}/${id}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.message || 'Failed to update', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Network error', 'error');
    }
}

// Filters
const searchInput = document.getElementById('bdSearchInput');
const roomFilter = document.getElementById('bdRoomFilter');
const typeFilter = document.getElementById('bdTypeFilter');
const statusFilter = document.getElementById('bdStatusFilter');

function applyFilters() {
    const search = searchInput.value.toLowerCase().trim();
    const roomId = roomFilter.value;
    const type = typeFilter.value;
    const status = statusFilter.value;

    let visible = 0;

    document.querySelectorAll('.bd-card').forEach(card => {
        const bedNo = card.getAttribute('data-bed-no');
        const roomNo = card.getAttribute('data-room-no');
        const cardRoomId = card.getAttribute('data-room-id');
        const cardType = card.getAttribute('data-type');
        const cardStatus = card.getAttribute('data-status');

        const matchSearch = !search || bedNo.includes(search) || roomNo.includes(search);
        const matchRoom = !roomId || cardRoomId === roomId;
        const matchType = !type || cardType === type;
        const matchStatus = !status || cardStatus === status;

        if (matchSearch && matchRoom && matchType && matchStatus) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    const total = document.querySelectorAll('.bd-card').length;
    const label = document.getElementById('bdCountLabel');
    label.textContent = visible !== total ? `${visible} of ${total} beds` : `${total} beds`;
}

searchInput.addEventListener('input', applyFilters);
roomFilter.addEventListener('change', applyFilters);
typeFilter.addEventListener('change', applyFilters);
statusFilter.addEventListener('change', applyFilters);
</script>
@endpush
