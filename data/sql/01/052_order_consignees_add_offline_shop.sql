ALTER TABLE `cake_order_consignees`
ADD COLUMN `ziti_id` INT NULL DEFAULT NULL AFTER `town_id`,
ADD COLUMN `ziti_type` INT NULL AFTER `ziti_id`;
