ALTER TABLE `cake_users` ADD `own_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `hx_password`, ADD INDEX (`own_id`);