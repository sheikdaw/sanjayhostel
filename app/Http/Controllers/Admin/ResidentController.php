<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Hostel;
use App\Models\Resident;
use App\Models\Room;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ResidentController extends Controller
{
    /**
     * Display a listing of residents with full details
     */
    public function index()
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
            $residents = Resident::with(['hostel', 'room', 'bed', 'room.roomType'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
            $residents = Resident::with(['hostel', 'room', 'bed', 'room.roomType'])
                ->whereIn('hostel_id', $hostelIds)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get statistics
        $stats = [
            'total' => $residents->count(),
            'active' => $residents->where('status', 'ACTIVE')->count(),
            'vacated' => $residents->where('status', 'VACATED')->count(),
            'male' => $residents->filter(function ($r) {
                return $r->hostel && $r->hostel->hostel_type == 'MEN';
            })->count(),
            'female' => $residents->filter(function ($r) {
                return $r->hostel && $r->hostel->hostel_type == 'WOMEN';
            })->count(),
            'with_food' => $residents->where('food_status', 'WITH_FOOD')->where('status', 'ACTIVE')->count(),
            'without_food' => $residents->where('food_status', 'WITHOUT_FOOD')->where('status', 'ACTIVE')->count(),
            'total_rent' => $residents->where('status', 'ACTIVE')->sum('rent_amount'),
        ];

        // Biometric statistics
        $biometricStats = [
            'total' => $residents->count(),
            'synced' => $residents->whereNotNull('employee_code')->count(),
            'access_enabled' => $residents->where('biometric_access', true)->count(),
            'access_disabled' => $residents->where('biometric_access', false)->count(),
            'never_synced' => $residents->whereNull('employee_code')->count()
        ];

        return view('admin.residents.index', compact('residents', 'hostels', 'stats', 'user', 'biometricStats'));
    }

    /**
     * Export filtered residents to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        // Build query based on filters
        $query = Resident::with(['hostel', 'room', 'bed']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by hostel
        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        // Filter by gender (based on hostel type)
        if ($request->filled('gender')) {
            $hostelIds = Hostel::where('hostel_type', $request->gender)->pluck('id');
            $query->whereIn('hostel_id', $hostelIds);
        }

        // Filter by food status
        if ($request->filled('food_status')) {
            $query->where('food_status', $request->food_status);
        }

        // Filter by biometric status
        if ($request->filled('biometric_status')) {
            if ($request->biometric_status == 'enabled') {
                $query->where('biometric_access', true);
            } elseif ($request->biometric_status == 'disabled') {
                $query->where('biometric_access', false);
            } elseif ($request->biometric_status == 'not_synced') {
                $query->whereNull('employee_code');
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('resident_code', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('employee_code', 'LIKE', "%{$search}%");
            });
        }

        // Apply user role restriction
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereIn('hostel_id', $hostelIds);
        }

        // Order by
        $query->orderBy('name');

        $residents = $query->get();

        // If no residents found, return empty CSV with headers
        if ($residents->isEmpty()) {
            $csv = "\xEF\xBB\xBF";
            $csv .= "ID,Resident Code,Name,Phone,Parents Phone,Email,Hostel,Hostel Type,Room,Bed,Bed Type,Food Status,Joining Date,Vacate Date,Rent (₹),Deposit (₹),Status,Employee Code,Biometric Access,Last Sync\n";
            $csv .= "No data found matching the filters,,,,,,,,,,,,,,,,,,,\n";

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="residents-' . date('Y-m-d') . '.csv"');
        }

        // Build CSV content
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "ID,Resident Code,Name,Phone,Parents Phone,Email,Hostel,Hostel Type,Room,Bed,Bed Type,Food Status,Joining Date,Vacate Date,Rent (₹),Deposit (₹),Status,Employee Code,Biometric Access,Last Sync\n";

        foreach ($residents as $resident) {
            $hostelName = $resident->hostel ? $resident->hostel->hostel_name : 'N/A';
            $hostelType = $resident->hostel ? $resident->hostel->hostel_type : 'N/A';
            $roomNo = $resident->room ? $resident->room->room_no : 'N/A';
            $bedNo = $resident->bed ? $resident->bed->bed_no : 'N/A';
            $bedType = $resident->bed ? $resident->bed->bed_type : 'N/A';
            $foodLabel = $resident->food_status == 'WITH_FOOD' ? 'With Food' : 'Without Food';
            $biometricAccess = $resident->biometric_access ? 'Enabled' : 'Disabled';
            $lastSync = $resident->last_sync_at ? $resident->last_sync_at->format('Y-m-d H:i:s') : 'Never';

            $csv .= $this->csvEscape($resident->id) . ",";
            $csv .= $this->csvEscape($resident->resident_code) . ",";
            $csv .= $this->csvEscape($resident->name) . ",";
            $csv .= $this->csvEscape($resident->phone) . ",";
            $csv .= $this->csvEscape($resident->parentsphone ?? '') . ",";
            $csv .= $this->csvEscape($resident->email ?? '') . ",";
            $csv .= $this->csvEscape($hostelName) . ",";
            $csv .= $this->csvEscape($hostelType) . ",";
            $csv .= $this->csvEscape($roomNo) . ",";
            $csv .= $this->csvEscape($bedNo) . ",";
            $csv .= $this->csvEscape($bedType) . ",";
            $csv .= $this->csvEscape($foodLabel) . ",";
            $csv .= $this->csvEscape($resident->joining_date ? $resident->joining_date->format('Y-m-d') : '') . ",";
            $csv .= $this->csvEscape($resident->vacate_date ? $resident->vacate_date->format('Y-m-d') : '') . ",";
            $csv .= $this->csvEscape(number_format($resident->rent_amount ?? 0, 2)) . ",";
            $csv .= $this->csvEscape(number_format($resident->deposit_amount ?? 0, 2)) . ",";
            $csv .= $this->csvEscape($resident->status) . ",";
            $csv .= $this->csvEscape($resident->employee_code ?? 'Not Synced') . ",";
            $csv .= $this->csvEscape($biometricAccess) . ",";
            $csv .= $this->csvEscape($lastSync) . "\n";
        }

        // Generate filename with filters info
        $filename = 'residents';
        if ($request->filled('status')) {
            $filename .= '-' . strtolower($request->status);
        }
        if ($request->filled('hostel_id')) {
            $hostel = Hostel::find($request->hostel_id);
            if ($hostel) {
                $filename .= '-' . $hostel->hostel_code;
            }
        }
        $filename .= '-' . date('Y-m-d') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'public')
            ->header('Cache-Control', 'max-age=86400');
    }

    /**
     * Export filtered residents to Excel (optional - if you want to add later)
     */
    public function exportExcel(Request $request)
    {
        return $this->export($request);
    }

    /**
     * Get full resident details including all information
     */
    public function getResidentDetails($id)
    {
        try {
            $resident = Resident::with(['hostel', 'room', 'bed', 'room.roomType'])
                ->findOrFail($id);

            $currentMonth = now()->month;
            $currentYear = now()->year;
            $currentPayment = Payment::where('resident_id', $id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            $paymentHistory = Payment::where('resident_id', $id)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get();

            $details = [
                'id' => $resident->id,
                'resident_code' => $resident->resident_code,
                'name' => $resident->name,
                'phone' => $resident->phone,
                'parents_phone' => $resident->parentsphone,
                'email' => $resident->email,
                'aadhaar_no' => $resident->aadhaar_no,
                'address' => $resident->address,
                'dob' => $resident->dob ? $resident->dob->format('Y-m-d') : null,
                'dob_formatted' => $resident->formatted_dob,
                'age' => $resident->age,
                'profile_image' => $resident->profile_image_url,
                'profile_image_thumb' => $resident->profile_image_thumb,
                'initials' => $resident->initials,

                'biometric' => [
                    'employee_code' => $resident->employee_code ?? 'Not Generated',
                    'access_enabled' => $resident->biometric_access ?? false,
                    'access_status' => $resident->biometric_status,
                    'status_class' => $resident->biometric_status_class,
                    'status_icon' => $resident->biometric_status_icon,
                    'last_sync_at' => $resident->last_sync_at ? $resident->last_sync_at->format('Y-m-d H:i:s') : 'Never',
                    'access_enabled_at' => $resident->access_enabled_at ? $resident->access_enabled_at->format('Y-m-d H:i:s') : null,
                    'access_disabled_at' => $resident->access_disabled_at ? $resident->access_disabled_at->format('Y-m-d H:i:s') : null,
                    'is_synced' => $resident->is_synced,
                    'has_access' => $resident->has_biometric_access,
                ],

                'hostel' => [
                    'id' => $resident->hostel_id,
                    'name' => $resident->hostel->hostel_name ?? 'N/A',
                    'code' => $resident->hostel->hostel_code ?? 'N/A',
                    'type' => $resident->hostel->hostel_type ?? 'N/A',
                    'type_icon' => ($resident->hostel->hostel_type ?? '') == 'MEN' ? '👨' : '👩',
                ],
                'room' => [
                    'id' => $resident->room_id,
                    'number' => $resident->room->room_no ?? 'N/A',
                    'type' => $resident->room->roomType->room_type_name ?? 'N/A',
                    'floor' => $resident->room->floor ?? 'N/A',
                    'status' => $resident->room->status ?? 'N/A',
                ],
                'bed' => [
                    'id' => $resident->bed_id,
                    'number' => $resident->bed->bed_no ?? 'N/A',
                    'type' => $resident->bed->bed_type ?? 'N/A',
                    'status' => $resident->bed->status ?? 'N/A',
                ],

                'financial' => [
                    'rent_amount' => $resident->rent_amount,
                    'rent_formatted' => $resident->formatted_rent,
                    'deposit_amount' => $resident->deposit_amount,
                    'deposit_formatted' => $resident->formatted_deposit,
                    'food_status' => $resident->food_status,
                    'food_status_label' => $resident->food_status_label,
                    'food_status_badge' => $resident->food_status_badge,
                    'food_status_icon' => $resident->food_status_icon,
                ],

                'status' => [
                    'value' => $resident->status,
                    'label' => $resident->status_label,
                    'badge' => $resident->status_badge,
                    'joining_date' => $resident->joining_date ? $resident->joining_date->format('Y-m-d') : null,
                    'joining_date_formatted' => $resident->joining_date ? $resident->joining_date->format('d M Y') : 'N/A',
                    'vacate_date' => $resident->vacate_date ? $resident->vacate_date->format('Y-m-d') : null,
                    'vacate_date_formatted' => $resident->vacate_date ? $resident->vacate_date->format('d M Y') : 'N/A',
                ],

                'documents' => [
                    'profile_image' => [
                        'exists' => !is_null($resident->profile_image),
                        'url' => $resident->profile_image_url,
                        'name' => 'Profile Image',
                        'icon' => 'bi-person-circle',
                    ],
                    'aadhar_document' => [
                        'exists' => !is_null($resident->aadhar_document),
                        'url' => $resident->aadhar_document_url,
                        'name' => 'Aadhar Document',
                        'icon' => $resident->getDocumentIcon('aadhar_document'),
                    ],
                    'application_document' => [
                        'exists' => !is_null($resident->application_document),
                        'url' => $resident->application_document_url,
                        'name' => 'Application Document',
                        'icon' => $resident->getDocumentIcon('application_document'),
                    ],
                ],

                'current_payment' => $currentPayment ? [
                    'month' => $currentPayment->month,
                    'year' => $currentPayment->year,
                    'month_name' => date('F', mktime(0, 0, 0, $currentPayment->month, 1)),
                    'rent_amount' => $currentPayment->rent_amount,
                    'cash_paid' => $currentPayment->cash_paid_amount ?? 0,
                    'upi_paid' => $currentPayment->upi_paid_amount ?? 0,
                    'total_paid' => ($currentPayment->cash_paid_amount ?? 0) + ($currentPayment->upi_paid_amount ?? 0),
                    'balance' => $currentPayment->balance_amount ?? 0,
                    'status' => $currentPayment->status,
                    'status_label' => $currentPayment->status == 'PAID' ? 'Paid ✅' : 'Pending ❌',
                ] : null,
                'payment_history' => $paymentHistory->map(function ($payment) {
                    return [
                        'month' => $payment->month,
                        'year' => $payment->year,
                        'month_name' => date('F', mktime(0, 0, 0, $payment->month, 1)),
                        'rent_amount' => $payment->rent_amount,
                        'total_paid' => ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0),
                        'balance' => $payment->balance_amount ?? 0,
                        'status' => $payment->status,
                        'status_label' => $payment->status == 'PAID' ? '✅' : '❌',
                    ];
                }),

                'created_at' => $resident->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $resident->created_at->format('d M Y, h:i A'),
                'updated_at' => $resident->updated_at->format('Y-m-d H:i:s'),
                'updated_at_formatted' => $resident->updated_at->format('d M Y, h:i A'),
            ];

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resident
     */
    public function edit($id)
    {
        try {
            $user = auth()->user();
            $resident = Resident::with(['hostel', 'room', 'bed', 'room.roomType'])
                ->findOrFail($id);

            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($resident->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to edit this resident!'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $resident
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a newly created resident
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($request->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add residents to this hostel!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'parentsphone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'aadhaar_no' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'dob' => 'nullable|date|before:today|after:1900-01-01',
            'rent_amount' => 'required|numeric|min:0',
            'food_status' => 'required|in:WITH_FOOD,WITHOUT_FOOD',
            'joining_date' => 'required|date',
            'deposit_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,VACATED',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'aadhar_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'application_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Check if bed is available
            $bed = Bed::find($request->bed_id);

            // Check if bed is truly vacant - ONLY ACTIVE residents occupy beds
            $activeResident = Resident::where('bed_id', $request->bed_id)
                ->where('status', 'ACTIVE')
                ->first();

            if ($bed->status !== 'VACANT' || $activeResident) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Selected bed is not vacant!'
                ], 400);
            }

            // Check if room belongs to hostel
            $room = Room::find($request->room_id);
            if ($room->hostel_id != $request->hostel_id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Selected room does not belong to the selected hostel!'
                ], 400);
            }

            // Check if bed belongs to room
            if ($bed->room_id != $request->room_id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Selected bed does not belong to the selected room!'
                ], 400);
            }

            // Generate resident code
            $hostel = Hostel::find($request->hostel_id);
            $prefix = strtoupper(substr($hostel->hostel_code, 0, 3));
            $year = date('Y');
            $random = strtoupper(Str::random(4));
            $code = 'RES-' . $prefix . '-' . $year . '-' . $random;

            while (Resident::where('resident_code', $code)->exists()) {
                $random = strtoupper(Str::random(4));
                $code = 'RES-' . $prefix . '-' . $year . '-' . $random;
            }

            // Handle file uploads
            $residentData = $request->except(['profile_image', 'aadhar_document', 'application_document']);
            $residentData['resident_code'] = $code;

            if ($request->hasFile('profile_image')) {
                $path = $this->uploadFile($request->file('profile_image'), 'profile');
                if ($path) {
                    $residentData['profile_image'] = $path;
                }
            }

            if ($request->hasFile('aadhar_document')) {
                $path = $this->uploadFile($request->file('aadhar_document'), 'documents/aadhar');
                if ($path) {
                    $residentData['aadhar_document'] = $path;
                }
            }

            if ($request->hasFile('application_document')) {
                $path = $this->uploadFile($request->file('application_document'), 'documents/application');
                if ($path) {
                    $residentData['application_document'] = $path;
                }
            }

            // Create resident
            $resident = Resident::create($residentData);

            // Generate employee code for biometric
            $employeeCode = $resident->generateEmployeeCode();
            $resident->employee_code = $employeeCode;
            $resident->biometric_access = true;
            $resident->last_sync_at = now();
            $resident->save();

            // ============================================
            // CRITICAL: Update bed status based on resident status
            // Resident.ACTIVE → Bed.OCCUPIED
            // Resident.VACATED → Bed.VACANT
            // ============================================
            if ($resident->status === 'ACTIVE') {
                $bed->update(['status' => 'OCCUPIED']);
            } else {
                $bed->update(['status' => 'VACANT']);
            }

            // Update room status
            $this->updateRoomStatus($room);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resident registered successfully! Resident Code: ' . $code,
                'data' => $resident
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create resident: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resident
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $resident = Resident::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this resident!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'parentsphone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'aadhaar_no' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'dob' => 'nullable|date|before:today|after:1900-01-01',
            'rent_amount' => 'required|numeric|min:0',
            'food_status' => 'required|in:WITH_FOOD,WITHOUT_FOOD',
            'joining_date' => 'required|date',
            'vacate_date' => 'nullable|date|after_or_equal:joining_date',
            'deposit_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,VACATED',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'aadhar_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'application_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $oldBed = null;
            $oldRoom = null;
            $bedChanged = false;

            // ============================================
            // 1. HANDLE BED CHANGE
            // ============================================
            if ($resident->bed_id != $request->bed_id) {
                $bedChanged = true;

                // Free old bed - VACATED resident or no resident = VACANT
                $oldBed = Bed::find($resident->bed_id);
                if ($oldBed) {
                    $oldBed->update(['status' => 'VACANT']);
                }

                // Check new bed availability - ONLY ACTIVE residents occupy beds
                $newBed = Bed::find($request->bed_id);

                // Check if new bed is truly vacant
                $activeResident = Resident::where('bed_id', $request->bed_id)
                    ->where('status', 'ACTIVE')
                    ->where('id', '!=', $id)
                    ->first();

                if ($newBed->status !== 'VACANT' || $activeResident) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected bed is not vacant!'
                    ], 400);
                }

                // Check if bed belongs to room
                if ($newBed->room_id != $request->room_id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected bed does not belong to the selected room!'
                    ], 400);
                }

                // Occupy new bed ONLY if resident is ACTIVE
                if ($request->status === 'ACTIVE') {
                    $newBed->update(['status' => 'OCCUPIED']);
                } else {
                    $newBed->update(['status' => 'VACANT']);
                }

                $oldRoom = Room::find($resident->room_id);
            }

            // ============================================
            // 2. HANDLE FILE UPLOADS
            // ============================================
            $residentData = $request->except(['profile_image', 'aadhar_document', 'application_document']);

            if ($request->hasFile('profile_image')) {
                if ($resident->profile_image) {
                    $this->deleteFile($resident->profile_image);
                }
                $path = $this->uploadFile($request->file('profile_image'), 'profile');
                if ($path) {
                    $residentData['profile_image'] = $path;
                }
            }

            if ($request->hasFile('aadhar_document')) {
                if ($resident->aadhar_document) {
                    $this->deleteFile($resident->aadhar_document);
                }
                $path = $this->uploadFile($request->file('aadhar_document'), 'documents/aadhar');
                if ($path) {
                    $residentData['aadhar_document'] = $path;
                }
            }

            if ($request->hasFile('application_document')) {
                if ($resident->application_document) {
                    $this->deleteFile($resident->application_document);
                }
                $path = $this->uploadFile($request->file('application_document'), 'documents/application');
                if ($path) {
                    $residentData['application_document'] = $path;
                }
            }

            // ============================================
            // 3. HANDLE STATUS CHANGE - CRITICAL FIX
            // Resident.ACTIVE → Bed.OCCUPIED
            // Resident.VACATED → Bed.VACANT
            // ============================================
            $oldStatus = $resident->status;
            $newStatus = $request->status;

            // If status is changing to VACATED, free the bed
            if ($newStatus == 'VACATED' && $oldStatus != 'VACATED') {
                // Free the bed - VACATED resident = VACANT bed
                $bed = Bed::find($resident->bed_id);
                if ($bed) {
                    $bed->update(['status' => 'VACANT']);
                }

                // Set vacate date if not provided
                if (empty($request->vacate_date)) {
                    $residentData['vacate_date'] = now()->toDateString();
                }
            }

            // If status is changing to ACTIVE from VACATED
            if ($newStatus == 'ACTIVE' && $oldStatus == 'VACATED') {
                // Check if bed is vacant
                $bed = Bed::find($resident->bed_id);
                if ($bed) {
                    // Check if another ACTIVE resident is using this bed
                    $otherActiveResident = Resident::where('bed_id', $resident->bed_id)
                        ->where('id', '!=', $resident->id)
                        ->where('status', 'ACTIVE')
                        ->first();

                    if ($otherActiveResident) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot reactivate. Bed is occupied by another active resident!'
                        ], 400);
                    }

                    // Occupy the bed if it's vacant
                    if ($bed->status === 'VACANT') {
                        $bed->update(['status' => 'OCCUPIED']);
                    }
                }

                // Clear vacate date
                $residentData['vacate_date'] = null;
            }

            // Update resident
            $resident->update($residentData);

            // ============================================
            // 4. UPDATE ROOM STATUSES
            // ============================================
            if ($oldRoom) {
                $this->updateRoomStatus($oldRoom);
            }

            $newRoom = Room::find($request->room_id);
            $this->updateRoomStatus($newRoom);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resident updated successfully!',
                'data' => $resident->fresh(['hostel', 'room', 'bed'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update resident: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resident
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $resident = Resident::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this resident!'
                ], 403);
            }
        }

        DB::beginTransaction();

        try {
            if ($resident->profile_image) {
                $this->deleteFile($resident->profile_image);
            }
            if ($resident->aadhar_document) {
                $this->deleteFile($resident->aadhar_document);
            }
            if ($resident->application_document) {
                $this->deleteFile($resident->application_document);
            }

            // Free the bed
            $bed = Bed::find($resident->bed_id);
            if ($bed) {
                $bed->update(['status' => 'VACANT']);
            }

            $room = Room::find($resident->room_id);
            if ($room) {
                $this->updateRoomStatus($room);
            }

            $resident->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resident deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete resident: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle resident status
     */
    public function toggleStatus($id)
    {
        $user = auth()->user();
        $resident = Resident::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this resident!'
                ], 403);
            }
        }

        DB::beginTransaction();

        try {
            $oldStatus = $resident->status;
            $newStatus = $resident->status === 'ACTIVE' ? 'VACATED' : 'ACTIVE';

            $resident->status = $newStatus;

            if ($newStatus === 'VACATED') {
                // ============================================
                // VACATED resident → Bed.VACANT
                // ============================================
                $resident->vacate_date = now()->toDateString();

                $bed = Bed::find($resident->bed_id);
                if ($bed) {
                    $bed->update(['status' => 'VACANT']);
                }

                $room = Room::find($resident->room_id);
                if ($room) {
                    $this->updateRoomStatus($room);
                }
            } else {
                // ============================================
                // ACTIVE resident → Bed.OCCUPIED
                // ============================================
                $resident->vacate_date = null;

                $bed = Bed::find($resident->bed_id);
                if ($bed) {
                    // Check if another ACTIVE resident is using this bed
                    $otherActiveResident = Resident::where('bed_id', $resident->bed_id)
                        ->where('id', '!=', $resident->id)
                        ->where('status', 'ACTIVE')
                        ->first();

                    if ($otherActiveResident) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot reactivate resident. Bed is occupied by another active resident!'
                        ], 400);
                    }

                    // Occupy the bed if it's vacant
                    if ($bed->status === 'VACANT') {
                        $bed->update(['status' => 'OCCUPIED']);
                    }
                }

                $room = Room::find($resident->room_id);
                if ($room) {
                    $this->updateRoomStatus($room);
                }
            }

            $resident->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resident status updated successfully!',
                'data' => $resident->fresh(['hostel', 'room', 'bed'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle biometric access for a resident
     */
    public function toggleBiometricAccess($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            if (!$resident->employee_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee code not generated. Please sync resident first.'
                ]);
            }

            $resident->biometric_access = !$resident->biometric_access;

            if ($resident->biometric_access) {
                $resident->access_enabled_at = now();
                $resident->access_disabled_at = null;
            } else {
                $resident->access_disabled_at = now();
                $resident->access_enabled_at = null;
            }

            $resident->save();

            return response()->json([
                'success' => true,
                'message' => 'Biometric access ' . ($resident->biometric_access ? 'enabled' : 'disabled') . ' successfully',
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'biometric_access' => $resident->biometric_access
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync a single resident to biometric system
     */
    public function syncToBiometric($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            $resident->employee_code = $resident->generateEmployeeCode();
            $resident->biometric_access = true;
            $resident->last_sync_at = now();
            $resident->access_enabled_at = now();
            $resident->access_disabled_at = null;
            $resident->save();

            return response()->json([
                'success' => true,
                'message' => 'Resident synced to biometric system successfully',
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'employee_code' => $resident->employee_code,
                    'biometric_access' => $resident->biometric_access
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync all residents to biometric system
     */
    public function syncAllToBiometric()
    {
        try {
            $residents = Resident::all();

            if ($residents->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No residents found'
                ]);
            }

            $successCount = 0;
            $failureCount = 0;
            $results = [];

            foreach ($residents as $resident) {
                try {
                    $resident->employee_code = $resident->generateEmployeeCode();
                    $resident->biometric_access = true;
                    $resident->last_sync_at = now();
                    $resident->access_enabled_at = now();
                    $resident->access_disabled_at = null;
                    $resident->save();
                    $successCount++;
                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'employee_code' => $resident->employee_code,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $failureCount++;
                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => $failureCount === 0,
                'total' => $residents->count(),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get residents with biometric status
     */
    public function biometricList()
    {
        try {
            $user = auth()->user();

            if ($user->role === 'admin') {
                $residents = Resident::all();
            } else {
                $hostelIds = $user->hostel_ids ?? [];
                $residents = Resident::whereIn('hostel_id', $hostelIds)->get();
            }

            $data = $residents->map(function ($resident) {
                return [
                    'id' => $resident->id,
                    'name' => $resident->name,
                    'resident_code' => $resident->resident_code,
                    'hostel_id' => $resident->hostel_id,
                    'employee_code' => $resident->employee_code ?? 'Not Generated',
                    'biometric_access' => $resident->biometric_access ? 'Enabled' : 'Disabled',
                    'biometric_status' => $resident->biometric_status,
                    'status_class' => $resident->biometric_status_class,
                    'last_sync_at' => $resident->last_sync_at ? $resident->last_sync_at->format('Y-m-d H:i:s') : 'Never',
                    'status' => $resident->status
                ];
            });

            return response()->json([
                'success' => true,
                'total' => $data->count(),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get resident's biometric status
     */
    public function biometricStatus($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'employee_code' => $resident->employee_code ?? 'Not generated',
                    'biometric_access' => $resident->biometric_access ? 'Enabled' : 'Disabled',
                    'biometric_status' => $resident->biometric_status,
                    'last_sync_at' => $resident->last_sync_at ? $resident->last_sync_at->format('Y-m-d H:i:s') : 'Never',
                    'access_enabled_at' => $resident->access_enabled_at ? $resident->access_enabled_at->format('Y-m-d H:i:s') : null,
                    'access_disabled_at' => $resident->access_disabled_at ? $resident->access_disabled_at->format('Y-m-d H:i:s') : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Fix orphaned occupied beds - ONLY ACTIVE residents should occupy beds
     */
    public function fixOrphanedBeds()
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can run this maintenance function!'
                ], 403);
            }

            DB::beginTransaction();

            try {
                // Get all beds
                $allBeds = Bed::all();
                $fixed = 0;
                $bedIds = [];

                foreach ($allBeds as $bed) {
                    // Check if there's an ACTIVE resident using this bed
                    $activeResident = Resident::where('bed_id', $bed->id)
                        ->where('status', 'ACTIVE')
                        ->first();

                    if ($activeResident) {
                        // ACTIVE resident → Bed should be OCCUPIED
                        if ($bed->status !== 'OCCUPIED') {
                            $bed->update(['status' => 'OCCUPIED']);
                            $fixed++;
                            $bedIds[] = $bed->id;
                        }
                    } else {
                        // No ACTIVE resident → Bed should be VACANT
                        if ($bed->status === 'OCCUPIED' && $bed->status !== 'BLOCKED') {
                            $bed->update(['status' => 'VACANT']);
                            $fixed++;
                            $bedIds[] = $bed->id;
                        }
                    }
                }

                // Update all room statuses
                $rooms = Room::all();
                foreach ($rooms as $room) {
                    $this->updateRoomStatus($room);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Fixed {$fixed} beds",
                    'fixed' => $fixed,
                    'bed_ids' => $bedIds
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fix beds: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get bed integrity report - check for inconsistencies
     */
    public function getBedIntegrityReport()
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can view this report!'
                ], 403);
            }

            $issues = [];

            // Check: Beds marked OCCUPIED but no ACTIVE resident
            $occupiedBeds = Bed::where('status', 'OCCUPIED')->get();
            $orphanedBeds = [];

            foreach ($occupiedBeds as $bed) {
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();
                if (!$activeResident) {
                    $orphanedBeds[] = $bed;
                }
            }

            if (count($orphanedBeds) > 0) {
                $issues[] = [
                    'type' => 'orphaned_beds',
                    'count' => count($orphanedBeds),
                    'description' => 'Beds marked OCCUPIED but have no ACTIVE resident assigned',
                    'beds' => array_map(function ($bed) {
                        return [
                            'id' => $bed->id,
                            'bed_no' => $bed->bed_no,
                            'room_id' => $bed->room_id,
                            'status' => $bed->status
                        ];
                    }, $orphanedBeds)
                ];
            }

            // Check: Residents with no bed
            $residentsWithNoBed = Resident::whereNull('bed_id')->get();
            if ($residentsWithNoBed->count() > 0) {
                $issues[] = [
                    'type' => 'residents_no_bed',
                    'count' => $residentsWithNoBed->count(),
                    'description' => 'Residents with no bed assigned',
                    'residents' => $residentsWithNoBed->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'name' => $r->name,
                            'resident_code' => $r->resident_code,
                            'status' => $r->status
                        ];
                    })
                ];
            }

            // Check: Beds VACANT but ACTIVE residents assigned
            $vacantBeds = Bed::where('status', 'VACANT')->get();
            $mismatchedBeds = [];

            foreach ($vacantBeds as $bed) {
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();
                if ($activeResident) {
                    $mismatchedBeds[] = [
                        'bed' => $bed,
                        'resident' => $activeResident
                    ];
                }
            }

            if (count($mismatchedBeds) > 0) {
                $issues[] = [
                    'type' => 'mismatched_beds',
                    'count' => count($mismatchedBeds),
                    'description' => 'Beds marked VACANT but have ACTIVE residents assigned',
                    'beds' => array_map(function ($item) {
                        return [
                            'id' => $item['bed']->id,
                            'bed_no' => $item['bed']->bed_no,
                            'status' => $item['bed']->status,
                            'resident_name' => $item['resident']->name,
                            'resident_status' => $item['resident']->status
                        ];
                    }, $mismatchedBeds)
                ];
            }

            return response()->json([
                'success' => true,
                'total_issues' => count($issues),
                'issues' => $issues,
                'has_issues' => count($issues) > 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    private function csvEscape($value)
    {
        if ($value === null) return '';
        $value = (string) $value;
        if (strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, '"') !== false) {
            $value = str_replace('"', '""', $value);
            return '"' . $value . '"';
        }
        return $value;
    }

    private function uploadFile($file, $subDirectory = '')
    {
        if (!$file) return null;

        $timestamp = time();
        $random = Str::random(8);
        $extension = $file->getClientOriginalExtension();
        $filename = $timestamp . '_' . $random . '.' . $extension;

        $directory = 'uploads/residents/' . $subDirectory;
        $path = public_path($directory);

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $file->move($path, $filename);
        return $directory . '/' . $filename;
    }

    private function deleteFile($filePath)
    {
        if ($filePath && File::exists(public_path($filePath))) {
            File::delete(public_path($filePath));
            return true;
        }
        return false;
    }

    /**
     * Update room status based on bed occupancy
     * ONLY ACTIVE residents count as occupied
     */
    private function updateRoomStatus($room)
    {
        if (!$room) return;

        $totalBeds = $room->beds()->count();

        // Get all beds in this room
        $beds = $room->beds()->get();

        $occupiedBeds = 0;
        $maintenanceBeds = 0;
        $vacantBeds = 0;

        foreach ($beds as $bed) {
            if ($bed->status === 'BLOCKED') {
                $maintenanceBeds++;
            } else if ($bed->status === 'OCCUPIED') {
                // ============================================
                // CRITICAL FIX: Only ACTIVE residents occupy beds
                // ============================================
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();

                if ($activeResident) {
                    $occupiedBeds++;
                } else {
                    // No ACTIVE resident - bed should be VACANT
                    $bed->update(['status' => 'VACANT']);
                    $vacantBeds++;
                }
            } else if ($bed->status === 'VACANT') {
                // Check if there's an ACTIVE resident using this bed (inconsistent state)
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();

                if ($activeResident) {
                    // Fix: bed should be OCCUPIED
                    $bed->update(['status' => 'OCCUPIED']);
                    $occupiedBeds++;
                } else {
                    $vacantBeds++;
                }
            }
        }

        // Recalculate to be safe
        $totalBeds = $room->beds()->count();
        $occupiedBeds = 0;
        $maintenanceBeds = $room->beds()->where('status', 'BLOCKED')->count();
        $vacantBeds = 0;

        $allBeds = $room->beds()->get();
        foreach ($allBeds as $bed) {
            if ($bed->status === 'BLOCKED') {
                $maintenanceBeds++;
            } else if ($bed->status === 'OCCUPIED') {
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();
                if ($activeResident) {
                    $occupiedBeds++;
                } else {
                    $bed->update(['status' => 'VACANT']);
                    $vacantBeds++;
                }
            } else if ($bed->status === 'VACANT') {
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();
                if ($activeResident) {
                    $bed->update(['status' => 'OCCUPIED']);
                    $occupiedBeds++;
                } else {
                    $vacantBeds++;
                }
            }
        }

        // Determine room status
        if ($totalBeds == 0) {
            $room->status = 'VACANT';
        } elseif ($maintenanceBeds == $totalBeds) {
            $room->status = 'MAINTENANCE';
        } elseif ($occupiedBeds == $totalBeds) {
            $room->status = 'FULL';
        } elseif ($occupiedBeds > 0 && $vacantBeds > 0) {
            $room->status = 'PARTIAL';
        } else {
            $room->status = 'VACANT';
        }

        $room->save();
    }

    /**
     * Get rooms by hostel
     */
    public function getRooms($hostelId)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($hostelId, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this hostel\'s rooms!'
                ], 403);
            }
        }

        $rooms = Room::where('hostel_id', $hostelId)
            ->whereIn('status', ['VACANT', 'PARTIAL'])
            ->with('roomType')
            ->get();

        foreach ($rooms as $room) {
            // ============================================
            // CRITICAL FIX: Count ONLY ACTIVE residents as occupied
            // ============================================
            $beds = $room->beds()->where('status', 'VACANT')->get();
            $availableBeds = 0;

            foreach ($beds as $bed) {
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();

                if (!$activeResident) {
                    $availableBeds++;
                }
            }

            $room->available_beds = $availableBeds;
        }

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Get beds by room
     */
    public function getBeds($roomId)
    {
        $user = auth()->user();
        $room = Room::find($roomId);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this room\'s beds!'
                ], 403);
            }
        }

        $beds = Bed::where('room_id', $roomId)->get();

        // ============================================
        // CRITICAL FIX: Clean up based on ACTIVE residents only
        // ============================================
        foreach ($beds as $bed) {
            // Only ACTIVE resident means occupied
            $activeResident = Resident::where('bed_id', $bed->id)
                ->where('status', 'ACTIVE')
                ->first();

            if ($activeResident) {
                if ($bed->status !== 'OCCUPIED') {
                    $bed->update(['status' => 'OCCUPIED']);
                }
            } else {
                // VACATED resident or no resident = vacant
                if ($bed->status !== 'BLOCKED') {
                    if ($bed->status !== 'VACANT') {
                        $bed->update(['status' => 'VACANT']);
                    }
                }
            }
        }

        // Refresh collection after cleanup
        $beds = Bed::where('room_id', $roomId)->get();

        return response()->json([
            'success' => true,
            'data' => $beds
        ]);
    }

    /**
     * Get hostel rooms via POST
     */
    public function getHostelRooms(Request $request)
    {
        $user = auth()->user();
        $hostelId = $request->hostel_id;

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($hostelId, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this hostel\'s rooms!'
                ], 403);
            }
        }

        $rooms = Room::where('hostel_id', $hostelId)
            ->whereIn('status', ['VACANT', 'PARTIAL'])
            ->with('roomType')
            ->get();

        foreach ($rooms as $room) {
            // ============================================
            // CRITICAL FIX: Count ONLY ACTIVE residents as occupied
            // ============================================
            $beds = $room->beds()->where('status', 'VACANT')->get();
            $availableBeds = 0;

            foreach ($beds as $bed) {
                $activeResident = Resident::where('bed_id', $bed->id)
                    ->where('status', 'ACTIVE')
                    ->first();

                if (!$activeResident) {
                    $availableBeds++;
                }
            }

            $room->available_beds = $availableBeds;
        }

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Get room details
     */
    public function getRoomDetails($id)
    {
        try {
            $room = Room::with('roomType')->find($id);
            if (!$room) {
                return response()->json(['success' => false, 'message' => 'Room not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $room
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get resident documents
     */
    public function getResidentDocuments($id)
    {
        $user = auth()->user();
        $resident = Resident::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this resident\'s documents!'
                ], 403);
            }
        }

        $documents = [
            'profile_image' => [
                'exists' => !is_null($resident->profile_image),
                'url' => $resident->profile_image_url,
                'name' => 'Profile Image',
                'icon' => 'bi-person-circle'
            ],
            'aadhar_document' => [
                'exists' => !is_null($resident->aadhar_document),
                'url' => $resident->aadhar_document_url,
                'name' => 'Aadhar Document',
                'icon' => 'bi-file-earmark-pdf'
            ],
            'application_document' => [
                'exists' => !is_null($resident->application_document),
                'url' => $resident->application_document_url,
                'name' => 'Application Document',
                'icon' => 'bi-file-earmark-text'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Bulk delete residents
     */
    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:residents,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $deleted = 0;
            $errors = [];
            $roomsToUpdate = [];

            foreach ($request->ids as $id) {
                $resident = Resident::find($id);
                if (!$resident) continue;

                if ($user->role !== 'admin') {
                    $hostelIds = $user->hostel_ids ?? [];
                    if (!in_array($resident->hostel_id, $hostelIds)) {
                        $errors[] = "No permission to delete resident: {$resident->name}";
                        continue;
                    }
                }

                if ($resident->profile_image) $this->deleteFile($resident->profile_image);
                if ($resident->aadhar_document) $this->deleteFile($resident->aadhar_document);
                if ($resident->application_document) $this->deleteFile($resident->application_document);

                $bed = Bed::find($resident->bed_id);
                if ($bed) {
                    $bed->update(['status' => 'VACANT']);
                }

                $roomsToUpdate[] = $resident->room_id;
                $resident->delete();
                $deleted++;
            }

            foreach (array_unique($roomsToUpdate) as $roomId) {
                $room = Room::find($roomId);
                if ($room) $this->updateRoomStatus($room);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deleted} residents deleted successfully!",
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete residents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk status update
     */
    public function bulkStatus(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:residents,id',
            'status' => 'required|in:ACTIVE,VACATED'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $updated = 0;
            $roomsToUpdate = [];

            foreach ($request->ids as $id) {
                $resident = Resident::find($id);
                if (!$resident) continue;

                if ($user->role !== 'admin') {
                    $hostelIds = $user->hostel_ids ?? [];
                    if (!in_array($resident->hostel_id, $hostelIds)) {
                        continue;
                    }
                }

                if ($request->status == 'VACATED' && $resident->status == 'ACTIVE') {
                    $resident->vacate_date = now()->toDateString();
                    $bed = Bed::find($resident->bed_id);
                    if ($bed) {
                        $bed->update(['status' => 'VACANT']);
                    }
                    $roomsToUpdate[] = $resident->room_id;
                } elseif ($request->status == 'ACTIVE' && $resident->status == 'VACATED') {
                    $resident->vacate_date = null;
                    $bed = Bed::find($resident->bed_id);
                    if ($bed && $bed->status == 'VACANT') {
                        // Check if another ACTIVE resident is using this bed
                        $otherActiveResident = Resident::where('bed_id', $resident->bed_id)
                            ->where('id', '!=', $resident->id)
                            ->where('status', 'ACTIVE')
                            ->first();

                        if (!$otherActiveResident) {
                            $bed->update(['status' => 'OCCUPIED']);
                            $roomsToUpdate[] = $resident->room_id;
                        }
                    }
                }

                $resident->status = $request->status;
                $resident->save();
                $updated++;
            }

            foreach (array_unique($roomsToUpdate) as $roomId) {
                $room = Room::find($roomId);
                if ($room) $this->updateRoomStatus($room);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updated} residents updated successfully!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update residents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $residents = Resident::all();
            $hostels = Hostel::all();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $residents = Resident::whereIn('hostel_id', $hostelIds)->get();
            $hostels = Hostel::whereIn('id', $hostelIds)->get();
        }

        $stats = [
            'total' => $residents->count(),
            'active' => $residents->where('status', 'ACTIVE')->count(),
            'vacated' => $residents->where('status', 'VACATED')->count(),
            'male' => $residents->filter(function ($r) use ($hostels) {
                $hostel = $hostels->firstWhere('id', $r->hostel_id);
                return $hostel && $hostel->hostel_type == 'MEN';
            })->count(),
            'female' => $residents->filter(function ($r) use ($hostels) {
                $hostel = $hostels->firstWhere('id', $r->hostel_id);
                return $hostel && $hostel->hostel_type == 'WOMEN';
            })->count(),
            'with_food' => $residents->where('food_status', 'WITH_FOOD')->where('status', 'ACTIVE')->count(),
            'without_food' => $residents->where('food_status', 'WITHOUT_FOOD')->where('status', 'ACTIVE')->count(),
            'total_rent' => $residents->where('status', 'ACTIVE')->sum('rent_amount'),
            'avg_rent' => $residents->where('status', 'ACTIVE')->count() > 0 ? round($residents->where('status', 'ACTIVE')->avg('rent_amount'), 2) : 0,
            'total_deposit' => $residents->sum('deposit_amount'),
            'biometric_synced' => $residents->whereNotNull('employee_code')->count(),
            'biometric_enabled' => $residents->where('biometric_access', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Search residents
     */
    public function search(Request $request)
    {
        $user = auth()->user();
        $query = $request->get('q', '');

        $residentsQuery = Resident::with(['hostel', 'room', 'bed'])
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('resident_code', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('employee_code', 'LIKE', "%{$query}%");

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        $residents = $residentsQuery->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $residents
        ]);
    }

    /**
     * Get residents by hostel
     */
    public function getResidentsByHostel($hostelId)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($hostelId, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this hostel\'s residents!'
                ], 403);
            }
        }

        $residents = Resident::with(['hostel', 'room', 'bed'])
            ->where('hostel_id', $hostelId)
            ->where('status', 'ACTIVE')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $residents
        ]);
    }
}
