ALTER TABLE `cake_tuan_teams`
  ADD COLUMN `published` TINYINT(1) DEFAULT '1' NOT NULL;

ALTER TABLE `cake_tuan_teams`
  ADD COLUMN `offline_store_id` INT(11) UNSIGNED NULL AFTER `leader_weixin`;