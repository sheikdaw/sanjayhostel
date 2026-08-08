<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
            $rooms = Room::with(['hostel', 'roomType', 'beds'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
            $rooms = Room::with(['hostel', 'roomType', 'beds'])
                ->whereIn('hostel_id', $hostelIds)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $roomTypes = RoomType::where('is_active', true)->get();

        // Calculate statistics
        $stats = [
            'total' => $rooms->count(),
            'vacant' => $rooms->where('status', 'VACANT')->count(),
            'partial' => $rooms->where('status', 'PARTIAL')->count(),
            'full' => $rooms->where('status', 'FULL')->count(),
            'maintenance' => $rooms->where('status', 'MAINTENANCE')->count(),
            'total_cots' => $rooms->sum('total_cots'),
            'occupied_cots' => $rooms->sum('occupied_cots'),
            'vacant_cots' => $rooms->sum('vacant_cots'),
            'occupancy_rate' => $rooms->sum('total_cots') > 0 ? round(($rooms->sum('occupied_cots') / $rooms->sum('total_cots')) * 100, 1) : 0
        ];

        return view('admin.rooms.index', compact('rooms', 'hostels', 'roomTypes', 'stats', 'user'));
    }

    public function getRoomTypes($hostelId)
    {
        $user = auth()->user();

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($hostelId, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this hostel\'s room types!'
                ], 403);
            }
        }

        $roomTypes = RoomType::where('hostel_id', $hostelId)
            ->where('is_active', true)
            ->get();
        return response()->json([
            'success' => true,
            'data' => $roomTypes
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($request->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add rooms to this hostel!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_no' => 'required|string|max:50|unique:rooms,room_no,null,id,hostel_id,' . $request->hostel_id,
            'normol_cot_count' => 'required|integer|min:0|max:20',
            'bunker_cot_count' => 'required|integer|min:0|max:20',
            'status' => 'required|in:VACANT,PARTIAL,FULL,MAINTENANCE'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if at least one cot is added
        if ($request->normol_cot_count == 0 && $request->bunker_cot_count == 0) {
            return response()->json([
                'success' => false,
                'message' => 'At least one cot (normal or bunker) must be added!'
            ], 422);
        }

        $room = Room::create($request->all());

        // Create normal cots
        for ($i = 1; $i <= $request->normol_cot_count; $i++) {
            $room->beds()->create([
                'bed_no' => 'N-' . $i,
                'bed_type' => 'NORMAL',
                'status' => 'VACANT'
            ]);
        }

        // Create bunker cots
        for ($i = 1; $i <= $request->bunker_cot_count; $i++) {
            $room->beds()->create([
                'bed_no' => 'B-' . $i,
                'bed_type' => 'BUNKER',
                'status' => 'VACANT'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room created successfully with ' . $request->normol_cot_count . ' normal and ' . $request->bunker_cot_count . ' bunker cots!',
            'data' => $room
        ]);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $room = Room::with(['hostel', 'roomType', 'beds'])->findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this room!'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $room
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $room = Room::findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this room!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_no' => 'required|string|max:50|unique:rooms,room_no,' . $id . ',id,hostel_id,' . $request->hostel_id,
            'normol_cot_count' => 'required|integer|min:0|max:20',
            'bunker_cot_count' => 'required|integer|min:0|max:20',
            'status' => 'required|in:VACANT,PARTIAL,FULL,MAINTENANCE'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if at least one cot is added
        if ($request->normol_cot_count == 0 && $request->bunker_cot_count == 0) {
            return response()->json([
                'success' => false,
                'message' => 'At least one cot (normal or bunker) must be added!'
            ], 422);
        }

        // Handle cot count changes
        $currentNormalCount = $room->beds()->where('bed_type', 'NORMAL')->count();
        $currentBunkerCount = $room->beds()->where('bed_type', 'BUNKER')->count();

        // Update normal cots
        if ($request->normol_cot_count > $currentNormalCount) {
            for ($i = $currentNormalCount + 1; $i <= $request->normol_cot_count; $i++) {
                $room->beds()->create([
                    'bed_no' => 'N-' . $i,
                    'bed_type' => 'NORMAL',
                    'status' => 'VACANT'
                ]);
            }
        } elseif ($request->normol_cot_count < $currentNormalCount) {
            $extraNormalCots = $room->beds()
                ->where('bed_type', 'NORMAL')
                ->where('status', 'VACANT')
                ->take($currentNormalCount - $request->normol_cot_count)
                ->get();

            foreach ($extraNormalCots as $cot) {
                $cot->delete();
            }
        }

        // Update bunker cots
        if ($request->bunker_cot_count > $currentBunkerCount) {
            for ($i = $currentBunkerCount + 1; $i <= $request->bunker_cot_count; $i++) {
                $room->beds()->create([
                    'bed_no' => 'B-' . $i,
                    'bed_type' => 'BUNKER',
                    'status' => 'VACANT'
                ]);
            }
        } elseif ($request->bunker_cot_count < $currentBunkerCount) {
            $extraBunkerCots = $room->beds()
                ->where('bed_type', 'BUNKER')
                ->where('status', 'VACANT')
                ->take($currentBunkerCount - $request->bunker_cot_count)
                ->get();

            foreach ($extraBunkerCots as $cot) {
                $cot->delete();
            }
        }

        $room->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully!',
            'data' => $room
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $room = Room::findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this room!'
                ], 403);
            }
        }

        if ($room->residents()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete room with active residents!'
            ], 400);
        }

        $room->beds()->delete();
        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully!'
        ]);
    }

    public function toggleStatus($id)
    {
        $user = auth()->user();
        $room = Room::findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($room->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this room!'
                ], 403);
            }
        }

        $statuses = ['VACANT', 'PARTIAL', 'FULL', 'MAINTENANCE'];
        $currentIndex = array_search($room->status, $statuses);
        $room->status = $statuses[($currentIndex + 1) % count($statuses)];
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully!',
            'data' => $room
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:rooms,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deleted = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            $room = Room::find($id);

            if (!$room) continue;

            // Check if user has access
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($room->hostel_id, $hostelIds)) {
                    $errors[] = "No permission to delete room: {$room->room_no}";
                    continue;
                }
            }

            if ($room->residents()->count() > 0) {
                $errors[] = "Cannot delete room {$room->room_no} - has active residents";
                continue;
            }

            $room->beds()->delete();
            $room->delete();
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$deleted} rooms deleted successfully!",
            'errors' => $errors
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:rooms,id',
            'status' => 'required|in:VACANT,PARTIAL,FULL,MAINTENANCE'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updated = 0;

        foreach ($request->ids as $id) {
            $room = Room::find($id);

            if (!$room) continue;

            // Check if user has access
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($room->hostel_id, $hostelIds)) {
                    continue;
                }
            }

            $room->status = $request->status;
            $room->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} rooms updated successfully!"
        ]);
    }

    public function getRoomsByHostel($hostelId)
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
            ->with(['roomType', 'beds'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    public function getStatistics()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $rooms = Room::all();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $rooms = Room::whereIn('hostel_id', $hostelIds)->get();
        }

        $stats = [
            'total' => $rooms->count(),
            'vacant' => $rooms->where('status', 'VACANT')->count(),
            'partial' => $rooms->where('status', 'PARTIAL')->count(),
            'full' => $rooms->where('status', 'FULL')->count(),
            'maintenance' => $rooms->where('status', 'MAINTENANCE')->count(),
            'total_cots' => $rooms->sum('total_cots'),
            'occupied_cots' => $rooms->sum('occupied_cots'),
            'vacant_cots' => $rooms->sum('vacant_cots'),
            'occupancy_rate' => $rooms->sum('total_cots') > 0 ? round(($rooms->sum('occupied_cots') / $rooms->sum('total_cots')) * 100, 1) : 0
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
            $rooms = Room::with(['hostel', 'roomType'])->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $rooms = Room::with(['hostel', 'roomType'])
                ->whereIn('hostel_id', $hostelIds)
                ->get();
        }

        $csv = "ID,Hostel,Room No,Room Type,Normal Cots,Bunker Cots,Total Cots,Occupied Cots,Vacant Cots,Occupancy %,Status\n";

        foreach ($rooms as $room) {
            $csv .= "{$room->id},{$room->hostel->hostel_name},{$room->room_no},{$room->roomType->room_type_name},{$room->normol_cot_count},{$room->bunker_cot_count},{$room->total_cots},{$room->occupied_cots},{$room->vacant_cots},{$room->occupancy_percentage}%,{$room->status}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="rooms-' . date('Y-m-d') . '.csv"');
    }
}
