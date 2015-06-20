CREATE TABLE `cake_promotion_codes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code_type` VARCHAR(45) NOT NULL DEFAULT '',
  `code` VARCHAR(100) NOT NULL,
  `gen_datetime` DATETIME NOT NULL,
  `available` TINYINT NOT NULL DEFAULT 1,
  `use_time` DATETIME NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `cake_promotion_codes`
ADD UNIQUE INDEX `code_UNIQUE` (`code` ASC);

ALTER TABLE `cake_promotion_codes`
ADD COLUMN `price` INT NOT NULL DEFAULT 0 AFTER `use_time`;

ALTER TABLE `cake_promotion_codes`
ADD COLUMN `product_id` INT NOT NULL AFTER `price`;


CREATE TABLE `cake_promotion_use_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `data_type` VARCHAR(45) NULL,
  `data_val` VARCHAR(45) NULL,
  `order_id` INT NULL,
  `user_id` INT NULL,
  `data_id` INT NULL,
  PRIMARY KEY (`id`));


