CREATE TABLE `cake_statistics_order_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_user_buy_count` int(11) NOT NULL DEFAULT '0',
  `all_order_count` int(11) NOT NULL DEFAULT '0',
  `ziti_order_count` int(11) NOT NULL DEFAULT '0',
  `tuan_order_count` int(11) NOT NULL DEFAULT '0',
  `max_order_count` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `all_new_user_count` int(11) NOT NULL DEFAULT '0',
  `max_order_date` date NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `repeat_buy_count` int(11) NOT NULL DEFAULT '0',
  `all_buy_user_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

CREATE TABLE `cake_statistics_ziti_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_user_buy_count` int(11) NOT NULL DEFAULT '0',
  `repeat_buy_count` int(11) NOT NULL DEFAULT '0',
  `all_buy_user_count` int(11) NOT NULL DEFAULT '0',
  `max_order_count` int(11) NOT NULL DEFAULT '0',
  `all_order_count` int(11) NOT NULL DEFAULT '0',
  `area_id` int(11) NOT NULL DEFAULT '0',
  `offline_store_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `all_new_user_count` int(11) NOT NULL DEFAULT '0',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `max_order_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
