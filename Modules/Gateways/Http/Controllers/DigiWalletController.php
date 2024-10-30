<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Entities\PaymentRequest;
use Modules\Gateways\Traits\Processor;

class DigiWalletController extends Controller
{
    use Processor;

    private mixed $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('digiWallet', 'payment_config');
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

        $url = ($config['mode'] == 'test') ? 'http://190.197.36.93:6060/Telepin' : 'http://190.197.36.93:6060/Telepin';
        $ch = curl_init();
        $data = [
            "Function" => "SALESREQUESTMERCHANT_OTP",
            "UserName" => $merchant_id,
            "Password" => $api_key,
            "Param1" => $config['brand_id'],
            "Param2" => $payment_data->payment_amount,
            "Param4" => $payment_data->source_account,
            "Param6" => $payment_data->destination_account
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
        $merchant_id = $config['merchant_id'];
        $api_key = $config['api_key'];

        $url = ($config['mode'] == 'test') ? "http://190.197.36.93:6060/Telepin/$requestId" : "http://190.197.36.93:6060/Telepin/$requestId";
        $ch = curl_init();
        $data = [
            "UserName" => $merchant_id,
            "Password" => $api_key,
            "Function" => "SALESREQUESTEXECTOSELF",
            "Param1" => $requestId
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

        $payment_data = $this->payment::where(['id' => $result['payment'][0]['reference']])->first();
        if (!isset($payment_data)) {
            return \redirect()->route('payment-fail');
        }
        if (isset($result['status']['status']) && $result['status']['status'] == 'APPROVED') {
            $this->payment::where(['id' => $result['payment'][0]['reference']])->update([
                'payment_method' => 'digiWallet',
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
        $merchant_id = $config['merchant_id'];
        $api_key = $config['api_key'];

        $url = ($config['mode'] == 'test') ? "http://190.197.36.93:6060/Telepin/$transactionId" : "http://190.197.36.93:6060/Telepin/$transactionId";
        $ch = curl_init();
        $data = [
            "UserName" => $merchant_id,
            "Password" => $api_key,
            "Function" => "SALESREQUESTEXECTOSELF",
            "Param1" => $transactionId
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
