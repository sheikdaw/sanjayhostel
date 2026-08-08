<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BedController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get rooms based on user role
        if ($user->role === 'admin') {
            $rooms = Room::with(['hostel', 'roomType'])->get();
            $beds = Bed::with(['room', 'room.hostel', 'resident'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $rooms = Room::with(['hostel', 'roomType'])
                ->whereIn('hostel_id', $hostelIds)
                ->get();
            $beds = Bed::with(['room', 'room.hostel', 'resident'])
                ->whereHas('room', function($query) use ($hostelIds) {
                    $query->whereIn('hostel_id', $hostelIds);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Calculate statistics
        $stats = [
            'total' => $beds->count(),
            'vacant' => $beds->where('status', 'VACANT')->count(),
            'occupied' => $beds->where('status', 'OCCUPIED')->count(),
            'blocked' => $beds->where('status', 'BLOCKED')->count(),
            'normal' => $beds->where('bed_type', 'NORMAL')->count(),
            'bunker' => $beds->where('bed_type', 'BUNKER')->count(),
            'occupancy_rate' => $beds->count() > 0 ? round(($beds->where('status', 'OCCUPIED')->count() / $beds->count()) * 100, 1) : 0
        ];

        return view('admin.beds.index', compact('beds', 'rooms', 'stats', 'user'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if user has access to this room's hostel
        $room = Room::find($request->room_id);
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add beds to this room!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'bed_no' => 'required|string|max:50|unique:beds,bed_no,null,id,room_id,' . $request->room_id,
            'bed_type' => 'required|in:NORMAL,BUNKER',
            'status' => 'required|in:VACANT,OCCUPIED,BLOCKED'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if room has capacity for this bed type
        $normalCount = $room->beds()->where('bed_type', 'NORMAL')->count();
        $bunkerCount = $room->beds()->where('bed_type', 'BUNKER')->count();

        if ($request->bed_type == 'NORMAL' && $normalCount >= ($room->normol_cot_count ?? 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Room already has maximum normal cots (' . $room->normol_cot_count . ')'
            ], 422);
        }

        if ($request->bed_type == 'BUNKER' && $bunkerCount >= ($room->bunker_cot_count ?? 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Room already has maximum bunker cots (' . $room->bunker_cot_count . ')'
            ], 422);
        }

        $bed = Bed::create($request->all());

        // Update room status if needed
        $this->updateRoomStatus($room);

        return response()->json([
            'success' => true,
            'message' => 'Bed created successfully!',
            'data' => $bed
        ]);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $bed = Bed::with(['room', 'room.hostel'])->findOrFail($id);

        // Check if user has access to this bed's hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($bed->room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this bed!'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $bed
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $bed = Bed::findOrFail($id);

        // Check if user has access to this bed's hostel
        $room = Room::find($request->room_id);
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this bed!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'bed_no' => 'required|string|max:50|unique:beds,bed_no,' . $id . ',id,room_id,' . $request->room_id,
            'bed_type' => 'required|in:NORMAL,BUNKER',
            'status' => 'required|in:VACANT,OCCUPIED,BLOCKED'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If bed type is changing, check capacity
        if ($bed->bed_type != $request->bed_type) {
            $normalCount = $room->beds()->where('bed_type', 'NORMAL')->count();
            $bunkerCount = $room->beds()->where('bed_type', 'BUNKER')->count();

            // Adjust counts since we're changing type
            if ($bed->bed_type == 'NORMAL') {
                $normalCount--;
            } else {
                $bunkerCount--;
            }

            if ($request->bed_type == 'NORMAL' && $normalCount >= ($room->normol_cot_count ?? 0)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room already has maximum normal cots (' . $room->normol_cot_count . ')'
                ], 422);
            }

            if ($request->bed_type == 'BUNKER' && $bunkerCount >= ($room->bunker_cot_count ?? 0)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room already has maximum bunker cots (' . $room->bunker_cot_count . ')'
                ], 422);
            }
        }

        $bed->update($request->all());

        // Update room status if needed
        if ($bed->room_id != $request->room_id) {
            $oldRoom = Room::find($bed->room_id);
            $newRoom = Room::find($request->room_id);
            $this->updateRoomStatus($oldRoom);
            $this->updateRoomStatus($newRoom);
        } else {
            $this->updateRoomStatus($room);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bed updated successfully!',
            'data' => $bed
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $bed = Bed::findOrFail($id);

        // Check if user has access to this bed's hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($bed->room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this bed!'
                ], 403);
            }
        }

        if ($bed->resident) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete bed with active resident!'
            ], 400);
        }

        $room = Room::find($bed->room_id);
        $bed->delete();

        // Update room status
        $this->updateRoomStatus($room);

        return response()->json([
            'success' => true,
            'message' => 'Bed deleted successfully!'
        ]);
    }

    public function toggleStatus($id)
    {
        $user = auth()->user();
        $bed = Bed::findOrFail($id);

        // Check if user has access to this bed's hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($bed->room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this bed!'
                ], 403);
            }
        }

        $statuses = ['VACANT', 'OCCUPIED', 'BLOCKED'];
        $currentIndex = array_search($bed->status, $statuses);
        $bed->status = $statuses[($currentIndex + 1) % count($statuses)];
        $bed->save();

        // Update room status
        $room = Room::find($bed->room_id);
        $this->updateRoomStatus($room);

        return response()->json([
            'success' => true,
            'message' => 'Bed status updated successfully!',
            'data' => $bed
        ]);
    }

    public function getBedsByRoom($roomId)
    {
        $user = auth()->user();
        $room = Room::find($roomId);

        // Check if user has access to this room's hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view these beds!'
                ], 403);
            }
        }

        $beds = Bed::where('room_id', $roomId)->get();
        return response()->json([
            'success' => true,
            'data' => $beds
        ]);
    }

    public function getAvailableBeds($roomId)
    {
        $user = auth()->user();
        $room = Room::find($roomId);

        // Check if user has access to this room's hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view these beds!'
                ], 403);
            }
        }

        $beds = Bed::where('room_id', $roomId)
            ->where('status', 'VACANT')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $beds
        ]);
    }

    public function bulkCreate(Request $request)
    {
        $user = auth()->user();
        $room = Room::find($request->room_id);

        // Check if user has access to this room's hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create beds in this room!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'normal_count' => 'required|integer|min:0',
            'bunker_count' => 'required|integer|min:0',
            'status' => 'required|in:VACANT,OCCUPIED,BLOCKED'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $createdBeds = [];

        // Check capacity limits
        $existingNormal = $room->beds()->where('bed_type', 'NORMAL')->count();
        $existingBunker = $room->beds()->where('bed_type', 'BUNKER')->count();
        $maxNormal = $room->normol_cot_count ?? 0;
        $maxBunker = $room->bunker_cot_count ?? 0;

        if (($existingNormal + $request->normal_count) > $maxNormal) {
            return response()->json([
                'success' => false,
                'message' => "Cannot create {$request->normal_count} normal beds. Maximum is {$maxNormal} and already have {$existingNormal}."
            ], 422);
        }

        if (($existingBunker + $request->bunker_count) > $maxBunker) {
            return response()->json([
                'success' => false,
                'message' => "Cannot create {$request->bunker_count} bunker beds. Maximum is {$maxBunker} and already have {$existingBunker}."
            ], 422);
        }

        // Create normal beds
        for ($i = 1; $i <= $request->normal_count; $i++) {
            $bed = Bed::create([
                'room_id' => $request->room_id,
                'bed_no' => 'N-' . ($existingNormal + $i),
                'bed_type' => 'NORMAL',
                'status' => $request->status
            ]);
            $createdBeds[] = $bed;
        }

        // Create bunker beds
        for ($i = 1; $i <= $request->bunker_count; $i++) {
            $bed = Bed::create([
                'room_id' => $request->room_id,
                'bed_no' => 'B-' . ($existingBunker + $i),
                'bed_type' => 'BUNKER',
                'status' => $request->status
            ]);
            $createdBeds[] = $bed;
        }

        // Update room status
        $this->updateRoomStatus($room);

        return response()->json([
            'success' => true,
            'message' => count($createdBeds) . ' beds created successfully!',
            'data' => $createdBeds
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:beds,id'
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
            $bed = Bed::find($id);

            if (!$bed) continue;

            // Check if user has access
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($bed->room->hostel_id, $hostelIds)) {
                    $errors[] = "No permission to delete bed: {$bed->bed_no}";
                    continue;
                }
            }

            if ($bed->resident) {
                $errors[] = "Cannot delete bed {$bed->bed_no} - has active resident";
                continue;
            }

            $roomsToUpdate[] = $bed->room_id;
            $bed->delete();
            $deleted++;
        }

        // Update room statuses
        foreach (array_unique($roomsToUpdate) as $roomId) {
            $room = Room::find($roomId);
            if ($room) {
                $this->updateRoomStatus($room);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deleted} beds deleted successfully!",
            'errors' => $errors
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:beds,id',
            'status' => 'required|in:VACANT,OCCUPIED,BLOCKED'
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
            $bed = Bed::find($id);

            if (!$bed) continue;

            // Check if user has access
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($bed->room->hostel_id, $hostelIds)) {
                    continue;
                }
            }

            $bed->status = $request->status;
            $bed->save();
            $roomsToUpdate[] = $bed->room_id;
            $updated++;
        }

        // Update room statuses
        foreach (array_unique($roomsToUpdate) as $roomId) {
            $room = Room::find($roomId);
            if ($room) {
                $this->updateRoomStatus($room);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} beds updated successfully!"
        ]);
    }

    public function getStatistics()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $beds = Bed::all();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $beds = Bed::whereHas('room', function($query) use ($hostelIds) {
                $query->whereIn('hostel_id', $hostelIds);
            })->get();
        }

        $stats = [
            'total' => $beds->count(),
            'vacant' => $beds->where('status', 'VACANT')->count(),
            'occupied' => $beds->where('status', 'OCCUPIED')->count(),
            'blocked' => $beds->where('status', 'BLOCKED')->count(),
            'normal' => $beds->where('bed_type', 'NORMAL')->count(),
            'bunker' => $beds->where('bed_type', 'BUNKER')->count(),
            'occupancy_rate' => $beds->count() > 0 ? round(($beds->where('status', 'OCCUPIED')->count() / $beds->count()) * 100, 1) : 0
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function export(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $beds = Bed::with(['room', 'room.hostel', 'room.roomType', 'resident'])->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $beds = Bed::with(['room', 'room.hostel', 'room.roomType', 'resident'])
                ->whereHas('room', function($query) use ($hostelIds) {
                    $query->whereIn('hostel_id', $hostelIds);
                })
                ->get();
        }

        $csv = "ID,Room,Hostel,Bed No,Bed Type,Status,Resident\n";

        foreach ($beds as $bed) {
            $residentName = $bed->resident ? $bed->resident->name : 'Vacant';
            $csv .= "{$bed->id},{$bed->room->room_no},{$bed->room->hostel->hostel_name},{$bed->bed_no},{$bed->bed_type},{$bed->status},{$residentName}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="beds-' . date('Y-m-d') . '.csv"');
    }

    // Helper function to update room status based on bed occupancy
    private function updateRoomStatus($room)
    {
        if (!$room) return;

        $totalBeds = $room->beds()->count();
        $occupiedBeds = $room->beds()->where('status', 'OCCUPIED')->count();
        $maintenanceBeds = $room->beds()->where('status', 'BLOCKED')->count();
        $vacantBeds = $totalBeds - $occupiedBeds - $maintenanceBeds;

        // Update room status
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
}
