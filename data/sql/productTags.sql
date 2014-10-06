DROP TABLE IF EXISTS `cake_product_tags`;
CREATE TABLE IF NOT EXISTS `cake_product_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `slug` varchar(30) NOT NULL DEFAULT '',
  `priority` int(11) DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`name`),
  unique key `slug_name` (`slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

insert into cake_product_tags(name, priority, slug) values
 ('最新热卖', 1, 'hottest'),
 ('新品试吃', 2, 'newest'),
 ('水果干果', 3, 'shuiguoganguo'),
 ('粮油肉蛋', 4, 'liangyou')
 ;
 
DROP table if exists `cake_product_productTags`;
create table if not exists `cake_product_productTags` (
	`id` bigint not null AUTO_INCREMENT primary key,
	`recommend` int not null default 0,
	`product_id` bigint not null,
	`tag_id` int not null,
	unique key (`tag_id`, `product_id`)
) default charset=utf8; 