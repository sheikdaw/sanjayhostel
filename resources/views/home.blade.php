@extends('layouts.frontend')

{{-- TASK 1: Title Tag (optimized for CTR & Local SEO) --}}
@section('title', 'Luxury & Normal PG in Alandur, Chennai | Sanjay Boys & Harini Girls Hostel with Gym')

{{-- TASK 2: Meta Description (compelling, ~155 chars) --}}
@section('meta_description', 'Sanjay Boys & Harini Girls Hostel offers luxury and normal PG accommodation in Alandur, St. Thomas Mount & Perungalathur. AC/Non-AC rooms, gym, home food, WiFi, CCTV. Near metro & railway. Book now!')

{{-- TASK 3: Canonical URL --}}
@section('canonical', 'https://www.sanjayandharinihostels.com/')

{{-- TASK 10: Open Graph (OG) Meta Tags --}}
@section('og_title', 'Luxury & Normal PG in Alandur, Chennai | Sanjay Boys & Harini Girls Hostel')
@section('og_description', 'Premium luxury and budget PG accommodation in Alandur, St. Thomas Mount, and Perungalathur. Gym, AC/Non-AC rooms, food, WiFi, CCTV. Near metro & railway.')
@section('og_url', 'https://www.sanjayandharinihostels.com/')
@section('og_type', 'website')
@section('og_image', 'https://www.sanjayandharinihostels.com/images/og-image.jpg')

{{-- TASK 11: Twitter Card Meta Tags --}}
@section('twitter_card', 'summary_large_image')
@section('twitter_title', 'Luxury & Normal PG in Alandur, Chennai | Sanjay Boys & Harini Girls Hostel')
@section('twitter_description', 'Luxury and budget PG accommodation in Alandur, St. Thomas Mount & Perungalathur. Gym, AC/Non-AC rooms, food, WiFi, CCTV. Near metro & railway.')
@section('twitter_image', 'https://www.sanjayandharinihostels.com/images/og-image.jpg')

@section('content')
<style>
    /* ===== ROOT VARIABLES ===== */
    :root {
        --gold: #C9A84C;
        --gold-light: #E8D5A3;
        --gold-dark: #A8892E;
        --amber: #D4A853;
        --amber-deep: #B8922E;
        --rose: #E85D75;
        --rose-deep: #C94A62;
        --cream: #FFF8F0;
        --ivory: #FDF7F0;
        --stone: #4A4A4A;
        --line: #E8E0D8;
        --shadow: 0 8px 30px rgba(0,0,0,0.12);
        --radius-lg: 20px;
        --radius-md: 12px;
        --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    /* ===== RESET & BASE ===== */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: var(--stone); background: #fff; line-height: 1.7; }
    .wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .panel-ivory { background: var(--ivory); padding: 80px 0; }
    .section-head { text-align: center; max-width: 720px; margin: 0 auto 56px; }
    .section-head .eyebrow { display: inline-block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 3px; color: var(--gold); font-weight: 700; margin-bottom: 12px; }
    .section-head h2 { font-size: 2.4rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2; color: #1a1a1a; }
    .section-head p { color: var(--stone); font-size: 1.05rem; margin-top: 16px; }
    .reveal { opacity: 0; transform: translateY(30px); animation: fadeUp 0.7s ease forwards; }
    .reveal:nth-child(2) { animation-delay: 0.15s; }
    .reveal:nth-child(3) { animation-delay: 0.3s; }

    @keyframes fadeUp {
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== BUTTONS ===== */
    .btn {
        display: inline-block;
        padding: 14px 36px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        transition: var(--transition);
        border: 2px solid transparent;
        cursor: pointer;
        letter-spacing: 0.3px;
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--gold), var(--gold-dark));
        color: #fff;
        border-color: var(--gold);
        box-shadow: 0 4px 20px rgba(201, 168, 76, 0.35);
    }
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(201, 168, 76, 0.45);
        background: linear-gradient(135deg, var(--gold-dark), var(--gold));
    }
    .btn-ghost {
        background: transparent;
        color: var(--stone);
        border-color: var(--line);
    }
    .btn-ghost:hover {
        background: var(--cream);
        border-color: var(--gold);
        color: var(--gold-dark);
    }
    .btn-gold {
        background: var(--gold);
        color: #fff;
    }
    .btn-gold:hover {
        background: var(--gold-dark);
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(201, 168, 76, 0.4);
    }

    /* ===== HERO ===== */
    .hero {
        position: relative;
        min-height: 90vh;
        display: flex;
        align-items: center;
        background: var(--cream);
        overflow: hidden;
        padding: 40px 0;
    }
    .hero-bg {
        position: absolute;
        inset: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        opacity: 0.3;
        z-index: 0;
    }
    .hero-bg .side { overflow: hidden; }
    .hero-bg .side img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 820px;
        margin: 0 auto;
        text-align: center;
        padding: 40px 20px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
    }
    .hero-tag {
        display: inline-block;
        background: linear-gradient(135deg, var(--gold), var(--gold-light));
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 6px 18px;
        border-radius: 50px;
        margin-bottom: 18px;
    }
    .hero-content h1 {
        font-size: 3rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        line-height: 1.15;
        color: #1a1a1a;
        margin-bottom: 18px;
    }
    .hero-content h1 .highlight { color: var(--gold); }
    .hero-content .sub {
        font-size: 1.1rem;
        color: var(--stone);
        max-width: 680px;
        margin: 0 auto 28px;
        line-height: 1.8;
    }
    .hero-actions {
        display: flex;
        gap: 14px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 32px;
    }
    .badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    .badge {
        background: rgba(255,255,255,0.8);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--stone);
        border: 1px solid var(--line);
    }
    .badge .tick { color: var(--gold); margin-right: 6px; }

    /* ===== CATEGORY CARDS ===== */
    .category-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        margin-top: 20px;
    }
    .category-card {
        background: #fff;
        border-radius: var(--radius-lg);
        padding: 40px 32px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    .category-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 48px rgba(0,0,0,0.15);
    }
    .category-card.luxury {
        border-color: var(--gold);
        background: linear-gradient(145deg, #fff, #FFFBF0);
    }
    .category-card.luxury::before {
        content: '★';
        position: absolute;
        top: -20px;
        right: -10px;
        font-size: 120px;
        color: rgba(201, 168, 76, 0.08);
    }
    .category-card.normal {
        border-color: var(--line);
        background: linear-gradient(145deg, #fff, #FAFAFA);
    }
    .category-card .icon {
        font-size: 2.8rem;
        margin-bottom: 12px;
        display: block;
    }
    .category-card .cat-tag {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        padding: 4px 14px;
        border-radius: 50px;
        margin-bottom: 12px;
    }
    .category-card.luxury .cat-tag {
        background: var(--gold);
        color: #fff;
    }
    .category-card.normal .cat-tag {
        background: var(--line);
        color: var(--stone);
    }
    .category-card h3 {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 12px;
        color: #1a1a1a;
    }
    .category-card p {
        color: var(--stone);
        margin-bottom: 16px;
        font-size: 0.95rem;
    }
    .category-card ul {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    .category-card ul li {
        padding: 6px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.92rem;
        color: var(--stone);
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .category-card ul li:last-child { border-bottom: none; }
    .category-card ul li .check {
        color: var(--gold);
        font-weight: 700;
    }
    .category-card .price {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1a1a1a;
        margin: 12px 0 8px;
    }
    .category-card .price span {
        font-size: 0.9rem;
        font-weight: 400;
        color: var(--stone);
    }

    /* ===== GYM SECTION ===== */
    .gym-section {
        background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
        color: #fff;
        padding: 80px 0;
        border-radius: 0;
        position: relative;
        overflow: hidden;
    }
    .gym-section::before {
        content: '💪';
        position: absolute;
        right: -40px;
        bottom: -40px;
        font-size: 200px;
        opacity: 0.05;
    }
    .gym-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: center;
    }
    .gym-content h2 {
        font-size: 2.4rem;
        font-weight: 800;
        margin-bottom: 16px;
        line-height: 1.2;
    }
    .gym-content h2 .highlight { color: var(--gold); }
    .gym-content p {
        color: rgba(255,255,255,0.7);
        font-size: 1.05rem;
        line-height: 1.8;
        margin-bottom: 20px;
    }
    .gym-features {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 16px;
    }
    .gym-features .gf {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.92rem;
        color: rgba(255,255,255,0.8);
        padding: 8px 12px;
        background: rgba(255,255,255,0.05);
        border-radius: var(--radius-md);
        border: 1px solid rgba(255,255,255,0.06);
    }
    .gym-features .gf .emoji { font-size: 1.2rem; }
    .gym-visual {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .gym-visual img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        border-radius: var(--radius-md);
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        transition: var(--transition);
    }
    .gym-visual img:hover { transform: scale(1.03); }
    .gym-visual .full { grid-column: 1 / -1; height: 200px; }

    /* ===== ABOUT ===== */
    .about-wrap {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: center;
    }
    .about-copy h2 { font-size: 2.2rem; font-weight: 800; line-height: 1.2; color: #1a1a1a; margin-bottom: 16px; }
    .about-copy p { color: var(--stone); margin-bottom: 14px; line-height: 1.8; }
    .about-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-top: 24px;
    }
    .about-stats .stat {
        text-align: center;
        background: #fff;
        padding: 16px 8px;
        border-radius: var(--radius-md);
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .about-stats .stat .num {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--gold-dark);
    }
    .about-stats .stat .label {
        font-size: 0.75rem;
        color: var(--stone);
        margin-top: 4px;
        font-weight: 600;
    }
    .about-visual {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .about-visual img {
        width: 100%;
        height: 280px;
        object-fit: cover;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow);
        transition: var(--transition);
    }
    .about-visual img:hover { transform: scale(1.02); }
    .about-visual .tall { height: 340px; }

    /* ===== LOCATIONS ===== */
    .location-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-top: 20px;
    }
    .location-card {
        background: #fff;
        border-radius: var(--radius-md);
        padding: 28px 24px;
        box-shadow: var(--shadow);
        border-top: 4px solid var(--gold);
        transition: var(--transition);
    }
    .location-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); }
    .location-card .loc-icon { font-size: 1.8rem; margin-bottom: 8px; }
    .location-card h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 6px; color: #1a1a1a; }
    .location-card p { font-size: 0.88rem; color: var(--stone); line-height: 1.6; }
    .location-card .tag {
        display: inline-block;
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 2px 12px;
        border-radius: 50px;
        background: var(--gold-light);
        color: var(--gold-dark);
        margin-top: 8px;
    }

    /* ===== WHY CHOOSE ===== */
    .why-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    .why-item {
        background: #fff;
        padding: 24px 16px;
        border-radius: var(--radius-md);
        text-align: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: var(--transition);
        border: 1px solid var(--line);
    }
    .why-item:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow);
        border-color: var(--gold);
    }
    .why-item .tick-circ {
        display: inline-block;
        width: 44px;
        height: 44px;
        line-height: 44px;
        border-radius: 50%;
        background: var(--gold-light);
        color: var(--gold-dark);
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .why-item p { font-weight: 600; font-size: 0.92rem; color: #1a1a1a; }

    /* ===== FAQ ===== */
    .faq-list {
        max-width: 800px;
        margin: 0 auto;
    }
    .faq-item {
        border-bottom: 1px solid var(--line);
        padding: 6px 0;
    }
    .faq-q {
        width: 100%;
        background: none;
        border: none;
        padding: 18px 0;
        font-size: 1rem;
        font-weight: 600;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        color: #1a1a1a;
        transition: var(--transition);
    }
    .faq-q:hover { color: var(--gold-dark); }
    .faq-q .plus {
        font-size: 1.4rem;
        font-weight: 300;
        color: var(--gold);
        transition: var(--transition);
    }
    .faq-q[aria-expanded="true"] .plus { transform: rotate(45deg); }
    .faq-a {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, padding 0.4s ease;
        padding: 0 0 0 0;
        color: var(--stone);
        line-height: 1.7;
    }
    .faq-q[aria-expanded="true"] + .faq-a {
        max-height: 200px;
        padding: 0 0 20px 0;
    }

    /* ===== TESTIMONIALS ===== */
    .testi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    .testi-card {
        background: #fff;
        padding: 28px 24px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow);
        border: 1px solid var(--line);
        transition: var(--transition);
    }
    .testi-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }
    .testi-card .stars {
        color: var(--gold);
        font-size: 1.1rem;
        letter-spacing: 2px;
        margin-bottom: 10px;
    }
    .testi-card .quote {
        font-size: 0.95rem;
        color: var(--stone);
        line-height: 1.7;
        font-style: italic;
        margin-bottom: 12px;
    }
    .testi-card .who {
        font-size: 0.8rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    /* ===== FINAL CTA ===== */
    .final-cta {
        background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
        color: #fff;
        padding: 80px 0;
        text-align: center;
    }
    .final-cta .strap {
        display: inline-block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: var(--gold);
        font-weight: 700;
        margin-bottom: 12px;
    }
    .final-cta h2 {
        font-size: 2.6rem;
        font-weight: 900;
        margin-bottom: 16px;
        line-height: 1.2;
    }
    .final-cta .lead {
        font-size: 1.1rem;
        color: rgba(255,255,255,0.7);
        max-width: 600px;
        margin: 0 auto 32px;
        line-height: 1.7;
    }
    .final-cta .btn-primary {
        background: linear-gradient(135deg, var(--gold), var(--gold-dark));
        border-color: var(--gold);
        color: #fff;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .category-grid { gap: 24px; }
        .category-card { padding: 32px 24px; }
        .gym-grid { gap: 32px; }
        .location-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .hero-content h1 { font-size: 2rem; }
        .hero-content .sub { font-size: 0.95rem; }
        .category-grid { grid-template-columns: 1fr; }
        .about-wrap { grid-template-columns: 1fr; }
        .about-visual { grid-template-columns: 1fr 1fr; }
        .about-visual img { height: 200px; }
        .about-visual .tall { height: 240px; }
        .gym-grid { grid-template-columns: 1fr; }
        .gym-visual { grid-template-columns: 1fr 1fr; }
        .gym-visual img { height: 160px; }
        .gym-features { grid-template-columns: 1fr; }
        .location-grid { grid-template-columns: 1fr; }
        .why-grid { grid-template-columns: repeat(2, 1fr); }
        .testi-grid { grid-template-columns: 1fr; }
        .about-stats { grid-template-columns: repeat(2, 1fr); }
        .section-head h2 { font-size: 1.8rem; }
        .final-cta h2 { font-size: 1.8rem; }
        .hero-actions { flex-direction: column; align-items: center; }
        .btn { width: 100%; max-width: 280px; text-align: center; }
    }

    @media (max-width: 480px) {
        .hero { min-height: auto; padding: 20px 0; }
        .hero-content { padding: 24px 16px; }
        .badge-row .badge { font-size: 0.7rem; padding: 4px 12px; }
        .category-card { padding: 24px 16px; }
        .about-visual { grid-template-columns: 1fr; }
        .about-visual img { height: 200px; }
        .why-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
        .why-item { padding: 16px 12px; }
        .about-stats { grid-template-columns: 1fr 1fr; gap: 10px; }
        .about-stats .stat { padding: 12px 8px; }
        .about-stats .stat .num { font-size: 1.2rem; }
    }
</style>

{{-- ===== HERO SECTION ===== --}}
<section class="hero seam" aria-labelledby="hero-title">
    <div class="hero-bg">
        <div class="side left">
            <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1200"
                 alt="Luxury boys PG room in Alandur, Chennai"
                 width="1200" height="800" loading="eager" decoding="async">
        </div>
        <div class="side right">
            <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=1200"
                 alt="Comfortable girls hostel room in St. Thomas Mount, Chennai"
                 width="1200" height="800" loading="eager" decoding="async">
        </div>
    </div>
    <div class="hero-content">
        <span class="hero-tag">🏆 Premium PG in Alandur · St. Thomas Mount · Perungalathur</span>
        <h1 id="hero-title">Best <span class="highlight">Luxury</span> &amp; <span class="highlight">Normal</span> PG in Alandur, Chennai</h1>
        <p class="sub">Sanjay Boys Hostel &amp; Harini Girls Hostel — offering <strong>luxury</strong> and <strong>budget-friendly</strong> PG accommodation. Enjoy gym access, AC/Non-AC rooms, home-style food, high-speed WiFi, and 24/7 CCTV security. Perfect for IT professionals, students, and airport staff.</p>
        <div class="hero-actions">
            <a href="{{ route('contact') }}" class="btn btn-primary">🚀 Book Your Room Now</a>
            <a href="{{ route('contact') }}" class="btn btn-ghost">📞 Contact Us</a>
        </div>
        <div class="badge-row">
            <span class="badge"><span class="tick">✓</span> Luxury &amp; Normal Rooms</span>
            <span class="badge"><span class="tick">✓</span> 🏋️ Gym Facility</span>
            <span class="badge"><span class="tick">✓</span> AC &amp; Non-AC</span>
            <span class="badge"><span class="tick">✓</span> High-Speed WiFi</span>
            <span class="badge"><span class="tick">✓</span> 24/7 CCTV</span>
            <span class="badge"><span class="tick">✓</span> Home-Style Food</span>
            <span class="badge"><span class="tick">✓</span> Near Alandur Metro</span>
        </div>
    </div>
</section>

{{-- ===== CATEGORIES: LUXURY & NORMAL ===== --}}
<section class="panel-ivory" aria-labelledby="categories-title">
    <div class="wrap">
        <div class="section-head reveal">
            <span class="eyebrow">Choose Your Stay</span>
            <h2 id="categories-title">Luxury or Normal — We Have It All</h2>
            <p>Whether you prefer premium luxury living or comfortable budget accommodation, we have the perfect PG for you in Chennai.</p>
        </div>

        <div class="category-grid reveal">
            {{-- LUXURY CARD --}}
            <div class="category-card luxury">
                <span class="icon">👑</span>
                <span class="cat-tag">★ Premium Luxury</span>
                <h3>Luxury PG Accommodation</h3>
                <p>Experience premium living with top-tier amenities designed for those who want the best.</p>
                <ul>
                    <li><span class="check">✓</span> Spacious AC Rooms with Attached Bathroom</li>
                    <li><span class="check">✓</span> 🏋️ Premium Gym Access (Included)</li>
                    <li><span class="check">✓</span> King-Size Bed with Premium Mattress</li>
                    <li><span class="check">✓</span> 43" Smart TV in Common Area</li>
                    <li><span class="check">✓</span> High-Speed Fiber WiFi (100 Mbps)</li>
                    <li><span class="check">✓</span> 24/7 Concierge &amp; Housekeeping</li>
                    <li><span class="check">✓</span> Gourmet Meals (Veg/Non-Veg)</li>
                    <li><span class="check">✓</span> Study Desk &amp; Wardrobe</li>
                    <li><span class="check">✓</span> Power Backup &amp; RO Water</li>
                </ul>
                <div class="price">₹12,000 <span>/ month</span></div>
                <a href="{{ route('contact') }}" class="btn btn-gold">Enquire About Luxury →</a>
            </div>

            {{-- NORMAL CARD --}}
            <div class="category-card normal">
                <span class="icon">🏠</span>
                <span class="cat-tag">● Budget Friendly</span>
                <h3>Normal PG Accommodation</h3>
                <p>Comfortable, affordable, and well-maintained PG rooms for students and working professionals.</p>
                <ul>
                    <li><span class="check">✓</span> AC &amp; Non-AC Room Options</li>
                    <li><span class="check">✓</span> 🏋️ Gym Access (Additional)</li>
                    <li><span class="check">✓</span> Comfortable Beds with Storage</li>
                    <li><span class="check">✓</span> Shared Bathroom (Maintained)</li>
                    <li><span class="check">✓</span> High-Speed WiFi</li>
                    <li><span class="check">✓</span> 24/7 CCTV Security</li>
                    <li><span class="check">✓</span> Home-Style Meals (Veg/Non-Veg)</li>
                    <li><span class="check">✓</span> Daily Housekeeping</li>
                    <li><span class="check">✓</span> Power Backup &amp; RO Water</li>
                </ul>
                <div class="price">₹6,500 <span>/ month</span></div>
                <a href="{{ route('contact') }}" class="btn btn-ghost">Enquire About Normal →</a>
            </div>
        </div>
    </div>
</section>

{{-- ===== GYM FACILITY SECTION ===== --}}
<section class="gym-section" aria-labelledby="gym-title">
    <div class="wrap">
        <div class="gym-grid">
            <div class="gym-content reveal">
                <span class="eyebrow" style="color: var(--gold);">🏋️ Premium Facility</span>
                <h2 id="gym-title">State-of-the-Art <span class="highlight">Gym</span> for Our Residents</h2>
                <p>Stay fit and healthy without leaving your hostel! Our fully-equipped gym is available for all residents. Whether you're a beginner or a fitness enthusiast, our gym has everything you need.</p>
                <div class="gym-features">
                    <div class="gf"><span class="emoji">🏋️</span> Cardio Equipment</div>
                    <div class="gf"><span class="emoji">💪</span> Weight Training Area</div>
                    <div class="gf"><span class="emoji">🏃</span> Treadmill &amp; Cross Trainer</div>
                    <div class="gf"><span class="emoji">🧘</span> Yoga &amp; Stretching Zone</div>
                    <div class="gf"><span class="emoji">⏰</span> 6 AM - 10 PM Access</div>
                    <div class="gf"><span class="emoji">👨‍🏫</span> Trainer Available</div>
                </div>
                <a href="{{ route('contact') }}" class="btn btn-gold" style="margin-top: 20px;">💪 Check Gym Availability</a>
            </div>
            <div class="gym-visual reveal">
                <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600"
                     alt="Modern gym facility at Sanjay Boys Hostel, Chennai"
                     width="300" height="240" loading="lazy" decoding="async">
                <img src="https://images.unsplash.com/photo-1549060279-7e168fcee0c2?q=80&w=600"
                     alt="Cardio equipment at Harini Girls Hostel gym"
                     width="300" height="240" loading="lazy" decoding="async">
                <img src="https://images.unsplash.com/photo-1538805060514-97d9cc17730c?q=80&w=600"
                     alt="Weight training area in PG hostel gym"
                     width="600" height="200" loading="lazy" decoding="async" class="full">
            </div>
        </div>
    </div>
</section>

{{-- ===== ABOUT SECTION ===== --}}
<section class="panel-ivory" id="about" aria-labelledby="about-title">
    <div class="wrap">
        <div class="about-wrap">
            <div class="about-copy reveal">
                <span class="eyebrow">About Us</span>
                <h2 id="about-title">Sanjay Boys &amp; Harini Girls — <span style="color: var(--gold);">Trusted PG</span> in Chennai</h2>
                <p><strong>Sanjay Boys Hostel</strong> and <strong>Harini Girls Hostel</strong> offer premium PG accommodation in Alandur, St. Thomas Mount, and Perungalathur. We provide both <strong>luxury</strong> and <strong>normal</strong> room options with world-class amenities including a fully-equipped <strong>gym</strong>, AC/Non-AC rooms, and home-style meals.</p>
                <p>Our hostels are perfect for IT employees, working professionals, college students, airport staff, and metro commuters. With 24/7 security, high-speed WiFi, and modern facilities, we ensure a comfortable and safe living experience.</p>
                <div class="about-stats">
                    <div class="stat"><div class="num">3</div><div class="label">Branches</div></div>
                    <div class="stat"><div class="num">🏋️</div><div class="label">Gym Facility</div></div>
                    <div class="stat"><div class="num">24/7</div><div class="label">Security &amp; Support</div></div>
                    <div class="stat"><div class="num">500+</div><div class="label">Happy Residents</div></div>
                </div>
                <a href="{{ route('about') }}" style="display:inline-block;margin-top:16px;font-weight:700;color:var(--gold-dark);text-decoration:underline;">Learn more about us →</a>
            </div>
            <div class="about-visual reveal">
                <img class="tall" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=800"
                     alt="Luxury boys hostel room in Alandur, Chennai"
                     width="400" height="340" loading="lazy" decoding="async">
                <div class="col">
                    <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=600"
                         alt="Harini Girls Hostel common lounge in St. Thomas Mount"
                         width="300" height="240" loading="lazy" decoding="async">
                    <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=600"
                         alt="Sanjay Boys Hostel shared dining area"
                         width="300" height="240" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== LOCATIONS ===== --}}
<section aria-labelledby="locations-title">
    <div class="wrap" style="padding: 80px 24px;">
        <div class="section-head reveal">
            <span class="eyebrow">📍 Our Locations</span>
            <h2 id="locations-title">PG Accommodation in <span style="color: var(--gold);">Alandur, St. Thomas Mount</span> &amp; Perungalathur</h2>
            <p>Choose your branch — all close to Alandur Metro, St. Thomas Mount Railway Station, and Chennai Airport.</p>
        </div>

        <div class="location-grid reveal">
            <div class="location-card">
                <div class="loc-icon">🏙️</div>
                <h4>Alandur Branch</h4>
                <p>Near Alandur Metro Station, Guindy, and Nanganallur. Perfect for IT professionals and students.</p>
                <span class="tag">Luxury &amp; Normal</span>
            </div>
            <div class="location-card">
                <div class="loc-icon">🚉</div>
                <h4>St. Thomas Mount Branch</h4>
                <p>Near St. Thomas Mount Railway Station, Kathipara, and Chennai Airport Metro.</p>
                <span class="tag">Luxury &amp; Normal</span>
            </div>
            <div class="location-card">
                <div class="loc-icon">🏫</div>
                <h4>Perungalathur Branch</h4>
                <p>Near Perungalathur Railway Station, Tambaram, Vandalur, and GST Road. (Boys Only)</p>
                <span class="tag">Normal Only</span>
            </div>
        </div>
    </div>
</section>

{{-- ===== WHY CHOOSE US ===== --}}
<section class="panel-ivory" aria-labelledby="why-title">
    <div class="wrap">
        <div class="section-head reveal">
            <span class="eyebrow">Why Choose Us</span>
            <h2 id="why-title">Why Choose Our PG Hostel in Chennai</h2>
        </div>
        <div class="why-grid reveal">
            <div class="why-item"><span class="tick-circ">👑</span><p>Luxury &amp; Normal Options</p></div>
            <div class="why-item"><span class="tick-circ">🏋️</span><p>Free Gym Access</p></div>
            <div class="why-item"><span class="tick-circ">📍</span><p>Near Metro &amp; Railway</p></div>
            <div class="why-item"><span class="tick-circ">❄️</span><p>AC / Non-AC Rooms</p></div>
            <div class="why-item"><span class="tick-circ">📶</span><p>High-Speed WiFi</p></div>
            <div class="why-item"><span class="tick-circ">📹</span><p>24/7 CCTV Security</p></div>
            <div class="why-item"><span class="tick-circ">🍛</span><p>Home-Style Food</p></div>
            <div class="why-item"><span class="tick-circ">👩‍🦰</span><p>Safe for Women</p></div>
        </div>
    </div>
</section>

{{-- ===== FAQ ===== --}}
<section aria-labelledby="faq-title">
    <div class="wrap" style="padding: 80px 24px;">
        <div class="section-head reveal">
            <span class="eyebrow">FAQs</span>
            <h2 id="faq-title">Frequently Asked Questions</h2>
        </div>
        <div class="faq-list reveal">
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    Do you have luxury PG rooms?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">Yes! We offer premium luxury PG rooms with AC, attached bathrooms, king-size beds, smart TV, and gym access.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    Do you have normal/budget PG rooms?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">Yes, we have comfortable budget-friendly PG rooms with AC/Non-AC options, WiFi, and all essential amenities.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    Is there a gym facility at the hostel?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">Yes! All our branches have a fully-equipped gym with cardio and weight training equipment. Gym access is free for luxury residents.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    Do you provide boys PG accommodation in Alandur?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">Yes, Sanjay Boys Hostel offers premium boys PG accommodation in Alandur, near Alandur Metro Station and Guindy.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    Do you provide girls hostel accommodation?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">Yes, Harini Girls Hostel provides safe and comfortable girls hostel accommodation in Alandur and St. Thomas Mount with 24/7 security.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    Is WiFi available at the hostel?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">Yes, high-speed WiFi is available throughout all our hostel premises for residents.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    Is the hostel near Alandur Metro?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">Yes, our Alandur branch is located very close to Alandur Metro Station, making it convenient for commuters.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q" aria-expanded="false">
                    What are the food options available?
                    <span class="plus" aria-hidden="true">+</span>
                </button>
                <div class="faq-a">We provide nutritious home-style meals with both vegetarian and non-vegetarian options daily. Luxury residents get gourmet meal options.</div>
            </div>
        </div>
    </div>
</section>

{{-- ===== TESTIMONIALS ===== --}}
<section class="panel-ivory" aria-labelledby="testimonials-title">
    <div class="wrap">
        <div class="section-head reveal">
            <span class="eyebrow">Testimonials</span>
            <h2 id="testimonials-title">What Our Residents Say</h2>
        </div>
        <div class="testi-grid reveal">
            <div class="testi-card">
                <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                <p class="quote">"The luxury PG is amazing! Gym, AC room, and gourmet food — everything is top-notch. Best PG in Alandur!"</p>
                <div class="who">Arun Kumar — Luxury Resident, Alandur</div>
            </div>
            <div class="testi-card">
                <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                <p class="quote">"Safe, affordable, and the gym is a bonus! Harini Girls Hostel is perfect for working women in Chennai."</p>
                <div class="who">Priya Sharma — Harini Girls Hostel</div>
            </div>
            <div class="testi-card">
                <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                <p class="quote">"Great location near St. Thomas Mount station. The gym and WiFi are excellent. Highly recommend!"</p>
                <div class="who">Suresh Raj — Sanjay Boys Hostel, St. Thomas Mount</div>
            </div>
        </div>
    </div>
</section>

{{-- ===== FINAL CTA ===== --}}
<section class="final-cta" aria-labelledby="cta-title">
    <div class="wrap">
        <div class="strap">🏋️ Limited Gym Slots Available</div>
        <h2 id="cta-title">Your Home Away From Home in Chennai</h2>
        <p class="lead">Choose from <strong>Luxury</strong> or <strong>Normal</strong> PG with <strong>gym facility</strong> at Sanjay Boys &amp; Harini Girls Hostel. Book your room today!</p>
        <div class="hero-actions">
            <a href="{{ route('contact') }}" class="btn btn-primary">🏋️ Book Your Room Now</a>
            <a href="{{ route('contact') }}" class="btn btn-ghost" style="background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.2);">📞 Contact Us</a>
        </div>
    </div>
</section>

<script>
// FAQ Accordion
document.querySelectorAll('.faq-q').forEach(button => {
    button.addEventListener('click', function() {
        const expanded = this.getAttribute('aria-expanded') === 'true' || false;
        this.setAttribute('aria-expanded', !expanded);
    });
});
</script>
@endsection