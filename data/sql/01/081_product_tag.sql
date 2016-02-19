ALTER TABLE `cake_weshare_products`
ADD COLUMN `tag_id` INT NOT NULL DEFAULT 0 AFTER `tbd`;

ALTER TABLE `cake_carts`
ADD COLUMN `tag_id` INT NOT NULL DEFAULT 0 AFTER `confirm_price`;

ALTER TABLE `cake_weshare_products`
ADD COLUMN `deleted` TINYINT NOT NULL DEFAULT 0 AFTER `tag_id`;


CREATE TABLE `cake_weshare_product_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `created` datetime NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;



