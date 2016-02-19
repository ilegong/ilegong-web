CREATE TABLE `cake_sharer_ship_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sharer_id` int(11) NOT NULL DEFAULT '0',
  `type` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cake_weshare_ship_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weshare_id` int(11) NOT NULL DEFAULT '0',
  `status` int(2) NOT NULL DEFAULT '0',
  `ship_fee` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
