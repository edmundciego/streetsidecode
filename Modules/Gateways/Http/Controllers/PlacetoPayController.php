<?php

namespace Modules\Gateways\Http\Controllers;

use App\CentralLogics\Helpers;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Str;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Entities\PaymentRequest;
use Modules\Gateways\Traits\Processor;

class PlacetoPayController extends Controller
{
    use Processor;

    private mixed $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $this->config_values = $this->getPlacetoPayConfig(); // Use Processor trait method for configurations
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
        $seed = date('c');
        $rawNonce = random_bytes(16);
        $nonce = base64_encode($rawNonce);
        $tranKey = base64_encode(hash('sha256', $rawNonce . $seed . $api_key, true));
        $expiration = date('c', strtotime('+10 minutes'));

        $url = env('PLACETOPAY_BASE_URL'); // Use environment variable

        $data = [
            "locale" => "en_US",
            "auth" => [
                "login" => $merchant_id,
                "tranKey" => $tranKey,
                "nonce" => $nonce,
                "seed" => $seed,
            ],
            "payment" => [
                "reference" => $ref,
                "description" => 'payment',
                "amount" => [
                    "currency" => $payment_data->currency_code,
                    "total" => $payment_data->payment_amount,
                ]
            ],
            "expiration" => $expiration,
            "returnUrl" => route('placetoPay.callback'),
            "skipResult" => true,
            "ipAddress" => $request->ip(),
            "userAgent" => $request->userAgent()
        ];

        $response = $this->makePlacetoPayRequest($url, $data);

        if (isset($response['processUrl'])) {
            return redirect()->to($response['processUrl']);
        }
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }

    public function callback(Request $request)
    {
        Log::info('Callback route hit', $request->all());

        $requestId = $request->input('requestId');
        if (!$requestId) {
            Log::error("Callback received without requestId.");
            return redirect()->route('payment-fail');
        }

        $config = $this->config_values;
        $url = env('PLACETOPAY_BASE_URL') . "/$requestId"; // Use environment variable

        $status = $this->checkPlacetoPayStatus($url);

        if ($status === 'APPROVED') {
            $this->payment::where(['transaction_id' => $requestId])->update([
                'payment_method' => 'placetoPay',
                'is_paid' => 1,
                'transaction_id' => $requestId,
            ]);
            return $this->payment_response($payment_data, 'success');
        }
        return $this->payment_response($payment_data, 'fail');
    }

    public function webhook(Request $request)
    {
        Log::info('Webhook route hit', $request->all());

        $requestId = $request->input('requestId');
        $statusData = $request->input('status');

        if (!$requestId || !$statusData) {
            Log::error("Webhook received without required data: requestId or status missing.");
            return response()->json(['error' => 'Invalid webhook data'], 400);
        }

        $status = $statusData['status'] ?? null;

        $payment_data = $this->payment::where(['transaction_id' => $requestId])->first();
        if (!$payment_data) {
            Log::error("Payment not found for requestId: $requestId");
            return response()->json(['error' => 'Payment not found'], 404);
        }

        if ($status === 'APPROVED') {
            $payment_data->update([
                'is_paid' => 1,
                'transaction_id' => $requestId,
                'payment_method' => 'placetoPay',
            ]);
            if (function_exists($payment_data->success_hook)) {
                call_user_func($payment_data->success_hook, $statusData);
            }
        } else {
            if (function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
        }

        Log::info("Webhook processed successfully for requestId: $requestId");
        return response()->json(['message' => 'Webhook received successfully'], 200);
    }

    private function makePlacetoPayRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function checkPlacetoPayStatus($url)
    {
        $config = $this->config_values;
        $seed = date('c');
        $rawNonce = random_bytes(16);
        $nonce = base64_encode($rawNonce);
        $tranKey = base64_encode(hash('sha256', $rawNonce . $seed . $config['api_key'], true));

        $data = [
            "auth" => [
                "login" => $config['merchant_id'],
                "tranKey" => $tranKey,
                "nonce" => $nonce,
                "seed" => $seed,
            ]
        ];

        $response = $this->makePlacetoPayRequest($url, $data);
        return $response['status']['status'] ?? null;
    }
}
