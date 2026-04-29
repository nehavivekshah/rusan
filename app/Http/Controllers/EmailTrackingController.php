<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrackedEmail;
use Carbon\Carbon;

class EmailTrackingController extends Controller
{
    public function trackOpen($token)
    {
        $email = TrackedEmail::where('tracking_token', $token)->first();

        if ($email) {
            $email->increment('opens');
            $email->update(['last_open' => Carbon::now()]);
        }

        // Return a 1x1 transparent GIF
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($img)->header('Content-Type', 'image/gif');
    }

    public function trackClick(Request $request, $token)
    {
        $url = $request->query('url');
        $email = TrackedEmail::where('tracking_token', $token)->first();

        if ($email) {
            $email->increment('clicks');
            $email->update(['last_click' => Carbon::now()]);
        }

        return redirect($url ?? '/');
    }
}
