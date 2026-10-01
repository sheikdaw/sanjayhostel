<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F2B46">

    <title>@yield('title', 'Sanjay & Harini Hostels | Best PG in Alandur, Chennai')</title>

    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    {{-- Open Graph / Twitter — only rendered when the page defines og_title --}}
    @hasSection('og_title')
        <meta property="og:title" content="@yield('og_title')">
        <meta property="og:description" content="@yield('og_description')">
        <meta property="og:url" content="@yield('og_url')">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:image" content="@yield('og_image')">
        <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
        <meta name="twitter:title" content="@yield('twitter_title')">
        <meta name="twitter:description" content="@yield('twitter_description')">
        <meta name="twitter:image" content="@yield('twitter_image')">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Site stylesheet — external file, cache-busted on every deploy --}}
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    @stack('styles')

    @hasSection('schema')
        @yield('schema')
    @endif
</head>

<body id="top">

    <a class="skip" href="#main">Skip to content</a>

    {{-- Icon sprite: <svg class="i"><use href="#i-check"/></svg> --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
        <symbol id="i-phone" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></symbol>
        <symbol id="i-chat" viewBox="0 0 24 24"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></symbol>
        <symbol id="i-pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></symbol>
        <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6z"/><path d="m9 12 2 2 4-4"/></symbol>
        <symbol id="i-wifi" viewBox="0 0 24 24"><path d="M12 20h.01M2 8.8a15 15 0 0 1 20 0M5 12.9a10 10 0 0 1 14 0M8.5 16.4a5 5 0 0 1 7 0"/></symbol>
        <symbol id="i-food" viewBox="0 0 24 24"><path d="M3 2v7a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V2M7 2v20M21 15V2a5 5 0 0 0-5 5v6a2 2 0 0 0 2 2h3Zm0 0v7"/></symbol>
        <symbol id="i-gym" viewBox="0 0 24 24"><path d="M6 7v10M18 7v10M3 10v4M21 10v4M6 12h12"/></symbol>
        <symbol id="i-train" viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="14" rx="3"/><path d="M5 11h14M9 21l2-4M15 21l-2-4M9 14h.01M15 14h.01"/></symbol>
        <symbol id="i-bed" viewBox="0 0 24 24"><path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8M2 17h20M6 10V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v4"/></symbol>
        <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
        <symbol id="i-snow" viewBox="0 0 24 24"><path d="M12 2v20M4.9 7l14.2 10M19.1 7 4.9 17"/></symbol>
    </svg>

    <div class="topbar">
        <div class="wrap">
            <span>Boys &amp; girls PG in Alandur, St. Thomas Mount and Perungalathur, Chennai</span>
            <span class="topbar-links">
                <a href="tel:{{ config('hostel.phone') }}"><svg class="i"><use href="#i-phone"/></svg> {{ config('hostel.phone_display') }}</a>
                <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> WhatsApp</a>
            </span>
        </div>
    </div>

    <header class="site-nav">
        <div class="wrap">
            <a class="brand" href="{{ route('home') }}" aria-label="Sanjay and Harini Hostels home">
                <svg class="brand-mark" viewBox="0 0 36 36" aria-hidden="true">
                    <circle cx="14" cy="18" r="11" fill="#1F5FA8"/>
                    <circle cx="23" cy="18" r="11" fill="#B0356A" fill-opacity=".88"/>
                </svg>
                <span>Sanjay &amp; Harini<small>PG hostels, Chennai</small></span>
            </a>
            <button class="nav-toggle" aria-label="Open menu" aria-expanded="false">
                <span class="toggle-bar"></span><span class="toggle-bar"></span><span class="toggle-bar"></span>
            </button>
            <nav class="nav-links" aria-label="Main">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('hostels.index') }}" class="{{ request()->routeIs('hostels.*') ? 'active' : '' }}">Our hostels</a>
                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms') ? 'active' : '' }}">Rooms &amp; pricing</a>
                @if (Route::has('gallery'))
                    <a href="{{ route('gallery') }}" class="{{ request()->routeIs('gallery') ? 'active' : '' }}">Gallery</a>
                @endif
                <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                <a href="tel:{{ config('hostel.phone') }}" class="nav-phone"><svg class="i"><use href="#i-phone"/></svg> Call now</a>
                <a href="{{ route('contact') }}" class="cta">Book a visit</a>
            </nav>
        </div>
    </header>

    @if (session('success') || session('error'))
        <div class="wrap flash">
            @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif
        </div>
    @endif

    <main id="main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="wrap">
            <div class="foot-grid">
                <div>
                    <div class="brand">
                        <svg class="brand-mark" viewBox="0 0 36 36" aria-hidden="true">
                            <circle cx="14" cy="18" r="11" fill="#4B8FE0"/>
                            <circle cx="23" cy="18" r="11" fill="#D95C92" fill-opacity=".85"/>
                        </svg>
                        <span>Sanjay &amp; Harini<small>PG hostels, Chennai</small></span>
                    </div>
                    <p>Safe, affordable PG accommodation for men and women in Alandur, St. Thomas Mount and Perungalathur, Chennai.</p>
                </div>
                <div>
                    <h5>Explore</h5>
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('hostels.index') }}">Our hostels</a></li>
                        <li><a href="{{ route('hostels.alandur') }}">PG in Alandur</a></li>
                        <li><a href="{{ route('hostels.perungalathur') }}">PG in Perungalathur</a></li>
                        <li><a href="{{ route('rooms') }}">Rooms &amp; pricing</a></li>
                        @if (Route::has('gallery'))<li><a href="{{ route('gallery') }}">Gallery</a></li>@endif
                        <li><a href="{{ route('about') }}">About</a></li>
                        <li><a href="{{ route('contact') }}">Contact / Book now</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Policies</h5>
                    <ul>
                        <li><a href="{{ route('terms') }}">Terms &amp; conditions</a></li>
                        @if (Route::has('refund.policy'))<li><a href="{{ route('refund.policy') }}">Refund policy</a></li>@endif
                        <li><a href="{{ route('privacy') }}">Privacy policy</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Contact</h5>
                    <ul>
                        <li><a href="tel:{{ config('hostel.phone') }}">{{ config('hostel.phone_display') }}</a></li>
                        <li><a href="mailto:{{ config('hostel.email') }}">{{ config('hostel.email') }}</a></li>
                        <li>Alandur, St. Thomas Mount, Perungalathur</li>
                    </ul>
                </div>
            </div>
            <div class="foot-bottom">
                <span>&copy; {{ date('Y') }} {{ config('hostel.name') }}. All rights reserved.</span>
                <span>developed by <span style="color:#D95C92;">♥</span> sheik</span>
            </div>
        </div>
    </footer>

    <div class="mobile-cta">
        <a href="tel:{{ config('hostel.phone') }}"><svg class="i"><use href="#i-phone"/></svg> Call</a>
        <a class="wa" href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> WhatsApp</a>
        <a class="book" href="{{ route('contact') }}">Book a visit</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile menu
            var toggle = document.querySelector('.nav-toggle');
            var menu = document.querySelector('.nav-links');
            if (toggle && menu) {
                toggle.addEventListener('click', function () {
                    var open = menu.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            }

            // FAQ accordion
            document.querySelectorAll('.faq-q').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var item = this.closest('.faq-item');
                    var open = item.classList.toggle('open');
                    this.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });

            // Facility tabs
            document.querySelectorAll('.tab-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
                    var target = document.getElementById(this.dataset.tab);
                    if (target) target.classList.add('active');
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
