ALTER TABLE `cake_coupons` ADD `shop_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `least_price`, ADD INDEX (`shop_id`);