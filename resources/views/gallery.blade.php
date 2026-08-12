@extends('layouts.frontend')

@section('title', 'Photo Gallery | Sanjay & Harini Hostels, Chennai')
@section('canonical', 'https://www.sanjayandharinihostels.com/gallery')
@section('meta_description', "See inside Sanjay Boys Hostel and Harini Girls Hostel — rooms, common areas, dining and study spaces across our Alandur, St. Thomas Mount and Perungalathur branches.")

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            <span class="eyebrow">Gallery</span>
            <h1>Photo Gallery — Sanjay & Harini Hostels</h1>
            <p>A look inside our rooms, common areas and dining spaces.</p>
        </div>
    </div>

    <section id="gallery">
        <div class="wrap">
            <div class="gallery-grid reveal">
                <a class="g1" href="#"><img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=700" alt="Sanjay Boys Hostel building, Alandur, near Guindy"></a>
                <a><img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=500" alt="Furnished AC room, Sanjay & Harini Hostels"></a>
                <a><img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=500" alt="Common area at Harini Girls Hostel, Alandur"></a>
                <a><img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500" alt="Dining area, Sanjay Boys Hostel"></a>
                <a class="g5" href="#"><img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?q=80&w=700" alt="Reception desk, Sanjay & Harini Hostels"></a>
                <a><img src="https://images.unsplash.com/photo-1556909114-44e3e9699e2b?q=80&w=500" alt="Study hall, Harini Girls Hostel"></a>
                <a><img src="https://images.unsplash.com/photo-1545048702-79362596cdc9?q=80&w=500" alt="Laundry and ironing area, PG hostel Chennai"></a>
            </div>
        </div>
    </section>
@endsection