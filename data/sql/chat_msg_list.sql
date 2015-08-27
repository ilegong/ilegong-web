CREATE TABLE IF NOT EXISTS `cake_msg_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg` varchar(500) NOT NULL,
  `uname` varchar(30) NOT NULL,
  `action` varchar(10) NOT NULL,
  `send_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=437;