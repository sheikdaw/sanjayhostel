<?php
// app/Http/Controllers/GuestComplaintController.php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
     * VERIFY RESIDENT BY PHONE — strict exact match
     */
    public function verifyResident(Request $request)
    {
        Log::info('verifyResident called', [
            'encoded_id' => $request->encoded_id,
            'phone'      => $request->phone,
        ]);

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

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $phone = substr($phone, -10);

        if (strlen($phone) !== 10) {
            return response()->json([
                'success'  => false,
                'message'  => '❌ Enter a valid 10-digit mobile number.',
                'verified' => false
            ], 422);
        }

        $resident = $this->findActiveResident($hostelId, $phone, ['room', 'hostel']);

        if (!$resident) {
            Log::info('verifyResident — no match', ['phone' => $phone, 'hostel_id' => $hostelId]);

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
                'photo'       => $this->residentPhoto($resident->profile_image),
            ]
        ]);
    }

    /**
     * STORE COMPLAINT
     * Image saved to public/complaints/
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

        $hostelId = self::decodeId($request->encoded_id);
        if (!$hostelId) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $hostel = Hostel::where('id', $hostelId)->where('status', 'ACTIVE')->first();
        if (!$hostel) {
            return response()->json(['success' => false, 'message' => 'Hostel not found'], 404);
        }

        // ================================================
        // RESIDENT VERIFICATION BY PHONE
        // ================================================
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $phone = substr($phone, -10);

        if (strlen($phone) !== 10) {
            return response()->json([
                'success' => false,
                'message' => '❌ Invalid phone number.',
                'code'    => 'INVALID_PHONE'
            ], 422);
        }

        $resident = $this->findActiveResident($hostelId, $phone, ['room']);

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

        // ================================================
        // IMAGE UPLOAD → public/complaints/
        // ================================================
        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Invalid image file uploaded.'
                ], 422);
            }

            $filename = 'complaint_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

            $destinationPath = public_path('complaints');

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            // Relative path from public root
            $imagePath = 'complaints/' . $filename;

            Log::info('Complaint image saved', [
                'filename' => $filename,
                'path'     => $imagePath,
            ]);
        }

        // ================================================
        // CREATE COMPLAINT
        // ================================================
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
                'image'            => $this->complaintImage($complaint->image),
                'resident_photo'   => $this->residentPhoto($complaint->resident?->profile_image),
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

        if (strlen($phone) !== 10) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid 10-digit mobile number.'
            ], 422);
        }

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
                    'description'      => mb_substr($c->description, 0, 100),
                    'created_at'       => $c->created_at->format('d M Y'),
                    'admin_remark'     => $c->admin_remark,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $complaints
        ]);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Find ACTIVE resident of a hostel by last 10 digits of phone
     */
    private function findActiveResident($hostelId, string $phone, array $with = []): ?Resident
    {
        $residents = Resident::with($with)
            ->where('hostel_id', $hostelId)
            ->where('status', 'ACTIVE')
            ->get();

        foreach ($residents as $r) {
            $storedPhone = preg_replace('/[^0-9]/', '', $r->phone ?? '');
            if (substr($storedPhone, -10) === $phone) {
                return $r;
            }
        }

        return null;
    }

    /**
     * Resident profile image URL.
     * ResidentController saves to: public/uploads/residents/profile/xxx.jpg
     */
    private function residentPhoto(?string $path): ?string
    {
        if (!$path) return null;

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        // 1. public folder (new uploads)
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // 2. public/storage symlink (old records)
        $clean = preg_replace('#^storage/#', '', $path);
        if (file_exists(public_path('storage/' . $clean))) {
            return asset('storage/' . $clean);
        }

        // 3. only filename stored
        $alt = 'uploads/residents/profile/' . basename($path);
        if (file_exists(public_path($alt))) {
            return asset($alt);
        }

        return null;
    }

    /**
     * Complaint image URL (public/complaints/xxx)
     */
    private function complaintImage(?string $path): ?string
    {
        if (!$path) return null;

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        return file_exists(public_path($path)) ? asset($path) : null;
    }
}
