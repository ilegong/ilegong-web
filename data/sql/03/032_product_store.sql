ALTER TABLE `51daifan`.`cake_weshare_products`
ADD COLUMN `sell_num` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `weight`;

ALTER TABLE `51daifan`.`cake_weshare_products`
ADD COLUMN `left_num` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `sell_num`;

ALTER TABLE `51daifan`.`cake_weshare_products`
CHANGE COLUMN `left_num` `left_num` INT(10) UNSIGNED NULL DEFAULT NULL ;
