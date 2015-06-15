DROP TABLE IF EXISTS `cake_product_consignment_dates`;
CREATE TABLE `cake_product_consignment_dates` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `week_days` VARCHAR(45) NOT NULL DEFAULT '2,4,6',
  `deadline_day` INT NOT NULL DEFAULT 3,
  `deadline_time` VARCHAR(45) NOT NULL DEFAULT '19:00:00',
  `published` TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`));

ALTER TABLE `cake_product_consignment_dates`
  ADD UNIQUE INDEX `cake_product_consignment_dates_product_id` (`product_id` ASC);
