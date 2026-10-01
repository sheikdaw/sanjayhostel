@extends('layouts.frontend')

@section('title', 'Terms & Conditions | Sanjay & Harini Hostels, Chennai')
@section('canonical', \App\Support\Seo::url('/terms'))
@section('meta_description', 'Read the Terms and Conditions for Sanjay & Harini Hostels in Alandur and Perungalathur, Chennai: booking, payments, deposits, user responsibilities and governing law.')

@section('content')
<div class="page-hero panel-ivory">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => [['Home', '/'], ['Terms & conditions', '/terms']]])
            <span class="eyebrow">Legal</span>
            <h1>Terms & Conditions</h1>
            <p>Please read these terms carefully before using our website or booking a room at Sanjay & Harini Hostels.</p>
        </div>
    </div>

    <section id="terms">
        <div class="wrap">
            <div class="terms-content reveal">
                <div class="terms-intro">
                    @if (config('hostel.policy_updated'))<p class="last-updated"><strong>Last updated:</strong> {{ config('hostel.policy_updated') }}</p>@endif
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
                        <p><strong>Sanjay &amp; Harini Hostels</strong><br>
                        📍 Alandur and Perungalathur, Chennai<br>
                        📞 {{ config('hostel.phone_display') }}<br>
                        📧 {{ config('hostel.email') }}</p>
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

    <div class="back-top"><a href="#top">Back to top</a></div>
@endsection
