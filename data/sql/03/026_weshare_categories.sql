CREATE TABLE `51daifan`.`cake_weshare_tags` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `created` DATETIME NOT NULL,
  `deleted` TINYINT(2) NOT NULL DEFAULT 0,
  `user_id` BIGINT NOT NULL,
  PRIMARY KEY (`id`));