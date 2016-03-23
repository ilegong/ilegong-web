DROP TABLE IF EXISTS `cake_new_finds`;
CREATE TABLE `cake_new_finds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `type` smallint NOT NULL DEFAULT '0' COMMENT '0默认未定, 不会显示. 1表示轮播, 2表示TOP榜',
  `sort` int(11) NOT NULL DEFAULT '0',
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
