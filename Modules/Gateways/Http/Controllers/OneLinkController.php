<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Entities\PaymentRequest;
use Modules\Gateways\Traits\Processor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class OneLinkController extends Controller
{
    use Processor;

    private mixed $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('oneLink', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values, true);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values, true);
        }
        $this->payment = $payment;
    }

    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($payment_data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $ref = Str::random(5) . $payment_data->attribute_id;
        $payment_data->transaction_id = $ref;
        $payment_data->save();

        $config = $this->config_values;
        $merchant_id = $config['merchant_id'];
        $api_key = $config['api_key'];
        $token = $config['token'];
        $salt = $config['salt'];

        $url = 'https://api.onelink.bz/payment';
        $data = [
            "token" => $token,
            "salt" => $salt,
            "nameOnCard" => $payment_data->cardholder_name,
            "cardNumber" => $payment_data->card_number,
            "expirationDate" => $payment_data->card_expiry,
            "ccv" => $payment_data->card_cvv,
            "amount" => $payment_data->payment_amount,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        $result = $response->json();

        if ($result['msg'] === '51: INSUFF FUNDS') {
            return $this->payment_response($payment_data, 'fail');
        }

        return $this->payment_response($payment_data, 'success');
    }

    public function callback(Request $request)
    {
        // Handle callback logic if needed
    }

    public function checkStatus($transactionId)
    {
        $config = $this->config_values;
        $merchant_id = $config['merchant_id'];
        $api_key = $config['api_key'];

        $url = "https://api.onelink.bz/payment/$transactionId";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url);

        $result = $response->json();

        if (isset($result['status'])) {
            return $result['status'];
        }

        return null;
    }
}
