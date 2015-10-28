CREATE TABLE `cake_share_operate_settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `data_id` INT NOT NULL DEFAULT 0,
  `data_type` VARCHAR(45) NOT NULL,
  `user` INT NOT NULL DEFAULT 0,
  `deleted` TINYINT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));


-- 添加权限使用的范围

ALTER TABLE `cake_share_operate_settings`
ADD COLUMN `scope_id` INT NOT NULL AFTER `user`,
ADD COLUMN `scope_type` VARCHAR(45) NOT NULL AFTER `scope_id`;

