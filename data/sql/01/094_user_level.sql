CREATE TABLE `cake_user_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_value` int(11) NOT NULL,
  `data_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `deleted` tinyint(2) NOT NULL DEFAULT '0',
  `type` int(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
