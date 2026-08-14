@extends('layouts.app') {{-- or your main layout file --}}

@section('title', 'Privacy Policy | Sanjay & Harini Hostels')

@section('meta_description', 'Privacy Policy of Sanjay & Harini Hostels. Learn how we collect, use, and protect your personal information when you use our website and services.')

@section('canonical', route('privacy'))

@section('content')
<section class="page-hero" style="padding-top: 140px;">
    <div class="wrap">
        <span class="eyebrow">Legal</span>
        <h1>Privacy Policy</h1>
        <p>Your privacy matters to us. This policy explains how we collect, use, and protect your personal information.</p>
        <p style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--stone); margin-top: 8px;">
            Last updated: {{ date('F d, Y') }}
        </p>
    </div>
</section>

<section style="padding-top: 20px;">
    <div class="wrap">
        <div class="form-shell" style="max-width: 880px; margin: 0 auto;">

            <h3 style="font-size: 1.2rem;">1. Information We Collect</h3>
            <p style="color: var(--stone); margin-bottom: 24px;">
                We collect information you provide directly to us, such as when you:
            </p>
            <ul style="list-style: disc; padding-left: 24px; color: var(--stone); margin-bottom: 28px; line-height: 2;">
                <li>Fill out the contact or booking form (name, email, phone number, message)</li>
                <li>Call or message us directly</li>
                <li>Subscribe to our newsletter or updates</li>
                <li>Interact with our website (cookies, IP address, browser type, pages visited)</li>
            </ul>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">2. How We Use Your Information</h3>
            <ul style="list-style: disc; padding-left: 24px; color: var(--stone); margin-bottom: 28px; line-height: 2;">
                <li>To respond to your inquiries and booking requests</li>
                <li>To process and confirm your accommodation bookings</li>
                <li>To improve our website and services</li>
                <li>To send you updates, offers, or important notices (only with your consent)</li>
                <li>To comply with legal obligations</li>
            </ul>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">3. Sharing Your Information</h3>
            <p style="color: var(--stone); margin-bottom: 24px;">
                We do not sell, rent, or trade your personal information. We may share your data only:
            </p>
            <ul style="list-style: disc; padding-left: 24px; color: var(--stone); margin-bottom: 28px; line-height: 2;">
                <li>With trusted service providers who help us operate our website and services (e.g., hosting, email delivery)</li>
                <li>When required by law or to protect our legal rights</li>
                <li>With your explicit consent</li>
            </ul>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">4. Data Security</h3>
            <p style="color: var(--stone); margin-bottom: 24px;">
                We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.
            </p>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">5. Cookies</h3>
            <p style="color: var(--stone); margin-bottom: 24px;">
                Our website uses cookies to enhance your browsing experience. Cookies are small text files stored on your device. You can control or disable cookies in your browser settings, but this may affect some functionality of our website.
            </p>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">6. Your Rights</h3>
            <p style="color: var(--stone); margin-bottom: 12px;">
                You have the right to:
            </p>
            <ul style="list-style: disc; padding-left: 24px; color: var(--stone); margin-bottom: 28px; line-height: 2;">
                <li>Access, update, or delete your personal data</li>
                <li>Withdraw consent at any time</li>
                <li>Object to the processing of your data</li>
                <li>Request a copy of your data in a structured format</li>
            </ul>
            <p style="color: var(--stone);">
                To exercise these rights, please contact us using the details below.
            </p>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">7. Children's Privacy</h3>
            <p style="color: var(--stone); margin-bottom: 24px;">
                Our services are not directed to individuals under the age of 18. We do not knowingly collect personal information from minors. If you believe a minor has provided us with personal data, please contact us immediately.
            </p>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">8. Third-Party Links</h3>
            <p style="color: var(--stone); margin-bottom: 24px;">
                Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of such websites. We encourage you to read the privacy policies of any linked sites.
            </p>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">9. Changes to This Policy</h3>
            <p style="color: var(--stone); margin-bottom: 24px;">
                We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page with an updated date. We encourage you to review this policy periodically.
            </p>

            <hr style="border: none; border-top: 1.5px solid var(--line); margin: 28px 0;">

            <h3 style="font-size: 1.2rem;">10. Contact Us</h3>
            <p style="color: var(--stone); margin-bottom: 8px;">
                If you have any questions, concerns, or requests regarding this Privacy Policy, please contact us:
            </p>
            <ul style="list-style: none; color: var(--stone); margin: 16px 0 0; line-height: 2;">
                <li>📞 <strong>+91 98765 43210</strong></li>
                <li>📧 <strong>info@sanjayandharinihostels.com</strong></li>
                <li>📍 <strong>Alandur, St. Thomas Mount, Perungalathur, Chennai</strong></li>
            </ul>
        </div>
    </div>
</section>

<!-- Back to Home -->
<section style="padding: 20px 0 60px;">
    <div class="wrap" style="text-align: center;">
        <a href="{{ route('home') }}" class="btn btn-ghost" style="border-color: var(--line);">
            ← Back to Home
        </a>
    </div>
</section>
@endsection