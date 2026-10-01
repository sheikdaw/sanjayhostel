@extends('layouts.frontend')

@section('title', 'Contact Us & Book a PG Room | Sanjay & Harini Hostels')
@section('canonical', \App\Support\Seo::url('/contact'))
@section('meta_description', "Check room availability and book a PG hostel in Alandur or Perungalathur, Chennai. Call, WhatsApp or send the enquiry form to Sanjay & Harini Hostels.")

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            @include('partials.breadcrumbs', ['crumbs' => [['Home', '/'], ['Contact', '/contact']]])
            <span class="eyebrow">Contact</span>
            <h1>Contact Us / Book a Room</h1>
            <p>Men's and women's PG hostels in Alandur and Perungalathur, Chennai.</p>
        </div>
    </div>

    <section id="contact">
        <div class="wrap">
            <div class="contact-grid reveal">
                <div class="contact-card boys-c">
                    <span class="tag-pill">Alandur</span>
                    <h2>Men's and women's hostels in Alandur</h2>
                    <div class="contact-row"><span class="ic">📍</span><span>Pudupettai Street, M.K.N. Road and Raja Street, Alandur, Chennai</span></div>
                    <div class="contact-row"><span class="ic">📞</span><a href="tel:{{ config('hostel.phone') }}">{{ config('hostel.phone_display') }}</a></div>
                    <div class="contact-row"><span class="ic">📧</span><a href="mailto:{{ config('hostel.email') }}">{{ config('hostel.email') }}</a></div>
                    <div class="contact-row"><span class="ic">➜</span><a class="text-link" href="{{ route('hostels.alandur') }}">See our hostels in Alandur</a></div>
                </div>
                <div class="contact-card girls-c">
                    <span class="tag-pill">Perungalathur</span>
                    <h2>Men's hostel in Perungalathur</h2>
                    <div class="contact-row"><span class="ic">📍</span><span>Perungalathur, Chennai (between Tambaram and Vandalur)</span></div>
                    <div class="contact-row"><span class="ic">📞</span><a href="tel:{{ config('hostel.phone') }}">{{ config('hostel.phone_display') }}</a></div>
                    <div class="contact-row"><span class="ic">📧</span><a href="mailto:{{ config('hostel.email') }}">{{ config('hostel.email') }}</a></div>
                    <div class="contact-row"><span class="ic">➜</span><a class="text-link" href="{{ route('hostels.perungalathur') }}">See the Perungalathur hostel</a></div>
                </div>
            </div>

            <div class="form-shell reveal">
                <h3>Enquiry Form – Room & Lunch Box</h3>
                <p>Fill in your details and we'll get back to you with availability.</p>
                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div><label for="c-name">Full Name</label><input id="c-name" type="text" name="name" required autocomplete="name" placeholder="Your name"></div>
                        <div><label for="c-phone">Phone</label><input id="c-phone" type="tel" name="phone" required autocomplete="tel" placeholder="+91 XXXXX XXXXX"></div>
                    </div>
                    <div class="form-row">
                        <div><label for="c-interest">Interest</label>
                            <select id="c-interest" name="interest">
                                <option value="sanjay_room">Men's hostel – Room</option>
                                <option value="harini_room">Women's hostel – Room</option>
                                <option value="lunch_box">Lunch Box Delivery</option>
                                <option value="general">General Enquiry</option>
                            </select>
                        </div>
                        <div><label for="c-branch">Branch</label>
                            <select id="c-branch" name="branch">
                                <option value="alandur">Alandur</option>
                                <option value="st_thomas_mount">St. Thomas Mount</option>
                                <option value="perungalathur">Perungalathur (Boys only)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-full"><label for="c-message">Message</label><textarea id="c-message" name="message" rows="3" placeholder="Room type, move-in date, AC preference..."></textarea></div>
                    <button type="submit" class="form-submit">Send Enquiry</button>
                </form>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="panel-ivory">
        <div class="wrap">
            <div class="section-head reveal"><span class="eyebrow">FAQ</span><h2>Frequently Asked Questions</h2></div>
            <div class="faq-list reveal">
                <div class="faq-item"><button class="faq-q">What room types are available? <span class="plus">+</span></button><div class="faq-a">Single, double, triple, and dormitory – AC & non-AC.</div></div>
                <div class="faq-item"><button class="faq-q">Is WiFi included? <span class="plus">+</span></button><div class="faq-a">Yes, high-speed WiFi is free for all residents.</div></div>
                <div class="faq-item"><button class="faq-q">Are meals provided? <span class="plus">+</span></button><div class="faq-a">Yes, 4 meals daily (breakfast, lunch, snacks, dinner). Lunch box delivery also available.</div></div>
                <div class="faq-item"><button class="faq-q">Is parking available? <span class="plus">+</span></button><div class="faq-a">Yes, bike and car parking available.</div></div>
                <div class="faq-item"><button class="faq-q">Is laundry service available? <span class="plus">+</span></button><div class="faq-a">Yes, washing machine and ironing area on-site.</div></div>
                <div class="faq-item"><button class="faq-q">Are visitors allowed? <span class="plus">+</span></button><div class="faq-a">Visitors allowed during set hours with proper tracking.</div></div>
                <div class="faq-item"><button class="faq-q">Do you have power backup? <span class="plus">+</span></button><div class="faq-a">Yes, generator backup for 24/7 power.</div></div>
                <div class="faq-item"><button class="faq-q">Is the hostel safe for women? <span class="plus">+</span></button><div class="faq-a">Our women's hostels are exclusively for women, with 24/7 CCTV and an on-site warden.</div></div>
                <div class="faq-item"><button class="faq-q">Which hostels have AC rooms? <span class="plus">+</span></button><div class="faq-a">Our hostels offer AC and non-AC rooms.</div></div>
                <div class="faq-item"><button class="faq-q">How to book a room? <span class="plus">+</span></button><div class="faq-a">Fill the enquiry form or call us. We'll help you with availability.</div></div>
                <div class="faq-item"><button class="faq-q">Is lunch box delivery available for non-residents? <span class="plus">+</span></button><div class="faq-a">Yes, we deliver lunch boxes to offices, colleges, and homes.</div></div>
                <div class="faq-item"><button class="faq-q">Are there attached bathrooms? <span class="plus">+</span></button><div class="faq-a">Yes, in select rooms.</div></div>
                <div class="faq-item"><button class="faq-q">Do you have study tables? <span class="plus">+</span></button><div class="faq-a">Yes, every room has a study table and chair.</div></div>
                <div class="faq-item"><button class="faq-q">Is there a lift? <span class="plus">+</span></button><div class="faq-a">Yes, in multi-floor buildings.</div></div>
                <div class="faq-item"><button class="faq-q">Do you provide RO water? <span class="plus">+</span></button><div class="faq-a">Yes, 24/7 RO drinking water.</div></div>
                <div class="faq-item"><button class="faq-q">What are the nearby landmarks? <span class="plus">+</span></button><div class="faq-a">Around Alandur: Alandur Metro, St. Thomas Mount, Guindy and the airport. Around Perungalathur: the railway station, Tambaram and Vandalur. See <a href="{{ route('hostels.alandur') }}">Alandur</a> and <a href="{{ route('hostels.perungalathur') }}">Perungalathur</a> for details.</div></div>
                <div class="faq-item"><button class="faq-q">Is there a warden? <span class="plus">+</span></button><div class="faq-a">Yes, there is an on-site warden at our hostels.</div></div>
                <div class="faq-item"><button class="faq-q">Can I get a single room? <span class="plus">+</span></button><div class="faq-a">Yes, subject to availability.</div></div>
                <div class="faq-item"><button class="faq-q">Do you have a common TV area? <span class="plus">+</span></button><div class="faq-a">Yes, common lounge with TV.</div></div>
            </div>
        </div>
    </section>
@endsection
