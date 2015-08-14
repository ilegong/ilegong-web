CREATE TABLE `cake_comment_replys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL DEFAULT '0',
  `reply_id` int(11) NOT NULL DEFAULT '0',
  `data_id` int(11) NOT NULL DEFAULT '0',
  `data_type` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
