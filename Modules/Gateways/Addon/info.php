<?php return array (
  'software_id' => '48481246',
  'name' => 'Payment & Sms gateways',
  'is_published' => 1,
  'database_migrated' => 0,
  'purchase_code' => 'placetopay',
  'username' => 'placetopay',
  'class_files_updated' => 2,
  'migrations' => 
  array (
    0 => 
    array (
      'key' => 'update_0001.sql',
      'value' => 1,
      'key_names' => 
      array (
        0 => 'placetoPay',
      ),
      'settings_type' => 'payment_config',
    ),
    1 => 
    array (
      'key' => 'update_0002.sql',
      'value' => 1,
      'key_names' => 
      array (
        0 => 'sms',
      ),
      'settings_type' => 'sms_config',
    ),
    2 => 
    array (
      'key' => 'update_0003.sql',
      'value' => 1,
      'key_names' => 
      array (
        0 => 'placetoPay',
        1 => 'digiWallet',
      ),
      'settings_type' => 'payment_config',
    ),
  ),
);