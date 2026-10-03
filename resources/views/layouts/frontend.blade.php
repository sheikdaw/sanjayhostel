<!DOCTYPE html>
<html lang="en-IN">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="theme-color" content="#0F2B46">

    {{-- SEO TITLE --}}
    <title>@yield('title', 'Boys & Girls PG in Alandur & Perungalathur, Chennai | Sanjay & Harini Hostels')</title>

    {{-- META DESCRIPTION --}}
    <meta name="description"
          content="@yield('meta_description', 'Looking for PG in Alandur or Perungalathur? Sanjay & Harini Hostels offers boys & girls PG near Alandur Metro with AC rooms, food, WiFi, CCTV & gym. Monthly rent from ₹7,250. Call now for availability.')">

    {{-- META KEYWORDS (still used by some local search engines) --}}
    <meta name="keywords"
          content="@yield('meta_keywords', 'PG in Alandur, PG in Perungalathur, boys PG Chennai, girls PG Chennai, working women hostel Chennai, PG near Alandur Metro, PG with food Chennai, AC PG rooms Alandur, hostel with gym Chennai, monthly PG Chennai')">

    {{-- ROBOTS --}}
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">

    {{-- CANONICAL --}}
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif

    {{-- GEO TAGS FOR LOCAL SEO --}}
    <meta name="geo.region" content="IN-TN">
    <meta name="geo.placename" content="Alandur, Chennai">
    <meta name="geo.position" content="13.0045;80.2015">
    <meta name="ICBM" content="13.0045, 80.2015">

    {{-- OPEN GRAPH --}}
    @hasSection('og_title')
        <meta property="og:title" content="@yield('og_title')">
        <meta property="og:description" content="@yield('og_description')">
        <meta property="og:url" content="@yield('og_url', url()->current())">
        <meta property="og:type" content="@yield('og_type', 'website')">

        @hasSection('og_image')
            <meta property="og:image" content="@yield('og_image')">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:alt" content="@yield('og_title')">
        @endif

        <meta property="og:site_name" content="Sanjay & Harini Hostels">
        <meta property="og:locale" content="en_IN">
    @endif

    {{-- TWITTER --}}
    <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">

    @hasSection('twitter_title')
        <meta name="twitter:title" content="@yield('twitter_title')">
    @else
        <meta name="twitter:title" content="@yield('title')">
    @endif

    @hasSection('twitter_description')
        <meta name="twitter:description" content="@yield('twitter_description')">
    @else
        <meta name="twitter:description" content="@yield('meta_description')">
    @endif

    @hasSection('twitter_image')
        <meta name="twitter:image" content="@yield('twitter_image')">
    @endif

    {{-- FAVICON --}}
    @if (file_exists(public_path('favicon.ico')))
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- PRECONNECT --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- FONTS --}}
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Instrument+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    {{-- SITE CSS --}}
    <link rel="stylesheet"
          href="{{ asset('css/site.css') }}?v={{ file_exists(public_path('css/site.css')) ? filemtime(public_path('css/site.css')) : time() }}">

    @stack('styles')

    {{-- PAGE SCHEMA --}}
    @hasSection('schema')
        @yield('schema')
    @endif

</head>

<body id="top">

    {{-- SKIP LINK --}}
    <a class="skip" href="#main">
        Skip to content
    </a>

    {{-- ICON SPRITE --}}
    <svg width="0"
         height="0"
         style="position:absolute;overflow:hidden"
         aria-hidden="true"
         focusable="false">

        <symbol id="i-check" viewBox="0 0 24 24">
            <path d="M20 6 9 17l-5-5"/>
        </symbol>

        <symbol id="i-phone" viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2
            19.8 19.8 0 0 1-8.63-3.07
            19.5 19.5 0 0 1-6-6
            19.8 19.8 0 0 1-3.07-8.67
            A2 2 0 0 1 4.11 2h3
            a2 2 0 0 1 2 1.72
            c.13.96.36 1.9.7 2.81
            a2 2 0 0 1-.45 2.11
            L8.09 9.91
            a16 16 0 0 0 6 6
            l1.27-1.27
            a2 2 0 0 1 2.11-.45
            c.91.34 1.85.57 2.81.7
            A2 2 0 0 1 22 16.92z"/>
        </symbol>

        <symbol id="i-chat" viewBox="0 0 24 24">
            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>
        </symbol>

        <symbol id="i-pin" viewBox="0 0 24 24">
            <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/>
            <circle cx="12" cy="10" r="3"/>
        </symbol>

        <symbol id="i-shield" viewBox="0 0 24 24">
            <path d="M12 3 5 6v6c0 4.5 3 7.5 7 9
                     4-1.5 7-4.5 7-9V6z"/>
            <path d="m9 12 2 2 4-4"/>
        </symbol>

        <symbol id="i-wifi" viewBox="0 0 24 24">
            <path d="M12 20h.01M2 8.8a15 15 0 0 1 20 0
                     M5 12.9a10 10 0 0 1 14 0
                     M8.5 16.4a5 5 0 0 1 7 0"/>
        </symbol>

        <symbol id="i-food" viewBox="0 0 24 24">
            <path d="M3 2v7a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V2
                     M7 2v20
                     M21 15V2a5 5 0 0 0-5 5v6
                     a2 2 0 0 0 2 2h3Zm0 0v7"/>
        </symbol>

        <symbol id="i-gym" viewBox="0 0 24 24">
            <path d="M6 7v10M18 7v10
                     M3 10v4M21 10v4
                     M6 12h12"/>
        </symbol>

        <symbol id="i-train" viewBox="0 0 24 24">
            <rect x="5" y="3" width="14" height="14" rx="3"/>
            <path d="M5 11h14M9 21l2-4M15 21l-2-4
                     M9 14h.01M15 14h.01"/>
        </symbol>

        <symbol id="i-bed" viewBox="0 0 24 24">
            <path d="M2 20v-8a2 2 0 0 1 2-2h16
                     a2 2 0 0 1 2 2v8
                     M2 17h20
                     M6 10V6a2 2 0 0 1 2-2h8
                     a2 2 0 0 1 2 2v4"/>
        </symbol>

        <symbol id="i-users" viewBox="0 0 24 24">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6
                     a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87
                     M16 3.13a4 4 0 0 1 0 7.75"/>
        </symbol>

        <symbol id="i-arrow" viewBox="0 0 24 24">
            <path d="M5 12h14M13 6l6 6-6 6"/>
        </symbol>

        <symbol id="i-snow" viewBox="0 0 24 24">
            <path d="M12 2v20M4.9 7l14.2 10
                     M19.1 7 4.9 17"/>
        </symbol>

        <symbol id="i-star" viewBox="0 0 24 24">
            <path d="M12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z"/>
        </symbol>

    </svg>


    {{-- TOP BAR --}}
    <div class="topbar">
        <div class="wrap">

            <span>
                Boys &amp; girls PG in Alandur &amp; Perungalathur, Chennai — Near Metro
            </span>

            <span class="topbar-links">

                <a href="tel:{{ config('hostel.phone') }}">
                    <svg class="i">
                        <use href="#i-phone"/>
                    </svg>

                    {{ config('hostel.phone_display') }}
                </a>

                <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability%20in%20Alandur%20%2F%20Perungalathur"
                   target="_blank"
                   rel="noopener noreferrer">

                    <svg class="i">
                        <use href="#i-chat"/>
                    </svg>

                    WhatsApp
                </a>

            </span>

        </div>
    </div>


    {{-- HEADER --}}
    <header class="site-nav">

        <div class="wrap">

            <a class="brand"
               href="{{ route('home') }}"
               aria-label="Sanjay and Harini Hostels — Boys and Girls PG in Chennai">

                <svg class="brand-mark"
                     viewBox="0 0 36 36"
                     aria-hidden="true"
                     focusable="false">

                    <circle cx="14"
                            cy="18"
                            r="11"
                            fill="#1F5FA8"/>

                    <circle cx="23"
                            cy="18"
                            r="11"
                            fill="#B0356A"
                            fill-opacity=".88"/>

                </svg>

                <span>
                    Sanjay &amp; Harini
                    <small>PG Hostels, Chennai</small>
                </span>

            </a>


            <button class="nav-toggle"
                    type="button"
                    aria-label="Open navigation menu"
                    aria-expanded="false">

                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>

            </button>


            <nav class="nav-links" aria-label="Main navigation">

                <a href="{{ route('home') }}"
                   class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    Home
                </a>

                <a href="{{ route('hostels.index') }}"
                   class="{{ request()->routeIs('hostels.*') ? 'active' : '' }}">
                    Our Hostels
                </a>

                <a href="{{ route('rooms') }}"
                   class="{{ request()->routeIs('rooms') ? 'active' : '' }}">
                    Rooms &amp; Pricing
                </a>

                @if (Route::has('gallery'))
                    <a href="{{ route('gallery') }}"
                       class="{{ request()->routeIs('gallery') ? 'active' : '' }}">
                        Gallery
                    </a>
                @endif

                <a href="{{ route('about') }}"
                   class="{{ request()->routeIs('about') ? 'active' : '' }}">
                    About
                </a>

                <a href="{{ route('contact') }}"
                   class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                    Contact
                </a>

                <a href="tel:{{ config('hostel.phone') }}"
                   class="nav-phone">

                    <svg class="i">
                        <use href="#i-phone"/>
                    </svg>

                    Call Now

                </a>

                <a href="{{ route('contact') }}"
                   class="cta">
                    Book a Visit
                </a>

            </nav>

        </div>

    </header>


    {{-- FLASH MESSAGES --}}
    @if (session('success') || session('error'))

        <div class="wrap flash">

            @if (session('success'))
                <div class="alert alert-success"
                     role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error"
                     role="alert">
                    {{ session('error') }}
                </div>
            @endif

        </div>

    @endif


    {{-- MAIN CONTENT --}}
    <main id="main">
        @yield('content')
    </main>


    {{-- FOOTER --}}
    <footer class="site-footer">

        <div class="wrap">

            <div class="foot-grid">

                {{-- BRAND --}}
                <div>

                    <div class="brand">

                        <svg class="brand-mark"
                             viewBox="0 0 36 36"
                             aria-hidden="true"
                             focusable="false">

                            <circle cx="14"
                                    cy="18"
                                    r="11"
                                    fill="#4B8FE0"/>

                            <circle cx="23"
                                    cy="18"
                                    r="11"
                                    fill="#D95C92"
                                    fill-opacity=".85"/>

                        </svg>

                        <span>
                            Sanjay &amp; Harini
                            <small>PG Hostels, Chennai</small>
                        </span>

                    </div>

                    <p>
                        Boys and girls PG accommodation in Alandur and
                        Perungalathur, Chennai with AC rooms, home-style
                        food, high-speed WiFi, CCTV security and free
                        gym access for residents.
                    </p>

                </div>


                {{-- EXPLORE --}}
                <div>

                    <h5>Explore</h5>

                    <ul>

                        <li>
                            <a href="{{ route('home') }}">
                                Home
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('hostels.index') }}">
                                Our Hostels
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('hostels.alandur') }}">
                                PG in Alandur
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('hostels.perungalathur') }}">
                                Boys Hostel in Perungalathur
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('rooms') }}">
                                Rooms &amp; Pricing
                            </a>
                        </li>

                        @if (Route::has('gallery'))
                            <li>
                                <a href="{{ route('gallery') }}">
                                    Gallery
                                </a>
                            </li>
                        @endif

                        <li>
                            <a href="{{ route('about') }}">
                                About Us
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('contact') }}">
                                Contact / Book Now
                            </a>
                        </li>

                    </ul>

                </div>


                {{-- POPULAR SEARCHES (SEO GOLD) --}}
                <div>

                    <h5>Popular PG Searches</h5>

                    <ul>

                        <li>
                            <a href="{{ route('hostels.alandur') }}">
                                PG near Alandur Metro
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('hostels.alandur') }}">
                                Working Women Hostel Chennai
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('hostels.alandur') }}">
                                Gents PG near Guindy
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('hostels.alandur') }}">
                                Ladies Hostel near St. Thomas Mount
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('hostels.perungalathur') }}">
                                Boys PG near Tambaram
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('rooms') }}">
                                AC PG Rooms in Chennai
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('rooms') }}">
                                Budget PG with Food
                            </a>
                        </li>

                    </ul>

                </div>


                {{-- POLICIES --}}
                <div>

                    <h5>Policies</h5>

                    <ul>

                        @if (Route::has('terms'))
                            <li>
                                <a href="{{ route('terms') }}">
                                    Terms &amp; Conditions
                                </a>
                            </li>
                        @endif

                        @if (Route::has('refund.policy'))
                            <li>
                                <a href="{{ route('refund.policy') }}">
                                    Refund Policy
                                </a>
                            </li>
                        @endif

                        @if (Route::has('privacy'))
                            <li>
                                <a href="{{ route('privacy') }}">
                                    Privacy Policy
                                </a>
                            </li>
                        @endif

                    </ul>


                    <h5 style="margin-top:24px;">Contact</h5>

                    <ul>

                        <li>
                            <a href="tel:{{ config('hostel.phone') }}">
                                {{ config('hostel.phone_display') }}
                            </a>
                        </li>

                        <li>
                            <a href="mailto:{{ config('hostel.email') }}">
                                {{ config('hostel.email') }}
                            </a>
                        </li>

                        <li>
                            Alandur &amp; Perungalathur,<br>
                            Chennai, Tamil Nadu
                        </li>

                    </ul>

                </div>

            </div>


            <div class="foot-bottom">

                <span>
                    &copy; {{ date('Y') }}
                    {{ config('hostel.name') }}.
                    All rights reserved.
                </span>

                <span>
                    Boys &amp; Girls PG in Alandur &amp; Perungalathur, Chennai
                </span>

            </div>

        </div>

    </footer>


    {{-- MOBILE CTA --}}
    <div class="mobile-cta">

        <a href="tel:{{ config('hostel.phone') }}">

            <svg class="i">
                <use href="#i-phone"/>
            </svg>

            Call

        </a>

        <a class="wa"
           href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability%20in%20Alandur%20%2F%20Perungalathur"
           target="_blank"
           rel="noopener noreferrer">

            <svg class="i">
                <use href="#i-chat"/>
            </svg>

            WhatsApp

        </a>

        <a class="book"
           href="{{ route('contact') }}">
            Book a Visit
        </a>

    </div>


    {{-- JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* MOBILE MENU */
            var toggle = document.querySelector('.nav-toggle');
            var menu = document.querySelector('.nav-links');

            if (toggle && menu) {

                toggle.addEventListener('click', function () {

                    var open = menu.classList.toggle('open');

                    toggle.setAttribute(
                        'aria-expanded',
                        open ? 'true' : 'false'
                    );

                });

                document.addEventListener('keydown', function (e) {

                    if (e.key === 'Escape' && menu.classList.contains('open')) {

                        menu.classList.remove('open');

                        toggle.setAttribute('aria-expanded', 'false');

                        toggle.focus();

                    }

                });

            }


            /* FAQ ACCORDION */
            document.querySelectorAll('.faq-q')
                .forEach(function (btn) {

                    btn.addEventListener('click', function () {

                        var item = this.closest('.faq-item');

                        if (!item) return;

                        var isOpen = item.classList.contains('open');

                        /* Close all */
                        document.querySelectorAll('.faq-item.open')
                            .forEach(function (i) {

                                i.classList.remove('open');

                                var q = i.querySelector('.faq-q');

                                if (q) q.setAttribute('aria-expanded', 'false');

                            });

                        /* Toggle current */
                        if (!isOpen) {

                            item.classList.add('open');

                            this.setAttribute('aria-expanded', 'true');

                        }

                    });

                });


            /* FACILITY TABS */
            document.querySelectorAll('.tab-btn')
                .forEach(function (btn) {

                    btn.addEventListener('click', function () {

                        document.querySelectorAll('.tab-btn')
                            .forEach(function (b) {
                                b.classList.remove('active');
                            });

                        this.classList.add('active');


                        document.querySelectorAll('.tab-panel')
                            .forEach(function (p) {
                                p.classList.remove('active');
                            });


                        var target =
                            document.getElementById(
                                this.dataset.tab
                            );

                        if (target) {
                            target.classList.add('active');
                        }

                    });

                });

        });
    </script>


    @stack('scripts')

</body>

</html>
