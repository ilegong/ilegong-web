ALTER TABLE `cake_refund_logs`
ADD COLUMN `data_id` INT NOT NULL DEFAULT 0 AFTER `type`;
