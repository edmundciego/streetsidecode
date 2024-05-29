-- Insert PlaceToPay settings
INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`)
VALUES
('0d8a9308-d6aa-11ed-962c-0c7a158e4469', 'placetoPay', '{"gateway":"placetoPay","mode":"live","status":"1","merchant_id":"your_live_merchant_id","api_key":"your_live_api_key"}', '{"gateway":"placetoPay","mode":"test","status":"1","merchant_id":"your_test_merchant_id","api_key":"your_test_api_key"}', 'payment_config', 'test', 1, NULL, '2023-04-12 03:37:50', '{"gateway_title":"3244","gateway_image":"2023-04-12-64367be3b7b6a.png"}')
ON DUPLICATE KEY UPDATE
`key_name` = VALUES(`key_name`),
`live_values` = VALUES(`live_values`),
`test_values` = VALUES(`test_values`),
`settings_type` = VALUES(`settings_type`),
`mode` = VALUES(`mode`),
`is_active` = VALUES(`is_active`),
`created_at` = VALUES(`created_at`),
`updated_at` = VALUES(`updated_at`),
`additional_data` = VALUES(`additional_data`);

-- Insert DigiWallet settings
INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`)
VALUES
('0d8a9308-d6aa-11ed-962c-0c7a158e4470', 'digiWallet', '{"gateway":"digiWallet","mode":"live","status":"1","merchant_id":"your_live_merchant_id","api_key":"your_live_api_key"}', '{"gateway":"digiWallet","mode":"test","status":"1","merchant_id":"your_test_merchant_id","api_key":"your_test_api_key"}', 'payment_config', 'test', 1, NULL, '2023-04-12 03:37:50', '{"gateway_title":"DigiWallet","gateway_image":"digiwallet_logo.png"}')
ON DUPLICATE KEY UPDATE
`key_name` = VALUES(`key_name`),
`live_values` = VALUES(`live_values`),
`test_values` = VALUES(`test_values`),
`settings_type` = VALUES(`settings_type`),
`mode` = VALUES(`mode`),
`is_active` = VALUES(`is_active`),
`created_at` = VALUES(`created_at`),
`updated_at` = VALUES(`updated_at`),
`additional_data` = VALUES(`additional_data`);
