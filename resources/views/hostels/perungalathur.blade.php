@extends('layouts.frontend')

@php
    $path  = '/hostels/perungalathur';
    $title = "Men's Hostel & PG in Perungalathur | Sanjay & Harini Hostels";
    $desc  = "Men's PG hostel in Perungalathur, Chennai, for students and working professionals. The area sits between Tambaram and Vandalur on GST Road. Call or WhatsApp to book.";
    $crumbs = [['Home', '/'], ['Our hostels', '/hostels'], ['Perungalathur', $path]];
    $mapProp = collect($properties)->first(fn ($p) => \App\Support\Hostels::mapsEmbed($p));

    // No distances or minutes are claimed. Add them only after measuring from the hostel's confirmed address.
    $places = [
        'Railway and roads' => [
            ['Perungalathur Railway Station', 'A stop on the suburban line between Tambaram and Vandalur. The same line runs north through St. Thomas Mount and Guindy towards Chennai Beach.'],
            ['Tambaram Railway Station', 'A major station one stop towards Chennai, with many suburban services and several long-distance halts.'],
            ['Vandalur Railway Station', 'The next stop south of Perungalathur.'],
            ['Urapakkam', 'A residential area further down the same line and GST Road.'],
            ['GST Road', 'The main road corridor connecting Tambaram, Perungalathur and Vandalur to central Chennai and the airport side.'],
            ['Kilambakkam Bus Terminus', 'The long-distance bus terminus on GST Road beyond Vandalur.'],
        ],
        'Study and work' => [
            ['Tambaram', 'A large town with colleges, schools, offices, shops and hospitals.'],
            ['Madras Christian College', 'A well-known college campus in East Tambaram.'],
            ['Guindy and St. Thomas Mount', 'Reachable by suburban train, which helps residents who work in the Guindy industrial estate or tech parks.'],
        ],
        'Neighbourhoods and leisure' => [
            ['New Perungalathur', 'The residential side of Perungalathur, near the railway line.'],
            ['Vandalur', 'Known for the Arignar Anna Zoological Park, usually called Vandalur Zoo.'],
        ],
    ];

    $faqs = [
        ["Is there a boys or men's hostel in Perungalathur?", "Yes. Our Perungalathur hostel is a men's hostel, open to students, working professionals, interns and job seekers."],
        ["Is there a girls' or women's hostel in Perungalathur?", "No. Our Perungalathur hostel is for men only. Our women's hostels are in Alandur, which you can see on the Alandur hostels page."],
        ["Where is the Perungalathur hostel?", "In Perungalathur, Chennai, between Tambaram and Vandalur. Call or WhatsApp us and we will share the location and directions."],
        ["Is it close to Perungalathur Railway Station?", "The hostel is in Perungalathur, which is a stop on the suburban line. Ask us for the walking route from the hostel to the station."],
        ["Is it a good option if I work in Tambaram, Vandalur or Guindy?", "Perungalathur is on the same suburban line as Tambaram and Vandalur, and the line continues to St. Thomas Mount and Guindy. Many people choose this side of Chennai to travel by train."],
        ["Is food available?", "Yes. Our plans include home-style meals with vegetarian and non-vegetarian options. Confirm the meal plan for this hostel when you enquire."],
        ["Is WiFi available, and are the rooms furnished?", "Yes to both. Rooms come with a cot and mattress, a study table and chair, a wardrobe, fans and lights, and high-speed WiFi is provided."],
        ["Is the hostel safe?", "Our hostels have CCTV coverage, controlled entry and an on-site warden."],
        ["How can I contact the hostel or book a visit?", "Call or WhatsApp us, or send the enquiry form on our contact page and choose Perungalathur as the branch."],
    ];

    $schemaNodes = [
        \App\Support\Seo::webPage($path, $title, $desc),
        \App\Support\Seo::faq($faqs),
    ];
    foreach ($properties as $p) {
        $schemaNodes[] = \App\Support\Seo::hostel($p); // null until the address is confirmed
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
            <span class="eyebrow">Hostel in Perungalathur</span>
            <h1>Men's hostel in Perungalathur, Chennai</h1>
            <p>A PG hostel for men on the Tambaram to Vandalur stretch of GST Road, with the suburban railway line running through the area.</p>
        </div>
    </div>

    <section>
        <div class="wrap prose">
            <h2>PG in Perungalathur for students and working men</h2>
            <p>Our Perungalathur hostel is for <strong>men</strong>. It suits college students, working professionals, interns and people looking for a first place while they search for a job in Chennai.</p>
            <p>Perungalathur sits between Tambaram and Vandalur, so the railway station and GST Road are both part of daily life here. If you study or work in Tambaram, Vandalur or along GST Road, you can often get to work without a long commute. The suburban line also continues north through St. Thomas Mount and Guindy, which helps if your office is on that side.</p>
            <p>Room types, rent and availability for this hostel are confirmed when you call us. You can see typical rates on the <a href="{{ route('rooms') }}">rooms and pricing page</a>. If you need a women's hostel, see our <a href="{{ route('hostels.alandur') }}">hostels in Alandur</a>.</p>
        </div>
    </section>

    <section class="panel-ivory" aria-labelledby="props-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Our Perungalathur hostel</span>
                <h2 id="props-title">The Perungalathur hostel</h2>
            </div>
            <div class="prop-grid">
                @foreach ($properties as $p)
                    @include('partials.property-card', ['p' => $p])
                @endforeach
            </div>
            @if (config('hostel.photos_are_placeholders'))
                <p class="img-caption">Property photos are being added.</p>
            @endif
        </div>
    </section>

    <section aria-labelledby="fac-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Facilities</span>
                <h2 id="fac-title">What you can expect in Perungalathur</h2>
                <p>The gym, lift, parking and attached bathrooms vary by building, so please confirm them for this hostel.</p>
            </div>
            <div class="why-grid">
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-bed"/></svg></span><p>Furnished rooms with study table and wardrobe</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-snow"/></svg></span><p>AC and non-AC options</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-food"/></svg></span><p>Home-style meals, veg and non-veg</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-wifi"/></svg></span><p>High-speed WiFi</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-shield"/></svg></span><p>CCTV, controlled entry and warden</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-check"/></svg></span><p>RO drinking water and power backup</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-check"/></svg></span><p>Daily housekeeping and laundry</p></div>
                <div class="why-item"><span class="tick-circ"><svg class="i"><use href="#i-train"/></svg></span><p>On the suburban rail line</p></div>
            </div>
        </div>
    </section>

    <section class="panel-ivory" id="nearby" aria-labelledby="nearby-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Around Perungalathur</span>
                <h2 id="nearby-title">Places near our Perungalathur hostel</h2>
                <p>Stations, roads, campuses and neighbourhoods around Perungalathur.</p>
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
            <p class="note-box">We have not listed travel times because they depend on the exact walking route and the time of day. Ask us and we will tell you what to expect from the hostel.</p>
        </div>
    </section>

    @if ($mapProp)
        <section aria-labelledby="map-title">
            <div class="wrap">
                <div class="section-head"><span class="eyebrow">Location</span><h2 id="map-title">Find the Perungalathur hostel on the map</h2></div>
                <div class="map-box">
                    <iframe src="{{ \App\Support\Hostels::mapsEmbed($mapProp) }}" title="Map showing {{ $mapProp['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="600" height="360"></iframe>
                    <p class="map-cap">{{ \App\Support\Hostels::addressLine($mapProp) }}</p>
                </div>
            </div>
        </section>
    @endif

    <section class="panel-ivory" aria-labelledby="faq-title">
        <div class="wrap">
            <div class="section-head"><span class="eyebrow">FAQs</span><h2 id="faq-title">Questions about our Perungalathur hostel</h2></div>
            @include('partials.faq', ['faqs' => $faqs])
        </div>
    </section>

    <section class="final-cta" aria-labelledby="cta-title">
        <div class="wrap">
            <div>
                <h2 id="cta-title">Visit the Perungalathur hostel</h2>
                <p class="lead">Call or message us with your room preference and move-in date. Looking at the other side of the city? See our <a class="text-link" style="color:#fff" href="{{ route('hostels.alandur') }}">PG hostels in Alandur</a>.</p>
            </div>
            <div class="hero-actions">
                <a href="tel:{{ config('hostel.phone') }}" class="btn btn-primary"><svg class="i"><use href="#i-phone"/></svg> Call {{ config('hostel.phone_display') }}</a>
                <a href="https://wa.me/{{ config('hostel.whatsapp') }}?text=Hi%2C%20I%20want%20to%20check%20PG%20availability%20in%20Perungalathur" class="btn btn-wa" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> WhatsApp us</a>
                <a href="{{ route('contact') }}" class="btn btn-ghost">Send an enquiry</a>
            </div>
        </div>
    </section>
@endsection
