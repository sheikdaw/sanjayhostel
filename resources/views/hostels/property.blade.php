@extends('layouts.frontend')

{{--
  Individual hostel page. Only reachable when 'has_page' => true for that property in
  config/hostel.php. Publish a page only when the property has real, unique content
  (description paragraphs, its own photos, rooms/rent, FAQs). Otherwise Google may treat
  it as a thin page.
--}}
@php
    $p      = $property;
    $path   = \App\Support\Hostels::path($p);
    $area   = \App\Support\Hostels::areaName($p);
    $addr   = \App\Support\Hostels::addressLine($p);
    $embed  = \App\Support\Hostels::mapsEmbed($p);
    $maps   = ($addr || $p['maps_url']) ? \App\Support\Hostels::mapsLink($p) : null;
    $title  = ($p['name'] ?: $p['label']) . ', ' . $area . ' | Sanjay & Harini Hostels';
    $desc   = $p['summary'] ?: ($p['label'] . ' in ' . $area . ', Chennai, run by Sanjay & Harini Hostels. Call or WhatsApp to check rooms and book a visit.');
    $crumbs = [['Home', '/'], ['Our hostels', '/hostels'], [$area, config("hostel.areas.{$p['area']}.path")], [$p['label'], $path]];
    $other  = array_filter(\App\Support\Hostels::forArea($p['area']), fn ($x) => $x['key'] !== $p['key']);
@endphp

@section('title', $title)
@section('meta_description', $desc)
@section('canonical', \App\Support\Seo::url($path))
@section('schema')
    {!! \App\Support\Seo::json([\App\Support\Seo::webPage($path, $title, $desc), \App\Support\Seo::faq($p['faqs']), \App\Support\Seo::hostel($p)]) !!}
@endsection

@section('content')
    <div class="page-hero">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => $crumbs])
            <span class="eyebrow">{{ \App\Support\Hostels::genderLabel($p) }} in {{ $area }}</span>
            <h1>{{ $p['label'] }}</h1>
            @if ($p['summary'])<p>{{ $p['summary'] }}</p>@endif
        </div>
    </div>

    @if ($p['description'])
        <section>
            <div class="wrap prose">
                @foreach ($p['description'] as $para)<p>{{ $para }}</p>@endforeach
                @if ($addr)<p><strong>Address:</strong> {{ $addr }}</p>@endif
            </div>
        </section>
    @endif

    @if ($p['photos'])
        <section class="panel-ivory"><div class="wrap">
            <div class="prop-photos" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
                @foreach ($p['photos'] as $ph)
                    <img src="{{ asset($ph['src']) }}" alt="{{ $ph['alt'] }}" width="{{ $ph['w'] }}" height="{{ $ph['h'] }}" loading="lazy" decoding="async">
                @endforeach
            </div>
        </div></section>
    @endif

    @if ($p['amenities'] || $p['rooms'] || $p['rent'])
        <section><div class="wrap prose">
            <h2>Rooms and facilities</h2>
            @if ($p['rooms'])<p><strong>Room types:</strong> {{ implode(', ', $p['rooms']) }}</p>@endif
            @if ($p['rent'])<p><strong>Rent:</strong> {{ $p['rent'] }}</p>@endif
            @if ($p['amenities'])
                <ul class="amenities-list">
                    @foreach ($p['amenities'] as $a)
                        <li><svg class="i check"><use href="#i-check"/></svg><span>{{ $a }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div></section>
    @endif

    @if ($embed)
        <section class="panel-ivory"><div class="wrap">
            <div class="map-box">
                <iframe src="{{ $embed }}" title="Map showing {{ $p['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="600" height="360"></iframe>
                @if ($addr)<p class="map-cap">{{ $addr }}</p>@endif
            </div>
        </div></section>
    @endif

    @if ($p['faqs'])
        <section><div class="wrap">
            <div class="section-head"><h2>Questions about this hostel</h2></div>
            @include('partials.faq', ['faqs' => $p['faqs']])
        </div></section>
    @endif

    <section class="panel-ivory">
        <div class="wrap">
            <div class="prop-actions" style="max-width:640px;">
                <a class="btn btn-primary" href="{{ route('contact') }}">Book a visit</a>
                <a class="btn btn-wa" href="https://wa.me/{{ config('hostel.whatsapp') }}" target="_blank" rel="noopener">WhatsApp us</a>
                @if ($maps)<a class="btn btn-ghost" href="{{ $maps }}" target="_blank" rel="noopener">View on Google Maps</a>@endif
            </div>
            <div class="link-row">
                <a class="text-link" href="{{ route(config("hostel.areas.{$p['area']}.route")) }}">All hostels in {{ $area }}</a>
                @foreach ($other as $o)
                    <a class="text-link" href="{{ url(\App\Support\Hostels::path($o)) }}#{{ $o['key'] }}">{{ $o['label'] }}</a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
