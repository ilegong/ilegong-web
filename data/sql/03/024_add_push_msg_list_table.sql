CREATE TABLE `51daifan`.`cake_push_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) NOT NULL,
  `type` INT(4) NOT NULL,
  `data_val` VARCHAR(128) NOT NULL,
  `published` TINYINT(2) NOT NULL DEFAULT 0,
  `deleted` TINYINT(2) NOT NULL DEFAULT 0,
  `push_time` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`));
