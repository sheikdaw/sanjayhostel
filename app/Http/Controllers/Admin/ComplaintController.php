<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    /**
     * Admin complaints listing page.
     */
    public function index()
{
    $hostels = Hostel::where('status', 'ACTIVE')
        ->orderBy('hostel_name')
        ->get(['id', 'hostel_name']);

    return view('admin.complaint.index', compact('hostels'));
}
    /**
     * AJAX: Return filtered complaint list.
     * Admin sees ALL hostels (or filtered by hostel_id).
     */
    public function data(Request $request)
    {
        $q = Complaint::with(['resident:id,name,phone,profile_image', 'hostel:id,hostel_name'])
            ->orderBy('created_at', 'desc');

        // Filter: hostel
        if ($request->filled('hostel_id')) {
            $q->where('hostel_id', $request->hostel_id);
        }

        // Filter: status
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        // Filter: priority
        if ($request->filled('priority')) {
            $q->where('priority', $request->priority);
        }

        // Filter: category
        if ($request->filled('category')) {
            $q->where('category', $request->category);
        }

        // Filter: date range
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->to);
        }

        // Search: complaint no, name, phone, room
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('complaint_number', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('room_number', 'like', "%{$s}%");
            });
        }

        $complaints = $q->paginate(20);

        $complaints->getCollection()->transform(function ($c) {
            return [
                'id'                => $c->id,
                'complaint_number'  => $c->complaint_number,
                'hostel_id'         => $c->hostel_id,
                'hostel_name' => $c->hostel->hostel_name ?? '—',
                'resident_id'       => $c->resident_id,
                'name'              => $c->name,
                'phone'             => $c->phone,
                'email'             => $c->email,
                'room_number'       => $c->room_number,
                'category'          => $c->category,
                'priority'          => $c->priority,
                'description'       => $c->description,
                'status'            => $c->status,
                'admin_remark'      => $c->admin_remark,
                'image'             => $c->image ? asset('storage/' . $c->image) : null,
                'resident_photo'    => $c->resident?->profile_image
                                        ? asset('storage/' . $c->resident->profile_image)
                                        : null,
                'created_at'        => optional($c->created_at)->format('d M Y, h:i A'),
                'updated_at'        => optional($c->updated_at)->format('d M Y, h:i A'),
                'resolved_at'       => optional($c->resolved_at)->format('d M Y, h:i A'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $complaints,
        ]);
    }

    /**
     * Stats for dashboard cards.
     */
    public function stats(Request $request)
    {
        $q = Complaint::query();

        if ($request->filled('hostel_id')) {
            $q->where('hostel_id', $request->hostel_id);
        }

        $total      = (clone $q)->count();
        $pending    = (clone $q)->where('status', 'pending')->count();
        $inProgress = (clone $q)->where('status', 'in_progress')->count();
        $resolved   = (clone $q)->where('status', 'resolved')->count();
        $rejected   = (clone $q)->where('status', 'rejected')->count();
        $urgent     = (clone $q)->where('priority', 'urgent')
                                ->whereIn('status', ['pending', 'in_progress'])
                                ->count();

        return response()->json([
            'success' => true,
            'data' => compact('total', 'pending', 'inProgress', 'resolved', 'rejected', 'urgent'),
        ]);
    }

    /**
     * Show single complaint.
     */
    public function show($id)
    {
        $c = Complaint::with(['resident:id,name,phone,email,profile_image', 'hostel:id,name'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id'                => $c->id,
                'complaint_number'  => $c->complaint_number,
                'hostel_id'         => $c->hostel_id,
                'hostel_name' => $c->hostel->hostel_name ?? '—',
                'resident_id'       => $c->resident_id,
                'name'              => $c->name,
                'phone'             => $c->phone,
                'email'             => $c->email,
                'room_number'       => $c->room_number,
                'category'          => $c->category,
                'priority'          => $c->priority,
                'description'       => $c->description,
                'status'            => $c->status,
                'admin_remark'      => $c->admin_remark,
                'image'             => $c->image ? asset('storage/' . $c->image) : null,
                'resident_photo'    => $c->resident?->profile_image
                                        ? asset('storage/' . $c->resident->profile_image)
                                        : null,
                'created_at'        => optional($c->created_at)->format('d M Y, h:i A'),
                'resolved_at'       => optional($c->resolved_at)->format('d M Y, h:i A'),
            ],
        ]);
    }

    /**
     * Full update of a complaint.
     * Editable: category, priority, description, status, admin_remark.
     */
    public function update(Request $request, $id)
    {
        $c = Complaint::findOrFail($id);

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
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Auto-manage resolved_at
        $resolvedAt = $c->resolved_at;
        if ($request->status === 'resolved' && !$resolvedAt) {
            $resolvedAt = now();
        } elseif ($request->status !== 'resolved') {
            $resolvedAt = null;
        }

        $c->update([
            'category'     => $request->category,
            'priority'     => $request->priority,
            'description'  => $request->description,
            'status'       => $request->status,
            'admin_remark' => $request->admin_remark,
            'resolved_at'  => $resolvedAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✓ Complaint updated successfully',
            'data'    => [
                'id'         => $c->id,
                'status'     => $c->status,
                'updated_at' => $c->updated_at->format('d M Y, h:i A'),
            ],
        ]);
    }

    /**
     * Quick status change.
     */
    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'       => 'required|in:pending,in_progress,resolved,rejected',
            'admin_remark' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $c = Complaint::findOrFail($id);

        $resolvedAt = $c->resolved_at;
        if ($request->status === 'resolved' && !$resolvedAt) {
            $resolvedAt = now();
        } elseif ($request->status !== 'resolved') {
            $resolvedAt = null;
        }

        $c->update([
            'status'       => $request->status,
            'admin_remark' => $request->admin_remark ?? $c->admin_remark,
            'resolved_at'  => $resolvedAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => "✓ Status updated to {$c->status}",
            'data'    => ['id' => $c->id, 'status' => $c->status],
        ]);
    }

    /**
     * Delete a complaint.
     */
    public function destroy($id)
    {
        $c = Complaint::findOrFail($id);

        // Delete image file if present
        if ($c->image && \Storage::disk('public')->exists($c->image)) {
            \Storage::disk('public')->delete($c->image);
        }

        $number = $c->complaint_number;
        $c->delete();

        return response()->json([
            'success' => true,
            'message' => "✓ Complaint {$number} deleted",
        ]);
    }

    /**
     * Bulk status update.
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:complaints,id',
            'status' => 'required|in:pending,in_progress,resolved,rejected',
        ]);

        Complaint::whereIn('id', $request->ids)->update([
            'status'     => $request->status,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✓ Bulk status updated',
        ]);
    }

    /**
     * Bulk delete.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:complaints,id',
        ]);

        Complaint::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => '✓ Complaints deleted',
        ]);
    }
}