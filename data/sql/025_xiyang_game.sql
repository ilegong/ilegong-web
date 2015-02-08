CREATE TABLE `51daifan`.`cake_follow_other_account_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uid` INT NULL,
  `wx_account` VARCHAR(45) NULL,
  `status` INT NULL DEFAULT 1,
  `created` DATETIME NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `51daifan`.`cake_follow_other_account_logs`
ADD COLUMN `follow_token` VARCHAR(100) NULL AFTER `created`;

ALTER TABLE `51daifan`.`cake_follow_other_account_logs`
ADD COLUMN `from` VARCHAR(45) NULL AFTER `follow_token`;

ALTER TABLE `51daifan`.`cake_follow_other_account_logs`
ADD COLUMN `updated` DATETIME NULL AFTER `from`;


ALTER TABLE `51daifan`.`cake_award_weixin_time_logs`
ADD COLUMN `from` VARCHAR(45) NULL AFTER `modified`;


