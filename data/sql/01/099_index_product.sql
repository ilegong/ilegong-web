CREATE TABLE `cake_index_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `share_id` int(11) NOT NULL,
  `share_img` varchar(512) NOT NULL,
  `share_name` varchar(128) NOT NULL,
  `share_price` varchar(45) NOT NULL,
  `share_vote` int(11) NOT NULL,
  `share_user_id` int(11) NOT NULL,
  `share_user_img` varchar(512) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `type` int(2) NOT NULL DEFAULT '0',
  `tag_id` int(11) NOT NULL,
  `sort_val` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
