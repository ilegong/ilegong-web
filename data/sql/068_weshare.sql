CREATE TABLE `cake_weshares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` varchar(400) NOT NULL,
  `images` varchar(500) DEFAULT NULL,
  `status` int(2) NOT NULL DEFAULT '0',
  `creator` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cake_weshare_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL DEFAULT '0',
  `weshare_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cake_weshare_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weshare_id` int(11) NOT NULL,
  `address` varchar(100) NOT NULL,
  `get_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
