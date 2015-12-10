---pay log table add type



---pay notify table add type

--- logistics orders
CREATE TABLE `cake_logistics_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(2) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `update` datetime NOT NULL,
  `starting_name` varchar(64) NOT NULL,
  `starting_phone` varchar(45) NOT NULL,
  `starting_province` varchar(64) DEFAULT NULL,
  `starting_city` varchar(64) NOT NULL,
  `starting_address` varchar(256) NOT NULL,
  `total_price` float NOT NULL,
  `pickup_time` datetime DEFAULT NULL,
  `pickup_code` varchar(128) DEFAULT NULL,
  `business_no` varchar(128) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `business_order_id` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- logistics order item
CREATE TABLE `cake_logistics_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logistics_order_id` int(11) NOT NULL,
  `goods_name` varchar(256) NOT NULL,
  `goods_weight` double NOT NULL,
  `goods_worth` float NOT NULL,
  `consignee_name` varchar(128) NOT NULL,
  `consignee_phone` varchar(45) NOT NULL,
  `consignee_province` varchar(45) DEFAULT NULL,
  `consignee_city` varchar(45) NOT NULL,
  `consignee_address` varchar(256) NOT NULL,
  `remark` varchar(128) DEFAULT NULL,
  `total_price` float NOT NULL,
  `business_no` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
