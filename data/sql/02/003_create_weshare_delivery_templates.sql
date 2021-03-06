CREATE TABLE `cake_weshare_delivery_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(11) NOT NULL,
  `weshare_id` int(11) NOT NULL,
  `unit_type` smallint(4) NOT NULL DEFAULT '0',
  `start_units` int(11) NOT NULL,
  `start_fee` int(11) NOT NULL,
  `add_units` int(11) NOT NULL,
  `add_fee` int(11) NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;



CREATE TABLE `cake_weshare_template_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weshare_id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `province_name` varchar(512) NOT NULL,
  `creator` int(11) NOT NULL,
  `delivery_template_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
