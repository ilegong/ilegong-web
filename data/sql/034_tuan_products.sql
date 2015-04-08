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

INSERT INTO `cake_tuan_products` (`id`, `product_id`, `list_img`, `detail_img`, `alias`, `tuan_price`, `deleted`)
VALUES
	(1, 838, 'http://www.tongshijia.com/img/tuan/pro/listgood_838.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-838.jpg', '草莓', 19, 0),
	(2, 851, 'http://www.tongshijia.com/img/tuan/pro/listgood_851.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-851.jpg', '芒果', 149, 0),
	(3, 862, 'http://www.tongshijia.com/img/tuan/pro/listgood_862.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-862.jpg', '好好蛋糕', 129, 0),
	(4, 863, 'http://www.tongshijia.com/img/tuan/pro/listgood_863.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-863.jpg', '草莓863', 19, 0),
	(5, 381, 'http://www.tongshijia.com/img/tuan/pro/listgood_381.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-381.jpg', '牛肉干', 22, 0),
	(6, 868, 'http://www.tongshijia.com/img/tuan/pro/listgood_868.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-868.jpg', '建平小米', 32, 0),
	(7, 873, 'http://www.tongshijia.com/img/tuan/pro/listgood_873.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-873.jpg', '好好蛋糕大团', 168, 0),
	(8, 874, 'http://www.tongshijia.com/img/tuan/pro/listgood_874.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-874.jpg', '海南出口金菠萝', 128, 0),
	(9, 876, 'http://www.tongshijia.com/img/tuan/pro/listgood_876.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-876.jpg', '蔬菜单次试吃', 0, 0),
	(10, 879, 'http://www.tongshijia.com/img/tuan/pro/listgood_879.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-879.jpg', '烟台苹果', 74.9, 0),
	(11, 883, 'http://www.tongshijia.com/img/tuan/pro/listgood_883.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-883.jpg', '海南椰子冻奶酪', 29.9, 0),
	(12, 884, 'http://www.tongshijia.com/img/tuan/pro/listgood_884.jpg', 'http://www.tongshijia.com/img/tuan/bannerdetail-884.jpg', '释迦果', 149, 0);


