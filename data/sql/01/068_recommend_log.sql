CREATE TABLE `cake_recommend_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_id` int(11) NOT NULL DEFAULT '0',
  `data_type` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `memo` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
