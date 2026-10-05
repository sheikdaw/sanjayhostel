<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    /**
     * Display resident management page.
     * Vacated residents are HIDDEN by default (only shown when filter applied).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Resident::with(['hostel', 'room', 'bed'])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin()) {
            $query->whereIn('hostel_id', $user->hostel_ids ?? []);
        }

        $statusFilter = $request->input('status', 'active');
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', strtoupper($statusFilter));
        }

        $residents = $query->get();

        $hostelQuery = Hostel::orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }
        $hostels = $hostelQuery->get([
            'id', 'hostel_name', 'hostel_code', 'employee_code_prefix',
        ]);

        $roomQuery = Room::with(['hostel', 'beds' => function ($q) {
            $q->where('status', 'VACANT')->orderBy('bed_no');
        }])->orderBy('room_no');

        if (!$user->isAdmin()) {
            $roomQuery->whereIn('hostel_id', $user->hostel_ids ?? []);
        }
        $rooms = $roomQuery->get();

        $roomsJson = [];
        foreach ($rooms as $r) {
            $bedsArr = [];
            foreach ($r->beds as $b) {
                $bedsArr[] = [
                    'id'       => $b->id,
                    'bed_no'   => $b->bed_no,
                    'bed_type' => $b->bed_type,
                ];
            }
            $roomsJson[] = [
                'id'           => $r->id,
                'hostel_id'    => $r->hostel_id,
                'room_no'      => $r->room_no,
                'room_type_id' => $r->room_type_id,
                'beds'         => $bedsArr,
            ];
        }

        $nextResidentCode = $this->generateResidentCode();
        $nextEmployeeCode = 'Auto-generated on save';

        return view('admin.residents.index', compact(
            'residents',
            'hostels',
            'rooms',
            'roomsJson',
            'statusFilter',
            'nextResidentCode',
            'nextEmployeeCode'
        ));
    }

    /**
     * Store new resident — codes are auto-generated.
     */
    public function store(Request $request)
    {
        $autoResidentCode = $this->generateResidentCode();
        $request->merge(['resident_code' => $autoResidentCode]);

        $validator = Validator::make($request->all(), [
            'hostel_id' => [
                'required', 'exists:hostels,id',
                function ($attr, $value, $fail) {
                    if (!auth()->user()->hasAccessToHostel($value)) {
                        $fail('You do not have access to this hostel.');
                    }
                },
            ],
            'room_id' => 'required|exists:rooms,id',
            'bed_id'  => [
                'required', 'exists:beds,id',
                function ($attr, $value, $fail) use ($request) {
                    $bed = Bed::find($value);
                    if ($bed && $bed->room_id != $request->room_id) {
                        $fail('Selected bed does not belong to the selected room.');
                    }
                    if ($bed && $bed->status !== 'VACANT') {
                        $fail('Selected bed is not vacant.');
                    }
                },
            ],
            'resident_code'    => 'required|string|max:50|unique:residents,resident_code',
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'parentsphone'     => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'aadhaar_no'       => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'dob'              => 'required|date',
            'joining_date'     => 'required|date',
            'food_status'      => 'required|in:WITH_FOOD,WITHOUT_FOOD',
            'rent_amount'      => 'required|numeric|min:0',
            'deposit_amount'   => 'nullable|numeric|min:0',
            'status'           => 'nullable|in:ACTIVE,VACATED',
            'biometric_access' => 'nullable|boolean',

            'profile_image'        => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'aadhar_document'      => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'application_document' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->except(['profile_image', 'aadhar_document', 'application_document']);

            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $this->saveFile($request->file('profile_image'), 'profile');
            }
            if ($request->hasFile('aadhar_document')) {
                $data['aadhar_document'] = $this->saveFile($request->file('aadhar_document'), 'aadhar');
            }
            if ($request->hasFile('application_document')) {
                $data['application_document'] = $this->saveFile($request->file('application_document'), 'application');
            }

            $data['status']           = $data['status'] ?? 'ACTIVE';
            $data['biometric_access'] = $request->has('biometric_access') ? (bool) $request->biometric_access : true;
            $data['deposit_amount']   = $data['deposit_amount'] ?? 0;

            // Step 1 — insert with employee_code = null (column is nullable)
            $data['employee_code'] = null;
            $resident = Resident::create($data);

            // Step 2 — build the employee code from hostel prefix + resident id
            $employeeCode = $this->generateEmployeeCode($resident->hostel_id, $resident->id);
            $employeeCode = $this->ensureUniqueEmployeeCode($employeeCode, $resident->id);

            // Step 3 — persist it
            $resident->update(['employee_code' => $employeeCode]);

            $this->markBedOccupied($resident->bed_id);
            $this->updateRoomStatus($resident->room_id);

            DB::commit();

            $resident = $resident->fresh()->load(['hostel', 'room', 'bed']);

            return response()->json([
                'success'  => true,
                'message'  => 'Resident registered successfully! Code: ' . $resident->resident_code,
                'resident' => $resident,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to register resident: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single resident.
     */
    public function show($id)
    {
        try {
            $resident = Resident::with(['hostel', 'room', 'bed'])->findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
                return response()->json(['success' => false, 'message' => 'No access'], 403);
            }

            return response()->json([
                'success'  => true,
                'resident' => $resident,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
    }

    /**
     * Update resident — codes remain unchanged.
     */
    public function update(Request $request, $id)
    {
        $resident = Resident::findOrFail($id);

        if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        $request->merge([
            'resident_code' => $resident->resident_code,
            'employee_code' => $resident->employee_code,
        ]);

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id',
            'room_id'   => 'required|exists:rooms,id',
            'bed_id'    => 'required|exists:beds,id',
            'resident_code' => [
                'required', 'string', 'max:50',
                Rule::unique('residents', 'resident_code')->ignore($id),
            ],
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'parentsphone'     => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'aadhaar_no'       => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'dob'              => 'required|date',
            'joining_date'     => 'required|date',
            'vacate_date'      => 'nullable|date',
            'food_status'      => 'required|in:WITH_FOOD,WITHOUT_FOOD',
            'rent_amount'      => 'required|numeric|min:0',
            'deposit_amount'   => 'nullable|numeric|min:0',
            'status'           => 'required|in:ACTIVE,VACATED',
            'employee_code'    => [
                'nullable', 'string', 'max:50',
                Rule::unique('residents', 'employee_code')->ignore($id),
            ],
            'biometric_access' => 'nullable|boolean',

            'profile_image'        => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'aadhar_document'      => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'application_document' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $oldBedId  = $resident->bed_id;
            $oldRoomId = $resident->room_id;
            $oldStatus = $resident->status;

            $data = $request->except(['profile_image', 'aadhar_document', 'application_document']);

            if ($request->hasFile('profile_image')) {
                $this->deleteFile($resident->profile_image);
                $data['profile_image'] = $this->saveFile($request->file('profile_image'), 'profile');
            }
            if ($request->hasFile('aadhar_document')) {
                $this->deleteFile($resident->aadhar_document);
                $data['aadhar_document'] = $this->saveFile($request->file('aadhar_document'), 'aadhar');
            }
            if ($request->hasFile('application_document')) {
                $this->deleteFile($resident->application_document);
                $data['application_document'] = $this->saveFile($request->file('application_document'), 'application');
            }

            $data['biometric_access'] = $request->has('biometric_access') ? (bool) $request->biometric_access : false;
            $data['deposit_amount']   = $data['deposit_amount'] ?? 0;

            if ($data['status'] === 'VACATED' && empty($data['vacate_date'])) {
                $data['vacate_date'] = now()->format('Y-m-d');
            }
            if ($data['status'] === 'ACTIVE') {
                $data['vacate_date'] = null;
            }

            $resident->update($data);

            if ($oldBedId != $resident->bed_id) {
                $this->freeBed($oldBedId);
                $this->markBedOccupied($resident->bed_id);
            }

            if ($oldStatus === 'ACTIVE' && $data['status'] === 'VACATED') {
                $this->freeBed($resident->bed_id);
                $this->disableBiometric($resident);
            }

            if ($oldStatus === 'VACATED' && $data['status'] === 'ACTIVE') {
                $this->markBedOccupied($resident->bed_id);
            }

            $this->updateRoomStatus($oldRoomId);
            if ($oldRoomId != $resident->room_id) {
                $this->updateRoomStatus($resident->room_id);
            }

            DB::commit();

            $resident = $resident->fresh()->load(['hostel', 'room', 'bed']);

            return response()->json([
                'success'  => true,
                'message'  => 'Resident updated successfully!',
                'resident' => $resident,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete resident.
     */
    public function destroy($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
                return response()->json(['success' => false, 'message' => 'No access'], 403);
            }

            if ($resident->payments()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete resident with payment history. Use "Vacate" instead.',
                ], 422);
            }

            DB::beginTransaction();
            try {
                $bedId  = $resident->bed_id;
                $roomId = $resident->room_id;

                $this->deleteFile($resident->profile_image);
                $this->deleteFile($resident->aadhar_document);
                $this->deleteFile($resident->application_document);

                $resident->delete();

                $this->freeBed($bedId);
                $this->updateRoomStatus($roomId);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Resident deleted successfully!',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vacate resident.
     */
    public function vacate($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
                return response()->json(['success' => false, 'message' => 'No access'], 403);
            }

            if ($resident->status === 'VACATED') {
                return response()->json(['success' => false, 'message' => 'Already vacated'], 422);
            }

            DB::beginTransaction();
            try {
                $resident->update([
                    'status'             => 'VACATED',
                    'vacate_date'        => now()->format('Y-m-d'),
                    'biometric_access'   => false,
                    'access_disabled_at' => now(),
                ]);

                $this->freeBed($resident->bed_id);
                $this->updateRoomStatus($resident->room_id);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Resident vacated successfully! Bed is now available.',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to vacate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reactivate vacated resident.
     */
    public function reactivate($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
                return response()->json(['success' => false, 'message' => 'No access'], 403);
            }

            if ($resident->status === 'ACTIVE') {
                return response()->json(['success' => false, 'message' => 'Already active'], 422);
            }

            $bed = Bed::find($resident->bed_id);
            if (!$bed || $bed->status !== 'VACANT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reactivate — the assigned bed is no longer vacant. Please reassign.',
                ], 422);
            }

            DB::beginTransaction();
            try {
                $resident->update([
                    'status'            => 'ACTIVE',
                    'vacate_date'       => null,
                    'biometric_access'  => true,
                    'access_enabled_at' => now(),
                ]);

                $this->markBedOccupied($resident->bed_id);
                $this->updateRoomStatus($resident->room_id);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Resident reactivated successfully!',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vacant beds for a room (AJAX).
     */
    public function getVacantBeds($roomId)
    {
        $room = Room::find($roomId);
        if (!$room || !auth()->user()->hasAccessToHostel($room->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        $beds = Bed::where('room_id', $roomId)
            ->where('status', 'VACANT')
            ->orderBy('bed_no')
            ->get(['id', 'bed_no', 'bed_type']);

        return response()->json(['success' => true, 'beds' => $beds]);
    }

    /* =========================================================
     |  🔑 EMPLOYEE CODE REGENERATION
     ========================================================= */

    /**
     * Regenerate the employee_code for a single resident.
     * Format: {hostel.employee_code_prefix} + {resident.id}  (numeric addition)
     * Example: prefix 1000 + id 5 → 1005
     */
    public function regenerateEmployeeCode($id)
    {
        try {
            $resident = Resident::findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
                return response()->json(['success' => false, 'message' => 'No access'], 403);
            }

            DB::beginTransaction();
            try {
                $newCode = $this->generateEmployeeCode($resident->hostel_id, $resident->id);
                $newCode = $this->ensureUniqueEmployeeCode($newCode, $resident->id);

                if ($resident->employee_code === $newCode) {
                    DB::commit();
                    return response()->json([
                        'success'       => true,
                        'message'       => 'Employee code is already up to date.',
                        'employee_code' => $newCode,
                    ]);
                }

                $resident->update(['employee_code' => $newCode]);

                DB::commit();

                return response()->json([
                    'success'       => true,
                    'message'       => 'Employee code regenerated: ' . $newCode,
                    'employee_code' => $newCode,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate employee_codes for all residents (optionally filtered by hostel).
     */
    public function regenerateAllEmployeeCodes(Request $request)
    {
        $user = auth()->user();

        $query = Resident::query();
        if (!$user->isAdmin()) {
            $query->whereIn('hostel_id', $user->hostel_ids ?? []);
        }
        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        $residents = $query->get();
        $updated = 0;
        $failed  = [];

        DB::beginTransaction();
        try {
            foreach ($residents as $resident) {
                try {
                    $newCode = $this->generateEmployeeCode($resident->hostel_id, $resident->id);
                    $newCode = $this->ensureUniqueEmployeeCode($newCode, $resident->id);

                    if ($resident->employee_code !== $newCode) {
                        $resident->update(['employee_code' => $newCode]);
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed[] = ['id' => $resident->id, 'error' => $e->getMessage()];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Regenerated {$updated} employee code(s).",
                'updated' => $updated,
                'failed'  => $failed,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk regeneration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  AUTO-GENERATORS
     ========================================================= */

    /**
     * Generate the next resident code: RES-0001, RES-0002, ...
     */
    protected function generateResidentCode(): string
    {
        $prefix  = 'RES-';
        $padding = 4;

        $last = Resident::where('resident_code', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(resident_code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->value('resident_code');

        $nextNumber = 1;
        if ($last) {
            $num = (int) substr($last, strlen($prefix));
            $nextNumber = $num + 1;
        }

        $code = $prefix . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);

        while (Resident::where('resident_code', $code)->exists()) {
            $nextNumber++;
            $code = $prefix . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * Generate employee code = {hostel.employee_code_prefix} + {resident.id}.
     *
     * ⚠️ NOTE: This is NUMERIC ADDITION (not concatenation).
     * Example: prefix 1000 + id 5 → 1005
     *          prefix 1000 + id 40 → 1040
     */
    protected function generateEmployeeCode($hostelId, int $residentId): string
    {
        $prefix = 1000; // default fallback (integer)

        if ($hostelId) {
            $hostel = Hostel::find($hostelId);
            if ($hostel && $hostel->employee_code_prefix !== null && $hostel->employee_code_prefix !== '') {
                $prefix = (int) $hostel->employee_code_prefix;
            }
        }

        // ➕ NUMERIC ADDITION (not string concatenation)
        return (string) ($prefix + $residentId);
    }

    /**
     * Ensure the generated employee code is unique.
     * @param  string    $code
     * @param  int|null  $ignoreId  Skip this resident id (for updates)
     */
    protected function ensureUniqueEmployeeCode(string $code, ?int $ignoreId = null): string
    {
        $check = function ($candidate) use ($ignoreId) {
            $q = Resident::where('employee_code', $candidate);
            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }
            return $q->exists();
        };

        if (!$check($code)) {
            return $code;
        }

        // If collision, append suffix -1, -2, ...
        $suffix = 1;
        do {
            $candidate = $code . '-' . $suffix;
            $suffix++;
        } while ($check($candidate));

        return $candidate;
    }

    /* =========================================================
     |  HELPERS
     ========================================================= */

    protected function saveFile($file, $prefix): string
    {
        $uploadPath = public_path('assets/residents');

        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $filename);

        return $filename;
    }

    protected function deleteFile(?string $filename): void
    {
        if ($filename) {
            $path = public_path('assets/residents/' . $filename);
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    protected function markBedOccupied($bedId): void
    {
        Bed::where('id', $bedId)->update(['status' => 'OCCUPIED']);
    }

    protected function freeBed($bedId): void
    {
        Bed::where('id', $bedId)->update(['status' => 'VACANT']);
    }

    protected function updateRoomStatus($roomId): void
    {
        $room = Room::find($roomId);
        if (!$room) return;

        $total    = $room->beds()->count();
        $occupied = $room->beds()->where('status', 'OCCUPIED')->count();

        if ($occupied === 0) {
            $status = 'VACANT';
        } elseif ($occupied >= $total) {
            $status = 'FULL';
        } else {
            $status = 'PARTIAL';
        }

        $room->update(['status' => $status]);
    }

    protected function disableBiometric($resident): void
    {
        $resident->update([
            'biometric_access'   => false,
            'access_disabled_at' => now(),
        ]);
    }
}
