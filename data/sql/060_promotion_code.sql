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

