CREATE TABLE `cake_user_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(11) NOT NULL,
  `cart_id` bigint(11) NOT NULL,
  `customized_price` float NOT NULL,
  `uid` bigint(11) not null,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;