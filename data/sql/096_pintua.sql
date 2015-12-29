ALTER TABLE `cake_orders`
ADD COLUMN `group_id` INT NOT NULL DEFAULT 0 AFTER `relate_type`;
