ALTER TABLE `cake_product_ship_settings`
ADD COLUMN `least_num` INT(11) NOT NULL DEFAULT 0 AFTER `data_type`;
