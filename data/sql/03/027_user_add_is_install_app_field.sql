ALTER TABLE `51daifan`.`cake_users`
ADD COLUMN `is_install_app` TINYINT(2) NOT NULL DEFAULT 0 AFTER `own_id`;
