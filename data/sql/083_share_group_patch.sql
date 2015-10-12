ALTER TABLE `cake_weshares`
ADD COLUMN `order_status` INT NOT NULL DEFAULT 0 AFTER `refer_share_id`;
