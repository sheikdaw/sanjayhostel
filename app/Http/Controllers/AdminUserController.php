<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users (focus on account role).
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Default: show only account users (since this is allocation page)
        // But allow filtering by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        } else {
            $query->where('role', 'account');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all active hostels for allocation
        $hostels = Hostel::where('status', 'active')
            ->orderBy('hostel_name')
            ->get();

        // Get a map of hostel id => name for quick lookup
        $hostelMap = $hostels->pluck('hostel_name', 'id')->toArray();

        return view('admin.users.index', compact('users', 'hostels', 'hostelMap'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'phone'        => 'required|string|max:20',
            'role'         => 'required|in:admin,account,stay',
            'hostel_ids'   => 'nullable|array',
            'hostel_ids.*' => 'exists:hostels,id',
            'is_active'    => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'phone'      => $request->phone,
                'role'       => $request->role,
                'hostel_ids' => $request->hostel_ids ?? [],
                'is_active'  => $request->boolean('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user'    => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single user (for edit modal).
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $id,
            'password'     => 'nullable|string|min:6',
            'phone'        => 'required|string|max:20',
            'role'         => 'required|in:admin,account,stay',
            'hostel_ids'   => 'nullable|array',
            'hostel_ids.*' => 'exists:hostels,id',
            'is_active'    => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $data = [
                'name'       => $request->name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'role'       => $request->role,
                'hostel_ids' => $request->hostel_ids ?? [],
                'is_active'  => $request->boolean('is_active', true),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user'    => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change your own status.',
                ], 403);
            }

            $user->is_active = !$user->is_active;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => $user->is_active ? 'User activated.' : 'User deactivated.',
                'status'  => $user->is_active ? 'active' : 'inactive',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get hostels assigned to a specific user.
     */
    public function getUserHostels($id)
    {
        $user = User::findOrFail($id);
        $assignedHostels = $user->getAssignedHostels();

        return response()->json([
            'success' => true,
            'hostels' => $assignedHostels,
        ]);
    }
}
