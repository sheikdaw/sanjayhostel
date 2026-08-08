<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceController extends Controller
{
    public function index()
    {
        return view('welcomeweb');
    }

    public function detect(Request $request)
    {
        $request->validate([
            'image' => 'required'
        ]);

        $image = $request->image;

        // Remove Base64 header
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        $imageName = 'faces/' . time() . '.jpg';

        Storage::disk('public')->put(
            $imageName,
            base64_decode($image)
        );

        return response()->json([
            'success' => true,
            'path' => asset('storage/' . $imageName)
        ]);
    }
}
