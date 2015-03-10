CREATE TABLE `cake_product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `deleted` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE `cake_product_specs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `attr_id` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;


CREATE TABLE `cake_product_spec_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spec_ids` varchar(100) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  `spec_names` varchar(500) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;

INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (1,'口味',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (2,'尺寸',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (3,'蛋糕口味',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (4,'规格',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (5,'水果到付日',0);

INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (1,'榴莲',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (2,'芒果',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (3,'菠萝',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (4,'木瓜',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (5,'半奶13日自提',4,0,822);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (6,'全奶13日自提',4,0,822);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (7,'半奶15日自提',4,0,822);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (8,'全奶15日自提',4,0,822);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (9,'3月13日',5,0,657);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (10,'3月15日',5,0,657);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (11,'3月13日',5,0,639);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (12,'3月15日',5,0,639);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (13,'五香',1,0,383);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (14,'麻辣',1,0,383);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (15,'爆辣',1,0,383);






