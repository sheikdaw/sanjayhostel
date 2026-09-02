@extends('layouts.frontend')

{{-- TASK 1: Title Tag (optimized for CTR & Local SEO) --}}
@section('title', 'PG in Alandur, Chennai | Sanjay Boys & Harini Girls Hostel')

{{-- TASK 2: Meta Description (compelling, ~155 chars) --}}
@section('meta_description', 'Sanjay Boys Hostel and Harini Girls Hostel offer premium PG accommodation in Alandur, St. Thomas Mount & Perungalathur. AC/Non-AC rooms, home food, WiFi, CCTV. Near metro & railway. Book now!')

{{-- TASK 3: Canonical URL (ensures there is only one) --}}
@section('canonical', 'https://www.sanjayandharinihostels.com/')

{{-- TASK 10: Open Graph (OG) Meta Tags --}}
@section('og_title', 'PG in Alandur, Chennai | Sanjay Boys & Harini Girls Hostel')
@section('og_description', 'Safe, affordable boys & girls PG accommodation in Alandur, St. Thomas Mount, and Perungalathur. AC/Non-AC rooms, food, WiFi, CCTV. Near metro & railway.')
@section('og_url', 'https://www.sanjayandharinihostels.com/')
@section('og_type', 'website')
@section('og_image', 'https://www.sanjayandharinihostels.com/images/og-image.jpg')

{{-- TASK 11: Twitter Card Meta Tags --}}
@section('twitter_card', 'summary_large_image')
@section('twitter_title', 'PG in Alandur, Chennai | Sanjay Boys & Harini Girls Hostel')
@section('twitter_description', 'Premium boys & girls PG accommodation in Alandur, St. Thomas Mount & Perungalathur. AC/Non-AC rooms, food, WiFi, CCTV. Near metro & railway.')
@section('twitter_image', 'https://www.sanjayandharinihostels.com/images/og-image.jpg')

{{-- TASK 14: JSON-LD Structured Data --}}
@section('schema')
@verbatim
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "Organization",
            "name": "Sanjay & Harini Hostels",
            "url": "https://www.sanjayandharinihostels.com/",
            "logo": "https://www.sanjayandharinihostels.com/images/logo.png",
            "description": "Premium boys and girls PG accommodation in Alandur, St. Thomas Mount, and Perungalathur, Chennai.",
            "contactPoint": {
                "@type": "ContactPoint",
                "telephone": "+91-9876543210",
                "contactType": "Sales",
                "availableLanguage": ["en", "ta"]
            },
            "sameAs": [
                "https://www.facebook.com/sanjayandharinihostels",
                "https://www.instagram.com/sanjayandharinihostels"
            ]
        },
        {
            "@type": "LocalBusiness",
            "name": "Sanjay Boys Hostel",
            "description": "Men's PG accommodation in Alandur and St. Thomas Mount, Chennai.",
            "branchOf": {
                "@type": "Organization",
                "name": "Sanjay & Harini Hostels"
            },
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Alandur",
                "addressRegion": "Tamil Nadu",
                "addressCountry": "IN"
            },
            "telephone": "+91-9876543210"
        },
        {
            "@type": "LocalBusiness",
            "name": "Harini Girls Hostel",
            "description": "Women's PG accommodation in Alandur and St. Thomas Mount, Chennai.",
            "branchOf": {
                "@type": "Organization",
                "name": "Sanjay & Harini Hostels"
            },
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Alandur",
                "addressRegion": "Tamil Nadu",
                "addressCountry": "IN"
            },
            "telephone": "+91-9876543210"
        },
        {
            "@type": "WebSite",
            "url": "https://www.sanjayandharinihostels.com/",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "https://www.sanjayandharinihostels.com/search?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        },
        {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "https://www.sanjayandharinihostels.com/"
                }
            ]
        }
    ]
}
</script>
@endverbatim
@endsection

@section('content')
    {{-- HERO Section --}}
    <section class="hero seam" aria-labelledby="hero-title">
        <div class="hero-bg">
            <div class="side left">
                <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=1200"
                     alt="Spacious boys PG room in Alandur, Chennai"
                     width="1200" height="800" loading="eager" decoding="async">
            </div>
            <div class="side right">
                <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=1200"
                     alt="Comfortable girls hostel room in St. Thomas Mount, Chennai"
                     width="1200" height="800" loading="eager" decoding="async">
            </div>
        </div>
        <div class="hero-content">
            <span class="hero-tag">PG in Alandur · St. Thomas Mount · Perungalathur</span>
            <h1 id="hero-title">Best PG &amp; Hostel in Alandur, Chennai | Sanjay Boys &amp; Harini Girls</h1>
            <p class="sub">Sanjay Boys Hostel &amp; Harini Girls Hostel — premium PG accommodation in Alandur, St. Thomas Mount, and Perungalathur. Choose from AC/Non-AC rooms, enjoy home-style food, high-speed WiFi, 24/7 CCTV security, and easy access to Alandur Metro and St. Thomas Mount Railway Station. Ideal for IT professionals, students, and airport staff.</p>
            <div class="hero-actions">
                <a href="{{ route('contact') }}" class="btn btn-primary">Book a Room Now →</a>
                <a href="{{ route('contact') }}" class="btn btn-ghost">Contact Us</a>
            </div>
            <div class="badge-row">
                <span class="badge"><span class="tick">✓</span> AC &amp; Non-AC Rooms</span>
                <span class="badge"><span class="tick">✓</span> High-Speed WiFi</span>
                <span class="badge"><span class="tick">✓</span> 24/7 CCTV</span>
                <span class="badge"><span class="tick">✓</span> Home-Style Food</span>
                <span class="badge"><span class="tick">✓</span> Near Alandur Metro</span>
                <span class="badge"><span class="tick">✓</span> Near St. Thomas Mount Station</span>
            </div>
        </div>
    </section>

    {{-- ABOUT Section --}}
    <section class="panel-ivory" id="about" aria-labelledby="about-title">
        <div class="wrap about">
            <div class="about-copy reveal">
                <span class="eyebrow">PG in Chennai — Alandur, St. Thomas Mount, Perungalathur</span>
                <h2 id="about-title">Safe, Affordable Hostels for Men &amp; Women in Chennai</h2>
                <p><strong>Sanjay Boys Hostel</strong> and <strong>Harini Girls Hostel</strong> provide premium paying guest accommodation in Alandur, St. Thomas Mount, and Perungalathur. We cater to IT employees, working professionals, college students, airport staff, and metro commuters looking for a comfortable, safe, and well-connected place to stay in Chennai.</p>
                <p>Each branch features modern amenities including AC and non-AC rooms, high-speed WiFi, 24/7 CCTV surveillance, RO purified water, daily laundry and housekeeping services, and nutritious home-style meals. Separate facilities for boys and girls ensure privacy, safety, and peace of mind. Explore our <a href="{{ route('rooms') }}#facilities" aria-label="View all PG facilities">complete list of PG facilities</a> or check our <a href="{{ route('rooms') }}#food" aria-label="View daily food menu">daily food menu</a>.</p>
                <div class="about-stats">
                    <div class="stat"><div class="num">3</div><div class="label">Branches</div></div>
                    <div class="stat"><div class="num">24/7</div><div class="label">Security &amp; Support</div></div>
                    <div class="stat"><div class="num">4</div><div class="label">Meals Daily</div></div>
                    <div class="stat"><div class="num">5+</div><div class="label">Room Types</div></div>
                </div>
            </div>
            <div class="about-visual reveal">
                <img class="tall" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=800"
                     alt="Clean and well-furnished boys hostel room in Alandur, Chennai"
                     width="400" height="600" loading="lazy" decoding="async">
                <div class="col">
                    <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=600"
                         alt="Harini Girls Hostel common lounge in St. Thomas Mount, Chennai"
                         width="300" height="280" loading="lazy" decoding="async">
                    <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=600"
                         alt="Sanjay Boys Hostel shared dining area in Alandur, Chennai"
                         width="300" height="280" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </section>

    {{-- BRANCH HIGHLIGHTS --}}
    <section aria-labelledby="locations-title">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">Our Locations</span>
                <h2 id="locations-title">PG Accommodation in Alandur, St. Thomas Mount &amp; Perungalathur</h2>
                <p>Choose your branch — all close to Alandur Metro, St. Thomas Mount Railway Station, Perungalathur Railway Station, Guindy, Nanganallur, Ekkatuthangal, Tambaram, Vandalur, GST Road, Kathipara, and Chennai Airport. <a href="{{ route('about') }}" style="color:var(--amber-deep);text-decoration:underline;" aria-label="Learn more about each location">Read more about each location →</a></p>
            </div>

            <div class="identity-grid reveal">
                <div class="identity-card boys">
                    <span class="tag-pill">Sanjay Boys Hostel</span>
                    <h3>Boys PG in Alandur &amp; St. Thomas Mount</h3>
                    <div class="branch-loc">📍 Alandur — Near Alandur Metro, Guindy, Nanganallur</div>
                    <div class="branch-loc">📍 St. Thomas Mount — Near St. Thomas Mount Railway Station, Kathipara, Chennai Airport Metro</div>
                    <ul>
                        <li>AC &amp; Non-AC rooms</li>
                        <li>High-speed WiFi, 24/7 CCTV, power backup</li>
                        <li>Home-style food (veg &amp; non-veg options)</li>
                        <li>Laundry &amp; housekeeping services</li>
                    </ul>
                    <a href="{{ route('contact') }}" style="display:inline-block;margin-top:8px;font-weight:700;color:var(--amber-deep);text-decoration:underline;" aria-label="Check room availability at Sanjay Boys Hostel">Check availability at Sanjay Boys Hostel →</a>
                </div>

                <div class="identity-card girls">
                    <span class="tag-pill">Harini Ladies Hostel</span>
                    <h3>Girls PG in Alandur &amp; St. Thomas Mount</h3>
                    <div class="branch-loc">📍 Alandur — Near Alandur Metro, Ekkatuthangal, Nanganallur</div>
                    <div class="branch-loc">📍 St. Thomas Mount — Near St. Thomas Mount, Chennai Airport Metro, Guindy</div>
                    <ul>
                        <li>Women-only safe accommodation</li>
                        <li>24/7 CCTV &amp; visitor management</li>
                        <li>Single, double, and triple sharing rooms</li>
                        <li>RO water, ironing, and study hall</li>
                    </ul>
                    <a href="{{ route('contact') }}" style="display:inline-block;margin-top:8px;font-weight:700;color:var(--rose-deep);text-decoration:underline;" aria-label="Check room availability at Harini Girls Hostel">Check availability at Harini Girls Hostel →</a>
                </div>
            </div>

            <div style="margin-top:32px;text-align:center;background:var(--cream);border:1px solid var(--line);border-radius:var(--radius-lg);padding:32px;">
                <span class="tag-pill" style="background:var(--rose);color:var(--cream);">Perungalathur — Boys Only</span>
                <h3 style="margin-top:14px;font-size:1.3rem;">Men's PG Hostel in Perungalathur, Chennai</h3>
                <p style="color:var(--stone);margin-top:8px;">Sanjay Boys Hostel at Perungalathur — conveniently located near Perungalathur Railway Station, Tambaram, Vandalur, and GST Road. Ideal for students and working professionals seeking affordable and well-connected PG accommodation in South Chennai.
                    <a href="{{ route('contact') }}" style="color:var(--rose-deep);text-decoration:underline;font-weight:700;" aria-label="Enquire about the Perungalathur branch">Enquire about the Perungalathur branch →</a>
                </p>
            </div>
        </div>
    </section>

    {{-- WHY CHOOSE US --}}
    <section class="panel-ivory" aria-labelledby="why-title">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">Why Stay With Us</span>
                <h2 id="why-title">Why Choose Our PG Hostel in Chennai</h2>
            </div>
            <div class="why-grid reveal">
                <div class="why-item"><span class="tick-circ">✓</span><p>Affordable Rent</p></div>
                <div class="why-item"><span class="tick-circ">✓</span><p>Near Metro &amp; Railway</p></div>
                <div class="why-item"><span class="tick-circ">✓</span><p>AC / Non-AC Rooms</p></div>
                <div class="why-item"><span class="tick-circ">✓</span><p>WiFi &amp; CCTV</p></div>
                <div class="why-item"><span class="tick-circ">✓</span><p>Home-Style Food</p></div>
                <div class="why-item"><span class="tick-circ">✓</span><p>Laundry Services</p></div>
                <div class="why-item"><span class="tick-circ">✓</span><p>Power Backup</p></div>
                <div class="why-item"><span class="tick-circ">✓</span><p>Safe for Women</p></div>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section aria-labelledby="faq-title">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">FAQs</span>
                <h2 id="faq-title">Frequently Asked Questions</h2>
            </div>
            <div class="faq-list reveal">
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
                        Do you have AC and non-AC rooms?
                        <span class="plus" aria-hidden="true">+</span>
                    </button>
                    <div class="faq-a">Yes, we offer both AC and non-AC rooms with single, double, and triple sharing options at all our branches.</div>
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
                        Is food provided at the PG?
                        <span class="plus" aria-hidden="true">+</span>
                    </button>
                    <div class="faq-a">Yes, we provide nutritious home-style meals with both vegetarian and non-vegetarian options daily.</div>
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
                        Is the hostel near St. Thomas Mount Railway Station?
                        <span class="plus" aria-hidden="true">+</span>
                    </button>
                    <div class="faq-a">Yes, our St. Thomas Mount branch is situated near the St. Thomas Mount Railway Station and Chennai Airport Metro.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-q" aria-expanded="false">
                        Do you have accommodation in Perungalathur?
                        <span class="plus" aria-hidden="true">+</span>
                    </button>
                    <div class="faq-a">Yes, Sanjay Boys Hostel has a dedicated branch in Perungalathur, near Perungalathur Railway Station, Tambaram, and Vandalur.</div>
                </div>
            </div>
        </div>
    </section>

    {{-- TESTIMONIALS --}}
    <section class="panel-ivory" aria-labelledby="testimonials-title">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">Testimonials</span>
                <h2 id="testimonials-title">What Our Residents Say</h2>
            </div>
            <div class="testi-grid reveal">
                <div class="testi-card">
                    <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                    <p class="quote">Best PG near Alandur Metro — clean rooms, good food, and very safe for women. Highly recommend Harini Girls Hostel!</p>
                    <div class="who">Harini Girls Hostel Resident</div>
                </div>
                <div class="testi-card">
                    <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                    <p class="quote">I work at Chennai Airport, and this hostel is perfectly located. Affordable, great WiFi, and friendly staff at Sanjay Boys Hostel.</p>
                    <div class="who">Sanjay Boys Hostel, St. Thomas Mount Resident</div>
                </div>
                <div class="testi-card">
                    <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
                    <p class="quote">Perfect for IT professionals — attached bathroom, study table, power backup, and homely atmosphere. Best PG in Alandur!</p>
                    <div class="who">Working Professional, Alandur</div>
                </div>
            </div>
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section class="final-cta" aria-labelledby="cta-title">
        <div class="wrap">
            <div class="strap">Trusted by 500+ Residents</div>
            <h2 id="cta-title">Your Home Away From Home in Chennai</h2>
            <p class="lead">Sanjay Boys Hostel &amp; Harini Girls Hostel — the best PG in Alandur, St. Thomas Mount, and Perungalathur. Book your room today!</p>
            <div class="hero-actions">
                <a href="{{ route('contact') }}" class="btn btn-primary">Book Your Room Now →</a>
                <a href="{{ route('contact') }}" class="btn btn-ghost">Contact Us</a>
            </div>
        </div>
    </section>
@endsection