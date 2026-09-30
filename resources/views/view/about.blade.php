@extends('layouts.frontend')

@section('title', 'About Sanjay & Harini Hostels | PG in Alandur, St. Thomas Mount & Perungalathur')
@section('canonical', 'https://www.sanjayandharinihostels.com/about')
@section('meta_description', "Learn about Sanjay Boys Hostel and Harini Girls Hostel — safe, affordable PG accommodation in Alandur, St. Thomas Mount and Perungalathur, Chennai, with 24/7 security and separate buildings for men and women.")

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            <span class="eyebrow">About Us</span>
            <h1>About Sanjay & Harini Hostels</h1>
            <p>Safe, affordable PG accommodation for men and women across Alandur, St. Thomas Mount and Perungalathur, Chennai.</p>
        </div>
    </div>

    <!-- ABOUT -->
    <section class="panel-ivory" id="about">
        <div class="wrap about">
            <div class="about-copy reveal">
                <span class="eyebrow">PG in Chennai – Alandur, St. Thomas Mount, Perungalathur</span>
                <h2>Safe, Affordable Hostels for Men & Women</h2>
                <p><strong>Sanjay Boys Hostel</strong> and <strong>Harini Girls Hostel</strong> offer premium PG accommodation in Alandur, St. Thomas Mount, and Perungalathur. We cater to IT employees, working professionals, college students, airport staff, and metro commuters.</p>
                <p>Each branch is equipped with modern amenities – AC/non-AC rooms, high-speed WiFi, 24/7 CCTV, RO water, laundry, housekeeping, and home-style meals. Separate facilities for boys and girls ensure safety and comfort. See the full <a href="{{ route('rooms') }}">list of PG facilities</a>.</p>
                <div class="about-stats">
                    <div class="stat"><div class="num">3</div><div class="label">Branches</div></div>
                    <div class="stat"><div class="num">24/7</div><div class="label">Security & Support</div></div>
                    <div class="stat"><div class="num">4</div><div class="label">Meals Daily</div></div>
                    <div class="stat"><div class="num">5+</div><div class="label">Room Types</div></div>
                </div>
            </div>
            <div class="about-visual reveal">
                <img class="tall a" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=800" alt="Boys hostel Alandur">
                <div class="col">
                    <img class="r" src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=600" alt="Harini Girls Hostel, St. Thomas Mount branch">
                    <img class="a" src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=600" alt="Common lounge at Sanjay Boys Hostel, Alandur">
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
                    <p style="color:var(--stone);margin-bottom:32px;">Both hostels have separate buildings, monitored entry, biometric access, and on-site wardens. Women's safety is our priority.</p>
                    <div class="safety-list">
                        <div class="safety-item"><span class="ic">●</span><div><h4>CCTV Coverage</h4><p>All corridors & entry points.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h4>Biometric Entry</h4><p>Secure access.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h4>Warden Supervision</h4><p>On-site support.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h4>Visitor Tracking</h4><p>Strict logging.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h4>Fire Safety</h4><p>Extinguishers & exits.</p></div></div>
                        <div class="safety-item"><span class="ic">●</span><div><h4>Separate Buildings</h4><p>Boys & girls.</p></div></div>
                    </div>
                </div>
                <div class="safety-visual reveal">
                    <img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?q=80&w=900" alt="24/7 CCTV monitored entrance, Harini Girls Hostel">
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
                <span class="nearby-chip">📍 Alandur Metro (1 min)</span>
                <span class="nearby-chip">📍 St. Thomas Mount Railway Station</span>
                <span class="nearby-chip">📍 Perungalathur Railway Station</span>
                <span class="nearby-chip">📍 Guindy Industrial Estate</span>
                <span class="nearby-chip">📍 Chennai Airport (7 km)</span>
                <span class="nearby-chip">📍 Kathipara Junction</span>
                <span class="nearby-chip">📍 Ekkatuthangal Metro</span>
                <span class="nearby-chip">📍 Nanganallur</span>
                <span class="nearby-chip">📍 Tambaram</span>
                <span class="nearby-chip">📍 Vandalur</span>
                <span class="nearby-chip">📍 GST Road</span>
                <span class="nearby-chip">📍 Velachery Railway Station</span>
            </div>
        </div>
    </section>
@endsection