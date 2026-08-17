public function payment($amount)
{
    $upiId = 'YOUR_UPI_ID@bank';
    $name = 'SGT Solutions';

    $upiUrl = 'upi://pay?' . http_build_query([
        'pa' => $upiId,
        'pn' => $name,
        'am' => $amount,
        'cu' => 'INR',
        'tn' => 'Payment'
    ]);

    return view('payment', compact('upiUrl', 'amount'));
}