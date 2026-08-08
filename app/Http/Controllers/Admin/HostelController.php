<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HostelController extends Controller
{
    public function index()
    {
        $hostels = Hostel::withCount(['residents', 'rooms'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($hostels as $hostel) {
            $hostel->beds_count = $hostel->rooms->sum(function($room) {
                return $room->beds()->count();
            });
        }

        return view('admin.hostels.index', compact('hostels'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hostel_code' => 'required|string|max:50|unique:hostels,hostel_code',
            'hostel_name' => 'required|string|max:255',
            'hostel_type' => 'required|in:MEN,WOMEN',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $hostel = Hostel::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Hostel created successfully!',
            'data' => $hostel
        ]);
    }

    public function edit($id)
    {
        $hostel = Hostel::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $hostel
        ]);
    }

    public function update(Request $request, $id)
    {
        $hostel = Hostel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'hostel_code' => 'required|string|max:50|unique:hostels,hostel_code,' . $id,
            'hostel_name' => 'required|string|max:255',
            'hostel_type' => 'required|in:MEN,WOMEN',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $hostel->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Hostel updated successfully!',
            'data' => $hostel
        ]);
    }

    public function destroy($id)
    {
        $hostel = Hostel::findOrFail($id);

        $residentCount = $hostel->residents()->count();
        if ($residentCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete hostel with ' . $residentCount . ' active residents!'
            ], 400);
        }

        $hostel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hostel deleted successfully!'
        ]);
    }

    public function toggleStatus($id)
    {
        $hostel = Hostel::findOrFail($id);
        $hostel->status = $hostel->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $hostel->save();

        return response()->json([
            'success' => true,
            'message' => 'Hostel status updated successfully!',
            'data' => $hostel
        ]);
    }
}
