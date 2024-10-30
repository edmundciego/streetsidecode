<?php

namespace Modules\Gateways\Helpers;

use Illuminate\Support\Facades\Http;

class SMSHelper
{
    public static function sendSMS($to, $message, $config)
    {
        $apiKey = $config['api_key'];
        $senderId = $config['sender_id'];

        $response = Http::post('https://sms_provider_url/api/send', [
            'apiKey' => $apiKey,
            'senderId' => $senderId,
            'to' => $to,
            'message' => $message
        ]);

        return $response->json();
    }
}
