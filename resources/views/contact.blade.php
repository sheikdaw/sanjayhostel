@extends('layouts.frontend')

@section('title', 'Contact Us | Book a PG Room in Alandur, St. Thomas Mount & Perungalathur')
@section('canonical', 'https://www.sanjayandharinihostels.com/contact')
@section('meta_description', "Contact Sanjay Boys Hostel and Harini Girls Hostel to check room availability and book a PG in Alandur, St. Thomas Mount or Perungalathur, Chennai. Call, WhatsApp, or fill the enquiry form.")

@section('schema')
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "LodgingBusiness",
                "@id": "https://www.sanjayandharinihostels.com/#organization",
                "name": "Sanjay & Harini Hostels",
                "description": "PG accommodation in Alandur, St. Thomas Mount and Perungalathur, Chennai.",
                "url": "https://www.sanjayandharinihostels.com",
                "telephone": "+91-9876543210",
                "priceRange": "₹₹",
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "Chennai",
                    "addressRegion": "Tamil Nadu",
                    "addressCountry": "IN"
                }
            },
            {
                "@type": "BreadcrumbList",
                "itemListElement": [
                    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://www.sanjayandharinihostels.com/" },
                    { "@type": "ListItem", "position": 2, "name": "Contact / Book Now", "item": "https://www.sanjayandharinihostels.com/contact" }
                ]
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "What room types are available?",
                        "acceptedAnswer": { "@type": "Answer", "text": "Single, double, triple, and dormitory sharing — both AC and non-AC options." }
                    },
                    {
                        "@type": "Question",
                        "name": "Are meals provided?",
                        "acceptedAnswer": { "@type": "Answer", "text": "Yes, 4 meals daily (breakfast, lunch, snacks, dinner). Lunch box delivery is also available." }
                    },
                    {
                        "@type": "Question",
                        "name": "Is the hostel safe for women?",
                        "acceptedAnswer": { "@type": "Answer", "text": "Harini Girls Hostel is exclusively for women, with 24/7 CCTV, biometric entry and on-site warden supervision." }
                    },
                    {
                        "@type": "Question",
                        "name": "Which locations do you operate in?",
                        "acceptedAnswer": { "@type": "Answer", "text": "Sanjay Boys Hostel operates in Alandur, St. Thomas Mount and Perungalathur. Harini Girls Hostel operates in Alandur and St. Thomas Mount." }
                    },
                    {
                        "@type": "Question",
                        "name": "How can I book a room?",
                        "acceptedAnswer": { "@type": "Answer", "text": "Fill out the enquiry form on the website, call the hostel directly, or message on WhatsApp to check availability." }
                    }
                ]
            }
        ]
    }
    </script>
@endsection

@section('content')
    <div class="page-hero panel-ivory">
        <div class="wrap">
            <span class="eyebrow">Contact</span>
            <h1>Contact Us / Book a Room</h1>
            <p>PG in Alandur, St. Thomas Mount & Perungalathur – affordable, safe, and comfortable.</p>
        </div>
    </div>

    <section id="contact">
        <div class="wrap">
            <div class="contact-grid reveal">
                <div class="contact-card boys-c">
                    <span class="tag-pill">Sanjay Boys Hostel</span>
                    <h2>Men's PG – Alandur, St. Mount & Perungalathur</h2>
                    <div class="contact-row"><span class="ic">📍</span><span>Alandur, St. Thomas Mount, Perungalathur</span></div>
                    <div class="contact-row"><span class="ic">📞</span><span>+91 98765 43210</span></div>
                    <div class="contact-row"><span class="ic">📧</span><span>sanjayboys@hostel.in</span></div>
                </div>
                <div class="contact-card girls-c">
                    <span class="tag-pill">Harini Ladies Hostel</span>
                    <h2>Women's PG – Alandur & St. Thomas Mount</h2>
                    <div class="contact-row"><span class="ic">📍</span><span>Alandur, St. Thomas Mount</span></div>
                    <div class="contact-row"><span class="ic">📞</span><span>+91 98765 43211</span></div>
                    <div class="contact-row"><span class="ic">📧</span><span>harinigirls@hostel.in</span></div>
                </div>
            </div>

            <div class="form-shell reveal">
                <h3>Enquiry Form – Room & Lunch Box</h3>
                <p>Fill in your details and we'll get back to you with availability.</p>
                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div><label>Full Name</label><input type="text" name="name" required placeholder="Your name"></div>
                        <div><label>Phone</label><input type="tel" name="phone" required placeholder="+91 XXXXX XXXXX"></div>
                    </div>
                    <div class="form-row">
                        <div><label>Interest</label>
                            <select name="interest">
                                <option value="sanjay_room">Sanjay Boys – Room</option>
                                <option value="harini_room">Harini Girls – Room</option>
                                <option value="lunch_box">Lunch Box Delivery</option>
                                <option value="general">General Enquiry</option>
                            </select>
                        </div>
                        <div><label>Branch</label>
                            <select name="branch">
                                <option value="alandur">Alandur</option>
                                <option value="st_thomas_mount">St. Thomas Mount</option>
                                <option value="perungalathur">Perungalathur (Boys only)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-full"><label>Message</label><textarea name="message" rows="3" placeholder="Room type, move-in date, AC preference..."></textarea></div>
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
                <div class="faq-item"><button class="faq-q">Is the hostel safe for women? <span class="plus">+</span></button><div class="faq-a">Harini Girls Hostel is exclusively for women with 24/7 CCTV and warden.</div></div>
                <div class="faq-item"><button class="faq-q">Which branches have AC rooms? <span class="plus">+</span></button><div class="faq-a">All branches offer AC and non-AC rooms.</div></div>
                <div class="faq-item"><button class="faq-q">How to book a room? <span class="plus">+</span></button><div class="faq-a">Fill the enquiry form or call us. We'll help you with availability.</div></div>
                <div class="faq-item"><button class="faq-q">Is lunch box delivery available for non-residents? <span class="plus">+</span></button><div class="faq-a">Yes, we deliver lunch boxes to offices, colleges, and homes.</div></div>
                <div class="faq-item"><button class="faq-q">Are there attached bathrooms? <span class="plus">+</span></button><div class="faq-a">Yes, in select rooms.</div></div>
                <div class="faq-item"><button class="faq-q">Do you have study tables? <span class="plus">+</span></button><div class="faq-a">Yes, every room has a study table and chair.</div></div>
                <div class="faq-item"><button class="faq-q">Is there a lift? <span class="plus">+</span></button><div class="faq-a">Yes, in multi-floor buildings.</div></div>
                <div class="faq-item"><button class="faq-q">Do you provide RO water? <span class="plus">+</span></button><div class="faq-a">Yes, 24/7 RO drinking water.</div></div>
                <div class="faq-item"><button class="faq-q">What are the nearby landmarks? <span class="plus">+</span></button><div class="faq-a">Alandur Metro, St. Thomas Mount station, Perungalathur station, Guindy, Tambaram, Airport.</div></div>
                <div class="faq-item"><button class="faq-q">Is there a warden? <span class="plus">+</span></button><div class="faq-a">Yes, on-site warden for both hostels.</div></div>
                <div class="faq-item"><button class="faq-q">Can I get a single room? <span class="plus">+</span></button><div class="faq-a">Yes, subject to availability.</div></div>
                <div class="faq-item"><button class="faq-q">Do you have a common TV area? <span class="plus">+</span></button><div class="faq-a">Yes, common lounge with TV.</div></div>
            </div>
        </div>
    </section>
@endsection