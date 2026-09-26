<?php
// app/Http/Controllers/GuestComplaintController.php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuestComplaintController extends Controller
{
    public static function encodeId($hostelId)
    {
        return Crypt::encryptString($hostelId);
    }

    public static function decodeId($encodedId)
    {
        try {
            return Crypt::decryptString($encodedId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Complaint portal page (hostel-wise)
     */
    public function index($encodedId = null)
    {
        if (!$encodedId) abort(404, 'Invalid link');

        $hostelId = self::decodeId($encodedId);
        if (!$hostelId) abort(404, 'Invalid or expired link');

        $hostel = Hostel::where('id', $hostelId)
                        ->where('status', 'ACTIVE')
                        ->first();

        if (!$hostel) abort(404, 'Hostel not found or inactive');

        return view('guest.complaint.index', [
            'hostel'    => $hostel,
            'encodedId' => $encodedId,
            'categories' => [
                'electrical' => 'Electrical Issue',
                'plumbing'   => 'Plumbing / Water',
                'furniture'  => 'Furniture Damage',
                'cleaning'   => 'Cleaning / Housekeeping',
                'wifi'       => 'WiFi / Internet',
                'food'       => 'Food / Mess',
                'security'   => 'Security Concern',
                'other'      => 'Other',
            ],
            'priorities' => [
                'low'    => 'Low',
                'medium' => 'Medium',
                'high'   => 'High',
                'urgent' => 'Urgent',
            ],
        ]);
    }

    /**
     * VERIFY RESIDENT BY PHONE
     * Only ACTIVE residents of THIS hostel can verify
     */
    public function verifyResident(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'encoded_id' => 'required',
            'phone'      => 'required|string|min:10|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $hostelId = self::decodeId($request->encoded_id);
        if (!$hostelId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid hostel link'
            ], 400);
        }

        // Clean phone: keep only digits, use LAST 10 digits
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $phone = substr($phone, -10);

        if (strlen($phone) < 10) {
            return response()->json([
                'success'  => false,
                'message'  => '❌ Enter a valid 10-digit mobile number.',
                'verified' => false
            ], 422);
        }

        // STRICT MATCH — resident must exist in this hostel & be ACTIVE
        $resident = Resident::with(['room', 'hostel'])
            ->where('hostel_id', $hostelId)
            ->where('status', 'ACTIVE')
            ->where(function ($q) use ($phone) {
                $q->where('phone', $phone)
                  ->orWhere('phone', 'LIKE', "%{$phone}")
                  ->orWhere('phone', 'LIKE', "{$phone}%");
            })
            ->first();

        if (!$resident) {
            return response()->json([
                'success'  => false,
                'message'  => '❌ This phone number is not registered as an active resident in this hostel. Only residents can register complaints.',
                'verified' => false
            ], 404);
        }

        return response()->json([
            'success'  => true,
            'verified' => true,
            'message'  => '✓ Resident verified successfully',
            'data' => [
                'resident_id' => $resident->id,
                'name'        => $resident->name,
                'phone'       => $resident->phone,
                'email'       => $resident->email,
                'room_number' => $resident->room->room_number ?? $resident->room->room_no ?? null,
                'room_id'     => $resident->room_id,
                'hostel_id'   => $resident->hostel_id,
                'hostel_name' => $resident->hostel->hostel_name ?? $resident->hostel->name ?? null,
                'photo'       => $resident->profile_image
                                    ? asset('storage/' . $resident->profile_image)
                                    : null,
            ]
        ]);
    }

    /**
     * STORE COMPLAINT
     * 🔥 Verifies resident by PHONE ONLY (not by resident_id)
     * Server-side strict verification — cannot be bypassed
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'encoded_id'  => 'required',
            'phone'       => 'required|string|min:10|max:15',
            'category'    => 'required|in:electrical,plumbing,furniture,cleaning,wifi,food,security,other',
            'priority'    => 'required|in:low,medium,high,urgent',
            'description' => 'required|string|min:10|max:2000',
            'image'       => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // Decode hostel
        $hostelId = self::decodeId($request->encoded_id);
        if (!$hostelId) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $hostel = Hostel::where('id', $hostelId)->where('status', 'ACTIVE')->first();
        if (!$hostel) {
            return response()->json(['success' => false, 'message' => 'Hostel not found'], 404);
        }

        // ================================================
        // 🔥 RESIDENT VERIFICATION BY PHONE (single source of truth)
        // ================================================
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $phone = substr($phone, -10);

        if (strlen($phone) < 10) {
            return response()->json([
                'success' => false,
                'message' => '❌ Invalid phone number.',
                'code'    => 'INVALID_PHONE'
            ], 422);
        }

        // Match resident by phone in THIS hostel, ACTIVE only
        $resident = Resident::with('room')
            ->where('hostel_id', $hostelId)
            ->where('status', 'ACTIVE')
            ->where(function ($q) use ($phone) {
                $q->where('phone', $phone)
                  ->orWhere('phone', 'LIKE', "%{$phone}")
                  ->orWhere('phone', 'LIKE', "{$phone}%");
            })
            ->first();

        // If resident NOT found → BLOCK complaint
        if (!$resident) {
            Log::warning('Complaint blocked — resident not found', [
                'hostel_id' => $hostelId,
                'phone'     => $phone,
                'ip'        => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Complaint registration failed. Only registered active residents of this hostel can submit complaints.',
                'code'    => 'RESIDENT_NOT_FOUND'
            ], 403);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaints', 'public');
        }

        // Create complaint — data from VERIFIED resident
        $complaint = Complaint::create([
            'hostel_id'   => $hostelId,
            'resident_id' => $resident->id,
            'name'        => $resident->name,
            'phone'       => $resident->phone,
            'email'       => $resident->email,
            'room_number' => $resident->room->room_number ?? $resident->room->room_no ?? null,
            'category'    => $request->category,
            'priority'    => $request->priority,
            'description' => $request->description,
            'image'       => $imagePath,
            'status'      => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => '✓ Complaint registered successfully!',
            'data' => [
                'complaint_number' => $complaint->complaint_number,
                'id'               => $complaint->id,
                'resident_id'      => $complaint->resident_id,
                'status'           => $complaint->status,
            ]
        ]);
    }

    /**
     * TRACK complaint by number
     */
    public function track(Request $request)
    {
        $request->validate([
            'encoded_id'       => 'required',
            'complaint_number' => 'required',
        ]);

        $hostelId = self::decodeId($request->encoded_id);
        if (!$hostelId) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $complaint = Complaint::with('resident')
            ->where('hostel_id', $hostelId)
            ->where('complaint_number', $request->complaint_number)
            ->first();

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'complaint_number' => $complaint->complaint_number,
                'name'             => $complaint->name,
                'room_number'      => $complaint->room_number,
                'category'         => $complaint->category,
                'priority'         => $complaint->priority,
                'description'      => $complaint->description,
                'status'           => $complaint->status,
                'admin_remark'     => $complaint->admin_remark,
                'created_at'       => $complaint->created_at->format('d M Y, h:i A'),
                'resolved_at'      => $complaint->resolved_at?->format('d M Y, h:i A'),
                'image'            => $complaint->image ? asset('storage/' . $complaint->image) : null,
                'resident_photo'   => $complaint->resident?->profile_image
                                        ? asset('storage/' . $complaint->resident->profile_image)
                                        : null,
            ]
        ]);
    }

    /**
     * MY COMPLAINTS by phone
     */
    public function myComplaints(Request $request)
    {
        $request->validate([
            'encoded_id' => 'required',
            'phone'      => 'required',
        ]);

        $hostelId = self::decodeId($request->encoded_id);
        if (!$hostelId) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $phone = substr($phone, -10);

        $complaints = Complaint::where('hostel_id', $hostelId)
            ->where('phone', 'LIKE', "%{$phone}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($c) {
                return [
                    'complaint_number' => $c->complaint_number,
                    'category'         => $c->category,
                    'priority'         => $c->priority,
                    'status'           => $c->status,
                    'description'      => substr($c->description, 0, 100),
                    'created_at'       => $c->created_at->format('d M Y'),
                    'admin_remark'     => $c->admin_remark,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $complaints
        ]);
    }
}
