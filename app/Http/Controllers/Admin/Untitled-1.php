<?pp
public function store(Request $request)
{
    $user = auth()->user();

    $resident = Resident::find($request->resident_id);
    if (!$resident) {
        return response()->json([
            'success' => false,
            'message' => 'Resident not found!'
        ], 404);
    }

    // Permission check
    if ($user->role !== 'admin') {
        $hostelIds = $user->hostel_ids ?? [];
        if (!in_array($resident->hostel_id, $hostelIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add payments for this resident!'
            ], 403);
        }
    }

    // Validation
    $validator = Validator::make($request->all(), [
        'resident_id' => 'required|exists:residents,id',
        'month' => 'required|integer|min:1|max:12',
        'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        'cash_paid_amount' => 'required|numeric|min:0',
        'upi_paid_amount' => 'required|numeric|min:0',
        'payment_date' => 'required|date',
        'transaction_id' => 'nullable|string|max:500',
        'fine_amount' => 'nullable|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    DB::beginTransaction();

    try {
        $paymentDate = $request->payment_date;
        $month = $request->month;
        $year = $request->year;
        $rentAmount = (float) ($resident->rent_amount ?? 0);
        $fineAmount = (float) ($request->fine_amount ?? 0);
        
        // Total paid by customer
        $totalPaid = (float) $request->cash_paid_amount + (float) $request->upi_paid_amount + $fineAmount;

        // ✅ Calculate discount based on payment date ONLY
        $discount = (float) $this->calculateDiscount($paymentDate);

        // ✅ Check if already paid for this month
        $existingPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existingPayment && $existingPayment->status === 'PAID') {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "⚠️ Payment already completed for " . date('F Y', mktime(0,0,0,$month,1,$year)) . "!\n" .
                             "Receipt: {$existingPayment->receipt_no}\n" .
                             "Amount: " . number_format($existingPayment->rent_amount, 2) . "\n" .
                             "Status: PAID ✅",
                'data' => [
                    'existing_payment' => $existingPayment,
                    'receipt_no' => $existingPayment->receipt_no,
                    'amount' => $existingPayment->rent_amount,
                    'status' => $existingPayment->status
                ]
            ], 422);
        }

        // ✅ Get previous pending payments (months before current)
        $previousPendingList = $this->getPreviousPendingDetails($resident->id, $month, $year);
        $totalPreviousPending = $previousPendingList->sum('balance_amount');

        // ✅ TOTAL NEED TO PAY = Current Rent + Previous Pending - Discount
        $totalNeedToPay = $rentAmount + $totalPreviousPending - $discount;

        // ✅ Customer actually paid = cash + UPI + fine - discount
        $toPay = $totalPaid - $discount;

        // Generate receipt number
        $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        while (Payment::where('receipt_no', $receiptNo)->exists()) {
            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        }

        // Initialize variables
        $previousPaid = 0;
        $currentPaid = 0;
        $advanceAmount = 0;
        $previousBalance = 0;
        $currentBalance = 0;
        $totalBalance = 0;
        $previousClearedCount = 0;
        $remaining = $toPay;

        // ✅ STEP 1: Clear Previous Pending (oldest first)
        foreach ($previousPendingList as $prevPayment) {
            if ($remaining <= 0) break;

            $prevBalance = $prevPayment->balance_amount;
            $payAmount = min($remaining, $prevBalance);
            
            if ($payAmount > 0) {
                // Update previous payment
                $prevPayment->cash_paid_amount += $payAmount;
                $newBalance = $prevBalance - $payAmount;
                $prevPayment->balance_amount = max(0, $newBalance);
                $prevPayment->status = ($newBalance <= 0) ? 'PAID' : 'PARTIAL';

                // Append transaction ID
                if ($request->transaction_id && $payAmount > 0) {
                    if ($prevPayment->transaction_id) {
                        $prevPayment->transaction_id .= ' / ' . $request->transaction_id;
                    } else {
                        $prevPayment->transaction_id = $request->transaction_id;
                    }
                }

                // ✅ Remark: Show which month's payment is being cleared
                $monthName = date('F Y', mktime(0,0,0,$prevPayment->month,1,$prevPayment->year));
                $prevPayment->remark = ($newBalance <= 0)
                    ? "✅ Previous {$monthName} pending " . number_format($prevBalance, 2) . " cleared using " . date('F Y', mktime(0,0,0,$month,1,$year)) . " payment. (Receipt: {$receiptNo})"
                    : "🟡 Partially cleared {$monthName} pending: " . number_format($payAmount, 2) . " paid. Remaining: " . number_format($newBalance, 2) . ". (Receipt: {$receiptNo})";

                $prevPayment->save();

                $previousPaid += $payAmount;
                $remaining -= $payAmount;
                if ($newBalance <= 0) {
                    $previousClearedCount++;
                }
            }
        }

        $previousBalance = max(0, $totalPreviousPending - $previousPaid);

        // ✅ STEP 2: Pay Current Month (remaining amount)
        $currentDue = $rentAmount - $discount + $fineAmount;
        $currentPaid = min($remaining, $currentDue);
        $remaining -= $currentPaid;
        $currentBalance = max(0, $currentDue - $currentPaid);

        // ✅ STEP 3: Remaining goes to Advance Payment
        $advanceAmount = max(0, $remaining);

        // ✅ Total balance after all allocations
        $totalBalance = $previousBalance + $currentBalance;

        // ✅ Determine status
        $status = 'PENDING';
        if ($totalBalance <= 0 && $advanceAmount >= 0) {
            $status = 'PAID';
        } elseif ($totalPaid > 0 && $totalBalance > 0) {
            $status = 'PARTIAL';
        }

        // ✅ Generate current month payment remark
        $monthName = date('F Y', mktime(0,0,0,$month,1,$year));
        $remarkParts = [];

        // Previous pending clearing info
        if ($previousPaid > 0) {
            if ($previousBalance <= 0) {
                $remarkParts[] = "✅ Previous pending fully cleared (" . number_format($previousPaid, 2) . ")";
            } else {
                $remarkParts[] = "🟡 Previous pending partially cleared (" . number_format($previousPaid, 2) . "). Remaining: " . number_format($previousBalance, 2);
            }
        } elseif ($totalPreviousPending > 0) {
            $remarkParts[] = "⚠️ Previous pending (" . number_format($totalPreviousPending, 2) . ") not cleared";
        } else {
            $remarkParts[] = "✅ No previous pending";
        }

        // Current month payment info
        if ($currentPaid > 0) {
            if ($currentBalance <= 0) {
                $remarkParts[] = "✅ {$monthName} fully paid (" . number_format($currentPaid, 2) . ")";
            } else {
                $remarkParts[] = "🟡 {$monthName} partial paid (" . number_format($currentPaid, 2) . "). Balance: " . number_format($currentBalance, 2);
            }
        } else {
            $remarkParts[] = "⏳ {$monthName} not paid";
        }

        // Advance payment info
        if ($advanceAmount > 0) {
            $remarkParts[] = "💰 Advance payment: " . number_format($advanceAmount, 2) . " (will adjust next month)";
        }

        // Total balance info
        if ($totalBalance > 0) {
            $remarkParts[] = "📊 Total pending: " . number_format($totalBalance, 2);
        } else {
            $remarkParts[] = "✅ All dues cleared!";
        }

        $remark = implode(" | ", $remarkParts);

        // ✅ Create or update payment for current month
        if ($existingPayment) {
            // Update existing payment (for partial completion or additional payment)
            $existingPayment->cash_paid_amount += $currentPaid;
            $existingPayment->upi_paid_amount += 0;
            $existingPayment->balance_amount = $currentBalance;
            $existingPayment->status = $status;
            $existingPayment->payment_date = $paymentDate;
            $existingPayment->discount_amount = $discount;
            $existingPayment->fine_amount = $fineAmount;
            $existingPayment->remark = $remark;
            
            if ($request->transaction_id) {
                if ($existingPayment->transaction_id) {
                    $existingPayment->transaction_id .= ' / ' . $request->transaction_id;
                } else {
                    $existingPayment->transaction_id = $request->transaction_id;
                }
            }
            
            $existingPayment->save();
            $payment = $existingPayment;
        } else {
            // Create new payment
            $payment = Payment::create([
                'resident_id' => $resident->id,
                'receipt_no' => $receiptNo,
                'month' => $month,
                'year' => $year,
                'rent_amount' => $rentAmount,
                'discount_amount' => $discount,
                'fine_amount' => $fineAmount,
                'cash_paid_amount' => $currentPaid,
                'upi_paid_amount' => 0,
                'balance_amount' => $currentBalance,
                'payment_date' => $paymentDate,
                'transaction_id' => $request->transaction_id,
                'status' => $status,
                'payment_type' => 'all',
                'previous_pending_cleared' => $previousPaid,
                'remark' => $remark,
            ]);
        }

        $payment->load(['resident.hostel', 'resident.room']);

        DB::commit();

        // ✅ Build response message
        $message = $this->buildDetailedResponseMessage(
            $totalPaid,
            $previousPaid,
            $currentPaid,
            $advanceAmount,
            $previousBalance,
            $currentBalance,
            $totalBalance,
            $totalPreviousPending,
            $previousClearedCount,
            $receiptNo,
            $existingPayment ? true : false
        );

        // ✅ Add discount info
        $discountMessage = $discount > 0
            ? "✅ Discount applied: " . number_format($discount, 2) . " (" . ($discount == 250 ? 'Early Bird 1st-5th' : 'Early Payment 6th-10th') . ")"
            : "❌ No discount applied (Payment after 10th)";

        return response()->json([
            'success' => true,
            'message' => $message . "\n" . $discountMessage,
            'data' => [
                'payment' => $payment,
                'receipt_no' => $receiptNo,
                'total_paid' => $totalPaid,
                'previous_pending_cleared' => $previousPaid,
                'previous_pending_remaining' => $previousBalance,
                'current_month_paid' => $currentPaid,
                'current_month_balance' => $currentBalance,
                'advance_payment' => $advanceAmount,
                'total_balance' => $totalBalance,
                'status' => $status,
                'discount_applied' => $discount,
                'discount_type' => $discount > 0 ? ($discount == 250 ? 'Early Bird (1st-5th)' : 'Early Payment (6th-10th)') : 'No discount',
                'fine_applied' => $fineAmount,
                'previous_months_cleared' => $previousClearedCount,
                'is_existing_payment' => $existingPayment ? true : false,
                'remark' => $remark,
                'calculation' => [
                    'rent' => $rentAmount,
                    'discount' => $discount,
                    'previous_pending' => $totalPreviousPending,
                    'current_due' => $currentDue,
                    'total_need_to_pay' => $totalNeedToPay,
                    'customer_paid' => $toPay,
                    'total_paid' => $totalPaid,
                    'payment_day' => date('j', strtotime($paymentDate)),
                    'scenario' => $totalNeedToPay > $toPay ? 'Partial Payment' : ($totalNeedToPay == $toPay ? 'Exact Payment' : 'Over Payment')
                ]
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Payment Store Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to create payment: ' . $e->getMessage()
        ], 500);
    }
}/**
 * Get previous pending payments with details
 */
private function getPreviousPendingDetails($residentId, $month, $year)
{
    return Payment::where('resident_id', $residentId)
        ->where(function($q) use ($month, $year) {
            $q->where('year', '<', $year)
              ->orWhere(function($q2) use ($month, $year) {
                  $q2->where('year', $year)
                     ->where('month', '<', $month);
              });
        })
        ->whereIn('status', ['PENDING', 'PARTIAL'])
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
}