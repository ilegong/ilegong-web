CREATE TABLE `cake_sharer_ship_options` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sharer_id` INT NOT NULL DEFAULT 0,
  `type` INT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));

  CREATE TABLE `cake_weshare_ship_settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `weshare_id` INT NOT NULL DEFAULT 0,
  `type` INT NOT NULL DEFAULT 0,
  `ship_fee` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));

