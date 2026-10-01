@extends('layouts.frontend')

@section('title', 'Rooms, Rent & Facilities | Sanjay & Harini Hostels')
@section('canonical', \App\Support\Seo::url('/rooms'))
@section('meta_description', "Single, double, triple and dormitory rooms, AC or non-AC, with WiFi, CCTV and home-style meals at Sanjay & Harini Hostels in Alandur and Perungalathur, Chennai.")

@section('content')
<div class="page-hero panel-ivory">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => [['Home', '/'], ['Rooms & pricing', '/rooms']]])
            <span class="eyebrow">Rooms & Facilities</span>
            <h1>Rooms & Facilities at Sanjay & Harini Hostels</h1>
            <p>Single, double and triple sharing — AC and non-AC — with WiFi, CCTV, laundry and daily home-style meals.</p>
        </div>
    </div>

    <!-- PRICING OVERVIEW -->
    <section class="panel-ivory" id="pricing">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">Pricing</span>
                <h2>Room Pricing – Choose Your Stay</h2>
                <p>Affordable monthly rates with all amenities included. Prices are per person.</p>
            </div>
            <div class="pricing-grid reveal">
                <div class="pricing-card">
                    <div class="pricing-badge">Popular</div>
                    <h3>Single Sharing</h3>
                    <div class="price">₹8,500<span>/month</span></div>
                    <ul class="pricing-features">
                        <li>✓ Private room</li>
                        <li>✓ AC available (+₹2,000)</li>
                        <li>✓ Attached bathroom</li>
                        <li>✓ 4 meals daily</li>
                        <li>✓ WiFi & CCTV</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="btn-primary">Book Now</a>
                </div>
                <div class="pricing-card featured">
                    <div class="pricing-badge">Best Value</div>
                    <h3>Double Sharing</h3>
                    <div class="price">₹6,000<span>/month</span></div>
                    <ul class="pricing-features">
                        <li>✓ Comfortable for 2</li>
                        <li>✓ AC available (+₹1,500)</li>
                        <li>✓ Attached bathroom</li>
                        <li>✓ 4 meals daily</li>
                        <li>✓ WiFi & CCTV</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="btn-primary">Book Now</a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-badge">Budget</div>
                    <h3>Triple Sharing</h3>
                    <div class="price">₹4,500<span>/month</span></div>
                    <ul class="pricing-features">
                        <li>✓ Budget-friendly</li>
                        <li>✓ AC available (+₹1,000)</li>
                        <li>✓ Common bathroom</li>
                        <li>✓ 4 meals daily</li>
                        <li>✓ WiFi & CCTV</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="btn-primary">Book Now</a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-badge">Flexible</div>
                    <h3>Dormitory</h3>
                    <div class="price">₹3,000<span>/month</span></div>
                    <ul class="pricing-features">
                        <li>✓ For short stays</li>
                        <li>✓ Non-AC only</li>
                        <li>✓ Common bathroom</li>
                        <li>✓ Meals optional</li>
                        <li>✓ WiFi & CCTV</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="btn-primary">Book Now</a>
                </div>
            </div>
            <p class="price-note" style="margin-top:20px;">
                * Prices are per person per month. All rates are inclusive of meals, WiFi, housekeeping & security.
                <br>Security deposit: ₹2,000 (refundable). Minimum stay: 1 month.
            </p>
            <p class="price-note">Rent can vary by hostel. See our <a class="text-link" href="{{ route('hostels.alandur') }}">hostels in Alandur</a> and the <a class="text-link" href="{{ route('hostels.perungalathur') }}">hostel in Perungalathur</a>, or <a class="text-link" href="{{ route('contact') }}">contact us</a> for the exact rent at the hostel you choose.</p>
        </div>
    </section>

    <!-- FACILITIES -->
    <section id="facilities">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">Amenities</span>
                <h2>Facilities – AC, WiFi, CCTV, Food & More</h2>
                <p>Everything you need for a comfortable stay – from furnished rooms to daily meals.</p>
            </div>
            <div class="facility-tabs reveal">
                <button class="tab-btn active" data-tab="rooms">Room Types</button>
                <button class="tab-btn" data-tab="furnish">Furnishing</button>
                <button class="tab-btn" data-tab="amenities">Amenities</button>
            </div>
            <div class="tab-panel grid-cards active" id="rooms">
                <div class="fac-card"><div class="ic">🛏️</div><h3>Single Sharing</h3><p>₹8,500/mo</p></div>
                <div class="fac-card"><div class="ic">🛏️</div><h3>Double Sharing</h3><p>₹6,000/mo</p></div>
                <div class="fac-card"><div class="ic">🛏️</div><h3>Triple Sharing</h3><p>₹4,500/mo</p></div>
                <div class="fac-card"><div class="ic">🏠</div><h3>Dormitory</h3><p>₹3,000/mo</p></div>
                <div class="fac-card"><div class="ic">❄️</div><h3>AC Rooms</h3><p>+₹1,000–2,000</p></div>
                <div class="fac-card"><div class="ic">🌬️</div><h3>Non-AC Rooms</h3><p>Included</p></div>
                <div class="fac-card"><div class="ic">🚿</div><h3>Attached Bathroom</h3><p>Select rooms</p></div>
                <div class="fac-card"><div class="ic">🌿</div><h3>Balcony Rooms</h3><p>Subject to availability</p></div>
            </div>
            <div class="tab-panel grid-cards" id="furnish">
                <div class="fac-card"><div class="ic">🛌</div><h3>Cot & Mattress</h3></div>
                <div class="fac-card"><div class="ic">📚</div><h3>Study Table & Chair</h3></div>
                <div class="fac-card"><div class="ic">🚪</div><h3>Wardrobe & Locker</h3></div>
                <div class="fac-card"><div class="ic">🔌</div><h3>Charging Points</h3></div>
                <div class="fac-card"><div class="ic">💡</div><h3>LED Lights</h3></div>
                <div class="fac-card"><div class="ic">🌀</div><h3>Ceiling Fans</h3></div>
            </div>
            <div class="tab-panel grid-cards" id="amenities">
                <div class="fac-card"><div class="ic">📶</div><h3>High-Speed WiFi</h3></div>
                <div class="fac-card"><div class="ic">🚰</div><h3>RO Water 24x7</h3></div>
                <div class="fac-card"><div class="ic">🔒</div><h3>CCTV & Biometric</h3></div>
                <div class="fac-card"><div class="ic">⚡</div><h3>Power Backup</h3></div>
                <div class="fac-card"><div class="ic">🛗</div><h3>Lift Facility</h3></div>
                <div class="fac-card"><div class="ic">🧹</div><h3>Daily Housekeeping</h3></div>
                <div class="fac-card"><div class="ic">👕</div><h3>Laundry & Ironing</h3></div>
                <div class="fac-card"><div class="ic">📖</div><h3>Study Hall</h3></div>
                <div class="fac-card"><div class="ic">🅿️</div><h3>Parking</h3></div>
            </div>
        </div>
    </section>

    <!-- FOOD -->
    <section class="panel-ivory" id="food">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="eyebrow">Food & Lunch Box</span>
                <h2>Home-Style Meals Daily</h2>
                <p>Vegetarian & non-vegetarian options. Lunch box delivery for working professionals.</p>
            </div>
            <div class="food-grid reveal">
                <div>
                    <h3 style="font-size:1.4rem;">4 meals a day</h3>
                    <div class="meal-tags">
                        <div class="meal-tag">Breakfast</div>
                        <div class="meal-tag">Lunch</div>
                        <div class="meal-tag">Snacks</div>
                        <div class="meal-tag">Dinner</div>
                    </div>
                    <p style="color:var(--stone);">Freshly cooked with RO water. South Indian & North Indian options. Weekly specials.</p>
                    <h3 style="margin-top:30px;">Lunch Box Delivery</h3>
                    <p style="color:var(--stone);">For office employees, IT staff, college students, and senior citizens. Bulk orders for corporates.</p>
                    <div class="plan-grid">
                        <div class="plan-card"><span class="tag-pill">Veg</span><h3>Daily Veg</h3><p>₹120/meal</p></div>
                        <div class="plan-card"><span class="tag-pill">Premium</span><h3>Veg / Non-Veg</h3><p>₹150/meal</p></div>
                        <div class="plan-card"><span class="tag-pill">Corporate</span><h3>Bulk Supply</h3><p>Custom quote</p></div>
                    </div>
                </div>
                <div class="food-visual">
                    <img loading="lazy" decoding="async" width="900" height="675" src="https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&q=70&w=900" alt="Home-style meals served at Sanjay & Harini Hostels, Chennai">
                </div>
            </div>
        </div>
    </section>
@endsection
