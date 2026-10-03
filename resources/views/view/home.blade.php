@extends('layouts.frontend')

@section('title', 'Sanjay & Harini Hostels | Boys & Girls PG in Alandur & Perungalathur, Chennai')

@section('meta_description', 'Sanjay & Harini Hostels offers boys and girls PG accommodation in Alandur and
    Perungalathur, Chennai. AC and non-AC rooms, food, WiFi, CCTV, gym and convenient access to metro and railway
    stations.')

@section('canonical', \App\Support\Seo::url('/'))

@section('og_title', 'Sanjay & Harini Hostels | Boys & Girls PG in Alandur & Perungalathur')

@section('og_description', 'Six boys and girls hostels across Alandur and Perungalathur, Chennai with comfortable rooms,
    food, WiFi, CCTV and gym facilities.')

@section('og_url', \App\Support\Seo::url('/'))

@section('og_type', 'website')


@section('schema')

    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@graph": [

        {
            "@@type": "Organization",
            "@@id": "{{ url('/') }}#organization",
            "name": "Sanjay & Harini Hostels",
            "url": "{{ url('/') }}",
            "telephone": "{{ config('hostel.phone') }}",
            "email": "{{ config('hostel.email') }}",
            "areaServed": {
                "@@type": "City",
                "name": "Chennai"
            }
        },

        {
            "@@type": "WebSite",
            "@@id": "{{ url('/') }}#website",
            "url": "{{ url('/') }}",
            "name": "Sanjay & Harini Hostels",
            "publisher": {
                "@@id": "{{ url('/') }}#organization"
            },
            "inLanguage": "en-IN"
        },

        {
            "@@type": "WebPage",
            "@@id": "{{ url('/') }}#webpage",
            "url": "{{ url('/') }}",
            "name": "Sanjay & Harini Hostels | Boys & Girls PG in Alandur & Perungalathur, Chennai",
            "description": "Boys and girls PG hostels in Alandur and Perungalathur, Chennai.",
            "isPartOf": {
                "@@id": "{{ url('/') }}#website"
            },
            "about": {
                "@@id": "{{ url('/') }}#organization"
            },
            "inLanguage": "en-IN"
        },

        {
            "@@type": "ItemList",
            "@@id": "{{ url('/') }}#hostels",
            "name": "Sanjay & Harini Hostel Locations",
            "itemListElement": [

                {
                    "@@type": "ListItem",
                    "position": 1,
                    "name": "Pudupettai Street Alandur - Men's Hostel",
                    "url": "{{ url('/hostels/alandur') }}"
                },

                {
                    "@@type": "ListItem",
                    "position": 2,
                    "name": "Pudupettai Street Alandur - Women's Hostel 1",
                    "url": "{{ url('/hostels/alandur') }}"
                },

                {
                    "@@type": "ListItem",
                    "position": 3,
                    "name": "Pudupettai Street Alandur - Women's Hostel 2",
                    "url": "{{ url('/hostels/alandur') }}"
                },

                {
                    "@@type": "ListItem",
                    "position": 4,
                    "name": "M.K.N. Road Alandur - Men's Hostel",
                    "url": "{{ url('/hostels/alandur') }}"
                },

                {
                    "@@type": "ListItem",
                    "position": 5,
                    "name": "Raja Street Alandur - Ladies' Hostel",
                    "url": "{{ url('/hostels/alandur') }}"
                },

                {
                    "@@type": "ListItem",
                    "position": 6,
                    "name": "Perungalathur - Boys' Hostel",
                    "url": "{{ url('/hostels/perungalathur') }}"
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

        <img class="hero-photo" src="{{ asset('images/hostel/hero.jpg') }}"
            alt="Sanjay and Harini PG hostels in Alandur and Perungalathur Chennai" width="1200" height="675"
            fetchpriority="high" decoding="async">


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
                    Sanjay &amp; Harini Hostels operates six hostels across
                    Alandur and Perungalathur, Chennai, with separate
                    accommodation for men and women. Choose from comfortable
                    PG rooms with food, WiFi, CCTV, gym facilities and
                    convenient access to local transport.
                </p>


                <ul class="hero-points">

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        AC and non-AC room options
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        Home-style meals
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
                        CCTV and hostel security
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        Gym access for residents
                    </li>

                    <li>
                        <svg class="i">
                            <use href="#i-check" />
                        </svg>
                        Convenient locations in Chennai
                    </li>

                </ul>


                <div class="hero-actions">

                    <a href="tel:{{ config('hostel.phone') }}" class="btn btn-primary">

                        <svg class="i">
                            <use href="#i-phone" />
                        </svg>

                        Call {{ config('hostel.phone_display') }}

                    </a>


                    <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability"
                        class="btn btn-ghost" target="_blank" rel="noopener noreferrer">

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
                    Tell us what you are looking for and we will contact you
                    with current room availability and pricing.
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
                        6 Hostels
                    </strong>

                    <span>
                        Men and women hostels across Alandur and Perungalathur
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
                        Hostel Security
                    </strong>

                    <span>
                        CCTV and resident security arrangements
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
                        Food Available
                    </strong>

                    <span>
                        Home-style vegetarian and non-vegetarian meals
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
                        Connected Locations
                    </strong>

                    <span>
                        Convenient access to metro, railway and major roads
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
                    Rooms &amp; Pricing
                </span>

                <h2 id="categories-title">
                    PG Rooms for Students and Working Professionals
                </h2>

                <p>
                    Choose between Luxury PG and Normal PG options based on
                    your room requirements and budget.
                </p>

            </div>


            <div class="category-grid">


                {{-- LUXURY --}}

                <div class="category-card luxury">

                    <span class="cat-tag">
                        Premium
                    </span>

                    <h3>
                        Luxury PG
                    </h3>

                    <p>
                        A premium PG option with additional room facilities
                        and attached bathroom.
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
                                Premium bed (6×2 ft)
                                with premium mattress
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
                                Veg and non-veg meals
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

                            Enquire About Luxury PG

                        </a>

                    </div>

                </div>



                {{-- NORMAL --}}

                <div class="category-card normal">

                    <span class="cat-tag">
                        Budget Friendly
                    </span>

                    <h3>
                        Normal PG
                    </h3>

                    <p>
                        Comfortable and affordable accommodation for
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

                            Enquire About Normal PG

                        </a>

                    </div>

                </div>

            </div>


            <p class="price-note" style="margin-top:16px;">

                For single, double, triple and dormitory room options,
                visit our
                <a href="{{ route('rooms') }}" style="color:var(--boys);font-weight:700;text-decoration:underline;">
                    rooms and pricing page
                </a>.

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
                    6 Hostels in Alandur and Perungalathur
                </h2>

                <p>
                    Sanjay &amp; Harini Hostels operates separate men's and
                    women's hostels at multiple locations in Alandur and
                    Perungalathur, Chennai.
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
                        Pudupettai Street, Alandur
                    </h3>


                    <p>
                        Three hostels at Pudupettai Street in Alandur:
                        two women's hostels and one men's hostel.
                        This location is suitable for students and working
                        professionals looking for PG accommodation in Alandur.
                    </p>


                    <a class="go" href="{{ route('hostels.alandur') }}">

                        Explore Alandur Hostels

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
                        M.K.N. Road, Alandur
                    </h3>


                    <p>
                        Men's PG accommodation on M.K.N. Road, Alandur,
                        near the Lalitha Thanga Maligai area.
                        The branch is suitable for working professionals,
                        students and commuters.
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
                        Raja Street, Alandur
                    </h3>


                    <p>
                        Ladies' hostel accommodation in Raja Street,
                        Alandur for women looking for a convenient and
                        comfortable place to stay in Chennai.
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
                        Boys Hostel in Perungalathur
                    </h3>


                    <p>
                        Boys PG accommodation in Perungalathur,
                        convenient for students and working professionals
                        travelling around Tambaram, Vandalur and GST Road.
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
                    hostels page
                </a>.

            </p>

        </div>

    </section>



    {{-- =========================================================
     NEARBY AREAS
========================================================= --}}

    <section class="panel-ivory" aria-labelledby="areas-title">

        <div class="wrap">

            <div class="section-head">

                <span class="eyebrow">
                    Chennai Locations
                </span>

                <h2 id="areas-title">
                    Convenient for Work, College and Daily Travel
                </h2>

                <p>
                    Our Alandur and Perungalathur locations can be useful
                    for residents travelling to nearby business areas,
                    colleges, railway stations, metro stations and major
                    roads in Chennai.
                </p>

            </div>


            <div class="why-grid">

                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <p>
                        Alandur
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-train" />
                        </svg>
                    </span>

                    <p>
                        Alandur Metro
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-train" />
                        </svg>
                    </span>

                    <p>
                        Guindy
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <p>
                        St. Thomas Mount area
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <p>
                        Ekkatuthangal
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <p>
                        Nanganallur
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-train" />
                        </svg>
                    </span>

                    <p>
                        Tambaram
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">
                        <svg class="i">
                            <use href="#i-pin" />
                        </svg>
                    </span>

                    <p>
                        Vandalur &amp; GST Road
                    </p>

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
                        Free Gym Access for Residents
                    </h2>

                    <p>
                        Residents can use the in-house gym facility without
                        leaving the hostel building. The facility is designed
                        for everyday workouts and fitness routines.
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
                            Resident access
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

                    <img src="{{ asset('images/hostel/gym-1.jpg') }}" alt="Gym facility at Sanjay and Harini Hostels"
                        width="300" height="240" loading="lazy" decoding="async">


                    <img src="{{ asset('images/hostel/gym-2.jpg') }}" alt="Fitness equipment for hostel residents"
                        width="300" height="240" loading="lazy" decoding="async">


                    <img class="full" src="{{ asset('images/hostel/gym-3.jpg') }}" alt="Hostel gym and workout area"
                        width="600" height="200" loading="lazy" decoding="async">

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
                        Boys and Girls PG Accommodation in Chennai
                    </h2>


                    <p>
                        Sanjay &amp; Harini Hostels operates six hostels
                        across Alandur and Perungalathur, Chennai.
                        Our locations include separate accommodation for
                        men and women.
                    </p>


                    <p>
                        We provide different room options along with
                        facilities such as food, WiFi, CCTV, housekeeping
                        and gym access, depending on the hostel and room plan.
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

                    <img class="tall" src="{{ asset('images/hostel/room.jpg') }}"
                        alt="PG hostel room at Sanjay and Harini Hostels Chennai" width="400" height="340"
                        loading="lazy" decoding="async">


                    <div class="col">

                        <img src="{{ asset('images/hostel/common-area.jpg') }}"
                            alt="Common area at Sanjay and Harini Hostel" width="300" height="240" loading="lazy"
                            decoding="async">


                        <img src="{{ asset('images/hostel/dining.jpg') }}" alt="Dining area at Sanjay and Harini Hostel"
                            width="300" height="240" loading="lazy" decoding="async">

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
                    Comfortable PG Living in Chennai
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
                        Luxury and Normal PG options
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-gym" />
                        </svg>

                    </span>

                    <p>
                        Gym access
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-train" />
                        </svg>

                    </span>

                    <p>
                        Convenient transport access
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-snow" />
                        </svg>

                    </span>

                    <p>
                        AC and non-AC options
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
                        Home-style food
                    </p>

                </div>


                <div class="why-item">

                    <span class="tick-circ">

                        <svg class="i">
                            <use href="#i-users" />
                        </svg>

                    </span>

                    <p>
                        Separate accommodation for women
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
                    Frequently Asked Questions About Our PG Hostels
                </h2>

            </div>


            <div class="faq-list">


                @foreach ([
            ['Where are Sanjay & Harini Hostels located?', 'Sanjay & Harini Hostels has six hostels across Alandur and Perungalathur in Chennai. The Alandur locations include Pudupettai Street, M.K.N. Road and Raja Street. There is also a boys hostel in Perungalathur.'],

            ['How many hostels do you have in Alandur?', 'There are five hostels in Alandur: two women\'s hostels and one men\'s hostel on Pudupettai Street, one men\'s hostel on M.K.N. Road and one ladies\' hostel on Raja Street.'],

            ['Do you provide boys PG accommodation in Alandur?', 'Yes. Sanjay & Harini Hostels has men\'s PG accommodation in Alandur, including a men\'s hostel at Pudupettai Street and another men\'s hostel at M.K.N. Road.'],

            ['Do you provide girls hostel accommodation in Alandur?', 'Yes. There are two women\'s hostels at Pudupettai Street and one ladies\' hostel at Raja Street in Alandur.'],

            ['Do you have a boys hostel in Perungalathur?', 'Yes. Sanjay & Harini Hostels operates a boys hostel in Perungalathur.'],

            ['What room options are available?', 'Room options include Luxury PG and Normal PG categories. Depending on the branch, room types may include single, double, triple or shared accommodation. Contact us for current availability.'],

            ['Do you provide food?', 'Yes. Home-style vegetarian and non-vegetarian meal options are available according to the hostel and accommodation plan.'],

            ['Is WiFi available?', 'WiFi is available as part of the hostel facilities. Please confirm the current plan and availability for the branch you are interested in.'],

            ['Is gym access available?', 'Gym access is available for residents. Please confirm the current gym timings and facilities when you enquire.'],

            ['How can I check room availability?', 'You can call us, send a WhatsApp message or submit the enquiry form on this website. We can then provide the available room options and current pricing.'],
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

    {{--
    Do NOT publish made-up reviews.
    Keep this section disabled until you have genuine resident reviews.
--}}

    @if (config('hostel.show_testimonials'))
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

                    {{-- Example structure:

            <div class="testi-card">

                <div class="stars">
                    ★★★★★
                </div>

                <p class="quote">
                    "Actual resident review goes here."
                </p>

                <div class="who">
                    Resident name, branch
                </div>

            </div>

            --}}

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
                    Looking for a PG in Alandur or Perungalathur?
                </h2>

                <p class="lead">
                    Tell us your preferred location, room type and
                    accommodation requirement. We can help you check
                    the available hostel options and current pricing.
                </p>

            </div>


            <div class="hero-actions">


                <a href="tel:{{ config('hostel.phone') }}" class="btn btn-primary">

                    <svg class="i">
                        <use href="#i-phone" />
                    </svg>

                    Call {{ config('hostel.phone_display') }}

                </a>


                <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20room%20availability"
                    class="btn btn-wa" target="_blank" rel="noopener noreferrer">

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
