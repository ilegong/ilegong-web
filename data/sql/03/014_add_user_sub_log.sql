CREATE TABLE `51daifan`.`cake_user_sub_logs` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `follow_id` INT NOT NULL,
  `created` DATETIME NOT NULL,
  `type` VARCHAR(45) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));
