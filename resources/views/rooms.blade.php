@extends('layouts.frontend')

@section('title', 'Rooms & Facilities | AC/Non-AC PG in Alandur, St. Thomas Mount & Perungalathur')
@section('canonical', 'https://www.sanjayandharinihostels.com/rooms')
@section('meta_description', "Single, double and triple sharing rooms, AC/non-AC options, WiFi, CCTV and daily home-style meals at Sanjay Boys Hostel and Harini Girls Hostel, Chennai.")

@section('content')
<style>
    /* Pricing Section */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.pricing-card {
    background: #fff;
    border-radius: 16px;
    padding: 30px 25px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border: 1px solid #eee;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pricing-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.10);
}

.pricing-card.featured {
    border-color: #c9a84c;
    background: #fcf9f0;
}

.pricing-badge {
    position: absolute;
    top: -12px;
    right: 20px;
    background: #c9a84c;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 4px 16px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.pricing-card h3 {
    font-size: 1.4rem;
    margin-bottom: 10px;
    color: #2d2d2d;
}

.price {
    font-size: 2.4rem;
    font-weight: 700;
    color: #c9a84c;
    margin: 10px 0 15px;
}

.price span {
    font-size: 1rem;
    font-weight: 400;
    color: #888;
}

.pricing-features {
    list-style: none;
    padding: 0;
    margin: 15px 0 25px;
    text-align: left;
}

.pricing-features li {
    padding: 6px 0;
    color: #555;
    font-size: 0.95rem;
    border-bottom: 1px solid #f5f5f5;
}

.pricing-features li:last-child {
    border-bottom: none;
}

.pricing-card .btn-primary {
    display: inline-block;
    background: #c9a84c;
    color: #fff;
    padding: 10px 32px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s ease;
}

.pricing-card .btn-primary:hover {
    background: #b0943d;
}

/* Price tags in facility cards */
.fac-card p {
    font-size: 0.9rem;
    color: #888;
    margin-top: 2px;
}
</style>
    <div class="page-hero panel-ivory">
        <div class="wrap">
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
            <p style="text-align:center;color:var(--stone);margin-top:20px;font-size:0.9rem;">
                * Prices are per person per month. All rates are inclusive of meals, WiFi, housekeeping & security.
                <br>Security deposit: ₹2,000 (refundable). Minimum stay: 1 month.
            </p>
        </div>
    </section>

    <!-- FACILITIES -->
    <section class="panel-ivory" id="facilities">
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
                <div class="fac-card"><div class="ic">🛏️</div><h4>Single Sharing</h4><p>₹8,500/mo</p></div>
                <div class="fac-card"><div class="ic">🛏️</div><h4>Double Sharing</h4><p>₹6,000/mo</p></div>
                <div class="fac-card"><div class="ic">🛏️</div><h4>Triple Sharing</h4><p>₹4,500/mo</p></div>
                <div class="fac-card"><div class="ic">🏠</div><h4>Dormitory</h4><p>₹3,000/mo</p></div>
                <div class="fac-card"><div class="ic">❄️</div><h4>AC Rooms</h4><p>+₹1,000–2,000</p></div>
                <div class="fac-card"><div class="ic">🌬️</div><h4>Non-AC Rooms</h4><p>Included</p></div>
                <div class="fac-card"><div class="ic">🚿</div><h4>Attached Bathroom</h4><p>Select rooms</p></div>
                <div class="fac-card"><div class="ic">🌿</div><h4>Balcony Rooms</h4><p>Subject to availability</p></div>
            </div>
            <div class="tab-panel grid-cards" id="furnish">
                <div class="fac-card"><div class="ic">🛌</div><h4>Cot & Mattress</h4></div>
                <div class="fac-card"><div class="ic">📚</div><h4>Study Table & Chair</h4></div>
                <div class="fac-card"><div class="ic">🚪</div><h4>Wardrobe & Locker</h4></div>
                <div class="fac-card"><div class="ic">🔌</div><h4>Charging Points</h4></div>
                <div class="fac-card"><div class="ic">💡</div><h4>LED Lights</h4></div>
                <div class="fac-card"><div class="ic">🌀</div><h4>Ceiling Fans</h4></div>
            </div>
            <div class="tab-panel grid-cards" id="amenities">
                <div class="fac-card"><div class="ic">📶</div><h4>High-Speed WiFi</h4></div>
                <div class="fac-card"><div class="ic">🚰</div><h4>RO Water 24x7</h4></div>
                <div class="fac-card"><div class="ic">🔒</div><h4>CCTV & Biometric</h4></div>
                <div class="fac-card"><div class="ic">⚡</div><h4>Power Backup</h4></div>
                <div class="fac-card"><div class="ic">🛗</div><h4>Lift Facility</h4></div>
                <div class="fac-card"><div class="ic">🧹</div><h4>Daily Housekeeping</h4></div>
                <div class="fac-card"><div class="ic">👕</div><h4>Laundry & Ironing</h4></div>
                <div class="fac-card"><div class="ic">📖</div><h4>Study Hall</h4></div>
                <div class="fac-card"><div class="ic">🅿️</div><h4>Parking</h4></div>
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
                    <h3 style="font-size:1.3rem;">4 Meals a Day</h3>
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
                        <div class="plan-card"><span class="tag-pill">Veg</span><h4>Daily Veg</h4><p>₹120/meal</p></div>
                        <div class="plan-card"><span class="tag-pill">Premium</span><h4>Veg / Non-Veg</h4><p>₹150/meal</p></div>
                        <div class="plan-card"><span class="tag-pill">Corporate</span><h4>Bulk Supply</h4><p>Custom quote</p></div>
                    </div>
                </div>
                <div class="food-visual">
                    <img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=900" alt="Home-style meals served at Sanjay & Harini Hostels, Chennai">
                </div>
            </div>
        </div>
    </section>
@endsection
