
CREATE TABLE `cake_order_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_star` int(11) DEFAULT NULL,
  `logistics_star` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;


ALTER TABLE `cake_orders` 
ADD COLUMN `is_comment` INT NULL DEFAULT 0 AFTER `type`;

ALTER TABLE `cake_comments` 
ADD COLUMN `order_id` INT NOT NULL AFTER `is_shichi_vote`;


SET SQL_SAFE_UPDATES = 0;

update cake_comments co, cake_carts ca set co.order_id=ca.order_id where co.data_id=ca.product_id and co.user_id=ca.creator and ca.order_id is not null and co.type='Product';

ALTER TABLE `cake_comments`
ADD COLUMN `buy_time` DATETIME NULL AFTER `order_id`;

update cake_comments co,cake_orders orders set co.buy_time=orders.created where co.order_id=orders.id and co.buy_time is null;

ALTER TABLE `cake_comments`
ADD COLUMN `publish_time` DATETIME NULL AFTER `buy_time`;

update cake_comments set publish_time=updated where status=1;
