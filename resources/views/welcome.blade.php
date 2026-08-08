<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sanjay Boys Hostel & Harini Girls Hostel — Your Home Away From Home</title>
<meta name="description" content="Safe, comfortable, affordable hostel accommodation for men and women in India. AC & Non-AC rooms, home-style meals, daily lunch box delivery, 24/7 security.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300..900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1B1612;
    --ink-soft:#2A231D;
    --ivory:#FBF7F0;
    --ivory-dim:#F1E9DC;
    --amber:#C77B3D;
    --amber-deep:#A8612C;
    --rose:#8C2F39;
    --rose-deep:#74232B;
    --sage:#3E5C4E;
    --sage-light:#5A7C6C;
    --stone:#D9CBB4;
    --stone-dim:#B9A98C;
    --font-display:'Fraunces', serif;
    --font-body:'Inter', sans-serif;
  }

  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    font-family:var(--font-body);
    background:var(--ink);
    color:var(--ivory);
    line-height:1.6;
    overflow-x:hidden;
  }
  img{max-width:100%;display:block;}
  a{color:inherit;text-decoration:none;}
  button{font-family:inherit;cursor:pointer;border:none;background:none;}
  ul{list-style:none;}

  .wrap{max-width:1240px;margin:0 auto;padding:0 28px;}

  /* ---------- Seam motif ---------- */
  .seam{
    position:relative;
  }
  .seam::before{
    content:'';
    position:absolute;
    top:0;bottom:0;left:50%;
    width:3px;
    background:linear-gradient(180deg, var(--amber), var(--rose));
    transform:translateX(-50%);
    opacity:.55;
    z-index:2;
  }
  @media(max-width:860px){
    .seam::before{display:none;}
  }

  .eyebrow{
    font-family:var(--font-body);
    font-size:.72rem;
    letter-spacing:.18em;
    text-transform:uppercase;
    font-weight:700;
    color:var(--stone-dim);
  }

  h1,h2,h3,h4{font-family:var(--font-display);font-weight:600;letter-spacing:-0.01em;}

  /* ---------- Nav ---------- */
  header{
    position:fixed;top:0;left:0;right:0;
    z-index:100;
    background:rgba(27,22,18,.86);
    backdrop-filter:blur(14px);
    border-bottom:1px solid rgba(217,203,180,.12);
  }
  nav{
    display:flex;align-items:center;justify-content:space-between;
    padding:16px 28px;
    max-width:1240px;margin:0 auto;
  }
  .brand-mark{
    display:flex;align-items:center;gap:10px;
    font-family:var(--font-display);font-size:1.15rem;font-weight:700;
  }
  .brand-mark .split{
    display:inline-flex;width:14px;height:14px;border-radius:50%;
    background:linear-gradient(135deg, var(--amber) 50%, var(--rose) 50%);
  }
  .nav-links{display:flex;gap:32px;align-items:center;}
  .nav-links a{
    font-size:.88rem;font-weight:500;color:var(--stone);
    transition:color .2s;
  }
  .nav-links a:hover{color:var(--ivory);}
  .nav-cta{
    background:var(--amber);
    color:var(--ink);
    padding:10px 20px;
    border-radius:999px;
    font-weight:700;
    font-size:.85rem;
    transition:transform .2s, background .2s;
  }
  .nav-cta:hover{background:var(--ivory);transform:translateY(-1px);}
  .nav-toggle{display:none;font-size:1.6rem;color:var(--ivory);background:none;}

  @media(max-width:900px){
    .nav-links{
      position:fixed;top:64px;left:0;right:0;
      background:var(--ink-soft);
      flex-direction:column;
      gap:0;
      max-height:0;
      overflow:hidden;
      transition:max-height .3s ease;
      border-bottom:1px solid rgba(217,203,180,.12);
    }
    .nav-links.open{max-height:420px;}
    .nav-links a{width:100%;padding:16px 28px;border-bottom:1px solid rgba(217,203,180,.08);}
    .nav-cta{margin:16px 28px;display:inline-block;}
    .nav-toggle{display:block;}
  }

  /* ---------- Hero ---------- */
  .hero{
    position:relative;
    padding:148px 0 96px;
    overflow:hidden;
  }
  .hero-bg{
    position:absolute;inset:0;
    display:grid;grid-template-columns:1fr 1fr;
    z-index:0;
  }
  .hero-bg .side{
    position:relative;
    overflow:hidden;
  }
  .hero-bg .side img{
    width:100%;height:100%;object-fit:cover;
    opacity:.32;
  }
  .hero-bg .side.left::after{
    content:'';position:absolute;inset:0;
    background:linear-gradient(120deg, var(--ink) 30%, rgba(27,22,18,.5) 100%);
  }
  .hero-bg .side.right::after{
    content:'';position:absolute;inset:0;
    background:linear-gradient(240deg, var(--ink) 30%, rgba(27,22,18,.5) 100%);
  }
  .hero-content{
    position:relative;z-index:1;
    max-width:880px;margin:0 auto;
    text-align:center;
    padding:0 28px;
  }
  .hero-tag{
    display:inline-flex;align-items:center;gap:8px;
    padding:7px 16px;
    border:1px solid rgba(217,203,180,.3);
    border-radius:999px;
    font-size:.76rem;letter-spacing:.1em;text-transform:uppercase;
    font-weight:600;color:var(--stone);
    margin-bottom:28px;
  }
  .hero h1{
    font-size:clamp(2.6rem, 6vw, 4.6rem);
    line-height:1.04;
    margin-bottom:22px;
  }
  .hero h1 em{
    font-style:italic;
    background:linear-gradient(90deg, var(--amber), var(--rose));
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
  }
  .hero p.sub{
    font-size:1.15rem;
    color:var(--stone);
    max-width:620px;
    margin:0 auto 40px;
  }
  .hero-actions{
    display:flex;gap:16px;justify-content:center;flex-wrap:wrap;
    margin-bottom:56px;
  }
  .btn{
    padding:15px 30px;
    border-radius:10px;
    font-weight:700;
    font-size:.95rem;
    transition:transform .2s, box-shadow .2s, background .2s;
    display:inline-flex;align-items:center;gap:8px;
  }
  .btn-primary{
    background:linear-gradient(95deg, var(--amber), var(--rose));
    color:var(--ivory);
  }
  .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 24px -8px rgba(199,123,61,.5);}
  .btn-ghost{
    background:transparent;
    border:1.5px solid rgba(217,203,180,.35);
    color:var(--ivory);
  }
  .btn-ghost:hover{border-color:var(--ivory);background:rgba(255,255,255,.04);}

  .badge-row{
    display:flex;flex-wrap:wrap;gap:10px;justify-content:center;
  }
  .badge{
    display:flex;align-items:center;gap:7px;
    background:rgba(217,203,180,.07);
    border:1px solid rgba(217,203,180,.16);
    padding:8px 14px;
    border-radius:999px;
    font-size:.82rem;font-weight:500;
    color:var(--stone);
  }
  .badge .tick{color:var(--sage-light);font-weight:800;}

  /* ---------- Section shells ---------- */
  section{padding:108px 0;position:relative;}
  .section-head{
    max-width:680px;margin:0 auto 64px;text-align:center;
  }
  .section-head h2{
    font-size:clamp(1.9rem, 3.4vw, 2.7rem);
    margin:14px 0 16px;
    color:var(--ivory);
  }
  .section-head p{color:var(--stone);font-size:1.05rem;}
  .section-head .eyebrow{display:block;margin-bottom:4px;}

  .panel-ivory{background:var(--ivory);color:var(--ink);}
  .panel-ivory .eyebrow{color:var(--stone-dim);}
  .panel-ivory .section-head h2{color:var(--ink);}
  .panel-ivory .section-head p{color:var(--ink-soft);}

  /* ---------- About ---------- */
  .about{
    display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;
  }
  .about-copy .eyebrow{margin-bottom:14px;display:block;}
  .about-copy h2{font-size:clamp(1.8rem,3.2vw,2.5rem);margin-bottom:20px;}
  .about-copy p{color:var(--ink-soft);margin-bottom:16px;font-size:1.02rem;}
  .about-stats{
    display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:32px;
  }
  .stat{
    padding:18px 0;border-top:2px solid var(--ink);
  }
  .stat .num{font-family:var(--font-display);font-size:2.1rem;font-weight:700;}
  .stat .label{font-size:.82rem;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.05em;}
  .about-visual{
    display:grid;grid-template-columns:1fr 1fr;gap:14px;
  }
  .about-visual img{
    border-radius:16px;width:100%;height:100%;object-fit:cover;
  }
  .about-visual .tall{grid-row:span 2;height:100%;}
  .about-visual .col{display:flex;flex-direction:column;gap:14px;}
  .about-visual img.a{border:3px solid var(--amber);}
  .about-visual img.r{border:3px solid var(--rose);}

  @media(max-width:860px){
    .about{grid-template-columns:1fr;gap:40px;}
  }

  /* ---------- Two-hostel identity strip ---------- */
  .identity-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:0;
    border-radius:24px;overflow:hidden;
    border:1px solid rgba(217,203,180,.15);
  }
  .identity-card{
    padding:48px 40px;
    position:relative;
  }
  .identity-card.boys{background:linear-gradient(160deg, rgba(199,123,61,.16), rgba(199,123,61,.03));}
  .identity-card.girls{background:linear-gradient(200deg, rgba(140,47,57,.18), rgba(140,47,57,.04));}
  .identity-card .tag-pill{
    display:inline-block;padding:5px 14px;border-radius:999px;
    font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
    margin-bottom:18px;
  }
  .boys .tag-pill{background:var(--amber);color:var(--ink);}
  .girls .tag-pill{background:var(--rose);color:var(--ivory);}
  .identity-card h3{font-size:1.8rem;margin-bottom:10px;}
  .identity-card p{color:var(--stone);margin-bottom:22px;}
  .identity-card ul{display:flex;flex-direction:column;gap:10px;}
  .identity-card li{display:flex;align-items:baseline;gap:10px;font-size:.92rem;color:var(--ivory);}
  .identity-card li::before{content:'—';color:var(--stone-dim);}

  @media(max-width:860px){
    .identity-grid{grid-template-columns:1fr;}
  }

  /* ---------- Facilities cards ---------- */
  .facility-tabs{
    display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:48px;
  }
  .tab-btn{
    padding:11px 22px;border-radius:999px;font-size:.88rem;font-weight:600;
    border:1px solid var(--stone-dim);color:var(--ink-soft);
    transition:all .2s;
  }
  .tab-btn.active{background:var(--ink);color:var(--ivory);border-color:var(--ink);}

  .grid-cards{
    display:grid;grid-template-columns:repeat(4,1fr);gap:18px;
  }
  .fac-card{
    background:rgba(255,255,255,.65);
    border:1px solid rgba(27,22,18,.08);
    border-radius:16px;
    padding:26px 22px;
    transition:transform .25s, box-shadow .25s, border-color .25s;
  }
  .fac-card:hover{
    transform:translateY(-4px);
    box-shadow:0 16px 32px -16px rgba(27,22,18,.25);
    border-color:var(--amber);
  }
  .fac-card .ic{
    width:42px;height:42px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    background:var(--ink);color:var(--ivory);
    font-size:1.1rem;margin-bottom:16px;
  }
  .fac-card h4{font-size:1.02rem;margin-bottom:8px;color:var(--ink);}
  .fac-card p{font-size:.86rem;color:var(--ink-soft);}

  .tab-panel{display:none;}
  .tab-panel.active{display:grid;}

  @media(max-width:980px){.grid-cards{grid-template-columns:repeat(2,1fr);}}
  @media(max-width:560px){.grid-cards{grid-template-columns:1fr;}}

  /* ---------- Safety ---------- */
  .safety{
    background:linear-gradient(160deg, var(--ink-soft), var(--ink));
  }
  .safety-grid{
    display:grid;grid-template-columns:1.1fr 1fr;gap:60px;align-items:center;
  }
  .safety-list{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
  .safety-item{
    display:flex;gap:14px;align-items:flex-start;
    padding:18px;border-radius:14px;
    background:rgba(217,203,180,.05);
    border:1px solid rgba(217,203,180,.1);
  }
  .safety-item .ic{color:var(--sage-light);font-size:1.2rem;flex-shrink:0;}
  .safety-item h4{font-size:.95rem;margin-bottom:4px;color:var(--ivory);font-family:var(--font-body);font-weight:700;}
  .safety-item p{font-size:.82rem;color:var(--stone);}
  .safety-visual{
    border-radius:20px;overflow:hidden;
    border:1px solid rgba(217,203,180,.15);
    position:relative;
  }
  .safety-visual img{width:100%;height:420px;object-fit:cover;opacity:.85;}
  .safety-visual::after{
    content:'24 / 7 MONITORED';
    position:absolute;bottom:20px;left:20px;
    background:var(--sage);color:var(--ivory);
    padding:8px 16px;border-radius:8px;
    font-size:.78rem;font-weight:700;letter-spacing:.04em;
  }
  @media(max-width:860px){
    .safety-grid{grid-template-columns:1fr;}
    .safety-list{grid-template-columns:1fr;}
  }

  /* ---------- Food ---------- */
  .food-grid{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start;}
  .food-visual{position:relative;border-radius:20px;overflow:hidden;}
  .food-visual img{width:100%;height:480px;object-fit:cover;}
  .meal-tags{display:flex;gap:10px;flex-wrap:wrap;margin:24px 0 32px;}
  .meal-tag{
    background:var(--ivory-dim);border-radius:10px;padding:14px 18px;
    font-size:.85rem;font-weight:600;color:var(--ink);
    flex:1;min-width:120px;text-align:center;
  }

  .plan-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:20px;}
  .plan-card{
    border:1.5px solid var(--stone-dim);
    border-radius:16px;padding:24px 20px;
    transition:border-color .2s, transform .2s;
  }
  .plan-card:hover{border-color:var(--sage);transform:translateY(-3px);}
  .plan-card .tag-pill{
    background:var(--sage);color:var(--ivory);
    font-size:.68rem;padding:4px 10px;border-radius:999px;
    font-weight:700;letter-spacing:.06em;text-transform:uppercase;
    display:inline-block;margin-bottom:14px;
  }
  .plan-card h4{font-family:var(--font-body);font-weight:700;font-size:1.02rem;margin-bottom:6px;color:var(--ink);}
  .plan-card p{font-size:.85rem;color:var(--ink-soft);}
  @media(max-width:980px){.plan-grid{grid-template-columns:repeat(2,1fr);}}
  @media(max-width:860px){.food-grid{grid-template-columns:1fr;}}

  /* ---------- Why choose ---------- */
  .why-grid{
    display:grid;grid-template-columns:repeat(4,1fr);gap:1px;
    background:rgba(217,203,180,.12);
    border:1px solid rgba(217,203,180,.12);
    border-radius:18px;overflow:hidden;
  }
  .why-item{
    background:var(--ink);padding:30px 22px;
    display:flex;flex-direction:column;gap:10px;
  }
  .why-item .tick-circ{
    width:32px;height:32px;border-radius:50%;
    background:linear-gradient(135deg, var(--amber), var(--rose));
    display:flex;align-items:center;justify-content:center;
    font-weight:800;font-size:.9rem;color:var(--ivory);
  }
  .why-item p{font-size:.9rem;font-weight:600;color:var(--ivory);}
  @media(max-width:860px){.why-grid{grid-template-columns:repeat(2,1fr);}}
  @media(max-width:520px){.why-grid{grid-template-columns:1fr;}}

  /* ---------- Nearby ---------- */
  .nearby-row{
    display:flex;flex-wrap:wrap;gap:12px;justify-content:center;
  }
  .nearby-chip{
    padding:10px 20px;border-radius:999px;
    background:rgba(217,203,180,.06);
    border:1px solid rgba(217,203,180,.18);
    font-size:.88rem;color:var(--stone);
  }

  /* ---------- Testimonials ---------- */
  .testi-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:22px;
  }
  .testi-card{
    background:rgba(255,255,255,.7);
    border-radius:18px;padding:30px 26px;
    border:1px solid rgba(27,22,18,.06);
  }
  .stars{color:var(--amber-deep);font-size:.95rem;margin-bottom:14px;letter-spacing:2px;}
  .testi-card p.quote{font-size:1.0rem;color:var(--ink);font-style:italic;margin-bottom:18px;}
  .testi-card .who{font-size:.82rem;font-weight:700;color:var(--ink-soft);}
  @media(max-width:860px){.testi-grid{grid-template-columns:1fr;}}

  /* ---------- Gallery ---------- */
  .gallery-grid{
    display:grid;grid-template-columns:repeat(4,1fr);grid-auto-rows:160px;gap:12px;
  }
  .gallery-grid img{width:100%;height:100%;object-fit:cover;border-radius:12px;transition:transform .35s;}
  .gallery-grid a{overflow:hidden;border-radius:12px;display:block;}
  .gallery-grid a:hover img{transform:scale(1.08);}
  .gallery-grid .g1{grid-column:span 2;grid-row:span 2;}
  .gallery-grid .g5{grid-column:span 2;}
  @media(max-width:860px){
    .gallery-grid{grid-template-columns:repeat(2,1fr);grid-auto-rows:140px;}
    .gallery-grid .g1{grid-column:span 2;grid-row:span 2;}
    .gallery-grid .g5{grid-column:span 2;}
  }

  /* ---------- FAQ ---------- */
  .faq-list{max-width:760px;margin:0 auto;}
  .faq-item{
    border-bottom:1px solid rgba(217,203,180,.15);
  }
  .faq-q{
    width:100%;display:flex;justify-content:space-between;align-items:center;
    padding:22px 4px;font-size:1.02rem;font-weight:600;color:var(--ivory);
    text-align:left;
  }
  .faq-q .plus{font-size:1.4rem;color:var(--stone-dim);transition:transform .25s;}
  .faq-item.open .plus{transform:rotate(45deg);color:var(--amber);}
  .faq-a{
    max-height:0;overflow:hidden;transition:max-height .3s ease;
    color:var(--stone);font-size:.92rem;
  }
  .faq-item.open .faq-a{max-height:200px;padding-bottom:22px;}

  /* ---------- Booking / Contact ---------- */
  .contact-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:48px;
  }
  .contact-card{
    background:var(--ivory);color:var(--ink);
    border-radius:20px;padding:36px;
  }
  .contact-card .tag-pill{
    font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
    padding:5px 13px;border-radius:999px;display:inline-block;margin-bottom:16px;
  }
  .contact-card.boys-c .tag-pill{background:var(--amber);color:var(--ink);}
  .contact-card.girls-c .tag-pill{background:var(--rose);color:var(--ivory);}
  .contact-card h3{font-size:1.5rem;margin-bottom:18px;}
  .contact-row{display:flex;gap:12px;margin-bottom:14px;font-size:.94rem;align-items:flex-start;}
  .contact-row .ic{color:var(--ink-soft);}

  .form-shell{
    background:var(--ivory-dim);
    border-radius:20px;padding:36px;color:var(--ink);
  }
  .form-shell h3{color:var(--ink);font-size:1.4rem;margin-bottom:8px;}
  .form-shell p{color:var(--ink-soft);font-size:.9rem;margin-bottom:24px;}
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;}
  .form-shell input, .form-shell select, .form-shell textarea{
    width:100%;padding:13px 14px;border-radius:10px;
    border:1.5px solid rgba(27,22,18,.15);
    font-family:inherit;font-size:.92rem;background:#fff;color:var(--ink);
  }
  .form-shell input:focus, .form-shell select:focus, .form-shell textarea:focus{
    outline:2px solid var(--amber);outline-offset:1px;border-color:var(--amber);
  }
  .form-shell label{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--ink-soft);display:block;margin-bottom:6px;}
  .form-full{margin-bottom:14px;}
  .form-submit{
    width:100%;padding:15px;border-radius:10px;
    background:linear-gradient(95deg, var(--amber), var(--rose));
    color:var(--ivory);font-weight:700;font-size:.96rem;margin-top:6px;
    transition:transform .2s;
  }
  .form-submit:hover{transform:translateY(-2px);}
  @media(max-width:860px){
    .contact-grid{grid-template-columns:1fr;}
    .form-row{grid-template-columns:1fr;}
  }

  /* ---------- Final CTA ---------- */
  .final-cta{
    text-align:center;
    background:radial-gradient(ellipse at center, rgba(199,123,61,.12), transparent 70%);
  }
  .final-cta h2{font-size:clamp(2rem,4vw,3rem);margin-bottom:16px;}
  .final-cta .strap{
    color:var(--stone-dim);font-weight:700;letter-spacing:.06em;text-transform:uppercase;font-size:.82rem;margin-bottom:18px;
  }
  .final-cta p.lead{max-width:560px;margin:0 auto 36px;color:var(--stone);}

  /* ---------- Footer ---------- */
  footer{
    background:var(--ink-soft);
    padding:56px 0 28px;
    border-top:1px solid rgba(217,203,180,.1);
  }
  .footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:40px;margin-bottom:40px;}
  .footer-grid h4{font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:var(--stone-dim);margin-bottom:16px;}
  .footer-grid ul li{margin-bottom:10px;font-size:.9rem;color:var(--stone);}
  .footer-bottom{
    border-top:1px solid rgba(217,203,180,.1);
    padding-top:24px;
    display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;
    font-size:.8rem;color:var(--stone-dim);
  }
  @media(max-width:760px){.footer-grid{grid-template-columns:1fr;}}

  /* ---------- WhatsApp float ---------- */
  .wa-float{
    position:fixed;bottom:24px;right:24px;z-index:90;
    width:58px;height:58px;border-radius:50%;
    background:#25D366;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 10px 30px -8px rgba(0,0,0,.5);
    font-size:1.6rem;
    animation:pulse 2.4s infinite;
  }
  @keyframes pulse{
    0%{box-shadow:0 0 0 0 rgba(37,211,102,.5);}
    70%{box-shadow:0 0 0 14px rgba(37,211,102,0);}
    100%{box-shadow:0 0 0 0 rgba(37,211,102,0);}
  }

  /* scroll reveal */
  .reveal{opacity:0;transform:translateY(24px);transition:opacity .6s ease, transform .6s ease;}
  .reveal.in{opacity:1;transform:translateY(0);}

  @media (prefers-reduced-motion: reduce){
    .reveal{transition:none;opacity:1;transform:none;}
    .wa-float{animation:none;}
    html{scroll-behavior:auto;}
  }

  @media(max-width:980px){
    .why-grid, .testi-grid{grid-template-columns:repeat(2,1fr);}
  }
</style>
</head>
<body>

<header>
  <nav>
    <div class="brand-mark"><span class="split"></span> Sanjay & Harini Hostels</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="#about">About</a></li>
      <li><a href="#facilities">Facilities</a></li>
      <li><a href="#food">Food & Lunch Box</a></li>
      <li><a href="#safety">Safety</a></li>
      <li><a href="#gallery">Gallery</a></li>
      <li><a href="#faq">FAQ</a></li>
      <li><a href="#contact" class="nav-cta">Book a Room</a></li>
    </ul>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
  </nav>
</header>

<!-- HERO -->
<section class="hero seam">
  <div class="hero-bg">
    <div class="side left"><img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1200" alt=""></div>
    <div class="side right"><img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=1200" alt=""></div>
  </div>
  <div class="hero-content">
    <span class="hero-tag">Sanjay Boys Hostel · Harini Girls Hostel</span>
    <h1 class="reveal in">Your Home <em>Away</em><br>From Home</h1>
    <p class="sub">Safe, comfortable & affordable accommodation for students, employees, and working professionals — with separate, professionally managed facilities for men and women.</p>
    <div class="hero-actions">
      <a href="#contact" class="btn btn-primary">Book a Room →</a>
      <a href="#contact" class="btn btn-ghost">Contact Us</a>
      <a href="#facilities" class="btn btn-ghost">View Facilities</a>
    </div>
    <div class="badge-row">
      <span class="badge"><span class="tick">✓</span> AC & Non-AC Rooms</span>
      <span class="badge"><span class="tick">✓</span> High-Speed WiFi</span>
      <span class="badge"><span class="tick">✓</span> CCTV Security</span>
      <span class="badge"><span class="tick">✓</span> Daily Housekeeping</span>
      <span class="badge"><span class="tick">✓</span> Home-Style Food</span>
      <span class="badge"><span class="tick">✓</span> Laundry Facilities</span>
      <span class="badge"><span class="tick">✓</span> Power Backup</span>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section class="panel-ivory" id="about">
  <div class="wrap about">
    <div class="about-copy reveal">
      <span class="eyebrow">Who we are</span>
      <h2>Two hostels. One standard of care.</h2>
      <p>Sanjay Boys Hostel and Harini Girls Hostel provide a safe, hygienic, and comfortable living environment for students, working professionals, job seekers, interns, and trainees — each in their own dedicated, separately managed building.</p>
      <p>Every room is built around the same promise: modern amenities, affordable pricing, real security, and nutritious home-style food, so settling into a new city feels less like a compromise.</p>
      <div class="about-stats">
        <div class="stat"><div class="num">2</div><div class="label">Dedicated Hostels</div></div>
        <div class="stat"><div class="num">24/7</div><div class="label">Security & Support</div></div>
        <div class="stat"><div class="num">4</div><div class="label">Daily Meals Served</div></div>
        <div class="stat"><div class="num">5+</div><div class="label">Room Configurations</div></div>
      </div>
    </div>
    <div class="about-visual reveal">
      <img class="tall a" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=800" alt="Hostel building exterior">
      <div class="col">
        <img class="r" src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=600" alt="Hostel room interior">
        <img class="a" src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=600" alt="Hostel common area">
      </div>
    </div>
  </div>
</section>

<!-- IDENTITY SPLIT -->
<section>
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Separate, by design</span>
      <h2>Built for boys. Built for girls. Built right.</h2>
      <p>Two independently run wings, sharing one management philosophy: safety first, comfort always.</p>
    </div>
    <div class="identity-grid reveal">
      <div class="identity-card boys">
        <span class="tag-pill">For Men</span>
        <h3>Sanjay Boys Hostel</h3>
        <p>A dedicated residence for male students and professionals, with the daily structure busy people need.</p>
        <ul>
          <li>Single, double, triple & dormitory sharing</li>
          <li>Warden supervision & biometric entry</li>
          <li>Study hall, indoor games, common TV area</li>
          <li>Bike parking & visitor management</li>
        </ul>
      </div>
      <div class="identity-card girls">
        <span class="tag-pill">For Women</span>
        <h3>Harini Girls Hostel</h3>
        <p>A secure, independent residence for women, with safety measures built into every part of daily life.</p>
        <ul>
          <li>Single, double, triple & dormitory sharing</li>
          <li>Women safety measures & visitor tracking</li>
          <li>Reading room, laundry, ironing area</li>
          <li>24/7 CCTV monitored entry & corridors</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FACILITIES -->
<section class="panel-ivory" id="facilities">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Inside the hostel</span>
      <h2>Facilities, room types & furnishing</h2>
      <p>Everything is included from day one — nothing to arrange, nothing to chase.</p>
    </div>
    <div class="facility-tabs reveal">
      <button class="tab-btn active" data-tab="rooms">Room Types</button>
      <button class="tab-btn" data-tab="furnish">In-Room Furnishing</button>
      <button class="tab-btn" data-tab="amenities">Amenities</button>
    </div>

    <div class="tab-panel grid-cards active" id="rooms">
      <div class="fac-card"><div class="ic">🛏️</div><h4>Single Sharing</h4><p>A private room for residents who want their own space.</p></div>
      <div class="fac-card"><div class="ic">🛏️</div><h4>Double Sharing</h4><p>Two beds per room, balancing privacy and affordability.</p></div>
      <div class="fac-card"><div class="ic">🛏️</div><h4>Triple Sharing</h4><p>Comfortable shared living for three residents.</p></div>
      <div class="fac-card"><div class="ic">🛏️</div><h4>Four Sharing</h4><p>Our most budget-friendly configuration.</p></div>
      <div class="fac-card"><div class="ic">🏠</div><h4>Dormitory</h4><p>Multi-bed dormitory accommodation for short stays.</p></div>
      <div class="fac-card"><div class="ic">❄️</div><h4>AC Rooms</h4><p>Climate-controlled rooms across all sharing types.</p></div>
      <div class="fac-card"><div class="ic">🌬️</div><h4>Non-AC Rooms</h4><p>Ceiling-fan cooled rooms at a lower monthly rate.</p></div>
      <div class="fac-card"><div class="ic">🚿</div><h4>Attached Bathroom</h4><p>Private attached bathrooms in select rooms.</p></div>
      <div class="fac-card"><div class="ic">🌿</div><h4>Balcony Rooms</h4><p>Rooms with a private balcony, subject to availability.</p></div>
      <div class="fac-card"><div class="ic">✨</div><h4>Premium Rooms</h4><p>Our top-tier rooms with upgraded furnishing.</p></div>
    </div>

    <div class="tab-panel grid-cards" id="furnish">
      <div class="fac-card"><div class="ic">🛏️</div><h4>Cot & Mattress</h4><p>Sturdy cot with a quality mattress in every room.</p></div>
      <div class="fac-card"><div class="ic">🛌</div><h4>Pillow & Bed Sheet</h4><p>Fresh linen provided and laundered regularly.</p></div>
      <div class="fac-card"><div class="ic">📚</div><h4>Study Table & Chair</h4><p>A dedicated work surface in every room.</p></div>
      <div class="fac-card"><div class="ic">🚪</div><h4>Wardrobe & Locker</h4><p>Personal storage with a lockable compartment.</p></div>
      <div class="fac-card"><div class="ic">🔌</div><h4>Charging Points</h4><p>Multiple power outlets placed near the bed and desk.</p></div>
      <div class="fac-card"><div class="ic">💡</div><h4>LED Lights</h4><p>Energy-efficient lighting throughout the room.</p></div>
      <div class="fac-card"><div class="ic">🌀</div><h4>Ceiling Fans</h4><p>Fitted as standard in every room, AC or non-AC.</p></div>
      <div class="fac-card"><div class="ic">🪞</div><h4>Furnished Interiors</h4><p>Move in with a bag — the room is ready to live in.</p></div>
    </div>

    <div class="tab-panel grid-cards" id="amenities">
      <div class="fac-card"><div class="ic">📶</div><h4>High-Speed WiFi</h4><p>Reliable internet across rooms and common areas.</p></div>
      <div class="fac-card"><div class="ic">🚰</div><h4>24x7 Water & RO</h4><p>Round-the-clock water supply with hot water and RO drinking water.</p></div>
      <div class="fac-card"><div class="ic">🔒</div><h4>CCTV & Biometric Entry</h4><p>Monitored entry points with a stationed security guard.</p></div>
      <div class="fac-card"><div class="ic">⚡</div><h4>Power & Generator Backup</h4><p>Uninterrupted power, even during outages.</p></div>
      <div class="fac-card"><div class="ic">🛗</div><h4>Lift Facility</h4><p>Elevator access in multi-floor buildings.</p></div>
      <div class="fac-card"><div class="ic">🧹</div><h4>Daily Housekeeping</h4><p>Room cleaning handled as part of your stay.</p></div>
      <div class="fac-card"><div class="ic">👕</div><h4>Laundry & Ironing</h4><p>Washing machines and a dedicated ironing area.</p></div>
      <div class="fac-card"><div class="ic">🎮</div><h4>Common TV & Games</h4><p>A shared lounge with TV and indoor games.</p></div>
      <div class="fac-card"><div class="ic">📖</div><h4>Reading Room & Study Hall</h4><p>Quiet, dedicated spaces for focused study.</p></div>
      <div class="fac-card"><div class="ic">🅿️</div><h4>Parking & Visitor Desk</h4><p>Vehicle and bike parking, with visitor sign-in.</p></div>
    </div>
  </div>
</section>

<!-- SAFETY -->
<section class="safety" id="safety">
  <div class="wrap">
    <div class="safety-grid">
      <div class="reveal">
        <span class="eyebrow">Non-negotiable</span>
        <h2 style="margin:14px 0 16px;font-size:clamp(1.8rem,3.2vw,2.5rem);">Your safety is our priority</h2>
        <p style="color:var(--stone);margin-bottom:32px;max-width:480px;">Both hostels are run with the same baseline of supervision, monitoring, and emergency readiness — not as an add-on, but as the foundation everything else is built on.</p>
        <div class="safety-list">
          <div class="safety-item"><span class="ic">●</span><div><h4>24/7 CCTV Monitoring</h4><p>Every entry, corridor, and common area is covered.</p></div></div>
          <div class="safety-item"><span class="ic">●</span><div><h4>Secure Entry System</h4><p>Biometric and supervised access at all entry points.</p></div></div>
          <div class="safety-item"><span class="ic">●</span><div><h4>Warden Supervision</h4><p>On-site wardens for daily oversight and support.</p></div></div>
          <div class="safety-item"><span class="ic">●</span><div><h4>Separate Facilities</h4><p>Independent buildings for boys and girls.</p></div></div>
          <div class="safety-item"><span class="ic">●</span><div><h4>Fire Safety Equipment</h4><p>Extinguishers and emergency exits in place.</p></div></div>
          <div class="safety-item"><span class="ic">●</span><div><h4>Visitor Tracking</h4><p>Every visitor is logged at the entry desk.</p></div></div>
        </div>
      </div>
      <div class="safety-visual reveal">
        <img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?q=80&w=900" alt="Secure hostel corridor">
      </div>
    </div>
  </div>
</section>

<!-- FOOD -->
<section class="panel-ivory" id="food">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Food & dining</span>
      <h2>Healthy, home-style meals</h2>
      <p>Cooked fresh daily in a hygienic kitchen — for residents, and for anyone in the city who needs a reliable lunch box.</p>
    </div>
    <div class="food-grid reveal">
      <div>
        <h3 style="font-size:1.3rem;margin-bottom:16px;">Served every day</h3>
        <div class="meal-tags">
          <div class="meal-tag">Breakfast</div>
          <div class="meal-tag">Lunch</div>
          <div class="meal-tag">Evening Snacks</div>
          <div class="meal-tag">Dinner</div>
        </div>
        <p style="color:var(--ink-soft);margin-bottom:18px;">Cooked with RO water in a hygienic kitchen, with both vegetarian and non-vegetarian options, South and North Indian meals, and a rotating weekly and festival special menu.</p>
        <h3 style="font-size:1.3rem;margin:32px 0 6px;">Daily Lunch Box Delivery</h3>
        <p style="color:var(--ink-soft);margin-bottom:18px;">Not staying at the hostel? Our meal supply service delivers fresh lunch boxes to office employees, IT professionals, college students, factory workers, shop owners, working women, and senior citizens across the city.</p>
        <div class="plan-grid">
          <div class="plan-card"><span class="tag-pill">Basic</span><h4>Daily Veg Meals</h4><p>Simple, consistent vegetarian lunch, every day.</p></div>
          <div class="plan-card"><span class="tag-pill">Standard</span><h4>Veg + Variety</h4><p>A rotating menu with more variety built in.</p></div>
          <div class="plan-card"><span class="tag-pill">Premium</span><h4>Veg / Non-Veg</h4><p>Choose your meal type, day to day.</p></div>
          <div class="plan-card"><span class="tag-pill">Corporate</span><h4>Bulk Supply</h4><p>Lunch delivery for entire teams and offices.</p></div>
        </div>
      </div>
      <div class="food-visual">
        <img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=900" alt="Home-style Indian thali meal">
      </div>
    </div>
  </div>
</section>

<!-- WHY CHOOSE -->
<section>
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Why residents stay</span>
      <h2>Built around what actually matters</h2>
    </div>
    <div class="why-grid reveal">
      <div class="why-item"><span class="tick-circ">✓</span><p>Affordable Pricing</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Safe Environment</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Prime Location</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Modern Facilities</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Clean Rooms</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Fast WiFi</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Fresh Food</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Laundry Support</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Power Backup</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Friendly Management</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Professional Service</p></div>
      <div class="why-item"><span class="tick-circ">✓</span><p>Comfortable Stay</p></div>
    </div>
  </div>
</section>

<!-- NEARBY -->
<section class="panel-ivory">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Location</span>
      <h2>Close to everything you need</h2>
    </div>
    <div class="nearby-row reveal">
      <span class="nearby-chip">🎓 Colleges</span>
      <span class="nearby-chip">🏫 Schools</span>
      <span class="nearby-chip">🏢 IT Parks</span>
      <span class="nearby-chip">🚌 Bus Stand</span>
      <span class="nearby-chip">🚉 Railway Station</span>
      <span class="nearby-chip">🏥 Hospitals</span>
      <span class="nearby-chip">🛒 Supermarkets</span>
      <span class="nearby-chip">🏦 ATM</span>
      <span class="nearby-chip">💊 Pharmacy</span>
      <span class="nearby-chip">🍽️ Restaurants</span>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="panel-ivory">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">In residents' words</span>
      <h2>What people say after staying</h2>
    </div>
    <div class="testi-grid reveal">
      <div class="testi-card">
        <div class="stars">★★★★★</div>
        <p class="quote">"Excellent hostel with good food and clean rooms."</p>
        <div class="who">Resident, Sanjay Boys Hostel</div>
      </div>
      <div class="testi-card">
        <div class="stars">★★★★★</div>
        <p class="quote">"Safe accommodation and excellent WiFi."</p>
        <div class="who">Resident, Harini Girls Hostel</div>
      </div>
      <div class="testi-card">
        <div class="stars">★★★★★</div>
        <p class="quote">"Best hostel experience with home-style meals."</p>
        <div class="who">Working Professional</div>
      </div>
    </div>
  </div>
</section>

<!-- GALLERY -->
<section id="gallery">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Take a look around</span>
      <h2>Gallery</h2>
    </div>
    <div class="gallery-grid reveal">
      <a class="g1" href="#"><img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=700" alt="Building exterior"></a>
      <a><img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=500" alt="AC room"></a>
      <a><img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=500" alt="Common area"></a>
      <a><img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500" alt="Dining hall"></a>
      <a class="g5" href="#"><img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?q=80&w=700" alt="Reception area"></a>
      <a><img src="https://images.unsplash.com/photo-1556909114-44e3e9699e2b?q=80&w=500" alt="Study hall"></a>
      <a><img src="https://images.unsplash.com/photo-1545048702-79362596cdc9?q=80&w=500" alt="Laundry area"></a>
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Questions, answered</span>
      <h2>Frequently asked questions</h2>
    </div>
    <div class="faq-list reveal">
      <div class="faq-item">
        <button class="faq-q">What room types are available? <span class="plus">+</span></button>
        <div class="faq-a">Single, double, triple, four-sharing, and dormitory rooms, available in both AC and non-AC configurations.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Is WiFi included? <span class="plus">+</span></button>
        <div class="faq-a">Yes, high-speed WiFi is included for every resident at no extra cost.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Are meals included? <span class="plus">+</span></button>
        <div class="faq-a">Breakfast, lunch, evening snacks, and dinner are served daily and included in most plans. A standalone lunch box delivery service is also available for non-residents.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Is parking available? <span class="plus">+</span></button>
        <div class="faq-a">Yes, dedicated parking is available, including separate bike parking.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Is laundry available? <span class="plus">+</span></button>
        <div class="faq-a">Yes, washing machines, a laundry service, and an ironing area are available on-site.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Are visitors allowed? <span class="plus">+</span></button>
        <div class="faq-a">Visitors are allowed during set hours and are logged through our visitor management system for everyone's safety.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Is power backup available? <span class="plus">+</span></button>
        <div class="faq-a">Yes, both hostels have power backup with generator support during outages.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Are AC rooms available? <span class="plus">+</span></button>
        <div class="faq-a">Yes, AC rooms are available alongside non-AC options, across most sharing types.</div>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Get in touch</span>
      <h2>Book your stay today</h2>
      <p>Safe • Comfortable • Affordable — your home away from home.</p>
    </div>

    <div class="contact-grid reveal" style="margin-bottom:48px;">
      <div class="contact-card boys-c">
        <span class="tag-pill">For Men</span>
        <h3>Sanjay Boys Hostel</h3>
        <div class="contact-row"><span class="ic">📍</span><span>Address on request — fill the enquiry form for directions.</span></div>
        <div class="contact-row"><span class="ic">📞</span><span>Phone number provided on enquiry / booking confirmation.</span></div>
        <div class="contact-row"><span class="ic">📧</span><span>Email provided on enquiry / booking confirmation.</span></div>
      </div>
      <div class="contact-card girls-c">
        <span class="tag-pill">For Women</span>
        <h3>Harini Girls Hostel</h3>
        <div class="contact-row"><span class="ic">📍</span><span>Address on request — fill the enquiry form for directions.</span></div>
        <div class="contact-row"><span class="ic">📞</span><span>Phone number provided on enquiry / booking confirmation.</span></div>
        <div class="contact-row"><span class="ic">📧</span><span>Email provided on enquiry / booking confirmation.</span></div>
      </div>
    </div>

    <div class="form-shell reveal">
      <h3>Room enquiry & lunch box form</h3>
      <p>Tell us what you need — we'll get back with availability and pricing.</p>
      <form onsubmit="event.preventDefault(); document.getElementById('formMsg').style.display='block';">
        <div class="form-row">
          <div><label>Full Name</label><input type="text" required placeholder="Your name"></div>
          <div><label>Phone Number</label><input type="tel" required placeholder="+91 XXXXX XXXXX"></div>
        </div>
        <div class="form-row">
          <div><label>I'm interested in</label>
            <select>
              <option>Sanjay Boys Hostel — Room Booking</option>
              <option>Harini Girls Hostel — Room Booking</option>
              <option>Daily Lunch Box Subscription</option>
              <option>General Enquiry</option>
            </select>
          </div>
          <div><label>Preferred Room Type</label>
            <select>
              <option>Single Sharing</option>
              <option>Double Sharing</option>
              <option>Triple Sharing</option>
              <option>Four Sharing</option>
              <option>Dormitory</option>
              <option>Not sure yet</option>
            </select>
          </div>
        </div>
        <div class="form-full"><label>Message</label><textarea rows="3" placeholder="Move-in date, AC/Non-AC preference, or any question..."></textarea></div>
        <button type="submit" class="form-submit">Send Enquiry</button>
        <p id="formMsg" style="display:none;margin-top:14px;font-size:.85rem;color:var(--sage);font-weight:600;">Thanks — your enquiry has been noted. Our team will reach out shortly.</p>
      </form>
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="final-cta">
  <div class="wrap">
    <div class="strap">Join hundreds of residents who already call it home</div>
    <h2>Ready to move in?</h2>
    <p class="lead">Hundreds of students and professionals trust Sanjay Boys Hostel & Harini Girls Hostel for a safe, comfortable living experience.</p>
    <div class="hero-actions">
      <a href="#contact" class="btn btn-primary">Book a Room →</a>
      <a href="#contact" class="btn btn-ghost">Talk to Us</a>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <div class="brand-mark" style="margin-bottom:14px;"><span class="split"></span> Sanjay & Harini Hostels</div>
        <p style="color:var(--stone);font-size:.9rem;max-width:340px;">Professionally managed accommodation for men and women, with daily lunch box delivery across the city.</p>
      </div>
      <div>
        <h4>Explore</h4>
        <ul>
          <li><a href="#about">About Us</a></li>
          <li><a href="#facilities">Facilities</a></li>
          <li><a href="#food">Food & Lunch Box</a></li>
          <li><a href="#gallery">Gallery</a></li>
        </ul>
      </div>
      <div>
        <h4>Support</h4>
        <ul>
          <li><a href="#faq">FAQs</a></li>
          <li><a href="#contact">Contact</a></li>
          <li><a href="#contact">Book a Room</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Sanjay Boys Hostel & Harini Girls Hostel. All rights reserved.</span>
      <span>Built with care for safe, comfortable living.</span>
    </div>
  </div>
</footer>

<a class="wa-float" href="https://wa.me/910000000000" target="_blank" aria-label="Chat on WhatsApp">💬</a>

<script>
  // mobile nav
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  navToggle.addEventListener('click', () => navLinks.classList.toggle('open'));
  navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('open')));

  // facility tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });

  // FAQ accordion
  document.querySelectorAll('.faq-item').forEach(item => {
    item.querySelector('.faq-q').addEventListener('click', () => {
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!wasOpen) item.classList.add('open');
    });
  });

  // scroll reveal
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('in'); });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>
