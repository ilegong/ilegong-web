ALTER TABLE `cake_sharer_ship_options`
CHANGE COLUMN `type` `status` INT(2) NOT NULL DEFAULT '0' ,
ADD COLUMN `ship_option` INT NOT NULL DEFAULT 1 AFTER `status`;

