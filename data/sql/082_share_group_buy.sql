ALTER TABLE `cake_weshare_ship_settings`
ADD COLUMN `limit` INT NOT NULL DEFAULT 0 AFTER `tag`,
ADD COLUMN `price` INT NOT NULL DEFAULT -1 AFTER `limit`;

ALTER TABLE `cake_weshares`
ADD COLUMN `type` INT(2) NOT NULL DEFAULT 0 AFTER `settlement`;

ALTER TABLE `cake_weshares`
ADD COLUMN `refer_share_id` INT NOT NULL DEFAULT 0 AFTER `type`;

ALTER TABLE `cake_orders`
ADD COLUMN `relate_type` INT(2) NOT NULL DEFAULT 0 AFTER `process_prepaid_status`;

ALTER TABLE `cake_rebate_track_logs`
ADD COLUMN `type` INT NOT NULL DEFAULT 0 AFTER `updated`;

CREATE TABLE `cake_weshare_offline_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL,
  `share_id` int(11) NOT NULL,
  `refer_share_id` int(11) NOT NULL DEFAULT '0',
  `address` varchar(128) NOT NULL,
  `created` datetime NOT NULL,
  `remarks` varchar(256) DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;


