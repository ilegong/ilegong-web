CREATE TABLE `cake_tuan_teams` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(80) NOT NULL DEFAULT '',
  `leader_name` varchar(18) DEFAULT NULL,
  `tuan_name` varchar(30) NOT NULL DEFAULT '',
  `leader_id` int(11) NOT NULL DEFAULT '0',
  `leader_weixin` varchar(20) NOT NULL DEFAULT '0',
  `status` tinyint(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `location_long` double DEFAULT NULL,
  `location_lat` double DEFAULT NULL,
  `tuan_addr` varchar(255) DEFAULT NULL,
  `tuan_desc` varchar(255) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

CREATE TABLE `cake_tuan_buyings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tuan_id` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `join_num` int(11) DEFAULT NULL,
  `sold_num` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `end_time` datetime DEFAULT NULL,
  `consign_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE `cake_tuan_members` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tuan_id` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `join_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `cake_tuan_teams`
ADD COLUMN `member_num` INT NULL DEFAULT 1 AFTER `priority`;


ALTER TABLE `cake_orders`
ADD COLUMN `tuan_buying_id` INT NULL DEFAULT 0 AFTER `is_comment`;

ALTER TABLE `cake_tuan_buyings`
ADD COLUMN `max_num` INT(11) NULL DEFAULT 0 AFTER `target_num`;

ALTER TABLE `cake_tuan_buyings`
ADD COLUMN `tuan_price` FLOAT NULL DEFAULT -1 AFTER `max_num`;

ALTER TABLE `cake_tuan_buyings`
ADD COLUMN `limit_buy_num` INT NULL DEFAULT 0 AFTER `tuan_price`;

-- 微信模板消息记录日志
CREATE TABLE `cake_template_msg_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(60) NOT NULL,
  `flag` VARCHAR(60) NOT NULL,
  `send_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`));
-- 团购邮费设置
CREATE TABLE `cake_tuan_buy_ships` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tuan_buy_id` INT NOT NULL,
  `ship_name` VARCHAR(60) NOT NULL,
  `ship_fee` FLOAT NOT NULL DEFAULT 0,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));