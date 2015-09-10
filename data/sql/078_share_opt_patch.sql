ALTER TABLE `cake_opt_logs`
ADD COLUMN `thumbnail` VARCHAR(256) NULL AFTER `referer`;
ALTER TABLE `cake_opt_logs`
ADD COLUMN `weight` INT NOT NULL DEFAULT 0 AFTER `created`;
