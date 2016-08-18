ALTER TABLE `51daifan`.`cake_weshare_products`
ADD COLUMN `left_store` INT(11) NOT NULL DEFAULT 0 AFTER `weight`,
ADD COLUMN `sell_count` INT(11) NOT NULL DEFAULT 0 AFTER `left_store`;
