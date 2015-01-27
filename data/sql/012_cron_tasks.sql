CREATE TABLE `cake_crons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `type` tinyint(11) NOT NULL DEFAULT '0',
  `expires` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;