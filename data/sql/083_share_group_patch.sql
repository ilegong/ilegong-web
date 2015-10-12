ALTER TABLE `cake_weshares`
ADD COLUMN `order_status` INT NOT NULL DEFAULT 0 AFTER `refer_share_id`;

ALTER TABLE `cake_weshare_offline_addresses`
DROP COLUMN `refer_share_id`,
DROP COLUMN `share_id`;

ALTER TABLE `cake_weshares`
ADD COLUMN `offline_address_id` INT NOT NULL DEFAULT 0 AFTER `order_status`;


