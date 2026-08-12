<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sanjay & Harini Hostels | Best PG in Alandur, Chennai')</title>

    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif

    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
   <style>
    /* ============================================================
   Sanjay & Harini Hostels — Design System
   Two houses, one home. The palette and layout are built around
   a single motif: a warm "seam" that runs through the site,
   amber on the men's side, rose on the women's, meeting in the
   middle wherever the two brands are shown together.
   ============================================================ */

@import url('https://fonts.googleapis.com/css2?family=Zilla+Slab:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

:root {
  /* Color */
  --ivory: #F6EFE0;
  --ivory-deep: #EDE2C8;
  --cream: #FFFDF8;
  --ink: #221D18;
  --stone: #6E6153;
  --line: #E1D3B2;

  --amber: #B9752E;
  --amber-deep: #8F5A20;
  --amber-tint: #F1E1C7;

  --rose: #A83B52;
  --rose-deep: #7E2A3C;
  --rose-tint: #F0DCDF;

  /* Type */
  --font-display: 'Zilla Slab', Georgia, serif;
  --font-body: 'Work Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-mono: 'IBM Plex Mono', 'Courier New', monospace;

  /* Layout */
  --wrap-w: 1180px;
  --radius: 14px;
  --radius-lg: 22px;
  --shadow: 0 12px 34px rgba(34, 29, 24, 0.10);
  --shadow-lg: 0 24px 60px rgba(34, 29, 24, 0.16);
}

/* ---------- Reset ---------- */
* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
  font-family: var(--font-body);
  color: var(--ink);
  background: var(--ivory);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}
img { max-width: 100%; display: block; }
a { color: inherit; text-decoration: none; }
ul { list-style: none; }
button { font: inherit; cursor: pointer; border: none; background: none; color: inherit; }
h1, h2, h3, h4 { font-family: var(--font-display); font-weight: 700; line-height: 1.15; color: var(--ink); }
:focus-visible { outline: 3px solid var(--amber); outline-offset: 3px; }
@media (prefers-reduced-motion: reduce) {
  html { scroll-behavior: auto; }
  * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
}

.wrap { max-width: var(--wrap-w); margin: 0 auto; padding: 0 24px; }
section { padding: 88px 0; }
.panel-ivory { background: var(--ivory-deep); }

.eyebrow {
  display: inline-block;
  font-family: var(--font-mono);
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--amber-deep);
  margin-bottom: 14px;
}
.section-head { max-width: 640px; margin-bottom: 48px; }
.section-head h2 { font-size: clamp(1.7rem, 3vw, 2.4rem); margin-bottom: 12px; }
.section-head p { color: var(--stone); font-size: 1.05rem; }

/* ---------- Scroll reveal ---------- */
.reveal { opacity: 0; transform: translateY(22px); transition: opacity 0.7s ease, transform 0.7s ease; }
.reveal.in { opacity: 1; transform: translateY(0); }

/* ---------- Buttons & tags ---------- */
.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 28px; border-radius: 999px;
  font-family: var(--font-mono); font-weight: 600; font-size: 0.92rem;
  letter-spacing: 0.02em; transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}
.btn-primary {
  background: linear-gradient(100deg, var(--amber) 0%, var(--rose) 100%);
  color: var(--cream); box-shadow: var(--shadow);
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
.btn-ghost { background: transparent; color: var(--ink); border: 1.5px solid var(--line); }
.btn-ghost:hover { border-color: var(--amber); color: var(--amber-deep); }

.tag-pill {
  display: inline-block; font-family: var(--font-mono); font-size: 0.72rem;
  font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
  padding: 6px 16px; border-radius: 999px; background: var(--amber-tint); color: var(--amber-deep);
}
.identity-card.girls .tag-pill { background: var(--rose-tint); color: var(--rose-deep); }

/* ============================================================
   NAV
   ============================================================ */
.site-nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  padding: 20px 0; transition: background 0.35s ease, padding 0.35s ease, box-shadow 0.35s ease;
}
.site-nav .wrap { display: flex; align-items: center; justify-content: space-between; }
.brand { font-family: var(--font-display); font-weight: 700; font-size: 1.25rem; }
.brand .amp { color: var(--rose); }
.nav-links { display: flex; align-items: center; gap: 30px; }
.nav-links a { font-family: var(--font-mono); font-size: 0.85rem; font-weight: 600; letter-spacing: 0.02em; }
.nav-links a.cta {
  background: var(--ink); color: var(--cream); padding: 10px 20px; border-radius: 999px;
}
.nav-toggle { display: none; }

/* Transparent nav variant, used on the home hero */
.site-nav.on-hero { background: transparent; }
.site-nav.on-hero .brand, .site-nav.on-hero .nav-links a:not(.cta) { color: var(--cream); }
.site-nav.on-hero.scrolled {
  background: rgba(246, 239, 224, 0.92); backdrop-filter: blur(8px);
  box-shadow: 0 2px 20px rgba(34,29,24,0.08); padding: 14px 0;
}
.site-nav.on-hero.scrolled .brand, .site-nav.on-hero.scrolled .nav-links a:not(.cta) { color: var(--ink); }
.site-nav.solid { background: rgba(246, 239, 224, 0.96); backdrop-filter: blur(8px); box-shadow: 0 2px 20px rgba(34,29,24,0.06); }

@media (max-width: 820px) {
  .nav-links { position: fixed; inset: 0 0 0 30%; background: var(--cream); flex-direction: column;
    justify-content: center; align-items: flex-start; padding: 40px; gap: 24px; box-shadow: -12px 0 40px rgba(0,0,0,0.15);
    transform: translateX(100%); transition: transform 0.35s ease; }
  .nav-links.open { transform: translateX(0); }
  .nav-links a { color: var(--ink) !important; font-size: 1.1rem; }
  .nav-toggle { display: block; z-index: 101; }
  .site-nav .toggle-bar { width: 24px; height: 2px; background: currentColor; display: block; margin: 5px 0; transition: 0.25s; }
  .site-nav.on-hero .nav-toggle { color: var(--cream); }
  .site-nav.on-hero.scrolled .nav-toggle { color: var(--ink); }
}

/* ============================================================
   HERO (home)
   ============================================================ */
.hero { position: relative; min-height: 100vh; display: flex; align-items: center; overflow: hidden; }
.hero-bg { position: absolute; inset: 0; display: flex; z-index: 0; }
.hero-bg .side { flex: 1; position: relative; }
.hero-bg .side img { width: 100%; height: 100%; object-fit: cover; }
.hero-bg .side.left::after { content: ''; position: absolute; inset: 0; background: linear-gradient(0deg, rgba(34,20,10,0.65), rgba(34,20,10,0.25)); }
.hero-bg .side.right::after { content: ''; position: absolute; inset: 0; background: linear-gradient(0deg, rgba(40,15,22,0.65), rgba(40,15,22,0.25)); }

/* the seam: the site's signature element — a glowing gradient line
   where the boys' and girls' houses meet, echoed later as card
   accents and section dividers */
.hero.seam::before {
  content: ''; position: absolute; top: 0; bottom: 0; left: 50%; width: 6px; margin-left: -3px; z-index: 1;
  background: linear-gradient(180deg, var(--amber), var(--rose));
  box-shadow: 0 0 40px 6px rgba(184, 60, 82, 0.45);
}
.hero-content { position: relative; z-index: 2; max-width: 760px; margin: 0 auto; padding: 160px 24px 80px; text-align: center; color: var(--cream); }
.hero-tag {
  display: inline-block; font-family: var(--font-mono); font-size: 0.78rem; letter-spacing: 0.1em;
  text-transform: uppercase; padding: 8px 18px; border-radius: 999px;
  background: rgba(255,253,248,0.14); border: 1px solid rgba(255,253,248,0.35); margin-bottom: 24px;
}
.hero h1 { font-size: clamp(2.3rem, 5.5vw, 3.6rem); margin-bottom: 20px; }
.hero h1 em { font-style: normal; background: linear-gradient(100deg, var(--amber) 10%, var(--rose) 90%); -webkit-background-clip: text; background-clip: text; color: transparent; }
.hero .sub { font-size: 1.08rem; color: rgba(255,253,248,0.88); margin-bottom: 34px; }
.hero-actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 40px; }
.hero .btn-ghost { border-color: rgba(255,253,248,0.5); color: var(--cream); }
.hero .btn-ghost:hover { border-color: var(--cream); }

.badge-row { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
.badge {
  display: inline-flex; align-items: center; gap: 6px; font-family: var(--font-mono); font-size: 0.78rem;
  padding: 7px 14px; border-radius: 999px; background: rgba(255,253,248,0.12); border: 1px solid rgba(255,253,248,0.25);
}
.badge .tick { color: #E9C46A; font-weight: 700; }

/* ---------- Secondary page hero (about / rooms / contact) ---------- */
.page-hero { padding: 168px 0 60px; }
.page-hero h1 { font-size: clamp(2rem, 4vw, 2.8rem); margin: 10px 0 12px; }
.page-hero p { color: var(--stone); max-width: 560px; font-size: 1.05rem; }

/* ============================================================
   ABOUT
   ============================================================ */
.about { display: grid; grid-template-columns: 1.05fr 0.95fr; gap: 64px; align-items: center; }
.about-copy p { color: var(--stone); margin-bottom: 18px; font-size: 1.02rem; }
.about-copy a { color: var(--amber-deep); text-decoration: underline; text-underline-offset: 3px; }
.about-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-top: 36px; }
.about-stats .stat { background: var(--cream); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px 12px; text-align: center; }
.about-stats .num { font-family: var(--font-mono); font-weight: 600; font-size: 1.5rem; color: var(--rose-deep); }
.about-stats .label { font-size: 0.76rem; color: var(--stone); margin-top: 4px; }

.about-visual { display: grid; grid-template-columns: 1.1fr 1fr; gap: 16px; }
.about-visual img { border-radius: var(--radius-lg); object-fit: cover; box-shadow: var(--shadow); }
.about-visual .tall { height: 100%; }
.about-visual .col { display: flex; flex-direction: column; gap: 16px; }
.about-visual .col img { height: calc(50% - 8px); }

@media (max-width: 900px) {
  .about, .about-visual { grid-template-columns: 1fr; }
  .about-stats { grid-template-columns: repeat(2, 1fr); }
}

/* ============================================================
   IDENTITY / BRANCH CARDS
   ============================================================ */
.identity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
.identity-card {
  background: var(--cream); border-radius: var(--radius-lg); padding: 34px;
  border-top: 5px solid var(--amber); box-shadow: var(--shadow);
}
.identity-card.girls { border-top-color: var(--rose); }
.identity-card h3 { font-size: 1.3rem; margin: 10px 0 16px; }
.identity-card .branch-loc { font-size: 0.9rem; color: var(--stone); margin-bottom: 6px; }
.identity-card ul { margin-top: 16px; display: grid; gap: 8px; }
.identity-card li { font-size: 0.95rem; color: var(--ink); padding-left: 20px; position: relative; }
.identity-card li::before { content: '—'; position: absolute; left: 0; color: var(--amber); }
.identity-card.girls li::before { color: var(--rose); }

@media (max-width: 800px) { .identity-grid { grid-template-columns: 1fr; } }

/* ============================================================
   WHY CHOOSE
   ============================================================ */
.why-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.why-item {
  display: flex; align-items: center; gap: 12px; background: var(--cream);
  border: 1px solid var(--line); border-radius: var(--radius); padding: 16px 18px;
}
.tick-circ {
  width: 30px; height: 30px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  border-radius: 50%; background: linear-gradient(120deg, var(--amber), var(--rose)); color: var(--cream); font-size: 0.85rem;
}
.why-item p { font-weight: 500; }
@media (max-width: 900px) { .why-grid { grid-template-columns: repeat(2, 1fr); } }

/* ============================================================
   TESTIMONIALS
   ============================================================ */
.testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.testi-card { background: var(--cream); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow); }
.testi-card .stars { color: var(--amber); letter-spacing: 2px; margin-bottom: 14px; }
.testi-card .quote { color: var(--ink); font-size: 1rem; margin-bottom: 16px; }
.testi-card .who { font-family: var(--font-mono); font-size: 0.8rem; color: var(--stone); }
@media (max-width: 900px) { .testi-grid { grid-template-columns: 1fr; } }

/* ============================================================
   FINAL CTA
   ============================================================ */
.final-cta { background: var(--ink); color: var(--cream); text-align: center; position: relative; overflow: hidden; }
.final-cta::before {
  content: ''; position: absolute; top: -40%; left: 50%; width: 900px; height: 900px; margin-left: -450px;
  background: radial-gradient(circle, rgba(184,60,82,0.35), transparent 60%); z-index: 0;
}
.final-cta .wrap { position: relative; z-index: 1; }
.final-cta .strap { font-family: var(--font-mono); font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; color: #E9C46A; margin-bottom: 16px; }
.final-cta h2 { font-size: clamp(1.9rem, 4vw, 2.6rem); color: var(--cream); margin-bottom: 14px; }
.final-cta .lead { color: rgba(255,253,248,0.78); max-width: 520px; margin: 0 auto 34px; }
.final-cta .hero-actions { justify-content: center; }
.final-cta .btn-ghost { border-color: rgba(255,253,248,0.4); color: var(--cream); }

/* ============================================================
   SAFETY (about page)
   ============================================================ */
.safety-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; }
.safety-list { display: grid; gap: 18px; margin-top: 8px; }
.safety-item { display: flex; gap: 14px; align-items: flex-start; }
.safety-item .ic { color: var(--rose); font-size: 1.1rem; line-height: 1.6; }
.safety-item h4 { font-size: 1rem; margin-bottom: 3px; }
.safety-item p { color: var(--stone); font-size: 0.92rem; }
.safety-visual img { border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); }
@media (max-width: 900px) { .safety-grid { grid-template-columns: 1fr; } }

/* ---------- Nearby chips ---------- */
.nearby-row { display: flex; flex-wrap: wrap; gap: 12px; }
.nearby-chip {
  font-family: var(--font-mono); font-size: 0.82rem; padding: 9px 16px; border-radius: 999px;
  background: var(--cream); border: 1px solid var(--line); color: var(--stone);
}

/* ============================================================
   CONTACT
   ============================================================ */
.contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 48px; }
.contact-card { background: var(--cream); border-radius: var(--radius-lg); padding: 30px; border-top: 5px solid var(--amber); box-shadow: var(--shadow); }
.contact-card.girls-c { border-top-color: var(--rose); }
.contact-card h2 { font-size: 1.15rem; margin: 8px 0 18px; }
.contact-row { display: flex; gap: 10px; align-items: center; padding: 8px 0; border-top: 1px dashed var(--line); font-size: 0.95rem; }
.contact-row:first-of-type { border-top: none; }
@media (max-width: 800px) { .contact-grid { grid-template-columns: 1fr; } }

.form-shell { background: var(--cream); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow); }
.form-shell h3 { font-size: 1.3rem; margin-bottom: 6px; }
.form-shell > p { color: var(--stone); margin-bottom: 24px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
.form-full { margin-bottom: 20px; }
.form-shell label { display: block; font-family: var(--font-mono); font-size: 0.78rem; letter-spacing: 0.04em; text-transform: uppercase; color: var(--stone); margin-bottom: 8px; }
.form-shell input, .form-shell select, .form-shell textarea {
  width: 100%; padding: 13px 16px; border: 1.5px solid var(--line); border-radius: 10px;
  background: var(--ivory); font-family: var(--font-body); font-size: 0.98rem; color: var(--ink);
  transition: border-color 0.2s ease;
}
.form-shell input:focus, .form-shell select:focus, .form-shell textarea:focus { border-color: var(--amber); outline: none; }
.form-submit {
  display: inline-flex; padding: 14px 32px; border-radius: 999px; font-family: var(--font-mono); font-weight: 600;
  background: linear-gradient(100deg, var(--amber), var(--rose)); color: var(--cream);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.form-submit:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
@media (max-width: 700px) { .form-row { grid-template-columns: 1fr; } }

/* ---------- FAQ accordion ---------- */
.faq-list { display: grid; gap: 12px; }
.faq-item { background: var(--cream); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; }
.faq-q { width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; text-align: left; font-weight: 600; }
.faq-item .plus { font-family: var(--font-mono); color: var(--amber-deep); transition: transform 0.3s ease; font-size: 1.2rem; }
.faq-item.open .plus { transform: rotate(45deg); }
.faq-a {
  max-height: 0; overflow: hidden; transition: max-height 0.35s ease, padding 0.35s ease;
  padding: 0 22px; color: var(--stone); font-size: 0.95rem;
}
.faq-item.open .faq-a { padding: 0 22px 20px; }

/* ============================================================
   ROOMS / FACILITIES
   ============================================================ */
.facility-tabs { display: flex; gap: 10px; margin-bottom: 32px; flex-wrap: wrap; }
.tab-btn {
  padding: 11px 22px; border-radius: 999px; border: 1.5px solid var(--line);
  font-family: var(--font-mono); font-size: 0.85rem; font-weight: 600; color: var(--stone);
  transition: all 0.25s ease;
}
.tab-btn.active { background: var(--ink); border-color: var(--ink); color: var(--cream); }
.tab-panel { display: none; }
.tab-panel.active { display: grid; }

.grid-cards { grid-template-columns: repeat(4, 1fr); gap: 18px; }
.fac-card {
  background: var(--cream); border: 1px solid var(--line); border-radius: var(--radius); padding: 22px 18px;
  text-align: center; transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}
.fac-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: var(--amber); }
.fac-card .ic { font-size: 1.6rem; margin-bottom: 10px; }
.fac-card h4 { font-size: 0.95rem; margin-bottom: 4px; }
.fac-card p { font-size: 0.82rem; color: var(--stone); }
@media (max-width: 900px) { .grid-cards { grid-template-columns: repeat(2, 1fr); } }

/* ---------- Food ---------- */
.food-grid { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 56px; align-items: center; }
.meal-tags { display: flex; gap: 10px; margin: 14px 0 18px; flex-wrap: wrap; }
.meal-tag { font-family: var(--font-mono); font-size: 0.8rem; padding: 8px 16px; border-radius: 999px; background: var(--amber-tint); color: var(--amber-deep); }
.plan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 16px; }
.plan-card { background: var(--cream); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px; }
.plan-card h4 { font-size: 0.98rem; margin: 10px 0 4px; }
.plan-card p { font-size: 0.82rem; color: var(--stone); }
.food-visual img { border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); }
@media (max-width: 900px) { .food-grid { grid-template-columns: 1fr; } .plan-grid { grid-template-columns: 1fr; } }

/* ============================================================
   FOOTER
   ============================================================ */
.site-footer { background: var(--ink); color: rgba(255,253,248,0.7); padding: 56px 0 28px; }
.site-footer .foot-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 40px; margin-bottom: 40px; }
.site-footer h5 { font-family: var(--font-mono); font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--cream); margin-bottom: 16px; }
.site-footer .brand { color: var(--cream); margin-bottom: 12px; }
.site-footer p { font-size: 0.92rem; line-height: 1.7; }
.site-footer ul { display: grid; gap: 10px; }
.site-footer a:hover { color: var(--cream); }
.foot-bottom { border-top: 1px solid rgba(255,253,248,0.14); padding-top: 22px; font-size: 0.8rem; font-family: var(--font-mono); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
@media (max-width: 800px) { .site-footer .foot-grid { grid-template-columns: 1fr; } }

/* ---------- Alerts (shared) ---------- */
.alert { padding: 14px 18px; border-radius: var(--radius); margin-bottom: 20px; font-size: 0.95rem; }
.alert-success { background: #DCEEDD; color: #245C2C; border: 1px solid #B7DAB9; }
.alert-error { background: var(--rose-tint); color: var(--rose-deep); border: 1px solid #E3B9C0; }

@media (max-width: 600px) {
  section { padding: 60px 0; }
  .hero-content { padding: 130px 20px 60px; }
}
   </style>

    @stack('styles')

    @hasSection('schema')
        @yield('schema')
    @endif
</head>
<body>

    <nav class="site-nav on-hero">
        <div class="wrap">
            <a class="brand" href="{{ route('home') }}">Sanjay <span class="amp">&amp;</span> Harini</a>
            <button class="nav-toggle" aria-label="Toggle menu">
                <span class="toggle-bar"></span><span class="toggle-bar"></span><span class="toggle-bar"></span>
            </button>
            <div class="nav-links">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('rooms') }}">Rooms</a>
                <a href="{{ route('contact') }}" class="cta">Book Now</a>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="wrap" style="position:relative;z-index:5;padding-top:100px;">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="wrap" style="position:relative;z-index:5;padding-top:100px;">
            <div class="alert alert-error">{{ session('error') }}</div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>