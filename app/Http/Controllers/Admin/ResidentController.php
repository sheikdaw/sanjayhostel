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

class ResidentController extends Controller
{
    /**
     * Display a listing of residents.
     */
    public function index()
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
            $residents = Resident::with(['hostel', 'room', 'bed'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
            $residents = Resident::with(['hostel', 'room', 'bed'])
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
            'total_rent' => $residents->where('status', 'ACTIVE')->sum('rent_amount')
        ];

        // Biometric statistics
        $biometricStats = [
            'total' => $residents->count(),
            'synced' => $residents->whereNotNull('employee_code')->count(),
            'access_enabled' => $residents->where('biometric_access', true)->count(),
            'access_disabled' => $residents->where('biometric_access', false)->count()
        ];

        return view('admin.residents.index', compact('residents', 'hostels', 'stats', 'user', 'biometricStats'));
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

        // Check if user has access to this hostel
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

        // Check if user has access to this room's hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this room\'s beds!'
                ], 403);
            }
        }

        // Get all beds in the room
        $beds = Bed::where('room_id', $roomId)->get();

        // Return all beds so we can show the current bed in edit mode
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

        // Check if user has access to this hostel
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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg',
            'aadhar_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg',
            'application_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg',
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

        // Generate employee code for biometric
        $employeeCode = $this->generateEmployeeCode($resident);
        $resident->employee_code = $employeeCode;
        $resident->biometric_access = true;
        $resident->last_sync_at = now();
        $resident->save();

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
     * Show the form for editing the specified resident
     */
    public function edit($id)
    {
        $user = auth()->user();
        $resident = Resident::with(['hostel', 'room', 'bed', 'room.roomType'])->findOrFail($id);

        // Check if user has access to this resident's hostel
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
            'email' => 'nullable|email|max:100',
            'parentsphone' => 'nullable|string|max:20',
            'aadhaar_no' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'rent_amount' => 'required|numeric|min:0',
            'food_status' => 'required|in:WITH_FOOD,WITHOUT_FOOD',
            'joining_date' => 'required|date',
            'vacate_date' => 'nullable|date|after_or_equal:joining_date',
            'deposit_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,VACATED',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg',
            'aadhar_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg',
            'application_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg',
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

        if ($resident->profile_image) {
            $this->deleteFile($resident->profile_image);
        }
        if ($resident->aadhar_document) {
            $this->deleteFile($resident->aadhar_document);
        }
        if ($resident->application_document) {
            $this->deleteFile($resident->application_document);
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

        $residents = Resident::with(['hostel', 'room', 'bed'])
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('resident_code', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $residents
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

            if ($resident->profile_image) {
                $this->deleteFile($resident->profile_image);
            }
            if ($resident->aadhar_document) {
                $this->deleteFile($resident->aadhar_document);
            }
            if ($resident->application_document) {
                $this->deleteFile($resident->application_document);
            }

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
            if ($room) {
                $this->updateRoomStatus($room);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deleted} residents deleted successfully!",
            'errors' => $errors
        ]);
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
                    $bed->update(['status' => 'OCCUPIED']);
                    $roomsToUpdate[] = $resident->room_id;
                }
            }

            $resident->status = $request->status;
            $resident->save();
            $updated++;
        }

        foreach (array_unique($roomsToUpdate) as $roomId) {
            $room = Room::find($roomId);
            if ($room) {
                $this->updateRoomStatus($room);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} residents updated successfully!"
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
            'total_deposit' => $residents->sum('deposit_amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
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
            $csv .= "ID,Resident Code,Name,Phone,Parents Phone,Email,Hostel,Room,Bed,Food Status,Joining Date,Vacate Date,Rent,Deposit,Status,Employee Code,Biometric Access\n";
            $csv .= "No data available,,,,,,,,,,,,,,,,,\n";

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="residents-' . date('Y-m-d') . '.csv"');
        }

        $csv = "\xEF\xBB\xBF";
        $csv .= "ID,Resident Code,Name,Phone,Parents Phone,Email,Hostel,Room,Bed,Food Status,Joining Date,Vacate Date,Rent,Deposit,Status,Employee Code,Biometric Access\n";

        foreach ($residents as $resident) {
            $hostelName = $resident->hostel ? $resident->hostel->hostel_name : 'N/A';
            $roomNo = $resident->room ? $resident->room->room_no : 'N/A';
            $bedNo = $resident->bed ? $resident->bed->bed_no : 'N/A';
            $foodLabel = $resident->food_status == 'WITH_FOOD' ? 'With Food' : 'Without Food';
            $biometricAccess = $resident->biometric_access ? 'Enabled' : 'Disabled';

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
            $csv .= $this->csvEscape($resident->employee_code ?? 'Not Synced') . ",";
            $csv .= $this->csvEscape($biometricAccess) . "\n";
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
     * Generate employee code for biometric system
     */
    private function generateEmployeeCode($resident)
    {
        $hostelId = $resident->hostel_id ?? 1;
        $code = $resident->id + 10000;
        return 'RES_H' . $hostelId . '_' . str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if biometric columns exist
     */
    private function checkBiometricColumns()
    {
        $columns = ['employee_code', 'biometric_access', 'last_sync_at'];
        $missing = [];

        foreach ($columns as $column) {
            if (!Schema::hasColumn('residents', $column)) {
                $missing[] = $column;
            }
        }

        if (!empty($missing)) {
            throw new \Exception('Missing columns: ' . implode(', ', $missing));
        }

        return true;
    }

    /**
     * Get resident's biometric status
     */
    public function biometricStatus($id)
    {
        try {
            $this->checkBiometricColumns();
            $resident = Resident::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'employee_code' => $resident->employee_code ?? 'Not generated',
                    'biometric_access' => $resident->biometric_access ? 'Enabled' : 'Disabled',
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
     * Sync a single resident to biometric system
     */
    public function syncToBiometric($id)
    {
        try {
            $this->checkBiometricColumns();
            $resident = Resident::findOrFail($id);

            // Generate employee code if not exists
            if (!$resident->employee_code) {
                $resident->employee_code = $this->generateEmployeeCode($resident);
            }

            $resident->biometric_access = true;
            $resident->last_sync_at = now();
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
            $this->checkBiometricColumns();
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
                    if (!$resident->employee_code) {
                        $resident->employee_code = $this->generateEmployeeCode($resident);
                    }
                    $resident->biometric_access = true;
                    $resident->last_sync_at = now();
                    $resident->save();
                    $successCount++;
                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
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
     * Toggle biometric access for a resident
     */
    public function toggleBiometricAccess($id)
    {
        try {
            $this->checkBiometricColumns();
            $resident = Resident::findOrFail($id);

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
     * Get residents with biometric status
     */
    public function biometricList()
    {
        try {
            $this->checkBiometricColumns();
            $user = auth()->user();

            if ($user->role === 'admin') {
                $residents = Resident::all();
            } else {
                $hostelIds = $user->hostel_ids ?? [];
                $residents = Resident::whereIn('hostel_id', $hostelIds)->get();
            }

            $data = $residents->map(function($resident) {
                return [
                    'id' => $resident->id,
                    'name' => $resident->name,
                    'resident_code' => $resident->resident_code,
                    'hostel_id' => $resident->hostel_id,
                    'employee_code' => $resident->employee_code ?? 'Not generated',
                    'biometric_access' => $resident->biometric_access ? 'Enabled' : 'Disabled',
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
}
