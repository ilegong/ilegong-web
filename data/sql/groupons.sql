CREATE TABLE `cake_teams` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '',
  `slug` varchar(50) DEFAULT '',
  `market_price` int DEFAULT '0',
  `team_price` int NOT NULL DEFAULT '0',
  `begin_time` int(11) unsigned NOT NULL DEFAULT '0',
  `end_time` int(11) unsigned NOT NULL DEFAULT '0',
  `min_number` int(11) DEFAULT NULL,
  `max_number` int(11) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `notice` varchar(1000) DEFAULT NULL,
  `per_number` int(11) NOT NULL DEFAULT '1',
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `cake_groupons` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(11) DEFAULT NULL,
  `area` varchar(40) DEFAULT NULL,
  `address` varchar(80) DEFAULT NULL,
  `mobile` varchar(18) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pay_number` int(11) NOT NULL DEFAULT '1',
  `status` tinyint(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cake_groupon_members` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupon_id` int(11) DEFAULT NULL,
  `user_id` bigint(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table `cake_teams` add column product_id int not null;
alter table `cake_groupon_members` add column team_id int not null;

alter table `cake_teams` add unit_pay int default 100;
alter table `cake_teams` add unit_val int default 500; 

alter table `cake_teams` add share_title varchar(255) default '';
alter table `cake_teams` add share_desc varchar(255) default '';
update cake_teams set share_title='就是土豪，就是任性！组团一起吃，一箱贡柑低至12元，包邮！' where id=1;
update `cake_teams` set share_desc='德庆贡柑皇帝橙，皮薄易剥，清甜香蜜，朋友说，很靠谱。' where id=1;

alter table `cake_orders` add member_id int not null default 0;


INSERT INTO `cake_teams` (`id`, `title`, `slug`, `market_price`, `team_price`, `begin_time`, `end_time`, `min_number`, `max_number`, `summary`, `notice`, `per_number`, `image`)
VALUES
	(1, '仅29.9元，天天特价', 'xixiapingguo', 1000, 400, 1419933923, 1429933923, 10, 10, '在国内推行环保10年）讲得到他们正在做的环保新农业，我就被彻底的吸引，大量的走访调查，和各个农场种植户的沟通，让我坚定的开始把这个环保生态新农业种植方式应用到我们的猕猴桃种植上。', '发起组团寄到一个指定的地点', 1, NULL);
