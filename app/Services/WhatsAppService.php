<?php

namespace App\Services;

use App\Models\ThirdPartySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService extends BaseService
{
    public function sendMessage($to, $message, $link = null)
    {
        $settings = ThirdPartySetting::where('provider', 'whatsapp')
            ->where('status', true)
            ->first();

        if (!$settings) {
            // Fallback to existing logic or log error
            return ['success' => false, 'message' => 'WhatsApp settings not configured.'];
        }

        $token = $settings->api_token;
        $phoneId = $settings->account_sid;

        $url = "https://graph.facebook.com/v17.0/{$phoneId}/messages";

        $body = $message;
        if ($link) {
            $body .= "\n\n🔗 View attachment: " . $link;
        }

        try {
            $response = Http::withToken($token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['body' => $body]
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WhatsApp Error: ' . $response->body());
            return ['success' => false, 'message' => 'WhatsApp API error: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('WhatsApp Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
