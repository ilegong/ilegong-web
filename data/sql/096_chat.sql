ALTER TABLE `51daifan`.`cake_users`
ADD COLUMN `hx_password` VARCHAR(128) NULL AFTER `is_proxy`;


CREATE TABLE `cake_user_friends` (
  `id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `friend_id` INT NOT NULL,
  `status` INT NOT NULL DEFAULT 0,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `cake_user_groups` (
  `id` INT NOT NULL,
  `hx_group_id` VARCHAR(128) NOT NULL,
  `created` DATETIME NOT NULL,
  `creator` INT NOT NULL,
  `approval` TINYINT NOT NULL DEFAULT 0,
  `public` TINYINT NOT NULL DEFAULT 0,
  `maxusers` INT NOT NULL DEFAULT 300,
  `description` VARCHAR(256) NOT NULL,
  `status` INT NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));
