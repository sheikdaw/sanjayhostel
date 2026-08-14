<?php
// app/Http/Controllers/Admin/HostelController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Resident;
use App\Services\EbioServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HostelController extends Controller
{
    protected $ebioService;

    public function __construct(EbioServerService $ebioService)
    {
        $this->ebioService = $ebioService;
    }

    /**
     * Display a listing of hostels with biometric stats
     */
    public function index()
    {
        $hostels = Hostel::withCount(['residents', 'rooms'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($hostels as $hostel) {
            $hostel->beds_count = $hostel->rooms->sum(function ($room) {
                return $room->beds()->count();
            });

            $hostel->biometric_residents_count = $hostel->residents()->whereNotNull('employee_code')->count();
            $hostel->biometric_access_count = $hostel->residents()->where('biometric_access', true)->count();
        }

        return view('admin.hostels.index', compact('hostels'));
    }

    /**
     * Show biometric configuration page
     */
    public function biometricConfig()
    {
        $hostels = Hostel::with('residents')->get();
        return view('admin.hostels.biometric', compact('hostels'));
    }

    /**
     * Get hostel biometric configuration
     */
    public function getBiometricConfig($id)
    {
        try {
            $hostel = Hostel::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $hostel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Save hostel biometric configuration
     */
    public function saveBiometricConfig(Request $request, $id)
    {
        try {
            $hostel = Hostel::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'biometric_device_id' => 'required|string|max:100',
                'biometric_ip_address' => 'required|ip',
                'biometric_port' => 'nullable|string|max:10',
                'biometric_device_name' => 'nullable|string|max:255',
                'biometric_location_code' => 'nullable|string|max:100',
                'employee_code_prefix' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $hostel->update([
                'biometric_device_id' => $request->biometric_device_id,
                'biometric_device_name' => $request->biometric_device_name,
                'biometric_ip_address' => $request->biometric_ip_address,
                'biometric_port' => $request->biometric_port ?? '4370',
                'biometric_location_code' => $request->biometric_location_code,
                'employee_code_prefix' => $request->employee_code_prefix,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Biometric configuration saved successfully!',
                'data' => $hostel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync hostel residents to biometric device
     */
    public function syncHostelBiometric($id)
    {
        try {
            $hostel = Hostel::with('residents')->findOrFail($id);

            if (!$hostel->biometric_device_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biometric device not configured for this hostel!'
                ]);
            }

            // Set hostel-specific configuration
            $this->ebioService->setHostelConfig($hostel);

            $residents = $hostel->residents()->where('status', 'ACTIVE')->get();
            $synced = 0;
            $failed = 0;
            $results = [];

            foreach ($residents as $resident) {
                try {
                    // Generate employee code based on hostel
                    if (!$resident->employee_code) {
                        $resident->employee_code = $resident->generateEmployeeCode();
                    }

                    // Update resident
                    $resident->biometric_access = true;
                    $resident->last_sync_at = now();
                    $resident->save();

                    // Sync to device using hostel-specific URL
                    $result = $this->ebioService->updateEmployee([
                        'employee_code' => $resident->employee_code,
                        'employee_name' => $resident->name,
                        'location' => $hostel->biometric_location_code ?? 'LOC_001',
                        'employee_role' => 'Normal Users',
                        'verification_type' => '17',
                    ]);

                    if ($result['success']) {
                        $synced++;
                    } else {
                        $failed++;
                    }

                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'employee_code' => $resident->employee_code,
                        'status' => $result['success'] ? 'success' : 'failed',
                        'message' => $result['message'] ?? ''
                    ];

                } catch (\Exception $e) {
                    $failed++;
                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => $failed === 0,
                'message' => "Synced {$synced} residents, {$failed} failed",
                'hostel' => $hostel->hostel_name,
                'synced' => $synced,
                'failed' => $failed,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync all hostels biometric
     */
    public function syncAllHostelsBiometric()
    {
        try {
            $hostels = Hostel::whereNotNull('biometric_device_id')->get();

            $totalSynced = 0;
            $hostelsSynced = 0;
            $results = [];

            foreach ($hostels as $hostel) {
                $response = $this->syncHostelBiometric($hostel->id);
                $data = $response->getData();

                if ($data->success) {
                    $hostelsSynced++;
                    $totalSynced += $data->synced;
                    $results[] = [
                        'hostel' => $hostel->hostel_name,
                        'synced' => $data->synced,
                        'failed' => $data->failed,
                        'status' => 'success'
                    ];
                } else {
                    $results[] = [
                        'hostel' => $hostel->hostel_name,
                        'status' => 'failed',
                        'message' => $data->message
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Synced {$totalSynced} residents from {$hostelsSynced} hostels",
                'total_synced' => $totalSynced,
                'hostels_synced' => $hostelsSynced,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test biometric device connection
     */
    public function testBiometricConnection($id)
    {
        try {
            $hostel = Hostel::findOrFail($id);

            if (!$hostel->biometric_ip_address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biometric IP address not configured!'
                ]);
            }

            // Set hostel-specific configuration
            $this->ebioService->setHostelConfig($hostel);

            $result = $this->ebioService->getDeviceList();

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'Device is online and reachable!' : 'Device is offline or unreachable.',
                'device' => $hostel->biometric_device_name,
                'ip' => $hostel->biometric_ip_address
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get hostel-wise biometric stats
     */
    public function getBiometricStats()
    {
        try {
            $hostels = Hostel::withCount(['residents'])->get();

            $stats = [];
            foreach ($hostels as $hostel) {
                $stats[] = [
                    'id' => $hostel->id,
                    'name' => $hostel->hostel_name,
                    'code' => $hostel->hostel_code,
                    'type' => $hostel->hostel_type,
                    'total_residents' => $hostel->residents_count,
                    'biometric_synced' => $hostel->residents()->whereNotNull('employee_code')->count(),
                    'biometric_enabled' => $hostel->residents()->where('biometric_access', true)->count(),
                    'device_id' => $hostel->biometric_device_id,
                    'device_name' => $hostel->biometric_device_name,
                    'ip_address' => $hostel->biometric_ip_address,
                    'status' => $hostel->biometric_status,
                    'status_badge' => $hostel->biometric_status_badge,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ============================================
    // BASIC CRUD METHODS WITH UPI FIELDS
    // ✅ STORE FULL UPI URL AS-IS
    // ============================================

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hostel_code' => 'required|string|max:50|unique:hostels,hostel_code',
            'hostel_name' => 'required|string|max:255',
            'hostel_type' => 'required|in:MEN,WOMEN',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'biometric_device_id' => 'nullable|string|max:100',
            'biometric_device_name' => 'nullable|string|max:255',
            'biometric_ip_address' => 'nullable|ip',
            'biometric_port' => 'nullable|string|max:10',
            'biometric_location_code' => 'nullable|string|max:100',
            'employee_code_prefix' => 'nullable|string|max:20',
            'upi_id' => 'nullable|string|max:500', // Increased to store full URL
            'upi_payee_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // ✅ Store UPI ID as-is (full URL or whatever user entered)
        // No extraction, no validation - just store what user entered
        $data['upi_id'] = $data['upi_id'] ?? null;

        // Default payee name to hostel name if UPI ID is set but payee name is blank
        if (!empty($data['upi_id']) && empty($data['upi_payee_name'])) {
            $data['upi_payee_name'] = $data['hostel_name'];
        }

        $hostel = Hostel::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Hostel created successfully!',
            'data' => $hostel
        ]);
    }

    public function edit($id)
    {
        $hostel = Hostel::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $hostel
        ]);
    }

    public function update(Request $request, $id)
    {
        $hostel = Hostel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'hostel_code' => 'required|string|max:50|unique:hostels,hostel_code,' . $id,
            'hostel_name' => 'required|string|max:255',
            'hostel_type' => 'required|in:MEN,WOMEN',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'biometric_device_id' => 'nullable|string|max:100',
            'biometric_device_name' => 'nullable|string|max:255',
            'biometric_ip_address' => 'nullable|ip',
            'biometric_port' => 'nullable|string|max:10',
            'biometric_location_code' => 'nullable|string|max:100',
            'employee_code_prefix' => 'nullable|string|max:20',
            'upi_id' => 'nullable|string|max:500',
            'upi_payee_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // ✅ Store UPI ID as-is - no extraction or validation
        $data['upi_id'] = $data['upi_id'] ?? null;

        // Default payee name to hostel name if UPI ID is set but payee name is blank
        if (!empty($data['upi_id']) && empty($data['upi_payee_name'])) {
            $data['upi_payee_name'] = $data['hostel_name'];
        }

        // If UPI ID is cleared, clear the payee name too
        if (empty($data['upi_id'])) {
            $data['upi_payee_name'] = null;
        }

        $hostel->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Hostel updated successfully!',
            'data' => $hostel
        ]);
    }

    public function destroy($id)
    {
        $hostel = Hostel::findOrFail($id);

        $residentCount = $hostel->residents()->count();
        if ($residentCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete hostel with ' . $residentCount . ' active residents!'
            ], 400);
        }

        $hostel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hostel deleted successfully!'
        ]);
    }

    public function toggleStatus($id)
    {
        $hostel = Hostel::findOrFail($id);
        $hostel->status = $hostel->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $hostel->save();

        return response()->json([
            'success' => true,
            'message' => 'Hostel status updated successfully!',
            'data' => $hostel
        ]);
    }
}
