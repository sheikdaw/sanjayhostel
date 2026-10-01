@extends('layouts.frontend')

@section('title', 'Refund Policy | Sanjay & Harini Hostels, Chennai')
@section('canonical', \App\Support\Seo::url('/refund-policy'))
@section('meta_description', 'Refund policy for room bookings, security deposits and meal plans at Sanjay & Harini Hostels in Alandur and Perungalathur, Chennai.')

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => [['Home', '/'], ['Refund policy', '/refund-policy']]])
            <span class="eyebrow">Policy</span>
            <h1>Refund Policy</h1>
            <p>Clear and transparent refund policies for room bookings, security deposits, and meal plans.</p>
        </div>
    </div>

    <section class="panel-ivory" id="refund-policy">
        <div class="wrap">
            <div class="policy-content reveal">
                <div class="policy-intro">
                    @if (config('hostel.policy_updated'))<p class="last-updated"><strong>Last updated:</strong> {{ config('hostel.policy_updated') }}</p>@endif
                    <p>At Sanjay & Harini Hostels, we strive to provide the best accommodation experience. This refund policy outlines the terms and conditions for refunds on room bookings, security deposits, and meal plans.</p>
                </div>

                <!-- Section 1: Refund Processing Timeline -->
                <div class="policy-section highlight-section">
                    <div class="timeline-badge">⏱️</div>
                    <h2>1. Refund Processing Timeline</h2>
                    <div class="timeline-cards">
                        <div class="timeline-card">
                            <div class="step-number">Step 1</div>
                            <h3>Approval</h3>
                            <p>Once we approve the refund request, we will process the refund within <strong>3–5 business days</strong>.</p>
                        </div>
                        <div class="timeline-card">
                            <div class="step-number">Step 2</div>
                            <h3>Processing</h3>
                            <p>After processing, the refund will be credited to the original mode of payment.</p>
                        </div>
                        <div class="timeline-card">
                            <div class="step-number">Step 3</div>
                            <h3>Crediting</h3>
                            <p>Refund will be credited within <strong>7–10 business days</strong>, depending on the payment provider/bank.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Types of Refunds -->
                <div class="policy-section">
                    <h2>2. Types of Refunds</h2>
                    
                    <div class="refund-type">
                        <h3>🏠 Room Booking Refunds</h3>
                        <ul>
                            <li><strong>Advance Booking:</strong> Full refund if cancelled 7+ days before check-in date.</li>
                            <li><strong>Early Check-out:</strong> Refund for unused days will be processed minus a 10% cancellation fee.</li>
                            <li><strong>No-show:</strong> No refund will be provided for no-shows.</li>
                        </ul>
                    </div>

                    <div class="refund-type">
                        <h3>🔒 Security Deposit Refunds</h3>
                        <ul>
                            <li>Security deposits are refundable subject to the terms of the rental agreement.</li>
                            <li>Deductions may apply for damages, unpaid dues, or outstanding bills.</li>
                            <li>Refunds will be processed within 7–10 business days after room inspection and checkout.</li>
                        </ul>
                    </div>

                    <div class="refund-type">
                        <h3>🍽️ Meal Plan Refunds</h3>
                        <ul>
                            <li>Pre-paid meal plans can be cancelled with a full refund if requested 24+ hours before the start date.</li>
                            <li>No refunds for meals already consumed or partial months.</li>
                            <li>Lunch box orders can be cancelled 2 hours before delivery for a full refund.</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 3: Refund Methods -->
                <div class="policy-section">
                    <h2>3. Refund Methods</h2>
                    <div class="refund-methods-grid">
                        <div class="method-card">
                            <div class="method-icon">💳</div>
                            <h3>Credit/Debit Card</h3>
                            <p>Refunds will be credited back to the original card used for payment within 7–10 business days.</p>
                        </div>
                        <div class="method-card">
                            <div class="method-icon">🏦</div>
                            <h3>Bank Transfer (NEFT/RTGS)</h3>
                            <p>Refunds will be transferred to the provided bank account within 5–7 business days.</p>
                        </div>
                        <div class="method-card">
                            <div class="method-icon">📱</div>
                            <h3>UPI / Digital Wallets</h3>
                            <p>Refunds will be credited to the same UPI/wallet account within 3–5 business days.</p>
                        </div>
                        <div class="method-card">
                            <div class="method-icon">💵</div>
                            <h3>Cash / Cheque</h3>
                            <p>Cash refunds are available at the hostel reception. Cheque refunds take 5–7 business days.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Non-Refundable Items -->
                <div class="policy-section warning-section">
                    <h2>4. Non-Refundable Items</h2>
                    <ul>
                        <li>❌ <strong>Registration Fee:</strong> One-time registration fee is non-refundable.</li>
                        <li>❌ <strong>Service Charges:</strong> Any service charges or convenience fees are non-refundable.</li>
                        <li>❌ <strong>Damages:</strong> Cost of repairing damages caused by the resident.</li>
                        <li>❌ <strong>Unpaid Dues:</strong> Outstanding rent, utility bills, or other charges.</li>
                    </ul>
                </div>

                <!-- Section 5: How to Request a Refund -->
                <div class="policy-section process-section">
                    <h2>5. How to Request a Refund</h2>
                    <div class="process-steps">
                        <div class="process-step">
                            <span class="step-circle">1</span>
                            <div>
                                <h3>Submit a Refund Request</h3>
                                <p>Email us at <a href="mailto:{{ config('hostel.email') }}">{{ config('hostel.email') }}</a> or visit the hostel reception.</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <span class="step-circle">2</span>
                            <div>
                                <h3>Provide Required Details</h3>
                                <p>Include booking ID, reason for refund, and bank/payment details.</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <span class="step-circle">3</span>
                            <div>
                                <h3>Verification</h3>
                                <p>Our team will verify your request within 2-3 business days.</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <span class="step-circle">4</span>
                            <div>
                                <h3>Refund Processing</h3>
                                <p>Once approved, refund will be processed as per the timeline above.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Contact Information -->
                <div class="policy-section contact-section">
                    <h2>6. Need Help?</h2>
                    <p>If you have any questions about our refund policy or need assistance with a refund request, please contact us:</p>
                    <div class="contact-details">
                        <div>
                            <p><strong>📧 Email:</strong> <a href="mailto:{{ config('hostel.email') }}">{{ config('hostel.email') }}</a></p>
                            <p><strong>📞 Phone:</strong> {{ config('hostel.phone_display') }}</p>
                        </div>
                        <div>
                            <p><strong>📍 Hostels:</strong></p>
                            <p>Men's and women's hostels in Alandur and Perungalathur, Chennai. See <a href="{{ route('hostels.index') }}">all our hostels</a>.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="policy-footer">
                    <p>Sanjay & Harini Hostels reserves the right to modify this refund policy at any time. Any changes will be posted on this page.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Back to Top Button -->
    <div style="text-align:center;padding:20px 0 40px;">
        <a href="#top" class="text-link">
            ↑ Back to Top
        </a>
    </div>
@endsection