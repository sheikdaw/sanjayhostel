@extends('layouts.frontend')

@section('title', 'Boys & Girls PG in Alandur & Perungalathur, Chennai | Sanjay & Harini')

@section('meta_description', 'Looking for PG in Alandur or Perungalathur? Sanjay & Harini offers boys & girls PG near Alandur Metro with AC rooms, food, WiFi, CCTV & gym. Rent from ₹7,250. Call now.')

@section('meta_keywords', 'PG in Alandur, PG in Perungalathur, boys PG Chennai, girls PG Chennai, working women hostel Chennai, PG near Alandur Metro, PG with food Chennai, AC PG rooms Alandur, hostel with gym Chennai, monthly PG Chennai, gents PG near Guindy, ladies hostel St Thomas Mount, boys PG near Tambaram')

@section('canonical', \App\Support\Seo::url('/'))

@section('og_title', 'Boys & Girls PG in Alandur & Perungalathur, Chennai | Sanjay & Harini')

@section('og_description', 'Six boys and girls hostels across Alandur and Perungalathur, Chennai with AC rooms, food, WiFi, CCTV and free gym. Near Alandur Metro. Rent from ₹7,250/month.')

@section('og_url', \App\Support\Seo::url('/'))

@section('og_type', 'website')

@section('og_image', asset('images/hostel/hero.jpg'))


@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [

        {
            "@type": "Organization",
            "@id": "{{ url('/') }}#organization",
            "name": "Sanjay & Harini Hostels",
            "alternateName": "Sanjay and Harini PG Hostels Chennai",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo.png') }}",
            @if(config('hostel.phone'))
            "telephone": "{{ config('hostel.phone') }}",
            @endif
            @if(config('hostel.email'))
            "email": "{{ config('hostel.email') }}",
            @endif
            "description": "Boys and girls PG hostels in Alandur and Perungalathur, Chennai with AC rooms, food, WiFi, CCTV and free gym access.",
            "areaServed": [
                {"@type": "City", "name": "Chennai"},
                {"@type": "Place", "name": "Alandur"},
                {"@type": "Place", "name": "Perungalathur"},
                {"@type": "Place", "name": "Guindy"},
                {"@type": "Place", "name": "Tambaram"}
            ],
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Alandur",
                "addressRegion": "Tamil Nadu",
                "postalCode": "600016",
                "addressCountry": "IN"
            }
        },

        {
            "@type": "WebSite",
            "@id": "{{ url('/') }}#website",
            "url": "{{ url('/') }}",
            "name": "Sanjay & Harini Hostels",
            "publisher": {"@id": "{{ url('/') }}#organization"},
            "inLanguage": "en-IN"
        },

        {
            "@type": "WebPage",
            "@id": "{{ url('/') }}#webpage",
            "url": "{{ url('/') }}",
            "name": "Boys & Girls PG in Alandur & Perungalathur, Chennai | Sanjay & Harini",
            "description": "Boys and girls PG hostels in Alandur and Perungalathur, Chennai with AC rooms, food, WiFi, CCTV and gym. Near Alandur Metro.",
            "isPartOf": {"@id": "{{ url('/') }}#website"},
            "about": {"@id": "{{ url('/') }}#organization"},
            "inLanguage": "en-IN"
        },

        {
            "@type": "Hostel",
            "@id": "{{ url('/') }}#hostel-alandur",
            "name": "Sanjay & Harini Hostel — Alandur, Chennai",
            "url": "{{ route('hostels.alandur') }}",
            "telephone": "{{ config('hostel.phone') }}",
            "priceRange": "₹7,250 - ₹12,000",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Pudupettai Street, Alandur",
                "addressLocality": "Chennai",
                "addressRegion": "Tamil Nadu",
                "postalCode": "600016",
                "addressCountry": "IN"
            },
            "geo": {
                "@type": "GeoCoordinates",
                "latitude": "13.0045",
                "longitude": "80.2015"
            },
            "amenityFeature": [
                {"@type": "LocationFeatureSpecification", "name": "Free WiFi", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "Food Included", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "Gym Access", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "CCTV Security", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "AC Rooms", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "Power Backup", "value": true}
            ]
        },

        {
            "@type": "Hostel",
            "@id": "{{ url('/') }}#hostel-perungalathur",
            "name": "Sanjay & Harini Boys Hostel — Perungalathur, Chennai",
            "url": "{{ route('hostels.perungalathur') }}",
            "telephone": "{{ config('hostel.phone') }}",
            "priceRange": "₹7,250 - ₹12,000",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Perungalathur",
                "addressLocality": "Chennai",
                "addressRegion": "Tamil Nadu",
                "addressCountry": "IN"
            },
            "amenityFeature": [
                {"@type": "LocationFeatureSpecification", "name": "Free WiFi", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "Food Included", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "Gym Access", "value": true},
                {"@type": "LocationFeatureSpecification", "name": "CCTV Security", "value": true}
            ]
        },

        {
            "@type": "ItemList",
            "@id": "{{ url('/') }}#hostels",
            "name": "Sanjay & Harini Hostel Locations in Chennai",
            "itemListElement": [
                {"@type": "ListItem", "position": 1, "name": "Men's PG Hostel — Pudupettai Street, Alandur", "url": "{{ route('hostels.alandur') }}#pudupettai"},
                {"@type": "ListItem", "position": 2, "name": "Women's PG Hostel — Pudupettai Street, Alandur", "url": "{{ route('hostels.alandur') }}#pudupettai"},
                {"@type": "ListItem", "position": 3, "name": "Women's PG Hostel 2 — Pudupettai Street, Alandur", "url": "{{ route('hostels.alandur') }}#pudupettai"},
                {"@type": "ListItem", "position": 4, "name": "Men's PG Hostel — M.K.N. Road, Alandur", "url": "{{ route('hostels.alandur') }}#mkn"},
                {"@type": "ListItem", "position": 5, "name": "Ladies Hostel — Raja Street, Alandur", "url": "{{ route('hostels.alandur') }}#raja"},
                {"@type": "ListItem", "position": 6, "name": "Boys Hostel — Perungalathur", "url": "{{ route('hostels.perungalathur') }}"}
            ]
        },

        {
            "@type": "FAQPage",
            "@id": "{{ url('/') }}#faq",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "Is there a PG near Alandur Metro Station?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes. Our Pudupettai Street and M.K.N. Road branches are within walking distance from Alandur Metro Station, ideal for daily commuters working in Guindy, Ekkatuthangal and St. Thomas Mount."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Do you have a working women's hostel in Chennai?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes. We have two dedicated women's hostels at Pudupettai Street and one ladies' hostel at Raja Street, Alandur with CCTV, warden support and home-style food."
                    }
                },
                {
                    "@type": "Question",
                    "name": "What is the monthly PG rent in Alandur?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Normal PG starts at ₹7,250 per month with EB charges extra as per meter. Luxury AC PG with attached bathroom starts at ₹12,000 per month with EB included up to 200 units."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Do you provide PG for IT professionals near Guindy?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes. Our Alandur branches are about 10 minutes from Guindy and Ekkatuthangal, making them popular with IT professionals and working women."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Is food included in the PG rent?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes. Home-style vegetarian and non-vegetarian meals are included in the monthly rent at our boys and girls PG hostels in Chennai."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Do you have AC PG rooms in Chennai?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes. Both our Luxury PG and Normal PG categories offer AC and non-AC room options depending on the branch and availability."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Which is the best PG for students near Alandur?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Our Pudupettai Street and Raja Street hostels are popular with students due to their proximity to Alandur Metro, colleges, and food options."
                    }
                },
                {
                    "@type": "Question",
                    "name": "How many hostels do Sanjay & Harini operate in Chennai?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Sanjay & Harini Hostels operates six hostels — five in Alandur (Pudupettai Street, M.K.N. Road, and Raja Street) and one boys hostel in Perungalathur."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Do you have a boys hostel in Perungalathur near Tambaram?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes. We operate a boys hostel in Perungalathur, convenient for students and working professionals travelling around Tambaram, Vandalur and GST Road."
                    }
                },
                {
                    "@type": "Question",
                    "name": "How can I check PG room availability?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "You can call us, send a WhatsApp message, or submit the enquiry form on this website. We will share current room availability and pricing for your preferred location."
                    }
                }
            ]
        },

        {
            "@type": "BreadcrumbList",
            "@id": "{{ url('/') }}#breadcrumb",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "{{ url('/') }}"
                }
            ]
        }

    ]
}
</script>
@endsection


@section('content')


    {{-- =========================================================
     HERO
========================================================= --}}

    <section class="hero" aria-labelledby="hero-title">

        <img class="hero-photo"
             src="{{ asset('images/hostel/hero.jpg') }}"
             alt="Boys and girls PG hostel building in Alandur Chennai near metro station"
             width="1200"
             height="675"
             fetchpriority="high"
             decoding="async">


        <div class="wrap hero-grid">

            <div>

                <span class="hero-kicker">

                    <svg class="i">
                        <use href="#i-pin" />
                    </svg>

                    Alandur &amp; Perungalathur, Chennai

                </span>


                <h1 id="hero-title">
                    Boys &amp; Girls PG Hostels in Alandur &amp; Perungalathur, Chennai
                </h1>


                <p class="sub">
                    Sanjay &amp; Harini Hostels operates <strong>six boys and girls PG hostels
                    in Alandur and Perungalathur, Chennai</strong>. We offer comfortable
                    <strong>PG accommodation near Alandur Metro</strong>, Guindy, St. Thomas Mount
                    and Tambaram with AC rooms, home-style food, high-speed WiFi, CCTV
                    security and free gym access.
                </p>


                <ul class="hero-points">

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        AC &amp; non-AC PG rooms
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        Home-style food included
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        High-speed WiFi
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        CCTV &amp; hostel security
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        Free gym for residents
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        Walking distance to Metro
                    </li>

                </ul>


                <div class="hero-actions">

                    <a href="tel:{{ config('hostel.phone') }}" class="btn btn-primary">

                        <svg class="i">
                            <use href="#i-phone" />
                        </svg>

                        Call {{ config('hostel.phone_display') }}

                    </a>


                    <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability%20in%20Alandur%20%2F%20Perungalathur"
                       class="btn btn-ghost"
                       target="_blank"
                       rel="noopener noreferrer">

                        <svg class="i">
                            <use href="#i-chat" />
                        </svg>

                        WhatsApp Us

                    </a>

                </div>

            </div>


            {{-- ENQUIRY FORM --}}

            <div class="enquiry form-shell" id="enquire">

                <h2>
                    Check PG Room Availability
                </h2>

                <p>
                    Tell us your preferred location &amp; room type — we will
                    share current availability and monthly rent.
                </p>


                <form action="{{ route('contact.submit') }}" method="POST">

                    @csrf


                    <div class="form-row">

                        <div>

                            <label for="hq-name">
                                Full Name
                            </label>

                            <input id="hq-name" type="text" name="name" required autocomplete="name">

                        </div>


                        <div>

                            <label for="hq-phone">
                                Phone
                            </label>

                            <input id="hq-phone" type="tel" name="phone" required autocomplete="tel"
                                placeholder="+91">

                        </div>

                    </div>


                    <div class="form-row">

                        <div>

                            <label for="hq-interest">
                                I am looking for
                            </label>

                            <select id="hq-interest" name="interest">

                                <option value="sanjay_room">
                                    Boys PG
                                </option>

                                <option value="harini_room">
                                    Girls PG
                                </option>

                                <option value="lunch_box">
                                    Lunch Box Delivery
                                </option>

                                <option value="general">
                                    General Enquiry
                                </option>

                            </select>

                        </div>


                        <div>

                            <label for="hq-branch">
                                Preferred Location
                            </label>

                            <select id="hq-branch" name="branch">

                                <option value="pudupettai_alandur">
                                    Pudupettai Street, Alandur
                                </option>

                                <option value="mkn_road_alandur">
                                    M.K.N. Road, Alandur
                                </option>

                                <option value="raja_street_alandur">
                                    Raja Street, Alandur
                                </option>

                                <option value="perungalathur">
                                    Perungalathur
                                </option>

                            </select>

                        </div>

                    </div>


                    <input type="hidden" name="message" value="Quick PG room enquiry from the home page">


                    <button type="submit" class="form-submit">

                        Get a Call Back

                    </button>


                    <p class="form-note">
                        No booking fee. We only use your contact details
                        to respond to this enquiry.
                    </p>

                </form>

            </div>

        </div>

    </section>



    {{-- =========================================================
     TRUST
========================================================= --}}

    <section class="trust" aria-label="Hostel highlights">

        <div class="wrap trust-grid">


            <div class="trust-item">

                <span class="ic">

                    <svg class="i">
                        <use href="#i-pin" />
                    </svg>

                </span>

                <div>

                    <strong>
                        6 Hostels in Chennai
                    </strong>

                    <span>
                        Boys &amp; girls PG across Alandur and Perungalathur
                    </span>

                </div>

            </div>


            <div class="trust-item">

                <span class="ic">

                    <svg class="i">
                        <use href="#i-shield" />
                    </svg>

                </span>

                <div>

                    <strong>
                        CCTV &amp; Hostel Security
                    </strong>

                    <span>
                        Safe accommodation for men and women
                    </span>

                </div>

            </div>


            <div class="trust-item">

                <span class="ic">

                    <svg class="i">
                        <use href="#i-food" />
                    </svg>

                </span>

                <div>

                    <strong>
                        Food Included
                    </strong>

                    <span>
                        Home-style veg &amp; non-veg meals
                    </span>

                </div>

            </div>


            <div class="trust-item">

                <span class="ic">

                    <svg class="i">
                        <use href="#i-train" />
                    </svg>

                </span>

                <div>

                    <strong>
                        Near Metro &amp; Railway
                    </strong>

                    <span>
                        Easy access to Alandur Metro and Tambaram
                    </span>

                </div>

            </div>

        </div>

    </section>



    {{-- =========================================================
     ROOM CATEGORIES
========================================================= --}}

    <section class="panel-ivory" id="rooms" aria-labelledby="categories-title">

        <div class="wrap">

            <div class="section-head">

                <span class="eyebrow">
                    Rooms &amp; Monthly Rent
                </span>

                <h2 id="categories-title">
                    PG Rooms for Students and Working Professionals in Chennai
                </h2>

                <p>
                    Choose between our <strong>Luxury AC PG</strong> and
                    <strong>Normal PG</strong> options based on your room
                    requirements and monthly budget. Rent starts from ₹7,250 per month.
                </p>

            </div>


            <div class="category-grid">


                {{-- LUXURY --}}

                <div class="category-card luxury">

                    <span class="cat-tag">
                        Premium
                    </span>

                    <h3>
                        Luxury AC PG Rooms in Chennai
                    </h3>

                    <p>
                        Premium <strong>AC PG rooms with attached bathroom in
                        Alandur</strong> for working professionals and students.
                        Includes smart TV, geyser &amp; free gym access.
                    </p>


                    <div class="price-block">

                        <div class="price">
                            ₹12,000
                            <span>/ month</span>
                        </div>

                        <div class="price-note">
                            EB bill included up to 200 units.
                            Extra units ₹8 each.
                        </div>

                    </div>


                    <ul class="amenities-list">

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            <span>
                                Premium bed (6×2 ft) with mattress
                            </span>
                        </li>


                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            <span>
                                Attached bathroom with geyser
                            </span>
                        </li>


                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            <span>
                                AC room with 43&quot; smart TV
                            </span>
                        </li>


                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            <span>
                                Free gym access
                            </span>
                        </li>


                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            <span>
                                Veg &amp; non-veg meals
                            </span>
                        </li>


                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            <span>
                                Fibre WiFi (100 Mbps)
                            </span>
                        </li>

                    </ul>


                    <details class="more">

                        <summary>
                            See everything included
                        </summary>

                        <ul class="amenities-list">

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Induction stove
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Washing machine
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Water heater and RO water
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Housekeeping
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Study desk and wardrobe
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Power backup
                            </li>

                        </ul>

                    </details>


                    <div class="card-foot">

                        <a href="{{ route('contact') }}" class="btn btn-primary btn-block">

                            Enquire About Luxury AC PG

                        </a>

                    </div>

                </div>



                {{-- NORMAL --}}

                <div class="category-card normal">

                    <span class="cat-tag">
                        Budget Friendly
                    </span>

                    <h3>
                        Budget PG Rooms with Food in Alandur &amp; Perungalathur
                    </h3>

                    <p>
                        Affordable <strong>monthly PG accommodation</strong>
                        with home-style food, WiFi and AC/non-AC options for
                        students and working professionals.
                    </p>


                    <div class="price-block">

                        <div class="price">
                            ₹7,250
                            <span>/ month</span>
                        </div>

                        <div class="price-note">
                            EB bill extra according to meter reading.
                        </div>

                    </div>


                    <ul class="amenities-list">

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            Comfortable bed with mattress
                        </li>

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            Shared bathroom
                        </li>

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            AC and non-AC options
                        </li>

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            Home-style meals
                        </li>

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            Free gym access
                        </li>

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            WiFi and CCTV
                        </li>

                        <li>
                            <svg class="i check">
                                <use href="#i-check" />
                            </svg>

                            Housekeeping and power backup
                        </li>

                    </ul>


                    <details class="more">

                        <summary>
                            See everything included
                        </summary>

                        <ul class="amenities-list">

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Common kitchen
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Induction stove
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Common washing machine
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                Water heater
                            </li>

                            <li>
                                <svg class="i check">
                                    <use href="#i-check" />
                                </svg>
                                RO purified water
                            </li>

                        </ul>

                    </details>


                    <div class="card-foot">

                        <a href="{{ route('contact') }}" class="btn btn-dark btn-block">

                            Enquire About Budget PG

                        </a>

                    </div>

                </div>

            </div>


            <p class="price-note" style="margin-top:16px;">

                For single, double, triple and dormitory room options,
                visit our
                <a href="{{ route('rooms') }}" style="color:var(--boys);font-weight:700;text-decoration:underline;">
                    PG rooms &amp; monthly rent in Chennai
                </a> page.

            </p>

        </div>

    </section>



    {{-- =========================================================
     SIX HOSTELS
========================================================= --}}

    <section aria-labelledby="locations-title">

        <div class="wrap">

            <div class="section-head">

                <span class="eyebrow">
                    Our Locations
                </span>

                <h2 id="locations-title">
                    6 Boys &amp; Girls PG Hostels in Alandur and Perungalathur, Chennai
                </h2>

                <p>
                    Sanjay &amp; Harini Hostels operates separate men's and
                    women's PG hostels at multiple locations in Alandur and
                    Perungalathur — all within easy reach of Alandur Metro,
                    Guindy, Tambaram and major IT parks.
                </p>

            </div>


            <div class="location-grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">


                {{-- PUDUPETTAI --}}

                <div class="location-card">

                    <div class="who">

                        <span class="pill">
                            Men
                        </span>

                        <span class="pill girls">
                            Women
                        </span>

                    </div>


                    <h3>
                        PG Hostel in Pudupettai Street, Alandur
                    </h3>


                    <p>
                        Three hostels at Pudupettai Street in Alandur — two
                        women's and one men's — <strong>walking distance from
                        Alandur Metro Station</strong>. Ideal for working
                        professionals near Guindy, Ekkatuthangal &amp; St. Thomas Mount.
                    </p>


                    <a class="go" href="{{ route('hostels.alandur') }}">

                        Explore Alandur PG Hostels

                        <svg class="i">
                            <use href="#i-arrow" />
                        </svg>

                    </a>

                </div>



                {{-- MKN --}}

                <div class="location-card">

                    <div class="who">

                        <span class="pill">
                            Men
                        </span>

                    </div>


                    <h3>
                        Men's PG on M.K.N. Road, Alandur
                    </h3>


                    <p>
                        Men's PG accommodation on M.K.N. Road, Alandur, near
                        Lalitha Thanga Maligai. Suitable for
                        <strong>IT professionals, students and daily commuters</strong>
                        working around Guindy &amp; Ekkatuthangal.
                    </p>


                    <a class="go" href="{{ route('hostels.alandur') }}">

                        View Alandur PG

                        <svg class="i">
                            <use href="#i-arrow" />
                        </svg>

                    </a>

                </div>



                {{-- RAJA STREET --}}

                <div class="location-card">

                    <div class="who">

                        <span class="pill girls">
                            Women
                        </span>

                    </div>


                    <h3>
                        Ladies Hostel in Raja Street, Alandur
                    </h3>


                    <p>
                        <strong>Working women's hostel in Alandur</strong> on
                        Raja Street — convenient, safe and comfortable
                        accommodation with CCTV and warden support for women.
                    </p>


                    <a class="go" href="{{ route('hostels.alandur') }}">

                        View Alandur Hostels

                        <svg class="i">
                            <use href="#i-arrow" />
                        </svg>

                    </a>

                </div>



                {{-- PERUNGALATHUR --}}

                <div class="location-card">

                    <div class="who">

                        <span class="pill">
                            Boys
                        </span>

                    </div>


                    <h3>
                        Boys PG Hostel in Perungalathur
                    </h3>


                    <p>
                        <strong>Boys PG near Tambaram</strong> — convenient
                        for students and working professionals travelling
                        around Tambaram, Vandalur and GST Road.
                    </p>


                    <a class="go" href="{{ route('hostels.perungalathur') }}">

                        View Perungalathur Hostel

                        <svg class="i">
                            <use href="#i-arrow" />
                        </svg>

                    </a>

                </div>

            </div>


            <p class="price-note" style="margin-top:16px;">

                View all branches and hostel details on our
                <a href="{{ route('hostels.index') }}"
                    style="color:var(--boys);font-weight:700;text-decoration:underline;">
                    PG hostels in Chennai
                </a> page.

            </p>

        </div>

    </section>



    {{-- =========================================================
     NEARBY AREAS — LOCAL SEO SECTION
========================================================= --}}

    <section class="panel-ivory" aria-labelledby="areas-title">

        <div class="wrap">

            <div class="section-head">

                <span class="eyebrow">
                    Nearby Areas We Serve
                </span>

                <h2 id="areas-title">
                    PG Near Metro, Railway &amp; IT Parks in Chennai
                </h2>

                <p>
                    Our Alandur and Perungalathur PG hostels are convenient
                    for residents working or studying in these nearby areas
                    of Chennai.
                </p>

            </div>


            <div class="why-grid">

                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-train" />
                        </svg>
                    </span>

                    <div>
                        <h3>PG near Alandur Metro</h3>
                        <p>5 min walk from Alandur Metro Station</p>
                    </div>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <div>
                        <h3>Gents PG near Guindy</h3>
                        <p>Ideal for IT professionals in Guindy</p>
                    </div>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-users" />
                        </svg>
                    </span>

                    <div>
                        <h3>Ladies hostel near St. Thomas Mount</h3>
                        <p>Safe women's PG with CCTV &amp; warden</p>
                    </div>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-train" />
                        </svg>
                    </span>

                    <div>
                        <h3>Boys PG near Tambaram</h3>
                        <p>Perungalathur branch for Tambaram commuters</p>
                    </div>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <div>
                        <h3>PG near Ekkatuthangal</h3>
                        <p>Close to Ekkatuthangal IT corridor</p>
                    </div>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <div>
                        <h3>PG near Nanganallur</h3>
                        <p>Quick access to Nanganallur &amp; Palavanthangal</p>
                    </div>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <div>
                        <h3>PG near Vandalur &amp; GST Road</h3>
                        <p>Perungalathur branch near Vandalur Zoo</p>
                    </div>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-train" />
                        </svg>
                    </span>

                    <div>
                        <h3>PG near Chennai Airport</h3>
                        <p>Quick access to Meenambakkam Airport</p>
                    </div>

                </div>

            </div>

        </div>

    </section>



    {{-- =========================================================
     GYM
========================================================= --}}

    <section aria-labelledby="gym-title">

        <div class="wrap">

            <div class="gym-grid">

                <div class="gym-content">

                    <span class="eyebrow">
                        Resident Facility
                    </span>

                    <h2 id="gym-title">
                        Free Gym Access for PG Residents in Chennai
                    </h2>

                    <p>
                        Residents can use the in-house gym facility without
                        leaving the hostel building. Ideal for daily workouts
                        and fitness routines.
                    </p>


                    <div class="gym-features">

                        <div class="gf">
                            <svg class="i">
                                <use href="#i-gym" />
                            </svg>
                            Cardio equipment
                        </div>

                        <div class="gf">
                            <svg class="i">
                                <use href="#i-gym" />
                            </svg>
                            Weight training
                        </div>

                        <div class="gf">
                            <svg class="i">
                                <use href="#i-gym" />
                            </svg>
                            Treadmill
                        </div>

                        <div class="gf">
                            <svg class="i">
                                <use href="#i-gym" />
                            </svg>
                            Exercise area
                        </div>

                        <div class="gf">
                            <svg class="i">
                                <use href="#i-check" />
                            </svg>
                            Free for residents
                        </div>

                    </div>


                    <div class="gym-rate">

                        <span>
                            <b>
                                Free gym access
                            </b>
                            for residents
                        </span>

                    </div>


                    <a href="{{ route('contact') }}" class="btn btn-primary">

                        Ask About Availability

                    </a>

                </div>


                <div class="gym-visual">

                    <img src="{{ asset('images/hostel/gym-1.jpg') }}"
                         alt="Free gym access for PG residents in Alandur Chennai hostel"
                         width="300"
                         height="240"
                         loading="lazy"
                         decoding="async">


                    <img src="{{ asset('images/hostel/gym-2.jpg') }}"
                         alt="Weight training equipment in Alandur PG hostel gym Chennai"
                         width="300"
                         height="240"
                         loading="lazy"
                         decoding="async">


                    <img class="full"
                         src="{{ asset('images/hostel/gym-3.jpg') }}"
                         alt="Cardio and workout area inside boys and girls PG hostel Chennai"
                         width="600"
                         height="200"
                         loading="lazy"
                         decoding="async">

                </div>

            </div>

        </div>

    </section>



    {{-- =========================================================
     ABOUT
========================================================= --}}

    <section id="about" aria-labelledby="about-title">

        <div class="wrap">

            <div class="about-wrap">

                <div class="about-copy">

                    <span class="eyebrow">
                        About Sanjay &amp; Harini
                    </span>


                    <h2 id="about-title">
                        Affordable Boys &amp; Girls PG Accommodation in Chennai
                    </h2>


                    <p>
                        Sanjay &amp; Harini Hostels operates six hostels
                        across Alandur and Perungalathur, Chennai. Our
                        locations include <strong>separate accommodation for
                        men and women</strong>.
                    </p>


                    <p>
                        We provide different room options along with
                        facilities such as <strong>food, WiFi, CCTV,
                        housekeeping and gym access</strong>, depending on the
                        hostel and room plan.
                    </p>


                    <div class="about-stats">

                        <div class="stat">
                            <div class="num">
                                6
                            </div>

                            <div class="label">
                                Hostels
                            </div>
                        </div>


                        <div class="stat">
                            <div class="num">
                                2
                            </div>

                            <div class="label">
                                Main Areas
                            </div>
                        </div>


                        <div class="stat">
                            <div class="num">
                                Gym
                            </div>

                            <div class="label">
                                Resident Facility
                            </div>
                        </div>


                        <div class="stat">
                            <div class="num">
                                WiFi
                            </div>

                            <div class="label">
                                Available
                            </div>
                        </div>

                    </div>


                    <a href="{{ route('about') }}">
                        Learn More About Us
                    </a>

                </div>


                <div class="about-visual">

                    <img class="tall"
                         src="{{ asset('images/hostel/room.jpg') }}"
                         alt="AC PG room for working professionals in Alandur Chennai"
                         width="400"
                         height="340"
                         loading="lazy"
                         decoding="async">


                    <div class="col">

                        <img src="{{ asset('images/hostel/common-area.jpg') }}"
                             alt="Common dining area in boys PG hostel Alandur Chennai"
                             width="300"
                             height="240"
                             loading="lazy"
                             decoding="async">


                        <img src="{{ asset('images/hostel/dining.jpg') }}"
                             alt="Home-style food served at Sanjay Harini girls hostel Chennai"
                             width="300"
                             height="240"
                             loading="lazy"
                             decoding="async">

                    </div>

                </div>

            </div>

        </div>

    </section>



    {{-- =========================================================
     WHY CHOOSE US
========================================================= --}}

    <section class="panel-ivory" aria-labelledby="why-title">

        <div class="wrap">

            <div class="section-head">

                <span class="eyebrow">
                    Hostel Facilities
                </span>

                <h2 id="why-title">
                    Why Choose Our PG Hostels in Chennai
                </h2>

                <p>
                    Facilities vary by branch and room category.
                    Contact us to confirm current availability.
                </p>

            </div>


            <div class="why-grid">


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-bed" />
                        </svg>

                    </span>

                    <p>
                        Luxury &amp; Normal PG options
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-gym" />
                        </svg>

                    </span>

                    <p>
                        Free gym access
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-train" />
                        </svg>

                    </span>

                    <p>
                        Walking distance to Alandur Metro
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-snow" />
                        </svg>

                    </span>

                    <p>
                        AC and non-AC rooms
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-wifi" />
                        </svg>

                    </span>

                    <p>
                        High-speed WiFi
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-shield" />
                        </svg>

                    </span>

                    <p>
                        CCTV security
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-food" />
                        </svg>

                    </span>

                    <p>
                        Home-style food included
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-users" />
                        </svg>

                    </span>

                    <p>
                        Separate hostels for women
                    </p>

                </div>

            </div>

        </div>

    </section>



    {{-- =========================================================
     FAQ
========================================================= --}}

    <section aria-labelledby="faq-title">

        <div class="wrap">

            <div class="section-head">

                <span class="eyebrow">
                    FAQs
                </span>

                <h2 id="faq-title">
                    Frequently Asked Questions About Our PG Hostels in Chennai
                </h2>

            </div>


            <div class="faq-list">


                @foreach ([
            ['Is there a PG near Alandur Metro Station?', 'Yes. Our Pudupettai Street and M.K.N. Road branches are within walking distance from Alandur Metro Station, ideal for daily commuters working in Guindy, Ekkatuthangal and St. Thomas Mount.'],

            ['Do you have a working women\'s hostel in Chennai?', 'Yes. We have two dedicated women\'s hostels at Pudupettai Street and one ladies\' hostel at Raja Street, Alandur with CCTV, warden support and home-style food.'],

            ['What is the monthly PG rent in Alandur?', 'Normal PG starts at ₹7,250 per month with EB charges extra as per meter. Luxury AC PG with attached bathroom starts at ₹12,000 per month with EB included up to 200 units.'],

            ['Do you provide PG for IT professionals near Guindy?', 'Yes. Our Alandur branches are about 10 minutes from Guindy and Ekkatuthangal, making them popular with IT professionals and working women.'],

            ['Is food included in the PG rent?', 'Yes. Home-style vegetarian and non-vegetarian meals are included in the monthly rent at our boys and girls PG hostels in Chennai.'],

            ['Do you have AC PG rooms in Chennai?', 'Yes. Both our Luxury PG and Normal PG categories offer AC and non-AC room options depending on the branch and availability.'],

            ['Which is the best PG for students near Alandur?', 'Our Pudupettai Street and Raja Street hostels are popular with students due to their proximity to Alandur Metro, colleges, and food options.'],

            ['How many hostels do Sanjay & Harini operate in Chennai?', 'Sanjay & Harini Hostels operates six hostels — five in Alandur (Pudupettai Street, M.K.N. Road, and Raja Street) and one boys hostel in Perungalathur.'],

            ['Do you have a boys hostel in Perungalathur near Tambaram?', 'Yes. We operate a boys hostel in Perungalathur, convenient for students and working professionals travelling around Tambaram, Vandalur and GST Road.'],

            ['How can I check PG room availability?', 'You can call us, send a WhatsApp message, or submit the enquiry form on this website. We will share current room availability and pricing for your preferred location.'],
        ] as $faq)
                    <div class="faq-item">

                        <button class="faq-q" type="button" aria-expanded="false">

                            {{ $faq[0] }}

                            <span class="plus" aria-hidden="true">
                                +
                            </span>

                        </button>


                        <div class="faq-a">
                            {{ $faq[1] }}
                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>



    {{-- =========================================================
     TESTIMONIALS
========================================================= --}}

    @if (config('hostel.show_testimonials', false))
        <section class="panel-ivory" aria-labelledby="testimonials-title">

            <div class="wrap">

                <div class="section-head">

                    <span class="eyebrow">
                        Resident Reviews
                    </span>

                    <h2 id="testimonials-title">
                        What Our Residents Say
                    </h2>

                </div>


                <div class="testi-grid">

                    {{-- Add ONLY genuine resident reviews here. --}}

                </div>

            </div>

        </section>
    @endif



    {{-- =========================================================
     FINAL CTA
========================================================= --}}

    <section class="final-cta" aria-labelledby="cta-title">

        <div class="wrap">

            <div>

                <div class="strap">
                    Check availability
                </div>

                <h2 id="cta-title">
                    Looking for a PG in Alandur or Perungalathur, Chennai?
                </h2>

                <p class="lead">
                    Tell us your preferred location, room type and
                    accommodation requirement. We can help you check
                    available PG rooms and current monthly pricing in Chennai.
                </p>

            </div>


            <div class="hero-actions">


                <a href="tel:{{ config('hostel.phone') }}" class="btn btn-primary">

                    <svg class="i">
                        <use href="#i-phone" />
                    </svg>

                    Call {{ config('hostel.phone_display') }}

                </a>


                <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability%20in%20Alandur%20%2F%20Perungalathur"
                   class="btn btn-wa"
                   target="_blank"
                   rel="noopener noreferrer">

                    <svg class="i">
                        <use href="#i-chat" />
                    </svg>

                    WhatsApp

                </a>


                <a href="{{ route('contact') }}" class="btn btn-ghost">

                    Send an Enquiry

                </a>

            </div>

        </div>

    </section>


@endsection
