<?php

namespace App\Services;

use App\Models\ThirdPartySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExotelService extends BaseService
{
    public function initiateCall($to)
    {
        $settings = ThirdPartySetting::where('provider', 'exotel')
            ->where('status', true)
            ->first();

        if (!$settings) {
            return ['success' => false, 'message' => 'Exotel settings not configured.'];
        }

        $sid = $settings->account_sid;
        $apiKey = $settings->api_key;
        $apiToken = $settings->api_token;
        $from = $settings->from_number;

        $url = "https://api.exotel.com/v1/Accounts/{$sid}/Calls/connect.json";

        try {
            $response = Http::withBasicAuth($apiKey, $apiToken)
                ->asForm()
                ->post($url, [
                    'From' => $from,
                    'To' => $to,
                    'CallerId' => $from,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('Exotel Call Error: ' . $response->body());
            return ['success' => false, 'message' => 'Exotel API error: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('Exotel Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
