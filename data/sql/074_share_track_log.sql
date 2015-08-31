CREATE TABLE `cake_rebate_track_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sharer` int(11) NOT NULL,
  `clicker` int(11) NOT NULL,
  `is_paid` tinyint(4) NOT NULL DEFAULT '0',
  `is_rebate` tinyint(4) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
