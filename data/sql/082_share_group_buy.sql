ALTER TABLE `cake_weshare_ship_settings`
ADD COLUMN `limit` INT NOT NULL DEFAULT 0 AFTER `tag`,
ADD COLUMN `price` INT NOT NULL DEFAULT -1 AFTER `limit`;

ALTER TABLE `cake_weshares`
ADD COLUMN `type` INT(2) NOT NULL DEFAULT 0 AFTER `settlement`;

ALTER TABLE `cake_weshares`
ADD COLUMN `refer_share_id` INT NOT NULL DEFAULT 0 AFTER `type`;

ALTER TABLE `cake_orders`
ADD COLUMN `relate_type` INT(2) NOT NULL DEFAULT 0 AFTER `process_prepaid_status`;
