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
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(838, '兴寿草莓', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(851, '海南芒果', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(862, '好好蛋糕', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(863, '16元草莓' 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(230, '薇薇安蛋糕', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(381, '饭二牛肉干' 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(868, '建平小米', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(873, '好好蛋糕大团', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(874, '海南出口金菠萝', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(876, '蔬菜单次试吃', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(879, '烟台苹果', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(883, '海南椰子冻奶酪', 19);
INSERT INTO `cake_tuan_products`(`product_id`, `alias`, `tuan_price`) VALUES(884, '海南释迦果', 19);

