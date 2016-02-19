CREATE TABLE `cake_follow_other_account_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uid` INT NULL,
  `wx_account` VARCHAR(45) NULL,
  `status` INT NULL DEFAULT 1,
  `created` DATETIME NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `cake_follow_other_account_logs`
ADD COLUMN `follow_token` VARCHAR(100) NULL AFTER `created`;

ALTER TABLE `cake_follow_other_account_logs`
ADD COLUMN `from` VARCHAR(45) NULL AFTER `follow_token`;

ALTER TABLE `cake_follow_other_account_logs`
ADD COLUMN `updated` DATETIME NULL AFTER `from`;


ALTER TABLE `cake_award_weixin_time_logs`
ADD COLUMN `from` VARCHAR(45) NULL AFTER `modified`;

ALTER TABLE `cake_award_weixin_time_logs`
DROP INDEX `award_info` ,
ADD UNIQUE INDEX `award_info` (`type` ASC, `uid` ASC, `from` ASC);

INSERT INTO `cake_coupons` (`id`, `name`, `brand_id`, `product_list`, `category_id`, `status`, `valid_begin`, `valid_end`, `published`, `last_updator`, `deleted`, `created`, `modified`, `reduced_price`, `type`, `least_price`) VALUES
(19069, '羊年游戏-58元券', 0, '816', 1, 1, '2015-02-10 00:00:00', '2015-03-31 23:59:59', 1, 632, 0, NULL, NULL, 5800, 2, 0),
(19070, '羊年游戏-158元券', 0, '816', 1, 1, '2015-02-10 00:00:00', '2015-02-14 23:59:59', 1, 632, 0, NULL, NULL, 15800, 2, 0);



