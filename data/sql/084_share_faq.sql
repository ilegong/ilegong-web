CREATE TABLE `cake_share_faqs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `share_id` INT NOT NULL DEFAULT 0,
  `sender` INT NOT NULL,
  `reciver` INT NOT NULL,
  `created` DATETIME NOT NULL,
  `msg` TINYTEXT NOT NULL,
  `readed` TINYINT NOT NULL DEFAULT 0,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));
