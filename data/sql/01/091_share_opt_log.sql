ALTER TABLE `cake_opt_logs`
ADD COLUMN `deleted` TINYINT NOT NULL DEFAULT 0 AFTER `reply_content`;
