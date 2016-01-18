CREATE TABLE `cake_sharer_statics_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_count` int(11) NOT NULL DEFAULT '0',
  `trading_volume` float NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `data_date` date NOT NULL,
  `sharer_id` int(11) NOT NULL,
  `share_count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
