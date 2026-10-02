<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminHostelController extends Controller
{
    /* =========================================================
     |  INDEX
     ========================================================= */
    public function index()
    {
        $hostels = Hostel::withCount(['rooms', 'residents'])
            ->orderBy('hostel_name')
            ->get();

        return view('admin.hostels.index', compact('hostels'));
    }

    /* =========================================================
     |  STORE
     ========================================================= */
    public function store(Request $request)
    {
        // 🔧 Normalize BEFORE validation
        $request->merge([
            'hostel_type' => $this->normalizeType($request->input('hostel_type')),
            'status'      => strtolower(trim((string) $request->input('status', 'active'))),
            'hostel_code' => trim((string) $request->input('hostel_code')),
            'hostel_name' => trim((string) $request->input('hostel_name')),
        ]);

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

    /* =========================================================
     |  SHOW
     ========================================================= */
    public function show($id)
    {
        $hostel = Hostel::withCount(['rooms', 'residents'])->find($id);

        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'hostel'  => $hostel,
        ]);
    }

    /* =========================================================
     |  UPDATE
     ========================================================= */
    public function update(Request $request, $id)
    {
        $hostel = Hostel::find($id);

        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found.',
            ], 404);
        }

        // 🔧 Normalize BEFORE validation
        $request->merge([
            'hostel_type' => $this->normalizeType($request->input('hostel_type')),
            'status'      => strtolower(trim((string) $request->input('status', $hostel->status))),
            'hostel_code' => trim((string) $request->input('hostel_code')),
            'hostel_name' => trim((string) $request->input('hostel_name')),
        ]);

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

    /* =========================================================
     |  DESTROY
     ========================================================= */
    public function destroy($id)
    {
        $hostel = Hostel::find($id);

        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found.',
            ], 404);
        }

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

    /* =========================================================
     |  TOGGLE STATUS
     ========================================================= */
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

    /**
     * Force hostel_type into one of the safe, valid values.
     * Accepts: male / female / co-ed (case-insensitive, trimmed).
     * Falls back to 'male' if nothing matches.
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
}
