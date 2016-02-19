CREATE TABLE `cake_user_relations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL DEFAULT 0,
  `follow_id` INT NOT NULL DEFAULT 0,
  `type` VARCHAR(16) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));
