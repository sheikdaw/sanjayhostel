<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', "Sanjay & Harini Hostels | Boys & Girls PG in Alandur, St. Thomas Mount & Perungalathur, Chennai")</title>
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="@yield('canonical', 'https://www.sanjayandharinihostels.com/')">
  <meta name="description" content="@yield('meta_description', 'Sanjay Boys Hostel & Harini Girls Hostel offer safe, affordable PG accommodation in Alandur, St. Thomas Mount and Perungalathur, Chennai — AC/non-AC rooms, home-style food, WiFi, CCTV and 24/7 security for students and working professionals.')">
  <meta name="keywords" content="@yield('meta_keywords', "Sanjai Men's PG Hostel, best pg in alandur, men's PG in Alandur, men's PG in Guindy, best men's PG in Chennai, working men hostel Guindy, bachelor hostel Alandur, budget PG in Guindy Chennai, affordable PG for men Chennai, PG near Guindy Metro, PG near St Thomas Mount Metro, PG with food in Guindy, AC PG for men Chennai, single sharing PG Guindy, double sharing PG Alandur, triple sharing PG Chennai, safe men's hostel Chennai, furnished PG for working professionals, hostel near Guindy railway station")">
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('og_title', 'Sanjay & Harini Hostels | Boys & Girls PG in Alandur, St. Thomas Mount & Perungalathur')">
  <meta property="og:description" content="@yield('og_description', 'Safe, affordable PG for men and women in Alandur, St. Thomas Mount and Perungalathur, Chennai. AC/non-AC rooms, home food, WiFi, CCTV, 24/7 security.')">
  <meta property="og:url" content="@yield('canonical', 'https://www.sanjayandharinihostels.com/')">
  <meta property="og:image" content="https://www.sanjayandharinihostels.com/images/sanjay-harini-hostel-exterior.jpg">
  <meta property="og:site_name" content="Sanjay & Harini Hostels">
  <meta property="og:locale" content="en_IN">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('og_title', 'Sanjay & Harini Hostels | Boys & Girls PG in Alandur, St. Thomas Mount & Perungalathur')">
  <meta name="twitter:description" content="@yield('og_description', 'Safe, affordable PG for men and women in Alandur, St. Thomas Mount and Perungalathur, Chennai. AC/non-AC rooms, home food, WiFi, CCTV, 24/7 security.')">
  <meta name="twitter:image" content="https://www.sanjayandharinihostels.com/images/sanjay-harini-hostel-exterior.jpg">
  <meta name="geo.region" content="IN-TN">
  <meta name="geo.placename" content="Chennai">
  <meta name="theme-color" content="#C77B3D">
  <!-- NOTE: og:image path is a placeholder — swap in your real hosted image before launch -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@@9..144,300..900&family=Inter:wght@@400;500;600;700;800&display=swap" rel="stylesheet">
  @verbatim
  <style>
    :root {
      --ink: #1B1612;
      --ink-soft: #2A231D;
      --ivory: #FBF7F0;
      --ivory-dim: #F1E9DC;
      --amber: #C77B3D;
      --amber-deep: #A8612C;
      --rose: #8C2F39;
      --rose-deep: #74232B;
      --sage: #3E5C4E;
      --sage-light: #5A7C6C;
      --stone: #D9CBB4;
      --stone-dim: #B9A98C;
      --font-display: 'Fraunces', serif;
      --font-body: 'Inter', sans-serif;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    html { scroll-behavior:smooth; }
    body { font-family:var(--font-body); background:var(--ink); color:var(--ivory); line-height:1.6; overflow-x:hidden; }
    img { max-width:100%; display:block; }
    a { color:inherit; text-decoration:none; }
    button { font-family:inherit; cursor:pointer; border:none; background:none; }
    ul { list-style:none; }
    .wrap { max-width:1240px; margin:0 auto; padding:0 28px; }
    .seam { position:relative; }
    .seam::before { content:''; position:absolute; top:0; bottom:0; left:50%; width:3px; background:linear-gradient(180deg,var(--amber),var(--rose)); transform:translateX(-50%); opacity:.55; z-index:2; }
    @media(max-width:860px){ .seam::before { display:none; } }
    .eyebrow { font-family:var(--font-body); font-size:.72rem; letter-spacing:.18em; text-transform:uppercase; font-weight:700; color:var(--stone-dim); }
    h1,h2,h3,h4 { font-family:var(--font-display); font-weight:600; letter-spacing:-0.01em; }
    header { position:fixed; top:0; left:0; right:0; z-index:100; background:rgba(27,22,18,.86); backdrop-filter:blur(14px); border-bottom:1px solid rgba(217,203,180,.12); }
    nav { display:flex; align-items:center; justify-content:space-between; padding:16px 28px; max-width:1240px; margin:0 auto; }
    .brand-mark { display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-size:1.15rem; font-weight:700; }
    .brand-mark .split { display:inline-flex; width:14px; height:14px; border-radius:50%; background:linear-gradient(135deg,var(--amber) 50%,var(--rose) 50%); }
    .nav-links { display:flex; gap:32px; align-items:center; }
    .nav-links a { font-size:.88rem; font-weight:500; color:var(--stone); transition:color .2s; }
    .nav-links a:hover, .nav-links a.active { color:var(--ivory); }
    .nav-cta { background:var(--amber); color:var(--ink); padding:10px 20px; border-radius:999px; font-weight:700; font-size:.85rem; transition:transform .2s, background .2s; }
    .nav-cta:hover { background:var(--ivory); transform:translateY(-1px); }
    .nav-toggle { display:none; font-size:1.6rem; color:var(--ivory); background:none; }
    @media(max-width:900px){ .nav-links { position:fixed; top:64px; left:0; right:0; background:var(--ink-soft); flex-direction:column; gap:0; max-height:0; overflow:hidden; transition:max-height .3s ease; border-bottom:1px solid rgba(217,203,180,.12); } .nav-links.open { max-height:420px; } .nav-links a { width:100%; padding:16px 28px; border-bottom:1px solid rgba(217,203,180,.08); } .nav-cta { margin:16px 28px; display:inline-block; } .nav-toggle { display:block; } }
    .hero { padding:148px 0 96px; position:relative; overflow:hidden; }
    .hero-bg { position:absolute; inset:0; display:grid; grid-template-columns:1fr 1fr; z-index:0; }
    .hero-bg .side { position:relative; overflow:hidden; }
    .hero-bg .side img { width:100%; height:100%; object-fit:cover; opacity:.32; }
    .hero-bg .side.left::after { content:''; position:absolute; inset:0; background:linear-gradient(120deg,var(--ink) 30%,rgba(27,22,18,.5) 100%); }
    .hero-bg .side.right::after { content:''; position:absolute; inset:0; background:linear-gradient(240deg,var(--ink) 30%,rgba(27,22,18,.5) 100%); }
    .hero-content { position:relative; z-index:1; max-width:880px; margin:0 auto; text-align:center; padding:0 28px; }
    .hero-tag { display:inline-flex; align-items:center; gap:8px; padding:7px 16px; border:1px solid rgba(217,203,180,.3); border-radius:999px; font-size:.76rem; letter-spacing:.1em; text-transform:uppercase; font-weight:600; color:var(--stone); margin-bottom:28px; }
    .hero h1 { font-size:clamp(2.6rem,6vw,4.6rem); line-height:1.04; margin-bottom:22px; }
    .hero h1 em { font-style:italic; background:linear-gradient(90deg,var(--amber),var(--rose)); -webkit-background-clip:text; background-clip:text; color:transparent; }
    .hero p.sub { font-size:1.15rem; color:var(--stone); max-width:620px; margin:0 auto 40px; }
    .hero-actions { display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-bottom:56px; }
    .btn { padding:15px 30px; border-radius:10px; font-weight:700; font-size:.95rem; transition:transform .2s,box-shadow .2s,background .2s; display:inline-flex; align-items:center; gap:8px; }
    .btn-primary { background:linear-gradient(95deg,var(--amber),var(--rose)); color:var(--ivory); }
    .btn-primary:hover { transform:translateY(-2px); box-shadow:0 12px 24px -8px rgba(199,123,61,.5); }
    .btn-ghost { background:transparent; border:1.5px solid rgba(217,203,180,.35); color:var(--ivory); }
    .btn-ghost:hover { border-color:var(--ivory); background:rgba(255,255,255,.04); }
    .badge-row { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
    .badge { display:flex; align-items:center; gap:7px; background:rgba(217,203,180,.07); border:1px solid rgba(217,203,180,.16); padding:8px 14px; border-radius:999px; font-size:.82rem; font-weight:500; color:var(--stone); }
    .badge .tick { color:var(--sage-light); font-weight:800; }
    section { padding:108px 0; position:relative; }
    .page-hero { padding:148px 0 64px; text-align:center; }
    .page-hero h1 { font-size:clamp(2.2rem,4.6vw,3.4rem); margin-bottom:16px; }
    .page-hero p { color:var(--stone); max-width:640px; margin:0 auto; font-size:1.05rem; }
    .section-head { max-width:680px; margin:0 auto 64px; text-align:center; }
    .section-head h2 { font-size:clamp(1.9rem,3.4vw,2.7rem); margin:14px 0 16px; color:var(--ivory); }
    .section-head p { color:var(--stone); font-size:1.05rem; }
    .section-head .eyebrow { display:block; margin-bottom:4px; }
    .panel-ivory { background:var(--ivory); color:var(--ink); }
    .panel-ivory .eyebrow { color:var(--stone-dim); }
    .panel-ivory .section-head h2, .panel-ivory .page-hero h1 { color:var(--ink); }
    .panel-ivory .section-head p, .panel-ivory .page-hero p { color:var(--ink-soft); }
    .about { display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center; }
    .about-copy .eyebrow { margin-bottom:14px; display:block; }
    .about-copy h2 { font-size:clamp(1.8rem,3.2vw,2.5rem); margin-bottom:20px; }
    .about-copy p { color:var(--ink-soft); margin-bottom:16px; font-size:1.02rem; }
    .about-stats { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:32px; }
    .stat { padding:18px 0; border-top:2px solid var(--ink); }
    .stat .num { font-family:var(--font-display); font-size:2.1rem; font-weight:700; }
    .stat .label { font-size:.82rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.05em; }
    .about-visual { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .about-visual img { border-radius:16px; width:100%; height:100%; object-fit:cover; }
    .about-visual .tall { grid-row:span 2; height:100%; }
    .about-visual .col { display:flex; flex-direction:column; gap:14px; }
    .about-visual img.a { border:3px solid var(--amber); }
    .about-visual img.r { border:3px solid var(--rose); }
    @media(max-width:860px){ .about { grid-template-columns:1fr; gap:40px; } }
    .identity-grid { display:grid; grid-template-columns:1fr 1fr; gap:0; border-radius:24px; overflow:hidden; border:1px solid rgba(217,203,180,.15); }
    .identity-card { padding:48px 40px; position:relative; }
    .identity-card.boys { background:linear-gradient(160deg,rgba(199,123,61,.16),rgba(199,123,61,.03)); }
    .identity-card.girls { background:linear-gradient(200deg,rgba(140,47,57,.18),rgba(140,47,57,.04)); }
    .identity-card .tag-pill { display:inline-block; padding:5px 14px; border-radius:999px; font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:18px; }
    .boys .tag-pill { background:var(--amber); color:var(--ink); }
    .girls .tag-pill { background:var(--rose); color:var(--ivory); }
    .identity-card h1, .identity-card h3 { font-size:1.8rem; margin-bottom:10px; }
    .identity-card p { color:var(--stone); margin-bottom:22px; }
    .identity-card ul { display:flex; flex-direction:column; gap:10px; }
    .identity-card li { display:flex; align-items:baseline; gap:10px; font-size:.92rem; color:var(--ivory); }
    .identity-card li::before { content:'—'; color:var(--stone-dim); }
    @media(max-width:860px){ .identity-grid { grid-template-columns:1fr; } }
    .facility-tabs { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-bottom:48px; }
    .tab-btn { padding:11px 22px; border-radius:999px; font-size:.88rem; font-weight:600; border:1px solid var(--stone-dim); color:var(--ink-soft); transition:all .2s; }
    .tab-btn.active { background:var(--ink); color:var(--ivory); border-color:var(--ink); }
    .grid-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
    .fac-card { background:rgba(255,255,255,.65); border:1px solid rgba(27,22,18,.08); border-radius:16px; padding:26px 22px; transition:transform .25s, box-shadow .25s, border-color .25s; }
    .fac-card:hover { transform:translateY(-4px); box-shadow:0 16px 32px -16px rgba(27,22,18,.25); border-color:var(--amber); }
    .fac-card .ic { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:var(--ink); color:var(--ivory); font-size:1.1rem; margin-bottom:16px; }
    .fac-card h4 { font-size:1.02rem; margin-bottom:8px; color:var(--ink); }
    .fac-card p { font-size:.86rem; color:var(--ink-soft); }
    .tab-panel { display:none; }
    .tab-panel.active { display:grid; }
    @media(max-width:980px){ .grid-cards { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:560px){ .grid-cards { grid-template-columns:1fr; } }
    .safety { background:linear-gradient(160deg,var(--ink-soft),var(--ink)); }
    .safety-grid { display:grid; grid-template-columns:1.1fr 1fr; gap:60px; align-items:center; }
    .safety-list { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .safety-item { display:flex; gap:14px; align-items:flex-start; padding:18px; border-radius:14px; background:rgba(217,203,180,.05); border:1px solid rgba(217,203,180,.1); }
    .safety-item .ic { color:var(--sage-light); font-size:1.2rem; flex-shrink:0; }
    .safety-item h4 { font-size:.95rem; margin-bottom:4px; color:var(--ivory); font-family:var(--font-body); font-weight:700; }
    .safety-item p { font-size:.82rem; color:var(--stone); }
    .safety-visual { border-radius:20px; overflow:hidden; border:1px solid rgba(217,203,180,.15); position:relative; }
    .safety-visual img { width:100%; height:420px; object-fit:cover; opacity:.85; }
    .safety-visual::after { content:'24 / 7 MONITORED'; position:absolute; bottom:20px; left:20px; background:var(--sage); color:var(--ivory); padding:8px 16px; border-radius:8px; font-size:.78rem; font-weight:700; letter-spacing:.04em; }
    @media(max-width:860px){ .safety-grid { grid-template-columns:1fr; } .safety-list { grid-template-columns:1fr; } }
    .food-grid { display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:start; }
    .food-visual { position:relative; border-radius:20px; overflow:hidden; }
    .food-visual img { width:100%; height:480px; object-fit:cover; }
    .meal-tags { display:flex; gap:10px; flex-wrap:wrap; margin:24px 0 32px; }
    .meal-tag { background:var(--ivory-dim); border-radius:10px; padding:14px 18px; font-size:.85rem; font-weight:600; color:var(--ink); flex:1; min-width:120px; text-align:center; }
    .plan-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-top:20px; }
    .plan-card { border:1.5px solid var(--stone-dim); border-radius:16px; padding:24px 20px; transition:border-color .2s,transform .2s; }
    .plan-card:hover { border-color:var(--sage); transform:translateY(-3px); }
    .plan-card .tag-pill { background:var(--sage); color:var(--ivory); font-size:.68rem; padding:4px 10px; border-radius:999px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; display:inline-block; margin-bottom:14px; }
    .plan-card h4 { font-family:var(--font-body); font-weight:700; font-size:1.02rem; margin-bottom:6px; color:var(--ink); }
    .plan-card p { font-size:.85rem; color:var(--ink-soft); }
    @media(max-width:980px){ .plan-grid { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:860px){ .food-grid { grid-template-columns:1fr; } }
    .why-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:rgba(217,203,180,.12); border:1px solid rgba(217,203,180,.12); border-radius:18px; overflow:hidden; }
    .why-item { background:var(--ink); padding:30px 22px; display:flex; flex-direction:column; gap:10px; }
    .why-item .tick-circ { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,var(--amber),var(--rose)); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.9rem; color:var(--ivory); }
    .why-item p { font-size:.9rem; font-weight:600; color:var(--ivory); }
    @media(max-width:860px){ .why-grid { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:520px){ .why-grid { grid-template-columns:1fr; } }
    .nearby-row { display:flex; flex-wrap:wrap; gap:12px; justify-content:center; }
    .nearby-chip { padding:10px 20px; border-radius:999px; background:rgba(217,203,180,.06); border:1px solid rgba(217,203,180,.18); font-size:.88rem; color:var(--stone); }
    .testi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:22px; }
    .testi-card { background:rgba(255,255,255,.7); border-radius:18px; padding:30px 26px; border:1px solid rgba(27,22,18,.06); }
    .stars { color:var(--amber-deep); font-size:.95rem; margin-bottom:14px; letter-spacing:2px; }
    .testi-card p.quote { font-size:1.0rem; color:var(--ink); font-style:italic; margin-bottom:18px; }
    .testi-card .who { font-size:.82rem; font-weight:700; color:var(--ink-soft); }
    @media(max-width:860px){ .testi-grid { grid-template-columns:1fr; } }
    .gallery-grid { display:grid; grid-template-columns:repeat(4,1fr); grid-auto-rows:160px; gap:12px; }
    .gallery-grid img { width:100%; height:100%; object-fit:cover; border-radius:12px; transition:transform .35s; }
    .gallery-grid a { overflow:hidden; border-radius:12px; display:block; }
    .gallery-grid a:hover img { transform:scale(1.08); }
    .gallery-grid .g1 { grid-column:span 2; grid-row:span 2; }
    .gallery-grid .g5 { grid-column:span 2; }
    @media(max-width:860px){ .gallery-grid { grid-template-columns:repeat(2,1fr); grid-auto-rows:140px; } .gallery-grid .g1 { grid-column:span 2; grid-row:span 2; } .gallery-grid .g5 { grid-column:span 2; } }
    .faq-list { max-width:760px; margin:0 auto; }
    .faq-item { border-bottom:1px solid rgba(217,203,180,.15); }
    .faq-q { width:100%; display:flex; justify-content:space-between; align-items:center; padding:22px 4px; font-size:1.02rem; font-weight:600; color:var(--ivory); text-align:left; }
    .faq-q .plus { font-size:1.4rem; color:var(--stone-dim); transition:transform .25s; }
    .faq-item.open .plus { transform:rotate(45deg); color:var(--amber); }
    .faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; color:var(--stone); font-size:.92rem; }
    .faq-item.open .faq-a { max-height:200px; padding-bottom:22px; }
    .contact-grid { display:grid; grid-template-columns:1fr 1fr; gap:48px; }
    .contact-card { background:var(--ivory); color:var(--ink); border-radius:20px; padding:36px; }
    .contact-card .tag-pill { font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:5px 13px; border-radius:999px; display:inline-block; margin-bottom:16px; }
    .contact-card.boys-c .tag-pill { background:var(--amber); color:var(--ink); }
    .contact-card.girls-c .tag-pill { background:var(--rose); color:var(--ivory); }
    .contact-card h2, .contact-card h3 { font-size:1.5rem; margin-bottom:18px; }
    .contact-row { display:flex; gap:12px; margin-bottom:14px; font-size:.94rem; align-items:flex-start; }
    .contact-row .ic { color:var(--ink-soft); }
    .form-shell { background:var(--ivory-dim); border-radius:20px; padding:36px; color:var(--ink); }
    .form-shell h3 { color:var(--ink); font-size:1.4rem; margin-bottom:8px; }
    .form-shell p { color:var(--ink-soft); font-size:.9rem; margin-bottom:24px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
    .form-shell input, .form-shell select, .form-shell textarea { width:100%; padding:13px 14px; border-radius:10px; border:1.5px solid rgba(27,22,18,.15); font-family:inherit; font-size:.92rem; background:#fff; color:var(--ink); }
    .form-shell input:focus, .form-shell select:focus, .form-shell textarea:focus { outline:2px solid var(--amber); outline-offset:1px; border-color:var(--amber); }
    .form-shell label { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--ink-soft); display:block; margin-bottom:6px; }
    .form-full { margin-bottom:14px; }
    .form-submit { width:100%; padding:15px; border-radius:10px; background:linear-gradient(95deg,var(--amber),var(--rose)); color:var(--ivory); font-weight:700; font-size:.96rem; margin-top:6px; transition:transform .2s; }
    .form-submit:hover { transform:translateY(-2px); }
    @media(max-width:860px){ .contact-grid { grid-template-columns:1fr; } .form-row { grid-template-columns:1fr; } }
    .final-cta { text-align:center; background:radial-gradient(ellipse at center,rgba(199,123,61,.12),transparent 70%); }
    .final-cta h2 { font-size:clamp(2rem,4vw,3rem); margin-bottom:16px; }
    .final-cta .strap { color:var(--stone-dim); font-weight:700; letter-spacing:.06em; text-transform:uppercase; font-size:.82rem; margin-bottom:18px; }
    .final-cta p.lead { max-width:560px; margin:0 auto 36px; color:var(--stone); }
    footer { background:var(--ink-soft); padding:56px 0 28px; border-top:1px solid rgba(217,203,180,.1); }
    .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr; gap:40px; margin-bottom:40px; }
    .footer-grid h4 { font-size:.85rem; text-transform:uppercase; letter-spacing:.06em; color:var(--stone-dim); margin-bottom:16px; }
    .footer-grid ul li { margin-bottom:10px; font-size:.9rem; color:var(--stone); }
    .footer-bottom { border-top:1px solid rgba(217,203,180,.1); padding-top:24px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; font-size:.8rem; color:var(--stone-dim); }
    @media(max-width:760px){ .footer-grid { grid-template-columns:1fr; } }
    .wa-float { position:fixed; bottom:24px; right:24px; z-index:90; width:58px; height:58px; border-radius:50%; background:#25D366; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 30px -8px rgba(0,0,0,.5); font-size:1.6rem; animation:pulse 2.4s infinite; }
    @keyframes pulse { 0% { box-shadow:0 0 0 0 rgba(37,211,102,.5); } 70% { box-shadow:0 0 0 14px rgba(37,211,102,0); } 100% { box-shadow:0 0 0 0 rgba(37,211,102,0); } }
    .reveal { opacity:0; transform:translateY(24px); transition:opacity .6s ease, transform .6s ease; }
    .reveal.in { opacity:1; transform:translateY(0); }
    @media(prefers-reduced-motion:reduce){ .reveal { transition:none; opacity:1; transform:none; } .wa-float { animation:none; } html { scroll-behavior:auto; } }
    .branch-loc { font-size:.95rem; color:var(--stone); border-left:3px solid var(--amber); padding-left:14px; margin-bottom:18px; }
  </style>
  @endverbatim

  <!--
    SCHEMA NOTE: Replace telephone / streetAddress / postalCode below with your
    real, verified details for each branch before publishing.
  -->
  @verbatim
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "https://www.sanjayandharinihostels.com/#organization",
        "name": "Sanjay & Harini Hostels",
        "url": "https://www.sanjayandharinihostels.com/",
        "logo": "https://www.sanjayandharinihostels.com/images/sanjay-harini-hostels-logo.png",
        "sameAs": []
      }
    ]
  }
  </script>
  @endverbatim
  @yield('schema')
</head>
<body>
  <header>
    <nav>
      <div class="brand-mark"><span class="split"></span> Sanjay & Harini Hostels</div>
      <ul class="nav-links" id="navLinks">
        <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
        <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
        <li><a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms') ? 'active' : '' }}">Rooms & Facilities</a></li>
        <li><a href="{{ route('gallery') }}" class="{{ request()->routeIs('gallery') ? 'active' : '' }}">Gallery</a></li>
        <li><a href="{{ route('contact') }}" class="nav-cta">Book Now</a></li>
      </ul>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
    </nav>
  </header>

  @yield('content')

  <footer>
    <div class="wrap">
      <div class="footer-grid">
        <div>
          <div class="brand-mark" style="margin-bottom:14px;"><span class="split"></span> Sanjay & Harini Hostels</div>
          <p style="color:var(--stone);font-size:.9rem;">PG in Alandur, St. Thomas Mount, Perungalathur – AC/Non-AC, WiFi, CCTV, home food, near metro & railway.</p>
        </div>
        <div>
          <h4>Quick Links</h4>
          <ul>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('about') }}">About</a></li>
            <li><a href="{{ route('rooms') }}">Rooms & Facilities</a></li>
            <li><a href="{{ route('gallery') }}">Gallery</a></li>
          </ul>
        </div>
        <div>
          <h4>Support</h4>
          <ul>
            <li><a href="{{ route('contact') }}#faq">FAQ</a></li>
            <li><a href="{{ route('contact') }}">Contact</a></li>
            <li><a href="{{ route('contact') }}">Book a Room</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© {{ date('Y') }} Sanjay Boys Hostel & Harini Girls Hostel. All rights reserved.</span>
        <span>Best PG in Alandur, St. Thomas Mount & Perungalathur.</span>
      </div>
    </div>
  </footer>

  <a class="wa-float" href="https://wa.me/919876543210" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">💬</a>

  @verbatim
  <script>
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle) {
      navToggle.addEventListener('click', () => navLinks.classList.toggle('open'));
      navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('open')));
    }

    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
      });
    });

    document.querySelectorAll('.faq-item').forEach(item => {
      const q = item.querySelector('.faq-q');
      if (q) {
        q.addEventListener('click', () => {
          const wasOpen = item.classList.contains('open');
          document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
          if (!wasOpen) item.classList.add('open');
        });
      }
    });

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('in');
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  </script>
  @endverbatim
  @yield('scripts')
</body>
</html>