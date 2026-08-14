<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    /**
     * Show the contact form
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission
     */
    public function submit(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'interest' => 'required|string',
            'branch' => 'required|string',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            // Send email
            Mail::to('info@sanjayandharinihostels.com')->send(new ContactFormMail($validated));

            // Log successful submission
            Log::info('Contact form submitted successfully', ['email' => 'info@sanjayandharinihostels.com', 'name' => $validated['name']]);

            // Redirect with success message
            return redirect()->route('contact')
                ->with('success', 'Thank you! We will contact you shortly.');

        } catch (\Exception $e) {
            // Log error
            Log::error('Contact form submission failed: ' . $e->getMessage());

            // Redirect with error message
            return redirect()->route('contact')
                ->with('error', 'Something went wrong. Please try again or call us directly.');
        }
    }
}
