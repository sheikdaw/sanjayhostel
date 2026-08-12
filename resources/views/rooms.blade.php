@extends('layouts.frontend')

@section('title', 'Rooms & Facilities | AC/Non-AC PG in Alandur, St. Thomas Mount & Perungalathur')
@section('canonical', 'https://www.sanjayandharinihostels.com/rooms')
@section('meta_description', "Single, double and triple sharing rooms, AC/non-AC options, WiFi, CCTV and daily home-style meals at Sanjay Boys Hostel and Harini Girls Hostel, Chennai.")

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            <span class="eyebrow">Rooms & Facilities</span>
            <h1>Rooms & Facilities at Sanjay & Harini Hostels</h1>
            <p>Single, double and triple sharing — AC and non-AC — with WiFi, CCTV, laundry and daily home-style meals.</p>
        </div>
    </div>

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
                <div class="fac-card"><div class="ic">🛏️</div><h4>Single Sharing</h4><p>Private room for professionals.</p></div>
                <div class="fac-card"><div class="ic">🛏️</div><h4>Double Sharing</h4><p>Comfortable for two.</p></div>
                <div class="fac-card"><div class="ic">🛏️</div><h4>Triple Sharing</h4><p>Budget-friendly.</p></div>
                <div class="fac-card"><div class="ic">🏠</div><h4>Dormitory</h4><p>For short stays.</p></div>
                <div class="fac-card"><div class="ic">❄️</div><h4>AC Rooms</h4><p>Climate controlled.</p></div>
                <div class="fac-card"><div class="ic">🌬️</div><h4>Non-AC Rooms</h4><p>Ceiling fan cooled.</p></div>
                <div class="fac-card"><div class="ic">🚿</div><h4>Attached Bathroom</h4><p>In select rooms.</p></div>
                <div class="fac-card"><div class="ic">🌿</div><h4>Balcony Rooms</h4><p>Subject to availability.</p></div>
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
                        <div class="plan-card"><span class="tag-pill">Veg</span><h4>Daily Veg</h4><p>Simple & healthy</p></div>
                        <div class="plan-card"><span class="tag-pill">Premium</span><h4>Veg / Non-Veg</h4><p>Choice every day</p></div>
                        <div class="plan-card"><span class="tag-pill">Corporate</span><h4>Bulk Supply</h4><p>For teams</p></div>
                    </div>
                </div>
                <div class="food-visual">
                    <img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=900" alt="Home-style meals served at Sanjay & Harini Hostels, Chennai">
                </div>
            </div>
        </div>
    </section>
@endsection