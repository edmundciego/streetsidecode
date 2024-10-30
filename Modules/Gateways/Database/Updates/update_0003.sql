-- Insert or update PlaceToPay settings
IF NOT EXISTS (SELECT 1 FROM `addon_settings` WHERE `key_name` = 'placetoPay')
THEN
    INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`)
    VALUES
    ('0d8a9308-d6aa-11ed-962c-0c7a158e4469', 'placetoPay', '{"gateway":"placetoPay","mode":"live","status":"1","merchant_id":"your_live_merchant_id","api_key":"your_live_api_key"}', '{"gateway":"placetoPay","mode":"test","status":"1","merchant_id":"your_test_merchant_id","api_key":"your_test_api_key"}', 'payment_config', 'test', 1, NULL, '2023-04-12 03:37:50', '{"gateway_title":"3244","gateway_image":"2023-04-12-64367be3b7b6a.png"}')
    ON DUPLICATE KEY UPDATE
    `live_values` = VALUES(`live_values`),
    `test_values` = VALUES(`test_values`),
    `settings_type` = VALUES(`settings_type`),
    `mode` = VALUES(`mode`),
    `is_active` = VALUES(`is_active`),
    `created_at` = VALUES(`created_at`),
    `updated_at` = VALUES(`updated_at`),
    `additional_data` = VALUES(`additional_data`);
END IF;

-- Insert or update DigiWallet settings
IF NOT EXISTS (SELECT 1 FROM `addon_settings` WHERE `key_name` = 'digiWallet')
THEN
    INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`)
    VALUES
    ('0d8a9308-d6aa-11ed-962c-0c7a158e4470', 'digiWallet', '{"gateway":"digiWallet","mode":"live","status":"1","merchant_id":"your_live_merchant_id","api_key":"your_live_api_key"}', '{"gateway":"digiWallet","mode":"test","status":"1","merchant_id":"your_test_merchant_id","api_key":"your_test_api_key"}', 'payment_config', 'test', 1, NULL, '2023-04-12 03:37:50', '{"gateway_title":"DigiWallet","gateway_image":"digiwallet_logo.png"}')
    ON DUPLICATE KEY UPDATE
    `live_values` = VALUES(`live_values`),
    `test_values` = VALUES(`test_values`),
    `settings_type` = VALUES(`settings_type`),
    `mode` = VALUES(`mode`),
    `is_active` = VALUES(`is_active`),
    `created_at` = VALUES(`created_at`),
    `updated_at` = VALUES(`updated_at`),
    `additional_data` = VALUES(`additional_data`);
END IF;

-- Insert or update HTTPSMS settings
IF NOT EXISTS (SELECT 1 FROM `addon_settings` WHERE `key_name` = 'httpsms')
THEN
    INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`)
    VALUES
    ('0d8a9308-d6aa-11ed-962c-0c7a158e4480', 'httpsms', '{"gateway":"httpsms","mode":"live","status":"1","username":"your_live_username","password":"your_live_password","sender_id":"your_live_sender_id"}', '{"gateway":"httpsms","mode":"test","status":"1","username":"your_test_username","password":"your_test_password","sender_id":"your_test_sender_id"}', 'sms_config', 'test', 1, NULL, '2023-04-12 03:37:50', '{"gateway_title":"HTTPSMS","gateway_image":"httpsms_logo.png"}')
    ON DUPLICATE KEY UPDATE
    `live_values` = VALUES(`live_values`),
    `test_values` = VALUES(`test_values`),
    `settings_type` = VALUES(`settings_type`),
    `mode` = VALUES(`mode`),
    `is_active` = VALUES(`is_active`),
    `created_at` = VALUES(`created_at`),
    `updated_at` = VALUES(`updated_at`),
    `additional_data` = VALUES(`additional_data`);
END IF;

-- Insert or update OneLink settings
IF NOT EXISTS (SELECT 1 FROM `addon_settings` WHERE `key_name` = 'oneLink')
THEN
    INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`)
    VALUES
    ('0d8a9308-d6aa-11ed-962c-0c7a158e4490', 'oneLink', '{"gateway":"oneLink","mode":"live","status":"1","token":"your_live_token","salt":"your_live_salt"}', '{"gateway":"oneLink","mode":"test","status":"1","token":"your_test_token","salt":"your_test_salt"}', 'payment_config', 'test', 1, NULL, '2023-04-12 03:37:50', '{"gateway_title":"OneLink","gateway_image":"onelink_logo.png"}')
    ON DUPLICATE KEY UPDATE
    `live_values` = VALUES(`live_values`),
    `test_values` = VALUES(`test_values`),
    `settings_type` = VALUES(`settings_type`),
    `mode` = VALUES(`mode`),
    `is_active` = VALUES(`is_active`),
    `created_at` = VALUES(`created_at`),
    `updated_at` = VALUES(`updated_at`),
    `additional_data` = VALUES(`additional_data`);
END IF;
