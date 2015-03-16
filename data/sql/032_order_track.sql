CREATE TABLE `cake_order_tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `product_id` int(11) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `cake_track_order_maps` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `track_id` INT NOT NULL,
  `order_id` INT NOT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `cake_order_track_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log` VARCHAR(500) NOT NULL,
  `track_id` INT NOT NULL,
  `date` DATETIME NULL,
  `deleted` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));
