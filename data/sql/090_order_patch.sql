ALTER TABLE `cake_orders`
ADD COLUMN `ship_type_name` VARCHAR(64) NULL AFTER `total_price`;
