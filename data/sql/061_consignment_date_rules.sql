CREATE TABLE `cake_consignment_date_rules` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `before_days` INT NOT NULL,
  `week_days` VARCHAR(45) NOT NULL,
  `deleted` TINYINT NULL DEFAULT 0,
  PRIMARY KEY (`id`));

ALTER TABLE `cake_consignment_date_rules`
CHANGE COLUMN `deleted` `deleted` TINYINT(4) NOT NULL DEFAULT '0' ,
ADD COLUMN `cut_time` VARCHAR(45) NOT NULL AFTER `deleted`;
