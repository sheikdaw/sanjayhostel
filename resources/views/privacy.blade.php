@extends('layouts.frontend')

@section('title', 'Privacy Policy | Sanjay & Harini Hostels')
@section('meta_description', 'Privacy Policy of Sanjay & Harini Hostels. Learn how we collect, use, and protect your personal information when you use our website and services.')
@section('canonical', route('privacy'))

@section('content')
@php
    $sections = [
        ['id' => 'collection', 'title' => 'Information we collect', 'nav' => 'Collection',
         'content' => 'We collect information you provide directly to us, such as when you:',
         'items' => [
             'Fill out the contact or booking form (name, email, phone number, message)',
             'Call or message us directly',
             'Subscribe to our newsletter or updates',
             'Interact with our website (cookies, IP address, browser type, pages visited)',
         ]],
        ['id' => 'usage', 'title' => 'How we use your information', 'nav' => 'Usage',
         'content' => 'We use your information to:',
         'items' => [
             'Respond to your inquiries and booking requests',
             'Process and confirm your accommodation bookings',
             'Improve our website and services',
             'Send you updates, offers, or important notices (only with your consent)',
             'Comply with legal obligations',
         ]],
        ['id' => 'sharing', 'title' => 'Sharing your information', 'nav' => 'Sharing',
         'content' => 'We do not sell, rent, or trade your personal information. We may share your data only:',
         'items' => [
             'With trusted service providers who help us operate our website and services (for example, hosting and email delivery)',
             'When required by law or to protect our legal rights',
             'With your explicit consent',
         ]],
        ['id' => 'security', 'title' => 'Data security', 'nav' => 'Security',
         'content' => 'We use appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction. No method of transmission over the internet is 100% secure, so we cannot guarantee absolute security.',
         'items' => []],
        ['id' => 'cookies', 'title' => 'Cookies', 'nav' => 'Cookies',
         'content' => 'Our website uses cookies to improve your browsing experience. Cookies are small text files stored on your device. You can control or disable cookies in your browser settings, but some parts of the website may stop working as expected.',
         'items' => []],
        ['id' => 'rights', 'title' => 'Your rights', 'nav' => 'Your rights',
         'content' => 'You have the right to:',
         'items' => [
             'Access, update, or delete your personal data',
             'Withdraw consent at any time',
             'Object to the processing of your data',
             'Request a copy of your data in a structured format',
         ]],
        ['id' => 'children', 'title' => "Children's privacy", 'nav' => 'Children',
         'content' => 'Our services are not directed to individuals under the age of 18. We do not knowingly collect personal information from minors. If you believe a minor has given us personal data, please contact us immediately.',
         'items' => []],
        ['id' => 'links', 'title' => 'Third-party links', 'nav' => 'Third-party links',
         'content' => 'Our website may link to third-party websites. We are not responsible for their privacy practices or content, and we encourage you to read their privacy policies.',
         'items' => []],
        ['id' => 'changes', 'title' => 'Changes to this policy', 'nav' => 'Changes',
         'content' => 'We may update this Privacy Policy from time to time. Changes are posted on this page with an updated date. Please review the policy periodically.',
         'items' => []],
    ];
@endphp

    <div class="page-hero">
        <div class="wrap">
            <span class="eyebrow">Legal</span>
            <h1>Privacy Policy</h1>
            <p>Your privacy matters to us. This policy explains how we collect, use and protect your personal information.</p>
        </div>
    </div>

    <section id="privacy">
        <div class="wrap">
            <nav class="legal-nav" aria-label="Sections">
                @foreach ($sections as $s)
                    <a class="nearby-chip" href="#{{ $s['id'] }}">{{ $s['nav'] }}</a>
                @endforeach
                <a class="nearby-chip" href="#contact-us">Contact</a>
            </nav>

            <div class="terms-content">
                <div class="terms-intro">
                    <p class="last-updated"><strong>Last updated:</strong> {{ now()->format('d F, Y') }}</p>
                    <p>This policy applies to information collected through this website and when you contact Sanjay &amp; Harini Hostels about a room or lunch box service.</p>
                </div>

                @foreach ($sections as $s)
                    <div class="terms-section" id="{{ $s['id'] }}">
                        <h2>{{ $loop->iteration }}. {{ $s['title'] }}</h2>
                        <p>{{ $s['content'] }}</p>
                        @if (!empty($s['items']))
                            <ul>
                                @foreach ($s['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach

                <div class="terms-section contact-section" id="contact-us">
                    <h2>{{ count($sections) + 1 }}. Contact us</h2>
                    <p>If you have questions or requests about this Privacy Policy, contact us:</p>
                    <div class="contact-details">
                        <p><strong>Phone</strong>+91 9043093470</p>
                        <p><strong>Email</strong>info@sanjayandharinihostels.com</p>
                        <p><strong>Location</strong>Alandur, Chennai</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="back-top"><a href="#top">Back to top</a></div>
@endsection
