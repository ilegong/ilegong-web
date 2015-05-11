CREATE TABLE `cake_tuan_ship_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(45) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*
-- Query: SELECT * FROM 51daifan.cake_tuan_ship_types
LIMIT 0, 100

-- Date: 2015-05-11 09:09
*/
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (1,'自提','ziti',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (2,'朋友说自提','pysziti',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (3,'好邻居自提','hljziti',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (4,'顺风包邮','sfby',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (5,'顺风到付','sfdf',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (6,'快递到家','kddj',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (7,'顺风到家','sfdj',0);

CREATE TABLE `cake_product_ship_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ship_type` int(11) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `ship_fee` float DEFAULT '0',
  `deleted` tinyint(4) DEFAULT '0',
  `data_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
