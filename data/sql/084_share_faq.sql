CREATE TABLE `cake_share_faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `share_id` int(11) NOT NULL DEFAULT '0',
  `sender` int(11) NOT NULL,
  `receiver` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `msg` tinytext NOT NULL,
  `has_read` tinyint(4) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
