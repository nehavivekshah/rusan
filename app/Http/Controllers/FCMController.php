<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Http\Request;

class FCMController extends Controller
{
    protected $messaging;

    public function __construct()
    {
        try {

            $factory = (new Factory)
                ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

            $this->messaging = $factory->createMessaging();

        } catch (\Exception $e) {
            // Log initialization errors
            \Log::error('Firebase Initialization Error: ' . $e->getMessage());
            abort(500, 'Unable to initialize Firebase');
        }
    }

    public function sendNotification(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'device_token' => 'required|string', // The FCM device token
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $deviceToken = $request->input('device_token');
        $title = $request->input('title');
        $body = $request->input('body');

        // Create the notification message
        $message = CloudMessage::fromArray([
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ]);

        try {
            // Send the notification message
            $report = $this->messaging->send($message);

            \Log::info('FCM Message Sent Success', ['report' => $report, 'token' => $deviceToken]);

            return response()->json(['status' => 'Message sent successfully', 'report' => $report], 200);
        } catch (\Exception $e) {
            // Log any errors
            \Log::error('FCM Send Error', [
                'message' => $e->getMessage(),
                'token' => $deviceToken,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Unable to send notification: ' . $e->getMessage()], 500);
        }
    }
}
