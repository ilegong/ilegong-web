DROP TABLE IF EXISTS `cake_template_histories`;
CREATE TABLE IF NOT EXISTS `cake_template_histories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `creator` bigint(13) DEFAULT '0',
  `content` text,
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
