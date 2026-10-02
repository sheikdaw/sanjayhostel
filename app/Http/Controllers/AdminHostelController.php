<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminHostelController extends Controller
{
    /**
     * Display hostel management page.
     * admin   → all hostels
     * account → only hostels in user.hostel_ids
     */
    public function index()
    {
        $user = auth()->user();

        $query = Hostel::withCount(['rooms', 'residents'])
            ->orderBy('created_at', 'desc');

        // Non-admin users only see their assigned hostels
        if (! $user->isAdmin()) {
            $query->whereIn('id', $user->hostel_ids ?? []);
        }

        $hostels = $query->get();

        return view('admin.hostels.index', compact('hostels'));
    }

    /**
     * Store new hostel.
     * Auto-assigns to current user if not admin.
     */
    public function store(Request $request)
    {
        // Normalize before validation
        $request->merge([
            'hostel_type' => $this->normalizeType($request->input('hostel_type')),
            'status'      => strtolower(trim((string) $request->input('status', 'active'))),
            'hostel_code' => trim((string) $request->input('hostel_code')),
            'hostel_name' => trim((string) $request->input('hostel_name')),
        ]);

        $validator = Validator::make($request->all(), [
            'hostel_code' => 'required|string|max:50|unique:hostels,hostel_code',
            'hostel_name' => 'required|string|max:255',
            'hostel_type' => 'required|in:male,female,co-ed',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'status'      => 'required|in:active,inactive',

            'biometric_device_id'     => 'nullable|string|max:100',
            'biometric_device_name'   => 'nullable|string|max:255',
            'biometric_ip_address'    => 'nullable|string|max:50',
            'biometric_port'          => 'nullable|integer|min:1|max:65535',
            'biometric_location_code' => 'nullable|string|max:50',
            'employee_code_prefix'    => 'nullable|string|max:20',

            'upi_id'         => 'nullable|string|max:100',
            'upi_payee_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $hostel = Hostel::create($validator->validated());

            // Auto-assign to non-admin users
            $user = auth()->user();
            if (! $user->isAdmin()) {
                $ids = $user->hostel_ids ?? [];
                $ids[] = $hostel->id;
                $user->hostel_ids = array_values(array_unique($ids));
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Hostel created successfully!',
                'hostel'  => $hostel->loadCount(['rooms', 'residents']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single hostel (with access check).
     */
    public function show($id)
    {
        try {
            $hostel = Hostel::withCount(['rooms', 'residents'])->findOrFail($id);

            if (! auth()->user()->hasAccessToHostel($hostel->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this hostel.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'hostel'  => $hostel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found',
            ], 404);
        }
    }

    /**
     * Update hostel (with access check).
     */
    public function update(Request $request, $id)
    {
        $hostel = Hostel::findOrFail($id);

        if (! auth()->user()->hasAccessToHostel($hostel->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this hostel.',
            ], 403);
        }

        // Normalize before validation
        $request->merge([
            'hostel_type' => $this->normalizeType($request->input('hostel_type')),
            'status'      => strtolower(trim((string) $request->input('status', $hostel->status))),
            'hostel_code' => trim((string) $request->input('hostel_code')),
            'hostel_name' => trim((string) $request->input('hostel_name')),
        ]);

        $validator = Validator::make($request->all(), [
            'hostel_code' => [
                'required', 'string', 'max:50',
                Rule::unique('hostels', 'hostel_code')->ignore($id),
            ],
            'hostel_name' => 'required|string|max:255',
            'hostel_type' => 'required|in:male,female,co-ed',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'status'      => 'required|in:active,inactive',

            'biometric_device_id'     => 'nullable|string|max:100',
            'biometric_device_name'   => 'nullable|string|max:255',
            'biometric_ip_address'    => 'nullable|string|max:50',
            'biometric_port'          => 'nullable|integer|min:1|max:65535',
            'biometric_location_code' => 'nullable|string|max:50',
            'employee_code_prefix'    => 'nullable|string|max:20',

            'upi_id'         => 'nullable|string|max:100',
            'upi_payee_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $hostel->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Hostel updated successfully!',
                'hostel'  => $hostel->fresh()->loadCount(['rooms', 'residents']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete hostel (with access check + auto-unassign from users).
     */
    public function destroy($id)
    {
        try {
            $hostel = Hostel::findOrFail($id);

            if (! auth()->user()->hasAccessToHostel($hostel->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this hostel.',
                ], 403);
            }

            if ($hostel->rooms()->count() > 0 || $hostel->residents()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete hostel with existing rooms or residents.',
                ], 422);
            }

            $hostelId = $hostel->id;
            $hostel->delete();

            // Remove this hostel from all users' hostel_ids
            User::whereJsonContains('hostel_ids', (int) $hostelId)
                ->each(function ($u) use ($hostelId) {
                    $ids = $u->hostel_ids ?? [];
                    $ids = array_values(array_filter($ids, fn ($x) => (int) $x !== (int) $hostelId));
                    $u->hostel_ids = $ids;
                    $u->save();
                });

            return response()->json([
                'success' => true,
                'message' => 'Hostel deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle hostel status (with access check).
     */
    public function toggleStatus($id)
    {
        try {
            $hostel = Hostel::findOrFail($id);

            if (! auth()->user()->hasAccessToHostel($hostel->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this hostel.',
                ], 403);
            }

            $hostel->status = $hostel->status === 'active' ? 'inactive' : 'active';
            $hostel->save();

            return response()->json([
                'success' => true,
                'message' => 'Hostel status updated!',
                'status'  => $hostel->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
            ], 500);
        }
    }

    /* =========================================================
     |  HELPERS
     ========================================================= */

    /**
     * Force hostel_type into one of the safe, valid values.
     */
    protected function normalizeType($value): string
    {
        $value = strtolower(trim((string) $value));

        return match (true) {
            str_contains($value, 'female'),
            str_contains($value, 'women'),
            str_contains($value, 'girl'),
            str_contains($value, 'ladies')  => 'female',

            str_contains($value, 'co-ed'),
            str_contains($value, 'coed'),
            str_contains($value, 'mixed'),
            str_contains($value, 'unisex')  => 'co-ed',

            default                          => 'male',
        };
    }
}
