@extends('layouts.frontend')

@section('title', 'Terms & Conditions | Sanjay & Harini Hostels, Chennai')
@section('canonical', 'https://www.sanjayandharinihostels.com/terms')
@section('meta_description', 'Read the Terms and Conditions for Sanjay Boys Hostel and Harini Girls Hostel. Learn about our policies, user responsibilities, and governing laws.')

@section('content')
<style>
    /* Terms & Conditions Page Styles */
.terms-content {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px 0;
}

.terms-intro {
    background: #f9f6f0;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 40px;
    border-left: 4px solid #c9a84c;
}

.terms-intro .last-updated {
    color: #888;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.terms-intro p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #444;
    margin: 0;
}

.terms-section {
    margin-bottom: 35px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.terms-section:last-of-type {
    border-bottom: none;
}

.terms-section h2 {
    font-size: 1.4rem;
    color: #2d2d2d;
    margin-bottom: 15px;
    font-weight: 600;
}

.terms-section h2::before {
    content: "";
    display: inline-block;
    width: 4px;
    height: 24px;
    background: #c9a84c;
    margin-right: 12px;
    vertical-align: middle;
    border-radius: 2px;
}

.terms-section ul {
    padding-left: 20px;
    margin: 10px 0;
}

.terms-section ul li {
    padding: 8px 0;
    color: #555;
    line-height: 1.7;
    font-size: 1rem;
}

.terms-section ul li::marker {
    color: #c9a84c;
}

.contact-section {
    background: #f9f6f0;
    padding: 30px;
    border-radius: 12px;
    margin-top: 20px;
}

.contact-section h2 {
    margin-top: 0;
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

.contact-details p strong {
    color: #2d2d2d;
    display: block;
    margin-bottom: 5px;
}

.terms-footer {
    margin-top: 50px;
    padding: 25px 30px;
    background: #2d2d2d;
    color: #f5f0e0;
    border-radius: 12px;
    text-align: center;
}

.terms-footer p {
    margin: 8px 0;
    font-size: 1rem;
}

.terms-footer .business-name {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,0.1);
    font-size: 0.95rem;
    color: #c9a84c;
}

.back-to-top {
    transition: color 0.3s ease;
}

.back-to-top:hover {
    color: #b0943d !important;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .contact-details {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .terms-section h2 {
        font-size: 1.2rem;
    }
    
    .terms-intro {
        padding: 20px;
    }
    
    .contact-section {
        padding: 20px;
    }
}
</style>
    <div class="page-hero panel-ivory">
        <div class="wrap">
            <span class="eyebrow">Legal</span>
            <h1>Terms & Conditions</h1>
            <p>Please read these terms carefully before using our website or booking a room at Sanjay & Harini Hostels.</p>
        </div>
    </div>

    <section class="panel-ivory" id="terms">
        <div class="wrap">
            <div class="terms-content reveal">
                <div class="terms-intro">
                    <p class="last-updated"><strong>Last Updated:</strong> {{ now()->format('d F, Y') }}</p>
                    <p>These Terms and Conditions govern your use of this website and the purchase of products or services offered herein. By accessing or using this website, you agree to be bound by these terms. Please read them carefully.</p>
                </div>

                <!-- Section 1: General Use -->
                <div class="terms-section">
                    <h2>1. General Use</h2>
                    <ul>
                        <li>By using this website, you confirm that you are at least 18 years old or are using the website under the supervision of a parent or legal guardian.</li>
                        <li>All content on this website is for informational purposes only and is subject to change without notice.</li>
                    </ul>
                </div>

                <!-- Section 2: User Responsibilities -->
                <div class="terms-section">
                    <h2>2. User Responsibilities</h2>
                    <ul>
                        <li>Users agree not to misuse the website by knowingly introducing viruses, trojans, or other malicious material.</li>
                        <li>You must not attempt to gain unauthorized access to the server, database, or any part of the site.</li>
                        <li>You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account.</li>
                    </ul>
                </div>

                <!-- Section 3: Product & Service Descriptions -->
                <div class="terms-section">
                    <h2>3. Product & Service Descriptions</h2>
                    <ul>
                        <li>All efforts are made to ensure accuracy in product descriptions, images, pricing, and availability.</li>
                        <li>However, we do not warrant that product descriptions or other content are complete, current, or error-free.</li>
                        <li>Room availability and pricing are subject to change without prior notice.</li>
                    </ul>
                </div>

                <!-- Section 4: Booking & Cancellation -->
                <div class="terms-section">
                    <h2>4. Booking & Cancellation Policy</h2>
                    <ul>
                        <li>Placing a booking request on this website does not constitute a confirmed booking. We reserve the right to refuse or cancel any booking for reasons including but not limited to room availability, pricing errors, or suspected fraud.</li>
                        <li>Cancellation policies vary by booking type. Please check with our team at the time of booking.</li>
                        <li>Security deposits are refundable subject to the terms of the rental agreement.</li>
                    </ul>
                </div>

                <!-- Section 5: Pricing and Payment -->
                <div class="terms-section">
                    <h2>5. Pricing and Payment</h2>
                    <ul>
                        <li>All prices are displayed in Indian Rupees (INR) and are inclusive or exclusive of taxes as indicated.</li>
                        <li>Payments must be made through secure and approved payment gateways. The website is not liable for any payment gateway errors.</li>
                        <li>Monthly rent includes the amenities as described on the rooms page. Additional services may incur extra charges.</li>
                    </ul>
                </div>

                <!-- Section 6: Intellectual Property -->
                <div class="terms-section">
                    <h2>6. Intellectual Property</h2>
                    <ul>
                        <li>All text, graphics, logos, images, and other materials on this website are the intellectual property of their respective owners and protected by copyright and trademark laws.</li>
                        <li>Unauthorized use or duplication of any materials is prohibited.</li>
                        <li>You may not reproduce, distribute, or create derivative works from any content on this site without prior written permission.</li>
                    </ul>
                </div>

                <!-- Section 7: Limitation of Liability -->
                <div class="terms-section">
                    <h2>7. Limitation of Liability</h2>
                    <ul>
                        <li>We are not responsible for any indirect or consequential damages that may arise from the use or inability to use the website or the products purchased through it.</li>
                        <li>Liability is limited to the value of the product purchased, if applicable.</li>
                        <li>We are not liable for any loss or damage caused by viruses, hacking, or other malicious activity.</li>
                    </ul>
                </div>

                <!-- Section 8: Modifications to Terms -->
                <div class="terms-section">
                    <h2>8. Modifications to Terms</h2>
                    <ul>
                        <li>These terms may be revised at any time without prior notice. Continued use of the site after changes implies acceptance of those changes.</li>
                        <li>It is your responsibility to review these terms periodically for updates.</li>
                    </ul>
                </div>

                <!-- Section 9: Governing Law -->
                <div class="terms-section">
                    <h2>9. Governing Law</h2>
                    <ul>
                        <li>These terms shall be governed by and construed in accordance with the laws of India.</li>
                        <li>Any disputes arising from these terms shall be subject to the exclusive jurisdiction of the courts in Chennai, Tamil Nadu.</li>
                    </ul>
                </div>

                <!-- Section 10: Contact Information -->
                <div class="terms-section contact-section">
                    <h2>10. Contact Us</h2>
                    <p>If you have any questions about these Terms & Conditions, please contact us:</p>
                    <div class="contact-details">
                        <p><strong>Sanjay Boys Hostel</strong><br>
                        📍 Alandur, St. Thomas Mount, Perungalathur, Chennai<br>
                        📞 +91 98765 43210<br>
                        📧 sanjayboys@hostel.in</p>
                        <p><strong>Harini Girls Hostel</strong><br>
                        📍 Alandur, St. Thomas Mount, Chennai<br>
                        📞 +91 98765 43211<br>
                        📧 harinigirls@hostel.in</p>
                    </div>
                </div>

                <!-- Acceptance Footer -->
                <div class="terms-footer">
                    <p>By using this website, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
                    <p class="business-name"><strong>Business Name:</strong> Sanjay & Harini Hostels</p>
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