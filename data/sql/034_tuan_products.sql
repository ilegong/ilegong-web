-- 创建团购产品表
ALTER TABLE cake_tuan_products
        DROP FOREIGN KEY fk_cake_tuan_products_product_id;

DROP TABLE IF EXISTS `cake_tuan_products`;

CREATE TABLE `cake_tuan_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `list_img` varchar(200) NOT NULL,
  `detail_img` varchar(200) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `tuan_price` float NOT NULL DEFAULT '0',
  `deleted` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
ALTER TABLE cake_tuan_products
        ADD CONSTRAINT fk_cake_tuan_products_product_id
        FOREIGN KEY (product_id)
        REFERENCES cake_products(id);

-- '838'=>'草莓', '851' => '芒果', '862'=>'好好蛋糕', '863' => '草莓863', '230' => '蛋糕230','381'=>'牛肉干','868'=>'建平小米','873'=>'好好蛋糕大团','874'=>'海南出口金菠萝','876'=>'蔬菜单次试吃','879'=>'烟台苹果','883'=>'海南椰子冻奶酪','884'=>'释迦果'
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(838, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(851, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(862, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(863, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(230, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(381, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(868, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(873, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(874, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(876, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(879, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(883, 19);
INSERT INTO `cake_tuan_products`(`product_id`, `tuan_price`) VALUES(884, 19);

