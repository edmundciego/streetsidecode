<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Gateways\Traits\SmsGateway;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use Modules\Gateways\Entities\Setting;

class SMSConfigController extends Controller
{
    use SmsGateway;

    private $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    public function sms_config_get()
    {
        $data_values = $this->setting->where('settings_type', 'sms_config')
                                     ->whereIn('key_name', ['twilio', 'nexmo', '2factor', 'msg91', 'httpsms'])
                                     ->get();
        return view('Gateways::sms-config.sms-config', compact('data_values'));
    }

    public function sms_config_set(Request $request)
    {
        $validation = [
            'gateway' => 'required|in:httpsms,twilio,nexmo,2factor,msg91',
            'mode' => 'required|in:live,test',
            'status' => 'required|in:1,0',
            'api_key' => 'required',
            'from' => 'required'
        ];

        $validatedData = $request->validate($validation);

        // Save the configuration without double-encoding it
        $this->setting->updateOrCreate(
            ['key_name' => $request['gateway'], 'settings_type' => 'sms_config'],
            [
                'key_name' => $request['gateway'],
                'live_values' => $validatedData,
                'test_values' => $validatedData,
                'settings_type' => 'sms_config',
                'mode' => $request['mode'],
                'is_active' => $request['status'],
            ]
        );

        Toastr::success('Configuration updated successfully.');
        return back();
    }

    public function send_test_sms(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
        ]);

        $phone_number = $request->input('phone_number');
        $message = 'This is a test SMS. If you received this, the configuration works!';

        // Retrieve the configuration for the active SMS gateway
        $gateway = $this->setting->where('settings_type', 'sms_config')
                                 ->where('is_active', 1)
                                 ->first();

        if (!$gateway) {
            Log::error('No active SMS gateway configuration found.');
            Toastr::error('No active SMS gateway configuration found.');
            return back();
        }

        $config = is_string($gateway->live_values) ? json_decode($gateway->live_values, true) : $gateway->live_values;
        Log::info('Retrieved gateway configuration', ['config' => $config]);

        if (!isset($config['from'])) {
            Log::error('HTTP SMS Error: "from" key is not set in the configuration');
            Toastr::error('The "from" field is required in the SMS gateway configuration.');
            return back();
        }

        try {
            Log::info("Sending test SMS to $phone_number");
            $result = SmsGateway::send($phone_number, $message);
            Log::info("Test SMS result for $phone_number", ['result' => $result]);

            if ($result == 'success') {
                Toastr::success('Test SMS sent successfully.');
            } else {
                Toastr::error('Failed to send Test SMS. Result: ' . $result);
            }
        } catch (\Exception $exception) {
            Log::error("Failed to send SMS to $phone_number", ['message' => $exception->getMessage()]);
            Toastr::error('An error occurred while sending the Test SMS.');
        }

        return back();
    }
}
