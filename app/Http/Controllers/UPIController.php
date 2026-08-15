<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UPIController extends Controller
{
    // Show Payment Page with QR
    public function showPaymentForm()
    {
        $upiId = "sheikjob888@okicici";
        $amount = 1.00;
        $merchantName = "Your Store";
        
        // Generate UPI URL
        $upiUrl = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$amount}&cu=INR";
        
        // Generate QR Code
        $qrCode = QrCode::size(250)->generate($upiUrl);
        
        return view('payment', compact('qrCode', 'upiId', 'amount', 'upiUrl'));
    }

    // Initiate UPI Payment (Auto-Pay)
    public function initiatePayment(Request $request)
    {
        $upiId = "sheikjob888@okicici";
        $amount = $request->amount ?? 1.00;
        $merchantName = "Your Store";
        $transactionId = "TXN" . time();
        $description = "Payment for Order";

        // UPI Deep Link URL
        $upiUrl = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$amount}&cu=INR&tn={$description}&tid={$transactionId}";

        // Store transaction in session
        session(['txn_id' => $transactionId, 'amount' => $amount, 'upi_id' => $upiId]);

        // Auto-redirect to UPI app
        return redirect()->away($upiUrl);
    }

    // Handle Return After Payment
    public function paymentStatus(Request $request)
    {
        $txnId = session('txn_id');
        $amount = session('amount');
        $upiId = session('upi_id');

        return view('payment-status', [
            'status' => 'success',
            'txn_id' => $txnId,
            'amount' => $amount,
            'upi_id' => $upiId
        ]);
    }
}