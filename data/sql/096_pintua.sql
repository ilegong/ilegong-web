ALTER TABLE `cake_orders`
ADD COLUMN `group_id` INT NOT NULL DEFAULT 0 AFTER `relate_type`;

CREATE TABLE `cake_pintuan_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `share_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `expire_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(11) NOT NULL DEFAULT '0',
  `deleted` int(11) NOT NULL DEFAULT '0',
  `num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


CREATE TABLE `cake_pintuan_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

ALTER TABLE `cake_pintuan_tags`
ADD COLUMN `pid` INT NOT NULL DEFAULT 1 AFTER `num`;

CREATE TABLE `cake_data_collects` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `data_type` INT NOT NULL DEFAULT 0,
  `data_id` INT NOT NULL DEFAULT 0,
  `count` INT NOT NULL DEFAULT 0,
  `plus_count` INT NOT NULL DEFAULT 0,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));



