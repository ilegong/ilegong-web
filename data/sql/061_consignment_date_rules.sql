CREATE TABLE `cake_product_consignment_dates` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `deadline_day` INT NOT NULL DEFAULT 3,
  `deadline_time` VARCHAR(45) NOT NULL DEFAULT '19:00:00',
  `week_days` VARCHAR(45) NOT NULL DEFAULT '2,4,6',
  `deleted` TINYINT(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));