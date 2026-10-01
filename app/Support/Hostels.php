<?php

namespace App\Support;

/**
 * Reads config('hostel.properties') and fills in safe defaults.
 * Never invents data: missing values stay null/empty and the views skip them.
 */
class Hostels
{
    protected static array $defaults = [
        'slug'              => null,
        'name'              => null,
        'summary'           => null,
        'description'       => [],
        'address'           => null,
        'address_confirmed' => false,
        'phone'             => null,
        'phone_verified'    => false,
        'maps_url'          => null,
        'maps_embed'        => null,
        'geo'               => null,
        'rooms'             => [],
        'rent'              => null,
        'amenities'         => [],
        'photos'            => [],
        'faqs'              => [],
        'has_page'          => false,
    ];

    public static function all(): array
    {
        $out = [];
        foreach (config('hostel.properties', []) as $key => $p) {
            $p = array_merge(self::$defaults, $p, ['key' => $key]);
            $p['slug'] = $p['slug'] ?: $key;
            $out[$key] = $p;
        }
        return $out;
    }

    public static function forArea(string $area): array
    {
        return array_filter(self::all(), fn ($p) => $p['area'] === $area);
    }

    public static function published(): array
    {
        return array_filter(self::all(), fn ($p) => $p['has_page']);
    }

    public static function find(string $area, string $slug): ?array
    {
        foreach (self::published() as $p) {
            if ($p['area'] === $area && $p['slug'] === $slug) {
                return $p;
            }
        }
        return null;
    }

    public static function areaName(array $p): string
    {
        return config("hostel.areas.{$p['area']}.name", ucfirst($p['area']));
    }

    /** Path of the page that represents this property. */
    public static function path(array $p): string
    {
        return $p['has_page']
            ? "/hostels/{$p['area']}/{$p['slug']}"
            : config("hostel.areas.{$p['area']}.path");
    }

    /** Full address line, only when the owner has confirmed it. */
    public static function addressLine(array $p): ?string
    {
        if (!$p['address_confirmed'] || empty($p['address'])) {
            return null;
        }
        $a = $p['address'];
        return trim(implode(', ', array_filter([
            $a['street'] ?? null,
            $a['city'] ?? null,
            trim(($a['region'] ?? '') . ' ' . ($a['postal'] ?? '')),
        ])));
    }

    public static function mapsLink(array $p): ?string
    {
        if ($p['maps_url']) {
            return $p['maps_url'];
        }
        $line = self::addressLine($p);
        return $line
            ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($line)
            : null;
    }

    public static function mapsEmbed(array $p): ?string
    {
        if ($p['maps_embed']) {
            return $p['maps_embed'];
        }
        $line = self::addressLine($p);
        return $line
            ? 'https://www.google.com/maps?q=' . rawurlencode($line) . '&output=embed'
            : null;
    }

    public static function genderLabel(array $p): string
    {
        return $p['gender'] === 'women' ? "Women's hostel" : "Men's hostel";
    }
}
