<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledEmail;
use Carbon\Carbon;

class EmailTrackingController extends Controller
{
    /**
     * Track email opens using a 1x1 transparent GIF.
     */
    public function trackOpen($token)
    {
        $email = ScheduledEmail::where('tracking_token', $token)->first();
        
        if ($email && !$email->opened_at) {
            $email->opened_at = Carbon::now();
            $email->save();
        }

        // Return a 1x1 transparent GIF
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($img)->header('Content-Type', 'image/gif');
    }

    /**
     * Track email clicks and redirect to the target URL.
     */
    public function trackClick(Request $request, $token)
    {
        $url = $request->query('url');
        
        if (!$url) {
            abort(404);
        }

        $email = ScheduledEmail::where('tracking_token', $token)->first();
        
        if ($email && !$email->clicked_at) {
            $email->clicked_at = Carbon::now();
            $email->save();
        }

        return redirect($url);
    }
}
