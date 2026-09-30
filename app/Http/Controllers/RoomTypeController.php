<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    /**
     * Display room type page
     * admin  → all room types
     * account → room types of assigned hostels only
     */
    public function index()
    {
        $user = auth()->user();

        $query = RoomType::with('hostel')
            ->withCount('rooms')
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin()) {
            $query->whereIn('hostel_id', $user->hostel_ids ?? []);
        }

        $roomTypes = $query->get();

        $hostelQuery = Hostel::orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }
        $hostels = $hostelQuery->get(['id', 'hostel_name', 'hostel_code']);

        $roomTypeOptions = self::roomTypeOptions();  // ← Add this

        return view('admin.room-types.index', compact('roomTypes', 'hostels', 'roomTypeOptions'));
    }

    /**
     * Store new room type
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hostel_id' => [
                'required',
                'exists:hostels,id',
                function ($attribute, $value, $fail) {
                    if (!auth()->user()->hasAccessToHostel($value)) {
                        $fail('You do not have access to this hostel.');
                    }
                },
            ],
            'room_type_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('room_types')->where(function ($query) use ($request) {
                    return $query->where('hostel_id', $request->hostel_id);
                })
            ],
            'sharing_count'  => 'required|integer|min:1|max:20',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'is_active'      => 'nullable|boolean',
        ], [
            'room_type_name.unique' => 'This room type name already exists for the selected hostel.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['is_active'] = $request->has('is_active')
                ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)
                : true;

            $roomType = RoomType::create($data);
            $roomType->load('hostel')->loadCount('rooms');

            return response()->json([
                'success'  => true,
                'message'  => 'Room type created successfully!',
                'roomType' => $roomType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single room type
     */
    public function show($id)
    {
        try {
            $roomType = RoomType::with('hostel')->withCount('rooms')->findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($roomType->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room type.'
                ], 403);
            }

            return response()->json([
                'success'  => true,
                'roomType' => $roomType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room type not found'
            ], 404);
        }
    }

    /**
     * Update room type
     */
    public function update(Request $request, $id)
    {
        $roomType = RoomType::findOrFail($id);

        // Access check on the CURRENT hostel
        if (!auth()->user()->hasAccessToHostel($roomType->hostel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this room type.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'hostel_id' => [
                'required',
                'exists:hostels,id',
                function ($attribute, $value, $fail) {
                    if (!auth()->user()->hasAccessToHostel($value)) {
                        $fail('You do not have access to this hostel.');
                    }
                },
            ],
            'room_type_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('room_types')->where(function ($query) use ($request) {
                    return $query->where('hostel_id', $request->hostel_id);
                })->ignore($id)
            ],
            'sharing_count'  => 'required|integer|min:1|max:20',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'is_active'      => 'nullable|boolean',
        ], [
            'room_type_name.unique' => 'This room type name already exists for the selected hostel.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['is_active'] = $request->has('is_active')
                ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)
                : false;

            $roomType->update($data);
            $roomType = $roomType->fresh()->load('hostel')->loadCount('rooms');

            return response()->json([
                'success'  => true,
                'message'  => 'Room type updated successfully!',
                'roomType' => $roomType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete room type
     */
    public function destroy($id)
    {
        try {
            $roomType = RoomType::findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($roomType->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room type.'
                ], 403);
            }

            if ($roomType->rooms()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room type with existing rooms.'
                ], 422);
            }

            $roomType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room type deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        try {
            $roomType = RoomType::findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($roomType->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room type.'
                ], 403);
            }

            $roomType->is_active = !$roomType->is_active;
            $roomType->save();

            return response()->json([
                'success'   => true,
                'message'   => 'Room type status updated!',
                'is_active' => $roomType->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }
    /**
     * Predefined room type options (grouped)
     */
    public static function roomTypeOptions(): array
    {
        $grouped = [];

        for ($sharing = 1; $sharing <= 8; $sharing++) {
            $groupLabel = "{$sharing} Sharing";
            $grouped[$groupLabel] = [];

            $variants = [
                ['bed' => 'Normal Cot', 'ac' => 'Non-AC', 'bedCode' => 'NORMAL', 'acCode' => 'NON_AC'],
                ['bed' => 'Normal Cot', 'ac' => 'AC',     'bedCode' => 'NORMAL', 'acCode' => 'AC'],
                ['bed' => 'Bunker Cot', 'ac' => 'Non-AC', 'bedCode' => 'BUNKER', 'acCode' => 'NON_AC'],
                ['bed' => 'Bunker Cot', 'ac' => 'AC',     'bedCode' => 'BUNKER', 'acCode' => 'AC'],
            ];

            foreach ($variants as $v) {
                $label = "{$sharing} Sharing - {$v['bed']} - {$v['ac']}";
                $grouped[$groupLabel][] = [
                    'value'         => $label,
                    'label'         => "{$v['bed']} - {$v['ac']}",
                    'sharing_count' => $sharing,
                    'bed_type'      => $v['bedCode'],
                    'ac_type'       => $v['acCode'],
                ];
            }
        }

        return $grouped;
    }
}
