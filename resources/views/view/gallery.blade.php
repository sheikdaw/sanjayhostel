@extends('layouts.frontend')

@section('title', 'Photo Gallery | Sanjay & Harini Hostels, Chennai')
@section('canonical', \App\Support\Seo::url('/gallery'))
@section('meta_description', "Photos of rooms, common areas, dining and study spaces at Sanjay & Harini Hostels, with men's and women's hostels in Alandur and Perungalathur, Chennai.")

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => [['Home', '/'], ['Gallery', '/gallery']]])
            <span class="eyebrow">Gallery</span>
            <h1>Photo Gallery: Sanjay &amp; Harini Hostels</h1>
            <p>A look at rooms, common areas and dining spaces in our hostels.</p>
        </div>
    </div>

    {{-- Photos of each real property appear here as soon as they are added to config/hostel.php ('photos'). --}}
    @foreach (\App\Support\Hostels::all() as $p)
        @if ($p['photos'])
            <section id="{{ $p['key'] }}">
                <div class="wrap">
                    <div class="section-head"><h2>{{ $p['label'] }}</h2></div>
                    <div class="prop-photos" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
                        @foreach ($p['photos'] as $ph)
                            <img src="{{ asset($ph['src']) }}" alt="{{ $ph['alt'] }}" width="{{ $ph['w'] }}" height="{{ $ph['h'] }}" loading="lazy" decoding="async">
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endforeach

    <section id="gallery">
        <div class="wrap">
            <div class="gallery-grid reveal">
                <div class="gal g1"><img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&q=70&w=700" alt="Hostel building exterior" width="700" height="525" loading="lazy" decoding="async"></div>
                <div class="gal "><img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&q=70&w=500" alt="Furnished hostel room with bed and study desk" width="500" height="375" loading="lazy" decoding="async"></div>
                <div class="gal "><img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&q=70&w=500" alt="Hostel common area with seating" width="500" height="375" loading="lazy" decoding="async"></div>
                <div class="gal "><img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&q=70&w=500" alt="Dining area with home-style meals" width="500" height="375" loading="lazy" decoding="async"></div>
                <div class="gal g5"><img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?auto=format&fit=crop&q=70&w=700" alt="Hostel reception and entrance" width="700" height="525" loading="lazy" decoding="async"></div>
                <div class="gal "><img src="https://images.unsplash.com/photo-1556909114-44e3e9699e2b?auto=format&fit=crop&q=70&w=500" alt="Study hall with desks" width="500" height="375" loading="lazy" decoding="async"></div>
                <div class="gal "><img src="https://images.unsplash.com/photo-1545048702-79362596cdc9?auto=format&fit=crop&q=70&w=500" alt="Laundry and ironing area" width="500" height="375" loading="lazy" decoding="async"></div>
            </div>
            @if (config('hostel.photos_are_placeholders'))
                <p class="img-caption">These are illustrative images. Photos of each hostel are being added.</p>
            @endif
            <div class="link-row">
                <a class="btn btn-ghost" href="{{ route('hostels.alandur') }}">Hostels in Alandur</a>
                <a class="btn btn-ghost" href="{{ route('hostels.perungalathur') }}">Hostel in Perungalathur</a>
                <a class="btn btn-primary" href="{{ route('contact') }}">Book a visit</a>
            </div>
        </div>
    </section>
@endsection
