<?php
// app/Http/Controllers/GuestComplaintController.php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
     * Phone number hostel-la iruntha resident-a check pannum
     * Resident illa na → error return
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

        // Clean phone (remove spaces, +, -)
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $phone = substr($phone, -10); // last 10 digits

        // STRICT CHECK — resident MUST exist in this hostel
        $resident = Resident::with(['room', 'hostel'])
            ->where('hostel_id', $hostelId)
            ->where(function ($q) use ($phone) {
                $q->where('phone', 'LIKE', "%{$phone}%")
                  ->orWhere('phone', $phone);
            })
            ->where('status', 'ACTIVE')
            ->first();

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => '❌ This phone number is not registered as an active resident in this hostel. Only residents can register complaints.',
                'verified' => false
            ], 404);
        }

        // Return resident details
        return response()->json([
            'success'  => true,
            'verified' => true,
            'message'  => '✓ Resident verified successfully',
            'data' => [
                'resident_id'  => $resident->id,
                'name'         => $resident->name,
                'phone'        => $resident->phone,
                'email'        => $resident->email,
                'room_number'  => $resident->room->room_number ?? null,
                'room_id'      => $resident->room_id,
                'hostel_name'  => $resident->hostel->name ?? null,
                'photo'        => $resident->profile_image
                                    ? asset('storage/' . $resident->profile_image)
                                    : null,
            ]
        ]);
    }

    /**
     * STORE COMPLAINT
     * Server-side-la inum oru dhadavai resident verify pannum
     * (Frontend bypass panna mudiyathu)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'encoded_id'  => 'required',
            'phone'       => 'required',
            'category'    => 'required|in:electrical,plumbing,furniture,cleaning,wifi,food,security,other',
            'priority'    => 'required|in:low,medium,high,urgent',
            'description' => 'required|string|min:10|max:2000',
            'image'       => 'nullable|image|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $hostelId = self::decodeId($request->encoded_id);
        if (!$hostelId) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $hostel = Hostel::where('id', $hostelId)
                        ->where('status', 'ACTIVE')
                        ->first();

        if (!$hostel) {
            return response()->json(['success' => false, 'message' => 'Hostel not found'], 404);
        }

        // ================================================
        // SERVER-SIDE STRICT RESIDENT VERIFICATION
        // ================================================
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $phone = substr($phone, -10);

        $resident = Resident::with('room')
            ->where('hostel_id', $hostelId)
            ->where(function ($q) use ($phone) {
                $q->where('phone', 'LIKE', "%{$phone}%")
                  ->orWhere('phone', $phone);
            })
            ->where('status', 'ACTIVE')
            ->first();

        // If resident NOT found → BLOCK complaint
        if (!$resident) {
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

        // Create complaint with verified resident_id
        $complaint = Complaint::create([
            'hostel_id'      => $hostelId,
            'resident_id'    => $resident->id,   // ← from DB, not from user input
            'name'           => $resident->name, // ← snapshot from DB
            'phone'          => $resident->phone,
            'email'          => $resident->email,
            'room_number'    => $resident->room->room_number ?? null,
            'category'       => $request->category,
            'priority'       => $request->priority,
            'description'    => $request->description,
            'image'          => $imagePath,
            'status'         => 'pending',
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
