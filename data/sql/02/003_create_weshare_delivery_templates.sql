CREATE TABLE `cake_weshare_delivery_templates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `weshare_id` int(11) NOT NULL,
  `unit_type` smallint(4) NOT NULL DEFAULT '0',
  `start_units` int(11) NOT NULL,
  `start_fee` int(11) NOT NULL,
  `add_units` int(11) NOT NULL,
  `add_fee` int(11) NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
