ALTER TABLE `cake_weshare_ship_settings`
ADD COLUMN `limit` INT NOT NULL DEFAULT 0 AFTER `tag`,
ADD COLUMN `price` INT NOT NULL DEFAULT -1 AFTER `limit`;
