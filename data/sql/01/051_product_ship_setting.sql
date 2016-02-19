CREATE TABLE `cake_tuan_ship_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(45) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (1,'自提','ziti',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (4,'顺风包邮','sfby',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (5,'顺风到付','sfdf',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (6,'快递到家','kddj',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (7,'顺丰到家','sfdj',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (8,'快递包邮','baoyou',0);
INSERT INTO `cake_tuan_ship_types` (`id`,`name`,`code`,`deleted`) VALUES (9,'快递到付','kddf',0);



CREATE TABLE `cake_product_ship_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ship_type` int(11) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `ship_val` int(11) DEFAULT '0',
  `data_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;

INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (4,5,963,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (5,5,962,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (6,5,959,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (7,5,958,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (8,5,957,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (9,5,956,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (10,5,954,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (11,5,952,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (12,5,951,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (13,5,950,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (14,5,948,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (15,5,947,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (16,5,946,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (17,5,945,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (18,5,944,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (19,5,943,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (20,5,942,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (21,5,941,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (22,5,940,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (23,5,939,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (24,5,911,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (25,5,862,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (26,5,960,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (27,4,963,10,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (28,4,962,10,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (29,4,959,10,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (30,8,868,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (31,8,897,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (32,6,876,10,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (33,6,879,13,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (34,1,963,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (35,1,962,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (36,1,959,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (37,1,958,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (38,1,957,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (39,1,956,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (40,1,954,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (41,1,952,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (42,1,951,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (43,1,950,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (44,1,948,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (45,1,947,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (46,1,946,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (47,1,945,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (48,1,944,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (49,1,943,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (50,1,942,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (51,1,941,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (52,1,940,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (53,1,939,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (54,1,911,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (55,1,862,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (56,1,960,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (57,1,963,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (58,1,962,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (59,1,959,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (60,1,868,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (61,1,897,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (62,1,876,0,'Product');
INSERT INTO `cake_product_ship_settings` (`id`,`ship_type`,`data_id`,`ship_val`,`data_type`) VALUES (63,1,879,0,'Product');