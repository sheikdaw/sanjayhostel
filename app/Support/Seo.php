<?php

namespace App\Support;

/**
 * Builds valid JSON-LD. Each node is only emitted when the data behind it is
 * real: no ratings, no reviews, and no LocalBusiness without a confirmed
 * address. One physical property = one "Hostel" node. Addresses are never
 * combined.
 */
class Seo
{
    public static function url(string $path = '/'): string
    {
        return config('hostel.url') . '/' . ltrim($path, '/');
    }

    /** Wrap nodes in one <script type="application/ld+json"> @graph block. */
    public static function json(array $nodes): string
    {
        $nodes = array_values(array_filter($nodes));
        if (!$nodes) {
            return '';
        }
        $doc = ['@context' => 'https://schema.org', '@graph' => $nodes];
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
               | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT;
        return '<script type="application/ld+json">'
             . json_encode($doc, $flags)
             . '</script>';
    }

    public static function organization(): array
    {
        $n = [
            '@type' => 'Organization',
            '@id'   => self::url('/') . '#organization',
            'name'  => config('hostel.name'),
            'url'   => self::url('/'),
            'email' => config('hostel.email'),
        ];
        if (config('hostel.phone_verified')) {
            $n['telephone'] = config('hostel.phone');
        }
        if ($sameAs = array_values(array_filter(config('hostel.social', [])))) {
            $n['sameAs'] = $sameAs;
        }
        return $n;
    }

    public static function website(): array
    {
        return [
            '@type'      => 'WebSite',
            '@id'        => self::url('/') . '#website',
            'url'        => self::url('/'),
            'name'       => config('hostel.name'),
            'inLanguage' => 'en-IN',
            'publisher'  => ['@id' => self::url('/') . '#organization'],
        ];
    }

    public static function webPage(string $path, string $name, string $description): array
    {
        return [
            '@type'       => 'WebPage',
            '@id'         => self::url($path) . '#webpage',
            'url'         => self::url($path),
            'name'        => $name,
            'description' => $description,
            'inLanguage'  => 'en-IN',
            'isPartOf'    => ['@id' => self::url('/') . '#website'],
            'breadcrumb'  => ['@id' => self::url($path) . '#breadcrumb'],
        ];
    }

    /** @param array $crumbs list of [label, path] */
    public static function breadcrumbs(array $crumbs, string $path): array
    {
        $items = [];
        foreach (array_values($crumbs) as $i => [$label, $p]) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $label,
                'item'     => self::url($p),
            ];
        }
        return [
            '@type'           => 'BreadcrumbList',
            '@id'             => self::url($path) . '#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    /** Only call with FAQs that are visible on the same page. */
    public static function faq(array $faqs): ?array
    {
        if (!config('hostel.faq_schema') || !$faqs) {
            return null;
        }
        return [
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name'  => $f[0],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => strip_tags($f[1]),
                ],
            ], $faqs),
        ];
    }

    /** One physical property. Returns null unless its address is confirmed. */
    public static function hostel(array $p): ?array
    {
        $line = Hostels::addressLine($p);
        if (!$line) {
            return null;
        }
        $a    = $p['address'];
        $path = Hostels::path($p);

        $n = [
            '@type' => 'Hostel',
            '@id'   => self::url($path) . '#' . $p['key'],
            'name'  => $p['name'] ?: config('hostel.name') . ' – ' . $p['label'],
            'url'   => self::url($path),
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $a['street'],
                'addressLocality' => $a['city'],
                'addressRegion'   => $a['region'],
                'postalCode'      => $a['postal'],
                'addressCountry'  => 'IN',
            ],
            'hasMap' => Hostels::mapsLink($p),
            'parentOrganization' => ['@id' => self::url('/') . '#organization'],
        ];
        if ($p['summary']) {
            $n['description'] = $p['summary'];
        }
        if ($p['phone'] && $p['phone_verified']) {
            $n['telephone'] = $p['phone'];
        }
        if (!empty($p['geo']['lat']) && !empty($p['geo']['lng'])) {
            $n['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $p['geo']['lat'],
                'longitude' => $p['geo']['lng'],
            ];
        }
        if ($p['photos']) {
            $n['image'] = array_map(fn ($ph) => self::url($ph['src']), $p['photos']);
        }
        if ($p['amenities']) {
            $n['amenityFeature'] = array_map(fn ($x) => [
                '@type' => 'LocationFeatureSpecification',
                'name'  => $x,
                'value' => true,
            ], $p['amenities']);
        }
        return $n;
    }
}
