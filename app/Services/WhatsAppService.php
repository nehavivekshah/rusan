<?php

namespace App\Services;

use App\Models\ThirdPartySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService extends BaseService
{
    public function sendMessage($to, $message, $link = null)
    {
        $settings = $this->getSettings();
        if (!$settings) return ['success' => false, 'message' => 'WhatsApp not configured.'];

        $url = "https://graph.facebook.com/v17.0/{$settings->account_sid}/messages";
        $body = $message . ($link ? "\n\n🔗 View attachment: " . $link : "");

        return $this->callApi($url, $settings->api_token, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $body]
        ]);
    }

    public function sendDocument($to, $docUrl, $filename, $caption = null)
    {
        $settings = $this->getSettings();
        if (!$settings) return ['success' => false, 'message' => 'WhatsApp not configured.'];

        $url = "https://graph.facebook.com/v17.0/{$settings->account_sid}/messages";
        
        return $this->callApi($url, $settings->api_token, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'link' => $docUrl,
                'filename' => $filename,
                'caption' => $caption
            ]
        ]);
    }

    public function sendImage($to, $imageUrl, $caption = null)
    {
        $settings = $this->getSettings();
        if (!$settings) return ['success' => false, 'message' => 'WhatsApp not configured.'];

        $url = "https://graph.facebook.com/v17.0/{$settings->account_sid}/messages";

        return $this->callApi($url, $settings->api_token, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl,
                'caption' => $caption
            ]
        ]);
    }

    protected function getSettings()
    {
        return ThirdPartySetting::where('provider', 'whatsapp')->where('status', true)->first();
    }

    protected function callApi($url, $token, $payload)
    {
        try {
            $response = Http::withToken($token)->post($url, $payload);
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
