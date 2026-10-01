<?php

/*
|--------------------------------------------------------------------------
| Sanjay & Harini Hostels - single source of truth for SEO + contact data
|--------------------------------------------------------------------------
| Rules used by the views and by the JSON-LD builder (App\Support\Seo):
|
|  - null / empty values are simply NOT rendered on the public site.
|    Nothing is guessed. Fill a value in and it appears everywhere it belongs.
|  - A property only gets LocalBusiness ("Hostel") schema when
|    'address_confirmed' => true AND 'address' is filled in.
|  - A property only gets its own page (and sitemap entry) when
|    'has_page' => true. Do this only when the property has enough unique
|    content (description, photos, facilities, FAQs) to justify a page.
|
| Anything marked [NEED INFORMATION] must be supplied by the owner.
| (No closures in this file, so `php artisan config:cache` works.)
*/

return [

    'name'  => 'Sanjay & Harini Hostels',
    'url'   => rtrim(env('HOSTEL_SITE_URL', 'https://www.sanjayandharinihostels.com'), '/'),
    'email' => 'info@sanjayandharinihostels.com',

    // [NEED INFORMATION] Real main number / WhatsApp number.
    // Telephone is only added to schema once 'phone_verified' is true.
    'phone'          => '+919876543210',
    'phone_display'  => '+91 98765 43210',
    'whatsapp'       => '919876543210',
    'phone_verified' => false,

    // [NEED INFORMATION] Create public/images/og-image.jpg (1200x630).
    'og_image' => '/images/og-image.jpg',

    // [NEED INFORMATION] Real profile URLs (Instagram, Facebook, GBP...).
    'social' => [],

    // [NEED INFORMATION] Real date the policies were last reviewed,
    // e.g. '1 October 2026'.
    'policy_updated' => null,

    'show_testimonials'       => false,
    'photos_are_placeholders' => true,
    'faq_schema'              => false,

    'areas' => [
        'alandur' => [
            'name'  => 'Alandur',
            'route' => 'hostels.alandur',
            'path'  => '/hostels/alandur',
        ],
        'perungalathur' => [
            'name'  => 'Perungalathur',
            'route' => 'hostels.perungalathur',
            'path'  => '/hostels/perungalathur',
        ],
    ],

    'properties' => [

        'pudupettai-street-men' => [
            'area'   => 'alandur',
            'street' => 'Pudupettai Street',
            'gender' => 'men',
            'label'  => "Men's Hostel – Pudupettai Street",
            // [NEED INFORMATION] address, phone, maps_url, photos, rooms,
            // rent, amenities, exact Google Business Profile name.
        ],

        'pudupettai-street-women-1' => [
            'area'   => 'alandur',
            'street' => 'Pudupettai Street',
            'gender' => 'women',
            'label'  => "Women's Hostel – Pudupettai Street (Property 1)",
        ],

        'pudupettai-street-women-2' => [
            'area'   => 'alandur',
            'street' => 'Pudupettai Street',
            'gender' => 'women',
            'label'  => "Women's Hostel – Pudupettai Street (Property 2)",
        ],

        'mkn-road-men' => [
            'area'   => 'alandur',
            'street' => 'M.K.N. Road',
            'gender' => 'men',
            'label'  => "Men's Hostel – M.K.N. Road",
            'address' => [
                'street' => 'Behind Lalitha Thanga Maligai, M.K.N. Road, 19, S Raja St, Alandur',
                'city'   => 'Chennai',
                'region' => 'Tamil Nadu',
                'postal' => '600016',
            ],
            'address_confirmed' => true,
            // [NEED INFORMATION] phone, maps_url, photos, rooms, rent, amenities.
        ],

        'raja-street-women' => [
            'area'   => 'alandur',
            'street' => 'Raja Street',
            'gender' => 'women',
            'label'  => 'Ladies Hostel – Raja Street',
            // [NEED INFORMATION] address, phone, maps_url, photos, rooms, rent, amenities.
        ],

        'perungalathur-men' => [
            'area'   => 'perungalathur',
            'slug'   => 'men',
            'street' => 'Perungalathur',
            'gender' => 'men',
            'label'  => "Men's Hostel – Perungalathur",
            // PROVISIONAL — NOT shown or schematized until address_confirmed = true.
            'address' => [
                'street' => '13/1, Venkateswara Street, near Sekar Mahal, New Perungalathur',
                'city'   => 'Chennai',
                'region' => 'Tamil Nadu',
                'postal' => '600063',
            ],
            'address_confirmed' => false,
            // [NEED INFORMATION] confirm address, phone, maps_url, photos, rooms, rent, amenities.
        ],
    ],
];
