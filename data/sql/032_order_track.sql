
CREATE TABLE `cake_order_tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `product_id` int(11) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) default charset=utf8;

CREATE TABLE `cake_track_order_maps` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `track_id` INT NOT NULL,
  `order_id` INT NOT NULL,
  PRIMARY KEY (`id`)) default charset=utf8;

CREATE TABLE `cake_order_track_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log` VARCHAR(500) NOT NULL,
  `track_id` INT NOT NULL,
  `date` DATETIME NULL,
  `deleted` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)) default charset=utf8;

ALTER TABLE `cake_cake_dates` RENAME `cake_consignment_dates`;

ALTER TABLE `cake_consignment_dates` ADD `product_id` INT(11);

ALTER TABLE `cake_consignment_dates`

CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NULL ;

UPDATE `cake_consignment_dates` SET `product_id`=230;

ALTER TABLE `cake_carts` ADD `consignment_date` INT(11);

ALTER TABLE `cake_consignment_dates` DROP index `send_date`;

ALTER TABLE `cake_orders`
ADD COLUMN `mark_ship_date` DATETIME NULL AFTER `member_id`,
ADD COLUMN `ship_mark` VARCHAR(200) NULL AFTER `mark_ship_date`;

