CREATE TABLE `cake_rebate_track_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `from` INT NOT NULL,
  `to` INT NOT NULL,
  `is_paid` TINYINT NOT NULL DEFAULT 0,
  `order_id` INT NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`));
