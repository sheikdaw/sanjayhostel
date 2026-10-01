{{-- One physical hostel. Empty fields are not rendered. --}}
@php
    $addr  = \App\Support\Hostels::addressLine($p);
    $maps  = $addr || $p['maps_url'] ? \App\Support\Hostels::mapsLink($p) : null;
    $girls = $p['gender'] === 'women';
@endphp
<article class="prop-card {{ $girls ? 'women' : 'men' }}" id="{{ $p['key'] }}">
    <span class="pill {{ $girls ? 'girls' : '' }}">{{ \App\Support\Hostels::genderLabel($p) }}</span>
    <h3>{{ $p['label'] }}</h3>
    <p class="prop-street">{{ $p['street'] === \App\Support\Hostels::areaName($p) ? $p['street'] : $p['street'] . ', ' . \App\Support\Hostels::areaName($p) }}</p>

    @if ($addr)
        <p class="prop-addr"><svg class="i"><use href="#i-pin"/></svg> <span>{{ $addr }}</span></p>
    @endif

    @if ($p['summary'])
        <p class="prop-summary">{{ $p['summary'] }}</p>
    @endif

    @if ($p['photos'])
        <div class="prop-photos">
            @foreach ($p['photos'] as $ph)
                <img src="{{ asset($ph['src']) }}" alt="{{ $ph['alt'] }}" width="{{ $ph['w'] }}" height="{{ $ph['h'] }}" loading="lazy" decoding="async">
            @endforeach
        </div>
    @endif

    <div class="prop-actions">
        @if ($p['has_page'])
            <a class="btn btn-dark" href="{{ url(\App\Support\Hostels::path($p)) }}">Hostel details</a>
        @endif
        <a class="btn btn-primary" href="{{ route('contact') }}">Enquire about this hostel</a>
        @if ($maps)
            <a class="btn btn-ghost" href="{{ $maps }}" target="_blank" rel="noopener">View on Google Maps</a>
        @endif
    </div>
</article>
