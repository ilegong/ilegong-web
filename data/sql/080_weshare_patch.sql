ALTER TABLE `cake_weshare_products`
ADD COLUMN `tbd` TINYINT NOT NULL DEFAULT 0 AFTER `limit`;

ALTER TABLE `cake_orders`
ADD COLUMN `parent_order_id` INT NOT NULL DEFAULT 0 AFTER `remark_address`;

ALTER TABLE `cake_orders`
ADD COLUMN `price_difference` INT NOT NULL DEFAULT 0 AFTER `parent_order_id`;

ALTER TABLE `cake_orders`
ADD COLUMN `is_prepaid` TINYINT NOT NULL DEFAULT 0 AFTER `price_difference`;
