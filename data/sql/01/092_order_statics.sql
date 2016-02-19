CREATE TABLE `cake_statistics_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_count` int(11) NOT NULL DEFAULT '0',
  `trading_volume` float NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `data_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
