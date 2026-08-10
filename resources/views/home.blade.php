@extends('layouts.app')

@section('title', "Sanjay & Harini Hostels | Boys & Girls PG in Alandur, St. Thomas Mount & Perungalathur, Chennai")
@section('canonical', 'https://www.sanjayandharinihostels.com/')
@section('meta_description', "Sanjay Boys Hostel & Harini Girls Hostel offer safe, affordable PG accommodation in Alandur, St. Thomas Mount and Perungalathur, Chennai — AC/non-AC rooms, home-style food, WiFi, CCTV and 24/7 security.")

@section('content')
  @verbatim
  <!-- HERO -->
  <section class="hero seam">
    <div class="hero-bg">
      <div class="side left"><img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1200" alt="PG hostel Alandur Chennai"></div>
      <div class="side right"><img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=1200" alt="Girls hostel St Thomas Mount"></div>
    </div>
    <div class="hero-content">
      <span class="hero-tag">PG in Alandur · St. Thomas Mount · Perungalathur</span>
      <h1>Best Boys & Girls <em>PG Hostel</em> in Alandur, Chennai</h1>
      <p class="sub">Sanjay Boys Hostel & Harini Girls Hostel – with branches in St. Thomas Mount and Perungalathur. AC/Non-AC rooms, home food, WiFi, CCTV, near metro & railway. Ideal for IT pros, students & airport staff.</p>
      <div class="hero-actions">
  @endverbatim
        <a href="{{ route('contact') }}" class="btn btn-primary">Book a Room →</a>
        <a href="{{ route('rooms') }}" class="btn btn-ghost">See Rooms & Facilities</a>
  @verbatim
      </div>
      <div class="badge-row">
        <span class="badge"><span class="tick">✓</span> AC & Non-AC</span>
        <span class="badge"><span class="tick">✓</span> WiFi</span>
        <span class="badge"><span class="tick">✓</span> CCTV</span>
        <span class="badge"><span class="tick">✓</span> Food</span>
        <span class="badge"><span class="tick">✓</span> Near Alandur Metro</span>
        <span class="badge"><span class="tick">✓</span> St. Mount Station</span>
      </div>
    </div>
  </section>
  @endverbatim

  <!-- BRANCH HIGHLIGHTS -->
  <section>
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Our Locations</span>
        <h2>PG in Alandur, St. Thomas Mount & Perungalathur</h2>
        <p>Choose your branch – all close to metro, railway, IT parks, and the airport. <a href="{{ route('about') }}">Read more about each location →</a></p>
      </div>
      @verbatim
      <div class="identity-grid reveal">
        <div class="identity-card boys">
          <span class="tag-pill">Sanjay Boys Hostel</span>
          <h3>Men's PG in Alandur & St. Thomas Mount</h3>
          <div class="branch-loc">📍 Alandur – Near Alandur Metro, Guindy, Nanganallur</div>
          <div class="branch-loc">📍 St. Thomas Mount – Near St. Mount Station, Kathipara, Airport Metro</div>
          <ul>
            <li>AC / Non-AC rooms</li>
            <li>WiFi, CCTV, power backup</li>
            <li>Home-style food (veg/non-veg)</li>
            <li>Laundry, housekeeping</li>
          </ul>
      @endverbatim
          <a href="{{ route('contact') }}" style="display:inline-block;margin-top:8px;font-weight:700;color:var(--amber);text-decoration:underline;">Check room availability at Sanjay Boys Hostel →</a>
        </div>
        @verbatim
        <div class="identity-card girls">
          <span class="tag-pill">Harini Ladies Hostel</span>
          <h3>Women's PG in Alandur & St. Thomas Mount</h3>
          <div class="branch-loc">📍 Alandur – Near Alandur Metro, Ekkatuthangal, Nanganallur</div>
          <div class="branch-loc">📍 St. Thomas Mount – Near St. Mount, Airport Metro, Guindy</div>
          <ul>
            <li>Women-only safe accommodation</li>
            <li>24/7 CCTV & visitor tracking</li>
            <li>Single, double, triple sharing</li>
            <li>RO water, ironing, study hall</li>
          </ul>
        @endverbatim
          <a href="{{ route('contact') }}" style="display:inline-block;margin-top:8px;font-weight:700;color:var(--rose);text-decoration:underline;">Check room availability at Harini Girls Hostel →</a>
        </div>
      </div>
      <div style="margin-top:32px;text-align:center;background:rgba(217,203,180,.06);border-radius:20px;padding:28px;">
        <span class="tag-pill" style="background:var(--rose);color:var(--ivory);padding:6px 20px;border-radius:999px;">Perungalathur – Boys Only</span>
        <h3 style="margin-top:14px;font-size:1.3rem;">Men's PG Hostel in Perungalathur, Chennai</h3>
        <p style="color:var(--stone);margin-top:8px;">Sanjay Boys Hostel at Perungalathur – near Perungalathur railway station, Tambaram, Vandalur, GST Road. Ideal for students and professionals.
          <a href="{{ route('contact') }}" style="color:var(--rose);text-decoration:underline;font-weight:700;">Enquire about the Perungalathur branch →</a>
        </p>
      </div>
    </div>
  </section>

  @verbatim
  <!-- WHY CHOOSE -->
  <section>
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Why Stay With Us</span>
        <h2>Best PG in Alandur, St. Thomas Mount & Perungalathur</h2>
      </div>
      <div class="why-grid reveal">
        <div class="why-item"><span class="tick-circ">✓</span><p>Affordable Rent</p></div>
        <div class="why-item"><span class="tick-circ">✓</span><p>Near Metro & Railway</p></div>
        <div class="why-item"><span class="tick-circ">✓</span><p>AC / Non-AC</p></div>
        <div class="why-item"><span class="tick-circ">✓</span><p>WiFi & CCTV</p></div>
        <div class="why-item"><span class="tick-circ">✓</span><p>Home Food</p></div>
        <div class="why-item"><span class="tick-circ">✓</span><p>Laundry</p></div>
        <div class="why-item"><span class="tick-circ">✓</span><p>Power Backup</p></div>
        <div class="why-item"><span class="tick-circ">✓</span><p>Safe for Women</p></div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="panel-ivory">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Testimonials</span>
        <h2>What Our Residents Say</h2>
      </div>
      <div class="testi-grid reveal">
        <div class="testi-card"><div class="stars">★★★★★</div><p class="quote">"Best PG near Alandur Metro. Clean rooms, good food, and safe for women."</p><div class="who">Harini Girls Hostel resident</div></div>
        <div class="testi-card"><div class="stars">★★★★★</div><p class="quote">"I work at the airport. This hostel is close, affordable, and has great WiFi."</p><div class="who">Sanjay Boys, St. Thomas Mount</div></div>
        <div class="testi-card"><div class="stars">★★★★★</div><p class="quote">"Perfect for IT professionals. Attached bathroom, study table, and power backup."</p><div class="who">Working professional, Alandur</div></div>
      </div>
    </div>
  </section>
  @endverbatim

  <!-- FINAL CTA -->
  <section class="final-cta">
    <div class="wrap">
      <div class="strap">Trusted by 500+ residents</div>
      <h2>Your Home Away From Home</h2>
      <p class="lead">Sanjay Boys Hostel & Harini Girls Hostel – the best PG in Alandur, St. Thomas Mount & Perungalathur.</p>
      <div class="hero-actions">
        <a href="{{ route('contact') }}" class="btn btn-primary">Book Now →</a>
        <a href="{{ route('contact') }}" class="btn btn-ghost">Contact Us</a>
      </div>
    </div>
  </section>
@endsection