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

{{-- ===== HERO: headline + quick enquiry form ===== --}}
<section class="hero" aria-labelledby="hero-title">
    <img class="hero-photo" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1600"
         alt="" width="1600" height="900" loading="eager" decoding="async">
    <div class="wrap hero-grid">
        <div>
            <span class="hero-kicker"><svg class="i"><use href="#i-pin"/></svg> Alandur, St. Thomas Mount &amp; Perungalathur</span>
            <h1 id="hero-title">Furnished PG rooms in Chennai, next to the metro</h1>
            <p class="sub">Sanjay Boys Hostel and Harini Girls Hostel offer luxury and budget rooms with home-style food, WiFi, a gym and 24/7 security. Made for IT professionals, students and airport staff.</p>
            <ul class="hero-points">
                <li><svg class="i"><use href="#i-check"/></svg> AC and non-AC rooms</li>
                <li><svg class="i"><use href="#i-check"/></svg> 4 home-style meals a day</li>
                <li><svg class="i"><use href="#i-check"/></svg> High-speed WiFi</li>
                <li><svg class="i"><use href="#i-check"/></svg> 24/7 CCTV and warden</li>
                <li><svg class="i"><use href="#i-check"/></svg> Gym in the building</li>
                <li><svg class="i"><use href="#i-check"/></svg> Alandur Metro, 1 min</li>
            </ul>
            <div class="hero-actions">
                <a href="tel:+919876543210" class="btn btn-primary"><svg class="i"><use href="#i-phone"/></svg> Call +91 98765 43210</a>
                <a href="https://wa.me/919876543210?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability" class="btn btn-ghost" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> WhatsApp us</a>
            </div>
        </div>

        <div class="enquiry form-shell" id="enquire">
            <h2>Check room availability</h2>
            <p>Leave your number and we'll call you back with rooms and rates.</p>
            <form action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div><label for="hq-name">Full name</label><input id="hq-name" type="text" name="name" required autocomplete="name"></div>
                    <div><label for="hq-phone">Phone</label><input id="hq-phone" type="tel" name="phone" required autocomplete="tel" placeholder="+91"></div>
                </div>
                <div class="form-row">
                    <div><label for="hq-interest">I'm looking for</label>
                        <select id="hq-interest" name="interest">
                            <option value="sanjay_room">Boys PG (Sanjay)</option>
                            <option value="harini_room">Girls PG (Harini)</option>
                            <option value="lunch_box">Lunch box delivery</option>
                            <option value="general">Something else</option>
                        </select>
                    </div>
                    <div><label for="hq-branch">Branch</label>
                        <select id="hq-branch" name="branch">
                            <option value="alandur">Alandur</option>
                            <option value="st_thomas_mount">St. Thomas Mount</option>
                            <option value="perungalathur">Perungalathur (boys only)</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="message" value="Quick enquiry from home page">
                <button type="submit" class="form-submit">Get a call back</button>
                <p class="form-note">No booking fee. We only use your number to reply to this enquiry.</p>
            </form>
        </div>
    </div>
</section>

{{-- ===== TRUST STRIP ===== --}}
<section class="trust" aria-label="Highlights">
    <div class="wrap trust-grid">
        <div class="trust-item"><span class="ic"><svg class="i"><use href="#i-pin"/></svg></span><div><strong>3 branches</strong><span>Alandur, St. Thomas Mount, Perungalathur</span></div></div>
        <div class="trust-item"><span class="ic"><svg class="i"><use href="#i-shield"/></svg></span><div><strong>24/7 security</strong><span>CCTV, biometric entry, on-site warden</span></div></div>
        <div class="trust-item"><span class="ic"><svg class="i"><use href="#i-food"/></svg></span><div><strong>4 meals a day</strong><span>Veg and non-veg, home style</span></div></div>
        <div class="trust-item"><span class="ic"><svg class="i"><use href="#i-train"/></svg></span><div><strong>Near metro and rail</strong><span>Alandur Metro, St. Thomas Mount, Perungalathur</span></div></div>
    </div>
</section>

{{-- ===== ROOM CATEGORIES ===== --}}
<section class="panel-ivory" id="rooms" aria-labelledby="categories-title">
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">Rooms and rates</span>
            <h2 id="categories-title">Pick luxury or budget. Both include food and WiFi.</h2>
            <p>Monthly rent per person. Luxury gives you an attached bathroom and AC. Normal keeps it simple and affordable. Gym is free in both.</p>
        </div>

        <div class="category-grid">
            <div class="category-card luxury">
                <span class="cat-tag">Premium</span>
                <h3>Luxury PG</h3>
                <p>Top-tier amenities for those who want the best.</p>
                <div class="price-block">
                    <div class="price">₹12,000 <span>/ month</span></div>
                    <div class="price-note">EB bill included up to 200 units. Extra units ₹8 each.</div>
                </div>
                <ul class="amenities-list">
                    <li><svg class="i check"><use href="#i-check"/></svg><span><span class="highlight-text">Premium bed</span> (6×2 ft) with premium mattress</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span><span class="highlight-text">Attached bathroom</span> with geyser</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span>AC room with 43" smart TV</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span><span class="highlight-text">Free gym access</span> with premium equipment</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span>Gourmet meals, veg and non-veg</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span>Fibre WiFi (100 Mbps)</span></li>
                </ul>
                <details class="more">
                    <summary>See everything included</summary>
                    <ul class="amenities-list">
                        <li><svg class="i check"><use href="#i-check"/></svg><span>Induction stove for personal cooking</span></li>
                        <li><svg class="i check"><use href="#i-check"/></svg><span>Washing machine (in-room or shared)</span></li>
                        <li><svg class="i check"><use href="#i-check"/></svg><span>Water heater and RO purified water</span></li>
                        <li><svg class="i check"><use href="#i-check"/></svg><span>24/7 concierge and housekeeping</span></li>
                        <li><svg class="i check"><use href="#i-check"/></svg><span>Study desk, wardrobe and power backup</span></li>
                    </ul>
                </details>
                <div class="card-foot"><a href="{{ route('contact') }}" class="btn btn-primary btn-block">Enquire about luxury</a></div>
            </div>

            <div class="category-card normal">
                <span class="cat-tag">Budget friendly</span>
                <h3>Normal PG</h3>
                <p>Comfortable, well-kept rooms for students and working professionals.</p>
                <div class="price-block">
                    <div class="price">₹7,250 <span>/ month</span></div>
                    <div class="price-note">EB bill extra, on meter reading. Free gym access included.</div>
                </div>
                <ul class="amenities-list">
                    <li><svg class="i check"><use href="#i-check"/></svg><span><span class="highlight-text">Comfortable bed</span> (6×2 ft) with good mattress</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span><span class="highlight-text">Shared bathroom</span>, well maintained</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span>AC or non-AC room options</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span>Home-style meals, veg and non-veg</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span><span class="highlight-text">Free gym access</span></span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span>High-speed WiFi and 24/7 CCTV</span></li>
                    <li><svg class="i check"><use href="#i-check"/></svg><span>Daily housekeeping and power backup</span></li>
                </ul>
                <details class="more">
                    <summary>See everything included</summary>
                    <ul class="amenities-list">
                        <li><svg class="i check"><use href="#i-check"/></svg><span>Induction stove in the common kitchen</span></li>
                        <li><svg class="i check"><use href="#i-check"/></svg><span>Washing machine in the common area</span></li>
                        <li><svg class="i check"><use href="#i-check"/></svg><span>Water heater and RO purified water</span></li>
                    </ul>
                </details>
                <div class="card-foot"><a href="{{ route('contact') }}" class="btn btn-dark btn-block">Enquire about normal</a></div>
            </div>
        </div>
        <p class="price-note" style="margin-top:16px;">See single, double, triple and dormitory rates on the <a href="{{ route('rooms') }}" style="color:var(--boys);font-weight:700;text-decoration:underline;">rooms and pricing page</a>.</p>
    </div>
</section>

{{-- ===== LOCATIONS ===== --}}
<section aria-labelledby="locations-title">
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">Our branches</span>
            <h2 id="locations-title">Three locations, all close to the metro or railway</h2>
            <p>Choose the branch nearest to your office or college. All are within reach of Alandur Metro, St. Thomas Mount station and Chennai Airport.</p>
        </div>
        <div class="location-grid">
            <div class="location-card">
                <div class="who"><span class="pill">Boys</span><span class="pill girls">Girls</span></div>
                <h4>Alandur</h4>
                <p>Near Alandur Metro Station, Guindy and Nanganallur. Luxury and normal rooms. Good for IT professionals and students.</p>
                <a class="go" href="{{ route('contact') }}">Enquire for Alandur <svg class="i"><use href="#i-arrow"/></svg></a>
            </div>
            <div class="location-card">
                <div class="who"><span class="pill">Boys</span><span class="pill girls">Girls</span></div>
                <h4>St. Thomas Mount</h4>
                <p>Near St. Thomas Mount Railway Station, Kathipara and Chennai Airport Metro. Luxury and normal rooms.</p>
                <a class="go" href="{{ route('contact') }}">Enquire for St. Thomas Mount <svg class="i"><use href="#i-arrow"/></svg></a>
            </div>
            <div class="location-card">
                <div class="who"><span class="pill">Boys only</span></div>
                <h4>Perungalathur</h4>
                <p>Near Perungalathur Railway Station, Tambaram, Vandalur and GST Road. Normal rooms only.</p>
                <a class="go" href="{{ route('contact') }}">Enquire for Perungalathur <svg class="i"><use href="#i-arrow"/></svg></a>
            </div>
        </div>
    </div>
</section>

{{-- ===== GYM ===== --}}
<section class="panel-ivory" aria-labelledby="gym-title">
    <div class="wrap">
        <div class="gym-grid">
            <div class="gym-content">
                <span class="eyebrow">In-house gym</span>
                <h2 id="gym-title">Work out without leaving the building</h2>
                <p>A fully equipped gym for residents, open 6 AM to 10 PM. Whether you're starting out or training regularly, everything you need is downstairs.</p>
                <div class="gym-features">
                    <div class="gf"><svg class="i"><use href="#i-gym"/></svg> Cardio equipment</div>
                    <div class="gf"><svg class="i"><use href="#i-gym"/></svg> Weight training area</div>
                    <div class="gf"><svg class="i"><use href="#i-gym"/></svg> Treadmill and cross trainer</div>
                    <div class="gf"><svg class="i"><use href="#i-gym"/></svg> Yoga and stretching zone</div>
                    <div class="gf"><svg class="i"><use href="#i-check"/></svg> 6 AM to 10 PM access</div>
                    <div class="gf"><svg class="i"><use href="#i-users"/></svg> Trainer available</div>
                </div>
                <div class="gym-rate"><span><b>Free for all residents</b>, luxury and normal</span></div>
                <a href="{{ route('contact') }}" class="btn btn-primary">Check gym availability</a>
            </div>
            <div class="gym-visual">
                <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600" alt="Gym at Sanjay Boys Hostel, Chennai" width="300" height="240" loading="lazy" decoding="async">
                <img src="https://images.unsplash.com/photo-1549060279-7e168fcee0c2?q=80&w=600" alt="Cardio equipment at Harini Girls Hostel gym" width="300" height="240" loading="lazy" decoding="async">
                <img class="full" src="https://images.unsplash.com/photo-1538805060514-97d9cc17730c?q=80&w=600" alt="Weight training area in the PG hostel gym" width="600" height="200" loading="lazy" decoding="async">
            </div>
        </div>
    </div>
</section>

{{-- ===== ABOUT ===== --}}
<section id="about" aria-labelledby="about-title">
    <div class="wrap">
        <div class="about-wrap">
            <div class="about-copy">
                <span class="eyebrow">About us</span>
                <h2 id="about-title">Sanjay Boys and Harini Girls: a trusted PG in Chennai</h2>
                <p><strong>Sanjay Boys Hostel</strong> and <strong>Harini Girls Hostel</strong> offer PG accommodation in Alandur, St. Thomas Mount and Perungalathur, with luxury and normal rooms, a gym, AC and non-AC options and home-style meals.</p>
                <p>Our hostels suit IT employees, working professionals, college students, airport staff and metro commuters, with 24/7 security, high-speed WiFi and daily housekeeping.</p>
                <div class="about-stats">
                    <div class="stat"><div class="num">3</div><div class="label">Branches</div></div>
                    <div class="stat"><div class="num">Gym</div><div class="label">In the building</div></div>
                    <div class="stat"><div class="num">24/7</div><div class="label">Security and support</div></div>
                    <div class="stat"><div class="num">500+</div><div class="label">Happy residents</div></div>
                </div>
                <a href="{{ route('about') }}">Learn more about us</a>
            </div>
            <div class="about-visual">
                <img class="tall" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=800" alt="Boys hostel room in Alandur, Chennai" width="400" height="340" loading="lazy" decoding="async">
                <div class="col">
                    <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=600" alt="Harini Girls Hostel lounge in St. Thomas Mount" width="300" height="240" loading="lazy" decoding="async">
                    <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=600" alt="Shared dining area at Sanjay Boys Hostel" width="300" height="240" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== WHY CHOOSE US ===== --}}
<section class="panel-ivory" aria-labelledby="why-title">
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">Why residents choose us</span>
            <h2 id="why-title">Everything a PG stay needs, in one place</h2>
        </div>
        <div class="why-grid">
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-bed"/></svg></span><p>Luxury and normal options</p></div>
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-gym"/></svg></span><p>Free gym for all residents</p></div>
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-train"/></svg></span><p>Near metro and railway</p></div>
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-snow"/></svg></span><p>AC and non-AC rooms</p></div>
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-wifi"/></svg></span><p>High-speed WiFi</p></div>
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-shield"/></svg></span><p>24/7 CCTV security</p></div>
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-food"/></svg></span><p>Home-style food</p></div>
            <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-users"/></svg></span><p>Separate, safe hostel for women</p></div>
        </div>
    </div>
</section>

{{-- ===== FAQ ===== --}}
<section aria-labelledby="faq-title">
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">FAQs</span>
            <h2 id="faq-title">Questions we hear most</h2>
        </div>
        <div class="faq-list">
            @foreach ([
                ['What is included in the Luxury PG?', 'Luxury PG includes a premium bed (6×2 ft), attached bathroom, induction stove, washing machine, water heater, EB bill up to 200 units, free gym access, AC room with smart TV, high-speed WiFi and gourmet meals.'],
                ['What is included in the Normal PG?', 'Normal PG includes a comfortable bed (6×2 ft), shared bathroom, common kitchen with induction stove, common washing machine, water heater with RO water, AC or non-AC options, free gym access, WiFi, CCTV and home-style meals. EB bill is extra.'],
                ['Is the gym free for all residents?', 'Yes. Gym access is free for both Luxury and Normal PG residents.'],
                ['How does the EB bill work?', 'For Luxury PG, EB bill is included up to 200 units per month, and extra units are charged at ₹8 per unit. For Normal PG, EB bill is charged as per your individual meter reading.'],
                ['Do you provide a washing machine and induction stove?', 'Yes. Luxury rooms have an in-room washing machine and induction stove. Normal PG has a common washing machine and a common kitchen with an induction stove.'],
                ['Do you provide boys PG accommodation in Alandur?', 'Yes. Sanjay Boys Hostel offers boys PG accommodation in Alandur, near Alandur Metro Station and Guindy.'],
                ['Do you provide girls hostel accommodation?', 'Yes. Harini Girls Hostel provides safe, comfortable accommodation for women in Alandur and St. Thomas Mount, with 24/7 security.'],
                ['What are the food options?', 'We serve home-style meals with vegetarian and non-vegetarian options daily. Luxury residents get gourmet meal options.'],
            ] as $faq)
                <div class="faq-item">
                    <button class="faq-q" aria-expanded="false">{{ $faq[0] }}<span class="plus" aria-hidden="true">+</span></button>
                    <div class="faq-a">{{ $faq[1] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== TESTIMONIALS ===== --}}
<section class="panel-ivory" aria-labelledby="testimonials-title">
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">Resident reviews</span>
            <h2 id="testimonials-title">What our residents say</h2>
        </div>
        <div class="testi-grid">
            <div class="testi-card">
                <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                <p class="quote">"The luxury PG is amazing! Premium bed, attached bathroom and an induction stove made cooking so easy. Best PG in Alandur!"</p>
                <div class="who">Arun Kumar, Luxury resident, Alandur</div>
            </div>
            <div class="testi-card">
                <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                <p class="quote">"Safe, affordable, and the gym is a bonus! Harini Girls Hostel is perfect for working women in Chennai."</p>
                <div class="who">Priya Sharma, Harini Girls Hostel</div>
            </div>
            <div class="testi-card">
                <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                <p class="quote">"Great location near St. Thomas Mount station. The gym and WiFi are excellent. Highly recommend!"</p>
                <div class="who">Suresh Raj, Sanjay Boys Hostel, St. Thomas Mount</div>
            </div>
        </div>
    </div>
</section>

{{-- ===== FINAL CTA ===== --}}
<section class="final-cta" aria-labelledby="cta-title">
    <div class="wrap">
        <div>
            <div class="strap">Limited gym slots available</div>
            <h2 id="cta-title">See a room this week</h2>
            <p class="lead">Call or message us to check what's free at your branch, then visit before you decide. Luxury or normal, gym and food included in the plan you choose.</p>
        </div>
        <div class="hero-actions">
            <a href="tel:+919876543210" class="btn btn-primary"><svg class="i"><use href="#i-phone"/></svg> Call +91 98765 43210</a>
            <a href="https://wa.me/919876543210?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability" class="btn btn-wa" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> Message on WhatsApp</a>
            <a href="{{ route('contact') }}" class="btn btn-ghost">Send an enquiry</a>
        </div>
    </div>
</section>

@endsection
