<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Hostel;
use App\Models\Resident;
use App\Models\Room;
use App\Models\Payment;
use App\Services\EbioServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ResidentController extends Controller
{
    protected $ebioService;

    public function __construct(EbioServerService $ebioService)
    {
        $this->ebioService = $ebioService;
    }

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
            'face_registered' => $residents->where('face_registered', true)->count(),
        ];

        // Biometric statistics
        $biometricStats = [
            'total' => $residents->count(),
            'face_registered' => $residents->where('face_registered', true)->count(),
            'face_pending' => $residents->where('face_registered', false)->orWhereNull('face_registered')->count(),
            'has_profile_image' => $residents->whereNotNull('profile_image')->count(),
            'has_aadhar' => $residents->whereNotNull('aadhar_document')->count(),
            'has_application' => $residents->whereNotNull('application_document')->count(),
        ];

        return view('admin.residents.index', compact('residents', 'hostels', 'stats', 'user', 'biometricStats'));
    }

    /**
     * Get full resident details including all information
     */
    public function getResidentDetails($id)
    {
        try {
            $resident = Resident::with(['hostel', 'room', 'bed', 'room.roomType'])
                ->findOrFail($id);

            // Get current month payment
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $currentPayment = Payment::where('resident_id', $id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            // Get payment history (last 6 months)
            $paymentHistory = Payment::where('resident_id', $id)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get();

            $details = [
                // Personal Information
                'id' => $resident->id,
                'resident_code' => $resident->resident_code,
                'name' => $resident->name,
                'phone' => $resident->phone,
                'parents_phone' => $resident->parentsphone,
                'email' => $resident->email,
                'aadhaar_no' => $resident->aadhaar_no,
                'address' => $resident->address,
                'profile_image' => $resident->profile_image_url,
                'profile_image_thumb' => $resident->profile_image_thumb,
                'initials' => $resident->initials,

                // Face Recognition
                'face' => [
                    'face_id' => $resident->face_id,
                    'face_registered' => $resident->face_registered,
                    'face_registered_at' => $resident->face_registered_at ? $resident->face_registered_at->format('Y-m-d H:i:s') : null,
                    'face_image_url' => $resident->face_image_url,
                    'status' => $resident->has_face_registered ? 'Registered ✅' : 'Not Registered ❌',
                    'status_class' => $resident->has_face_registered ? 'success' : 'danger',
                ],

                // Accommodation Details
                'hostel' => [
                    'id' => $resident->hostel_id,
                    'name' => $resident->hostel->hostel_name ?? 'N/A',
                    'code' => $resident->hostel->hostel_code ?? 'N/A',
                    'type' => $resident->hostel->hostel_type ?? 'N/A',
                    'type_icon' => $resident->hostel->hostel_type == 'MEN' ? '👨' : '👩',
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

                // Financial Details
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

                // Status
                'status' => [
                    'value' => $resident->status,
                    'label' => $resident->status_label ?? $resident->status,
                    'badge' => $resident->status_badge,
                    'joining_date' => $resident->joining_date ? $resident->joining_date->format('Y-m-d') : null,
                    'joining_date_formatted' => $resident->joining_date ? $resident->joining_date->format('d M Y') : 'N/A',
                    'vacate_date' => $resident->vacate_date ? $resident->vacate_date->format('Y-m-d') : null,
                    'vacate_date_formatted' => $resident->vacate_date ? $resident->vacate_date->format('d M Y') : 'N/A',
                ],

                // Documents
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

                // Payment Info
                'current_payment' => $currentPayment ? [
                    'month' => $currentPayment->month,
                    'year' => $currentPayment->year,
                    'month_name' => $currentPayment->month_name,
                    'rent_amount' => $currentPayment->rent_amount,
                    'cash_paid' => $currentPayment->cash_paid_amount ?? 0,
                    'upi_paid' => $currentPayment->upi_paid_amount ?? 0,
                    'total_paid' => $currentPayment->total_paid,
                    'balance' => $currentPayment->balance_amount ?? 0,
                    'status' => $currentPayment->status,
                    'status_label' => $currentPayment->status_label,
                ] : null,
                'payment_history' => $paymentHistory->map(function($payment) {
                    return [
                        'month' => $payment->month,
                        'year' => $payment->year,
                        'month_name' => $payment->month_name,
                        'rent_amount' => $payment->rent_amount,
                        'total_paid' => $payment->total_paid,
                        'balance' => $payment->balance_amount ?? 0,
                        'status' => $payment->status,
                        'status_label' => $payment->status == 'PAID' ? '✅' : '❌',
                    ];
                }),

                // Timestamps
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
     * Show resident full details page
     */
    public function show($id)
    {
        try {
            $resident = Resident::with(['hostel', 'room', 'bed', 'room.roomType'])
                ->findOrFail($id);

            // Get current month payment
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $currentPayment = Payment::where('resident_id', $id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            // Get payment history
            $paymentHistory = Payment::where('resident_id', $id)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get();

            return view('admin.residents.show', compact('resident', 'currentPayment', 'paymentHistory'));
        } catch (\Exception $e) {
            return redirect()->route('admin.residents.index')
                ->with('error', 'Resident not found');
        }
    }

    /**
     * Store a newly created resident
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if user has access to this hostel
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

        // Check if bed is available
        $bed = Bed::find($request->bed_id);
        if ($bed->status !== 'VACANT') {
            return response()->json([
                'success' => false,
                'message' => 'Selected bed is not vacant!'
            ], 400);
        }

        // Check if room belongs to hostel
        $room = Room::find($request->room_id);
        if ($room->hostel_id != $request->hostel_id) {
            return response()->json([
                'success' => false,
                'message' => 'Selected room does not belong to the selected hostel!'
            ], 400);
        }

        // Check if bed belongs to room
        if ($bed->room_id != $request->room_id) {
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

        // Upload profile image
        if ($request->hasFile('profile_image')) {
            $path = $this->uploadFile($request->file('profile_image'), 'profile');
            if ($path) {
                $residentData['profile_image'] = $path;
            }
        }

        // Upload Aadhar document
        if ($request->hasFile('aadhar_document')) {
            $path = $this->uploadFile($request->file('aadhar_document'), 'documents/aadhar');
            if ($path) {
                $residentData['aadhar_document'] = $path;
            }
        }

        // Upload Application document
        if ($request->hasFile('application_document')) {
            $path = $this->uploadFile($request->file('application_document'), 'documents/application');
            if ($path) {
                $residentData['application_document'] = $path;
            }
        }

        // Create resident
        $resident = Resident::create($residentData);

        // Update bed status to OCCUPIED
        $bed->update(['status' => 'OCCUPIED']);

        // Update room status
        $this->updateRoomStatus($room);

        return response()->json([
            'success' => true,
            'message' => 'Resident registered successfully! Resident Code: ' . $code,
            'data' => $resident
        ]);
    }

    /**
     * Update the specified resident
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $resident = Resident::findOrFail($id);

        // Check if user has access to this resident's hostel
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
            'rent_amount' => 'required|numeric|min:0',
            'food_status' => 'required|in:WITH_FOOD,WITHOUT_FOOD',
            'joining_date' => 'required|date',
            'vacate_date' => 'nullable|date|after_or_equal:joining_date',
            'deposit_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,VACATED',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'aadhar_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'application_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'face_registered' => 'nullable|boolean',
            'face_id' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle bed change
        $oldBed = null;
        if ($resident->bed_id != $request->bed_id) {
            $oldBed = Bed::find($resident->bed_id);
            if ($oldBed) {
                $oldBed->update(['status' => 'VACANT']);
            }

            $newBed = Bed::find($request->bed_id);
            if ($newBed->status !== 'VACANT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected bed is not vacant!'
                ], 400);
            }
            $newBed->update(['status' => 'OCCUPIED']);
        }

        $oldRoom = null;
        if ($resident->room_id != $request->room_id) {
            $oldRoom = Room::find($resident->room_id);
        }

        // Handle file uploads
        $residentData = $request->except(['profile_image', 'aadhar_document', 'application_document']);

        // Handle Profile Image
        if ($request->hasFile('profile_image')) {
            if ($resident->profile_image) {
                $this->deleteFile($resident->profile_image);
            }
            $path = $this->uploadFile($request->file('profile_image'), 'profile');
            if ($path) {
                $residentData['profile_image'] = $path;
            }
        }

        // Handle Aadhar Document
        if ($request->hasFile('aadhar_document')) {
            if ($resident->aadhar_document) {
                $this->deleteFile($resident->aadhar_document);
            }
            $path = $this->uploadFile($request->file('aadhar_document'), 'documents/aadhar');
            if ($path) {
                $residentData['aadhar_document'] = $path;
            }
        }

        // Handle Application Document
        if ($request->hasFile('application_document')) {
            if ($resident->application_document) {
                $this->deleteFile($resident->application_document);
            }
            $path = $this->uploadFile($request->file('application_document'), 'documents/application');
            if ($path) {
                $residentData['application_document'] = $path;
            }
        }

        $resident->update($residentData);

        if ($oldRoom) {
            $this->updateRoomStatus($oldRoom);
        }

        $newRoom = Room::find($request->room_id);
        $this->updateRoomStatus($newRoom);

        if ($request->status == 'VACATED' && $resident->status != 'VACATED') {
            $bed = Bed::find($resident->bed_id);
            if ($bed) {
                $bed->update(['status' => 'VACANT']);
            }
            $this->updateRoomStatus($newRoom);
        }

        return response()->json([
            'success' => true,
            'message' => 'Resident updated successfully!',
            'data' => $resident
        ]);
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

        // Delete files
        if ($resident->profile_image) {
            $this->deleteFile($resident->profile_image);
        }
        if ($resident->aadhar_document) {
            $this->deleteFile($resident->aadhar_document);
        }
        if ($resident->application_document) {
            $this->deleteFile($resident->application_document);
        }
        if ($resident->face_image_path) {
            $this->deleteFile($resident->face_image_path);
        }

        $bed = Bed::find($resident->bed_id);
        if ($bed) {
            $bed->update(['status' => 'VACANT']);
        }

        $room = Room::find($resident->room_id);
        if ($room) {
            $this->updateRoomStatus($room);
        }

        $resident->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resident deleted successfully!'
        ]);
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

        $resident->status = $resident->status === 'ACTIVE' ? 'VACATED' : 'ACTIVE';

        if ($resident->status === 'VACATED') {
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
            $bed = Bed::find($resident->bed_id);
            if ($bed && $bed->status !== 'VACANT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reactivate resident. Bed is not vacant!'
                ], 400);
            }
            if ($bed) {
                $bed->update(['status' => 'OCCUPIED']);
            }
            $resident->vacate_date = null;
            $room = Room::find($resident->room_id);
            if ($room) {
                $this->updateRoomStatus($room);
            }
        }

        $resident->save();

        return response()->json([
            'success' => true,
            'message' => 'Resident status updated successfully!',
            'data' => $resident
        ]);
    }

    /**
     * Register face for resident
     */
    public function registerFace(Request $request, $id)
    {
        try {
            $resident = Resident::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'face_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
                'face_id' => 'required|string|max:100|unique:residents,face_id,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Upload face image
            if ($request->hasFile('face_image')) {
                if ($resident->face_image_path) {
                    $this->deleteFile($resident->face_image_path);
                }
                $path = $this->uploadFile($request->file('face_image'), 'faces');
                if ($path) {
                    $resident->face_image_path = $path;
                }
            }

            $resident->face_id = $request->face_id;
            $resident->face_registered = true;
            $resident->face_registered_at = now();
            $resident->save();

            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully!',
                'data' => $resident
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove face registration
     */
    public function removeFace($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            if ($resident->face_image_path) {
                $this->deleteFile($resident->face_image_path);
            }

            $resident->face_id = null;
            $resident->face_registered = false;
            $resident->face_registered_at = null;
            $resident->face_image_path = null;
            $resident->save();

            return response()->json([
                'success' => true,
                'message' => 'Face registration removed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get residents with face registration status
     */
    public function faceList()
    {
        try {
            $user = auth()->user();

            if ($user->role === 'admin') {
                $residents = Resident::select('id', 'name', 'resident_code', 'face_id', 'face_registered', 'face_registered_at', 'face_image_path')
                    ->orderBy('name')
                    ->get();
            } else {
                $hostelIds = $user->hostel_ids ?? [];
                $residents = Resident::select('id', 'name', 'resident_code', 'face_id', 'face_registered', 'face_registered_at', 'face_image_path')
                    ->whereIn('hostel_id', $hostelIds)
                    ->orderBy('name')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'total' => $residents->count(),
                'registered' => $residents->where('face_registered', true)->count(),
                'pending' => $residents->where('face_registered', false)->count(),
                'data' => $residents->map(function($resident) {
                    return [
                        'id' => $resident->id,
                        'name' => $resident->name,
                        'resident_code' => $resident->resident_code,
                        'face_id' => $resident->face_id,
                        'face_registered' => $resident->face_registered,
                        'face_registered_at' => $resident->face_registered_at ? $resident->face_registered_at->format('Y-m-d H:i:s') : null,
                        'face_image_url' => $resident->face_image_path ? asset($resident->face_image_path) : null,
                        'status' => $resident->face_registered ? 'Registered ✅' : 'Not Registered ❌',
                        'status_class' => $resident->face_registered ? 'success' : 'danger',
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export residents to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $residents = Resident::with(['hostel', 'room', 'bed'])
                ->orderBy('name')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $residents = Resident::with(['hostel', 'room', 'bed'])
                ->whereIn('hostel_id', $hostelIds)
                ->orderBy('name')
                ->get();
        }

        if ($residents->isEmpty()) {
            $csv = "No residents found in the system.\n";
            $csv .= "ID,Resident Code,Name,Phone,Parents Phone,Email,Hostel,Room,Bed,Food Status,Joining Date,Vacate Date,Rent,Deposit,Status,Face Registered,Face ID\n";
            $csv .= "No data available,,,,,,,,,,,,,,,,,,\n";

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="residents-' . date('Y-m-d') . '.csv"');
        }

        $csv = "\xEF\xBB\xBF";
        $csv .= "ID,Resident Code,Name,Phone,Parents Phone,Email,Hostel,Room,Bed,Food Status,Joining Date,Vacate Date,Rent,Deposit,Status,Face Registered,Face ID\n";

        foreach ($residents as $resident) {
            $hostelName = $resident->hostel ? $resident->hostel->hostel_name : 'N/A';
            $roomNo = $resident->room ? $resident->room->room_no : 'N/A';
            $bedNo = $resident->bed ? $resident->bed->bed_no : 'N/A';
            $foodLabel = $resident->food_status == 'WITH_FOOD' ? 'With Food' : 'Without Food';
            $faceStatus = $resident->face_registered ? 'Yes' : 'No';

            $csv .= $this->csvEscape($resident->id) . ",";
            $csv .= $this->csvEscape($resident->resident_code) . ",";
            $csv .= $this->csvEscape($resident->name) . ",";
            $csv .= $this->csvEscape($resident->phone) . ",";
            $csv .= $this->csvEscape($resident->parentsphone ?? '') . ",";
            $csv .= $this->csvEscape($resident->email ?? '') . ",";
            $csv .= $this->csvEscape($hostelName) . ",";
            $csv .= $this->csvEscape($roomNo) . ",";
            $csv .= $this->csvEscape($bedNo) . ",";
            $csv .= $this->csvEscape($foodLabel) . ",";
            $csv .= $this->csvEscape($resident->joining_date ? $resident->joining_date->format('Y-m-d') : '') . ",";
            $csv .= $this->csvEscape($resident->vacate_date ? $resident->vacate_date->format('Y-m-d') : '') . ",";
            $csv .= $this->csvEscape(number_format($resident->rent_amount ?? 0, 2)) . ",";
            $csv .= $this->csvEscape(number_format($resident->deposit_amount ?? 0, 2)) . ",";
            $csv .= $this->csvEscape($resident->status) . ",";
            $csv .= $this->csvEscape($faceStatus) . ",";
            $csv .= $this->csvEscape($resident->face_id ?? '') . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="residents-' . date('Y-m-d') . '.csv"')
            ->header('Pragma', 'public')
            ->header('Cache-Control', 'max-age=86400');
    }

    /**
     * Helper to escape CSV fields
     */
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

    /**
     * Upload file to public directory
     */
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

    /**
     * Delete file from public directory
     */
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
     */
    private function updateRoomStatus($room)
    {
        if (!$room) return;

        $totalBeds = $room->beds()->count();
        $occupiedBeds = $room->beds()->where('status', 'OCCUPIED')->count();
        $maintenanceBeds = $room->beds()->where('status', 'BLOCKED')->count();
        $vacantBeds = $totalBeds - $occupiedBeds - $maintenanceBeds;

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
            ->orWhere('face_id', 'LIKE', "%{$query}%");

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
            $room->available_beds = $room->beds()->where('status', 'VACANT')->count();
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
            $room->available_beds = $room->beds()->where('status', 'VACANT')->count();
        }

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
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
            'face_registered' => $residents->where('face_registered', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
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
            ],
            'face_image' => [
                'exists' => !is_null($resident->face_image_path),
                'url' => $resident->face_image_url,
                'name' => 'Face Image',
                'icon' => 'bi-person-face'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }
}