ALTER TABLE `cake_tuan_teams`
ADD COLUMN `principal` VARCHAR(45) NULL AFTER `type`,
ADD COLUMN `principal_phone` VARCHAR(45) NULL AFTER `principal`;

ALTER TABLE `cake_tuan_teams`
ADD COLUMN `remark` VARCHAR(200) NULL AFTER `principal_phone`;

