@extends('layouts.app')

@section('title', 'Privacy Policy | Sanjay & Harini Hostels')

@section('meta_description', 'Privacy Policy of Sanjay & Harini Hostels. Learn how we collect, use, and protect your personal information when you use our website and services.')

@section('canonical', route('privacy'))

@section('content')
<!-- Hero Section with Decorative Accent -->
<section class="page-hero" style="padding-top: 140px; position: relative; overflow: hidden;">
    <div class="wrap" style="position: relative; z-index: 2;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <span style="display: inline-block; width: 40px; height: 4px; background: linear-gradient(90deg, var(--amber), var(--rose)); border-radius: 4px;"></span>
            <span class="eyebrow" style="margin-bottom: 0;">Legal</span>
        </div>
        <h1 style="font-size: clamp(2.4rem, 4.5vw, 3.2rem); margin-bottom: 16px;">
            Privacy <span style="background: linear-gradient(100deg, var(--amber) 10%, var(--rose) 90%); -webkit-background-clip: text; background-clip: text; color: transparent;">Policy</span>
        </h1>
        <p style="color: var(--stone); font-size: 1.1rem; max-width: 640px; line-height: 1.7;">
            Your privacy matters to us. This policy explains how we collect, use, and protect your personal information.
        </p>
        <div style="display: flex; align-items: center; gap: 20px; margin-top: 18px; flex-wrap: wrap;">
            <span style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--stone); background: var(--cream); padding: 6px 18px; border-radius: 999px; border: 1px solid var(--line);">
                📅 Last updated: {{ date('F d, Y') }}
            </span>
            <span style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--stone); background: var(--cream); padding: 6px 18px; border-radius: 999px; border: 1px solid var(--line);">
                ⏱️ 5 min read
            </span>
        </div>
    </div>
    <!-- Decorative Background Blob -->
    <div style="position: absolute; top: -20%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(184, 117, 46, 0.08), transparent 70%); border-radius: 50%; z-index: 0; pointer-events: none;"></div>
    <div style="position: absolute; bottom: -30%; left: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(168, 59, 82, 0.06), transparent 70%); border-radius: 50%; z-index: 0; pointer-events: none;"></div>
</section>

<!-- Main Content -->
<section style="padding-top: 20px; padding-bottom: 60px;">
    <div class="wrap">
        <!-- Quick Navigation Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 40px; max-width: 880px; margin-left: auto; margin-right: auto;">
            @foreach([
                ['#section-1', '📋', 'Collection'],
                ['#section-2', '⚡', 'Usage'],
                ['#section-3', '🔗', 'Sharing'],
                ['#section-4', '🔒', 'Security'],
                ['#section-5', '🍪', 'Cookies'],
                ['#section-6', '👤', 'Your Rights'],
                ['#section-7', '🧒', 'Children'],
                ['#section-8', '🔗', 'Third-Party'],
                ['#section-9', '📝', 'Changes'],
                ['#section-10', '📞', 'Contact']
            ] as $item)
                <a href="{{ $item[0] }}" style="display: flex; align-items: center; gap: 8px; background: var(--cream); border: 1px solid var(--line); border-radius: var(--radius); padding: 12px 14px; font-family: var(--font-mono); font-size: 0.75rem; font-weight: 600; color: var(--stone); transition: all 0.25s ease; text-align: center; justify-content: center;"
                   onmouseover="this.style.borderColor='var(--amber)'; this.style.color='var(--ink)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--shadow)'"
                   onmouseout="this.style.borderColor='var(--line)'; this.style.color='var(--stone)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span style="font-size: 1rem;">{{ $item[1] }}</span>
                    {{ $item[2] }}
                </a>
            @endforeach
        </div>

        <!-- Policy Content -->
        <div class="form-shell" style="max-width: 880px; margin: 0 auto; padding: 44px 48px; border-radius: var(--radius-lg);">

            @php
                $sections = [
                    ['id' => 'section-1', 'num' => '1', 'title' => 'Information We Collect', 'icon' => '📋',
                     'content' => 'We collect information you provide directly to us, such as when you:',
                     'items' => [
                         'Fill out the contact or booking form (name, email, phone number, message)',
                         'Call or message us directly',
                         'Subscribe to our newsletter or updates',
                         'Interact with our website (cookies, IP address, browser type, pages visited)'
                     ]],
                    ['id' => 'section-2', 'num' => '2', 'title' => 'How We Use Your Information', 'icon' => '⚡',
                     'content' => 'We use your information to:',
                     'items' => [
                         'Respond to your inquiries and booking requests',
                         'Process and confirm your accommodation bookings',
                         'Improve our website and services',
                         'Send you updates, offers, or important notices (only with your consent)',
                         'Comply with legal obligations'
                     ]],
                    ['id' => 'section-3', 'num' => '3', 'title' => 'Sharing Your Information', 'icon' => '🔗',
                     'content' => 'We do not sell, rent, or trade your personal information. We may share your data only:',
                     'items' => [
                         'With trusted service providers who help us operate our website and services (e.g., hosting, email delivery)',
                         'When required by law or to protect our legal rights',
                         'With your explicit consent'
                     ]],
                    ['id' => 'section-4', 'num' => '4', 'title' => 'Data Security', 'icon' => '🔒',
                     'content' => 'We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.',
                     'items' => []],
                    ['id' => 'section-5', 'num' => '5', 'title' => 'Cookies', 'icon' => '🍪',
                     'content' => 'Our website uses cookies to enhance your browsing experience. Cookies are small text files stored on your device. You can control or disable cookies in your browser settings, but this may affect some functionality of our website.',
                     'items' => []],
                    ['id' => 'section-6', 'num' => '6', 'title' => 'Your Rights', 'icon' => '👤',
                     'content' => 'You have the right to:',
                     'items' => [
                         'Access, update, or delete your personal data',
                         'Withdraw consent at any time',
                         'Object to the processing of your data',
                         'Request a copy of your data in a structured format'
                     ]],
                    ['id' => 'section-7', 'num' => '7', 'title' => "Children's Privacy", 'icon' => '🧒',
                     'content' => 'Our services are not directed to individuals under the age of 18. We do not knowingly collect personal information from minors. If you believe a minor has provided us with personal data, please contact us immediately.',
                     'items' => []],
                    ['id' => 'section-8', 'num' => '8', 'title' => 'Third-Party Links', 'icon' => '🔗',
                     'content' => 'Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of such websites. We encourage you to read the privacy policies of any linked sites.',
                     'items' => []],
                    ['id' => 'section-9', 'num' => '9', 'title' => 'Changes to This Policy', 'icon' => '📝',
                     'content' => 'We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page with an updated date. We encourage you to review this policy periodically.',
                     'items' => []],
                    ['id' => 'section-10', 'num' => '10', 'title' => 'Contact Us', 'icon' => '📞',
                     'content' => 'If you have any questions, concerns, or requests regarding this Privacy Policy, please contact us:',
                     'items' => []]
                ];
            @endphp

            @foreach($sections as $section)
                <div id="{{ $section['id'] }}" style="scroll-margin-top: 100px; margin-bottom: 36px; {{ !$loop->last ? 'border-bottom: 1.5px solid var(--line); padding-bottom: 36px;' : '' }}">
                    <!-- Section Header with Icon -->
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; background: linear-gradient(135deg, var(--amber-tint), var(--rose-tint)); border-radius: 12px; font-size: 1.3rem; flex-shrink: 0;">
                            {{ $section['icon'] }}
                        </span>
                        <div>
                            <span style="font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--stone);">Section {{ $section['num'] }}</span>
                            <h3 style="font-size: 1.25rem; margin: 0; font-weight: 700;">{{ $section['title'] }}</h3>
                        </div>
                    </div>

                    <p style="color: var(--stone); margin-bottom: {{ !empty($section['items']) ? '16px' : '0' }}; line-height: 1.7; padding-left: 58px;">
                        {{ $section['content'] }}
                    </p>

                    @if(!empty($section['items']))
                        <ul style="list-style: none; padding-left: 58px; color: var(--ink); margin-bottom: 0;">
                            @foreach($section['items'] as $item)
                                <li style="display: flex; align-items: flex-start; gap: 12px; padding: 6px 0; line-height: 1.6;">
                                    <span style="color: var(--amber); font-weight: 700; font-size: 1.1rem; flex-shrink: 0; margin-top: 2px;">✦</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            <!-- Contact Cards (Section 10 enhanced) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-top: 8px; padding-left: 58px;">
                <div style="background: var(--ivory); border-radius: var(--radius); padding: 16px 18px; border-left: 4px solid var(--amber);">
                    <div style="font-size: 0.75rem; color: var(--stone); font-family: var(--font-mono); letter-spacing: 0.04em;">Phone</div>
                    <div style="font-weight: 600; font-size: 0.98rem;">+91 98765 43210</div>
                </div>
                <div style="background: var(--ivory); border-radius: var(--radius); padding: 16px 18px; border-left: 4px solid var(--rose);">
                    <div style="font-size: 0.75rem; color: var(--stone); font-family: var(--font-mono); letter-spacing: 0.04em;">Email</div>
                    <div style="font-weight: 600; font-size: 0.98rem;">info@sanjayandharinihostels.com</div>
                </div>
                <div style="background: var(--ivory); border-radius: var(--radius); padding: 16px 18px; border-left: 4px solid var(--amber-deep);">
                    <div style="font-size: 0.75rem; color: var(--stone); font-family: var(--font-mono); letter-spacing: 0.04em;">Location</div>
                    <div style="font-weight: 600; font-size: 0.98rem;">Alandur, Chennai</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Back to Home -->
<section style="padding: 0 0 60px;">
    <div class="wrap" style="text-align: center;">
        <a href="{{ route('home') }}" class="btn btn-ghost" style="border-color: var(--line); transition: all 0.3s ease;"
           onmouseover="this.style.borderColor='var(--amber)'; this.style.color='var(--amber-deep)'"
           onmouseout="this.style.borderColor='var(--line)'; this.style.color='var(--ink)'">
            <span style="display: inline-block; transition: transform 0.3s ease;">←</span> Back to Home
        </a>
    </div>
</section>
@endsection
