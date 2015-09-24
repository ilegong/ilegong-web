ALTER TABLE `cake_weshare_addresses`
ADD COLUMN `deleted` TINYINT(4) NOT NULL DEFAULT 0 AFTER `address`;
