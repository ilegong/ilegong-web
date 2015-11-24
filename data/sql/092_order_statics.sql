CREATE TABLE `cake_statistics_datas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_count` INT NOT NULL DEFAULT 0,
  `trading_volume` FLOAT NOT NULL DEFAULT 0,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`));
