<?php

namespace App\Services;

use App\Models\SmtpSettings;
use App\Mail\CustomMailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;

class BaseService
{
    /**
     * Discover SMTP settings with fallback from user to company level.
     */
    protected function getSmtpSettings($userId = null, $companyId = null)
    {
        $userId = $userId ?: Auth::id();
        $companyId = $companyId ?: Auth::user()->cid;

        // 1. Try to find user-specific settings
        $settings = SmtpSettings::where('user_id', $userId)->first();

        // 2. Fallback to company-specific settings
        if (!$settings && $companyId) {
            $settings = SmtpSettings::where('cid', $companyId)->first();
        }

        return $settings;
    }

    /**
     * Dynamically apply SMTP settings to the Laravel mailer at runtime.
     */
    protected function applySmtpSettings($settings)
    {
        if (!$settings) {
            return;
        }

        try {
            $password = Crypt::decryptString($settings->password);
        } catch (\Exception $e) {
            // Fallback if password is not encrypted or decryption fails
            $password = $settings->password;
        }

        Config::set('mail.mailers.smtp.host', $settings->host);
        Config::set('mail.mailers.smtp.port', $settings->port);
        Config::set('mail.mailers.smtp.encryption', $settings->encryption);
        Config::set('mail.mailers.smtp.username', $settings->username);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name);

        // Force Laravel to forget the previous mailer instance so it picks up the new config
        Mail::purge();
    }

    /**
     * Shared helper to send mail using CustomMailable with SMTP discovery.
     */
    public function sendMail($to, $subject, $viewName, $viewData, $userId = null, $companyId = null)
    {
        try {
            $settings = $this->getSmtpSettings($userId, $companyId);
            $this->applySmtpSettings($settings);

            $fromAddress = $settings?->from_address;
            $fromName = $settings?->from_name;

            // Generate tracking token
            $trackingToken = bin2hex(random_bytes(16));
            \App\Models\TrackedEmail::create([
                'cid' => $companyId ?: Auth::user()->cid,
                'recipient' => $to,
                'subject' => $subject,
                'tracking_token' => $trackingToken
            ]);

            $mailable = new CustomMailable(
                $subject,
                $viewName,
                $viewData,
                $fromAddress,
                $fromName,
                $trackingToken
            );

            Mail::to($to)->send($mailable);
            return true;
        } catch (\Exception $e) {
            \Log::error('SMTP Mail Error: ' . $e->getMessage());
            return false;
        }
    }
}
