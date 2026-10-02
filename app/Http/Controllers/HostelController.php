<?php

namespace App\Http\Controllers;

use App\Support\Hostels;

class HostelController extends Controller
{
    /**
     * Public hostels listing page.
     */
    public function index()
    {
        return view('hostels.index', [
            'areas'      => config('hostel.areas'),
            'properties' => Hostels::all(),
        ]);
    }

    /**
     * Public area page: Alandur.
     */
    public function alandur()
    {
        return view('hostels.alandur', [
            'properties' => Hostels::forArea('alandur'),
        ]);
    }

    /**
     * Public area page: Perungalathur.
     */
    public function perungalathur()
    {
        return view('hostels.perungalathur', [
            'properties' => Hostels::forArea('perungalathur'),
        ]);
    }

    /**
     * Individual property page — only when has_page = true in config.
     */
    public function property(string $area, string $slug)
    {
        $property = Hostels::find($area, $slug);
        abort_unless($property, 404);

        return view('hostels.property', ['property' => $property]);
    }
}
