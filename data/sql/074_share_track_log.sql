CREATE TABLE `cake_rebate_track_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `share_id` int(11) NOT NULL,
  `sharer` int(11) NOT NULL,
  `clicker` int(11) NOT NULL,
  `is_paid` tinyint(4) NOT NULL DEFAULT '0',
  `is_rebate` tinyint(4) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `rebate_money` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- add proxy field
ALTER TABLE `cake_users`
ADD COLUMN `is_proxy` TINYINT(4) ZEROFILL NOT NULL DEFAULT 0 AFTER `payment`;

-- add proxy settings

CREATE TABLE `cake_proxy_rebate_percents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `share_id` int(11) NOT NULL DEFAULT '0',
  `percent` float NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





