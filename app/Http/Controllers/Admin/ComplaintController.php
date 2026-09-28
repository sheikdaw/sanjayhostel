<?php
// app/Http/Controllers/Admin/ComplaintController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    /**
     * List page
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        }

        return view('admin.complaint.index', compact('hostels'));
    }

    /**
     * Data endpoint (AJAX)
     */
    public function data(Request $request)
    {
        $user = auth()->user();

        $query = Complaint::with(['resident', 'hostel'])
            ->orderBy('created_at', 'desc');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereIn('hostel_id', $hostelIds);
        }

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('complaint_number', 'LIKE', "%{$s}%")
                  ->orWhere('name', 'LIKE', "%{$s}%")
                  ->orWhere('phone', 'LIKE', "%{$s}%")
                  ->orWhere('room_number', 'LIKE', "%{$s}%");
            });
        }

        $complaints = $query->paginate(20);

        $complaints->getCollection()->transform(function ($c) {
            return [
                'id'               => $c->id,
                'complaint_number' => $c->complaint_number,
                'name'             => $c->name,
                'phone'            => $c->phone,
                'room_number'      => $c->room_number,
                'hostel_name'      => $c->hostel->hostel_name ?? 'N/A',
                'category'         => $c->category,
                'priority'         => $c->priority,
                'status'           => $c->status,
                'description'      => $c->description,
                'admin_remark'     => $c->admin_remark,
                'image'            => $this->complaintImage($c->image),
                'resident_photo'   => $this->residentPhoto($c->resident?->profile_image),
                'created_at'       => $c->created_at->format('d M Y, h:i A'),
                'resolved_at'      => $c->resolved_at?->format('d M Y, h:i A'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $complaints
        ]);
    }

    /**
     * Stats for dashboard cards
     */
    public function stats(Request $request)
    {
        $user = auth()->user();

        $query = Complaint::query();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereIn('hostel_id', $hostelIds);
        }

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        $total      = (clone $query)->count();
        $pending    = (clone $query)->where('status', 'pending')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $resolved   = (clone $query)->where('status', 'resolved')->count();
        $rejected   = (clone $query)->where('status', 'rejected')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total'      => $total,
                'pending'    => $pending,
                'inProgress' => $inProgress,
                'resolved'   => $resolved,
                'rejected'   => $rejected,
            ]
        ]);
    }

    /**
     * Show single complaint
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $complaint = Complaint::with(['resident', 'hostel'])->findOrFail($id);

            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($complaint->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to view this complaint!'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id'               => $complaint->id,
                    'complaint_number' => $complaint->complaint_number,
                    'name'             => $complaint->name,
                    'phone'            => $complaint->phone,
                    'email'            => $complaint->email,
                    'room_number'      => $complaint->room_number,
                    'hostel_name'      => $complaint->hostel->hostel_name ?? 'N/A',
                    'category'         => $complaint->category,
                    'priority'         => $complaint->priority,
                    'status'           => $complaint->status,
                    'description'      => $complaint->description,
                    'admin_remark'     => $complaint->admin_remark,
                    'image'            => $this->complaintImage($complaint->image),
                    'resident_photo'   => $this->residentPhoto($complaint->resident?->profile_image),
                    'created_at'       => $complaint->created_at->format('d M Y, h:i A'),
                    'resolved_at'      => $complaint->resolved_at?->format('d M Y, h:i A'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found'
            ], 404);
        }
    }

    /**
     * Full update
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($complaint->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this complaint!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'category'     => 'required|in:electrical,plumbing,furniture,cleaning,wifi,food,security,other',
            'priority'     => 'required|in:low,medium,high,urgent',
            'description'  => 'required|string|min:10|max:2000',
            'status'       => 'required|in:pending,in_progress,resolved,rejected',
            'admin_remark' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $oldStatus = $complaint->status;

        $complaint->update([
            'category'     => $request->category,
            'priority'     => $request->priority,
            'description'  => $request->description,
            'status'       => $request->status,
            'admin_remark' => $request->admin_remark,
            'resolved_at'  => $request->status === 'resolved' && $oldStatus !== 'resolved'
                                ? now()
                                : ($request->status !== 'resolved' ? null : $complaint->resolved_at),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint updated successfully!',
            'data'    => $complaint->fresh(['resident', 'hostel'])
        ]);
    }

    /**
     * Quick status change
     */
    public function changeStatus(Request $request, $id)
    {
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($complaint->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $complaint->status = $request->status;
        $complaint->resolved_at = $request->status === 'resolved' ? now() : null;
        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . $request->status,
            'data'    => $complaint
        ]);
    }

    /**
     * Delete complaint
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($complaint->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied!'
                ], 403);
            }
        }

        $this->deleteComplaintImage($complaint->image);

        $complaint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint deleted successfully!'
        ]);
    }

    /**
     * Bulk status update
     */
    public function bulkStatus(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ids'    => 'required|array',
            'ids.*'  => 'exists:complaints,id',
            'status' => 'required|in:pending,in_progress,resolved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $complaints = Complaint::whereIn('id', $request->ids)->get();
        $updated = 0;

        foreach ($complaints as $complaint) {
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($complaint->hostel_id, $hostelIds)) continue;
            }

            $complaint->status = $request->status;
            $complaint->resolved_at = $request->status === 'resolved' ? now() : null;
            $complaint->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} complaints updated successfully!"
        ]);
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:complaints,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $complaints = Complaint::whereIn('id', $request->ids)->get();
        $deleted = 0;

        foreach ($complaints as $complaint) {
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($complaint->hostel_id, $hostelIds)) continue;
            }

            $this->deleteComplaintImage($complaint->image);

            $complaint->delete();
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$deleted} complaints deleted successfully!"
        ]);
    }

    // ============================================
    // HELPER METHODS (image URL fix)
    // ============================================

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

        return null; // frontend la initial avatar show aagum
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

    /**
     * Delete complaint image file from public folder
     */
    private function deleteComplaintImage(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
