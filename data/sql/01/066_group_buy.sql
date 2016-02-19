CREATE TABLE `cake_group_buy_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `group_buy_tag` varchar(45) NOT NULL,
  `created` datetime NOT NULL,
  `is_paid` tinyint(4) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `cake_group_buy_records`
ADD UNIQUE INDEX `order_id_UNIQUE` (`order_id` ASC);


ALTER TABLE `cake_group_buy_records`
ADD COLUMN `is_send_msg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `is_paid`;


ALTER TABLE `cake_group_buy_records`
ADD COLUMN `group_buy_label` VARCHAR(45) NOT NULL DEFAULT '' AFTER `deleted`;
