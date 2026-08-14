@extends('layouts.frontend')

@section('title', 'Refund Policy | Sanjay & Harini Hostels, Chennai')
@section('canonical', 'https://www.sanjayandharinihostels.com/refund-policy')
@section('meta_description', 'Read our refund policy for room bookings, security deposits, and meal plans at Sanjay Boys Hostel and Harini Girls Hostel, Chennai.')

@section('content')
<style>
    /* Refund Policy Page Styles */
.policy-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px 0;
}

.policy-intro {
    background: #f9f6f0;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 40px;
    border-left: 4px solid #c9a84c;
}

.policy-intro .last-updated {
    color: #888;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.policy-intro p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #444;
    margin: 0;
}

.policy-section {
    margin-bottom: 45px;
    padding-bottom: 40px;
    border-bottom: 1px solid #eee;
}

.policy-section:last-of-type {
    border-bottom: none;
}

.policy-section h2 {
    font-size: 1.4rem;
    color: #2d2d2d;
    margin-bottom: 20px;
    font-weight: 600;
}

.policy-section h2::before {
    content: "";
    display: inline-block;
    width: 4px;
    height: 24px;
    background: #c9a84c;
    margin-right: 12px;
    vertical-align: middle;
    border-radius: 2px;
}

.policy-section ul {
    padding-left: 20px;
}

.policy-section ul li {
    padding: 8px 0;
    color: #555;
    line-height: 1.7;
    font-size: 1rem;
}

.policy-section ul li::marker {
    color: #c9a84c;
}

/* Highlight Section - Refund Timeline */
.highlight-section {
    background: linear-gradient(135deg, #fcf9f0 0%, #f5f0e0 100%);
    padding: 40px;
    border-radius: 16px;
    border: 1px solid #e8e0d0;
    position: relative;
}

.timeline-badge {
    font-size: 2.5rem;
    text-align: center;
    margin-bottom: 10px;
}

.timeline-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 20px;
}

.timeline-card {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
    transition: transform 0.3s ease;
}

.timeline-card:hover {
    transform: translateY(-5px);
}

.timeline-card .step-number {
    background: #c9a84c;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 14px;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
}

.timeline-card h3 {
    font-size: 1.1rem;
    color: #2d2d2d;
    margin-bottom: 8px;
}

.timeline-card p {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
}

/* Refund Types */
.refund-type {
    background: #fff;
    padding: 20px 25px;
    border-radius: 10px;
    margin-bottom: 15px;
    border: 1px solid #f0ece0;
    transition: all 0.3s ease;
}

.refund-type:hover {
    border-color: #c9a84c;
    box-shadow: 0 2px 12px rgba(201,168,76,0.1);
}

.refund-type h3 {
    font-size: 1.1rem;
    color: #2d2d2d;
    margin-bottom: 10px;
}

.refund-type ul {
    margin: 0;
}

.refund-type ul li {
    padding: 5px 0;
}

/* Refund Methods Grid */
.refund-methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.method-card {
    background: #fff;
    padding: 25px 20px;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #f0ece0;
    transition: all 0.3s ease;
}

.method-card:hover {
    border-color: #c9a84c;
    transform: translateY(-3px);
}

.method-card .method-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.method-card h3 {
    font-size: 1rem;
    color: #2d2d2d;
    margin-bottom: 8px;
}

.method-card p {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.6;
    margin: 0;
}

/* Warning Section */
.warning-section {
    background: #fdf6f0;
    padding: 30px 40px;
    border-radius: 12px;
    border-left: 4px solid #d9534f;
}

.warning-section ul li {
    color: #555;
}

.warning-section ul li strong {
    color: #2d2d2d;
}

/* Process Steps */
.process-steps {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 15px;
}

.process-step {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 15px 20px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #f0ece0;
    transition: all 0.3s ease;
}

.process-step:hover {
    border-color: #c9a84c;
}

.process-step .step-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    background: #c9a84c;
    color: #fff;
    font-weight: 700;
    border-radius: 50%;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.process-step h4 {
    font-size: 1rem;
    color: #2d2d2d;
    margin: 0 0 4px 0;
}

.process-step p {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
    line-height: 1.6;
}

.process-step p a {
    color: #c9a84c;
    text-decoration: none;
}

.process-step p a:hover {
    text-decoration: underline;
}

/* Contact Section */
.contact-section {
    background: #f9f6f0;
    padding: 30px;
    border-radius: 12px;
}

.contact-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 15px;
}

.contact-details p {
    margin: 5px 0;
    line-height: 1.8;
    color: #555;
}

.contact-details a {
    color: #c9a84c;
    text-decoration: none;
}

.contact-details a:hover {
    text-decoration: underline;
}

/* Policy Footer */
.policy-footer {
    margin-top: 40px;
    padding: 20px 30px;
    background: #2d2d2d;
    color: #f5f0e0;
    border-radius: 12px;
    text-align: center;
}

.policy-footer p {
    margin: 5px 0;
    font-size: 0.95rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .timeline-cards {
        grid-template-columns: 1fr;
    }
    
    .refund-methods-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .contact-details {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .highlight-section {
        padding: 20px;
    }
    
    .warning-section {
        padding: 20px;
    }
    
    .process-step {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .refund-methods-grid {
        grid-template-columns: 1fr;
    }
}
</style>
    <div class="page-hero panel-ivory">
        <div class="wrap">
            <span class="eyebrow">Policy</span>
            <h1>Refund Policy</h1>
            <p>Clear and transparent refund policies for room bookings, security deposits, and meal plans.</p>
        </div>
    </div>

    <section class="panel-ivory" id="refund-policy">
        <div class="wrap">
            <div class="policy-content reveal">
                <div class="policy-intro">
                    <p class="last-updated"><strong>Last Updated:</strong> {{ now()->format('d F, Y') }}</p>
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
                                <h4>Submit a Refund Request</h4>
                                <p>Email us at <a href="mailto:info@sanjayandharinihostels.com">info@sanjayandharinihostels.com</a> or visit the hostel reception.</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <span class="step-circle">2</span>
                            <div>
                                <h4>Provide Required Details</h4>
                                <p>Include booking ID, reason for refund, and bank/payment details.</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <span class="step-circle">3</span>
                            <div>
                                <h4>Verification</h4>
                                <p>Our team will verify your request within 2-3 business days.</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <span class="step-circle">4</span>
                            <div>
                                <h4>Refund Processing</h4>
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
                            <p><strong>📧 Email:</strong> <a href="mailto:info@sanjayandharinihostels.com">info@sanjayandharinihostels.com</a></p>
                            <p><strong>📞 Phone:</strong> +91 98765 43210 (Sanjay Boys) | +91 98765 43211 (Harini Girls)</p>
                        </div>
                        <div>
                            <p><strong>📍 Address:</strong></p>
                            <p>Sanjay Boys Hostel<br>Alandur, St. Thomas Mount, Perungalathur, Chennai</p>
                            <p>Harini Girls Hostel<br>Alandur, St. Thomas Mount, Chennai</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="policy-footer">
                    <p>Last reviewed: {{ now()->format('d F, Y') }}</p>
                    <p>Sanjay & Harini Hostels reserves the right to modify this refund policy at any time. Any changes will be posted on this page.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Back to Top Button -->
    <div style="text-align:center;padding:20px 0 40px;">
        <a href="#top" class="back-to-top" style="display:inline-block;color:#c9a84c;text-decoration:none;font-weight:600;">
            ↑ Back to Top
        </a>
    </div>
@endsection