<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomTypeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            // Admin can access all hostels
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            // Account/Other users can only access allocated hostels
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        }

        // Get room types based on user's accessible hostels
        if ($user->role === 'admin') {
            $roomTypes = RoomType::with('hostel')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $roomTypes = RoomType::with('hostel')
                ->whereIn('hostel_id', $hostelIds)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get statistics
        $stats = [
            'total' => $roomTypes->count(),
            'active' => $roomTypes->where('is_active', true)->count(),
            'inactive' => $roomTypes->where('is_active', false)->count(),
            'total_rent' => $roomTypes->sum('monthly_rent'),
            'total_deposit' => $roomTypes->sum('deposit_amount')
        ];

        return view('admin.room-types.index', compact('roomTypes', 'hostels', 'stats', 'user'));
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
                    'message' => 'You do not have permission to add room types to this hostel!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id',
            'room_type_name' => 'required|string|max:255',
            'sharing_count' => 'required|integer|min:1|max:10',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $roomType = RoomType::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Room type created successfully!',
            'data' => $roomType
        ]);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $roomType = RoomType::findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($roomType->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this room type!'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $roomType
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $roomType = RoomType::findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($roomType->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this room type!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id',
            'room_type_name' => 'required|string|max:255',
            'sharing_count' => 'required|integer|min:1|max:10',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $roomType->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Room type updated successfully!',
            'data' => $roomType
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $roomType = RoomType::findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($roomType->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this room type!'
                ], 403);
            }
        }

        if ($roomType->rooms()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete room type with existing rooms!'
            ], 400);
        }

        $roomType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room type deleted successfully!'
        ]);
    }

    public function toggleStatus($id)
    {
        $user = auth()->user();
        $roomType = RoomType::findOrFail($id);

        // Check if user has access to this hostel
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($roomType->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this room type!'
                ], 403);
            }
        }

        $roomType->is_active = !$roomType->is_active;
        $roomType->save();

        return response()->json([
            'success' => true,
            'message' => 'Room type status updated successfully!',
            'data' => $roomType
        ]);
    }

    public function getRoomTypesByHostel($hostelId)
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

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:room_types,id'
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
            $roomType = RoomType::find($id);

            // Check if user has access
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($roomType->hostel_id, $hostelIds)) {
                    $errors[] = "No permission to delete room type: {$roomType->room_type_name}";
                    continue;
                }
            }

            if ($roomType->rooms()->count() > 0) {
                $errors[] = "Cannot delete {$roomType->room_type_name} - has existing rooms";
                continue;
            }

            $roomType->delete();
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$deleted} room types deleted successfully!",
            'errors' => $errors
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:room_types,id',
            'status' => 'required|in:activate,deactivate'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updated = 0;
        $status = $request->status === 'activate';

        foreach ($request->ids as $id) {
            $roomType = RoomType::find($id);

            // Check if user has access
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($roomType->hostel_id, $hostelIds)) {
                    continue;
                }
            }

            $roomType->is_active = $status;
            $roomType->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} room types updated successfully!"
        ]);
    }

    public function getStatistics()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $roomTypes = RoomType::all();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $roomTypes = RoomType::whereIn('hostel_id', $hostelIds)->get();
        }

        $stats = [
            'total' => $roomTypes->count(),
            'active' => $roomTypes->where('is_active', true)->count(),
            'inactive' => $roomTypes->where('is_active', false)->count(),
            'total_rent' => $roomTypes->sum('monthly_rent'),
            'avg_rent' => $roomTypes->count() > 0 ? round($roomTypes->avg('monthly_rent'), 2) : 0,
            'max_rent' => $roomTypes->max('monthly_rent') ?? 0,
            'min_rent' => $roomTypes->min('monthly_rent') ?? 0,
            'total_sharing' => $roomTypes->sum('sharing_count'),
            'avg_sharing' => $roomTypes->count() > 0 ? round($roomTypes->avg('sharing_count'), 1) : 0
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
            $roomTypes = RoomType::with('hostel')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $roomTypes = RoomType::with('hostel')
                ->whereIn('hostel_id', $hostelIds)
                ->get();
        }

        $csv = "ID,Hostel,Room Type,Sharing,Monthly Rent,Deposit,Status\n";

        foreach ($roomTypes as $roomType) {
            $csv .= "{$roomType->id},{$roomType->hostel->hostel_name},{$roomType->room_type_name},{$roomType->sharing_count},{$roomType->monthly_rent},{$roomType->deposit_amount}," . ($roomType->is_active ? 'Active' : 'Inactive') . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="room-types-' . date('Y-m-d') . '.csv"');
    }
}
