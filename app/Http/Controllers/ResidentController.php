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
use Barryvdh\DomPDF\Facade\Pdf;

class ResidentController extends Controller
{
    /* =========================================================
     |  INDEX
     ========================================================= */

    public function index(Request $request)
    {
        $user = auth()->user();

        $residents = $this->buildFilteredQuery($request)->get();

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
        $statusFilter     = $request->input('status', 'active');

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

    /* =========================================================
     |  LIVE FILTER (AJAX)
     ========================================================= */

    public function filter(Request $request)
    {
        $residents = $this->buildFilteredQuery($request)->get();

        $html = view('admin.residents._cards', compact('residents'))->render();

        return response()->json([
            'success' => true,
            'count'   => $residents->count(),
            'html'    => $html,
        ]);
    }

    /* =========================================================
     |  SHARED FILTER BUILDER (Room No → Bed No → Name)
     ========================================================= */

    protected function buildFilteredQuery(Request $request)
    {
        $user = auth()->user();

        $query = Resident::with(['hostel', 'room', 'bed'])
            ->leftJoin('rooms as r_ord', 'residents.room_id', '=', 'r_ord.id')
            ->leftJoin('beds as b_ord', 'residents.bed_id', '=', 'b_ord.id')
            ->orderByRaw('CAST(r_ord.room_no AS UNSIGNED) ASC')
            ->orderByRaw('CAST(b_ord.bed_no AS UNSIGNED) ASC')
            ->orderBy('residents.name', 'ASC')
            ->select('residents.*');

        if (!$user->isAdmin()) {
            $query->whereIn('residents.hostel_id', $user->hostel_ids ?? []);
        }

        $statusFilter = $request->input('status', 'active');
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('residents.status', strtoupper($statusFilter));
        }

        if ($request->filled('hostel_id')) {
            $query->where('residents.hostel_id', $request->hostel_id);
        }

        if ($request->filled('food_status')) {
            $query->where('residents.food_status', $request->food_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('residents.name', 'like', "%{$search}%")
                  ->orWhere('residents.resident_code', 'like', "%{$search}%")
                  ->orWhere('residents.phone', 'like', "%{$search}%")
                  ->orWhere('residents.employee_code', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /* =========================================================
     |  STORE
     ========================================================= */

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

            $data['employee_code'] = null;
            $resident = Resident::create($data);

            $employeeCode = $this->generateEmployeeCode($resident->hostel_id, $resident->id);
            $employeeCode = $this->ensureUniqueEmployeeCode($employeeCode, $resident->id);
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

    /* =========================================================
     |  SHOW
     ========================================================= */

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

    /* =========================================================
     |  UPDATE
     ========================================================= */

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

    /* =========================================================
     |  DESTROY
     ========================================================= */

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

    /* =========================================================
     |  VACATE
     ========================================================= */

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

    /* =========================================================
     |  REACTIVATE
     ========================================================= */

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

    /* =========================================================
     |  VACANT BEDS
     ========================================================= */

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
     |  📥 EXPORT EXCEL (CSV)
     ========================================================= */

    public function exportExcel(Request $request)
    {
        $residents = $this->buildFilteredQuery($request)->get();

        $filename = 'residents_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Resident Code', 'Employee Code', 'Name', 'Phone', 'Parent Phone',
            'Email', 'Aadhaar No', 'Address', 'DOB', 'Joining Date', 'Vacate Date',
            'Hostel', 'Room No', 'Bed No', 'Food Status', 'Rent Amount',
            'Deposit Amount', 'Status', 'Biometric', 'Created At',
        ];

        $callback = function () use ($residents, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            foreach ($residents as $r) {
                fputcsv($file, [
                    $r->resident_code,
                    $r->employee_code,
                    $r->name,
                    $r->phone,
                    $r->parentsphone,
                    $r->email,
                    $r->aadhaar_no,
                    $r->address,
                    $r->dob ? $r->dob->format('Y-m-d') : '',
                    $r->joining_date ? $r->joining_date->format('Y-m-d') : '',
                    $r->vacate_date ? $r->vacate_date->format('Y-m-d') : '',
                    $r->hostel->hostel_name ?? '',
                    $r->room->room_no ?? '',
                    $r->bed->bed_no ?? '',
                    $r->food_status === 'WITH_FOOD' ? 'With Food' : 'Without Food',
                    $r->rent_amount,
                    $r->deposit_amount,
                    ucfirst(strtolower($r->status)),
                    $r->biometric_access ? 'Yes' : 'No',
                    $r->created_at ? $r->created_at->format('Y-m-d H:i') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* =========================================================
     |  📥 EXPORT PDF
     ========================================================= */

    public function exportPdf(Request $request)
    {
        $residents = $this->buildFilteredQuery($request)->get();

        $filters = [
            'status'      => $request->input('status', 'active'),
            'hostel'      => null,
            'food_status' => $request->input('food_status'),
            'search'      => $request->input('search'),
        ];

        if ($request->filled('hostel_id')) {
            $filters['hostel'] = Hostel::find($request->hostel_id)?->hostel_name;
        }

        $pdf = Pdf::loadView('admin.residents.pdf', [
            'residents' => $residents,
            'filters'   => $filters,
            'generated' => now()->format('d M Y, h:i A'),
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isRemoteEnabled' => true,
            'defaultFont'     => 'DejaVu Sans',
        ]);

        $filename = 'residents_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /* =========================================================
     |  🔑 REGENERATE EMPLOYEE CODE (single)
     ========================================================= */

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

    /* =========================================================
     |  🔑 REGENERATE ALL EMPLOYEE CODES
     ========================================================= */

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
     |  🏠 VACANCY REPORT — JSON (AJAX)
     ========================================================= */

    public function vacancyReport(Request $request)
    {
        $data = $this->buildVacancyData($request);

        return response()->json([
            'success' => true,
            'rows'    => $data['rows'],
            'summary' => $data['summary'],
        ]);
    }

    /* =========================================================
     |  🏠 VACANCY DATA BUILDER (shared)
     ========================================================= */

    protected function buildVacancyData(Request $request): array
    {
        $user = auth()->user();

        $hostelId = $request->input('hostel_id');
        $roomNo   = $request->input('room_no');
        $bedNo    = $request->input('bed_no');
        $status   = strtoupper($request->input('status') ?? '');
        $search   = $request->input('search');

        // Rooms + beds + beds.resident (ACTIVE only)
        $roomQuery = Room::with([
            'hostel',
            'roomType',
            'beds' => function ($q) {
                $q->orderByRaw('CAST(bed_no AS UNSIGNED) ASC');
            },
            'beds.resident' => function ($q) {
                $q->where('status', 'ACTIVE');
            },
        ])->orderByRaw('CAST(room_no AS UNSIGNED) ASC');

        if (!$user->isAdmin()) {
            $roomQuery->whereIn('hostel_id', $user->hostel_ids ?? []);
        }
        if ($hostelId) {
            $roomQuery->where('hostel_id', $hostelId);
        }
        if ($roomNo) {
            $roomQuery->where('room_no', 'LIKE', "%{$roomNo}%");
        }
        if ($bedNo) {
            $roomQuery->whereHas('beds', function ($q) use ($bedNo) {
                $q->where('bed_no', 'LIKE', "%{$bedNo}%");
            });
        }

        $rooms = $roomQuery->get();

        $rows = [];
        $summary = [
            'total_rooms'      => 0,
            'total_beds'       => 0,
            'occupied_beds'    => 0,
            'vacant_beds'      => 0,
            'fully_occupied'   => 0,
            'partial_occupied' => 0,
            'fully_vacant'     => 0,
            'occupancy_rate'   => 0,
        ];

        foreach ($rooms as $room) {
            $bedsArr       = [];
            $occupiedCount = 0;
            $vacantCount   = 0;

            foreach ($room->beds as $bed) {
                $resident = $bed->resident;

                if ($resident) {
                    $occupiedCount++;
                    $bedsArr[] = [
                        'bed_id'        => $bed->id,
                        'bed_no'        => $bed->bed_no,
                        'bed_type'      => $bed->bed_type,
                        'status'        => 'OCCUPIED',
                        'resident_id'   => $resident->id,
                        'resident_code' => $resident->resident_code,
                        'resident_name' => $resident->name,
                        'phone'         => $resident->phone,
                        'joining_date'  => $resident->joining_date
                            ? $resident->joining_date->format('d M Y')
                            : null,
                        'rent_amount'   => (float) $resident->rent_amount,
                        'food_status'   => $resident->food_status,
                    ];
                } else {
                    $vacantCount++;
                    $bedsArr[] = [
                        'bed_id'        => $bed->id,
                        'bed_no'        => $bed->bed_no,
                        'bed_type'      => $bed->bed_type,
                        'status'        => 'VACANT',
                        'resident_id'   => null,
                        'resident_code' => null,
                        'resident_name' => null,
                        'phone'         => null,
                        'joining_date'  => null,
                        'rent_amount'   => null,
                        'food_status'   => null,
                    ];
                }
            }

            $totalBeds = count($bedsArr);
            if ($totalBeds === 0) continue;

            if ($occupiedCount === 0) {
                $roomStatus = 'VACANT';
                $summary['fully_vacant']++;
            } elseif ($occupiedCount >= $totalBeds) {
                $roomStatus = 'FULL';
                $summary['fully_occupied']++;
            } else {
                $roomStatus = 'PARTIAL';
                $summary['partial_occupied']++;
            }

            if ($status && $status !== 'ALL' && $status !== $roomStatus) {
                continue;
            }

            if ($search) {
                $needle   = strtolower($search);
                $haystack = strtolower(
                    ($room->hostel->hostel_name ?? '') . ' ' .
                    $room->room_no . ' ' .
                    implode(' ', array_map(fn($b) => (string)($b['resident_name'] ?? ''), $bedsArr)) . ' ' .
                    implode(' ', array_map(fn($b) => (string)($b['resident_code'] ?? ''), $bedsArr))
                );
                if (strpos($haystack, $needle) === false) {
                    continue;
                }
            }

            $rows[] = [
                'room_id'        => $room->id,
                'hostel_id'      => $room->hostel_id,
                'hostel_name'    => $room->hostel->hostel_name ?? 'N/A',
                'room_no'        => $room->room_no,
                'room_type'      => $room->roomType->name ?? ($room->room_type->name ?? null),
                'total_beds'     => $totalBeds,
                'occupied_count' => $occupiedCount,
                'vacant_count'   => $vacantCount,
                'room_status'    => $roomStatus,
                'beds'           => $bedsArr,
            ];

            $summary['total_rooms']++;
            $summary['total_beds']    += $totalBeds;
            $summary['occupied_beds'] += $occupiedCount;
            $summary['vacant_beds']   += $vacantCount;
        }

        if ($summary['total_beds'] > 0) {
            $summary['occupancy_rate'] = round(
                ($summary['occupied_beds'] / $summary['total_beds']) * 100,
                1
            );
        }

        return ['rows' => $rows, 'summary' => $summary];
    }

    /* =========================================================
     |  📥 EXPORT VACANCY EXCEL (CSV)
     ========================================================= */

    public function exportVacancyExcel(Request $request)
    {
        $data    = $this->buildVacancyData($request);
        $rows    = $data['rows'];
        $summary = $data['summary'];

        $filename = 'vacancy-allocation_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $summary) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['ROOM VACANCY & ALLOCATION REPORT']);
            fputcsv($out, ['Generated', now()->format('d M Y, h:i A')]);
            fputcsv($out, []);
            fputcsv($out, ['Total Rooms',        $summary['total_rooms']]);
            fputcsv($out, ['Total Beds',         $summary['total_beds']]);
            fputcsv($out, ['Occupied Beds',      $summary['occupied_beds']]);
            fputcsv($out, ['Vacant Beds',        $summary['vacant_beds']]);
            fputcsv($out, ['Fully Occupied',     $summary['fully_occupied']]);
            fputcsv($out, ['Partially Occupied', $summary['partial_occupied']]);
            fputcsv($out, ['Fully Vacant',       $summary['fully_vacant']]);
            fputcsv($out, ['Occupancy Rate',     $summary['occupancy_rate'] . '%']);
            fputcsv($out, []);

            fputcsv($out, [
                'S.No', 'Hostel', 'Room No', 'Room Type', 'Room Status',
                'Total Beds', 'Occupied', 'Vacant',
                'Bed No', 'Bed Type', 'Bed Status',
                'Resident Code', 'Resident Name', 'Phone',
                'Joining Date', 'Rent', 'Food',
            ]);

            $sno = 1;
            foreach ($rows as $room) {
                foreach ($room['beds'] as $bed) {
                    fputcsv($out, [
                        $sno++,
                        $room['hostel_name'],
                        $room['room_no'],
                        $room['room_type'] ?? '—',
                        $room['room_status'],
                        $room['total_beds'],
                        $room['occupied_count'],
                        $room['vacant_count'],
                        $bed['bed_no'],
                        $bed['bed_type'],
                        $bed['status'],
                        $bed['resident_code'] ?? '—',
                        $bed['resident_name'] ?? '—',
                        $bed['phone'] ?? '—',
                        $bed['joining_date'] ?? '—',
                        $bed['rent_amount'] !== null ? number_format($bed['rent_amount'], 2) : '—',
                        $bed['food_status'] ?? '—',
                    ]);
                }
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* =========================================================
     |  📥 EXPORT VACANCY PDF
     ========================================================= */

    public function exportVacancyPdf(Request $request)
    {
        $data = $this->buildVacancyData($request);

        $pdf = Pdf::loadView('admin.residents.vacancy-pdf', [
            'rows'      => $data['rows'],
            'summary'   => $data['summary'],
            'generated' => now()->format('d M Y, h:i A'),
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isRemoteEnabled' => true,
            'defaultFont'     => 'DejaVu Sans',
        ]);

        return $pdf->download('vacancy-allocation_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /* =========================================================
     |  AUTO-GENERATORS
     ========================================================= */

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

    protected function generateEmployeeCode($hostelId, int $residentId): string
    {
        $prefix = 1000;

        if ($hostelId) {
            $hostel = Hostel::find($hostelId);
            if ($hostel && $hostel->employee_code_prefix !== null && $hostel->employee_code_prefix !== '') {
                $prefix = (int) $hostel->employee_code_prefix;
            }
        }

        return (string) ($prefix + $residentId);
    }

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
