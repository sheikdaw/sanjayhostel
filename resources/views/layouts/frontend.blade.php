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

    {{-- Open Graph / Twitter (home page already defines these sections) --}}
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

    <style>
        /* ============================================================
           Sanjay & Harini Hostels — Design System
           Navy = trust, Marigold = the action, Blue = boys, Plum = girls
           ============================================================ */
        :root {
            --navy: #0F2B46;
            --navy-2: #1B4166;
            --ink: #14212E;
            --stone: #586878;
            --line: #DCE3EA;
            --field: #7E90A3;
            --mist: #F3F6F9;
            --cta: #F7A800;
            --cta-deep: #DE9300;
            --boys: #1F5FA8;
            --boys-tint: #E4EEF9;
            --girls: #B0356A;
            --girls-tint: #F8E4EC;
            --ok: #12805C;
            --ok-tint: #E1F3EC;

            /* legacy names still used by some older inline styles */
            --ivory: var(--mist);
            --ivory-deep: var(--mist);
            --cream: #fff;
            --amber: var(--boys);
            --amber-deep: var(--navy);
            --amber-tint: var(--boys-tint);
            --rose: var(--girls);
            --rose-deep: #8A2653;
            --rose-tint: var(--girls-tint);

            --font-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
            --font-body: 'Instrument Sans', 'Segoe UI', system-ui, sans-serif;
            --font-mono: var(--font-body);
            --wrap-w: 1180px;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow: 0 1px 2px rgba(15, 43, 70, .06), 0 8px 24px rgba(15, 43, 70, .08);
            --shadow-lg: 0 18px 48px rgba(15, 43, 70, .22);
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font-body); font-size: 16px; line-height: 1.6; color: var(--ink); background: #fff; -webkit-font-smoothing: antialiased; }
        img { max-width: 100%; display: block; }
        a { color: inherit; text-decoration: none; }
        ul { list-style: none; }
        button { font: inherit; cursor: pointer; border: none; background: none; color: inherit; }
        h1, h2, h3, h4 { font-family: var(--font-display); font-weight: 700; line-height: 1.15; letter-spacing: -.01em; color: var(--ink); }
        :focus-visible { outline: 3px solid var(--boys); outline-offset: 3px; }
        .on-dark :focus-visible, .hero :focus-visible, .final-cta :focus-visible, .page-hero :focus-visible, .site-footer :focus-visible { outline-color: #fff; }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            * { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }

        .wrap { max-width: var(--wrap-w); margin: 0 auto; padding: 0 24px; }
        section { padding: 72px 0; }
        .panel-ivory { background: var(--mist); }
        .reveal { /* kept for old markup; no hidden state so nothing can stay invisible */ }

        .eyebrow { display: inline-flex; align-items: center; gap: 10px; font-size: .9rem; font-weight: 600; color: var(--boys); margin-bottom: 10px; }
        .eyebrow::before { content: ""; width: 22px; height: 2px; background: currentColor; }
        .section-head { max-width: 700px; margin-bottom: 40px; }
        .section-head h2 { font-size: clamp(1.6rem, 3vw, 2.3rem); margin-bottom: 10px; }
        .section-head p { color: var(--stone); font-size: 1.05rem; }
        .highlight { color: var(--cta); }

        /* ---------- Buttons ---------- */
        .btn, .btn-primary, .btn-ghost, .btn-dark, .btn-wa {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 46px; padding: 11px 22px; border-radius: 8px; border: 2px solid transparent;
            font-family: var(--font-body); font-weight: 700; font-size: .95rem; line-height: 1.2; cursor: pointer;
            transition: background .2s ease, border-color .2s ease, color .2s ease;
        }
        .btn-primary, .btn-gold { background: var(--cta); color: var(--ink); }
        .btn-primary:hover, .btn-gold:hover { background: var(--cta-deep); }
        .btn-gold { display: inline-flex; align-items: center; gap: 8px; min-height: 46px; padding: 11px 22px; border-radius: 8px; font-weight: 700; }
        .btn-dark { background: var(--navy); color: #fff; }
        .btn-dark:hover { background: var(--navy-2); }
        .btn-wa { background: var(--ok); color: #fff; }
        .btn-wa:hover { background: #0E6A4C; }
        .btn-ghost { background: transparent; color: var(--ink); border-color: var(--field); }
        .btn-ghost:hover { border-color: var(--navy); background: var(--mist); }
        .hero .btn-ghost, .final-cta .btn-ghost { color: #fff; border-color: rgba(255, 255, 255, .5); }
        .hero .btn-ghost:hover, .final-cta .btn-ghost:hover { background: rgba(255, 255, 255, .1); border-color: #fff; }
        .btn-block { width: 100%; }

        .i { width: 1.15em; height: 1.15em; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex: none; }

        .tag-pill, .pill { display: inline-block; font-size: .8rem; font-weight: 700; padding: 4px 12px; border-radius: 999px; background: var(--boys-tint); color: var(--boys); }
        .pill.girls, .girls-c .tag-pill, .identity-card.girls .tag-pill { background: var(--girls-tint); color: var(--rose-deep); }

        /* ============================================================
           TOP BAR + NAV
           ============================================================ */
        .topbar { background: var(--navy); color: #C9D7E6; font-size: .85rem; }
        .topbar .wrap { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding-top: 8px; padding-bottom: 8px; }
        .topbar a { color: #fff; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .topbar-links { display: flex; gap: 22px; }

        .site-nav { position: sticky; top: 0; z-index: 100; background: #fff; border-bottom: 1px solid var(--line); }
        .site-nav .wrap { display: flex; align-items: center; justify-content: space-between; height: 68px; }
        .brand { display: flex; align-items: center; gap: 10px; font-family: var(--font-display); font-weight: 800; font-size: 1.2rem; line-height: 1.1; }
        .brand small { display: block; font-family: var(--font-body); font-weight: 500; font-size: .74rem; color: var(--stone); margin-top: 2px; }
        .brand-mark { width: 36px; height: 36px; flex: none; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-links a { padding: 8px 13px; border-radius: 8px; font-weight: 600; font-size: .95rem; }
        .nav-links a:hover, .nav-links a.active { background: var(--mist); color: var(--boys); }
        .nav-links a.nav-phone { display: inline-flex; align-items: center; gap: 6px; margin-left: 10px; }
        .nav-links a.cta { background: var(--cta); color: var(--ink); padding: 10px 18px; margin-left: 6px; }
        .nav-links a.cta:hover { background: var(--cta-deep); }
        .nav-toggle { display: none; padding: 10px; border-radius: 8px; }
        .toggle-bar { display: block; width: 24px; height: 2px; background: var(--ink); margin: 5px 0; }

        .flash .alert { margin: 20px 0 0; }
        .alert { padding: 14px 18px; border-radius: 10px; font-size: .95rem; font-weight: 500; }
        .alert-success { background: var(--ok-tint); color: #0B5A40; border: 1px solid #A9DBC7; }
        .alert-error { background: var(--girls-tint); color: var(--rose-deep); border: 1px solid #E9B7CB; }

        /* ============================================================
           HOME HERO + TRUST STRIP
           ============================================================ */
        .hero { position: relative; background: var(--navy); color: #fff; padding: 64px 0 80px; overflow: hidden; }
        .hero-photo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .16; }
        .hero-grid { position: relative; display: grid; grid-template-columns: 1.1fr .9fr; gap: 56px; align-items: center; }
        .hero-kicker { display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .22); padding: 6px 14px; border-radius: 999px; font-size: .88rem; font-weight: 600; margin-bottom: 20px; }
        .hero h1 { color: #fff; font-size: clamp(2.1rem, 4.6vw, 3.5rem); margin-bottom: 18px; }
        .hero .sub { color: #C9D7E6; font-size: 1.08rem; max-width: 560px; margin-bottom: 26px; }
        .hero-points { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 22px; margin-bottom: 30px; max-width: 560px; }
        .hero-points li { display: flex; align-items: center; gap: 10px; font-weight: 500; font-size: .98rem; }
        .hero-points .i { color: var(--cta); }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }

        .enquiry { box-shadow: var(--shadow-lg); border: none; }
        .enquiry h2 { font-size: 1.4rem; }

        .trust { padding: 0; background: #fff; border-bottom: 1px solid var(--line); }
        .trust-grid { display: grid; grid-template-columns: repeat(4, 1fr); }
        .trust-item { display: flex; align-items: center; gap: 14px; padding: 22px 20px; border-left: 1px solid var(--line); }
        .trust-item:first-child { border-left: none; padding-left: 0; }
        .trust-item .ic { width: 44px; height: 44px; border-radius: 10px; background: var(--boys-tint); color: var(--boys); display: grid; place-items: center; flex: none; }
        .trust-item strong { display: block; font-size: 1rem; line-height: 1.25; }
        .trust-item span { font-size: .85rem; color: var(--stone); line-height: 1.3; display: block; }

        .page-hero { background: var(--navy); color: #fff; padding: 56px 0 52px; }
        .page-hero .eyebrow { color: #9CC4F0; }
        .page-hero h1 { color: #fff; font-size: clamp(1.9rem, 4vw, 2.8rem); margin: 6px 0 12px; }
        .page-hero p { color: #C9D7E6; max-width: 620px; font-size: 1.05rem; }

        /* ============================================================
           ROOM CARDS (home) + PRICING (rooms page)
           ============================================================ */
        .category-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: stretch; }
        .category-card { display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 32px; }
        .category-card.luxury { border: 2px solid var(--navy); }
        .cat-tag { align-self: flex-start; font-size: .8rem; font-weight: 700; padding: 4px 12px; border-radius: 999px; background: var(--mist); color: var(--stone); margin-bottom: 14px; }
        .luxury .cat-tag { background: var(--navy); color: #fff; }
        .category-card h3 { font-size: 1.5rem; margin-bottom: 6px; }
        .category-card > p { color: var(--stone); }
        .price { font-family: var(--font-display); font-weight: 800; font-size: 2.3rem; letter-spacing: -.02em; color: var(--ink); line-height: 1.1; }
        .price span { font-family: var(--font-body); font-size: 1rem; font-weight: 500; color: var(--stone); letter-spacing: 0; }
        .price-block { margin: 20px 0 6px; padding: 18px 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .price-note { font-size: .84rem; color: var(--stone); margin-top: 6px; }
        .amenities-list { display: grid; gap: 10px; margin: 18px 0 8px; }
        .amenities-list li { display: flex; gap: 10px; align-items: flex-start; font-size: .96rem; }
        .amenities-list .check { color: var(--ok); margin-top: 3px; }
        .highlight-text { font-weight: 600; }
        details.more { margin-bottom: 8px; }
        details.more summary { cursor: pointer; font-weight: 700; font-size: .92rem; color: var(--boys); padding: 6px 0; list-style: none; }
        details.more summary::-webkit-details-marker { display: none; }
        details.more summary::before { content: "+ "; }
        details.more[open] summary::before { content: "− "; }
        .category-card .btn, .category-card .btn-gold { margin-top: auto; align-self: stretch; }
        .category-card .card-foot { margin-top: auto; padding-top: 20px; }

        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .pricing-card { position: relative; display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 28px 24px; }
        .pricing-card.featured { border: 2px solid var(--navy); }
        .pricing-badge { align-self: flex-start; font-size: .78rem; font-weight: 700; padding: 4px 12px; border-radius: 999px; background: var(--mist); color: var(--stone); margin-bottom: 14px; }
        .featured .pricing-badge { background: var(--navy); color: #fff; }
        .pricing-card h3 { font-size: 1.3rem; margin-bottom: 6px; }
        .pricing-card .price { font-size: 2.1rem; margin: 6px 0 14px; }
        .pricing-features { margin: 0 0 22px; }
        .pricing-features li { padding: 8px 0; border-top: 1px solid var(--line); font-size: .95rem; color: var(--ink); }
        .pricing-card .btn-primary { margin-top: auto; }

        /* ============================================================
           GYM / ABOUT / LOCATIONS / WHY / TESTIMONIALS
           ============================================================ */
        .gym-grid, .about-wrap, .about, .safety-grid, .food-grid { display: grid; gap: 56px; align-items: center; }
        .gym-grid, .about-wrap, .safety-grid, .food-grid { grid-template-columns: 1fr 1fr; }
        .about { grid-template-columns: 1.05fr .95fr; }
        .gym-content h2, .about-copy h2 { font-size: clamp(1.6rem, 3vw, 2.3rem); margin-bottom: 14px; }
        .gym-content > p, .about-copy p { color: var(--stone); margin-bottom: 16px; font-size: 1.02rem; }
        .gym-features { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 20px; margin: 22px 0; }
        .gf { display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .gf .i { color: var(--boys); }
        .gym-rate { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-bottom: 22px; font-size: .95rem; }
        .gym-rate b { color: var(--boys); }
        .gym-visual { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .gym-visual img { width: 100%; height: 100%; min-height: 180px; object-fit: cover; border-radius: var(--radius); }
        .gym-visual img.full { grid-column: span 2; height: 200px; }

        .about-copy a { color: var(--boys); font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
        .about-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 28px 0 8px; }
        .about-stats .stat { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 16px 10px; text-align: center; }
        .about-stats .num { font-family: var(--font-display); font-weight: 800; font-size: 1.6rem; color: var(--navy); line-height: 1.2; }
        .about-stats .label { font-size: .8rem; color: var(--stone); margin-top: 2px; }
        .about-visual { display: grid; grid-template-columns: 1.1fr 1fr; gap: 14px; }
        .about-visual img { border-radius: var(--radius-lg); object-fit: cover; }
        .about-visual .tall { height: 100%; min-height: 320px; }
        .about-visual .col { display: flex; flex-direction: column; gap: 14px; }
        .about-visual .col img { height: calc(50% - 7px); min-height: 150px; }

        .location-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .location-card { display: flex; flex-direction: column; gap: 10px; background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 26px; }
        .location-card .who { display: flex; gap: 8px; }
        .location-card h4 { font-size: 1.3rem; }
        .location-card p { color: var(--stone); font-size: .96rem; }
        .location-card .go { margin-top: auto; padding-top: 8px; font-weight: 700; color: var(--boys); display: inline-flex; align-items: center; gap: 6px; }
        .location-card .go:hover { text-decoration: underline; }

        .why-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .why-item { display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 16px 18px; }
        .tick-circ { width: 38px; height: 38px; flex: none; display: grid; place-items: center; border-radius: 10px; background: var(--boys-tint); color: var(--boys); }
        .why-item p { font-weight: 600; line-height: 1.3; }

        .testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .testi-card { display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 28px; }
        .testi-card .stars { color: var(--cta-deep); letter-spacing: 2px; margin-bottom: 12px; }
        .testi-card .quote { font-size: 1.02rem; margin-bottom: 18px; }
        .testi-card .who { margin-top: auto; font-size: .88rem; font-weight: 600; color: var(--stone); }

        .final-cta { background: var(--navy); color: #fff; }
        .final-cta .wrap { display: grid; grid-template-columns: 1.2fr .8fr; gap: 40px; align-items: center; }
        .final-cta .strap { color: var(--cta); font-weight: 700; font-size: .95rem; margin-bottom: 10px; }
        .final-cta h2 { color: #fff; font-size: clamp(1.8rem, 3.6vw, 2.6rem); margin-bottom: 12px; }
        .final-cta .lead { color: #C9D7E6; max-width: 560px; }
        .final-cta .hero-actions { flex-direction: column; }

        .safety { background: var(--mist); }
        .safety-list { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 24px; margin-top: 8px; }
        .safety-item { display: flex; gap: 12px; align-items: flex-start; }
        .safety-item .ic { color: var(--ok); line-height: 1.6; }
        .safety-item h4 { font-size: 1rem; margin-bottom: 2px; }
        .safety-item p { color: var(--stone); font-size: .92rem; }
        .safety-visual img { border-radius: var(--radius-lg); }
        .nearby-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .nearby-chip { font-size: .9rem; font-weight: 500; padding: 9px 16px; border-radius: 999px; background: #fff; border: 1px solid var(--line); color: var(--ink); }
        a.nearby-chip:hover { border-color: var(--boys); color: var(--boys); }

        /* ============================================================
           CONTACT / FORMS / FAQ
           ============================================================ */
        .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .contact-card { background: #fff; border: 1px solid var(--line); border-top: 4px solid var(--boys); border-radius: var(--radius-lg); padding: 28px; }
        .contact-card.girls-c { border-top-color: var(--girls); }
        .contact-card h2 { font-size: 1.2rem; margin: 10px 0 16px; }
        .contact-row { display: flex; gap: 10px; align-items: center; padding: 10px 0; border-top: 1px solid var(--line); font-size: .98rem; }

        .form-shell { background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 32px; }
        .form-shell h3 { font-size: 1.35rem; margin-bottom: 4px; }
        .form-shell > p { color: var(--stone); margin-bottom: 22px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-full { margin-bottom: 18px; }
        .form-shell label { display: block; font-size: .9rem; font-weight: 600; margin-bottom: 6px; }
        .form-shell input, .form-shell select, .form-shell textarea { width: 100%; min-height: 46px; padding: 11px 14px; border: 1.5px solid var(--field); border-radius: 8px; background: #fff; font: inherit; font-size: 1rem; color: var(--ink); }
        .form-shell textarea { min-height: 96px; resize: vertical; }
        .form-shell input:focus, .form-shell select:focus, .form-shell textarea:focus { border-color: var(--boys); outline: 3px solid var(--boys-tint); }
        .form-submit { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 12px 28px; border-radius: 8px; font-weight: 700; background: var(--cta); color: var(--ink); }
        .form-submit:hover { background: var(--cta-deep); }
        .enquiry .form-row { margin-bottom: 14px; }
        .enquiry .form-submit { width: 100%; }
        .form-note { font-size: .82rem; color: var(--stone); margin-top: 12px; }

        .faq-list { display: grid; gap: 10px; max-width: 860px; }
        .faq-item { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); }
        .faq-q { width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 17px 20px; text-align: left; font-weight: 600; }
        .faq-item .plus { font-size: 1.4rem; line-height: 1; color: var(--boys); transition: transform .2s ease; }
        .faq-item.open .plus { transform: rotate(45deg); }
        .faq-a { max-height: 0; overflow: hidden; padding: 0 20px; color: var(--stone); transition: max-height .25s ease, padding .25s ease; }
        .faq-item.open .faq-a { max-height: 400px; padding: 0 20px 18px; }

        /* ============================================================
           ROOMS FACILITIES / FOOD / GALLERY
           ============================================================ */
        .facility-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
        .tab-btn { padding: 10px 20px; border-radius: 8px; border: 1.5px solid var(--field); font-weight: 600; font-size: .95rem; background: #fff; }
        .tab-btn.active { background: var(--navy); border-color: var(--navy); color: #fff; }
        .tab-panel { display: none; }
        .tab-panel.active { display: grid; }
        .grid-cards { grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .fac-card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 18px; }
        .fac-card .ic { width: 44px; height: 44px; display: grid; place-items: center; font-size: 1.35rem; background: var(--mist); border-radius: 10px; margin-bottom: 12px; }
        .fac-card h4 { font-size: 1rem; margin-bottom: 2px; }
        .fac-card p { font-size: .9rem; color: var(--stone); }
        .meal-tags { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 16px; }
        .meal-tag { font-size: .88rem; font-weight: 600; padding: 6px 14px; border-radius: 999px; background: var(--boys-tint); color: var(--boys); }
        .plan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 16px; }
        .plan-card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 16px; }
        .plan-card h4 { font-size: 1rem; margin: 8px 0 2px; }
        .plan-card p { font-size: .95rem; font-weight: 700; color: var(--navy); }
        .food-visual img { border-radius: var(--radius-lg); }

        .gallery-grid { display: grid; grid-template-columns: repeat(4, 1fr); grid-auto-rows: 190px; gap: 14px; }
        .gallery-grid a { display: block; overflow: hidden; border-radius: var(--radius); }
        .gallery-grid img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-grid .g1 { grid-column: span 2; grid-row: span 2; }
        .gallery-grid .g5 { grid-column: span 2; }

        /* ============================================================
           LEGAL (terms + privacy)
           ============================================================ */
        .terms-content { max-width: 860px; margin: 0 auto; }
        .terms-intro { background: var(--mist); border-left: 4px solid var(--navy); border-radius: var(--radius); padding: 24px 28px; margin-bottom: 36px; }
        .terms-intro .last-updated { color: var(--stone); font-size: .9rem; margin-bottom: 8px; }
        .terms-intro p:last-child { margin: 0; color: var(--ink); }
        .terms-section { margin-bottom: 32px; padding-bottom: 28px; border-bottom: 1px solid var(--line); scroll-margin-top: 90px; }
        .terms-section h2 { font-size: 1.3rem; margin-bottom: 12px; }
        .terms-section > p { color: var(--stone); margin-bottom: 10px; }
        .terms-section ul { list-style: disc; padding-left: 22px; }
        .terms-section li { padding: 5px 0; color: var(--stone); }
        .terms-section li::marker { color: var(--boys); }
        .contact-section { background: var(--mist); border: none; border-radius: var(--radius); padding: 28px; }
        .contact-details { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 14px; }
        .contact-details p { color: var(--stone); line-height: 1.8; }
        .contact-details strong { display: block; color: var(--ink); }
        .terms-footer { margin-top: 36px; padding: 24px 28px; background: var(--navy); color: #C9D7E6; border-radius: var(--radius); text-align: center; }
        .terms-footer .business-name { margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, .15); color: #fff; }
        .legal-nav { display: flex; flex-wrap: wrap; gap: 8px; max-width: 860px; margin: 0 auto 32px; }
        .back-top { text-align: center; padding: 8px 0 48px; }
        .back-top a { font-weight: 700; color: var(--boys); }

        /* ============================================================
           FOOTER + MOBILE ACTION BAR
           ============================================================ */
        .site-footer { background: var(--navy); color: #B8C8D9; padding: 56px 0 24px; }
        .site-footer .foot-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.3fr; gap: 36px; margin-bottom: 36px; }
        .site-footer .brand { color: #fff; margin-bottom: 12px; }
        .site-footer .brand small { color: #9FB3C8; }
        .site-footer h5 { font-family: var(--font-body); font-size: .95rem; font-weight: 700; color: #fff; margin-bottom: 14px; }
        .site-footer p { font-size: .94rem; line-height: 1.7; }
        .site-footer ul { display: grid; gap: 9px; font-size: .94rem; }
        .site-footer a:hover { color: #fff; text-decoration: underline; }
        .foot-bottom { border-top: 1px solid rgba(255, 255, 255, .14); padding-top: 20px; font-size: .85rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; }

        .mobile-cta { display: none; }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 1000px) {
            .hero-grid, .gym-grid, .about-wrap, .about, .safety-grid, .food-grid, .final-cta .wrap { grid-template-columns: 1fr; gap: 36px; }
            .trust-grid { grid-template-columns: 1fr 1fr; }
            .trust-item:nth-child(3) { border-left: none; padding-left: 0; }
            .trust-item:nth-child(n+3) { border-top: 1px solid var(--line); }
            .why-grid, .grid-cards { grid-template-columns: repeat(2, 1fr); }
            .location-grid, .testi-grid { grid-template-columns: 1fr; }
            .category-grid { grid-template-columns: 1fr; }
            .final-cta .hero-actions { flex-direction: row; }
            .site-footer .foot-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 820px) {
            .topbar { display: none; }
            .site-nav .wrap { height: 60px; }
            .nav-toggle { display: block; }
            .nav-links { display: none; position: absolute; top: 100%; left: 0; right: 0; flex-direction: column; align-items: stretch; gap: 2px; background: #fff; padding: 12px 24px 20px; border-bottom: 1px solid var(--line); box-shadow: 0 16px 30px rgba(15, 43, 70, .12); }
            .nav-links.open { display: flex; }
            .nav-links a { padding: 13px 12px; font-size: 1.02rem; }
            .nav-links a.nav-phone { margin-left: 0; }
            .nav-links a.cta { margin: 8px 0 0; text-align: center; }
            body { padding-bottom: 64px; }
            .mobile-cta { display: grid; grid-template-columns: 1fr 1fr 1.3fr; position: fixed; left: 0; right: 0; bottom: 0; z-index: 90; background: #fff; border-top: 1px solid var(--line); box-shadow: 0 -6px 20px rgba(15, 43, 70, .1); }
            .mobile-cta a { display: flex; align-items: center; justify-content: center; gap: 7px; min-height: 60px; font-weight: 700; font-size: .95rem; }
            .mobile-cta .wa { color: var(--ok); }
            .mobile-cta .book { background: var(--cta); color: var(--ink); }
        }
        @media (max-width: 700px) {
            section { padding: 52px 0; }
            .hero { padding: 40px 0 52px; }
            .hero-points, .gym-features, .safety-list, .contact-details { grid-template-columns: 1fr; }
            .form-row, .contact-grid, .plan-grid { grid-template-columns: 1fr; }
            .form-shell { padding: 22px; }
            .category-card { padding: 24px; }
            .about-stats { grid-template-columns: 1fr 1fr; }
            .gallery-grid { grid-template-columns: 1fr 1fr; grid-auto-rows: 140px; }
            .gallery-grid .g1 { grid-column: span 2; grid-row: span 1; }
            .gallery-grid .g5 { grid-column: span 2; }
            .trust-grid { grid-template-columns: 1fr; }
            .trust-item, .trust-item:nth-child(3) { border-left: none; padding: 16px 0; }
            .trust-item + .trust-item { border-top: 1px solid var(--line); }
            .why-grid, .grid-cards { grid-template-columns: 1fr; }
            .site-footer .foot-grid { grid-template-columns: 1fr; }
        }
    </style>

    @stack('styles')

    @hasSection('schema')
        @yield('schema')
    @endif
</head>

<body id="top">

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
                <a href="tel:+919876543210"><svg class="i"><use href="#i-phone"/></svg> +91 98765 43210</a>
                <a href="https://wa.me/919876543210?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> WhatsApp</a>
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
                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms') ? 'active' : '' }}">Rooms &amp; pricing</a>
                @if (Route::has('gallery'))
                    <a href="{{ route('gallery') }}" class="{{ request()->routeIs('gallery') ? 'active' : '' }}">Gallery</a>
                @endif
                <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                <a href="tel:+919876543210" class="nav-phone"><svg class="i"><use href="#i-phone"/></svg> Call now</a>
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

    <main>
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
                        <li><a href="tel:+919876543210">+91 98765 43210</a></li>
                        <li><a href="mailto:info@sanjayandharinihostels.com">info@sanjayandharinihostels.com</a></li>
                        <li>Alandur, St. Thomas Mount, Perungalathur</li>
                    </ul>
                </div>
            </div>
            <div class="foot-bottom">
                <span>&copy; {{ date('Y') }} Sanjay &amp; Harini Hostels. All rights reserved.</span>
                <span>developed by <span style="color:#D95C92;">♥</span> sheik</span>
            </div>
        </div>
    </footer>

    <div class="mobile-cta">
        <a href="tel:+919876543210"><svg class="i"><use href="#i-phone"/></svg> Call</a>
        <a class="wa" href="https://wa.me/919876543210?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> WhatsApp</a>
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
                    document.getElementById(this.dataset.tab).classList.add('active');
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
