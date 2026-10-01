@extends('layouts.frontend')

@php
    $path  = '/hostels';
    $title = "Our Hostels in Alandur & Perungalathur | Sanjay & Harini Hostels";
    $desc  = "Six men's and women's PG hostels across Alandur and Perungalathur, Chennai. See where each hostel is, who it is for, and how to choose between the two areas.";
    $crumbs = [['Home', '/'], ['Our hostels', $path]];
    $alandur = array_filter($properties, fn ($p) => $p['area'] === 'alandur');
    $perung  = array_filter($properties, fn ($p) => $p['area'] === 'perungalathur');
    $faqs = [
        ["Do you have only one hostel?", "No. Sanjay & Harini Hostels runs six separate hostels: five in Alandur (on Pudupettai Street, M.K.N. Road and Raja Street) and one in Perungalathur. Men and women stay in separate hostels."],
        ["Which hostels are for women?", "Our women's hostels are in Alandur: two on Pudupettai Street and a ladies' hostel on Raja Street."],
        ["Which hostels are for men?", "Our men's hostels are in Alandur (one on Pudupettai Street and one on M.K.N. Road) and one in Perungalathur."],
        ["How do I check which room is free?", "Rooms fill at different speeds in each hostel, so the quickest way is to call or WhatsApp us, or send the enquiry form. Tell us the hostel, the room type and your move-in date."],
    ];
@endphp

@section('title', $title)
@section('meta_description', $desc)
@section('canonical', \App\Support\Seo::url($path))
@section('schema')
    {!! \App\Support\Seo::json([\App\Support\Seo::webPage($path, $title, $desc), \App\Support\Seo::faq($faqs)]) !!}
@endsection

@section('content')
    <div class="page-hero">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => $crumbs])
            <span class="eyebrow">Our hostels</span>
            <h1>Men's and women's hostels in Alandur and Perungalathur</h1>
            <p>Six separate hostels, each with its own building. Pick the area that suits your office, college or daily commute, then the hostel that matches who you are.</p>
        </div>
    </div>

    <section>
        <div class="wrap prose">
            <h2>Two areas, six hostels</h2>
            <p>We operate multiple men's and women's hostels across Alandur and Perungalathur. Five of them are in Alandur, spread over three streets, and one is in Perungalathur. They are run as separate properties, so the rooms, rent and availability can differ from one hostel to the next.</p>
            <p>If you are not sure where to start, the two areas feel quite different. Alandur is a metro interchange, so it suits people who work around Guindy, the airport side or central Chennai. Perungalathur is on the suburban rail line and GST Road between Tambaram and Vandalur, which suits people studying or working in that corridor, or commuting by train.</p>
        </div>
    </section>

    <section class="panel-ivory" aria-labelledby="areas-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Choose an area</span>
                <h2 id="areas-title">Where do you want to stay?</h2>
            </div>
            <div class="location-grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">
                <div class="location-card">
                    <div class="who"><span class="pill">Men</span><span class="pill girls">Women</span></div>
                    <h3>Hostels in Alandur</h3>
                    <p>{{ count($alandur) }} hostels on Pudupettai Street, M.K.N. Road and Raja Street. Men's and women's hostels, close to Alandur Metro, Guindy and St. Thomas Mount.</p>
                    <a class="go" href="{{ route('hostels.alandur') }}">See PG in Alandur <svg class="i"><use href="#i-arrow"/></svg></a>
                </div>
                <div class="location-card">
                    <div class="who"><span class="pill">Men</span></div>
                    <h3>Hostel in Perungalathur</h3>
                    <p>A men's hostel in Perungalathur, between Tambaram and Vandalur, on the suburban rail line and GST Road corridor.</p>
                    <a class="go" href="{{ route('hostels.perungalathur') }}">See the Perungalathur hostel <svg class="i"><use href="#i-arrow"/></svg></a>
                </div>
            </div>
        </div>
    </section>

    <section aria-labelledby="all-title">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">All six hostels</span>
                <h2 id="all-title">Every hostel at a glance</h2>
                <p>Each hostel is a separate building. Call us for the exact location and a visit.</p>
            </div>
            <div class="table-wrap">
                <table class="prop-table">
                    <thead><tr><th scope="col">Hostel</th><th scope="col">For</th><th scope="col">Street / area</th><th scope="col">More</th></tr></thead>
                    <tbody>
                        @foreach ($properties as $p)
                            <tr>
                                <th scope="row" style="background:none;">{{ $p['label'] }}</th>
                                <td>{{ $p['gender'] === 'women' ? 'Women' : 'Men' }}</td>
                                <td>{{ $p['street'] === \App\Support\Hostels::areaName($p) ? $p['street'] : $p['street'] . ', ' . \App\Support\Hostels::areaName($p) }}</td>
                                <td><a class="text-link" href="{{ url(\App\Support\Hostels::path($p)) }}#{{ $p['key'] }}">Details</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="link-row">
                <a class="btn btn-primary" href="{{ route('contact') }}">Book a room</a>
                <a class="btn btn-ghost" href="{{ route('rooms') }}">Rooms &amp; pricing</a>
            </div>
        </div>
    </section>

    <section class="panel-ivory" aria-labelledby="faq-title">
        <div class="wrap">
            <div class="section-head"><span class="eyebrow">FAQs</span><h2 id="faq-title">Choosing a hostel</h2></div>
            @include('partials.faq', ['faqs' => $faqs])
        </div>
    </section>
@endsection
