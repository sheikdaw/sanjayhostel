<?php

namespace App\Http\Controllers;

use App\Support\Hostels;
use App\Support\Seo;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    public function index()
    {
        // Public, indexable pages only. No admin, login, face-registration or API URLs.
        $paths = ['/', '/hostels', '/hostels/alandur', '/hostels/perungalathur'];

        foreach (['rooms', 'gallery', 'about', 'contact'] as $name) {
            if (Route::has($name)) {
                $paths[] = route($name, [], false);
            }
        }

        // Property pages are added automatically once has_page = true.
        foreach (Hostels::published() as $p) {
            $paths[] = Hostels::path($p);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach (array_unique($paths) as $path) {
            $xml .= '  <url><loc>'
                  . htmlspecialchars(Seo::url($path), ENT_XML1)
                  . '</loc></url>' . "\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8'])
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
