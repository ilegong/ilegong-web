CREATE TABLE `cake_alipay_cache_forms` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(45) NULL,
  `form` LONGTEXT NULL,
  `created` DATETIME NULL,
  `status` INT NULL DEFAULT 1,
  `limit_time` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uuid_UNIQUE` (`uuid` ASC));

ALTER TABLE `51daifan`.`cake_alipay_cache_forms`
ADD COLUMN `order_id` INT NOT NULL AFTER `limit_time`;


