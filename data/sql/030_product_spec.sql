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



