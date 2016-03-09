ALTER TABLE `51daifan`.`cake_weshare_addresses`
ADD COLUMN `phone` VARCHAR(45) NOT NULL DEFAULT '' AFTER `address`,
ADD COLUMN `name` VARCHAR(45) NOT NULL DEFAULT '' AFTER `phone`;
