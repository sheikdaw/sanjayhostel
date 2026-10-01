@extends('layouts.frontend')

@section('title', 'About Us | Sanjay & Harini Hostels, Chennai')
@section('canonical', \App\Support\Seo::url('/about'))
@section('meta_description', "Learn about Sanjay & Harini Hostels, which runs men's and women's PG hostels in Alandur and Perungalathur, Chennai, with CCTV, wardens and separate buildings for men and women.")

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => [['Home', '/'], ['About', '/about']]])
            <span class="eyebrow">About Us</span>
            <h1>About Sanjay & Harini Hostels</h1>
            <p>Safe, affordable PG accommodation for men and women across Alandur and Perungalathur, Chennai.</p>
        </div>
    </div>

    <!-- ABOUT -->
    <section class="panel-ivory" id="about">
        <div class="wrap about">
            <div class="about-copy reveal">
                <span class="eyebrow">PG in Chennai – Alandur and Perungalathur</span>
                <h2>Safe, Affordable Hostels for Men & Women</h2>
                <p>Sanjay &amp; Harini Hostels operates six men's and women's PG hostels in <a href="{{ route('hostels.alandur') }}">Alandur</a> and <a href="{{ route('hostels.perungalathur') }}">Perungalathur</a>. We cater to IT employees, working professionals, college students, airport staff, and metro commuters.</p>
                <p>Our hostels offer modern amenities – AC/non-AC rooms, high-speed WiFi, 24/7 CCTV, RO water, laundry, housekeeping, and home-style meals. Separate hostels for men and women ensure safety and comfort. See the full <a href="{{ route('rooms') }}">list of PG facilities</a>.</p>
                <div class="about-stats">
                    <div class="stat"><div class="num">6</div><div class="label">Hostels</div></div>
                    <div class="stat"><div class="num">24/7</div><div class="label">Security & Support</div></div>
                    <div class="stat"><div class="num">4</div><div class="label">Meals Daily</div></div>
                    <div class="stat"><div class="num">5+</div><div class="label">Room Types</div></div>
                </div>
            </div>
            <div class="about-visual reveal">
                <img loading="lazy" decoding="async" width="800" height="600" class="tall a" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&q=70&w=800" alt="Hostel building exterior">
                <div class="col">
                    <img loading="lazy" decoding="async" width="600" height="450" class="r" src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&q=70&w=600" alt="Hostel room with a bed and study area">
                    <img loading="lazy" decoding="async" width="600" height="450" class="a" src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&q=70&w=600" alt="Hostel common lounge">
                </div>
            </div>
        </div>
    </section>

    <!-- SAFETY -->
    <section class="safety" id="safety">
        <div class="wrap">
            <div class="safety-grid">
                <div class="reveal">
                    <span class="eyebrow">Safety First</span>
                    <h2 style="font-size:clamp(1.8rem,3.2vw,2.5rem);">24/7 Security – CCTV, Wardens & More</h2>
                    <p style="color:var(--stone);margin-bottom:32px;">All our hostels are separate buildings for men or women, with, monitored entry, biometric access, and on-site wardens. Women's safety is our priority.</p>
                    <div class="safety-list">
                        <div class="safety-item"><span class="ic">●</span><div><h3>CCTV Coverage</h3><p>All corridors & entry points.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h3>Biometric Entry</h3><p>Secure access.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h3>Warden Supervision</h3><p>On-site support.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h3>Visitor Tracking</h3><p>Strict logging.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h3>Fire Safety</h3><p>Extinguishers & exits.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h3>Separate Buildings</h3><p>Boys & girls.</p></div></div>
                    </div>
                </div>
                <div class="safety-visual reveal">
                    <img loading="lazy" decoding="async" width="900" height="675" src="https://images.unsplash.com/photo-1486325212027-8081e485255e?auto=format&fit=crop&q=70&w=900" alt="Hostel entrance with CCTV">
                </div>
            </div>
        </div>
    </section>

    <!-- NEARBY LANDMARKS -->
    <section>
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">Location Highlights</span>
                <h2>Nearby Metro, Railway & IT Parks</h2>
                <p>Perfectly placed for commuters – Alandur Metro, St. Thomas Mount, Perungalathur station, Guindy, Tambaram and more.</p>
            </div>
            <div class="nearby-row reveal">
                <span class="nearby-chip">📍 Alandur Metro</span>
                <span class="nearby-chip">📍 St. Thomas Mount Railway Station</span>
                <span class="nearby-chip">📍 Perungalathur Railway Station</span>
                <span class="nearby-chip">📍 Guindy Industrial Estate</span>
                <span class="nearby-chip">📍 Chennai Airport</span>
                <span class="nearby-chip">📍 Kathipara Junction</span>
                <span class="nearby-chip">📍 Ekkatuthangal Metro</span>
                <span class="nearby-chip">📍 Nanganallur</span>
                <span class="nearby-chip">📍 Tambaram</span>
                <span class="nearby-chip">📍 Vandalur</span>
                <span class="nearby-chip">📍 GST Road</span>
                <span class="nearby-chip">📍 Velachery Railway Station</span>
            </div>
            <p class="price-note" style="margin-top:18px;">Details for each area: <a class="text-link" href="{{ route('hostels.alandur') }}#nearby">places near our Alandur hostels</a> and <a class="text-link" href="{{ route('hostels.perungalathur') }}#nearby">places near our Perungalathur hostel</a>.</p>
        </div>
    </section>
@endsection