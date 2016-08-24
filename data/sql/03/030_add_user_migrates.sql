CREATE TABLE `51daifan`.`cake_user_migrates` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `new_user_id` BIGINT NOT NULL,
  `old_user_id` BIGINT NOT NULL,
  `created` DATETIME NOT NULL,
  `remark` VARCHAR(125) NOT NULL,
  PRIMARY KEY (`id`));
