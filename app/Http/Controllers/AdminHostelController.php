<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminHostelController extends Controller
{
    /**
     * Admin: list hostels in the office grid.
     */
    public function index()
    {
        $hostels = Hostel::withCount(['rooms', 'residents'])
            ->orderBy('hostel_name')
            ->get()
            ->map(function ($hostel) {
                $hostel->type_icon  = $this->typeIcon($hostel->hostel_type);
                $hostel->type_label = $this->typeLabel($hostel->hostel_type);
                return $hostel;
            });

        return view('admin.hostels.index', compact('hostels'));
    }

    /**
     * Admin: create a new hostel.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $hostel = Hostel::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Hostel created successfully.',
                'hostel'  => $hostel,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: return a hostel as JSON (used by edit modal).
     */
    public function show($id)
    {
        $hostel = Hostel::withCount(['rooms', 'residents'])->find($id);

        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found.',
            ], 404);
        }

        $hostel->type_icon  = $this->typeIcon($hostel->hostel_type);
        $hostel->type_label = $this->typeLabel($hostel->hostel_type);

        return response()->json([
            'success' => true,
            'hostel'  => $hostel,
        ]);
    }

    /**
     * Admin: update a hostel.
     */
    public function update(Request $request, $id)
    {
        $hostel = Hostel::find($id);

        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), $this->rules($id));

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $hostel->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Hostel updated successfully.',
                'hostel'  => $hostel->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: delete a hostel.
     */
    public function destroy($id)
    {
        $hostel = Hostel::find($id);

        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found.',
            ], 404);
        }

        // Block deletion when residents still exist.
        if ($hostel->residents()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete hostel with active residents. Vacate them first.',
            ], 422);
        }

        try {
            $hostel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hostel deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: toggle active/inactive status.
     */
    public function toggleStatus($id)
    {
        $hostel = Hostel::find($id);

        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found.',
            ], 404);
        }

        try {
            $hostel->status = $hostel->status === 'active' ? 'inactive' : 'active';
            $hostel->save();

            return response()->json([
                'success' => true,
                'message' => "Hostel marked as {$hostel->status}.",
                'status'  => $hostel->status,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  HELPERS
     ========================================================= */

    protected function rules(?int $ignoreId = null): array
    {
        $uniqueCode = 'unique:hostels,hostel_code';
        if ($ignoreId) {
            $uniqueCode .= ',' . $ignoreId;
        }

        return [
            'hostel_code'             => "required|string|max:50|{$uniqueCode}",
            'hostel_name'             => 'required|string|max:150',
            'hostel_type'             => 'required|in:male,female,co-ed',
            'status'                  => 'required|in:active,inactive',
            'address'                 => 'nullable|string|max:500',
            'phone'                   => 'nullable|string|max:20',
            'email'                   => 'nullable|email|max:150',
            'biometric_device_id'     => 'nullable|string|max:100',
            'biometric_device_name'   => 'nullable|string|max:150',
            'biometric_ip_address'    => 'nullable|string|max:45',
            'biometric_port'          => 'nullable|integer|min:1|max:65535',
            'biometric_location_code' => 'nullable|string|max:100',
            'employee_code_prefix'    => 'nullable|string|max:50',
            'upi_id'                  => 'nullable|string|max:150',
            'upi_payee_name'          => 'nullable|string|max:150',
        ];
    }

    protected function typeIcon(?string $type): string
    {
        return match ($type) {
            'male'   => 'gender-male',
            'female' => 'gender-female',
            'co-ed'  => 'people',
            default  => 'building',
        };
    }

    protected function typeLabel(?string $type): string
    {
        return match ($type) {
            'male'   => 'Men',
            'female' => 'Women',
            'co-ed'  => 'Co-ed',
            default  => 'Unknown',
        };
    }
}
