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
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (5,'种类',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (6,'品种',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (7,'数量',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (8,'重量',0);
INSERT INTO `cake_product_attributes` (`id`,`name`,`deleted`) VALUES (9,'到货日',0);

INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (1,'榴莲',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (2,'芒果',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (3,'菠萝',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (4,'木瓜',3,0,230);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (5,'五香',1,0,383);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (6,'麻辣',1,0,383);
INSERT INTO `cake_product_specs` (`id`,`name`,`attr_id`,`deleted`,`product_id`) VALUES (7,'爆辣',1,0,383);



INSERT INTO `cake_product_spec_groups` (`id`, `spec_ids`, `price`, `stock`, `deleted`, `spec_names`, `product_id`) VALUES ('1', '1', '150', '0', '0', '榴莲', '230');
INSERT INTO `cake_product_spec_groups` (`id`, `spec_ids`, `price`, `stock`, `deleted`, `spec_names`, `product_id`) VALUES ('2', '2', '150', '0', '0', '芒果', '230');
INSERT INTO `cake_product_spec_groups` (`id`, `spec_ids`, `price`, `stock`, `deleted`, `spec_names`, `product_id`) VALUES ('3', '3', '150', '0', '0', '菠萝', '230');
INSERT INTO `cake_product_spec_groups` (`id`, `spec_ids`, `price`, `stock`, `deleted`, `spec_names`, `product_id`) VALUES ('4', '4', '150', '0', '0', '木瓜', '230');


INSERT INTO `cake_product_spec_groups` (`id`, `spec_ids`, `price`, `stock`, `deleted`, `spec_names`, `product_id`) VALUES ('5', '5', '12', '0', '0', '五香', '383');
INSERT INTO `cake_product_spec_groups` (`id`, `spec_ids`, `price`, `stock`, `deleted`, `spec_names`, `product_id`) VALUES ('6', '6', '12', '0', '0', '麻辣', '383');
INSERT INTO `cake_product_spec_groups` (`id`, `spec_ids`, `price`, `stock`, `deleted`, `spec_names`, `product_id`) VALUES ('7', '7', '12', '0', '0', '爆辣', '383');












