<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::orderBy('created_at', 'desc')->get();
        return view('campaigns', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

        Campaign::create([
            'name' => $request->name,
            'type' => $request->type,
            'status' => 'Draft'
        ]);

        return back()->with('success', 'Campaign Draft Created Successfully.');
    }

    public function launch(Request $request)
    {
        $request->validate(['id' => 'required|exists:campaigns,id']);
        $campaign = Campaign::find($request->id);
        if ($campaign) {
            $campaign->status = 'Active';
            $campaign->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function destroy($id)
    {
        $campaign = Campaign::find($id);
        if ($campaign) {
            $campaign->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Campaign not found.'], 404);
    }
}
