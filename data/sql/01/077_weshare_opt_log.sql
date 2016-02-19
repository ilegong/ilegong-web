CREATE TABLE `cake_opt_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `obj_type` int(11) NOT NULL DEFAULT '0',
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `parent_type` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `shown` int(11) NOT NULL DEFAULT '0',
  `reply_count` int(11) NOT NULL DEFAULT '0',
  `memo` text,
  `referer` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `cake_recommend_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_id` int(11) NOT NULL DEFAULT '0',
  `data_type` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `memo` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
