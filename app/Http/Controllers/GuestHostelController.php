<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Resident;
use App\Models\Room;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class GuestHostelController extends Controller
{
    /**
     * Show hostel with room-wise residents
     */
    public function show($encodedId)
    {
        try {
            $hostelId = Crypt::decryptString($encodedId);
            $hostel = Hostel::with(['rooms' => function($query) {
                $query->orderBy('room_no', 'asc');
            }, 'rooms.beds.resident'])->findOrFail($hostelId);

            // Get residents grouped by room
            $rooms = Room::where('hostel_id', $hostelId)
                ->with(['beds' => function($query) {
                    $query->where('status', 'OCCUPIED')->with('resident');
                }])
                ->orderBy('room_no', 'asc')
                ->get();

            // Filter rooms with at least one occupied bed
            $occupiedRooms = $rooms->filter(function($room) {
                return $room->beds->count() > 0;
            });

            // Calculate statistics
            $stats = [
                'total_residents' => Resident::where('hostel_id', $hostelId)
                    ->where('status', 'ACTIVE')
                    ->count(),
                'total_rooms' => $occupiedRooms->count(),
                'total_male' => Resident::where('hostel_id', $hostelId)
                    ->where('status', 'ACTIVE')
                    ->whereHas('hostel', function($q) {
                        $q->where('hostel_type', 'MEN');
                    })->count(),
                'total_female' => Resident::where('hostel_id', $hostelId)
                    ->where('status', 'ACTIVE')
                    ->whereHas('hostel', function($q) {
                        $q->where('hostel_type', 'WOMEN');
                    })->count(),
                'total_rent' => Resident::where('hostel_id', $hostelId)
                    ->where('status', 'ACTIVE')
                    ->sum('rent_amount'),
            ];

            return view('guest.hostel-view', compact('hostel', 'occupiedRooms', 'encodedId', 'stats'));

        } catch (Exception $e) {
            abort(404, 'Invalid hostel link');
        }
    }

    /**
     * Get resident payment details
     */
    public function getResidentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|exists:residents,id',
            'hostel_id' => 'required|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $resident = Resident::with(['room', 'bed'])
            ->where('id', $request->resident_id)
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Resident not found'
            ], 404);
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentDay = now()->day;

        // Get current month payment
        $currentPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        // Get pending payments
        $pendingPayments = Payment::where('resident_id', $resident->id)
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $rentAmount = (float) ($resident->rent_amount ?? 0);
        $totalDue = $pendingPayments->sum('balance_amount');

        $isCurrentMonthPaid = false;
        if ($currentPayment && $currentPayment->status === 'PAID') {
            $isCurrentMonthPaid = true;
        }

        if (!$isCurrentMonthPaid) {
            if (!$currentPayment) {
                $totalDue += $rentAmount;
            } else if ($currentPayment->status === 'PARTIAL') {
                $totalDue += $currentPayment->balance_amount;
            }
        }

        // Calculate discount
        $discount = 0;
        $discountMessage = '';
        $finalAmount = $totalDue;

        if ($pendingPayments->count() == 0 && !$isCurrentMonthPaid && $totalDue > 0) {
            if ($currentDay >= 1 && $currentDay <= 5) {
                $discount = min(250, $rentAmount * 0.10);
                $discountMessage = 'Early payment discount (1st-5th): 10% off up to ₹250';
            } elseif ($currentDay >= 6 && $currentDay <= 10) {
                $discount = min(125, $rentAmount * 0.05);
                $discountMessage = 'Early payment discount (6th-10th): 5% off up to ₹125';
            } else {
                $discountMessage = 'No discount available. Pay before 10th for early discount.';
            }
            $finalAmount = max(0, $totalDue - $discount);
        } else {
            if ($isCurrentMonthPaid) {
                $discountMessage = '✅ This month\'s rent is already paid.';
            } elseif ($pendingPayments->count() > 0) {
                $discountMessage = '⚠️ Previous pending payments found. No discount applicable.';
            }
        }

        // Calculate fine
        $fineAmount = 0;
        $fineMessage = '';

        if (!$isCurrentMonthPaid && $currentDay > 10 && $totalDue > 0) {
            $daysLate = $currentDay - 10;
            $fineAmount = $daysLate * 10;
            $fineMessage = "Late fee: ₹10 per day after 10th ({$daysLate} days late)";
        }

        $amountToPay = $finalAmount + $fineAmount;
        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

        // Get payment history
        $paymentHistory = Payment::where('resident_id', $resident->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->map(function($p) {
                return [
                    'month' => date('F', mktime(0, 0, 0, $p->month, 1)) . ' ' . $p->year,
                    'rent' => $p->rent_amount,
                    'paid' => ($p->cash_paid_amount ?? 0) + ($p->upi_paid_amount ?? 0),
                    'balance' => $p->balance_amount,
                    'status' => $p->status,
                    'receipt' => $p->receipt_no,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => $resident->id,
                'name' => $resident->name,
                'phone' => $resident->phone,
                'email' => $resident->email ?? 'Not provided',
                'room_no' => $resident->room->room_no ?? 'N/A',
                'bed_no' => $resident->bed->bed_no ?? 'N/A',
                'profile_image' => $resident->profile_image ? asset($resident->profile_image) : null,
                'rent_amount' => $rentAmount,
                'total_due' => $totalDue,
                'discount' => $discount,
                'discount_message' => $discountMessage,
                'fine_amount' => $fineAmount,
                'fine_message' => $fineMessage,
                'amount_to_pay' => $amountToPay,
                'final_amount' => $amountToPay,
                'reference' => $reference,
                'has_pending' => $pendingPayments->count() > 0,
                'pending_count' => $pendingPayments->count(),
                'is_paid' => $isCurrentMonthPaid && $totalDue == 0,
                'current_month_status' => $currentPayment ? $currentPayment->status : 'PENDING',
                'payment_history' => $paymentHistory,
            ]
        ]);
    }

    /**
     * Manual Payment Entry (Direct to payments table)
     */
    public function manualPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|exists:residents,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,upi,card,bank_transfer',
            'reference' => 'required|string|max:50',
            'remarks' => 'nullable|string|max:500',
            'hostel_id' => 'required|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $resident = Resident::findOrFail($request->resident_id);
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $paidAmount = (float) $request->amount;
        $rentAmount = (float) $resident->rent_amount;

        // Check if payment already exists for this month
        $payment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        if ($payment) {
            // Update existing payment
            if ($request->payment_method == 'cash') {
                $payment->cash_paid_amount = ($payment->cash_paid_amount ?? 0) + $paidAmount;
            } elseif ($request->payment_method == 'upi') {
                $payment->upi_paid_amount = ($payment->upi_paid_amount ?? 0) + $paidAmount;
            } elseif ($request->payment_method == 'card') {
                $payment->card_paid_amount = ($payment->card_paid_amount ?? 0) + $paidAmount;
            } elseif ($request->payment_method == 'bank_transfer') {
                $payment->bank_paid_amount = ($payment->bank_paid_amount ?? 0) + $paidAmount;
            }

            // Calculate total paid
            $totalPaid = ($payment->cash_paid_amount ?? 0) + 
                         ($payment->upi_paid_amount ?? 0) + 
                         ($payment->card_paid_amount ?? 0) + 
                         ($payment->bank_paid_amount ?? 0);
            
            $payment->total_paid_amount = $totalPaid;
            $payment->balance_amount = max(0, $rentAmount - $totalPaid);

            // Update status
            if ($payment->balance_amount <= 0) {
                $payment->status = 'PAID';
                $statusMessage = '✅ Payment completed! Full rent paid.';
            } elseif ($totalPaid > 0) {
                $payment->status = 'PARTIAL';
                $statusMessage = '⚠️ Partial payment recorded. Balance: ₹' . number_format($payment->balance_amount, 2);
            } else {
                $payment->status = 'PENDING';
                $statusMessage = '❌ Payment pending.';
            }

            $payment->payment_date = now();
            $payment->save();

        } else {
            // Create new payment
            $receiptNo = $request->reference;
            // Check if receipt exists, if yes generate new
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }

            $paymentData = [
                'resident_id' => $resident->id,
                'receipt_no' => $receiptNo,
                'month' => $currentMonth,
                'year' => $currentYear,
                'rent_amount' => $rentAmount,
                'discount_amount' => 0,
                'fine_amount' => 0,
                'cash_paid_amount' => 0,
                'upi_paid_amount' => 0,
                'card_paid_amount' => 0,
                'bank_paid_amount' => 0,
                'total_paid_amount' => 0,
                'balance_amount' => $rentAmount,
                'payment_date' => now(),
                'status' => 'PENDING',
            ];

            // Add payment method amount
            if ($request->payment_method == 'cash') {
                $paymentData['cash_paid_amount'] = $paidAmount;
            } elseif ($request->payment_method == 'upi') {
                $paymentData['upi_paid_amount'] = $paidAmount;
            } elseif ($request->payment_method == 'card') {
                $paymentData['card_paid_amount'] = $paidAmount;
            } elseif ($request->payment_method == 'bank_transfer') {
                $paymentData['bank_paid_amount'] = $paidAmount;
            }

            $payment = Payment::create($paymentData);

            // Recalculate totals
            $totalPaid = ($payment->cash_paid_amount ?? 0) + 
                         ($payment->upi_paid_amount ?? 0) + 
                         ($payment->card_paid_amount ?? 0) + 
                         ($payment->bank_paid_amount ?? 0);
            
            $payment->total_paid_amount = $totalPaid;
            $payment->balance_amount = max(0, $rentAmount - $totalPaid);

            // Update status
            if ($payment->balance_amount <= 0) {
                $payment->status = 'PAID';
                $statusMessage = '✅ Payment completed! Full rent paid.';
            } elseif ($totalPaid > 0) {
                $payment->status = 'PARTIAL';
                $statusMessage = '⚠️ Partial payment recorded. Balance: ₹' . number_format($payment->balance_amount, 2);
            } else {
                $payment->status = 'PENDING';
                $statusMessage = '❌ Payment pending.';
            }

            $payment->save();
        }

        // Get updated payment details
        $payment->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment of ₹' . number_format($paidAmount, 2) . ' recorded successfully!',
            'data' => [
                'payment_id' => $payment->id,
                'receipt_no' => $payment->receipt_no,
                'amount_paid' => $paidAmount,
                'total_paid' => ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0) + ($payment->card_paid_amount ?? 0) + ($payment->bank_paid_amount ?? 0),
                'rent_amount' => $rentAmount,
                'balance' => $payment->balance_amount,
                'status' => $payment->status,
                'status_message' => $statusMessage ?? 'Payment recorded',
                'payment_method' => $request->payment_method,
                'is_full_paid' => $payment->status === 'PAID',
                'is_partial' => $payment->status === 'PARTIAL',
            ]
        ]);
    }

    /**
     * Get payment history for a resident
     */
    public function getPaymentHistory($residentId)
    {
        $payments = Payment::where('resident_id', $residentId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function($payment) {
                return [
                    'month' => date('F', mktime(0, 0, 0, $payment->month, 1)) . ' ' . $payment->year,
                    'rent' => $payment->rent_amount,
                    'paid' => ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0) + ($payment->card_paid_amount ?? 0) + ($payment->bank_paid_amount ?? 0),
                    'balance' => $payment->balance_amount,
                    'status' => $payment->status,
                    'receipt_no' => $payment->receipt_no,
                    'date' => $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Generate payment link for hostel
     */
    public function generateLink($hostelId)
    {
        $hostel = Hostel::find($hostelId);
        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found'
            ], 404);
        }

        $encodedId = Crypt::encryptString($hostelId);
        $url = url('/guest/hostel/' . $encodedId);

        return response()->json([
            'success' => true,
            'data' => [
                'hostel_id' => $hostelId,
                'hostel_name' => $hostel->hostel_name,
                'hostel_code' => $hostel->hostel_code,
                'encoded_id' => $encodedId,
                'guest_link' => $url,
            ]
        ]);
    }
}