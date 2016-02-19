ALTER TABLE `cake_shared_offers`
ADD COLUMN `comment_id` INT NOT NULL DEFAULT 0 AFTER `order_id`;
