@extends('layouts.office')

@section('title', 'Hostel Biometric Configuration')
@section('page_title', 'Hostel Biometric Configuration')

@push('styles')
    <style>
        .biometric-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .biometric-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .biometric-card .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .device-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .device-status.online {
            background: #dcfce7;
            color: #166534;
        }

        .device-status.offline {
            background: #fee2e2;
            color: #991b1b;
        }

        .device-status.configuring {
            background: #fef3c7;
            color: #92400e;
        }

        .device-status .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .device-status.online .dot {
            background: #22c55e;
        }

        .device-status.offline .dot {
            background: #ef4444;
        }

        .device-status.configuring .dot {
            background: #f59e0b;
        }

        .stat-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .stat-mini-item {
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 6px;
            text-align: center;
        }

        .stat-mini-item .number {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-mini-item .label {
            font-size: 0.6rem;
            color: #6b7280;
            text-transform: uppercase;
        }

        .employee-code-preview {
            background: #f3f4f6;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--primary);
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4><i class="bi bi-fingerprint"></i> Hostel Biometric Configuration</h4>
                <p class="text-muted">Configure biometric devices for each hostel</p>
            </div>
            <button class="btn btn-primary" onclick="syncAllHostels()">
                <i class="bi bi-cloud-upload"></i> Sync All Hostels
            </button>
        </div>

        {{-- Summary Stats --}}
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="number">{{ $hostels->count() }}</div>
                <div class="label">Total Hostels</div>
            </div>
            <div class="stat-card" style="border-color: #22c55e;">
                <div class="number" style="color: #22c55e;">{{ $hostels->whereNotNull('biometric_device_id')->count() }}</div>
                <div class="label">Configured</div>
            </div>
            <div class="stat-card" style="border-color: #3b82f6;">
                <div class="number" style="color: #3b82f6;">{{ $hostels->whereNotNull('biometric_ip_address')->count() }}</div>
                <div class="label">With IP</div>
            </div>
            <div class="stat-card" style="border-color: #f59e0b;">
                <div class="number" style="color: #f59e0b;">{{ $hostels->whereNull('biometric_device_id')->count() }}</div>
                <div class="label">Not Configured</div>
            </div>
        </div>

        {{-- Hostel Cards --}}
        <div class="row g-4">
            @foreach ($hostels as $hostel)
                <div class="col-lg-6 col-xl-4">
                    <div class="biometric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">
                                    <i class="bi bi-building"></i> {{ $hostel->hostel_name }}
                                    <span class="badge bg-{{ $hostel->hostel_type == 'MEN' ? 'info' : 'pink' }}">{{ $hostel->type_label }}</span>
                                </h5>
                                <p class="text-muted small">Code: {{ $hostel->hostel_code }}</p>
                            </div>
                            <span class="device-status {{ $hostel->biometric_ip_address ? 'online' : 'configuring' }}">
                                <span class="dot"></span>
                                {{ $hostel->biometric_status }}
                            </span>
                        </div>

                        {{-- Device Info --}}
                        <div class="mt-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-muted">Device ID</small>
                                    <div class="fw-bold">{{ $hostel->biometric_device_id ?? 'Not Set' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Device Name</small>
                                    <div class="fw-bold">{{ $hostel->biometric_device_name ?? 'Not Set' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">IP Address</small>
                                    <div class="fw-bold">{{ $hostel->biometric_ip_address ?? 'Not Set' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Port</small>
                                    <div class="fw-bold">{{ $hostel->biometric_port ?? '4370' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Location Code</small>
                                    <div class="fw-bold">{{ $hostel->biometric_location_code ?? 'Not Set' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Employee Code Prefix</small>
                                    <div class="fw-bold">{{ $hostel->employee_code_prefix ?? 'H' . $hostel->id }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Employee Code Preview --}}
                        <div class="mt-3">
                            <small class="text-muted">Employee Code Format</small>
                            <div class="employee-code-preview">
                                {{ ($hostel->employee_code_prefix ?? 'H' . $hostel->id) . '-' . date('y') . '-XXXXXX' }}
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="stat-mini mt-3">
                            <div class="stat-mini-item">
                                <div class="number">{{ $hostel->residents->count() }}</div>
                                <div class="label">Total Residents</div>
                            </div>
                            <div class="stat-mini-item">
                                <div class="number" style="color: #22c55e;">
                                    {{ $hostel->residents->whereNotNull('employee_code')->count() }}
                                </div>
                                <div class="label">Synced</div>
                            </div>
                            <div class="stat-mini-item">
                                <div class="number" style="color: #3b82f6;">
                                    {{ $hostel->residents->where('biometric_access', true)->count() }}
                                </div>
                                <div class="label">Access Enabled</div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-primary" onclick="configureHostel({{ $hostel->id }})">
                                <i class="bi bi-gear"></i> Configure
                            </button>
                            <button class="btn btn-sm btn-success" onclick="syncHostel({{ $hostel->id }})">
                                <i class="bi bi-cloud-upload"></i> Sync
                            </button>
                            <button class="btn btn-sm btn-info" onclick="testConnection({{ $hostel->id }})">
                                <i class="bi bi-wifi"></i> Test
                            </button>
                            <a href="{{ route('admin.hostels.edit', $hostel->id) }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Configure Modal --}}
    <div class="modal fade" id="configureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-gear"></i> Configure Biometric Device</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="configureForm">
                    @csrf
                    <input type="hidden" id="configHostelId" name="hostel_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Device ID <span class="text-danger">*</span></label>
                            <input type="text" name="biometric_device_id" id="biometric_device_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Device Name</label>
                            <input type="text" name="biometric_device_name" id="biometric_device_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">IP Address <span class="text-danger">*</span></label>
                            <input type="text" name="biometric_ip_address" id="biometric_ip_address" class="form-control" placeholder="192.168.1.100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Port</label>
                            <input type="text" name="biometric_port" id="biometric_port" class="form-control" value="4370">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location Code</label>
                            <input type="text" name="biometric_location_code" id="biometric_location_code" class="form-control" placeholder="LOC_001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Employee Code Prefix</label>
                            <input type="text" name="employee_code_prefix" id="employee_code_prefix" class="form-control" placeholder="H1">
                            <small class="text-muted">Example: H1-24-000001</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function configureHostel(id) {
            $('#configHostelId').val(id);
            $('#biometric_device_id').val('');
            $('#biometric_device_name').val('');
            $('#biometric_ip_address').val('');
            $('#biometric_port').val('4370');
            $('#biometric_location_code').val('');
            $('#employee_code_prefix').val('');

            // Load existing configuration
            $.ajax({
                url: '/admin/hostels/' + id + '/biometric-config',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#biometric_device_id').val(data.biometric_device_id || '');
                        $('#biometric_device_name').val(data.biometric_device_name || '');
                        $('#biometric_ip_address').val(data.biometric_ip_address || '');
                        $('#biometric_port').val(data.biometric_port || '4370');
                        $('#biometric_location_code').val(data.biometric_location_code || '');
                        $('#employee_code_prefix').val(data.employee_code_prefix || 'H' + data.id);
                    }
                }
            });

            const modal = new bootstrap.Modal(document.getElementById('configureModal'));
            modal.show();
        }

        $('#configureForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#configHostelId').val();
            const formData = new FormData(this);

            $.ajax({
                url: '/admin/hostels/' + id + '/biometric-config',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#configureModal').modal('hide');
                        showToast('Biometric configuration saved successfully!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to save configuration!', 'error');
                }
            });
        });

        function syncHostel(id) {
            Swal.fire({
                title: 'Sync Hostel Residents?',
                text: "This will sync all residents of this hostel to the biometric device.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, sync them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/hostels/' + id + '/sync-biometric',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                showToast('Synced ' + response.synced + ' residents successfully!', 'success');
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showToast(response.message || 'Failed to sync!', 'error');
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON?.message || 'Failed to sync!', 'error');
                        }
                    });
                }
            });
        }

        function syncAllHostels() {
            Swal.fire({
                title: 'Sync All Hostels?',
                text: "This will sync all residents from all hostels to their respective biometric devices.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, sync all!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/hostels/sync-all-biometric',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                showToast('Synced ' + response.total_synced + ' residents from ' + response.hostels_synced + ' hostels!', 'success');
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showToast(response.message || 'Failed to sync!', 'error');
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON?.message || 'Failed to sync!', 'error');
                        }
                    });
                }
            });
        }

        function testConnection(id) {
            Swal.fire({
                title: 'Testing Connection...',
                text: 'Please wait while we test the biometric device connection.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admin/hostels/' + id + '/test-connection',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Connected ✅',
                            text: 'Device is online and reachable!',
                            confirmButtonColor: '#22c55e'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Failed ❌',
                            text: response.message || 'Unable to connect to device.',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Failed ❌',
                        text: xhr.responseJSON?.message || 'Unable to connect to device.',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('flashMessageContainer');
            if (!container) {
                const newContainer = document.createElement('div');
                newContainer.id = 'flashMessageContainer';
                newContainer.className = 'toast-container';
                document.body.appendChild(newContainer);
            }

            const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
            const color = type === 'success' ? '#22c55e' : '#dc2626';

            const toast = document.createElement('div');
            toast.className = 'toast-custom ' + (type === 'error' ? 'error' : '');
            toast.innerHTML = `
                <i class="bi ${icon}" style="color: ${color}; font-size: 1.25rem;"></i>
                <div class="message">${message}</div>
                <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
            `;
            document.getElementById('flashMessageContainer').appendChild(toast);

            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'slideOutRight 0.3s ease forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }
    </script>
@endsection