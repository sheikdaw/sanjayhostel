<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     * ADMIN - sees ALL users
     * ACCOUNT - sees only ACCOUNT and STAY users (not admin)
     * STAY - sees only STAY users
     */
    public function index()
    {
        $authUser = auth()->user();
        
        // Role-based access control
        if ($authUser->role === 'admin') {
            // Admin sees ALL users
            $users = User::orderBy('created_at', 'desc')->get();
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } elseif ($authUser->role === 'account') {
            // Account sees: account users + stay users (NOT admin)
            $users = User::whereIn('role', ['account', 'stay'])
                ->orderBy('role')
                ->orderBy('name')
                ->get();
            
            // Account sees only their assigned hostels
            $hostelIds = $authUser->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        } elseif ($authUser->role === 'stay') {
            // Stay sees only stay users
            $users = User::where('role', 'stay')
                ->orderBy('name')
                ->get();
            
            // Stay sees only their assigned hostels
            $hostelIds = $authUser->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        } else {
            // Default: only show users with same role
            $users = User::where('role', $authUser->role)
                ->orderBy('name')
                ->get();
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        }

        $roles = RoleEnum::getRoleLabels();
        $roleBadges = RoleEnum::getRoleBadges();
        $roleIcons = RoleEnum::getRoleIcons();

        $stats = [
            'total' => $users->count(),
            'active' => $users->where('is_active', true)->count(),
            'inactive' => $users->where('is_active', false)->count(),
            'admin' => $users->where('role', 'admin')->count(),
            'account' => $users->where('role', 'account')->count(),
            'stay' => $users->where('role', 'stay')->count()
        ];

        return view('admin.users.index', compact('users', 'hostels', 'roles', 'roleBadges', 'roleIcons', 'stats', 'authUser'));
    }

    /**
     * Store a newly created user.
     * ADMIN - can create any role
     * ACCOUNT - can create only ACCOUNT and STAY users
     * STAY - can create only STAY users
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();

        // Determine which roles this user can create
        if ($authUser->role === 'admin') {
            $allowedRoles = RoleEnum::getAllRoles();
        } elseif ($authUser->role === 'account') {
            $allowedRoles = ['account', 'stay'];
        } elseif ($authUser->role === 'stay') {
            $allowedRoles = ['stay'];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create users!'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'hostel_ids' => 'nullable|array',
            'hostel_ids.*' => 'exists:hostels,id',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check hostel access for non-admin users
        if ($authUser->role !== 'admin') {
            $allowedHostelIds = $authUser->hostel_ids ?? [];
            if (!empty($request->hostel_ids)) {
                foreach ($request->hostel_ids as $hostelId) {
                    if (!in_array($hostelId, $allowedHostelIds)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You cannot assign hostels you do not have access to!'
                        ], 403);
                    }
                }
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'hostel_ids' => $request->hostel_ids ?? [],
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!',
            'data' => $user
        ]);
    }

    /**
     * Show the form for editing the specified user.
     * User can only edit users they have access to
     */
    public function edit($id)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);

        // Check if user has permission to view this user
        if (!$this->canAccessUser($authUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this user!'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);

        // Check if user has permission to update this user
        if (!$this->canAccessUser($authUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this user!'
            ], 403);
        }

        // Determine which roles this user can assign
        if ($authUser->role === 'admin') {
            $allowedRoles = RoleEnum::getAllRoles();
        } elseif ($authUser->role === 'account') {
            $allowedRoles = ['account', 'stay'];
        } elseif ($authUser->role === 'stay') {
            $allowedRoles = ['stay'];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update users!'
            ], 403);
        }

        // Prevent changing admin role if not admin
        if ($user->role === 'admin' && $authUser->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You cannot modify admin users!'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'hostel_ids' => 'nullable|array',
            'hostel_ids.*' => 'exists:hostels,id',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check hostel access for non-admin users
        if ($authUser->role !== 'admin') {
            $allowedHostelIds = $authUser->hostel_ids ?? [];
            if (!empty($request->hostel_ids)) {
                foreach ($request->hostel_ids as $hostelId) {
                    if (!in_array($hostelId, $allowedHostelIds)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You cannot assign hostels you do not have access to!'
                        ], 403);
                    }
                }
            }
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'hostel_ids' => $request->hostel_ids ?? [],
            'is_active' => $request->is_active ?? true
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified user.
     * Cannot delete admin users
     * Non-admin users cannot delete users with higher/equal role
     */
    public function destroy($id)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);

        // Check if user has permission to delete this user
        if (!$this->canAccessUser($authUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this user!'
            ], 403);
        }

        // Prevent deleting admin users
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete admin user!'
            ], 400);
        }

        // Prevent deleting self
        if ($user->id === $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account!'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }

    /**
     * Toggle user status (active/inactive).
     */
    public function toggleStatus($id)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);

        // Check if user has permission to toggle this user
        if (!$this->canAccessUser($authUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to modify this user!'
            ], 403);
        }

        // Prevent disabling admin users
        if ($user->role === 'admin' && $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot disable admin user!'
            ], 400);
        }

        // Prevent disabling self
        if ($user->id === $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot disable your own account!'
            ], 400);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully!',
            'data' => $user
        ]);
    }

    /**
     * Get users by hostel.
     */
    public function getUsersByHostel($hostelId)
    {
        $authUser = auth()->user();
        
        // Check if user has access to this hostel
        if ($authUser->role !== 'admin') {
            $allowedHostelIds = $authUser->hostel_ids ?? [];
            if (!in_array($hostelId, $allowedHostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this hostel!'
                ], 403);
            }
        }

        $users = User::whereJsonContains('hostel_ids', $hostelId)
            ->orWhere('role', 'admin')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole($role)
    {
        $authUser = auth()->user();
        
        // Check if user has permission to view this role
        if ($authUser->role === 'account' && $role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view admin users!'
            ], 403);
        }

        if ($authUser->role === 'stay' && in_array($role, ['admin', 'account'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view admin or account users!'
            ], 403);
        }

        $users = User::where('role', $role)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'data' => $user
        ]);
    }

    /**
     * Get assigned hostels for the current user.
     */
    public function getAssignedHostels()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostels = Hostel::whereIn('id', $user->hostel_ids ?? [])
                ->where('status', 'ACTIVE')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $hostels
        ]);
    }

    /**
     * Get hostel options for user assignment.
     * Shows only hostels the current user has access to
     */
    public function getAssignableHostels()
    {
        $authUser = auth()->user();

        if ($authUser->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $authUser->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $hostels
        ]);
    }

    /**
     * Helper method to check if a user can access another user.
     */
    private function canAccessUser($authUser, $targetUser)
    {
        // Admin can access everyone
        if ($authUser->role === 'admin') {
            return true;
        }

        // Account can access account and stay users
        if ($authUser->role === 'account') {
            return in_array($targetUser->role, ['account', 'stay']);
        }

        // Stay can access only stay users
        if ($authUser->role === 'stay') {
            return $targetUser->role === 'stay';
        }

        // Default: can only access same role
        return $authUser->role === $targetUser->role;
    }

    /**
     * Helper method to check if user has access to a hostel.
     */
    private function hasHostelAccess($authUser, $hostelId)
    {
        if ($authUser->role === 'admin') {
            return true;
        }

        $allowedHostelIds = $authUser->hostel_ids ?? [];
        return in_array($hostelId, $allowedHostelIds);
    }
}