<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BedController extends Controller
{
    /**
     * Display bed management page
     */
    public function index()
    {
        $user = auth()->user();

        $query = Bed::with(['room.hostel', 'room.roomType', 'residents'])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin()) {
            $query->whereHas('room', function ($q) use ($user) {
                $q->whereIn('hostel_id', $user->hostel_ids ?? []);
            });
        }

        $beds = $query->get();

        // Rooms for filter dropdown
        $roomQuery = Room::with('hostel')->orderBy('room_no');
        if (!$user->isAdmin()) {
            $roomQuery->whereIn('hostel_id', $user->hostel_ids ?? []);
        }
        $rooms = $roomQuery->get();

        return view('admin.beds.index', compact('beds', 'rooms'));
    }

    /**
     * Store new bed
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => [
                'required', 'exists:rooms,id',
                function ($attr, $value, $fail) {
                    $room = Room::find($value);
                    if ($room && !auth()->user()->hasAccessToHostel($room->hostel_id)) {
                        $fail('You do not have access to this room.');
                    }
                },
            ],
            'bed_no'   => [
                'required', 'string', 'max:20',
                \Illuminate\Validation\Rule::unique('beds')->where(function ($q) use ($request) {
                    return $q->where('room_id', $request->room_id);
                })
            ],
            'bed_type' => 'required|in:NORMAL,BUNKER',
            'status'   => 'required|in:VACANT,OCCUPIED,BLOCKED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $bed = Bed::create($request->all());
            $bed->load(['room.hostel', 'room.roomType']);

            // Update room cot counts
            $this->syncRoomCotCounts($bed->room);

            return response()->json([
                'success' => true,
                'message' => 'Bed created successfully!',
                'bed'     => $bed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single bed
     */
    public function show($id)
    {
        try {
            $bed = Bed::with(['room.hostel', 'room.roomType', 'residents'])->findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($bed->room->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this bed.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'bed'     => $bed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found'
            ], 404);
        }
    }

    /**
     * Update bed
     */
    public function update(Request $request, $id)
    {
        $bed = Bed::with('room')->findOrFail($id);

        if (!auth()->user()->hasAccessToHostel($bed->room->hostel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this bed.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'bed_no'   => [
                'required', 'string', 'max:20',
                \Illuminate\Validation\Rule::unique('beds')->where(function ($q) use ($bed) {
                    return $q->where('room_id', $bed->room_id);
                })->ignore($id)
            ],
            'bed_type' => 'required|in:NORMAL,BUNKER',
            'status'   => 'required|in:VACANT,OCCUPIED,BLOCKED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // Prevent marking OCCUPIED bed as VACANT manually if resident exists
        if ($bed->status === 'OCCUPIED' && $request->status !== 'OCCUPIED') {
            if ($bed->residents()->where('status', 'ACTIVE')->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change status. Resident is assigned to this bed.'
                ], 422);
            }
        }

        try {
            $oldType = $bed->bed_type;
            $bed->update($request->all());
            $bed->load(['room.hostel', 'room.roomType']);

            // If bed_type changed, sync counts
            if ($oldType !== $request->bed_type) {
                $this->syncRoomCotCounts($bed->room);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bed updated successfully!',
                'bed'     => $bed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete bed
     */
    public function destroy($id)
    {
        try {
            $bed = Bed::with('room')->findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($bed->room->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this bed.'
                ], 403);
            }

            // Cannot delete occupied bed
            if ($bed->status === 'OCCUPIED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete occupied bed. Remove resident first.'
                ], 422);
            }

            // Cannot delete if residents reference it
            if ($bed->residents()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete bed. Residents are associated with it.'
                ], 422);
            }

            $room = $bed->room;
            $bed->delete();

            // Sync cot counts after deletion
            $this->syncRoomCotCounts($room);

            return response()->json([
                'success' => true,
                'message' => 'Bed deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle bed status
     */
    public function toggleStatus($id)
    {
        try {
            $bed = Bed::with('room')->findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($bed->room->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this bed.'
                ], 403);
            }

            // Cycle: VACANT → BLOCKED → VACANT (can't manually set OCCUPIED)
            if ($bed->status === 'OCCUPIED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Occupied bed status is managed by residents.'
                ], 422);
            }

            $bed->status = $bed->status === 'VACANT' ? 'BLOCKED' : 'VACANT';
            $bed->save();

            return response()->json([
                'success' => true,
                'message' => 'Bed status updated!',
                'status'  => $bed->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Sync room's normal + bunker cot counts based on actual beds
     */
    protected function syncRoomCotCounts(Room $room): void
    {
        $normal = $room->beds()->where('bed_type', 'NORMAL')->count();
        $bunker = $room->beds()->where('bed_type', 'BUNKER')->count();

        $room->update([
            'normal_cot_count' => $normal,
            'bunker_cot_count' => $bunker,
        ]);
    }
}
