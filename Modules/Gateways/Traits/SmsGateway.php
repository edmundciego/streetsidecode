<?php

namespace Modules\Gateways\Traits;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;

trait SmsGateway
{
    public static function send($receiver, $message): string
    {
        $config = self::get_settings('httpsms');
        Log::info('SMS Gateway configuration', ['config' => $config]);

        // Validate configuration
        if (!self::validate_config($config)) {
            Log::error("Invalid SMS gateway configuration.", ['config' => $config]);
            return 'not_found';
        }

        // Validate phone number
        $receiver = self::format_phone_number($receiver);
        if (!$receiver) {
            Log::error("Invalid phone number format: $receiver");
            return 'invalid_number';
        }

        return self::httpsms($receiver, $message);
    }

    private static function validate_config($config): bool
    {
        return is_array($config) && isset($config['status']) && $config['status'] == 1 && isset($config['api_key']) && isset($config['from']);
    }

    private static function format_phone_number($phone_number): ?string
    {
        // Basic validation and formatting for the phone number
        // Ensure it starts with a '+' and contains only digits thereafter
        $phone_number = preg_replace('/[^0-9]/', '', $phone_number);
        if (strlen($phone_number) > 0) {
            return '+' . $phone_number;
        }
        return null;
    }

    public static function httpsms($receiver, $message): string
    {
        $config = self::get_settings('httpsms');
        $response = 'error';

        if (self::validate_config($config)) {
            try {
                $client = new HttpClient();
                $apiKey = $config['api_key'];
                $from = $config['from'];

                $result = $client->request('POST', 'https://api.httpsms.com/v1/messages/send', [
                    'headers' => [
                        'x-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'content' => $message,
                        'from' => $from,
                        'to' => $receiver
                    ]
                ]);

                Log::info("HTTP SMS sent to $receiver", ['response' => $result->getBody()]);

                $response = 'success';
            } catch (\Exception $exception) {
                Log::error("HTTP SMS error", ['message' => $exception->getMessage()]);
                $response = 'error';
            }
        } else {
            Log::error("HTTP SMS Error: Configuration is missing or disabled", ['config' => $config]);
        }

        return $response;
    }

    public static function get_settings($name)
    {
        $data = config_settings($name, 'sms_config');
        if (isset($data) && !is_null($data->live_values)) {
            Log::info('Raw live_values retrieved from database', ['live_values' => $data->live_values]);

            $live_values = $data->live_values;

            // Ensure $live_values is decoded correctly
            if (is_string($live_values)) {
                $live_values = json_decode($live_values, true);
                Log::info('Decoded live_values', ['live_values' => $live_values]);
            }

            if (is_array($live_values)) {
                return $live_values;
            } else {
                Log::error('Failed to decode live_values', ['live_values' => $data->live_values]);
            }
        } else {
            Log::error('No live_values found or live_values is null', ['data' => $data]);
        }
        return null;
    }
}
