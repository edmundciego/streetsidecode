-- Add any new changes for DigiWallet
ALTER TABLE `addon_settings`
ADD COLUMN `new_column` VARCHAR(255) NULL AFTER `updated_at`;
