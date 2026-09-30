<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Bed;
use App\Models\Hostel;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Display room management page
     */
    public function index()
    {
        $user = auth()->user();

        $query = Room::with(['hostel', 'roomType', 'beds'])
            ->withCount(['beds', 'residents'])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin()) {
            $query->whereIn('hostel_id', $user->hostel_ids ?? []);
        }

        $rooms = $query->get();

        // Accessible hostels
        $hostelQuery = Hostel::orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }
        $hostels = $hostelQuery->get(['id', 'hostel_name', 'hostel_code']);

        // Room types for dropdown
        $roomTypeQuery = RoomType::with('hostel')->orderBy('room_type_name');
        if (!$user->isAdmin()) {
            $roomTypeQuery->whereIn('hostel_id', $user->hostel_ids ?? []);
        }
        $roomTypes = $roomTypeQuery->get();

        return view('admin.rooms.index', compact('rooms', 'hostels', 'roomTypes'));
    }

    /**
     * Store new room + auto-create beds
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hostel_id' => [
                'required', 'exists:hostels,id',
                function ($attr, $value, $fail) {
                    if (!auth()->user()->hasAccessToHostel($value)) {
                        $fail('You do not have access to this hostel.');
                    }
                },
            ],
            'room_type_id'      => 'required|exists:room_types,id',
            'room_no'           => [
                'required', 'string', 'max:20',
                Rule::unique('rooms')->where(function ($q) use ($request) {
                    return $q->where('hostel_id', $request->hostel_id);
                })
            ],
            'normal_cot_count'  => 'required|integer|min:0',
            'bunker_cot_count'  => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $totalBeds = (int) $request->normal_cot_count + (int) $request->bunker_cot_count;

        if ($totalBeds < 1) {
            return response()->json([
                'success' => false,
                'errors'  => ['normal_cot_count' => ['At least 1 bed is required.']]
            ], 422);
        }

        $roomType = RoomType::findOrFail($request->room_type_id);

        if ($roomType->hostel_id != $request->hostel_id) {
            return response()->json([
                'success' => false,
                'errors'  => ['room_type_id' => ['Selected room type does not belong to the selected hostel.']]
            ], 422);
        }

        // Validate total matches room type's sharing count
        if ($roomType->sharing_count > 0 && $totalBeds !== $roomType->sharing_count) {
            return response()->json([
                'success' => false,
                'errors'  => ['normal_cot_count' => [
                    "Total beds ({$totalBeds}) must match room type sharing count ({$roomType->sharing_count})."
                ]]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $room = Room::create([
                'hostel_id'         => $request->hostel_id,
                'room_type_id'      => $request->room_type_id,
                'room_no'           => $request->room_no,
                'normal_cot_count'  => (int) $request->normal_cot_count,
                'bunker_cot_count'  => (int) $request->bunker_cot_count,
                'status'            => 'VACANT',
            ]);

            $this->createBedsForRoom($room);

            DB::commit();

            $room = $room->fresh()->load(['hostel', 'roomType'])->loadCount(['beds', 'residents']);

            return response()->json([
                'success' => true,
                'message' => "Room created with {$totalBeds} bed(s)!",
                'room'    => $room
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single room
     */
    public function show($id)
    {
        try {
            $room = Room::with(['hostel', 'roomType', 'beds'])
                ->withCount(['beds', 'residents'])
                ->findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($room->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'room'    => $room
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }
    }

    /**
     * Update room with smart bed adjustment
     */
    public function update(Request $request, $id)
    {
        $room = Room::with(['beds'])->findOrFail($id);

        if (!auth()->user()->hasAccessToHostel($room->hostel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this room.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'hostel_id'         => 'required|exists:hostels,id',
            'room_type_id'      => 'required|exists:room_types,id',
            'room_no'           => [
                'required', 'string', 'max:20',
                Rule::unique('rooms')->where(function ($q) use ($request) {
                    return $q->where('hostel_id', $request->hostel_id);
                })->ignore($id)
            ],
            'normal_cot_count'  => 'required|integer|min:0',
            'bunker_cot_count'  => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $newNormal = (int) $request->normal_cot_count;
        $newBunker = (int) $request->bunker_cot_count;
        $newTotal  = $newNormal + $newBunker;

        if ($newTotal < 1) {
            return response()->json([
                'success' => false,
                'errors'  => ['normal_cot_count' => ['At least 1 bed is required.']]
            ], 422);
        }

        $newRoomType = RoomType::findOrFail($request->room_type_id);

        if ($newRoomType->hostel_id != $request->hostel_id) {
            return response()->json([
                'success' => false,
                'errors'  => ['room_type_id' => ['Selected room type does not belong to the selected hostel.']]
            ], 422);
        }

        if ($newRoomType->sharing_count > 0 && $newTotal !== $newRoomType->sharing_count) {
            return response()->json([
                'success' => false,
                'errors'  => ['normal_cot_count' => [
                    "Total beds ({$newTotal}) must match room type sharing count ({$newRoomType->sharing_count})."
                ]]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldNormal = $room->beds()->where('bed_type', 'NORMAL')->count();
            $oldBunker = $room->beds()->where('bed_type', 'BUNKER')->count();
            $oldTotal  = $oldNormal + $oldBunker;

            if ($newTotal > $oldTotal) {
                // Increase — add beds
                $this->addBeds($room, $newNormal, $newBunker, $oldNormal, $oldBunker);

            } elseif ($newTotal < $oldTotal) {
                // Decrease — remove vacant beds only
                $result = $this->removeBeds($room, $newNormal, $newBunker, $oldNormal, $oldBunker);

                if (!$result['success']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $result['message']
                    ], 422);
                }
            } else {
                // Same total — rebalance normal/bunker split if changed
                $result = $this->rebalanceBeds($room, $newNormal, $newBunker, $oldNormal, $oldBunker);
                if (!$result['success']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $result['message']
                    ], 422);
                }
            }

            $room->update([
                'hostel_id'         => $request->hostel_id,
                'room_type_id'      => $request->room_type_id,
                'room_no'           => $request->room_no,
                'normal_cot_count'  => $newNormal,
                'bunker_cot_count'  => $newBunker,
            ]);

            $this->recalculateRoomStatus($room);

            DB::commit();

            $room = $room->fresh()->load(['hostel', 'roomType'])->loadCount(['beds', 'residents']);

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully!',
                'room'    => $room
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete room (only if no residents)
     */
    public function destroy($id)
    {
        try {
            $room = Room::findOrFail($id);

            if (!auth()->user()->hasAccessToHostel($room->hostel_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room.'
                ], 403);
            }

            $occupiedBeds = $room->beds()->where('status', 'OCCUPIED')->count();
            if ($occupiedBeds > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete room. {$occupiedBeds} bed(s) are occupied by residents."
                ], 422);
            }

            DB::beginTransaction();
            try {
                $room->beds()->delete();
                $room->delete();
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Room and all beds deleted successfully!'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    protected function createBedsForRoom(Room $room): void
    {
        $counter = 1;

        // Normal beds
        for ($i = 0; $i < $room->normal_cot_count; $i++) {
            Bed::create([
                'room_id'  => $room->id,
                'bed_no'   => (string) $counter++,
                'bed_type' => 'NORMAL',
                'status'   => 'VACANT',
            ]);
        }

        // Bunker beds
        for ($i = 0; $i < $room->bunker_cot_count; $i++) {
            Bed::create([
                'room_id'  => $room->id,
                'bed_no'   => (string) $counter++,
                'bed_type' => 'BUNKER',
                'status'   => 'VACANT',
            ]);
        }
    }

    protected function addBeds(Room $room, int $newNormal, int $newBunker, int $oldNormal, int $oldBunker): void
    {
        $maxBedNo = (int) ($room->beds()->max('bed_no') ?? 0);

        $toAddNormal = max(0, $newNormal - $oldNormal);
        for ($i = 0; $i < $toAddNormal; $i++) {
            Bed::create([
                'room_id'  => $room->id,
                'bed_no'   => (string) ++$maxBedNo,
                'bed_type' => 'NORMAL',
                'status'   => 'VACANT',
            ]);
        }

        $toAddBunker = max(0, $newBunker - $oldBunker);
        for ($i = 0; $i < $toAddBunker; $i++) {
            Bed::create([
                'room_id'  => $room->id,
                'bed_no'   => (string) ++$maxBedNo,
                'bed_type' => 'BUNKER',
                'status'   => 'VACANT',
            ]);
        }
    }

    protected function removeBeds(Room $room, int $newNormal, int $newBunker, int $oldNormal, int $oldBunker): array
    {
        $normalToRemove = max(0, $oldNormal - $newNormal);
        $bunkerToRemove = max(0, $oldBunker - $newBunker);

        $occupiedNumbers = [];

        if ($normalToRemove > 0) {
            $normalBeds = Bed::where('room_id', $room->id)
                ->where('bed_type', 'NORMAL')
                ->orderByDesc('bed_no')
                ->limit($normalToRemove)
                ->get();

            foreach ($normalBeds as $bed) {
                if ($bed->status === 'OCCUPIED') {
                    $occupiedNumbers[] = $bed->bed_no;
                }
            }
        }

        if ($bunkerToRemove > 0) {
            $bunkerBeds = Bed::where('room_id', $room->id)
                ->where('bed_type', 'BUNKER')
                ->orderByDesc('bed_no')
                ->limit($bunkerToRemove)
                ->get();

            foreach ($bunkerBeds as $bed) {
                if ($bed->status === 'OCCUPIED') {
                    $occupiedNumbers[] = $bed->bed_no;
                }
            }
        }

        if (!empty($occupiedNumbers)) {
            $list = implode(', ', $occupiedNumbers);
            return [
                'success' => false,
                'message' => "Cannot reduce beds. Bed(s) {$list} are OCCUPIED by residents."
            ];
        }

        // Delete them
        if ($normalToRemove > 0) {
            Bed::where('room_id', $room->id)
                ->where('bed_type', 'NORMAL')
                ->orderByDesc('bed_no')
                ->limit($normalToRemove)
                ->get()
                ->each(fn($b) => $b->delete());
        }

        if ($bunkerToRemove > 0) {
            Bed::where('room_id', $room->id)
                ->where('bed_type', 'BUNKER')
                ->orderByDesc('bed_no')
                ->limit($bunkerToRemove)
                ->get()
                ->each(fn($b) => $b->delete());
        }

        return ['success' => true, 'message' => 'Beds removed'];
    }

    protected function rebalanceBeds(Room $room, int $newNormal, int $newBunker, int $oldNormal, int $oldBunker): array
    {
        if ($newNormal === $oldNormal && $newBunker === $oldBunker) {
            return ['success' => true, 'message' => 'No change'];
        }

        // Remove first
        if ($newNormal < $oldNormal || $newBunker < $oldBunker) {
            $result = $this->removeBeds($room, $newNormal, $newBunker, $oldNormal, $oldBunker);
            if (!$result['success']) return $result;
        }

        // Then add
        $currentNormal = $room->beds()->where('bed_type', 'NORMAL')->count();
        $currentBunker = $room->beds()->where('bed_type', 'BUNKER')->count();

        if ($newNormal > $currentNormal || $newBunker > $currentBunker) {
            $this->addBeds($room, $newNormal, $newBunker, $currentNormal, $currentBunker);
        }

        return ['success' => true, 'message' => 'Rebalanced'];
    }

    protected function recalculateRoomStatus(Room $room): void
    {
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
}
