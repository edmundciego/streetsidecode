<?php

namespace Modules\Gateways\Http\Controllers;

use App\CentralLogics\Helpers;
use App\Models\Order;
use Brian2694\Toastr\Facades\Toastr;
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
        $config = $this->payment_config('placetoPay', 'payment_config');
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
        $merchat_id = $config['merchant_id'];
        $api_key = $config['api_key'];
        $login = $merchat_id;
        $secretKey = $api_key;
        $seed = date('c');
        $rawNonce = rand();

        $tranKey = base64_encode(hash('sha256', $rawNonce . $seed . $secretKey, true));
        $nonce = base64_encode($rawNonce);
        $expirationTime = strtotime('+10 minutes', time());
        $expiration = date('c', $expirationTime);
        $url = ($config['mode'] == 'test') ? 'https://abgateway.atlabank.com/api/session' : 'https://abgateway.atlabank.com/api/session';
        $ch = curl_init();
        $data = [
            "locale" => "en_US",
            "auth" => [
                "login" => $login,
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
            "expiration" =>  $expiration,
            "returnUrl" => route('placetoPay.callback'),
            "skipResult" => true,
            "ipAddress" => $request->ip(),
            "userAgent" => $_SERVER['HTTP_USER_AGENT']
        ];
        $data_string = json_encode($data);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if (isset($result['processUrl'])) {
            return redirect()->to($result['processUrl']);
        }
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }

    public function callback(Request $request)
    {
        if(!$request->requestId){
            return redirect()->route('payment-fail');
        }
        $requestId = $request->requestId;

        $config = $this->config_values;
        $merchat_id = $config['merchant_id'];
        $api_key = $config['api_key'];
        $login = $merchat_id;
        $secretKey = $api_key;
        $seed = date('c');
        $rawNonce = rand();

        $tranKey = base64_encode(hash('sha256', $rawNonce . $seed . $secretKey, true));
        $nonce = base64_encode($rawNonce);
        $url = ($config['mode'] == 'test') ? "https://abgateway.atlabank.com/api/session/$requestId" : "https://abgateway.atlabank.com/api/session/$requestId";
        $ch = curl_init();
        $data = [
            "auth" => [
                "login" => $login,
                "tranKey" => $tranKey,
                "nonce" => $nonce,
                "seed" => $seed,
            ]
        ];
        $data_string = json_encode($data);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        // $order_id = substr($result['payment'][0]['reference'], 5);
        $payment_data = $this->payment::where(['id' => $result['payment'][0]['reference']])->first();
        if (!isset($payment_data)) {
            return \redirect()->route('payment-fail');
        }
        if (isset($result['status']['status']) && $result['status']['status'] == 'APPROVED') {
            $this->payment::where(['id' => $result['payment'][0]['reference']])->update([
                'payment_method' => 'placetoPay',
                'is_paid' => 1,
                'transaction_id' => $requestId,
            ]);

            if (isset($payment_data) && function_exists($payment_data->success_hook)) {
                call_user_func($payment_data->success_hook, $data);
            }

            return $this->payment_response($payment_data, 'success');
        }
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }

    public function checkStatus($transactionId)
    {
        $config = $this->config_values;
        $merchat_id = $config['merchant_id'];
        $api_key = $config['api_key'];
        $login = $merchat_id;
        $secretKey = $api_key;
        $seed = date('c');
        $rawNonce = rand();

        $tranKey = base64_encode(hash('sha256', $rawNonce . $seed . $secretKey, true));
        $nonce = base64_encode($rawNonce);
        $url = ($config['mode'] == 'test') ? "https://abgateway.atlabank.com/api/session/$transactionId" : "https://abgateway.atlabank.com/api/session/$transactionId";
        $ch = curl_init();
        $data = [
            "auth" => [
                "login" => $login,
                "tranKey" => $tranKey,
                "nonce" => $nonce,
                "seed" => $seed,
            ]
        ];
        $data_string = json_encode($data);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        if (isset($result['status']['status'])) {
            return $result['status']['status'];
        }

        return null;
    }

}
