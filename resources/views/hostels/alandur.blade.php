@extends('layouts.frontend')

@php
    $path  = '/hostels/alandur';
    $title = 'Hostel & PG in Alandur, Chennai | Sanjay & Harini Hostels';
    $desc  = "Men's and women's PG hostels in Alandur, Chennai, on Pudupettai Street, M.K.N. Road and Raja Street. Metro, Guindy and St. Thomas Mount are close by. Enquire today.";
    $crumbs = [['Home', '/'], ['Our hostels', '/hostels'], ['Alandur', $path]];

    $men   = array_filter($properties, fn ($p) => $p['gender'] === 'men');
    $women = array_filter($properties, fn ($p) => $p['gender'] === 'women');
    $mapProp = collect($properties)->first(fn ($p) => \App\Support\Hostels::mapsEmbed($p));

    // Distances and travel times are intentionally not stated: we only list places that
    // genuinely sit around Alandur. Add distances once measured from each hostel.
    $places = [
        'Metro and railway' => [
            ['Alandur Metro', 'The interchange between the Blue and Green lines, so the airport, Guindy and Chennai Central are each one ride away.'],
            ['St. Thomas Mount', 'A metro station (Green Line) and a suburban railway station, handy for trains towards Tambaram and Chennai Beach.'],
            ['Guindy', 'A Blue Line metro station and a stop on the suburban line, beside the industrial estate and tech parks.'],
            ['Ekkattuthangal', 'The next Green Line station from Alandur towards Ashok Nagar and Vadapalani.'],
            ['Nanganallur Road', 'The Blue Line station between Alandur and the airport.'],
            ['Chennai Airport', 'Served by the Blue Line from Alandur. Useful for airport and airline staff.'],
        ],
        'Work and study' => [
            ['Guindy Industrial Estate (SIDCO)', 'One of Chennai\'s long-established industrial areas.'],
            ['Olympia Tech Park', 'An IT park in Guindy, popular with software employees.'],
            ['Anna University', 'The Guindy campus, home to the College of Engineering, Guindy.'],
            ['IIT Madras', 'The campus is off Sardar Patel Road. You reach it through Guindy rather than directly from Alandur.'],
        ],
        'Neighbourhoods and roads' => [
            ['Nanganallur', 'A residential neighbourhood just south of Alandur with shops and eateries.'],
            ['Kathipara Junction', 'The big flyover junction where GST Road meets the Inner Ring Road.'],
            ['Little Mount', 'In Saidapet, further up the Blue Line past Guindy.'],
        ],
    ];

    $faqs = [
        ["Do you have a men's hostel in Alandur?", "Yes. We have two men's hostels in Alandur: one on Pudupettai Street and one on M.K.N. Road."],
        ["Do you have a women's or ladies' hostel in Alandur?", "Yes. We have three: two women's hostels on Pudupettai Street and a ladies' hostel on Raja Street. Women's hostels are only for women."],
        ["Where in Alandur are your hostels?", "On three streets: Pudupettai Street, M.K.N. Road and Raja Street. Call or WhatsApp us for the exact location and directions to the hostel you are interested in."],
        ["Is the hostel near Alandur Metro?", "All five hostels are in Alandur, where the metro interchange is. The walking route differs from hostel to hostel, so ask us for the route from the one you like."],
        ["Is it a good base for working in Guindy or near the airport?", "Alandur has metro links in both directions: the Blue Line to Guindy and the airport, and the Green Line towards St. Thomas Mount and Chennai Central. Guindy is also on the suburban rail line."],
        ["Is food available?", "Yes. Our plans include home-style meals, with vegetarian and non-vegetarian options. The exact meal plan can vary by hostel, so confirm it when you enquire."],
        ["Are the rooms furnished, and is WiFi available?", "Rooms come with a cot and mattress, a study table and chair, a wardrobe, fans and lights. High-speed WiFi is provided. AC and non-AC rooms are both available."],
        ["Is CCTV and security available?", "Yes. Our hostels have CCTV coverage, controlled entry and an on-site warden."],
        ["Is the hostel suitable for students and working professionals?", "Yes. Our residents include IT employees, working professionals, college students and airport staff."],
        ["How can I book a room or visit first?", "Use the enquiry form on our contact page, call, or message us on WhatsApp. You are welcome to see the hostel before you decide."],
    ];

    $schemaNodes = [
        \App\Support\Seo::webPage($path, $title, $desc),
        \App\Support\Seo::faq($faqs),
    ];
    foreach ($properties as $p) {
        $schemaNodes[] = \App\Support\Seo::hostel($p); // only if the address is confirmed
    }
@endphp

@section('title', $title)
@section('meta_description', $desc)
@section('canonical', \App\Support\Seo::url($path))
@section('schema')
    {!! \App\Support\Seo::json($schemaNodes) !!}
@endsection

@section('content')
    <div class="page-hero">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => $crumbs])
            <span class="eyebrow">Hostels in Alandur</span>
            <h1>Men's and women's hostels in Alandur, Chennai</h1>
            <p>Five PG hostels on Pudupettai Street, M.K.N. Road and Raja Street, with separate hostels for men and for women.</p>
        </div>
    </div>

    <section>
        <div class="wrap prose">
            <h2>PG in Alandur for men and women</h2>
            <p>We run five hostels in Alandur, on three streets. <strong>Pudupettai Street</strong> has one men's hostel and two women's hostels. <strong>M.K.N. Road</strong> has a men's hostel, and <strong>Raja Street</strong> has a ladies' hostel. Each is its own building, so men and women never share a hostel.</p>
            <p>Alandur works well if you travel by metro. It is where the Blue and Green lines cross, which means Guindy, the airport and Chennai Central are all reachable without changing to a bus. People working in the Guindy industrial estate and tech parks, at the airport, or in central Chennai often start their search here.</p>
            <p>Rent and room types depend on the hostel, whether you choose AC or non-AC, and how many people share. You can see the typical rates on the <a href="{{ route('rooms') }}">rooms and pricing page</a>, and we will confirm the exact figure for the hostel you pick.</p>
        </div>
    </section>

    <section class="panel-ivory" id="alandur-hostels" aria-labelledby="props-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Our Alandur hostels</span>
                <h2 id="props-title">Five hostels, three streets</h2>
                <p>Men's hostels: {{ count($men) }}. Women's hostels: {{ count($women) }}.</p>
            </div>
            <div class="prop-grid">
                @foreach ($properties as $p)
                    @include('partials.property-card', ['p' => $p])
                @endforeach
            </div>
            @if (config('hostel.photos_are_placeholders'))
                <p class="img-caption">Property photos are being added hostel by hostel.</p>
            @endif
        </div>
    </section>

    <section aria-labelledby="fac-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Facilities</span>
                <h2 id="fac-title">What you can expect in our Alandur hostels</h2>
                <p>Facilities such as the gym, lift, parking and attached bathrooms differ from building to building, so please confirm them for the hostel you like.</p>
            </div>
            <div class="why-grid">
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-bed"/></svg></span><p>Furnished rooms with study table and wardrobe</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-snow"/></svg></span><p>AC and non-AC rooms</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-food"/></svg></span><p>Home-style meals, veg and non-veg</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-wifi"/></svg></span><p>High-speed WiFi</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-shield"/></svg></span><p>CCTV, controlled entry and warden</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-check"/></svg></span><p>RO drinking water and power backup</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-check"/></svg></span><p>Daily housekeeping and laundry</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-users"/></svg></span><p>Separate hostels for men and women</p></div>
            </div>
        </div>
    </section>

    <section class="panel-ivory" id="nearby" aria-labelledby="nearby-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Around Alandur</span>
                <h2 id="nearby-title">Places near our Alandur hostels</h2>
                <p>A guide to the metro stations, workplaces and neighbourhoods around Alandur.</p>
            </div>
            <div class="place-grid">
                @foreach ($places as $heading => $items)
                    <div class="place-group">
                        <h3>{{ $heading }}</h3>
                        <ul>
                            @foreach ($items as [$name, $note])
                                <li><strong>{{ $name }}</strong>{{ $note }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <p class="note-box">We have not listed walking or driving times because they change with each hostel. Ask us for the route from the hostel you are considering and we will tell you what to expect.</p>
        </div>
    </section>

    @if ($mapProp)
        <section aria-labelledby="map-title">
            <div class="wrap">
                <div class="section-head">
                    <span class="eyebrow">Location</span>
                    <h2 id="map-title">Find our M.K.N. Road hostel on the map</h2>
                </div>
                <div class="map-box">
                    <iframe src="{{ \App\Support\Hostels::mapsEmbed($mapProp) }}" title="Map showing {{ $mapProp['label'] }}, Alandur" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="600" height="360"></iframe>
                    <p class="map-cap">{{ $mapProp['label'] }}: {{ \App\Support\Hostels::addressLine($mapProp) }}</p>
                </div>
            </div>
        </section>
    @endif

    <section class="panel-ivory" aria-labelledby="faq-title">
        <div class="wrap">
            <div class="section-head"><span class="eyebrow">FAQs</span><h2 id="faq-title">Questions about our Alandur hostels</h2></div>
            @include('partials.faq', ['faqs' => $faqs])
        </div>
    </section>

    <section class="final-cta" aria-labelledby="cta-title">
        <div class="wrap">
            <div>
                <h2 id="cta-title">See an Alandur hostel this week</h2>
                <p class="lead">Tell us whether you need a men's or women's hostel, your room preference and your move-in date, and we will point you to the right one. Also see our <a class="text-link" style="color:#fff" href="{{ route('hostels.perungalathur') }}">hostel in Perungalathur</a>.</p>
            </div>
            <div class="hero-actions">
                <a href="tel:{{ config('hostel.phone') }}" class="btn btn-primary"><svg class="i"><use href="#i-phone"/></svg> Call {{ config('hostel.phone_display') }}</a>
                <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20availability%20in%20Alandur" class="btn btn-wa" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> WhatsApp us</a>
                <a href="{{ route('contact') }}" class="btn btn-ghost">Send an enquiry</a>
            </div>
        </div>
    </section>
@endsection
