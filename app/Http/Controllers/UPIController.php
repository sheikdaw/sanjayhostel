<?php
// app/Http/Controllers/UPIController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UPIController extends Controller
{
    // Show Payment Form
    public function showPaymentForm()
    {
        return view('payment');
    }

    // Initiate UPI Payment
    public function initiatePayment(Request $request)
    {
        // 🔹 CHANGE THIS TO YOUR UPI ID
        $upiId = "Q940590249@ybl";
        $amount = $request->amount ?? 1.00;
        $merchantName = "Your Store";
        $transactionId = "TXN" . time();
        $description = "Payment for Order";

        // UPI Deep Link URL
        $upiUrl = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$amount}&cu=INR&tn={$description}&tid={$transactionId}";

        // Store transaction in session for verification
        session(['txn_id' => $transactionId, 'amount' => $amount]);

        // Redirect to UPI app
        return redirect()->away($upiUrl);
    }

    // Handle Return After Payment
    public function paymentStatus(Request $request)
    {
        // Get transaction details from session
        $txnId = session('txn_id');
        $amount = session('amount');

        // In real scenario, you need to verify payment status via bank API
        // For demo, we assume success if they return

        return view('payment-status', [
            'status' => 'success',
            'txn_id' => $txnId,
            'amount' => $amount
        ]);
    }
}