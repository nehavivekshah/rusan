<?php
    // Replace these with your actual WhatsApp Business API credentials
    $accessToken = "YOUR_ACCESS_TOKEN";       // Your permanent or temporary access token
    $phoneNumberId = "YOUR_PHONE_NUMBER_ID";  // Your approved Phone Number ID from WhatsApp Business API
    
    header('Content-Type: application/json; charset=UTF-8');
    
    // Read the JSON input from the Android client
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    if (!$input || !isset($input['recipient_phone']) || !isset($input['message_text'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }
    
    $recipientPhone = $input['recipient_phone']; // e.g. +1234567890
    $message = $input['message_text'];
    
    $url = "https://graph.facebook.com/v17.0/$phoneNumberId/messages";
    $payload = [
        "messaging_product" => "whatsapp",
        "to" => $recipientPhone,
        "type" => "text",
        "text" => ["body" => $message]
    ];
    
    $headers = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "cURL Error: " . curl_error($ch)
        ]);
        curl_close($ch);
        exit;
    }
    
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode([
            "success" => true,
            "message" => "Message sent successfully",
            "response" => json_decode($response, true)
        ]);
    } else {
        http_response_code($httpCode);
        echo json_encode([
            "success" => false,
            "message" => "Failed to send message",
            "response" => json_decode($response, true)
        ]);
    }
