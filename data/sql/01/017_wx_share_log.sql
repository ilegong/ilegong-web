CREATE TABLE `cake_wx_shares` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sharer` int(11) NOT NULL DEFAULT '0',
  `data_type` varchar(12) DEFAULT '',
  `data_id` int(11) DEFAULT '0',
  `created` int(11) DEFAULT '0',
  `share_type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `cake_share_track_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sharer` int(11) NOT NULL DEFAULT '0',
  `clicker` int(11) NOT NULL DEFAULT '0',
  `share_time` int(11) DEFAULT '0',
  `click_time` int(11) DEFAULT '0',
  `data_type` varchar(12) DEFAULT '',
  `data_id` int(11) DEFAULT '0',
  `share_type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;