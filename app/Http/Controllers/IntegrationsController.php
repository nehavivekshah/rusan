<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThirdPartySetting;
use Illuminate\Support\Facades\Auth;

class IntegrationsController extends Controller
{
    public function index()
    {
        $settings = ThirdPartySetting::all()->groupBy('provider');
        return view('integrations.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $provider = $request->provider;
        
        ThirdPartySetting::updateOrCreate(
            ['cid' => Auth::user()->cid, 'provider' => $provider],
            [
                'account_sid' => $request->account_sid,
                'api_key' => $request->api_key,
                'api_token' => $request->api_token,
                'from_number' => $request->from_number,
                'status' => $request->has('status') ? 1 : 0,
            ]
        );

        return back()->with('success', ucfirst($provider) . ' settings updated successfully.');
    }
}
