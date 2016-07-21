CREATE TABLE `51daifan`.`cake_rebate_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT NOT NULL,
  `reason` INT NOT NULL,
  `money` INT NOT NULL,
  `description` VARCHAR(512) NOT NULL,
  `order_id` INT NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`));


ALTER TABLE `51daifan`.`cake_users`
ADD COLUMN `rebate_money` INT(11) NOT NULL DEFAULT 0 AFTER `score`;

