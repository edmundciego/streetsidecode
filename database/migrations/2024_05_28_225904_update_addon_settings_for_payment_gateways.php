<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAddonSettingsForPaymentGateways extends Migration
{
    public function up()
    {
        // PlaceToPay settings
        DB::table('addon_settings')->updateOrInsert(
            ['id' => '0d8a9308-d6aa-11ed-962c-0c7a158e4469'],
            [
                'key_name' => 'placetoPay',
                'live_values' => '{"gateway":"placetoPay","mode":"live","status":"1","merchant_id":"your_live_merchant_id","api_key":"your_live_api_key"}',
                'test_values' => '{"gateway":"placetoPay","mode":"test","status":"1","merchant_id":"your_test_merchant_id","api_key":"your_test_api_key"}',
                'settings_type' => 'payment_config',
                'mode' => 'test',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'additional_data' => '{"gateway_title":"3244","gateway_image":"2023-04-12-64367be3b7b6a.png"}'
            ]
        );

        // DigiWallet settings
        DB::table('addon_settings')->updateOrInsert(
            ['id' => '0d8a9308-d6aa-11ed-962c-0c7a158e4470'],
            [
                'key_name' => 'digiWallet',
                'live_values' => '{"gateway":"digiWallet","mode":"live","status":"1","merchant_id":"your_live_merchant_id","api_key":"your_live_api_key"}',
                'test_values' => '{"gateway":"digiWallet","mode":"test","status":"1","merchant_id":"your_test_merchant_id","api_key":"your_test_api_key"}',
                'settings_type' => 'payment_config',
                'mode' => 'test',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'additional_data' => '{"gateway_title":"DigiWallet","gateway_image":"digiwallet_logo.png"}'
            ]
        );
    }

    public function down()
    {
        DB::table('addon_settings')->where('id', '0d8a9308-d6aa-11ed-962c-0c7a158e4469')->delete();
        DB::table('addon_settings')->where('id', '0d8a9308-d6aa-11ed-962c-0c7a158e4470')->delete();
    }
}