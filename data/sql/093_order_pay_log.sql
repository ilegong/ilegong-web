ALTER TABLE `cake_pay_logs`
ADD COLUMN `type` INT NOT NULL DEFAULT 0 AFTER `updated`;

ALTER TABLE `cake_pay_notifies`
ADD COLUMN `type` INT NOT NULL DEFAULT 0 AFTER `order_id`;

