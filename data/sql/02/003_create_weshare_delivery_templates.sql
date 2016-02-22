CREATE TABLE `cake_weshare_delivery_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL,
  `weshare_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `unit_type` int(11) NOT NULL,
  `start_units` int(11) NOT NULL,
  `start_fee` int(11) NOT NULL,
  `add_units` int(11) NOT NULL,
  `add_fee` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
